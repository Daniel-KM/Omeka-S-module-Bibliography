<?php declare(strict_types=1);
namespace Bibliography\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;

/**
 * @deprecated Use BulkExport instead.
 */
class RdfToCsv extends AbstractHelper
{
    /**
     * @var AbstractResourceEntityRepresentation
     */
    protected $resource;

    /**
     * @var array
     */
    protected $defaults;

    /**
     * @var array
     */
    protected $params;

    /**
     * Convert a resource into the format csv.
     *
     * @param AbstractResourceEntityRepresentation $resource
     * @param array $defaults Not used currently. See RdfToCsl.
     * @param array $params
     * @return string
     */
    public function __invoke(
        AbstractResourceEntityRepresentation $resource,
        array $defaults = [],
        array $params = ['delimiter' => ',', 'enclosure' => '"', 'escape' => '\\', 'separator' => ' | ']
    ) {
        $this->resource = $resource;
        $this->defaults = $defaults;
        $this->params = $params + ['delimiter' => ',', 'enclosure' => '"', 'escape' => '\\', 'separator' => ' | '];

        // TODO Add a check for the separator in the values.

        $headers = [];
        $headers[] = 'id';
        $headers[] = 'url';
        $headers[] = 'Resource class';

        $row = [];
        $row[] = $resource->id();
        $row[] = $resource->siteUrl(null, true);
        $resourceClass = $resource->resourceClass();
        $row[] = $resourceClass ? $resourceClass->term() : '';

        $resourceName = $resource->resourceName();
        switch ($resourceName) {
            case 'items':
                /** @var \Omeka\Api\Representation\ItemRepresentation @resource */
                $urls = [];
                foreach ($resource->itemSets() as $itemSet) {
                    $urls[] = $itemSet->displayTitle();
                }
                $headers[] = 'Item sets';
                $row[] = implode($this->params['separator'], array_filter($urls));

                $urls = [];
                /** @var \Omeka\Api\Representation\MediaRepresentation $media*/
                foreach ($resource->media() as $media) {
                    // TODO Manage all types of media.
                    $urls[] = $media->originalUrl();
                }
                $headers[] = 'Media';
                $row[] = implode($this->params['separator'], array_filter($urls));
                break;

            case 'item_sets':
                /* @var \Omeka\Api\Representation\ItemSetRepresentation @resource */
                // Nothing to do.
                break;

            case 'media':
                /* @var \Omeka\Api\Representation\MediaRepresentation @resource */
                $headers[] = 'Item';
                $headers[] = 'Media type';
                $headers[] = 'Size';
                $headers[] = 'Url';
                $row[] = $resource->item()->siteUrl();
                $row[] = $resource->mediaType();
                $row[] = $resource->size();
                $row[] = $resource->originalUrl();
                break;

            default:
                break;
        }

        foreach ($resource->values() as $term => $values) {
            $headers[] = $term;
            $row[] = implode($this->params['separator'], $values['values']);
        }

        return $this->csv_put_contents(
            [
                $headers,
                $row,
            ],
            $this->params['delimiter'],
            $this->params['enclosure'],
            $this->params['escape']
        );
    }

    private function csv_put_contents(array $rows, $delimiter = ',', $enclosure = '"', $escape = '\\')
    {
        $output = '';
        foreach ($rows as $row) {
            $output .= $this->str_putcsv($row, $delimiter, $enclosure, $escape);
        }
        return $output;
    }

    private function str_putcsv(array $fields, $delimiter = ',', $enclosure = '"', $escape = '\\')
    {
        $stream = fopen('php://temp', 'w+');
        fputcsv($stream, $fields, $delimiter, $enclosure, $escape);
        rewind($stream);
        return stream_get_contents($stream);
    }
}
