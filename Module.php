<?php
namespace Citation;

use Omeka\Module\AbstractModule;

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
}
