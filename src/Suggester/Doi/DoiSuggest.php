<?php

namespace Bibliography\Suggester\Doi;

use ValueSuggest\Suggester\SuggesterInterface;
use Zend\Http\Client;

class DoiSuggest implements SuggesterInterface
{
    /**
     * @var Client
     */
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
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
        $results = json_decode($response->getBody(), true);

        foreach ($results['message']['items'] as $result) {
            $result['title'][0];
            $suggestions[] = [
                'value' => $result['DOI'],
                'data' => [
                    'uri' => $result['URL'],
                    'info' => null,
                ],
            ];
        }

        return $suggestions;
    }
}
