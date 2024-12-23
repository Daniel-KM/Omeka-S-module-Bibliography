<?php declare(strict_types=1);

namespace Bibliography\Formatter;

use BulkExport\Formatter\AbstractViewFormatter;
use BulkExport\Traits\OpenDocumentTextTemplateTrait;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use PhpOffice\PhpWord;

class CitationOdt extends AbstractViewFormatter
{
    use OpenDocumentTextTemplateTrait;

    protected $label = 'citation (odt)';
    protected $extension = 'bib.odt';
    protected $mediaType = 'application/vnd.oasis.opendocument.text';

    protected $template = 'common/bulk-export-citation';

    /**
     * @var string
     */
    protected $filepath;

    public function format($resources, $output = null, array $options = []): self
    {
        if (!extension_loaded('zip') || !extension_loaded('xml')) {
            $this->services->get('Omeka\Logger')->err(
                'To process export to "{format}", the php extensions "zip" and "xml" are required.', // @translate
                ['format' => $this->getLabel()]
            );
            $this->hasError = false;
            $resources = false;
        }

        $options['template'] = $this->template;
        return parent::format($resources, $output, $options);
    }

    protected function initializeOutput(): self
    {
        $tempDir = $this->services->get('Config')['temp_dir'] ?: sys_get_temp_dir();
        $this->filepath = $this->isOutput
            ? $this->output
            // TODO Use Omeka factory for temp files.
            // TODO Use the method openToBrowser() too.
            // TODO Try php://output.
            // "php://temp" doesn't seem to work.
            : @tempnam($tempDir, 'omk_bib_');
        $this->initializeOpenDocumentText();
        return $this;
    }

    protected function writeResource(AbstractResourceEntityRepresentation $resource, $index): self
    {
        $conv = $this->converter;
        $value = $conv($resource, $index);
        $section = $this->openDocument->addSection(['breakType' => 'continuous']);
        $value = strip_tags($value);
        if (mb_strlen($value) < 1000) {
            $section->addText($value, 'recordMetadata', 'pRecordMetadata');
        } else {
            $this->logger->warn(
                'Skipped resource {resource_id}: it contains more than 1000 characters.', // @translate
                ['resource_id' => $resource->id()]
            );
        }
        $section->addTextBreak();
        return $this;
    }

    protected function finalizeOutput(): self
    {
        $objWriter = PhpWord\IOFactory::createWriter($this->openDocument, 'ODText');
        $objWriter->save($this->filepath);
        if (!$this->isOutput) {
            $this->content = file_get_contents($this->filepath);
            unlink($this->filepath);
        }
        return $this;
    }
}
