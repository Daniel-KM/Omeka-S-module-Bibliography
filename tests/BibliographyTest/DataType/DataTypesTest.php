<?php declare(strict_types=1);

namespace BibliographyTest\DataType;

use Bibliography\DataType\AbstractBibliographyDataType;
use CommonTest\AbstractTestCase;

class DataTypesTest extends AbstractTestCase
{
    protected function hasValueSuggest(): bool
    {
        return class_exists('ValueSuggest\Module', false);
    }

    protected function getBibliographyDataTypeNames(): array
    {
        $config = $this->getService('Config');
        $factories = $config['data_types']['factories'] ?? [];
        return array_keys(array_filter($factories, fn ($factory) => str_starts_with($factory, 'Bibliography\\')));
    }

    public function testDataTypesAreRegistered(): void
    {
        $names = $this->getBibliographyDataTypeNames();
        $this->assertNotEmpty($names);
        $dataTypeManager = $this->getService('Omeka\DataTypeManager');
        foreach ($names as $name) {
            $dataType = $dataTypeManager->get($name);
            $this->assertInstanceOf(AbstractBibliographyDataType::class, $dataType, $name);
            $this->assertSame($name, $dataType->getName());
            $this->assertNotEmpty($dataType->getLabel(), $name);
        }
    }

    public function testDataTypesOfValueSuggestAreValid(): void
    {
        if (!$this->hasValueSuggest()) {
            $this->markTestSkipped('Requires module ValueSuggest.');
        }
        $dataTypeManager = $this->getService('Omeka\DataTypeManager');
        $names = array_filter(
            $dataTypeManager->getRegisteredNames(),
            fn ($name) => str_starts_with($name, 'valuesuggest:')
        );
        $this->assertGreaterThan(count($this->getBibliographyDataTypeNames()), count($names));
        foreach ($names as $name) {
            $this->assertInstanceOf(
                \ValueSuggest\DataType\DataTypeInterface::class,
                $dataTypeManager->get($name),
                $name
            );
        }
    }

    public function testSuggestersImplementInterface(): void
    {
        if (!$this->hasValueSuggest()) {
            $this->markTestSkipped('Requires module ValueSuggest.');
        }
        $dataTypeManager = $this->getService('Omeka\DataTypeManager');
        foreach (['valuesuggest:doi:works', 'valuesuggest:doi:works:record', 'valuesuggest:isbn:id', 'valuesuggest:isbn:id:record'] as $name) {
            $this->assertInstanceOf(
                \ValueSuggest\Suggester\SuggesterInterface::class,
                $dataTypeManager->get($name)->getSuggester(),
                $name
            );
        }
    }

    public function testIsValid(): void
    {
        $dataType = $this->getService('Omeka\DataTypeManager')->get('valuesuggest:doi:works');
        $hasValueSuggest = $this->hasValueSuggest();
        $this->assertSame($hasValueSuggest, $dataType->isValid(['@id' => 'https://doi.org/10.1000/182']));
        $this->assertSame($hasValueSuggest, $dataType->isValid(['@value' => 'Title']));
        $this->assertFalse($dataType->isValid(['@id' => ' ']));
        $this->assertFalse($dataType->isValid([]));
    }
}
