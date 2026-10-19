<?php declare(strict_types=1);

namespace Bibliography\Stdlib;

use Seboettg\CiteProc\Style\Options\PageRangeFormats;

class StyleSheet
{
    /**
     * Load a csl style, fixed for the options not managed by CiteProc.
     *
     * The styles may use options more recent than the ones managed by CiteProc,
     * for example the page range format "chicago-16", so CiteProc fails. The
     * unknown page range formats are replaced by the nearest managed one.
     *
     * @throws \Seboettg\CiteProc\Exception\CiteProcException
     */
    public static function load(string $style): string
    {
        $xml = @\Seboettg\CiteProc\StyleSheet::loadStyleSheet($style);
        return preg_replace_callback(
            '~page-range-format="([^"]*)"~',
            fn ($matches) => PageRangeFormats::isValid($matches[1])
                ? $matches[0]
                : 'page-range-format="' . (strpos($matches[1], 'chicago') === 0 ? 'chicago' : 'expanded') . '"',
            $xml
        );
    }
}
