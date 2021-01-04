<?php declare(strict_types=1);
namespace Bibliography\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;

class RdfToCsl extends AbstractHelper
{
    /**
     * @var AbstractResourceEntityRepresentation
     */
    protected $resource;

    /**
     * @var array
     */
    protected $defaults;

    /**
     * Convert a resource into the format csl.
     *
     * @see https://citationstyles.org
     * @todo Improve conversion from dcterms and bibo into csl, or find a library for it.
     * @see https://docs.citationstyles.org/en/1.0.1/index.html
     *
     * @param AbstractResourceEntityRepresentation $resource
     * @param array $defaults A pseudo-resource with the defautls to use, for
     * example ['dcterms:creator' => [['@value' => 'John']]].
     * @return \stdClass
     */
    public function __invoke(AbstractResourceEntityRepresentation $resource, array $defaults = [])
    {
        $csl = [];

        $this->resource = $resource;
        $this->defaults = $defaults;

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

        $csl['type'] = $this->cslType();

        $csl['author'] = $this->cslAuthors();

        // TODO Use displayTitle().
        $csl['title'] = (string) $this->resourceValue('dcterms:title');

        // TODO Check FOAF and related items for the publisher.
        $csl['publisher'] = (string) $this->resourceValue('dcterms:publisher');
        // $csl['publisher-place'] = $this->resourceValue('');

        // TODO Check the format of the date if module NumericDataType is installed.
        $csl['created'] = $this->cslDate('dcterms:created');
        $csl['deposited'] = $this->cslDate('dcterms:dateSubmitted');
        $csl['approved'] = $this->cslDate('dcterms:dateAccepted');
        $csl['issued'] = $this->cslDate('dcterms:issued') ?: $this->cslDate('dcterms:date');
        // $csl['published-print'] = $this->cslDate('dcterms:issued') ?: $this->cslDate(dcterms:date');

        $csl['edition'] = (string) $this->resourceValue('bibo:edition');

        $csl['DOI'] = (string) $this->resourceValue('bibo:doi');
        $issn = $this->resourceValue('bibo:issn', ['all' => true]);
        if ($issn) {
            $csl['ISSN'] = count($issn) > 1 ? $this->stripTags($issn) : (string) reset($issn);
        }
        $csl['EISSN'] = (string) $this->resourceValue('bibo:eissn');
        $isbn = $this->resourceValue('bibo:isbn13', ['all' => true]);
        $isbn = array_merge($isbn, $this->resourceValue('bibo:isbn10', ['all' => true]));
        $isbn = array_merge($this->resourceValue('bibo:isbn', ['all' => true]));
        if ($isbn) {
            $csl['ISBN'] = count($isbn) > 1 ? $this->stripTags($isbn) : (string) reset($isbn);
        }
        $csl['URL'] = (string) $this->resourceValue('bibo:uri') ?: (empty($csl['DOI']) ? null : 'https://dx.doi.org/' . urlencode($csl['DOI']));
        $csl['volume'] = (string) $this->resourceValue('bibo:volume');
        $csl['number-of-volumes'] = (string) $this->resourceValue('bibo:numVolumes');
        $csl['issue'] = (string) $this->resourceValue('bibo:issue');

        $pages = (string) $this->resourceValue('bibo:pages');
        $pageStart = (string) $this->resourceValue('bibo:pageStart');
        $pageEnd = (string) $this->resourceValue('bibo:pageEnd');
        if ($pages) {
            $csl['page'] = $pages;
        } else {
            $csl['page'] = $pageStart . (mb_strlen($pageStart) && mb_strlen($pageEnd) ? '-' : '') . $pageEnd;
        }
        if (mb_strlen($pageStart)) {
            $csl['page-first'] = $pageStart;
        }
        if (mb_strlen($pageEnd)) {
            $csl['page-last'] = $pageEnd;
        }
        $csl['number-of-pages'] = (string) $this->resourceValue('bibo:numPages');

        $csl['abstract'] = (string) $this->resourceValue('bibo:shortDescription')
            ?: ((string) $this->resourceValue('bibo:abstract')
                ?: ((string) $this->resourceValue('dcterms:abstract') ?: (string) $this->resourceValue('dcterms:description')));

        $subjects = $this->resourceValue('dcterms:subject', ['all' => true]) ?: [];
        $subjects = $this->stripTags($subjects);
        $csl['subject'] = $subjects;

        $csl['medium'] = (string) $this->resourceValue('dcterms:medium');

        // TODO Check related items for the journal or conference (upper item).
        // The journal (for an article) or the book (for a part) is not directly
        // available, but required. It should be referenced as dcterms:relation
        // or dcterms:isPartOf.
        $container = $this->resourceValue('dcterms:relation') ?: $this->resourceValue('dcterms:isPartOf');
        if ($container) {
            if (strpos($container->type(), 'resource') === 0) {
                $containerResource = $container->valueResource();
                // $csl['collection-title'] = $this->resourceValue('');
                // $csl['collection-title-short'] = $this->resourceValue('');
                $csl['container-title'] = (string) $containerResource->value('dcterms:title') ?: (string) $containerResource->value('bibo:shortTitle');
                $csl['container-author'] = $this->cslAuthorsResource($containerResource);
            } else {
                $csl['container-title'] = (string) $container;
            }
            // $csl['event-place'] = $this->resourceValue('');
        }

        return (object) array_filter($csl);
    }

    /**
     * Get the csl type of a resource.
     *
     * @todo Improve mapping of the resource classes to csl type and check dcterms:type else.
     *
     * @return string
     */
    protected function cslType()
    {
        $class = $this->resource->resourceClass();
        if (!$class) {
            return '';
        }
        $class = $class->term();
        $map = require dirname(__DIR__, 3) . '/data/mapping/csl_resource_class_map.php';
        return empty($map[$class]) ? '' : $map[$class];
    }

    /**
     * Get the authors of the resource.
     *
     * @return \stdClass[]
     */
    protected function cslAuthors()
    {
        return $this->cslAuthorsResource($this->resource, $this->defaults);
    }

    /**
     * Get the authors of the specified resource.
     *
     * @todo Check FOAF and related items for the authors.
     * @todo Explode family/given. Check name for an organization.
     *
     * @param AbstractResourceEntityRepresentation $resource
     * @param array $defaults
     * @return \stdClass[]
     */
    protected function cslAuthorsResource(AbstractResourceEntityRepresentation $resource, array $defaults = [])
    {
        $authors = [];
        $creators = $resource->value('bibo:authorList', ['all' => true]) ?: [];
        if ($creators) {
            foreach ($creators as $creator) {
                $authors[] = (object) [
                    'family' => (string) $creator,
                    'given' => '',
                ];
            }
        } else {
            $creators = $resource->value('dcterms:creator', ['all' => true]) ?: [];
            if ($creators) {
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
            } elseif (@$defaults['dcterms:creator']) {
                foreach ($defaults['dcterms:creator'] as $creator) {
                    $authors[] = (object) [
                        'family' => strip_tags($creator['@value']),
                        'given' => '',
                    ];
                }
            }
        }
        return $authors;
    }

    /**
     * Get the csl date of a resource.
     *
     * @param string $term
     * @return \stdClass|null
     */
    protected function cslDate($term)
    {
        $date = $this->resource->value($term);
        if (!$date) {
            return null;
        }

        // Date should be either [2019, 11, 25] or ['20191125'] or ['25/11/2019'].
        return (object) ['date-parts' => [[(string) $date]]];
    }

    /**
     * Helper to get a resource value, managing a default value.
     *
     * @param string $term
     * @param array $options
     * @return \Omeka\Api\Representation\ValueRepresentation[]|\Omeka\Api\Representation\ValueRepresentation
     */
    protected function resourceValue($term, array $options = [])
    {
        if (empty($this->defaults[$term][0])) {
            return $this->resource->value($term, $options);
        }

        // Prepare the default value.
        if (empty($options['all'])) {
            $options['default'] = $this->defaults[$term][0]['@value'];
        } else {
            // In this view helper, default is always an empty array.
            $options['default'] = [];
            foreach ($this->defaults[$term] as $value) {
                $options['default'][] = $value['@value'];
            }
        }

        return $this->resource->value($term, $options);
    }

    /**
     * Strip formatting and remove empty elements.
     *
     * @param array $values
     * @return array
     */
    protected function stripTags(array $values)
    {
        return array_values(array_filter(array_map('strip_tags', $values)));
    }
}
