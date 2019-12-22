<?php
namespace Bibliography\Suggester\OpenLibrary;

use Bibliography\Suggester\AbstractBibliographySuggest;

class OpenLibrarySuggest extends AbstractBibliographySuggest
{
    /**
     * Retrieve suggestions for identifiers available via Open Library API.
     *
     * @see https://openlibrary.org/dev/docs/api/books
     * @param string $query
     * @param string $lang
     * @return array
     */
    public function getSuggestions($query, $lang = null)
    {
        if (!$this->options['identifier']) {
            return [];
        }

        $args = [
            // A 10-digits ISBN may have a check code of X (10) appended.
            'bibkeys' => $this->options['resource'] . ':' . preg_replace('~[^\dxX]~', '', $query),
            'format' => 'json',
            'jscmd' => 'data',
        ];
        $this->client->setParameterGet($args);

        $response = $this->client->send();
        if (!$response->isSuccess()) {
            return [];
        }

        $results = json_decode($response->getBody(), true);
        if (empty($results)) {
            return [];
        }

        // TODO Clean process, since  there is only one result.

        $result = reset($results);
        $id = key($results);
        $results = [$result];
        $csl = $this->convertToCsl($id, $result);

        $id = strtok($id, ':');
        $id = strtok(':');
        $ids = [$id];

        $list = [];
        // TODO Fix CiteProc to allow missing keys and to keep order.
        // CiteProc fails when the metadata are incomplete.
        try {
            $item = @$this->citeProc->render([$csl], 'bibliography');
        } catch (\Exception $e) {
            return [];
        }
        $list[] = substr($item, 52, -13);

        $suggestions = [];

        switch ($this->options['uri_label']) {
            case 'name':
                foreach ([$csl] as $key => $result) {
                    $suggestions[] = [
                        'value' => strip_tags($list[$key]),
                        'data' => [
                            'uri' => empty($result->url) ? null : $result->url,
                            'info' => $ids[$key],
                        ],
                    ];
                }
                break;
            case 'reference':
                foreach ([$csl] as $key => $result) {
                    $suggestions[] = [
                        'value' => strip_tags($list[$key]),
                        'data' => [
                            'uri' => null,
                            'info' => $ids[$key],
                        ],
                    ];
                }
                break;
            case 'record':
                foreach ([$csl] as $key => $result) {
                    $results[] =
                    $suggestions[] = [
                        'value' => strip_tags($list[$key]),
                        'data' => [
                            // The uri is not displayed, but sent to the controller.
                            // Use main results because result is csl formatted.
                            'uri' => json_encode($this->convertIntoRdf($results[$key]), 320),
                            'info' => $ids[$key],
                        ],
                    ];
                }
                break;
            case 'id':
            default:
                foreach ([$csl] as $key => $result) {
                    $suggestions[] = [
                        'value' => $ids[$key],
                        'data' => [
                            'uri' => empty($result->url) ? null : $result->url,
                            'info' => $list[$key],
                        ],
                    ];
                }
                break;
        }

        return $suggestions;
    }

    protected function convertToCsl($id, array $data)
    {
        $csl = [];

        // Some mappings are useless for a standard bibliographic reference, but
        // formats are very numerous, so try to map as many fields as possible.

        $data += [
            'url' => null,
            'title' => null,
            'subtitle' => null,
            'authors' => [],
            'identifiers' => [],
            'classifications' => [],
            'subjects' => [],
            'subject_places' => [],
            'subject_people' => [],
            'subject_times' => [],
            'publishers' => [],
            'publish_places' => [],
            'publish_date' => null,
            'excerpts' => [],
            'links' => [],
            'cover' => [],
            'ebooks' => [],
            'number_of_pages' => null,
            'weight' => null,
            // Undocumented.
            'pagination' => null,
        ];

        // An id is required internally.
        $csl['id'] = $id;

        $csl['type'] = 'book';

        $csl['author'] = [];
        foreach ($data['authors'] as $author) {
            $csl['author'][] = (object) [
                'family' => $author['name'],
                'given' => null,
            ];
        }

        $csl['title'] = $data['title'];
        // $csl[''] = $data['subtitle'];

        if (isset($data['publishers'][0])) {
            $csl['publisher'] = $data['publishers'][0]['name'];
        }
        if (isset($data['publish_places'][0])) {
            $csl['publisher-place'] = $data['publish_places'][0]['name'];
        }

        // TODO Check the format of the date if module NumericDataType is installed.
        if ($data['publish_date']) {
            // Date should be either [2019, 11, 25] or ['20191125'] or ['25/11/2019'].
            $csl['issued'] = (object) ['date-parts' => [[$data['publish_date']]]];
        }
        // $csl['published-print'] = (object) ['date-parts' => [[$data['publish_date']]]];

        if (isset($data['identifiers']['isbn_13'])) {
            $csl['ISBN'] = $data['identifiers']['isbn_13'][0];
        } elseif (isset($data['identifiers']['isbn_10'])) {
            $csl['ISBN'] = $data['identifiers']['isbn_10'][0];
        }

        /*
        // Not managed...
        if (isset($data['identifiers']['lccn'])) {
            $csl['LCCN'] = $data['identifiers']['lccn'][0];
        }
        if (isset($data['identifiers']['oclc'])) {
            $csl['OCLC'] = $data['identifiers']['oclc'][0];
        }
        if (isset($data['identifiers']['olid'])) {
            $csl['OLID'] = $data['identifiers']['olid'][0];
        }
        */

        $csl['URL'] = $data['url'];

        $csl['page'] = $data['pagination'];
        $csl['number-of-pages'] = $data['number_of_pages'];

        /*
        if (isset($data['excerpts'][0]['text'])) {
            $csl['abstract'] = $data['excerpts'][0]['text'];
        }
        */

        // TODO Add classification from OpenLibrary.
        // $csl['subject'] = $subject['classification'];
        foreach (['subjects', 'subject_places', 'subject_people', 'subject_times'] as $subjectType) {
            foreach ($data[$subjectType] as $subject) {
                $csl['subject'] = $subject['name'];
            }
        }

        return (object) array_filter($csl);
    }

    protected function convertIntoRdf(array $data)
    {
        $item = [];

        $defaults = [
            'url' => null,
            'title' => null,
            'subtitle' => null,
            'authors' => [],
            'identifiers' => [],
            'classifications' => [],
            'subjects' => [],
            'subject_places' => [],
            'subject_people' => [],
            'subject_times' => [],
            'publishers' => [],
            'publish_places' => [],
            'publish_date' => null,
            'excerpts' => [],
            'links' => [],
            'cover' => [],
            'ebooks' => [],
            'number_of_pages' => null,
            'weight' => null,
            // Undocumented.
            'by_statement' => null,
            'pagination' => null,
            'table_of_contents' => [],
            'notes' => null,
            // Id on ol.
            // 'key' => null,
        ];
        $data += $defaults;

        // Open Library manages books only.
        // Omeka property 3 = "dcterms:BibliographicResource":
        // Omeka property 40 = "bibo:Book".
        $item['o:resource_class'] = ['o:id' => 40];

        $mappingSingle = [
            // 'url' => 'bibo:uri',
            'title' => 'dcterms:title',
            'subtitle' => 'dcterms:alternative',
            // TODO Check the format of the date if module NumericDataType is installed.
            'publish_date' => 'dcterms:issued',
            'number_of_pages' => 'bibo:numPages',
            'weight' => 'dcterms:extent',
            'pagination' => 'bibo:pages',
        ];
        if (!empty($data['url'])) {
            $item['bibo:uri'][] = [
                'type' => 'uri',
                'property_id' => $this->propertyIds['bibo:uri'],
                '@id' => $data['url'],
            ];
        }
        foreach ($mappingSingle as $key => $map) {
            if (strlen($data[$key])) {
                $item[$map][] = [
                    'type' => 'literal',
                    'property_id' => $this->propertyIds[$map],
                    '@value' => $data[$key],
                ];
            }
        }

        // Every field has different subkeys.
        $mapping = [
            'authors' => 'dcterms:creator',
            'identifiers' => 'dcterms:identifier',
            'classifications' => 'dcterms:subject',
            'subjects' => 'dcterms:subject',
            'subject_people' => 'dcterms:subject',
            'subject_places' => 'dcterms:spatial',
            'subject_times' => 'dcterms:temporal',
            'publishers' => 'dcterms:publisher',
            // Merged with dcterms:publisher.
            // 'publish_places' => 'dcterms:publisher',
            // 'excerpts' => 'bibo:content',
            'links' => 'dcterms:relation',
            // May be media url or asset or link.
            'cover' => 'dcterms:relation',
            'ebooks' => 'dcterms:hasFormat',
        ];

        foreach ($data['authors'] as $value) {
            $item['dcterms:creator'][] = [
                'type' => 'uri',
                'property_id' => $this->propertyIds['dcterms:creator'],
                '@id' => $value['url'],
                'o:label' => $value['name'],
            ];
        }

        $bases = [
            'isbn' => 'bibo:isbn',
            'isbn_10' => 'bibo:isbn10',
            'isbn_13' => 'bibo:isbn13',
            'lccn' => 'bibo:lccn',
            'oclc' => 'bibo:oclcnum',
            'handle' => 'bibo:handle',
            'doi' => 'bibo:doi',
        ];
        foreach ($data['identifiers'] as $base => $values) {
            if (isset($bases[$base])) {
                $property = $bases[$base];
                $base = '';
            } else {
                $property = 'dcterms:identifier';
                $base .= ':';
            }
            foreach ($values as $value) {
                $item[$property][] = [
                    'type' => 'literal',
                    'property_id' => $this->propertyIds[$property],
                    '@value' => $base . $value,
                ];
            }
        }

        foreach ($data['publishers'] as $key => $value) {
            $name = $value['name'];
            if (isset($data['publish_places'][$key])) {
                $name .= ', ' . $data['publish_places'][$key]['name'];
            }
            $item['dcterms:publisher'][] = [
                'type' => 'literal',
                'property_id' => $this->propertyIds['dcterms:publisher'],
                '@value' => $name,
            ];
        }

        foreach ($data['classifications'] as $base => $values) {
            foreach ($values as $value) {
                $item['dcterms:subject'][] = [
                    'type' => 'literal',
                    'property_id' => $this->propertyIds['dcterms:subject'],
                    '@value' => $base . ':' . $value,
                ];
            }
        }

        foreach (['subjects', 'subject_places', 'subject_people', 'subject_times'] as $base) {
            $property = $mapping[$base];
            foreach ($data[$base] as $value) {
                $item[$property][] = [
                    'type' => 'uri',
                    'property_id' => $this->propertyIds[$property],
                    '@id' => $value['url'],
                    'o:label' => $value['name'],
                ];
            }
        }

        foreach ($data['cover'] as $key => $value) {
            $item['dcterms:relation'][] = [
                'type' => 'uri',
                'property_id' => $this->propertyIds['dcterms:relation'],
                '@id' => $value,
                'o:label' => 'cover ' . $key,
            ];
        }

        foreach ($data['ebooks'] as $value) {
            $item['dcterms:relation'][] = [
                'type' => 'uri',
                'property_id' => $this->propertyIds['dcterms:relation'],
                '@id' => $value['preview_url'],
                'o:label' => 'ebook',
            ];
        }

        foreach ($data['links'] as $value) {
            $item['dcterms:relation'][] = [
                'type' => 'uri',
                'property_id' => $this->propertyIds['dcterms:relation'],
                '@id' => $value['url'],
                'o:label' => $value['title'],
            ];
        }

        if ($data['table_of_contents']) {
            $toc = '';
            foreach ($data['table_of_contents'] as $value) {
                $toc .= ($value['level'] ? $value['level'] . '. ' : '')
                    . ($value['title'] ?: $value['label'])
                    . ($value['pagenum'] ? ' : ' . $value['pagenum'] : '')
                    . "\n";
            }
            $item['dcterms:tableOfContents'][] = [
                'type' => 'literal',
                'property_id' => $this->propertyIds['dcterms:tableOfContents'],
                '@value' => trim($toc),
            ];
        }

        return array_filter($item);
    }
}
