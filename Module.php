<?php declare(strict_types=1);

namespace Bibliography;

// Load the module dependencies when installed as a zip.
// With composer, libraries are stored in omeka vendor/ and the module has none.
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Common may be installed but not registered in autoloader, in particular
// during upgrade. So dynamically register all classes of the module.
if (!defined('COMMON_PSR4_FALLBACK')) {
    foreach ([
        OMEKA_PATH . '/modules/Common/src',
        OMEKA_PATH . '/composer-addons/modules/Common/src',
        dirname(__DIR__) . '/Common/src',
    ] as $commonSrc) {
        if (file_exists($commonSrc . '/TraitModule.php')) {
            define('COMMON_PSR4_FALLBACK', $commonSrc);
            spl_autoload_register(static function ($class): void {
                if (str_starts_with($class, 'Common\\')) {
                    $file = COMMON_PSR4_FALLBACK . '/' . strtr(substr($class, 7), '\\', '/') . '.php';
                    if (file_exists($file)) {
                        require_once $file;
                    }
                }
            });
            break;
        }
    }
}

use Common\Stdlib\PsrMessage;
use Common\TraitModule;
use Laminas\EventManager\Event;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Mvc\MvcEvent;
use Omeka\Module\AbstractModule;

/**
 * Bibliography
 *
 * Tools to manage bibliographic items.
 *
 * @copyright Daniel Berthereau, 2018-2024
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt
 */
class Module extends AbstractModule
{
    use TraitModule;

    const NAMESPACE = __NAMESPACE__;

    protected function preInstall(): void
    {
        $services = $this->getServiceLocator();
        $translator = $services->get('MvcTranslator');

        if (!method_exists($this, 'checkModuleActiveVersion') || !$this->checkModuleActiveVersion('Common', '3.4.94')) {
            $message = new \Omeka\Stdlib\Message(
                $translator->translate('The module %1$s should be upgraded to version %2$s or later.'), // @translate
                'Common', '3.4.94'
            );
            throw new \Omeka\Module\Exception\ModuleCannotInstallException((string) $message);
        }

        $errors = [];

        $file = __DIR__ . '/vendor/autoload.php';
        if (!file_exists($file)) {
            $message = new PsrMessage(
                'The libraries should be installed. See module’s installation documentation.' // @translate
            );
            $errors[] = (string) $message->setTranslator($translator);
        }

        if ($errors) {
            throw new \Omeka\Module\Exception\ModuleCannotInstallException(implode("\n", $errors));
        }

        // Note: The creation of the vocabulary directly failed in previous
        // version, so there were a pull request https://github.com/omeka/omeka-s/pull/1335 (Omeka < 2.1)
        // and a way to import it via sql.
        // The fix was done separetly in https://github.com/omeka/omeka-s/commit/8d1476bcc8f2ec51126a44ea7497025ea1dbcb3b.
    }

    protected function postInstall(): void
    {
        $this->uninstallModuleCitation();
    }

    public function onBootstrap(MvcEvent $event): void
    {
        parent::onBootstrap($event);

        $this->getServiceLocator()->get('Omeka\Acl')
            ->allow(
                null,
                ['Bibliography\Controller\Bibliography'],
                ['output']
            )
        ;
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager): void
    {
        // The site is not set yet, so checks are done in method.
        $sharedEventManager->attach(
            'Omeka\Controller\Site\Item',
            'view.show.before',
            [$this, 'handleDisplayCitation']
        );
        $sharedEventManager->attach(
            'Omeka\Controller\Site\Item',
            'view.show.after',
            [$this, 'handleDisplayCitation']
        );

        // Expand the values "collected record" into the resource.
        foreach ([
            \Omeka\Api\Adapter\ItemAdapter::class,
            \Omeka\Api\Adapter\ItemSetAdapter::class,
            \Omeka\Api\Adapter\MediaAdapter::class,
        ] as $adapter) {
            $sharedEventManager->attach(
                $adapter,
                'api.hydrate.pre',
                [$this, 'handleRecordValues']
            );
        }
        foreach ([
            'Omeka\Controller\Admin\Item',
            'Omeka\Controller\Admin\ItemSet',
            'Omeka\Controller\Admin\Media',
        ] as $controller) {
            $sharedEventManager->attach(
                $controller,
                'view.add.after',
                [$this, 'handleResourceForm']
            );
            $sharedEventManager->attach(
                $controller,
                'view.edit.after',
                [$this, 'handleResourceForm']
            );
        }

        $sharedEventManager->attach(
            \Omeka\Form\SettingForm::class,
            'form.add_elements',
            [$this, 'handleMainSettings']
        );
        // TODO Remove in version of Common 3.4.64.
        $sharedEventManager->attach(
            \Omeka\Form\SettingForm::class,
            'form.add_input_filters',
            [$this, 'handleMainSettingsFilters']
        );

        $sharedEventManager->attach(
            \Omeka\Form\SiteSettingsForm::class,
            'form.add_elements',
            [$this, 'handleSiteSettings']
        );
    }

    public function handleMainSettingsFilters(Event $event): void
    {
        $inputFilter = $event->getParam('inputFilter');
        $inputFilter
            ->add([
                'name' => 'bibliography_crossref_email',
                'required' => false,
            ]);
    }

    public function handleDisplayCitation(Event $event): void
    {
        /**
         * @var \Omeka\Settings\SiteSettings $siteSettings
         * @var \Laminas\View\Renderer\PhpRenderer $view
         * @var \Omeka\Api\Representation\AbstractResourceEntityRepresentation $resource
         * @see \Bibliography\View\Helper\Citation
         */

        $services = $this->getServiceLocator();
        $siteSettings = $services->get('Omeka\Settings\Site');

        $view = $event->getTarget();
        $resource = $view->resource;
        $resourceName = $resource->resourceName();

        $placements = $siteSettings->get('bibliography_placement_citation', []);
        if (in_array('before/' . $resourceName, $placements)
            || in_array('after/' . $resourceName, $placements)
        ) {
            echo $view->citation($resource, ['tag' => 'p']);
        }
    }

    /**
     * Expand the values "collected record" into the resource on save.
     */
    public function handleRecordValues(Event $event): void
    {
        /**
         * @var \Omeka\Api\Request $request
         * @var \Omeka\Entity\Resource $entity
         */
        $request = $event->getParam('request');
        $data = $request->getContent();
        if (!is_array($data) || strpos(json_encode($data), ':record"') === false) {
            return;
        }

        // During a partial update, the properties not sent are kept.
        $filledTerms = [];
        if ($request->getOperation() === \Omeka\Api\Request::UPDATE && $request->getOption('isPartial')) {
            foreach ($event->getParam('entity')->getValues() as $value) {
                $property = $value->getProperty();
                $filledTerms[] = $property->getVocabulary()->getPrefix() . ':' . $property->getLocalName();
            }
        }

        $result = (new \Bibliography\Stdlib\RecordExpander())->expand($data, array_unique($filledTerms));
        if (!$result['filled'] && !$result['skipped']) {
            return;
        }

        $request->setContent($result['data']);

        if ($result['skipped']) {
            $messenger = $this->getServiceLocator()->get('ControllerPluginManager')->get('messenger');
            $messenger->addWarning(new PsrMessage(
                'The bibliographic record was not used for these properties, that were already filled: {properties}.', // @translate
                ['properties' => implode(', ', $result['skipped'])]
            ));
        }
    }

    /**
     * Fill the resource form from the values "collected record".
     */
    public function handleResourceForm(Event $event): void
    {
        if (!class_exists('ValueSuggest\Module', false)) {
            return;
        }
        $view = $event->getTarget();
        $view->headScript()
            ->appendFile($view->assetUrl('js/bibliography-record.js', 'Bibliography'), 'text/javascript', ['defer' => 'defer']);
    }

    protected function uninstallModuleCitation(): void
    {
        $services = $this->getServiceLocator();
        /** @var \Omeka\Module\Manager $moduleManager */
        $moduleManager = $services->get('Omeka\ModuleManager');
        $module = $moduleManager->getModule('Citation');
        if (!$module) {
            return;
        }

        $state = $module->getState();
        if (!in_array($state, [
            \Omeka\Module\Manager::STATE_ACTIVE,
            \Omeka\Module\Manager::STATE_NOT_ACTIVE,
            \Omeka\Module\Manager::STATE_NOT_FOUND,
            \Omeka\Module\Manager::STATE_NEEDS_UPGRADE,
            \Omeka\Module\Manager::STATE_INVALID_OMEKA_VERSION,
        ])) {
            return;
        }

        $messenger = $services->get('ControllerPluginManager')->get('messenger');

        // Process uninstallation directly: the module has nothing to uninstall.
        $entityManager = $services->get('Omeka\EntityManager');
        $entity = $entityManager
            ->getRepository(\Omeka\Entity\Module::class)
            ->findOneById($module->getId());
        if (!$entity) {
            $message = new PsrMessage(
                'The module Bibliography replaces the module Citation, that cannot be automatically uninstalled.' // @translate
            );
            $messenger->addWarning($message);
            return;
        }

        $entityManager->remove($entity);
        $entityManager->flush();

        $message = new PsrMessage(
            'The module Bibliography replaces the module Citation, that was automatically uninstalled.' // @translate
        );
        $messenger->addNotice($message);

        $module->setState(\Omeka\Module\Manager::STATE_NOT_INSTALLED);
    }
}
