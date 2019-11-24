<?php
namespace Bibliography\DataType\Doi;

use Bibliography\Suggester\Doi\DoiSuggest;
use Seboettg\CiteProc\CiteProc;
use Seboettg\CiteProc\StyleSheet;
use ValueSuggest\DataType\AbstractDataType;

class Doi extends AbstractDataType
{
    const CROSSREF_DOI_API = 'https://api.crossref.org';

    public function getSuggester()
    {
        $resource = 'works';

        $viewHelpers = $this->services->get('ViewHelperManager');
        $setting = $viewHelpers->get('setting');

        /** @var \Omeka\Entity\Module $module */
        $module = $this->services->get('Omeka\ModuleManager')->getModule('Bibliography');
        $email = $setting('bibliography_crossref_email');

        /** @var \Zend\Http\Client $client */
        $client = $this->services->get('Omeka\HttpClient');
        $client->setUri(self::CROSSREF_DOI_API . '/' . $resource);
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
        $citeProc = new CiteProc($style, $locale);

        return new DoiSuggest($client, $citeProc);
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
