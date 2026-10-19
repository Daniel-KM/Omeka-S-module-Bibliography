<?php declare(strict_types=1);

namespace Bibliography\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;

class Citation extends AbstractHelper
{
    /**
     * Return a valid citation for this resource.
     *
     * @param AbstractResourceEntityRepresentation $resource
     * @param array $options Managed options are:
     * - style (string)
     * - locale (string)
     * - defaults (array)
     * - bibliographic (bool): remove date and site
     * - append_site (bool)
     * - append_date (bool)
     * - tag (string): "p" (default) or "div" or empty string to skip it.
     * - as_text (bool): the default output is html (italic for title, etc.)
     * - template (string)
     * The option "accessed" is set for the template: the localized access date.
     * The default options are used for Omeka resources. You may unset options
     * to append the site and the access date for real bibliographic resources.
     * Other options are passed to template.
     * @return string
     */
    public function __invoke(AbstractResourceEntityRepresentation $resource, array $options = [])
    {
        $view = $this->getView();

        // Set default options.
        $options += [
            'style' => null,
            'locale' => null,
            'defaults' => [],
            'append_site' => true,
            'append_date' => true,
            'bibliographic' => false,
            'tag' => 'p',
            'as_text' => false,
        ];
        $options['resource'] = $resource;

        if ($options['bibliographic']) {
            $options['append_site'] = false;
            $options['append_date'] = false;
        }

        $currentSite = $this->currentSite();
        $currentSetting = $currentSite
            ? $view->plugin('siteSetting')
            : $view->plugin('setting');
        if ($options['style'] === null) {
            $options['style'] = $currentSetting('bibliography_csl_style') ?: 'chicago-author-date';
        }
        if ($options['locale'] === null) {
            // A site without locale uses the main locale.
            $locale = $currentSetting('bibliography_csl_locale')
                ?: $currentSetting('locale')
                ?: $view->setting('locale');
            $options['locale'] = $locale ? strtr((string) $locale, ['_' => '-']) : 'en-US';
        } elseif (empty($options['locale'])) {
            $options['locale'] = 'en-US';
        }

        $options['accessed'] = $options['append_date']
            ? $this->accessed($options['locale'])
            : null;

        $template = empty($options['template']) ? 'common/citation' : $options['template'];
        unset($options['template']);

        return $view->partial($template, $options);
    }

    /**
     * Get the term "accessed" with the current date in the citation locale.
     *
     * The term and the date format are the ones of the csl locale, so they are
     * localized even without the php extension intl.
     */
    protected function accessed(string $locale): ?string
    {
        $style = <<<'XML'
            <?xml version="1.0" encoding="utf-8"?>
            <style xmlns="http://purl.org/net/xbiblio/csl" class="in-text" version="1.0">
              <info><title>Accessed</title><id>accessed</id><updated>2026-01-01T00:00:00+00:00</updated></info>
              <citation><layout><group delimiter=" "><text term="accessed"/><date variable="accessed" form="text"/></group></layout></citation>
            </style>
            XML;
        $item = (object) [
            'id' => 'accessed',
            'type' => 'webpage',
            'accessed' => (object) ['date-parts' => [array_map('intval', explode('-', date('Y-m-d')))]],
        ];
        try {
            $accessed = (new \Seboettg\CiteProc\CiteProc($style, $locale))->render([$item], 'citation');
        } catch (\Throwable $e) {
            return null;
        }
        return trim(strip_tags((string) $accessed)) ?: null;
    }

    /**
     * Get the current site from the view.
     */
    protected function currentSite(): ?\Omeka\Api\Representation\SiteRepresentation
    {
        return $this->view->site ?? $this->view->site = $this->view
            ->getHelperPluginManager()
            ->get('Laminas\View\Helper\ViewModel')
            ->getRoot()
            ->getVariable('site');
    }
}
