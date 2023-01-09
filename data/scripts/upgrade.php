<?php declare(strict_types=1);

namespace Bibliography;

use Omeka\Stdlib\Message;

/**
 * @var Module $this
 * @var \Laminas\ServiceManager\ServiceLocatorInterface $services
 * @var string $newVersion
 * @var string $oldVersion
 *
 * @var \Omeka\Api\Manager $api
 * @var \Omeka\Settings\Settings $settings
 * @var \Doctrine\DBAL\Connection $connection
 * @var \Doctrine\ORM\EntityManager $entityManager
 * @var \Omeka\Mvc\Controller\Plugin\Messenger $messenger
 */
$plugins = $services->get('ControllerPluginManager');
$api = $plugins->get('api');
$settings = $services->get('Omeka\Settings');
$connection = $services->get('Omeka\Connection');
$messenger = $plugins->get('messenger');
$entityManager = $services->get('Omeka\EntityManager');

if (version_compare($oldVersion, '3.0.3', '<')) {
    $sql = <<<'SQL'
UPDATE site_page_block
SET data = REPLACE(data, '"partial":"', '"template":"')
WHERE layout = 'bibliography';
SQL;
    $connection->exec($sql);
}

if (version_compare($oldVersion, '3.0.6', '<')) {
    $this->uninstallModuleCitation();
}

if (version_compare($oldVersion, '3.1.1', '<')) {
    $this->installAllResources();
}
