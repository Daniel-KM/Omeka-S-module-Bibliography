<?php
namespace Bibliography\View\Helper;

use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Zend\View\Helper\AbstractHelper;

class Citation extends AbstractHelper
{
    /**
     * Return a valid citation for this resource.
     *
     * Generally follows Chicago Manual of Style note format for webpages.
     * Implementers can use the item_citation filter to return a customized
     * citation.
     *
     * Upgrade of Omeka Classic Item::getCitation().
     *
     * @todo Find a full php library to manage citation. No event is triggered currently.
     *
     * @param AbstractResourceEntityRepresentation $resource
     * @param array $options Managed options are: "format", "append_site",
     * "append_access_date", "bibliographic", "tag", and "template".
     * The default options are used for Omeka resources. So use "bibliographic"
     * for a real bibliographic resource. Only Chicago is supported currently.
     * Other options are passed to the partial.
     * @return string
     */
    public function __invoke(AbstractResourceEntityRepresentation $resource, array $options = [])
    {
        $view = $this->getView();

        if (empty($options['bibliographic'])) {
            $options += [
                'format' => null,
                'append_site' => true,
                'append_access_date' => true,
                'bibliographic' => false,
                'tag' => 'p',
            ];
        } else {
            $options += [
                'format' => null,
                'append_site' => false,
                'append_access_date' => false,
                'tag' => 'p',
            ];
        }
        $options['resource'] = $resource;

        $template = empty($options['template']) ? 'common/citation' : $options['template'];
        unset($options['template']);

        return $view->partial($template, $options);
    }
}
