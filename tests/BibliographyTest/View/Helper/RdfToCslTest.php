<?php declare(strict_types=1);

namespace BibliographyTest\View\Helper;

use Bibliography\View\Helper\RdfToCsl;
use PHPUnit\Framework\TestCase;

/**
 * @see https://gitlab.com/Daniel-KM/Omeka-S-module-Bibliography/-/work_items/3
 * @see https://gitlab.com/Daniel-KM/Omeka-S-module-Bibliography/-/work_items/4
 */
class RdfToCslTest extends TestCase
{
    public function dateProvider(): array
    {
        return [
            'year' => ['1916', [[1916]]],
            'year-month' => ['2015-02', [[2015, 2]]],
            'full date' => ['2015-02-04', [[2015, 2, 4]]],
            'date time' => ['2015-02-04T10:20:30', [[2015, 2, 4]]],
            'date time zone' => ['2015-02-04T10:20:30+01:00', [[2015, 2, 4]]],
            'short year' => ['850', [[850]]],
            'negative year' => ['-50', [[-50]]],
            'interval slash' => ['1914/1918', [[1914], [1918]]],
            'interval dash' => ['1914-1918', [[1914], [1918]]],
            'interval dates' => ['1914-08-03/1918-11-11', [[1914, 8, 3], [1918, 11, 11]]],
            'text with year' => ['vers 1916', [[1916]]],
            'open interval' => ['1914/..', [[1914]]],
            'invalid month' => ['2015-13-01', [[2015]]],
        ];
    }

    /**
     * @dataProvider dateProvider
     */
    public function testDateToCsl(string $date, array $expected): void
    {
        $result = (new RdfToCsl())->dateToCsl($date);
        $this->assertNotNull($result);
        $this->assertSame($expected, $result->{'date-parts'});
        $this->assertFalse(isset($result->circa));
    }

    public function testApproximateDate(): void
    {
        $result = (new RdfToCsl())->dateToCsl('1916~');
        $this->assertSame([[1916]], $result->{'date-parts'});
        $this->assertTrue($result->circa);
    }

    public function testDateWithoutYear(): void
    {
        $this->assertNull((new RdfToCsl())->dateToCsl(''));
        $this->assertNull((new RdfToCsl())->dateToCsl('unknown'));
    }
}
