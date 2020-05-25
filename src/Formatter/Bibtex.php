<?php
namespace Bibliography\Formatter;

use BulkExport\Formatter\AbstractFormatter;

class Bibtex extends AbstractFormatter
{
    protected $label = 'bibtex';
    protected $extension = 'bib';
    protected $responseHeaders = [
        'Content-type' => 'text/plain',
    ];

    protected function process()
    {
        $this->initializeOutput();
        if ($this->hasError) {
            return;
        }

        $converter = $this->services->get('ViewHelperManager')->get('rdfToBibtex');

        if ($this->isId) {
            foreach ($this->resourceIds as $resourceId) {
                try {
                    $resource = $this->api->read($this->resourceType, ['id' => $resourceId])->getContent();
                } catch (\Omeka\Api\Exception\NotFoundException $e) {
                    continue;
                }
                fwrite($this->handle, $converter($resource) . "\n\n\n");
            }
        } else {
            foreach ($this->resources as $resource) {
                fwrite($this->handle, $converter($resource) . "\n\n\n");
            }
        }

        $this->finalizeOutput();
    }
}
