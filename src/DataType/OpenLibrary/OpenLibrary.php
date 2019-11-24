<?php
namespace Bibliography\DataType\OpenLibrary;

use Bibliography\Suggester\OpenLibrary\OpenLibrarySuggest;
use Seboettg\CiteProc\CiteProc;
use Seboettg\CiteProc\StyleSheet;
use ValueSuggest\DataType\AbstractDataType;

class OpenLibrary extends AbstractDataType
{
    const API = 'https://openlibrary.org/api/books';

    protected $name;
    protected $label;
    protected $resource;
    protected $identifier;

    public function getSuggester()
    {
        $viewHelpers = $this->services->get('ViewHelperManager');
        $setting = $viewHelpers->get('setting');

        /** @var \Omeka\Entity\Module $module */
        $module = $this->services->get('Omeka\ModuleManager')->getModule('Bibliography');

        /** @var \Zend\Http\Client $client */
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

        $currentSite = $this->services->get('ControllerPluginManager')->get('currentSite');
        $currentSite = $currentSite();
        $currentSetting = $currentSite
            ? $viewHelpers->get('siteSetting')
            : $setting;

        $style = $currentSetting('bibliography_csl_style') ?: 'chicago-fullnote-bibliography';
        try {
            $style = @StyleSheet::loadStyleSheet($style);
        } catch (\Seboettg\CiteProc\Exception\CiteProcException $e) {
            $style = StyleSheet::loadStyleSheet('chicago-fullnote-bibliography');
        }
        $locale = $currentSetting('bibliography_csl_locale') ?: str_replace('_', '-', $currentSetting('locale'));
        $citeProc = new CiteProc($style, $locale);

        return new OpenLibrarySuggest($client, $citeProc, $this->resource, $this->identifier);
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    public function setResource($resource)
    {
        $this->resource = $resource;
        return $this;
    }

    public function setIdentifier($identifier)
    {
        $this->identifier = (bool) $identifier;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getLabel()
    {
        return $this->label;
    }
}
