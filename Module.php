<?php
namespace Citation;

use Omeka\Module\AbstractModule;
use Zend\EventManager\Event;
use Zend\EventManager\SharedEventManagerInterface;

/**
 * Citation
 *
 * Displays a standard citation for items and bibliographic resources.
 *
 * @copyright Daniel Berthereau, 2018-2019
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt
 */
class Module extends AbstractModule
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
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
        echo $view->citation($view->resource);
    }
}
