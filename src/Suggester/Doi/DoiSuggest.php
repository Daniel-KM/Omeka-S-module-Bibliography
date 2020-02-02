<?php
namespace Bibliography\Suggester\Doi;

use Bibliography\Suggester\AbstractBibliographySuggest;

class DoiSuggest extends AbstractBibliographySuggest
{
    /**
     * Retrieve suggestions for DOI from crossref web services API.
     *
     * @see https://github.com/CrossRef/rest-api-doc
     * @param string $query
     * @param string $lang
     * @return array
     */
    public function getSuggestions($query, $lang = null)
    {
        if ($this->options['identifier']) {
            $query = trim($query);
            $this->client->setUri($this->client->getUri() . '/' . urlencode($query));
        } else {
            $args = [
                'query' => $query,
                'rows' => 20,
            ];
            if ($lang) {
                $args['language'] = $lang;
            }
            $this->client->setParameterGet($args);
        }

        $response = $this->client->send();
        if (!$response->isSuccess()) {
            return [];
        }

        // Don't convert to array: csl are objects.
        $results = json_decode($response->getBody());

        if ($this->options['identifier']) {
            $results->message->{'total-results'} = 1;
            $results->message->items = [$results->message];
        }

        if (empty($results->message->items)) {
            return [];
        }

        switch ($this->options['resource']) {
            case 'works':
                return $this->suggestedWorks($results);
            case 'journals':
                return $this->suggestedJournals($results);
            case 'funders':
                return $this->suggestedFunders($results);
            case 'members':
                return $this->suggestedMembers($results);
            case 'licenses':
                return $this->suggestedLicenses($results);
            case 'types':
                return $this->suggestedTypes($results);
            default:
                return [];
        }
    }

    protected function suggestedWorks($results)
    {
        $list = [];

        // Fix crossref output for CiteProc, that requires single strings.
        // @see https://github.com/Crossref/rest-api-doc/blob/master/api_format.md
        $toStrings = [
            'title',
            'original-title',
            'short-title',
            'subtitle',
            'short-container-title',
            'container-title',
            'collection-title',
            'collection-title-short',
            'ISBN',
            'ISSN',
            'archive',
        ];
        foreach ($results->message->items as $key => &$item) {
            foreach ($toStrings as $key) {
                if (isset($item->{$key}) && is_array($item->{$key})) {
                    $item->{$key} = reset($item->{$key});
                }
            }
            // CiteProc requires a family name, even for institutions.
            if (isset($item->author)) {
                foreach ($item->author as &$author) {
                    if (isset($author->name) && !isset($author->family)) {
                        $author->family = $author->name;
                        $author->given = null;
                    }
                }
                unset($author);
            }
            // CiteProc requires a key for genre.
            if (!isset($item->genre)) {
                $item->genre = null;
            }

            // The current version output notices when some common keys are missing.
            // CiteProc automatically sort the list of citations, and it's complex
            // to not sort, so the rendering is made one by one.
            // TODO Fix CiteProc to allow missing keys and to keep order.
            // CiteProc fails when the metadata are incomplete.
            try {
                $value = @$this->citeProc->render([$item], 'bibliography');
            } catch (\Exception $e) {
                continue;
            }
            $list[] = substr($value, 52, -13);
        }
        unset($item);

        $suggestions = [];

        switch ($this->options['uri_label']) {
            case 'name':
                foreach ($results->message->items as $key => $result) {
                    $suggestions[] = [
                        'value' => strip_tags($list[$key]),
                        'data' => [
                            'uri' => $result->URL,
                            'info' => $result->DOI,
                        ],
                    ];
                }
                break;
            case 'reference':
                foreach ($results->message->items as $key => $result) {
                    $suggestions[] = [
                        'value' => strip_tags($list[$key]),
                        'data' => [
                            'uri' => null,
                            'info' => $result->DOI,
                        ],
                    ];
                }
                break;
            case 'record':
                foreach ($results->message->items as $key => $result) {
                    $suggestions[] = [
                        'value' => strip_tags($list[$key]),
                        'data' => [
                            // The uri is not displayed, but sent to the controller.
                            'uri' => json_encode($this->convertIntoRdf($result), 320),
                            'info' => $result->DOI,
                        ],
                    ];
                }
                break;
            case 'id':
            default:
                foreach ($results->message->items as $key => $result) {
                    $suggestions[] = [
                        'value' => $result->DOI,
                        'data' => [
                            'uri' => $result->URL,
                            'info' => $list[$key],
                        ],
                    ];
                }
                break;
        }

        return $suggestions;
    }

    protected function suggestedJournals($results)
    {
        $suggestions = [];
        $useName = $this->options['uri_label'] === 'name';
        foreach ($results->message->items as $result) {
            $info = $result->title . (isset($result->publisher) ? ' (' . $result->publisher . ')' : '');
            if (!empty($result->DOI)) {
                $value = $result->DOI;
            } elseif (!empty($result->ISSN)) {
                $value = is_array($result->ISSN) ? reset($result->ISSN) : $result->ISSN;
            } else {
                $value = $info;
                $info = null;
            }
            if (!empty($result->URL)) {
                $url = $result->URL;
            } elseif (!empty($result->link[0]->URL)) {
                $url = $result->link[0]->URL;
            } else {
                $url = null;
            }

            if ($useName && $info) {
                $v = $value;
                $value = $info;
                $info = $v;
            }

            $suggestions[] = [
                'value' => $value,
                'data' => [
                    'uri' => $url,
                    'info' => $info,
                ],
            ];
        }
        return $suggestions;
    }

    protected function suggestedFunders($results)
    {
        $suggestions = [];
        $useName = $this->options['uri_label'] === 'name';
        foreach ($results->message->items as $result) {
            if ($useName) {
                $value = $result->name;
                $info = $result->id;
            } else {
                $value = $result->id;
                $info = $result->name;
            }
            $uri = empty($result->uri) ? null : $result->uri;
            $suggestions[] = [
                'value' => (string) $value,
                'data' => [
                    'uri' => $uri,
                    'info' => $info,
                ],
            ];
        }
        return $suggestions;
    }

    protected function suggestedMembers($results)
    {
        $suggestions = [];
        $useName = $this->options['uri_label'] === 'name';
        foreach ($results->message->items as $result) {
            if ($useName) {
                $value = $result->{'primary-name'};
                $info = $result->id;
            } else {
                $value = $result->id;
                $info = $result->{'primary-name'};
            }
            $uri = 'https://id.crossref.org/member/' . $result->id;
            $suggestions[] = [
                'value' => (string) $value,
                'data' => [
                    'uri' => $uri,
                    'info' => $info,
                ],
            ];
        }
        return $suggestions;
    }

    protected function suggestedLicenses($results)
    {
        $suggestions = [];
        foreach ($results->message->items as $result) {
            $suggestions[] = [
                'value' => $result->URL,
                'data' => [
                    'uri' => $result->URL,
                    'info' => null,
                ],
            ];
        }
        return $suggestions;
    }

    protected function suggestedTypes($results)
    {
        $suggestions = [];
        foreach ($results->message->items as $result) {
            $suggestions[] = [
                'value' => $result->id,
                'data' => [
                    'uri' => null,
                    'info' => $result->label,
                ],
            ];
        }
        return $suggestions;
    }

    protected function convertIntoRdf($data)
    {
        // Avoid recursion issues with useless data.
        unset($data->items);

        // Convert data into recursive array for easier process (with default).
        $data = json_decode(json_encode($data), true);

        $item = [];

        $defaults = [
            'title' => null,
            'author' => [],
            'publisher' => null,
            // Journal (available via issn too).
            'container-title' => null,
            // Short container title can be retrieved from the container title and is only a local convention.
            'short-container-title' => null,
            // Domain of the publisher, so can be retrieved from the publisher or the issn.
            'content-domain' => [],
            // Doi prefix of the journal.
            'prefix' => null,
            // Journal issue = published-print + issue.
            'journal-issue' => [],
            'issue' => null,
            'page' => null,
            'volume' => null,
            // Two letters iso code.
            'language' => null,

            // Dates.
            'created' => [],
            'deposited' => [],
            'issued' => [],
            'indexed' => [],
            'published-print' => [],
            'published-online' => [],

            'funder' => [],

            'DOI' => null,
            // The url for the first DOI.
            'URL' => null,
            // A list of alternative doi. First may be main DOI without prefix.
            'alternative-id' => [],
            'ISSN' => null,
            'issn-type' => [],

            'type' => null,
            // Never filled in many examples, but provided as key.
            'genre' => null,

            // Citations.
            'relation' => [],
            // History of the resource: received, accepted, published, etc.
            'assertion' => [],

            // Other data that don't really belong to the record.

            // Source is always "Crossref".
            'source' => null,
            // Member is an internal identifier of crossref?
            'member' => null,

            // A url to the update policy of the journal.
            'update-policy' => null,

            'is-referenced-by-count' => null,
            'reference-count' => null,
            'references-count' => null,
            'reference' => [],
            // Score according to the search engine. Useless.
            'score' => null,
        ];
        $data += $defaults;

        $mappingTypes = require dirname(dirname(dirname(__DIR__))) . '/data/mapping/crossref_types_map.php';
        $noRdfType = empty($mappingTypes[$data['type']]);
        $resourceClass = $noRdfType ? 'dcterms:BibliographicResource' : $mappingTypes[$data['type']];
        $item['o:resource_class'] = ['o:id' => $this->resourceClassIds[$resourceClass]];

        $mappingSingle = [
            'title' => 'dcterms:title',
            'publisher' => 'dcterms:publisher',
            'container-title' => 'dcterms:isPartOf',
            'issue' => 'bibo:issue',
            'page' => 'bibo:pages',
            'volume' => 'bibo:volume',
            'language' => 'dcterms:language',
            'type' => 'dcterms:type',
            'genre' => 'dcterms:subject',
        ];
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
            'author' => 'dcterms:creator',
            // 'content-domain' => 'dcterms:publisher',
            // 'journal-issue' => 'dcterms:issue' + 'bibo:issue',
            'created' => 'dcterms:created',
            'deposited' => 'dcterms:dateSubmitted',
            'issued' => 'dcterms:issued',
            // TODO Improve mapping for "indexed": indexed by Crossref?
            'indexed' => [
                'property' => 'dcterms:date',
                'append' => ' (indexed)',
            ],
            'published-print' => [
                'property' => 'dcterms:issued',
                'append' => ' (print)',
            ],
            'published-online' => [
                'property' => 'dcterms:issued',
                'append' => ' (online)',
            ],
            'funder' => [
                'dcterms:contributor',
                'append' => '',
            ],
            'DOI' => 'bibo:doi',
            'URL' => 'bibo:uri',
            'alternative-id' => 'bibo:doi',
            // ISSN is managed with issn-type.
            // 'ISSN' => 'bibo:issn',
            // 'issn-type' => [],
        ];

        foreach ($data['author'] as $value) {
            // Other informations are available: orcid, affiliation, order of
            // authors ("first" or "additional"), etc.
            // TODO Add an option to check/create/update the author, journal, etc.
            $value = $this->extractName($value);
            if ($value) {
                $item['dcterms:creator'][] = [
                    'type' => 'literal',
                    'property_id' => $this->propertyIds['dcterms:creator'],
                    '@value' => $value,
                ];
            }
        }

        foreach (['created', 'deposited', 'issued', 'indexed', 'published-print', 'published-online'] as $key) {
            if (empty($data[$key])) {
                continue;
            }
            $value = $this->extractDate($data[$key]);
            if (is_array($mapping[$key])) {
                $property = $mapping[$key]['property'];
                $suffix = $mapping[$key]['append'];
            } else {
                $property = $mapping[$key];
                $suffix = '';
            }
            $item[$property][] = [
                'type' => 'literal',
                'property_id' => $this->propertyIds[$property],
                '@value' => $value . $suffix,
            ];
        }

        // There are always doi and url, because it's fetched from the doi.
        $item['bibo:doi'][] = [
            'type' => 'uri',
            'property_id' => $this->propertyIds['bibo:doi'],
            '@id' => mb_strlen($data['URL'])
                // Use https to allow safe browsing and easier api.
                ? str_replace('http://', 'https://', $data['URL'])
                : 'https://dx.doi.org/' . $data['DOI'],
            'o:label' => $data['DOI'],
        ];

        // TODO Check if alternative ids are really always doi.
        foreach ($data['alternative-id'] as $value) {
            $valueDoi = $data['prefix'] . '/' . $value;
            if ($valueDoi === $data['DOI']) {
                continue;
            }
            $item['bibo:doi'][] = [
                'type' => 'uri',
                'property_id' => $this->propertyIds['bibo:doi'],
                '@id' => 'https://dx.doi.org/' . $value,
                'o:label' => $value,
            ];
        }

        if ($data['issn-type']) {
            foreach ($data['issn-type'] as $value) {
                $item['bibo:issn'][] = [
                    'type' => 'literal',
                    'property_id' => $value['type'] === 'electronic' ? $this->propertyIds['bibo:eissn'] : $this->propertyIds['bibo:issn'],
                    '@value' => $value['value'],
                ];
            }
        } elseif ($data['ISSN']) {
            $item['bibo:issn'][] = [
                'type' => 'literal',
                'property_id' => $this->propertyIds['bibo:issn'],
                '@value' => $data['ISSN'],
            ];
        }

        foreach ($data['funder'] as $value) {
            $item['dcterms:contributor'][] = [
                'type' => 'uri',
                'property_id' => $this->propertyIds['dcterms:contributor'],
                '@id' => 'https://dx.doi.org/' . $value['DOI'],
                'o:label' => $value['name'] . ' (funder)',
            ];
        }

        // Don't duplicate the type and the ressource class.
        if ($data['type'] && $noRdfType) {
            $mappingTypeLabels = [
                'book' => 'Book',
                'book-chapter' => 'Book Chapter',
                'book-part' => 'Part',
                'book-section' => 'Book Section',
                'book-series' => 'Book Series',
                'book-set' => 'Book Set',
                'book-track' => 'Book Track',
                'component' => 'Component',
                'dataset' => 'Dataset',
                'dissertation' => 'Dissertation',
                'edited-book' => 'Edited Book',
                'journal' => 'Journal',
                'journal-article' => 'Journal Article',
                'journal-issue' => 'Journal Issue',
                'journal-volume' => 'Journal Volume',
                'monograph' => 'Monograph',
                'other' => 'Other',
                'peer-review' => 'Peer Review',
                'posted-content' => 'Posted Content',
                'proceedings' => 'Proceedings',
                'proceedings-article' => 'Proceedings Article',
                'proceedings-series' => 'Proceedings Series',
                'reference-book' => 'Reference Book',
                'reference-entry' => 'Reference Entry',
                'report' => 'Report',
                'report-series' => 'Report Series',
                'standard' => 'Standard',
                'standard-series' => 'Standard Series',
            ];
            $item['dcterms:type'][] = [
                'type' => 'literal',
                'property_id' => $this->propertyIds['dcterms:type'],
                '@value' => isset($mappingTypeLabels[$data['type']]) ? $mappingTypeLabels[$data['type']] : $data['type'],
            ];
        }

        if ($data['genre']) {
            $item['dcterms:subject'][] = [
                'type' => 'literal',
                'property_id' => $this->propertyIds['dcterms:subject'],
                '@value' => $data['genre'],
            ];
        }

        // TODO Relations, assertions, references, etc.

        return array_filter($item);
    }

    /**
     * Convert a name data value into a string.
     *
     * @see \Bibliography\View\Helper\CslToRdf::extractName()
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
     * @see \Bibliography\View\Helper\CslToRdf::extractDate()
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
