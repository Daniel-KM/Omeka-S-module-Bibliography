<?php
namespace Citation\Site\BlockLayout;

use Omeka\Api\Representation\SitePageBlockRepresentation;
use Omeka\Api\Representation\SitePageRepresentation;
use Omeka\Api\Representation\SiteRepresentation;
use Omeka\Entity\SitePageBlock;
use Omeka\Site\BlockLayout\AbstractBlockLayout;
use Omeka\Stdlib\ErrorStore;
use Zend\View\Renderer\PhpRenderer;

class Bibliography extends AbstractBlockLayout
{
    public function getLabel()
    {
        return 'Bibliography'; // @translate
    }

    public function onHydrate(SitePageBlock $block, ErrorStore $errorStore)
    {
        $data = $block->getData();
        $data['query'] = ltrim($data['query'], '? ');
        $block->setData($data);
    }

    public function form(
        PhpRenderer $view,
        SiteRepresentation $site,
        SitePageRepresentation $page = null,
        SitePageBlockRepresentation $block = null
    ) {
        // Factory is not used to make rendering simpler.
        $services = $site->getServiceLocator();
        $formElementManager = $services->get('FormElementManager');
        $defaultSettings = $services->get('Config')['citation']['block_settings']['bibliography'];
        $blockFieldset = \Citation\Form\BibliographyBlockFieldset::class;

        $data = $block ? $block->data() + $defaultSettings : $defaultSettings;

        $dataForm = [];
        foreach ($data as $key => $value) {
            $dataForm['o:block[__blockIndex__][o:data][' . $key . ']'] = $value;
        }

        $fieldset = $formElementManager->get($blockFieldset);
        $fieldset->populateValues($dataForm);

        return $view->formCollection($fieldset);
    }

    public function render(PhpRenderer $view, SitePageBlockRepresentation $block)
    {
        $query = [];
        parse_str($block->dataValue('query'), $query);
        $originalQuery = $query;

        $site = $block->page()->site();
        if ($view->siteSetting('browse_attached_items', false)) {
            $query['site_attachments_only'] = true;
        }

        $query['site_id'] = $site->id();
        $query['limit'] = $block->dataValue('limit', 12);

        if (!isset($query['sort_by'])) {
            $query['sort_by'] = 'created';
        }
        if (!isset($query['sort_order'])) {
            $query['sort_order'] = 'desc';
        }

        $response = $view->api()->search('items', $query);
        $resources = $response->getContent();

        $partial = $block->dataValue('partial') ?: 'common/block-layout/bibliography';

        return $view->partial($partial, [
            'heading' => $block->dataValue('heading'),
            'query' => $originalQuery,
            'resources' => $resources,
            'options' => [
                'format' => $block->dataValue('format', 'Chicago'),
                'append_site' => $block->dataValue('append_site'),
                'append_access_date' => $block->dataValue('append_access_date'),
                'bibliographic' => $block->dataValue('bibliographic'),
            ],
        ]);
    }
}
