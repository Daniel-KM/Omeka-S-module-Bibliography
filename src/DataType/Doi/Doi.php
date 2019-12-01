<?php
namespace Bibliography\DataType\Doi;

use Bibliography\Suggester\Doi\DoiSuggest;
use Seboettg\CiteProc\CiteProc;
use Seboettg\CiteProc\StyleSheet;
use ValueSuggest\DataType\AbstractDataType;

class Doi extends AbstractDataType
{
    const API = 'https://api.crossref.org';

    protected $name;
    protected $label;
    protected $options;

    public function getSuggester()
    {
        $viewHelpers = $this->services->get('ViewHelperManager');
        $setting = $viewHelpers->get('setting');

        /** @var \Omeka\Entity\Module $module */
        $module = $this->services->get('Omeka\ModuleManager')->getModule('Bibliography');
        $email = $setting('bibliography_crossref_email');

        /** @var \Zend\Http\Client $client */
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
        // A default locale is currently required by CiteProc.
        $locale = $locale ?: 'en-US';
        $citeProc = new CiteProc($style, $locale);

        return new DoiSuggest($client, $citeProc, $this->options);
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

    public function setOptions(array $options)
    {
        $this->options = $options;
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
