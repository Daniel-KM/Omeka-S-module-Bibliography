<?php declare(strict_types=1);

namespace Bibliography\DataType;

// This check avoids a dependency on module ValueSuggest. The autoloader must
// be used: else, when the real class is not loaded yet, the fallback replaces
// it for all the data types of ValueSuggest, that are no more valid.
if (!class_exists('ValueSuggest\DataType\AbstractDataType')) {
    require_once __DIR__ . '/AbstractDataType.php';
}

use Bibliography\Stdlib\StyleSheet;
use Seboettg\CiteProc\CiteProc;
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

        $style = $currentSetting('bibliography_csl_style') ?: 'chicago-author-date';
        try {
            $style = StyleSheet::load($style);
        } catch (\Seboettg\CiteProc\Exception\CiteProcException $e) {
            $style = StyleSheet::load('chicago-author-date');
        }
        $locale = $currentSetting('bibliography_csl_locale') ?: strtr($currentSetting('locale'), ['_' => '-']);
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
