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
            'identifier' => false,
        ],
        'valuesuggest:doi:works:id' => [
            'label' => 'DOI: Single work', // @translate
            'resource' => 'works',
            'identifier' => true,
        ],
        'valuesuggest:doi:journals' => [
            'label' => 'DOI: Journals', // @translate
            'resource' => 'journals',
            'identifier' => false,
        ],
        'valuesuggest:doi:journals:id' => [
            'label' => 'DOI: Single journal', // @translate
            'resource' => 'journals',
            'identifier' => true,
        ],
        'valuesuggest:doi:funders' => [
            'label' => 'DOI: Funders', // @translate
            'resource' => 'funders',
            'identifier' => false,
        ],
        'valuesuggest:doi:funders:id' => [
            'label' => 'DOI: Single funders', // @translate
            'resource' => 'funders',
            'identifier' => true,
        ],
        'valuesuggest:doi:members' => [
            'label' => 'DOI: Members', // @translate
            'resource' => 'members',
            'identifier' => false,
        ],
        'valuesuggest:doi:members:id' => [
            'label' => 'DOI: Single member', // @translate
            'resource' => 'members',
            'identifier' => true,
        ],
        'valuesuggest:doi:licenses' => [
            'label' => 'DOI: Licenses', // @translate
            'resource' => 'licenses',
            'identifier' => false,
        ],
        'valuesuggest:doi:licenses:id' => [
            'label' => 'DOI: Single license', // @translate
            'resource' => 'licenses',
            'identifier' => true,
        ],
        'valuesuggest:doi:types' => [
            'label' => 'DOI: Types', // @translate
            'resource' => 'types',
            'identifier' => false,
        ],
    ];

    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $dataType = new Doi($services);
        return $dataType
            ->setDoiName($requestedName)
            ->setDoiLabel($this->types[$requestedName]['label'])
            ->setDoiResource($this->types[$requestedName]['resource'])
            ->setDoiIdentifier($this->types[$requestedName]['identifier'])
        ;
    }
}
