<?php declare(strict_types=1);
namespace Bibliography;

if (!class_exists(\Generic\AbstractModule::class)) {
    require file_exists(dirname(__DIR__) . '/Generic/AbstractModule.php')
        ? dirname(__DIR__) . '/Generic/AbstractModule.php'
        : __DIR__ . '/src/Generic/AbstractModule.php';
}

use Generic\AbstractModule;
use Laminas\EventManager\Event;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Mvc\MvcEvent;

/**
 * Bibliography
 *
 * Tools to manage bibliographic items.
 *
 * @copyright Daniel Berthereau, 2018-2019
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt
 */
class Module extends AbstractModule
{
    const NAMESPACE = __NAMESPACE__;

    public function init(ModuleManager $moduleManager): void
    {
        require_once __DIR__ . '/vendor/autoload.php';
    }

    protected function postInstall(): void
    {
        $this->uninstallModuleCitation();
        $this->installResources();
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
        $sharedEventManager->attach(
            \Omeka\Form\SiteSettingsForm::class,
            'form.add_input_filters',
            [$this, 'handleSiteSettingsFilters']
        );
    }

    public function handleMainSettingsFilters(Event $event): void
    {
        $event->getParam('inputFilter')
            ->get('bibliography')
            ->add([
                'name' => 'bibliography_crossref_email',
                'required' => false,
            ])
            ->add([
                'name' => 'bibliography_csl_style',
                'required' => false,
            ])
            ->add([
                'name' => 'bibliography_csl_locale',
                'required' => false,
            ]);
    }

    public function handleSiteSettingsFilters(Event $event): void
    {
        $inputFilter = $event->getParam('inputFilter');
        $inputFilter->get('bibliography')
            ->add([
                'name' => 'bibliography_csl_style',
                'required' => false,
            ])
            ->add([
                'name' => 'bibliography_csl_locale',
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

        $t = $services->get('MvcTranslator');
        $messenger = new \Omeka\Mvc\Controller\Plugin\Messenger();

        // Process uninstallation directly: the module has nothing to uninstall.
        $entityManager = $services->get('Omeka\EntityManager');
        $entity = $entityManager
            ->getRepository(\Omeka\Entity\Module::class)
            ->findOneById($module->getId());
        if (!$entity) {
            $message = new \Omeka\Stdlib\Message(
                $t->translate('The module Bibliography replaces the module Citation, that cannot be automatically uninstalled.') // @translate
            );
            $messenger->addWarning($message);
            return;
        }

        $entityManager->remove($entity);
        $entityManager->flush();

        $message = new \Omeka\Stdlib\Message(
            $t->translate('The module Bibliography replaces the module Citation, that was automatically uninstalled.') // @translate
        );
        $messenger->addNotice($message);

        $module->setState(\Omeka\Module\Manager::STATE_NOT_INSTALLED);
    }

    protected function installResources(): void
    {
        if (!class_exists(\Generic\InstallResources::class)) {
            require_once file_exists(dirname(__DIR__) . '/Generic/InstallResources.php')
                ? dirname(__DIR__) . '/Generic/InstallResources.php'
                : __DIR__ . '/src/Generic/InstallResources.php';
        }

        $services = $this->getServiceLocator();
        $installResources = new \Generic\InstallResources($services);
        $installResources = $installResources();

        $vocabulary = [
            'vocabulary' => [
                'o:namespace_uri' => 'http://purl.org/spar/fabio/',
                'o:prefix' => 'fabio',
                'o:label' => 'FaBiO', // @translate
                'o:comment' => 'FaBiO, the FRBR-aligned Bibliographic Ontology', // @translate
            ],
            'strategy' => 'file',
            'file' => __DIR__ . '/data/vocabularies/fabio_2019-02-19.ttl',
            'format' => 'turtle',
        ];
        // This vocabulary is too big to be imported directly, so use sql.
        // @todo Creation vocabulary directly with pull request https://github.com/omeka/omeka-s/pull/1335 (Omeka < 2.1).
        // $installResources->createVocabulary($vocabulary);
        if ($installResources->checkVocabulary($vocabulary)) {
            return;
        }
        $this->createVocabularyViaSql($vocabulary, __DIR__ . '/data/install/fabio.sql');
    }

    protected function createVocabularyViaSql(array $vocabulary, $file): void
    {
        $vocabulary = $vocabulary['vocabulary'];

        $services = $this->getServiceLocator();
        $connection = $services->get('Omeka\Connection');

        $api = $services->get('ControllerPluginManager')->get('api');
        $vocab = $api->searchOne('vocabularies', ['namespace_uri' => $vocabulary['o:namespace_uri']])->getContent();
        if ($vocab) {
            return;
        }

        $userId = $services->get('Omeka\AuthenticationService')->getIdentity()->getId();

        $sql = <<<SQL
INSERT INTO `vocabulary` (`owner_id`, `namespace_uri`, `prefix`, `label`, `comment`) VALUES
($userId, "{$vocabulary['o:namespace_uri']}", "{$vocabulary['o:prefix']}", "{$vocabulary['o:label']}", "{$vocabulary['o:comment']}");
SQL;
        $connection->exec($sql);

        $vocabularyId = $api->searchOne('vocabularies', ['namespace_uri' => $vocabulary['o:namespace_uri']])->getContent()->id();

        $sql = file_get_contents($file);
        $sql = str_replace('(1, __VOCABULARY_ID__,', "($userId, $vocabularyId,", $sql);
        $connection->exec($sql);
    }
}
