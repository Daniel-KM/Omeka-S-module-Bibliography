<?php declare(strict_types=1);

namespace Bibliography\Formatter;

use BulkExport\Formatter\AbstractViewFormatter;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;

class Ris extends AbstractViewFormatter
{
    protected $label = 'ris';
    protected $extension = 'ris';
    protected $responseHeaders = [
        'Content-type' => 'application/x-research-info-systems',
    ];
    protected $converterName = 'rdfToRis';

    protected function writeResource(AbstractResourceEntityRepresentation $resource, $index): void
    {
        $conv = $this->converter;
        fwrite($this->handle, $conv($resource) . "\n\n\n");
    }
}
