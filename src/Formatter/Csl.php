<?php declare(strict_types=1);

namespace Bibliography\Formatter;

use BulkExport\Formatter\AbstractViewFormatter;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;

class Csl extends AbstractViewFormatter
{
    protected $label = 'csl';
    protected $extension = 'csl';
    protected $responseHeaders = [
        'Content-type' => 'text/plain',
    ];
    protected $converterName = 'rdfToCsl';

    protected function writeResource(AbstractResourceEntityRepresentation $resource, $index): void
    {
        $citation = $this->services->get('ViewHelperManager')->get('citation');
        fwrite($this->handle, $citation($resource, ['style' => 'csl']) . "\n\n\n");
    }
}
