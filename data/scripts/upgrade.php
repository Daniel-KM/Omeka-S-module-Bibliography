<?php declare(strict_types=1);

namespace Bibliography;

use Common\Stdlib\PsrMessage;

/**
 * @var Module $this
 * @var \Laminas\ServiceManager\ServiceLocatorInterface $services
 * @var string $newVersion
 * @var string $oldVersion
 *
 * @var \Omeka\Api\Manager $api
 * @var \Omeka\View\Helper\Url $url
 * @var \Laminas\Log\Logger $logger
 * @var \Omeka\Settings\Settings $settings
 * @var \Doctrine\DBAL\Connection $connection
 * @var \Doctrine\ORM\EntityManager $entityManager
 * @var \Omeka\Mvc\Controller\Plugin\Messenger $messenger
 */
$plugins = $services->get('ControllerPluginManager');
$url = $services->get('ViewHelperManager')->get('url');
$api = $plugins->get('api');
$logger = $services->get('Omeka\Logger');
$settings = $services->get('Omeka\Settings');
$translate = $plugins->get('translate');
$translator = $services->get('MvcTranslator');
$connection = $services->get('Omeka\Connection');
$messenger = $plugins->get('messenger');
$entityManager = $services->get('Omeka\EntityManager');

if (!method_exists($this, 'checkModuleActiveVersion') || !$this->checkModuleActiveVersion('Common', '3.4.66')) {
    $message = new \Omeka\Stdlib\Message(
        $translate('The module %1$s should be upgraded to version %2$s or later.'), // @translate
        'Common', '3.4.66'
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

if (version_compare($oldVersion, '3.4.9', '<')) {
    /**
     * Migrate blocks of this module to new blocks of Omeka S v4.1.
     *
     * Migrate templates.
     * Replace filled settting "heading" by a specific block "Heading" or "Html".
     *
     * @var \Laminas\Log\Logger $logger
     *
     * @see \Omeka\Db\Migrations\MigrateBlockLayoutData
     */

    // It is not possible to search for templates that use heading, because it
    // is used in many places. So only in doc block.

    // Check themes that use "$heading" in block
    $strings = [
        'themes/*/view/common/block-layout/bibliography*' => [
            '* @var string $heading',
            'if ($options[\'heading\'])',
        ],
        'themes/*/view/common/block-template/bibliography*' => [
            '* @var string $heading',
            'if ($options[\'heading\'])',
        ],
    ];
    $manageModuleAndResources = $this->getManageModuleAndResources();
    $results = [];
    foreach ($strings as $path => $strings) {
        $result = $manageModuleAndResources->checkStringsInFiles($strings, $path);
        if ($result) {
            $results[] = $result;
        }
    }
    if ($results) {
        $message = new PsrMessage(
            'The option "heading" was removed from block Bibliography and replaced by a block Heading (if module Block Plus is present) or Html. Remove it in the following files before upgrade and automatic conversion: {json}', // @translate
            ['json' => json_encode($results, 448)]
        );
        $logger->err($message->getMessage(), $message->getContext());
        throw new \Omeka\Module\Exception\ModuleCannotInstallException((string) $message->setTranslator($translator));
    }

    $pageRepository = $entityManager->getRepository(\Omeka\Entity\SitePage::class);

    $viewHelpers = $services->get('ViewHelperManager');
    $escape = $viewHelpers->get('escapeHtml');
    $hasBlockPlus = $this->isModuleActive('BlockPlus');

    $pagesUpdated = [];
    $pagesUpdated2 = [];
    foreach ($pageRepository->findAll() as $page) {
        $pageSlug = $page->getSlug();
        $siteSlug = $page->getSite()->getSlug();
        $position = 0;
        foreach ($page->getBlocks() as $block) {
            $block->setPosition(++$position);
            $layout = $block->getLayout();
            if ($layout !== 'bibliography') {
                continue;
            }
            $data = $block->getData() ?: [];
            $layoutData = $block->getLayoutData() ?? [];

            // Migrate template.
            $template = $data['template'] ?? '';
            $layoutData = $block->getLayoutData() ?? [];
            $existingTemplateName = $layoutData['template_name'] ?? null;
            $templateName = pathinfo($template, PATHINFO_FILENAME);
            $templateCheck = 'bibliography';
            if ($templateName
                && $templateName !== $templateCheck
                && (!$existingTemplateName || $existingTemplateName === $templateCheck)
            ) {
                $layoutData['template_name'] = $templateName;
                $pagesUpdated[$siteSlug][$pageSlug] = $pageSlug;
            }
            unset($data['template']);

            // Replace setting "heading".
            $heading = $data['options']['heading'] ?? $data['heading'] ?? '';
            if (strlen($heading)) {
                $b = new \Omeka\Entity\SitePageBlock();
                $b->setPage($page);
                $b->setPosition(++$position);
                if ($hasBlockPlus) {
                    $b->setLayout('heading');
                    $b->setData([
                        'text' => $heading,
                        'level' => 2,
                    ]);
                } else {
                    $b->setLayout('html');
                    $b->setData([
                        'html' => '<h3>' . $escape($heading) . '</h3>',
                    ]);
                }
                $entityManager->persist($b);
                $block->setPosition(++$position);
                $pagesUpdated2[$siteSlug][$pageSlug] = $pageSlug;
            }
            unset($data['heading']);

            $block->setData($data);
            $block->setLayoutData($layoutData);
        }
    }

    $entityManager->flush();

    if ($pagesUpdated) {
        $result = array_map('array_values', $pagesUpdated);
        $message = new PsrMessage(
            'The setting "template" was moved to the new block layout settings available since Omeka S v4.1. You may check pages for styles: {json}', // @translate
            ['json' => json_encode($result, 448)]
        );
        $messenger->addWarning($message);
        $logger->warn($message->getMessage(), $message->getContext());
    }

    if ($pagesUpdated2) {
        $result = array_map('array_values', $pagesUpdated2);
        $message = new PsrMessage(
            'The option "heading" was removed from block Bibliography. New block "Heading" (if module Block Plus is present) or "Html" was prepended to all blocks that had a filled heading. You may check pages for styles: {json}', // @translate
            ['json' => json_encode($result, 448)]
        );
        $messenger->addWarning($message);
        $logger->warn($message->getMessage(), $message->getContext());
    }

    $siteSettings = $services->get('Omeka\Settings\Site');
    $siteIds = $api->search('sites', [], ['returnScalar' => 'id'])->getContent();
    foreach ($siteIds as $siteId) {
        $siteSettings->setTargetId($siteId);
        $siteSettings->set('bibliography_placement_citation', [
            'after/items',
        ]);
    }

    $message = new PsrMessage(
        'A new option in site settings allows to append the bibliographic reference via a resource block.', // @translate
    );
    $messenger->addSuccess($message);
}
