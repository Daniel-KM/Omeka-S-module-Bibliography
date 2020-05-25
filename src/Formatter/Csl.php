<?php
namespace Bibliography\Formatter;

use BulkExport\Formatter\AbstractFormatter;

class Csl extends AbstractFormatter
{
    protected $label = 'csl';
    protected $extension = 'csl';
    protected $responseHeaders = [
        'Content-type' => 'text/plain',
    ];

    protected function process()
    {
        $this->initializeOutput();
        if ($this->hasError) {
            return;
        }

        $converter = $this->services->get('ViewHelperManager')->get('rdfToCsl');

        if ($this->isId) {
            foreach ($this->resourceIds as $resourceId) {
                try {
                    $resource = $this->api->read($this->resourceType, ['id' => $resourceId])->getContent();
                } catch (\Omeka\Api\Exception\NotFoundException $e) {
                    continue;
                }
                $csl = json_encode($converter($resource));
                fwrite($this->handle, $csl . "\n\n\n");
            }
        } else {
            foreach ($this->resources as $resource) {
                $csl = json_encode($converter($resource));
                fwrite($this->handle, $csl . "\n\n\n");
            }
        }

        $this->finalizeOutput();
    }
}
