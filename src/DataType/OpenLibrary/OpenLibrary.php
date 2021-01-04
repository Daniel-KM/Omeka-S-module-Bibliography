<?php declare(strict_types=1);
namespace Bibliography\DataType\OpenLibrary;

use Bibliography\DataType\AbstractBibliographyDataType;
use Bibliography\Suggester\OpenLibrary\OpenLibrarySuggest;

class OpenLibrary extends AbstractBibliographyDataType
{
    const API = 'https://openlibrary.org/api/books';

    public function getSuggester()
    {
        /** @var \Omeka\Entity\Module $module */
        $module = $this->services->get('Omeka\ModuleManager')->getModule('Bibliography');

        /** @var \Laminas\Http\Client $client */
        $client = $this->services->get('Omeka\HttpClient');
        $client->setUri(self::API);
        $client->getRequest()->getHeaders()
            ->addHeaderLine(
                'User-Agent',
                'Omeka-S-module-Bibliography/'
                    . $module->getIni('version')
                    . ' ('
                    . $module->getIni('module_link')
                    . ')')
            ->addHeaderLine('Accept', 'application/json')
        ;

        $citeProc = $this->prepareCiteProc();

        $propertyIds = $this->options['uri_label'] === 'record'
            ? $this->getPropertyIds()
            : null;

        return new OpenLibrarySuggest($client, $citeProc, $this->options, $propertyIds);
    }
}
