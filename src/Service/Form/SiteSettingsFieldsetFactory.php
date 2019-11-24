<?php
namespace Bibliography\Service\Form;

use Bibliography\Form\SiteSettingsFieldset;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class SiteSettingsFieldsetFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        // TODO Create and cache the list of styles and locales with each label.

        $citationStyles = [];
        $directory = new \RecursiveDirectoryIterator(dirname(dirname(dirname(__DIR__) )). '/vendor/citation-style-language/styles-distribution', \RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directory);
        foreach ($iterator as $filepath => $file) {
            $extension = pathinfo($filepath, PATHINFO_EXTENSION);
            if ($extension === 'csl') {
                $name = pathinfo($filepath, PATHINFO_FILENAME);

                $citationStyles[$name] = $name;
            }
        }

        $citationLocales = [];
        $directory = new \RecursiveDirectoryIterator(dirname(dirname(dirname(__DIR__) )). '/vendor/citation-style-language/locales', \RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directory);
        foreach ($iterator as $filepath => $file) {
            $extension = pathinfo($filepath, PATHINFO_EXTENSION);
            if ($extension === 'xml') {
                $name = substr(pathinfo($filepath, PATHINFO_FILENAME), 8);
                $citationLocales[$name] = $name;
            }
        }

        $fieldset = new SiteSettingsFieldset(null, $options);
        return $fieldset
            ->setCitationStyles($citationStyles)
            ->setCitationLocales($citationLocales);
    }
}
