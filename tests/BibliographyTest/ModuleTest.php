<?php declare(strict_types=1);

namespace BibliographyTest;

use Bibliography\Module;
use CommonTest\AbstractTestCase;
use Omeka\Module\Exception\ModuleCannotInstallException;

class ModuleTest extends AbstractTestCase
{
    /**
     * The install must stop with a message, not a fatal error, when Common is
     * too old.
     *
     * @see https://github.com/Daniel-KM/Omeka-S-module-Bibliography/issues/4
     */
    public function testPreInstallWithOldCommonThrowsMessage(): void
    {
        $module = new class extends Module {
            public function checkModuleActiveVersion(string $module, ?string $version = null): bool
            {
                return false;
            }
        };
        $module->setServiceLocator($this->getServiceManager());

        $this->expectException(ModuleCannotInstallException::class);
        $this->expectExceptionMessage('Common');
        (new \ReflectionMethod($module, 'preInstall'))->invoke($module);
    }
}
