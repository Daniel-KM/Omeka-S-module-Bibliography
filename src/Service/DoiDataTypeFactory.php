<?php
namespace Bibliography\Service;

use Bibliography\DataType\Doi\Doi;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class DoiDataTypeFactory implements FactoryInterface
{
    protected $types = [
        'valuesuggest:doi:works' => [
            'label' => 'DOI: Works', // @translate
            'resource' => 'works',
        ],
        'valuesuggest:doi:journals' => [
            'label' => 'DOI: Journals', // @translate
            'resource' => 'journals',
        ],
        'valuesuggest:doi:funders' => [
            'label' => 'DOI: Funders', // @translate
            'resource' => 'funders',
        ],
        'valuesuggest:doi:members' => [
            'label' => 'DOI: Members', // @translate
            'resource' => 'members',
        ],
        'valuesuggest:doi:licenses' => [
            'label' => 'DOI: Licenses', // @translate
            'resource' => 'licenses',
        ],
        'valuesuggest:doi:types' => [
            'label' => 'DOI: Types', // @translate
            'resource' => 'types',
        ],
    ];

    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $dataType = new Doi($services);
        return $dataType
            ->setDoiName($requestedName)
            ->setDoiLabel($this->types[$requestedName]['label'])
            ->setDoiResource($this->types[$requestedName]['resource'])
        ;
    }
}
