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
        $id = $this->params()->fromRoute('id');

        try {
            $resource = $this->api()->read('resources', ['id' => $id])->getContent();
        } catch (NotFoundException $e) {
            throw new \Omeka\Mvc\Exception\NotFoundException();
        }

        return $this->outputJson($resource);
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
