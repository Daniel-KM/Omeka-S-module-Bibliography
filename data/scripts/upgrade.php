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
$helpers = $services->get('ViewHelperManager');
$url = $helpers->get('url');
$api = $plugins->get('api');
$logger = $services->get('Omeka\Logger');
$settings = $services->get('Omeka\Settings');
$translate = $plugins->get('translate');
$translator = $services->get('MvcTranslator');
$connection = $services->get('Omeka\Connection');
$messenger = $plugins->get('messenger');
$siteSettings = $services->get('Omeka\Settings\Site');
$entityManager = $services->get('Omeka\EntityManager');

if (!method_exists($this, 'checkModuleActiveVersion') || !$this->checkModuleActiveVersion('Common', '3.4.94')) {
    $message = new \Omeka\Stdlib\Message(
        $translate('The module %1$s should be upgraded to version %2$s or later.'), // @translate
        'Common', '3.4.94'
    );
    $messenger->addError($message);
    throw new \Omeka\Module\Exception\ModuleCannotInstallException((string) $translate('Missing requirement. Unable to upgrade.')); // @translate
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

if (version_compare($oldVersion, '3.4.11', '<')) {
    if ($settings->get('bibliography_csl_style') === 'chicago-fullnote-bibliography') {
        $settings->set('bibliography_csl_style', 'chicago-author-date');
    }

    $siteIds = $api->search('sites', [], ['returnScalar' => 'id'])->getContent();
    foreach ($siteIds as $siteId) {
        $siteSettings->setTargetId($siteId);
        if ($siteSettings->get('bibliography_csl_style') === 'chicago-fullnote-bibliography') {
            $siteSettings->set('bibliography_csl_style', 'chicago-author-date');
        }
        $v = $siteSettings->get('bibliography_placement_citation', []);
        $siteSettings->set('bibliography_placement_citation', array_diff($v, ['block/items', 'block/media', 'block/item_sets']));
    }

    $sql = <<<'SQL'
        UPDATE site_page_block
        SET data = REPLACE(data, '"style":"chicago-fullnote-bibliography"', '"style":"chicago-author-date"')
        WHERE layout = 'bibliography';
        SQL;
    $connection->executeStatement($sql);

    $message = new PsrMessage(
        'The new default is "chicago-author-date", that manages date better.', // @translate
    );
    $messenger->addSuccess($message);
}

if (version_compare($oldVersion, '3.4.13', '<')) {
    // The values "collected record" stored the whole record as json in the
    // uri. They are now expanded on save, so expand the existing ones too: the
    // empty properties are filled and the value keeps the uri of the record.
    $sql = <<<'SQL'
        SELECT DISTINCT `resource`.`id`, `resource`.`resource_type`
        FROM `value`
        JOIN `resource` ON `resource`.`id` = `value`.`resource_id`
        WHERE `value`.`type` LIKE 'valuesuggest:%:record'
            AND `value`.`uri` LIKE '{%'
        ORDER BY `resource`.`id`
        SQL;
    $resources = $connection->executeQuery($sql)->fetchAllKeyValue();

    // Without module Value Suggest, the values of its data types are invalid,
    // so the resources cannot be updated. They are expanded on next save.
    if ($resources && !$this->isModuleActive('ValueSuggest')) {
        $message = new PsrMessage(
            'The module Value Suggest is not active, so the values "collected record" of these resources were not expanded. Save them once the module is enabled: {resource_ids}.', // @translate
            ['resource_ids' => '#' . implode(', #', array_keys($resources))]
        );
        $messenger->addWarning($message);
        $resources = [];
    }

    $resourceNames = [
        \Omeka\Entity\Item::class => 'items',
        \Omeka\Entity\ItemSet::class => 'item_sets',
        \Omeka\Entity\Media::class => 'media',
    ];
    $expander = new \Bibliography\Stdlib\RecordExpander();
    $updated = [];
    $skipped = [];
    $errors = [];
    foreach ($resources as $id => $resourceType) {
        $resourceName = $resourceNames[$resourceType] ?? null;
        if (!$resourceName) {
            continue;
        }
        try {
            $resource = $api->read($resourceName, $id)->getContent();
            $data = json_decode(json_encode($resource), true);
            $result = $expander->expand($data);
            if ($result['data'] === $data) {
                continue;
            }
            // Use a partial update to keep the other data (media, item sets,
            // etc.), but pass all the values, since a partial update with
            // values replaces all of them.
            $update = array_filter(
                $result['data'],
                fn ($term) => strpos((string) $term, ':') && strpos((string) $term, 'o:') !== 0,
                ARRAY_FILTER_USE_KEY
            );
            if (($result['data']['o:resource_class'] ?? null) !== ($data['o:resource_class'] ?? null)) {
                $update['o:resource_class'] = $result['data']['o:resource_class'];
            }
            $api->update($resourceName, $id, $update, [], ['isPartial' => true, 'collectionAction' => 'replace']);
            $updated[] = $id;
            if ($result['skipped']) {
                $skipped[] = '#' . $id . ' (' . implode(', ', $result['skipped']) . ')';
            }
        } catch (\Throwable $e) {
            $errors[] = '#' . $id;
            $logger->err(
                'Bibliography: the collected record of resource #{resource_id} cannot be expanded: {message}', // @translate
                ['resource_id' => $id, 'message' => $e->getMessage()]
            );
        }
    }

    if ($updated) {
        $message = new PsrMessage(
            'The values "collected record" were expanded into the empty properties of these resources: {resource_ids}.', // @translate
            ['resource_ids' => '#' . implode(', #', $updated)]
        );
        $messenger->addSuccess($message);
    }
    if ($skipped) {
        $message = new PsrMessage(
            'The bibliographic record was not used for these properties, that were already filled: {properties}.', // @translate
            ['properties' => implode('; ', $skipped)]
        );
        $messenger->addWarning($message);
    }
    if ($errors) {
        $message = new PsrMessage(
            'The values "collected record" of these resources cannot be expanded (see logs): {resource_ids}.', // @translate
            ['resource_ids' => implode(', ', $errors)]
        );
        $messenger->addError($message);
    }
}
