<?php
namespace Bibliography\DataType\Doi;

use Bibliography\Suggester\Doi\DoiSuggest;
use ValueSuggest\DataType\AbstractDataType;

class Doi extends AbstractDataType
{
    const CROSSREF_DOI_API = 'https://api.crossref.org';

    public function getSuggester()
    {
        $resource = 'works';

        /** @var \Zend\Http\Client $client */
        $client = $this->services->get('Omeka\HttpClient');
        $client->setUri(self::CROSSREF_DOI_API . '/' . $resource);
        /** @var \Omeka\Entity\Module $module */
        $module = $this->services->get('Omeka\ModuleManager')->getModule('Bibliography');
        $email = $this->services->get('Omeka\Settings')->get('bibliography_crossref_email');
        $headers = $client->getRequest()->getHeaders();
        $headers
            ->addHeaderLine(
                'User-Agent',
                'Omeka-S-module-Bibliography/'
                    . $module->getIni('version')
                    . ' ('
                    . $module->getIni('module_link')
                    . ($email ? '; mailto:' . $email : '')
                    . ')')
            ->addHeaderLine('Accept', 'application/json')
        ;
        return new DoiSuggest($client);
    }

    public function getName()
    {
        return 'valuesuggest:doi';
    }

    public function getLabel()
    {
        return 'DOI: The digital object identifiers for research'; // @translate
    }
}
