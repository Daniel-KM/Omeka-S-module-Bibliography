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
     * @var string
     */
    protected $resource;

    /**
     * @var bool
     */
    protected $isIdentifier;

    public function __construct(Client $client, CiteProc $citeProc, $resource, $isIdentifier)
    {
        $this->client = $client;
        $this->citeProc = $citeProc;
        $this->resource = $resource;
        $this->isIdentifier = $isIdentifier;
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
        if ($this->isIdentifier) {
            $this->client->setUri($this->client->getUri() . '/' . urlencode($query));
        } else {
            $args = [
                'query' => $query,
                // 'rows' => 20,
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

        if ($this->isIdentifier) {
            $results->message->{'total-results'} = 1;
            $results->message->items = [$results->message];
        }

        if (empty($results->message->items)) {
            return [];
        }

        switch ($this->resource) {
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
        foreach ($results->message->items as &$item) {
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
        }
        unset($item);

        // The current version output notices when some common keys are missing.
        // TODO Fix CiteProc to allow missing keys.
        $list = @$this->citeProc->render($results->message->items, 'bibliography', [], true);
        $listXml = new \SimpleXMLElement($list);
        $list = [];
        foreach ($listXml->div as $item) {
            $list[] = substr($item->asXml(), 23, -6);
        }

        $suggestions = [];
        foreach ($results->message->items as $key => $result) {
            $suggestions[] = [
                'value' => $result->DOI,
                'data' => [
                    'uri' => $result->URL,
                    'info' => $list[$key],
                ],
            ];
        }
        return $suggestions;
    }

    protected function suggestedJournals($results)
    {
        $suggestions = [];
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
        foreach ($results->message->items as $result) {
            $value = $result->name;
            $info = null;
            if (!empty($result->uri)) {
                $uri = $result->uri;
            } else {
                $uri = null;
            }
            $suggestions[] = [
                'value' => $value,
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
        foreach ($results->message->items as $result) {
            $info = $result->{primary-name};
            $value = $result->id;
            $uri = 'https://id.crossref.org/member/' . $value;
            $suggestions[] = [
                'value' => $value,
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
