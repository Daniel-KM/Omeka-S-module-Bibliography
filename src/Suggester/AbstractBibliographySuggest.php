<?php declare(strict_types=1);
namespace Bibliography\Suggester;

use Laminas\Http\Client;
use Seboettg\CiteProc\CiteProc;
use ValueSuggest\Suggester\SuggesterInterface;

abstract class AbstractBibliographySuggest implements SuggesterInterface
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

    /**
     * @var array|null
     */
    protected $propertyIds;

    /**
     * @var array|null
     */
    protected $resourceClassIds;

    public function __construct(
        Client $client,
        CiteProc $citeProc,
        array $options,
        array $propertyIds = null,
        array $resourceClassIds = null
    ) {
        $this->client = $client;
        $this->citeProc = $citeProc;
        $this->options = $options;
        $this->propertyIds = $propertyIds;
        $this->resourceClassIds = $resourceClassIds;
    }

    // abstract public function getSuggestions($query, $lang = null);
}
