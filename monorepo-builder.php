<?php

declare(strict_types=1);

use Symplify\MonorepoBuilder\Config\MBConfig;

return static function (MBConfig $mbConfig): void {
    $mbConfig->packageDirectories([__DIR__ . '/lib']);
    $mbConfig->packageDirectoriesExcludes([
        __DIR__ . '/lib/Rozier/src',
    ]);
};
