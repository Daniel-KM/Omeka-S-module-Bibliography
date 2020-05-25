<?php
namespace Bibliography\Formatter;

use BulkExport\Formatter\AbstractFormatter;

class Ris extends AbstractFormatter
{
    protected $label = 'ris';
    protected $extension = 'ris';
    protected $responseHeaders = [
        'Content-type' => 'application/x-research-info-systems',
    ];

    protected function process()
    {
        $this->initializeOutput();
        if ($this->hasError) {
            return;
        }

        $converter = $this->services->get('ViewHelperManager')->get('rdfToRis');

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
