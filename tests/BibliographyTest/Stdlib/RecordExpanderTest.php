<?php declare(strict_types=1);

namespace BibliographyTest\Stdlib;

use Bibliography\Stdlib\RecordExpander;
use PHPUnit\Framework\TestCase;

class RecordExpanderTest extends TestCase
{
    protected function record(): array
    {
        return [
            'o:resource_class' => ['o:id' => 40],
            'dcterms:title' => [['type' => 'literal', 'property_id' => 1, '@value' => 'Ballots for Both']],
            'dcterms:creator' => [['type' => 'literal', 'property_id' => 2, '@value' => 'Clark, Walter']],
            'bibo:doi' => [['type' => 'uri', 'property_id' => 100, '@id' => 'https://doi.org/10.1000/182', 'o:label' => '10.1000/182']],
        ];
    }

    protected function recordValue(?array $record = null): array
    {
        return [
            'type' => 'valuesuggest:doi:works:record',
            'property_id' => 50,
            '@id' => json_encode($record ?? $this->record()),
            'o:label' => 'Clark, Walter. 1916. Ballots for Both.',
        ];
    }

    public function testEmptyPropertiesAreFilled(): void
    {
        $result = (new RecordExpander())->expand([
            'dcterms:bibliographicCitation' => [$this->recordValue()],
        ]);
        $data = $result['data'];
        $this->assertSame(['dcterms:title', 'dcterms:creator', 'bibo:doi'], $result['filled']);
        $this->assertSame([], $result['skipped']);
        $this->assertSame('Ballots for Both', $data['dcterms:title'][0]['@value']);
        $this->assertSame(['o:id' => 40], $data['o:resource_class']);

        // The value keeps the uri of the record and the reference as label.
        $value = $data['dcterms:bibliographicCitation'][0];
        $this->assertSame('valuesuggest:doi:works:record', $value['type']);
        $this->assertSame('https://doi.org/10.1000/182', $value['@id']);
        $this->assertSame('Clark, Walter. 1916. Ballots for Both.', $value['o:label']);
    }

    public function testFilledPropertiesAreSkipped(): void
    {
        $result = (new RecordExpander())->expand([
            'o:resource_class' => ['o:id' => 3],
            'dcterms:title' => [['type' => 'literal', 'property_id' => 1, '@value' => 'My title']],
            'dcterms:bibliographicCitation' => [$this->recordValue()],
        ]);
        $data = $result['data'];
        $this->assertSame(['dcterms:creator', 'bibo:doi'], $result['filled']);
        $this->assertSame(['dcterms:title'], $result['skipped']);
        $this->assertSame('My title', $data['dcterms:title'][0]['@value']);
        $this->assertCount(1, $data['dcterms:title']);
        $this->assertSame(['o:id' => 3], $data['o:resource_class']);
    }

    public function testTermsFilledOutsideOfDataAreSkipped(): void
    {
        $result = (new RecordExpander())->expand([
            'dcterms:bibliographicCitation' => [$this->recordValue()],
        ], ['dcterms:creator']);
        $this->assertSame(['dcterms:creator'], $result['skipped']);
        $this->assertArrayNotHasKey('dcterms:creator', $result['data']);
    }

    public function testRecordWithoutUriBecomesLiteral(): void
    {
        $record = $this->record();
        unset($record['bibo:doi']);
        $result = (new RecordExpander())->expand([
            'dcterms:bibliographicCitation' => [$this->recordValue($record)],
        ]);
        $value = $result['data']['dcterms:bibliographicCitation'][0];
        $this->assertSame('literal', $value['type']);
        $this->assertArrayNotHasKey('@id', $value);
        $this->assertSame('Clark, Walter. 1916. Ballots for Both.', $value['@value']);
    }

    public function testOtherValuesAreNotModified(): void
    {
        $data = [
            'dcterms:title' => [['type' => 'literal', 'property_id' => 1, '@value' => 'Title']],
            'bibo:doi' => [['type' => 'valuesuggest:doi:works', 'property_id' => 100, '@id' => 'https://doi.org/10.1000/182']],
            // A record already expanded has a plain uri.
            'dcterms:bibliographicCitation' => [['type' => 'valuesuggest:doi:works:record', 'property_id' => 50, '@id' => 'https://doi.org/10.1000/182']],
        ];
        $result = (new RecordExpander())->expand($data);
        $this->assertSame($data, $result['data']);
        $this->assertSame([], $result['filled']);
        $this->assertSame([], $result['skipped']);
    }

    public function testInvalidDoiAreRemoved(): void
    {
        $record = $this->record();
        $record['bibo:doi'] = [
            ['type' => 'uri', 'property_id' => 100, '@id' => 'https://dx.doi.org/2014-57555-025', 'o:label' => '2014-57555-025'],
            ['type' => 'uri', 'property_id' => 100, '@id' => 'https://doi.org/10.1000/182', 'o:label' => '10.1000/182'],
        ];
        $result = (new RecordExpander())->expand([
            'dcterms:bibliographicCitation' => [$this->recordValue($record)],
        ]);
        $this->assertSame(['https://doi.org/10.1000/182'], array_column($result['data']['bibo:doi'], '@id'));
        $this->assertSame('https://doi.org/10.1000/182', $result['data']['dcterms:bibliographicCitation'][0]['@id']);

        // Without any valid doi, the property is not filled.
        $record['bibo:doi'] = [$record['bibo:doi'][0]];
        $result = (new RecordExpander())->expand([
            'dcterms:bibliographicCitation' => [$this->recordValue($record)],
        ]);
        $this->assertArrayNotHasKey('bibo:doi', $result['data']);
        $this->assertNotContains('bibo:doi', $result['filled']);
    }
}
