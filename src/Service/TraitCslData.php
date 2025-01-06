<?php declare(strict_types=1);

namespace Bibliography\Service;

trait TraitCslData
{
    public function getCitationStyles(): array
    {
        $filepath = dirname(__DIR__, 2) . '/data/csl/csl-styles.php';
        return file_exists($filepath)
            ? include $filepath
            : [];
    }

    public function getCitationLocales(): array
    {
        $filepath = dirname(__DIR__, 2) . '/data/csl/csl-locales.php';
        return file_exists($filepath)
            ? include $filepath
            : [];
    }
}
