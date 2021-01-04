<?php declare(strict_types=1);
namespace Bibliography\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Omeka\Api\Exception\NotFoundException;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;

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
            case 'bib':
            case 'bibtex':
                $content = $this->outputBibtex($resource);
                $filename = $this->outputFilename($resource, 'bib');
                return $this->sendBibtex($content, $filename);
            case 'csv':
                $content = $this->outputCsv($resource, 'csv');
                $filename = $this->outputFilename($resource, 'csv');
                return $this->sendCsv($content, $filename, 'text/csv');
            case 'tsv':
                $content = $this->outputCsv($resource, 'tsv');
                $filename = $this->outputFilename($resource, 'tsv');
                return $this->sendCsv($content, $filename, 'text/tab-separated-values');
            case 'ris':
                $content = $this->outputRis($resource);
                $filename = $this->outputFilename($resource, 'ris');
                return $this->sendRis($content, $filename);
            case 'json':
                $content = $resource->jsonSerialize();
                $filename = $this->outputFilename($resource, 'json');
                return $this->sendJson($content, $filename);
            default:
                throw new \Omeka\Mvc\Exception\RuntimeException(sprintf(
                    $this->translate('Unsupported format: %s'), // @translated
                    $output
                ));
        }
    }

    protected function outputBibtex(AbstractResourceEntityRepresentation $resource)
    {
        $converter = $this->viewHelpers()->get('rdfToBibtex');
        return $converter($resource);
    }

    protected function sendBibtex($content, $filename)
    {
        $response = $this->getResponse();
        $response->setContent($content);
        /* @var \Laminas\Http\Headers $headers */
        $response->getHeaders()
            ->addHeaderLine('Content-Disposition: attachment; filename=' . $filename)
            ->addHeaderLine('Content-type: ' . 'text/plain')
            ->addHeaderLine('Content-length: ' . strlen($content))
            ->addHeaderLine('Expires: 0')
            ->addHeaderLine('Pragma: public');
        return $response;
    }

    protected function outputCsv(AbstractResourceEntityRepresentation $resource, $format)
    {
        switch ($format) {
            case 'tsv':
                $options = [
                    'delimiter' => "\t",
                    'enclosure' => chr(0),
                    'escape' => chr(0),
                    'separator' => ' | ',
                ];
                break;
            case 'csv':
            default:
                $options = [
                    'delimiter' => ',',
                    'enclosure' => '"',
                    'escape' => '\\',
                    'separator' => ' | ',
                ];
                break;
        }

        $converter = $this->viewHelpers()->get('rdfToCsv');
        return $converter($resource, [], $options);
    }

    protected function sendCsv($content, $filename, $mediaType)
    {
        $response = $this->getResponse();
        $response->setContent($content);
        /* @var \Laminas\Http\Headers $headers */
        $response->getHeaders()
            ->addHeaderLine('Content-Disposition: attachment; filename=' . $filename)
            ->addHeaderLine('Content-type: ' . $mediaType)
            ->addHeaderLine('Content-length: ' . strlen($content))
            ->addHeaderLine('Expires: 0')
            ->addHeaderLine('Pragma: public');
        return $response;
    }

    protected function outputRis(AbstractResourceEntityRepresentation $resource)
    {
        $converter = $this->viewHelpers()->get('rdfToRis');
        return $converter($resource);
    }

    protected function sendRis($content, $filename)
    {
        $response = $this->getResponse();
        $response->setContent($content);
        /* @var \Laminas\Http\Headers $headers */
        $response->getHeaders()
            ->addHeaderLine('Content-Disposition: attachment; filename=' . $filename)
            ->addHeaderLine('Content-type: ' . 'application/x-research-info-systems')
            ->addHeaderLine('Content-length: ' . strlen($content))
            ->addHeaderLine('Expires: 0')
            ->addHeaderLine('Pragma: public');
        return $response;
    }

    protected function sendJson($content, $filename)
    {
        $this->getResponse()->getHeaders()
            ->addHeaderLine('Content-Disposition: attachment; filename=' . $filename)
            ->addHeaderLine('Expires: 0')
            ->addHeaderLine('Pragma: public');

        $view = new JsonModel($content);
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
