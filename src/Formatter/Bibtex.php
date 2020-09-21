<?php

namespace Bibliography\Formatter;

use BulkExport\Formatter\AbstractViewFormatter;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;

class Bibtex extends AbstractViewFormatter
{
    protected $label = 'bibtex';
    protected $extension = 'bib';
    protected $responseHeaders = [
        'Content-type' => 'text/plain',
    ];
    protected $converterName = 'rdfToBibtex';

    protected function writeResource(AbstractResourceEntityRepresentation $resource, $index)
    {
        $conv = $this->converter;
        fwrite($this->handle, $conv($resource) . "\n\n\n");
    }
}
