<?php declare(strict_types=1);
namespace Bibliography\DataType\Doi;

use Bibliography\DataType\AbstractBibliographyDataType;
use Bibliography\Suggester\Doi\DoiSuggest;

class Doi extends AbstractBibliographyDataType
{
    const API = 'https://api.crossref.org';

    public function getSuggester()
    {
        $viewHelpers = $this->services->get('ViewHelperManager');
        $setting = $viewHelpers->get('setting');

        /** @var \Omeka\Entity\Module $module */
        $module = $this->services->get('Omeka\ModuleManager')->getModule('Bibliography');
        $email = $setting('bibliography_crossref_email');

        /** @var \Laminas\Http\Client $client */
        $client = $this->services->get('Omeka\HttpClient');
        $client->setUri(self::API . '/' . $this->options['resource']);
        $client->getRequest()->getHeaders()
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

        $citeProc = $this->prepareCiteProc();

        if ($this->options['uri_label'] === 'record') {
            $propertyIds = $this->getPropertyIds();
            $resourceClassIds = $this->getResourceClassIds();
        } else {
            $propertyIds = null;
            $resourceClassIds = null;
        }

        return new DoiSuggest($client, $citeProc, $this->options, $propertyIds, $resourceClassIds);
    }
}
