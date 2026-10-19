<?php declare(strict_types=1);

namespace BibliographyTest;

use CommonTest\AbstractTestCase;

/**
 * Expansion of the existing values "collected record" during upgrade.
 */
class UpgradeTest extends AbstractTestCase
{
    protected array $itemIds = [];

    protected array $itemSetIds = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->login();
    }

    /**
     * Authenticate, in particular after a clear of the entity manager, that
     * detaches the user.
     */
    protected function login(): void
    {
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
        foreach ($this->itemSetIds as $id) {
            $this->getService('Omeka\ApiManager')->delete('item_sets', $id);
        }
        parent::tearDown();
    }

    /**
     * Include the upgrade script with "require": the module includes it with
     * "require_once", so it would be run only once by process.
     */
    protected function runUpgrade(string $oldVersion, string $newVersion): void
    {
        $services = $this->getServiceManager();
        $module = new \Bibliography\Module();
        $module->setServiceLocator($services);
        $include = function (string $oldVersion, string $newVersion, $services): void {
            require dirname(__DIR__, 2) . '/data/scripts/upgrade.php';
        };
        \Closure::bind($include, $module, \Bibliography\Module::class)($oldVersion, $newVersion, $services);
    }

    public function testExistingRecordValuesAreExpanded(): void
    {
        $easyMeta = $this->getEasyMeta();
        $api = $this->getService('Omeka\ApiManager');
        $connection = $this->getService('Omeka\Connection');
        $entityManager = $this->getService('Omeka\EntityManager');

        $record = [
            'o:resource_class' => ['o:id' => $easyMeta->resourceClassId('bibo:Book')],
            'dcterms:title' => [['type' => 'literal', 'property_id' => $easyMeta->propertyId('dcterms:title'), '@value' => 'Ballots for Both']],
            'dcterms:creator' => [['type' => 'literal', 'property_id' => $easyMeta->propertyId('dcterms:creator'), '@value' => 'Clark, Walter']],
            'bibo:doi' => [['type' => 'uri', 'property_id' => $easyMeta->propertyId('bibo:doi'), '@id' => 'https://doi.org/10.1000/182', 'o:label' => '10.1000/182']],
        ];

        $itemSet = $api->create('item_sets', [])->getContent();
        $this->itemSetIds[] = $itemSet->id();
        $linked = $api->create('items', [])->getContent();
        $this->itemIds[] = $linked->id();

        // Store the values as in previous versions, without the expansion on
        // save: create a simple uri, then convert it directly in the database.
        $item = $api->create('items', [
            'o:item_set' => [['o:id' => $itemSet->id()]],
            'dcterms:relation' => [['type' => 'resource', 'property_id' => $easyMeta->propertyId('dcterms:relation'), 'value_resource_id' => $linked->id()]],
            'dcterms:subject' => [[
                'type' => 'literal',
                'property_id' => $easyMeta->propertyId('dcterms:subject'),
                '@value' => 'Suffrage',
                '@annotation' => [
                    'dcterms:source' => [['type' => 'literal', 'property_id' => $easyMeta->propertyId('dcterms:source'), '@value' => 'Annotation']],
                ],
            ]],
            'dcterms:title' => [['type' => 'literal', 'property_id' => $easyMeta->propertyId('dcterms:title'), '@value' => 'My title']],
            'dcterms:description' => [['type' => 'literal', 'property_id' => $easyMeta->propertyId('dcterms:description'), '@value' => 'Kept']],
            'dcterms:bibliographicCitation' => [
                ['type' => 'uri', 'property_id' => $easyMeta->propertyId('dcterms:bibliographicCitation'), '@id' => 'https://example.org/record', 'o:label' => 'Clark, Walter. 1916. Ballots for Both.'],
                ['type' => 'literal', 'property_id' => $easyMeta->propertyId('dcterms:bibliographicCitation'), '@value' => 'Other citation'],
            ],
        ])->getContent();
        $this->itemIds[] = $item->id();
        $connection->executeStatement(
            'UPDATE `value` SET `type` = :type, `uri` = :uri WHERE `resource_id` = :id AND `uri` = :old',
            ['type' => 'valuesuggest:doi:works:record', 'uri' => json_encode($record), 'id' => $item->id(), 'old' => 'https://example.org/record']
        );
        $entityManager->clear();
        $this->login();

        $this->runUpgrade('3.4.12', '3.4.13');
        $entityManager->clear();
        $this->login();

        $item = $api->read('items', $item->id())->getContent();
        $messages = $this->getControllerPlugin('messenger')->get();
        $warnings = array_map('strval', $messages[\Omeka\Mvc\Controller\Plugin\Messenger::WARNING] ?? []);

        if (!class_exists('ValueSuggest\Module', false)) {
            // The resource is not modified and listed to be saved later.
            $this->assertNotEmpty(array_filter($warnings, fn ($message) => strpos($message, '#' . $item->id()) !== false));
            $this->assertNull($item->value('dcterms:creator'));
            $this->assertSame('My title', (string) $item->value('dcterms:title'));
            return;
        }

        $this->assertSame('My title', (string) $item->value('dcterms:title'));
        $this->assertSame('Kept', (string) $item->value('dcterms:description'));
        $this->assertSame('Clark, Walter', (string) $item->value('dcterms:creator'));
        $this->assertSame('https://doi.org/10.1000/182', $item->value('bibo:doi')->uri());
        $this->assertSame('bibo:Book', $item->resourceClass()->term());

        // The other data are kept.
        $this->assertSame([$itemSet->id()], array_keys($item->itemSets()));
        $this->assertSame($linked->id(), $item->value('dcterms:relation')->valueResource()->id());
        $annotation = $item->value('dcterms:subject')->valueAnnotation();
        $this->assertNotNull($annotation);
        $this->assertSame('Annotation', (string) $annotation->value('dcterms:source'));

        $citations = $item->value('dcterms:bibliographicCitation', ['all' => true]);
        $this->assertCount(2, $citations);
        $this->assertSame('valuesuggest:doi:works:record', $citations[0]->type());
        $this->assertSame('https://doi.org/10.1000/182', $citations[0]->uri());
        $this->assertSame('Clark, Walter. 1916. Ballots for Both.', $citations[0]->value());
        $this->assertSame('Other citation', (string) $citations[1]);

        $this->assertNotEmpty(array_filter($warnings, fn ($message) => strpos($message, 'dcterms:title') !== false));

        // A second run does not change anything.
        $this->runUpgrade('3.4.12', '3.4.13');
        $entityManager->clear();
        $this->login();
        $item = $api->read('items', $item->id())->getContent();
        $this->assertCount(1, $item->value('dcterms:creator', ['all' => true]));
        $this->assertCount(2, $item->value('dcterms:bibliographicCitation', ['all' => true]));
    }
}
