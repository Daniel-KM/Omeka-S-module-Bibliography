<?php declare(strict_types=1);

namespace Bibliography\Formatter;

use BulkExport\Formatter\AbstractViewFormatter;

class CitationTxt extends AbstractViewFormatter
{
    protected $label = 'citation (txt)';
    protected $extension = 'bib.txt';
    protected $mediaType = 'text/plain';

    protected $template = 'common/bulk-export-citation';

    public function format($resources, $output = null, array $options = []): self
    {
        $options['template'] = $this->template;
        return parent::format($resources, $output, $options);
    }

    protected function initializeOutput(): self
    {
        parent::initializeOutput();
        // Prepend the utf-8 bom.
        if (!$this->hasError) {
            fwrite($this->handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
        }
        return $this;
    }
}
