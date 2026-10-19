<?php declare(strict_types=1);

namespace BibliographyTest\DataType;

use CommonTest\AbstractTestCase;
use ReflectionClass;

/**
 * The fallback parent of Bibliography must not replace the one of ValueSuggest.
 *
 * The check is valid only when the parent class is not loaded yet, so this test
 * should run first: keep its name sorted before the other tests.
 */
class AbstractBibliographyDataTypeTest extends AbstractTestCase
{
    public function testParentIsTheOneOfValueSuggestWhenAvailable(): void
    {
        $isLoaded = class_exists('ValueSuggest\DataType\AbstractDataType', false);

        // Load a data type of Bibliography first, as in the list of data types.
        $this->getService('Omeka\DataTypeManager')->get('valuesuggest:doi:works');

        $file = (new ReflectionClass('ValueSuggest\DataType\AbstractDataType'))->getFileName();
        if (class_exists('ValueSuggest\Module', false)) {
            $this->assertStringNotContainsString('/Bibliography/', $file);
            $this->assertFalse($isLoaded, 'The parent class was loaded before the test, so the regression cannot be checked.');
        } else {
            $this->assertSame(OMEKA_PATH . '/modules/Bibliography/src/DataType/AbstractDataType.php', $file);
        }
    }
}
