<?php
namespace Bibliography;

if (!class_exists(\Generic\AbstractModule::class)) {
    require file_exists(dirname(__DIR__) . '/Generic/AbstractModule.php')
        ? dirname(__DIR__) . '/Generic/AbstractModule.php'
        : __DIR__ . '/src/Generic/AbstractModule.php';
}

use Generic\AbstractModule;
use Zend\EventManager\Event;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\ModuleManager\ModuleManager;

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

    public function init(ModuleManager $moduleManager)
    {
        require_once __DIR__ . '/vendor/autoload.php';
    }

    protected function postInstall()
    {
        $this->uninstallModuleCitation();
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        $sharedEventManager->attach(
            'Omeka\Controller\Site\Item',
            'view.show.after',
            [$this, 'handleViewShowAfter']
        );
    }

    public function handleViewShowAfter(Event $event)
    {
        $view = $event->getTarget();
        echo $view->citation($view->resource, ['tag' => 'p']);
    }

    protected function uninstallModuleCitation()
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
}
