<?php
namespace Bibliography\Service;

/**
 * @todo Create and cache the list of styles and locales with each label. Or hard save them.
 */
trait TraitCslData
{
    /**
     * @var array
     */
    protected $citationStyles;

    /**
     * @var array
     */
    protected $citationLocales;

    /**
     * @param array $citationStyles
     * @return self
     */
    public function setCitationStyles(array $citationStyles)
    {
        $this->citationStyles = $citationStyles;
        return $this;
    }

    /**
     * @return array
     */
    public function getCitationStyles()
    {
        if (is_array($this->citationStyles)) {
            return $this->citationStyles;
        }

        $this->citationStyles = [];
        $dirpath = dirname(dirname(__DIR__)). '/vendor/citation-style-language/styles-distribution';
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
        $iterator = new \DirectoryIterator($dirpath);
        foreach ($iterator as $file) {
            if ($file->isFile() && !$file->isDot() && $file->isReadable() && $file->getExtension() === 'csl') {
                $name = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                $this->citationStyles[$name] = $name;
            }
        }

        asort($this->citationStyles);
        return $this->citationStyles;
    }

    /**
     * @param array $citationLocales
     * @return self
     */
    public function setCitationLocales(array $citationLocales)
    {
        $this->citationLocales = $citationLocales;
        return $this;
    }

    /**
     * @return array
     */
    public function getCitationLocales()
    {
        if (is_array($this->citationLocales)) {
            return $this->citationLocales;
        }

        $this->citationLocales = [];
        $dirpath = dirname(dirname(__DIR__)). '/vendor/citation-style-language/locales';
        $directory = new \RecursiveDirectoryIterator($dirpath, \RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directory);
        foreach ($iterator as $filepath => $file) {
            if ($file->getExtension() === 'xml') {
                $name = substr(pathinfo($filepath, PATHINFO_FILENAME), 8);
                $this->citationLocales[$name] = $name;
            }
        }
        asort($this->citationLocales);
        return $this->citationLocales;
    }
}
