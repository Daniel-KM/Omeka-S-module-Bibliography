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

    public function __construct(Client $client, CiteProc $citeProc)
    {
        $this->client = $client;
        $this->citeProc = $citeProc;
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
        $args = [
            'query' => $query,
        ];
        if ($lang) {
            $args['language'] = $lang;
        }
        $this->client->setParameterGet($args);

        $response = $this->client->send();
        if (!$response->isSuccess()) {
            return [];
        }

        // Parse the JSON response.
        $suggestions = [];
        // Don't convert to array: csl are objects.
        $results = json_decode($response->getBody());

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
        $citations = @$this->citeProc->render($results->message->items, 'bibliography', [], true);
        $citationsXml = new \SimpleXMLElement($citations);
        $citations = [];
        foreach ($citationsXml->div as $citation) {
            $citations[] = substr($citation->asXml(), 23, -6);
        }

        foreach ($results->message->items as $key => $result) {
            $suggestions[] = [
                'value' => $result->DOI,
                'data' => [
                    'uri' => $result->URL,
                    'info' => $citations[$key],
                ],
            ];
        }

        return $suggestions;
    }
}
