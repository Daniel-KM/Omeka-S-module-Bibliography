<?php declare(strict_types=1);

namespace Bibliography;

if (!class_exists(\Common\TraitModule::class)) {
    require_once dirname(__DIR__) . '/Common/TraitModule.php';
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

    public function init(ModuleManager $moduleManager): void
    {
        require_once __DIR__ . '/vendor/autoload.php';
    }

    protected function preInstall(): void
    {
        $services = $this->getServiceLocator();
        $translate = $services->get('ControllerPluginManager')->get('translate');
        $translator = $services->get('MvcTranslator');

        if (!method_exists($this, 'checkModuleActiveVersion') || !$this->checkModuleActiveVersion('Common', '3.4.63')) {
            $message = new \Omeka\Stdlib\Message(
                $translate('The module %1$s should be upgraded to version %2$s or later.'), // @translate
                'Common', '3.4.63'
            );
            throw new \Omeka\Module\Exception\ModuleCannotInstallException((string) $message);
        }

        $file = __DIR__ . '/vendor/autoload.php';
        if (!file_exists($file)) {
            $message = new PsrMessage(
                'The libraries should be installed. See module’s installation documentation.' // @translate
            );
            throw new \Omeka\Module\Exception\ModuleCannotInstallException((string) $message->setTranslator($translator));
        }

        // Note: The creation of the vocabulary directly failed in previous
        // veresion, so there were a pull request https://github.com/omeka/omeka-s/pull/1335 (Omeka < 2.1)
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
        $sharedEventManager->attach(
            'Omeka\Controller\Site\Item',
            'view.show.after',
            [$this, 'handleViewShowAfter']
        );

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

    public function handleViewShowAfter(Event $event): void
    {
        $view = $event->getTarget();
        echo $view->citation($view->resource, ['tag' => 'p']);
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
