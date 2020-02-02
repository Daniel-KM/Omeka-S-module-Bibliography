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
        switch ($format) {
            case 'tsv':
                $mediaType = 'text/tab-separated-values';
                $params = [
                    'delimiter' => "\t",
                    'enclosure' => chr(0),
                    'escape' => chr(0),
                    'separator' => ' | ',
                ];
                break;
            case 'csv':
            default:
                $mediaType = 'text/csv';
                $params = [
                    'delimiter' => ',',
                    'enclosure' => '"',
                    'escape' => '\\',
                    'separator' => ' | ',
                ];
                break;
        }

        $converter = $this->viewHelpers()->get('rdfToCsv');
        $content = $converter($resource, [], $params);

        $filename = $this->outputFilename($resource, $format);

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
}
