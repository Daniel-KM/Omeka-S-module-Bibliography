<?php
namespace Bibliography\Controller;

use Omeka\Api\Exception\NotFoundException;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class BibliographyController extends AbstractActionController
{
    public function outputAction()
    {
        $params = $this->params();
        $id = $params->fromRoute('id');

        try {
            $resource = $this->api()->read('resources', ['id' => $id])->getContent();
        } catch (NotFoundException $e) {
            throw new \Omeka\Mvc\Exception\NotFoundException();
        }

        $output = $params->fromRoute('output');
        switch ($output) {
            case 'csv':
                return $this->outputCsv($resource, 'csv');
            case 'tsv':
                return $this->outputCsv($resource, 'tsv');
            case 'json':
                return $this->outputJson($resource);
            default:
                throw new \Omeka\Mvc\Exception\RuntimeException(sprintf(
                    $this->translate('Unsupported format: %s'), // @translated
                    $output
                ));
        }
    }

    protected function outputCsv(AbstractResourceEntityRepresentation $resource, $format)
    {
        $filename = $this->outputFilename($resource, $format);
        $mediaType = $format === 'tsv'
            ? 'text/tab-separated-values'
            : 'text/csv';

        $content = $this->convertToCsv($resource, $format);

        $response = $this->getResponse();
        $response->setContent($content);
        /** @var \Zend\Http\Headers $headers */
        $response->getHeaders()
            ->addHeaderLine('Content-Disposition: attachment; filename=' . $filename)
            ->addHeaderLine('Content-type: ' . $mediaType)
            ->addHeaderLine('Content-length: ' . strlen($content))
            ->addHeaderLine('Expires: 0')
            ->addHeaderLine('Pragma: public');
        return $response;
    }

    protected function outputJson(AbstractResourceEntityRepresentation $resource)
    {
        $filename = $this->outputFilename($resource, 'json');

        $this->getResponse()->getHeaders()
            ->addHeaderLine('Content-Disposition: attachment; filename=' . $filename)
            ->addHeaderLine('Expires: 0')
            ->addHeaderLine('Pragma: public');

        $view = new JsonModel($resource->jsonSerialize());
        return $view
            ->setOption('prettyPrint', true)
            ->setTerminal(true);
    }

    protected function outputFilename(AbstractResourceEntityRepresentation $resource, $extension)
    {
        $server = $_SERVER['SERVER_NAME'];
        $resourceNames = [
            'items' => 'item',
            'item_sets' => 'item-set',
            'media' => 'media',
            'annotations' => 'annotation',
        ];
        $resourceName = $resource->resourceName();
        return $server . '-' . $resourceNames[$resourceName] . '-' . $resource->id() . '.' . $extension;
    }

    protected function convertToCsv(AbstractResourceEntityRepresentation $resource, $format)
    {
        switch ($format) {
            case 'tsv':
                $delimiter = "\t";
                $enclosure = chr(0);
                $escape = chr(0);
                break;
            case 'csv':
            default:
                $delimiter = ',';
                $enclosure = '"';
                $escape = '\\';
                break;
        }
        // TODO Adde a check for the separator in the values.
        $separator = ' | ';

        // What to export?

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
                $row[] = implode($separator, array_filter($urls));

                $urls = [];
                /** @var \Omeka\Api\Representation\MediaRepresentation $media*/
                foreach ($resource->media() as $media) {
                    // TODO Manage all types of media.
                    $urls[] = $media->originalUrl();
                }
                $headers[] = 'Media';
                $row[] = implode($separator, array_filter($urls));
                break;

            case 'item_sets':
                /** @var \Omeka\Api\Representation\ItemSetRepresentation @resource */
                // Nothing to do.
                break;

            case 'media':
                /** @var \Omeka\Api\Representation\MediaRepresentation @resource */
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
            $row[] = implode($separator, $values['values']);
        }

        $rows = [
            $headers,
            $row,
        ];

        return $this->csv_put_contents($rows, $delimiter, $enclosure, $escape);
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
