<?php

namespace Bibliography\Suggester\Doi;

use Seboettg\CiteProc\CiteProc;
use ValueSuggest\Suggester\SuggesterInterface;
use Zend\Http\Client;

class DoiSuggest implements SuggesterInterface
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var CiteProc
     */
    protected $citeProc;

    /**
     * Managed options are:
     * - resource: the type of resource to query
     * - identifier: search strictly the identifier
     * - uri_label: the data that will be use for the label (id or name)
     *
     * @var array
     */
    protected $options;

    public function __construct(Client $client, CiteProc $citeProc, array $options)
    {
        $this->client = $client;
        $this->citeProc = $citeProc;
        $this->options = $options;
    }

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

        $useName = $this->options['uri_label'] === 'name';
        if ($useName) {
            foreach ($results->message->items as $key => $result) {
                $suggestions[] = [
                    'value' => $list[$key],
                    'data' => [
                        'uri' => $result->URL,
                        'info' => $result->DOI,
                    ],
                ];
            }
        } else {
            foreach ($results->message->items as $key => $result) {
                $suggestions[] = [
                    'value' => $result->DOI,
                    'data' => [
                        'uri' => $result->URL,
                        'info' => $list[$key],
                    ],
                ];
            }
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
}
