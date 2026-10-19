<?php declare(strict_types=1);

namespace BibliographyTest\View\Helper;

use CommonTest\AbstractTestCase;
use Omeka\Api\Representation\ItemRepresentation;

/**
 * @see https://gitlab.com/Daniel-KM/Omeka-S-module-Bibliography/-/work_items/2
 * @see https://gitlab.com/Daniel-KM/Omeka-S-module-Bibliography/-/work_items/3
 * @see https://gitlab.com/Daniel-KM/Omeka-S-module-Bibliography/-/work_items/4
 */
class CitationTest extends AbstractTestCase
{
    protected ?ItemRepresentation $item = null;

    public function setUp(): void
    {
        parent::setUp();
        $auth = $this->getService('Omeka\AuthenticationService');
        $adapter = $auth->getAdapter();
        $adapter->setIdentity('admin@example.com');
        $adapter->setCredential('root');
        $auth->authenticate();

        $this->item = $this->getService('Omeka\ApiManager')->create('items', [
            'dcterms:title' => [['type' => 'literal', 'property_id' => 1, '@value' => 'Ballots for Both']],
        ])->getContent();
    }

    public function tearDown(): void
    {
        if ($this->item) {
            $this->getService('Omeka\ApiManager')->delete('items', $this->item->id());
        }
        parent::tearDown();
    }

    protected function citation(array $options): string
    {
        return strip_tags($this->getViewHelper('citation')($this->item, $options));
    }

    protected function setDate(string $date, string $type = 'literal'): void
    {
        $this->item = $this->getService('Omeka\ApiManager')->update('items', $this->item->id(), [
            'dcterms:title' => [['type' => 'literal', 'property_id' => 1, '@value' => 'Ballots for Both']],
            'dcterms:date' => [['type' => $type, 'property_id' => 7, '@value' => $date]],
        ])->getContent();
    }

    public function testYearIsNotShifted(): void
    {
        $this->setDate('1916');
        $citation = $this->citation(['locale' => 'en-US', 'bibliographic' => true]);
        $this->assertStringContainsString('1916', $citation);
        $this->assertStringNotContainsString('1915', $citation);
    }

    public function testFullIsoDateIsKept(): void
    {
        $this->setDate('2015-02-04');
        $citation = $this->citation(['locale' => 'en-US', 'bibliographic' => true]);
        $this->assertStringContainsString('2015. February 4', $citation);
    }

    public function testAccessedDateUsesTheCitationLocale(): void
    {
        $citation = $this->citation(['locale' => 'fr-FR']);
        $months = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
        $this->assertStringContainsString('consulté le ' . (int) date('j') . ' ' . $months[date('n') - 1] . ' ' . date('Y'), $citation);
    }

    public function testAccessedDateInEnglish(): void
    {
        $citation = $this->citation(['locale' => 'en-US']);
        $this->assertStringContainsString('accessed ' . date('F j, Y'), $citation);
    }

    public function testAccessedDateWithUnknownLocaleFallsBack(): void
    {
        $citation = $this->citation(['locale' => 'xx-XX']);
        $this->assertStringContainsString(date('Y'), $citation);
        $this->assertStringContainsString('/items/' . $this->item->id(), $citation);
    }

    public function testNoAccessedDateForBibliographicCitation(): void
    {
        $citation = $this->citation(['locale' => 'fr-FR', 'bibliographic' => true]);
        $this->assertStringNotContainsString('consulté', $citation);
        $this->assertStringContainsString('Ballots for Both', $citation);
    }
}
