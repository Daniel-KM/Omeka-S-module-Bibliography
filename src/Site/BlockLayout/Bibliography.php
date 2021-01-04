<?php declare(strict_types=1);
namespace Bibliography\Site\BlockLayout;

use Laminas\View\Renderer\PhpRenderer;
use Omeka\Api\Representation\SitePageBlockRepresentation;
use Omeka\Api\Representation\SitePageRepresentation;
use Omeka\Api\Representation\SiteRepresentation;
use Omeka\Entity\SitePageBlock;
use Omeka\Site\BlockLayout\AbstractBlockLayout;
use Omeka\Stdlib\ErrorStore;

class Bibliography extends AbstractBlockLayout
{
    /**
     * The default partial view script.
     */
    const PARTIAL_NAME = 'common/block-layout/bibliography';

    public function getLabel()
    {
        return 'Bibliography'; // @translate
    }

    public function onHydrate(SitePageBlock $block, ErrorStore $errorStore): void
    {
        $data = $block->getData();
        $query = $data['query'] ?? [];
        $data['query'] = is_array($query)
            ? http_build_query($query, "\n", '&', PHP_QUERY_RFC3986)
            : ltrim($query, "? \t\n\r\0\x0B");
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
        $defaultSettings = $services->get('Config')['bibliography']['block_settings']['bibliography'];
        $blockFieldset = \Bibliography\Form\BibliographyBlockFieldset::class;

        $defaultSettings['style'] = $view->siteSetting('bibliography_csl_style') ?: 'chicago-fullnote-bibliography';
        $defaultSettings['locale'] = $view->siteSetting('bibliography_csl_locale') ?: str_replace('_', '-', $view->siteSetting('locale'));

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
        parse_str((string) $block->dataValue('query'), $query);
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

        $vars = [
            'heading' => $block->dataValue('heading'),
            'query' => $originalQuery,
            'resources' => $resources,
            'options' => [
                'style' => $block->dataValue('style'),
                'locale' => $block->dataValue('locale'),
                'append_site' => $block->dataValue('append_site'),
                'append_date' => $block->dataValue('append_date'),
            ],
        ];
        $template = $block->dataValue('template', self::PARTIAL_NAME);
        return $view->resolver($template)
            ? $view->partial($template, $vars)
            : $view->partial(self::PARTIAL_NAME, $vars);
    }
}
