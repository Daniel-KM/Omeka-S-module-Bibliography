<?php declare(strict_types=1);
namespace Bibliography\DataType;

// This check avoids a dependency on module ValueSuggest.
// TODO Find a way to avoid the check of class of the dependency here.
if (!class_exists(\ValueSuggest\DataType\AbstractDataType::class)) {
    require_once __DIR__ . '/AbstractDataType.php';
}

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

    /**
     * Get all property ids by term.
     *
     * @return array Associative array of ids by term.
     */
    protected function getPropertyIds()
    {
        $properties = $this->services->get('Omeka\ApiManager')
            ->search('properties', [], ['responseContent' => 'resource'])->getContent();
        foreach ($properties as $property) {
            $term = $property->getVocabulary()->getPrefix() . ':' . $property->getLocalName();
            $properties[$term] = $property->getId();
        }
        return $properties;
    }

    /**
     * Get all resource class ids by term.
     *
     * @return array Associative array of ids by term.
     */
    protected function getResourceClassIds()
    {
        $classes = $this->services->get('Omeka\ApiManager')
            ->search('resource_classes', [], ['responseContent' => 'resource'])->getContent();
        foreach ($classes as $class) {
            $term = $class->getVocabulary()->getPrefix() . ':' . $class->getLocalName();
            $classes[$term] = $class->getId();
        }
        return $classes;
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
