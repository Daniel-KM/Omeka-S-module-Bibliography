<?php
namespace Bibliography\View\Helper;

use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Zend\View\Helper\AbstractHelper;

class ConvertIntoCsl extends AbstractHelper
{
    /**
     * Convert a resource into the format csl.
     *
     * @see https://citationstyles.org
     * @todo Improve conversion from dcterms and bibo into csl, or find a library for it.
     * @see https://docs.citationstyles.org/en/1.0.1/index.html
     *
     * @param AbstractResourceEntityRepresentation $resource
     * @return \stdClass
     */
    public function __invoke(AbstractResourceEntityRepresentation $resource)
    {
        $csl = [];

        // Some mappings are useless for a standard bibliographic reference, but
        // formats are very numerous, so try to map as many fields as possible.

        // An id is required internally.
        $types = [
            'items' => 'item',
            'item_sets' => 'itemset',
            'media' => 'media',
            'annotations' => 'annotation',
        ];
        $csl['id'] = $types[$resource->resourceName()] . '-' . $resource->id();

        $csl['type'] = $this->cslType($resource);

        $csl['author'] = $this->cslAuthors($resource);

        // TODO Use displayTitle() ?
        $csl['title'] = $resource->value('dcterms:title') ?: $resource->value('bibo:shortTitle');

        // TODO Check FOAF and related items for the publisher.
        $csl['publisher'] = $resource->value('dcterms:publisher');
        // $csl['publisher-place'] = $resource->value('');

        // TODO Check the format of the date if module NumericDataType is installed.
        $csl['created'] = $this->cslDate($resource, 'dcterms:created');
        $csl['deposited'] = $this->cslDate($resource, 'dcterms:dateSubmitted');
        $csl['approved'] = $this->cslDate($resource, 'dcterms:dateAccepted');
        $csl['issued'] = $this->cslDate($resource, 'dcterms:issued') ?: $this->cslDate($resource, 'dcterms:date');
        // $csl['published-print'] = $this->cslDate($resource, 'dcterms:issued') ?: $this->cslDate($resource, 'dcterms:date');

        $csl['edition'] = $resource->value('bibo:edition');

        $csl['DOI'] = $resource->value('bibo:doi');
        $issn = $resource->value('bibo:issn', ['all' => true]);
        if ($issn) {
            $csl['ISSN'] = count($issn) > 1 ? $issn : reset($issn);
        }
        $csl['EISSN'] = $resource->value('bibo:eissn');
        $isbn = [];
        $isbn += $resource->value('bibo:isbn13', ['all' => true, 'default' => []]);
        $isbn += $resource->value('bibo:isbn10', ['all' => true, 'default' => []]);
        $isbn += $resource->value('bibo:isbn', ['all' => true, 'default' => []]);
        $isbn = array_filter($isbn);
        if ($isbn) {
            $csl['ISBN'] = count($isbn) > 1 ? $isbn : reset($isbn);
        }
        $csl['URL'] = $resource->value('bibo:uri') ?: (empty($csl['DOI']) ? null : 'https://dx.doi.org/' . urlencode($csl['DOI']));
        $csl['volume'] = $resource->value('bibo:volume');
        $csl['number-of-volumes'] = $resource->value('bibo:numVolumes');
        $csl['issue'] = $resource->value('bibo:issue');

        $pages = $resource->value('bibo:pages');
        $pageStart = $resource->value('bibo:pageStart');
        $pageEnd = $resource->value('bibo:pageEnd');
        if ($pages) {
            $csl['page'] = $pages;
        } else {
            $csl['page'] = $pageStart . (strlen($pageStart) && strlen($pageEnd) ? '-' : '') . $pageEnd;
        }
        if (strlen($pageStart)) {
            $csl['page-first'] = $pageStart;
        }
        if (strlen($pageEnd)) {
            $csl['page-last'] = $pageEnd;
        }
        $csl['number-of-pages'] = $resource->value('bibo:numPages');

        $csl['abstract'] = $resource->value('bibo:shortDescription')
            ?: ($resource->value('bibo:abstract')
                ?: ($resource->value('dcterms:abstract') ?: $resource->value('dcterms:description')));

        $subjects = $resource->value('dcterms:subject', ['all' => true]) ?: [];
        $subjects = $this->stripTags($subjects);
        $csl['subject'] = $subjects;

        $csl['medium'] = $resource->value('dcterms:medium');

        // TODO Check related items for the journal or conference (upper item).
        // The journal (for an article) or the book (for a part) is not directly
        // available, but required. It should be referenced as dcterms:relation
        // or dcterms:isPartOf.
        $container = $resource->value('dcterms:relation') ?: $resource->value('dcterms:isPartOf');
        if ($container) {
            if (strpos($container->type(), 'resource') === 0) {
                $containerResource = $container->valueResource();
                // $csl['collection-title'] = $resource->value('');
                // $csl['collection-title-short'] = $resource->value('');
                $csl['container-title'] = $containerResource->value('dcterms:title') ?: $containerResource->value('bibo:shortTitle');
                $csl['container-author'] = $this->cslAuthors($containerResource);
            } else {
                $csl['container-title'] = $container;
            }
            // $csl['event-place'] = $resource->value('');
        }

        return (object) array_filter($csl);
    }

    /**
     * Get the csl type of a resource.
     *
     * @todo Improve mapping of the resource classes to csl type and check dcterms:type else.
     *
     * @param AbstractResourceEntityRepresentation $resource
     * @return string Default type is "standard".
     */
    protected function cslType(AbstractResourceEntityRepresentation $resource)
    {
        $class = $resource->resourceClass();
        if (!$class) {
            return 'standard';
        }
        $class = $class->term();
        $map = require dirname(dirname(dirname(__DIR__))) . '/data/mapping/resource_class_map.php';
        return isset($map[$class]) ? $map[$class] : 'standard';
    }

    /**
     * Get the author of the resource.
     *
     * @todo Check FOAF and related items for the authors.
     * @todo Explode family/given. Check name for an organization.
     *
     * @param AbstractResourceEntityRepresentation $resource
     * @return \stdClass[]
     */
    protected function cslAuthors(AbstractResourceEntityRepresentation $resource)
    {
        $authors = [];
        $creators = $resource->value('bibo:authorList', ['all' => true]) ?: [];
        if ($creators) {
            foreach ($creators as $creator) {
                $authors[] = (object) [
                    'family' => $creator,
                    'given' => '',
                ];
            }
        } else {
            $creators = $resource->value('dcterms:creator', ['all' => true]) ?: [];
            $creators = $this->stripTags($creators);
            foreach ($creators as $creator) {
                $authors[] = (object) [
                    // 'name' => $creator,
                    'family' => $creator,
                    'given' => '',
                    // 'non-dropping-particle' => '',
                    // 'dropping-particle' => '',
                    // 'suffix' => '',
                    // 'sequence' => 'first', // or 'additional'
                    // 'affiliation' => [],
                ];
            }
        }
        return $authors;
    }

    /**
     * Get the csl date of a resource.
     *
     * @param AbstractResourceEntityRepresentation $resource
     * @param string $term
     * @return \stdClass|null
     */
    protected function cslDate(AbstractResourceEntityRepresentation $resource, $term)
    {
        $date = $resource->value($term);
        if (!$date) {
            return null;
        }

        // Date should be either [2019, 11, 25] or ['20191125'] or ['25/11/2019'].
        $date = (string) $date;
        try {
            $datetime = new \DateTime($date);
            if ($datetime) {
                $date = $datetime->format('Ymd');
            }
        } catch (\Exception $e) {
        }
        $date = [$date];
        return (object) ['date-parts' => [$date]];
    }

    /**
     * Strip formatting and remove empty creator elements.
     *
     * @param array $values
     * @return array
     */
    protected function stripTags(array $values)
    {
        return array_values(array_filter(array_map('strip_tags', $values)));
    }
}
