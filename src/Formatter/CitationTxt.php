<?php declare(strict_types=1);

namespace Bibliography\Formatter;

use BulkExport\Formatter\AbstractViewFormatter;

class CitationTxt extends AbstractViewFormatter
{
    protected $label = 'citation (txt)';
    protected $extension = 'bib.txt';
    protected $responseHeaders = [
        'Content-type' => 'text/plain',
    ];
    protected $template = 'common/bulk-export-citation';

    public function format($resources, $output = null, array $options = []): \BulkExport\Formatter\FormatterInterface
    {
        $options['template'] = $this->template;
        return parent::format($resources, $output, $options);
    }

    protected function initializeOutput(): \BulkExport\Formatter\FormatterInterface
    {
        parent::initializeOutput();
        // Prepend the utf-8 bom.
        if (!$this->hasError) {
            fwrite($this->handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
        }
        return $this;
    }
}
