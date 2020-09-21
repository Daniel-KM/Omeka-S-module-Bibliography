<?php

namespace Bibliography\Formatter;

use BulkExport\Formatter\AbstractViewFormatter;

class Citation extends AbstractViewFormatter
{
    protected $label = 'citation';
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
}
