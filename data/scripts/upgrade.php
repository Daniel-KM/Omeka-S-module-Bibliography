<?php declare(strict_types=1);

namespace Bibliography;

/**
 * @var Module $this
 * @var \Laminas\ServiceManager\ServiceLocatorInterface $services
 * @var string $newVersion
 * @var string $oldVersion
 *
 * @var \Omeka\Api\Manager $api
 * @var \Omeka\View\Helper\Url $url
 * @var \Omeka\Settings\Settings $settings
 * @var \Doctrine\DBAL\Connection $connection
 * @var \Doctrine\ORM\EntityManager $entityManager
 * @var \Omeka\Mvc\Controller\Plugin\Messenger $messenger
 */
$plugins = $services->get('ControllerPluginManager');
$url = $services->get('ViewHelperManager')->get('url');
$api = $plugins->get('api');
$settings = $services->get('Omeka\Settings');
$translate = $plugins->get('translate');
// $translator = $services->get('MvcTranslator');
$connection = $services->get('Omeka\Connection');
$messenger = $plugins->get('messenger');
$entityManager = $services->get('Omeka\EntityManager');

if (!method_exists($this, 'checkModuleActiveVersion') || !$this->checkModuleActiveVersion('Common', '3.4.63')) {
    $message = new \Omeka\Stdlib\Message(
        $translate('The module %1$s should be upgraded to version %2$s or later.'), // @translate
        'Common', '3.4.63'
    );
    throw new \Omeka\Module\Exception\ModuleCannotInstallException((string) $message);
}

if (version_compare($oldVersion, '3.0.3', '<')) {
    $sql = <<<'SQL'
UPDATE site_page_block
SET data = REPLACE(data, '"partial":"', '"template":"')
WHERE layout = 'bibliography';
SQL;
    $connection->executeStatement($sql);
}

if (version_compare($oldVersion, '3.0.6', '<')) {
    $this->uninstallModuleCitation();
}

if (version_compare($oldVersion, '3.1.1', '<')) {
    $this->installAllResources();
}
