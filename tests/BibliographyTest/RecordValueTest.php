<?php declare(strict_types=1);

namespace BibliographyTest;

use CommonTest\AbstractTestCase;

/**
 * Expansion of the values "collected record" on save via the api.
 */
class RecordValueTest extends AbstractTestCase
{
    protected array $itemIds = [];

    public function setUp(): void
    {
        parent::setUp();
        if (!class_exists('ValueSuggest\Module', false)) {
            $this->markTestSkipped('Requires module ValueSuggest.');
        }
        $auth = $this->getService('Omeka\AuthenticationService');
        $adapter = $auth->getAdapter();
        $adapter->setIdentity('admin@example.com');
        $adapter->setCredential('root');
        $auth->authenticate();
    }

    public function tearDown(): void
    {
        foreach ($this->itemIds as $id) {
            $this->getService('Omeka\ApiManager')->delete('items', $id);
        }
        parent::tearDown();
    }

    protected function propertyId(string $term): int
    {
        return $this->getEasyMeta()->propertyId($term);
    }

    protected function recordValue(): array
    {
        $record = [
            'o:resource_class' => ['o:id' => $this->getEasyMeta()->resourceClassId('bibo:Book')],
            'dcterms:title' => [['type' => 'literal', 'property_id' => $this->propertyId('dcterms:title'), '@value' => 'Ballots for Both']],
            'dcterms:creator' => [['type' => 'literal', 'property_id' => $this->propertyId('dcterms:creator'), '@value' => 'Clark, Walter']],
            'bibo:doi' => [['type' => 'uri', 'property_id' => $this->propertyId('bibo:doi'), '@id' => 'https://doi.org/10.1000/182', 'o:label' => '10.1000/182']],
        ];
        return [
            'type' => 'valuesuggest:doi:works:record',
            'property_id' => $this->propertyId('dcterms:bibliographicCitation'),
            '@id' => json_encode($record),
            'o:label' => 'Clark, Walter. 1916. Ballots for Both.',
        ];
    }

    public function testRecordIsExpandedOnCreate(): void
    {
        $item = $this->getService('Omeka\ApiManager')->create('items', [
            'dcterms:creator' => [['type' => 'literal', 'property_id' => $this->propertyId('dcterms:creator'), '@value' => 'Anonymous']],
            'dcterms:bibliographicCitation' => [$this->recordValue()],
        ])->getContent();
        $this->itemIds[] = $item->id();

        $this->assertSame('Ballots for Both', (string) $item->value('dcterms:title'));
        $this->assertSame('bibo:Book', $item->resourceClass()->term());
        $this->assertSame('https://doi.org/10.1000/182', $item->value('bibo:doi')->uri());

        // The filled property is not modified.
        $creators = $item->value('dcterms:creator', ['all' => true]);
        $this->assertCount(1, $creators);
        $this->assertSame('Anonymous', (string) $creators[0]);

        $citation = $item->value('dcterms:bibliographicCitation');
        $this->assertSame('valuesuggest:doi:works:record', $citation->type());
        $this->assertSame('https://doi.org/10.1000/182', $citation->uri());
        $this->assertSame('Clark, Walter. 1916. Ballots for Both.', $citation->value());

        $messages = $this->getControllerPlugin('messenger')->get();
        $warnings = array_map('strval', $messages[\Omeka\Mvc\Controller\Plugin\Messenger::WARNING] ?? []);
        $this->assertNotEmpty(array_filter($warnings, fn ($message) => strpos($message, 'dcterms:creator') !== false));
    }

    public function testRecordIsExpandedOnPartialUpdate(): void
    {
        $api = $this->getService('Omeka\ApiManager');
        $item = $api->create('items', [
            'dcterms:title' => [['type' => 'literal', 'property_id' => $this->propertyId('dcterms:title'), '@value' => 'My title']],
        ])->getContent();
        $this->itemIds[] = $item->id();

        $item = $api->update('items', $item->id(), [
            'dcterms:bibliographicCitation' => [$this->recordValue()],
        ], [], ['isPartial' => true, 'collectionAction' => 'append'])->getContent();

        $titles = $item->value('dcterms:title', ['all' => true]);
        $this->assertCount(1, $titles);
        $this->assertSame('My title', (string) $titles[0]);
        $this->assertSame('Clark, Walter', (string) $item->value('dcterms:creator'));
    }
}
