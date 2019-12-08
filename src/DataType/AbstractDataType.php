<?php
namespace ValueSuggest\DataType;

use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Representation\ValueRepresentation;
use Omeka\DataType\AbstractDataType as BaseAbstractDataType;
use Omeka\Entity\Value;
use Zend\View\Renderer\PhpRenderer;

abstract class AbstractDataType extends BaseAbstractDataType
{
    public function getOptgroupLabel()
    {
        return 'Value Suggest'; // @translate
    }

    public function form(PhpRenderer $view)
    {
        return null;
    }

    public function isValid(array $valueObject)
    {
        return false;
    }

    public function hydrate(array $valueObject, Value $value, AbstractEntityAdapter $adapter)
    {
    }

    public function render(PhpRenderer $view, ValueRepresentation $value)
    {
        return '';
    }

    public function getJsonLd(ValueRepresentation $value)
    {
        return null;
    }
}
