<?php declare(strict_types=1);

namespace Bibliography\Service;

/**
 * @todo Create and cache the list of styles and locales with each label. Or hard save them.
 */
trait TraitCslData
{
    /**
     * @return array
     */
    public function getCitationStyles()
    {
        static $citationStyles;

        if (is_array($citationStyles)) {
            return $citationStyles;
        }

        $citationStyles = [];

        $dirpath = dirname(__DIR__, 2) . '/vendor/citation-style-language/styles';
        /* // TODO Create an autocomplete, the sub-dir is too big.
        $directory = new \RecursiveDirectoryIterator($dirpath, \RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directory);
        foreach ($iterator as $filepath => $file) {
            if ($file->getExtension() === 'csl') {
                $name = pathinfo($filepath, PATHINFO_FILENAME);
                $this->citationStyles[$name] = $name;
            }
        }
        */
        try {
            $iterator = new \DirectoryIterator($dirpath);
        } catch (\Exception $e) {
            return [];
        }
        foreach ($iterator as $file) {
            if ($file->isFile() && !$file->isDot() && $file->isReadable() && $file->getExtension() === 'csl') {
                $name = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                $citationStyles[$name] = $name;
            }
        }

        asort($citationStyles);
        return $citationStyles;
    }

    /**
     * @return array
     */
    public function getCitationLocales()
    {
        static $citationLocales;

        if (is_array($citationLocales)) {
            return $citationLocales;
        }

        $citationLocales = [];

        $dirpath = dirname(__DIR__, 2) . '/vendor/citation-style-language/locales';
        try {
            $directory = new \RecursiveDirectoryIterator($dirpath, \RecursiveDirectoryIterator::SKIP_DOTS);
        } catch (\Exception $e) {
            return [];
        }
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
