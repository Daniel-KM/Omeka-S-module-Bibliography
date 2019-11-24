<?php
namespace Bibliography\DataType\Doi;

use Bibliography\Suggester\Doi\DoiSuggest;
use Seboettg\CiteProc\CiteProc;
use Seboettg\CiteProc\StyleSheet;
use ValueSuggest\DataType\AbstractDataType;

class Doi extends AbstractDataType
{
    const CROSSREF_DOI_API = 'https://api.crossref.org';

    protected $doiName;
    protected $doiLabel;
    protected $doiResource;
    protected $doiIdentifier;

    public function getSuggester()
    {
        $resource = $this->doiResource ?: 'works';

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

        return new DoiSuggest($client, $citeProc, $this->doiResource, $this->doiIdentifier);
    }

    public function setDoiName($doiName)
    {
        $this->doiName = $doiName;
        return $this;
    }

    public function setDoiLabel($doiLabel)
    {
        $this->doiLabel = $doiLabel;
        return $this;
    }

    public function setDoiResource($doiResource)
    {
        $this->doiResource = $doiResource;
        return $this;
    }

    public function setDoiIdentifier($doiIdentifier)
    {
        $this->doiIdentifier = (bool) $doiIdentifier;
        return $this;
    }

    public function getName()
    {
        return $this->doiName;
    }

    public function getLabel()
    {
        return $this->doiLabel;
    }
}
