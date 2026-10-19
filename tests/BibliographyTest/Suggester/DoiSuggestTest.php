<?php declare(strict_types=1);

namespace BibliographyTest\Suggester;

use Bibliography\Stdlib\StyleSheet;
use Bibliography\Suggester\Doi\DoiSuggest;
use Laminas\Http\Client;
use PHPUnit\Framework\TestCase;
use Seboettg\CiteProc\CiteProc;

class DoiSuggestTest extends TestCase
{
    public function setUp(): void
    {
        if (!interface_exists('ValueSuggest\Suggester\SuggesterInterface')) {
            $this->markTestSkipped('Requires module ValueSuggest.');
        }
    }

    /**
     * Crossref returns lists for some values and omits others.
     */
    public function testConvertIntoRdfWithListsAndMissingValues(): void
    {
        $suggester = new DoiSuggest(
            new Client(),
            new CiteProc(StyleSheet::load('chicago-author-date'), 'en-US'),
            ['resource' => 'works', 'identifier' => false, 'uri_label' => 'record'],
            array_flip([
                'dcterms:title', 'dcterms:creator', 'dcterms:identifier', 'dcterms:publisher', 'dcterms:contributor',
                'dcterms:created', 'dcterms:date', 'dcterms:dateSubmitted', 'dcterms:issued',
                'dcterms:isPartOf', 'dcterms:language', 'dcterms:subject', 'dcterms:type',
                'bibo:doi', 'bibo:eissn', 'bibo:issn', 'bibo:issue', 'bibo:pages', 'bibo:uri', 'bibo:volume',
            ]),
            ['dcterms:BibliographicResource' => 3, 'bibo:AcademicArticle' => 35]
        );
        $data = (object) [
            'type' => 'journal-article',
            'title' => ['Ballots for Both'],
            'publisher' => 'Publisher',
            'DOI' => '10.1000/182',
            'URL' => null,
            'prefix' => '10.1000',
            'alternative-id' => ['182', '10.1000/183', '2014-57555-025'],
            'funder' => [['name' => 'Foundation']],
        ];
        $method = new \ReflectionMethod($suggester, 'convertIntoRdf');
        $method->setAccessible(true);
        $record = $method->invoke($suggester, $data);

        $this->assertSame('Ballots for Both', $record['dcterms:title'][0]['@value']);
        $this->assertSame('Publisher', $record['dcterms:publisher'][0]['@value']);
        $this->assertSame(['https://dx.doi.org/10.1000/182', 'https://dx.doi.org/10.1000/183'], array_column($record['bibo:doi'], '@id'));
        $this->assertSame(['2014-57555-025'], array_column($record['dcterms:identifier'], '@value'));
        $this->assertSame('literal', $record['dcterms:contributor'][0]['type']);
        $this->assertSame('Foundation (funder)', $record['dcterms:contributor'][0]['@value']);
    }
}
