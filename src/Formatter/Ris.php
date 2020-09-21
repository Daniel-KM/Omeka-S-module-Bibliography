<?php

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

    protected function writeResource(AbstractResourceEntityRepresentation $resource, $index)
    {
        $conv = $this->converter;
        fwrite($this->handle, $conv($resource) . "\n\n\n");
    }
}
