<?php declare(strict_types=1);

namespace Bibliography\Site\ResourcePageBlockLayout;

use Laminas\View\Renderer\PhpRenderer;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Site\ResourcePageBlockLayout\ResourcePageBlockLayoutInterface;

class CitationButton implements ResourcePageBlockLayoutInterface
{
    public function getLabel() : string
    {
        return 'Bibliography: Citation (button)'; // @translate
    }

    public function getCompatibleResourceNames() : array
    {
        return [
            'items',
        ];
    }

    public function render(PhpRenderer $view, AbstractResourceEntityRepresentation $resource) : string
    {
        return $view->partial('common/resource-page-block-layout/citation-button', [
            'resource' => $resource,
        ]);
    }
}
