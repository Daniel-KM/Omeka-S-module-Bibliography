<?php
/**
 * The list of citations contains some thousand files. Prepare it one time.
 *
 * There are two lists: csl styles and csl locales.
 */

$dirPath = dirname(__DIR__, 2) . '/data/csl';
if (!file_exists($dirPath)) {
    @mkdir($dirPath);
}
if (!file_exists($dirPath) || !is_writeable($dirPath)) {
    exit('The dir /data/csl inside the module is not writeable. It is recommended to install the whole release.');
}

/**
 * CSL Styles
 */

$libraryPath = dirname(__DIR__, 2) . '/vendor/citation-style-language/styles';
$dirPath = $libraryPath;

/**
 * This fix is used only when the library is set directly in composer, not when
 * loaded from seboettg/citeproc-php
 *
// Unlike deprecated citation-style-language/styles-distribution, the files are
// in a subdirectory named with the version, so get this dir name.
$directories = glob($dirPath . '/*' , GLOB_ONLYDIR);
foreach ($directories as $directory) {
    if (strpos(basename($directory), 'styles-0.0.') === 0) {
        $dirPath = $directory;
        break;
    }
}
*/

/**
 * TODO Create an autocomplete: the sub-dir is too big.
try {
    $directory = new \RecursiveDirectoryIterator($dirPath, \RecursiveDirectoryIterator::SKIP_DOTS);
    $iterator = new \RecursiveIteratorIterator($directory);
} catch (\Exception $e) {
    die('Enable to read the directory of csl styles.')
    $iterator = [];
}
foreach ($iterator as $filepath => $file) {
    if ($file->getExtension() === 'csl') {
        $name = pathinfo($filepath, PATHINFO_FILENAME);
        $citationStyles[$name] = $name;
    }
}
*/

try {
    $iterator = new \DirectoryIterator($dirPath);
} catch (\Exception $e) {
    exit('Enable to read the directory of csl styles.');
}

$citationStyles = [];
foreach ($iterator as $file) {
    if ($file->isFile() && !$file->isDot() && $file->isReadable() && $file->getExtension() === 'csl') {
        $name = pathinfo($file->getFilename(), PATHINFO_FILENAME);
        $citationStyles[$name] = $name;
    }
}

asort($citationStyles);

$outputFile = dirname(__DIR__, 2) . '/data/csl/csl-styles.php';
try {
    file_put_contents($outputFile, "<?php\nreturn " . var_export($citationStyles, true) . ";\n");
} catch (\Exception $e) {
    exit('Enable to read the directory of csl styles.');
}

/**
 * CSL Locales
 */

$libraryPath = dirname(__DIR__, 2) . '/vendor/citation-style-language/locales';
$dirPath = $libraryPath;

try {
    $iterator = new \RecursiveDirectoryIterator($dirPath, \RecursiveDirectoryIterator::SKIP_DOTS);
} catch (\Exception $e) {
    exit('Enable to read the directory of csl locales.');
}

$citationLocales = [];
foreach ($iterator as $filepath => $file) {
    if ($file->getExtension() === 'xml') {
        // Remove "locales-" to keep only the locale.
        $name = substr(pathinfo($filepath, PATHINFO_FILENAME), 8);
        $citationLocales[$name] = $name;
    }
}

asort($citationLocales);

$outputFile = dirname(__DIR__, 2) . '/data/csl/csl-locales.php';
try {
    file_put_contents($outputFile, "<?php\nreturn " . var_export($citationLocales, true) . ";\n");
} catch (\Exception $e) {
    exit('Enable to save data in the directory of csl locales.');
}
