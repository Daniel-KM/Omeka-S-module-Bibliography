<?php declare(strict_types=1);

namespace BibliographyTest\Stdlib;

use Bibliography\Stdlib\StyleSheet;
use PHPUnit\Framework\TestCase;
use Seboettg\CiteProc\CiteProc;

class StyleSheetTest extends TestCase
{
    public function styleProvider(): array
    {
        return [
            ['chicago-author-date'],
            ['chicago-notes-bibliography'],
            ['apa'],
            ['modern-language-association'],
        ];
    }

    /**
     * @dataProvider styleProvider
     */
    public function testStyleIsRendered(string $style): void
    {
        $xml = StyleSheet::load($style);
        $this->assertStringNotContainsString('page-range-format="chicago-16"', $xml);

        $item = (object) [
            'id' => 'a',
            'type' => 'article-journal',
            'title' => 'Ballots for Both',
            'container-title' => 'Journal',
            'page' => '321-328',
            'author' => [(object) ['family' => 'Clark', 'given' => 'Walter']],
            'issued' => (object) ['date-parts' => [[1916]]],
        ];
        $citation = strip_tags((new CiteProc($xml, 'en-US'))->render([$item], 'bibliography'));
        $this->assertStringContainsString('Clark', $citation);
        $this->assertStringContainsString('1916', $citation);
    }

    public function testValidPageRangeFormatIsKept(): void
    {
        $this->assertStringContainsString('page-range-format="expanded"', StyleSheet::load('apa'));
    }
}
