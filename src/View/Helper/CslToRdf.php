<?php declare(strict_types=1);
namespace Bibliography\View\Helper;

use ArrayObject;
use Laminas\View\Helper\AbstractHelper;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;

class CslToRdf extends AbstractHelper
{
    /**
     * @var array
     */
    protected $propertyIds;

    /**
     * @var array
     */
    protected $resourceClassIds;

    /**
     * @var ArrayObject
     */
    protected $csl;

    protected function __construct(array $propertyIds, array $resourceClassIds)
    {
        $this->propertyIds = $propertyIds;
        $this->resourceClassIds = $resourceClassIds;
    }

    /**
     * Convert a record from the format csl to a standard resource.
     *
     * @see https://citationstyles.org
     * @see https://docs.citationstyles.org/en/1.0.1/index.html
     *
     * @param AbstractResourceEntityRepresentation $resource
     * @return array A json-ld array for an Omeka Item, without specific data
     * added during hydration.
     */
    public function __invoke($csl)
    {
        // A standard array is simpler to manage.
        $csl = json_decode(json_encode($csl), true);
        if (!$csl) {
            return null;
        }

        $csl = new ArrayObject($csl, ArrayObject::ARRAY_AS_PROPS);
        $this->csl = $csl;

        $data = new ArrayObject([], ArrayObject::ARRAY_AS_PROPS);

        // $data['@context'] = 'http://dev.omeka.org/omeka-s-sandbox/api-context';
        // $data['@id'] = null;
        // $data['o:id'] = null;
        // $data['o:owner'] = null;
        // $data['o:thumbnail'] = null;
        // $data['o:created'] = null;
        // $data['o:modified'] = null;
        // $data['o:media'] = [];
        // $data['o:item_set'] = [];

        $data['@type'] = 'o:Item';
        $data['o:is_public'] = true;

        $data['o:resource_class'] = $this->cslToResourceClass();
        $data['o:resource_template'] = null;

        $mapping = require dirname(__DIR__, 3) . '/data/mapping/csl_variables_map.php';
        $mapping = array_filter(array_map(function ($v) {
            return empty($this->propertyIds[$v['property']]) ? null : $v + ['prepend' => '', 'append' => ''];
        }, array_filter($mapping)));

        // Filter keys to avoid checks.
        $csl = array_diff_key($csl, $mapping);

        foreach ($csl as $key => $data) {
            $property = $mapping[$key]['property'];
            $type = $mapping[$key]['type'] ?? 'standard';
            switch ($type) {
                case 'date':
                    // TODO Use datatype NumericDataType if available.
                    $value = $this->extractDate($csl[$key]);
                    if ($value) {
                        $data[$property][] = [
                            'type' => 'literal',
                            'property_id' => $this->propertyIds[$property],
                            '@value' => $mapping[$key]['prepend'] . $value . $mapping[$key]['append'],
                        ];
                    }
                    break;
                case 'name':
                    $values = array_map(function ($v) {
                        return $v + [
                            'name' => '',
                            'family' => '',
                            'given' => '',
                            // TODO Keep the full data of the name.
                            // 'non-dropping-particle' => '',
                            // 'dropping-particle' => '',
                            // 'suffix' => '',
                            // 'sequence' => 'first', // or 'additional'
                            // 'affiliation' => [],
                        ];
                    }, $csl[$key]);
                    foreach ($values as $value) {
                        $value = $this->extractName($value);
                        if ($value) {
                            $data[$property][] = [
                                'type' => 'literal',
                                'property_id' => $this->propertyIds[$property],
                                '@value' => $mapping[$key]['prepend'] . $value . $mapping[$key]['append'],
                            ];
                        }
                    }
                    break;
                case 'number':
                    // TODO Use datatype NumericDataType if available.
                case 'standard':
                default:
                    $values = is_array($csl[$key]) ? $csl[$key] : [$csl[$key]];
                    if (empty($mapping[$key]['multiple'])) {
                        $values = reset($values);
                    }
                    foreach ($values as $value) {
                        $data[$property][] = [
                            'type' => 'literal',
                            'property_id' => $this->propertyIds[$property],
                            '@value' => $mapping[$key]['prepend'] . $value . $mapping[$key]['append'],
                        ];
                    }
                    break;
            }
        }
    }

    /**
     * Convert a name data value into a string.
     *
     * @see \Bibliography\Suggester\Doi\DoiSuggest::extractName()
     *
     * @param array $value
     * @return string
     */
    protected function extractName($value)
    {
        if (!empty($value['name'])) {
            return $value['name'];
        }
        return $value['family'] . (empty($value['given']) ? '' : (', ' . $value['given']));
    }

    /**
     * Convert a date into a standard ISO-8601 string.
     *
     * @see \Bibliography\Suggester\Doi\DoiSuggest::extractDate()
     *
     * @param array $date
     * @return string|null
     */
    protected function extractDate($date)
    {
        if (!empty($date['date-time'])) {
            return $date['date-time'];
        }
        if (!empty($date['date-parts'][0])) {
            $result = $date['date-parts'][0][0];
            if (!empty($date['date-parts'][0][1])) {
                $result .= '-' . sprintf('%02d', $date['date-parts'][0][1]);
                if (!empty($date['date-parts'][0][2])) {
                    $result .= '-' . sprintf('%02d', $date['date-parts'][0][2]);
                }
            }
            return $result;
        }
        if (!empty($date['timestamp'])) {
            return (new \DateTime($date['timestamp']))->format('c');
        }
        return null;
    }
}
