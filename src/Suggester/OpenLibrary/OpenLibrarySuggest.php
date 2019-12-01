<?php

namespace Bibliography\Suggester\OpenLibrary;

use Seboettg\CiteProc\CiteProc;
use ValueSuggest\Suggester\SuggesterInterface;
use Zend\Http\Client;

class OpenLibrarySuggest implements SuggesterInterface
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

        $result = reset($results);
        $id = key($results);
        $csl = $this->convertIntoCsl($id, $result);

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
        $useName = $this->options['uri_label'] === 'name';
        foreach ([$csl] as $key => $result) {
            if ($useName) {
                $value = $list[$key];
                $info = $ids[$key];
            } else {
                $value = $ids[$key];
                $info = $list[$key];
            }
            $suggestions[] = [
                'value' => $value,
                'data' => [
                    'uri' => empty($result->url) ? null : $result->url,
                    'info' => $info,
                ],
            ];
        }
        return $suggestions;
    }

    protected function convertIntoCsl($id, array $data)
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
            $csl['ISBN'] = $data['identifiers']['isbn_13'];
        } elseif (isset($data['identifiers']['isbn_10'])) {
            $csl['ISBN'] = $data['identifiers']['isbn_10'];
        }

        /*
        // Not managed...
        if (isset($data['identifiers']['lccn'])) {
            $csl['LCCN'] = $data['identifiers']['lccn'];
        }
        if (isset($data['identifiers']['oclc'])) {
            $csl['OCLC'] = $data['identifiers']['oclc'];
        }
        if (isset($data['identifiers']['olid'])) {
            $csl['OLID'] = $data['identifiers']['olid'];
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
}
