<?php

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

    public function format($resources, $output = null, array $options = [])
    {
        $options['template'] = $this->template;
        return parent::format($resources, $output, $options);
    }

    protected function initializeOutput()
    {
        parent::initializeOutput();
        // Prepend the utf-8 bom.
        if (!$this->hasError) {
            fwrite($this->handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
        }
        return $this;
    }
}
