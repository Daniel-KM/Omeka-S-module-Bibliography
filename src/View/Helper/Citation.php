<?php
namespace Bibliography\View\Helper;

use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Zend\View\Helper\AbstractHelper;

class Citation extends AbstractHelper
{
    /**
     * Return a valid citation for this resource.
     *
     * @param AbstractResourceEntityRepresentation $resource
     * @param array $options Managed options are: "style", "locale", "append_site",
     * "append_access_date", "tag", and "template".
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
            'append_site' => true,
            'append_access_date' => true,
            'tag' => 'p',
        ];
        $options['resource'] = $resource;

        $siteSetting = $view->plugin('siteSetting');
        if (is_null($options['style'])) {
            $options['style'] = $siteSetting('bibliography_csl_style') ?: 'chicago-fullnote-bibliography';
        }
        if (is_null($options['locale'])) {
            $options['locale'] = $siteSetting('bibliography_csl_locale') ?: str_replace('_', '-', $siteSetting('locale'));
        }

        $template = empty($options['template']) ? 'common/citation' : $options['template'];
        unset($options['template']);

        return $view->partial($template, $options);
    }
}
