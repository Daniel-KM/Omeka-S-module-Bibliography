<?php
namespace Bibliography\DataType;

use Seboettg\CiteProc\CiteProc;
use Seboettg\CiteProc\StyleSheet;
use ValueSuggest\DataType\AbstractDataType;

abstract class AbstractBibliographyDataType extends AbstractDataType
{
    protected $name;
    protected $label;
    protected $options;

    /**
     * @return \Seboettg\CiteProc\CiteProc
     */
    protected function prepareCiteProc()
    {
        $viewHelpers = $this->services->get('ViewHelperManager');
        $setting = $viewHelpers->get('setting');

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

        return new CiteProc($style, $locale);
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
