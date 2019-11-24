<?php
namespace Bibliography\Service\Form;

use Bibliography\Form\BibliographyBlockFieldset;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class BibliographyBlockFieldsetFactory implements FactoryInterface
{
    use TraitCslData;

    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $fieldset = new BibliographyBlockFieldset(null, $options);
        return $fieldset
            ->setCitationStyles($this->getCitationStyles())
            ->setCitationLocales($this->getCitationLocales());
    }
}
