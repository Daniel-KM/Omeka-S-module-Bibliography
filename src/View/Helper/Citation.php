<?php
namespace Citation\View\Helper;

use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Zend\View\Helper\AbstractHelper;

class Citation extends AbstractHelper
{
    /**
     * Return a valid citation for this item.
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
     * @param array $options Managed options: "format", "site", "accessed", "bibliographic".
     * The default options are used for Omeka resources. So use "bibliographic" for
     * a real bibliographic resource. Only Chicago is supported currently.
     * @return string
     */
    public function __invoke(AbstractResourceEntityRepresentation $resource, array $options = [])
    {
        $citation = '';
        $view = $this->getView();
        $translate = $view->plugin('translate');

        if (empty($options['bibliographic'])) {
            $options += [
                'bibliographic' => false,
                'site' => true,
                'accessed' => true,
            ];
        } else {
            $options += [
                'site' => false,
                'accessed' => false,
            ];
        }

        $creators = $resource->value('dcterms:creator', ['all' => true]) ?: [];
        // Strip formatting and remove empty creator elements.
        $creators = array_filter(array_map('strip_tags', $creators));
        if ($creators) {
            switch (count($creators)) {
                case 1:
                    $creator = $creators[0];
                    break;
                case 2:
                    /// Chicago-style item citation: two authors
                    $creator = sprintf($translate('%1$s and %2$s'), $creators[0], $creators[1]);
                    break;
                case 3:
                    /// Chicago-style item citation: three authors
                    $creator = sprintf($translate('%1$s, %2$s, and %3$s'), $creators[0], $creators[1], $creators[2]);
                    break;
                default:
                    /// Chicago-style item citation: more than three authors
                    $creator = sprintf($translate('%s et al.'), $creators[0]);
                    break;
            }
            $citation .= $creator;
        }

        $title = $resource->displayTitle();
        if ($title) {
            $citation .= ($citation ? ', ' : '') . '“' . $title . '”';
        }

        if ($options['bibliographic']) {
            $publisher = $resource->value('dcterms:publisher');
            if ($publisher) {
                $citation .= ', <i>' . $publisher . '</i>';
            }
            $date = $resource->value('dcterms:date');
            if ($date) {
                $citation .= ', ' . $date;
            }
        } else {
            if ($options['site']) {
                $site = $view->currentSite();
                if ($site) {
                    $citation .= ', <i>' . $site->title() . '</i>';
                }
            }

            if ($options['accessed']) {
                // TODO Use the locale for the citation.
                $accessed = (new \DateTime())->format('j F Y');
                $url = '<span class="citation-url">' . $view->escapeHtml($resource->siteUrl(null, true)) . '</span>';
                /// Chicago-style item citation: access date and URL
                $citation .= ', ' . sprintf($translate('accessed %1$s, %2$s'), $accessed, $url);
            }
        }

        $citation .= '.';

        return $citation;
    }
}
