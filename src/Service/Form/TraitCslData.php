<?php
namespace Bibliography\Service\Form;

/**
 * @todo Create and cache the list of styles and locales with each label.
 */
trait TraitCslData
{
    protected function getCitationStyles()
    {
        static $citationStyles;

        if (is_array($citationStyles)) {
            return $citationStyles;
        }

        $citationStyles = [];
        $dirpath = dirname(dirname(dirname(__DIR__) )). '/vendor/citation-style-language/styles-distribution';
        $directory = new \RecursiveDirectoryIterator($dirpath, \RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directory);
        foreach ($iterator as $filepath => $file) {
            if ($file->getExtension() === 'csl') {
                $name = pathinfo($filepath, PATHINFO_FILENAME);
                $citationStyles[$name] = $name;
            }
        }
        asort($citationStyles);
        return $citationStyles;
    }

    protected function getCitationLocales()
    {
        static $citationLocales;

        if (is_array($citationLocales)) {
            return $citationLocales;
        }

        $citationLocales = [];
        $dirpath = dirname(dirname(dirname(__DIR__) )). '/vendor/citation-style-language/locales';
        $directory = new \RecursiveDirectoryIterator($dirpath, \RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directory);
        foreach ($iterator as $filepath => $file) {
            if ($file->getExtension() === 'xml') {
                $name = substr(pathinfo($filepath, PATHINFO_FILENAME), 8);
                $citationLocales[$name] = $name;
            }
        }
        asort($citationLocales);
        return $citationLocales;
    }
}
