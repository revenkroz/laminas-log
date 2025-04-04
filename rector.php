<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPreparedSets(codeQuality: true, codingStyle: true)
    ->withPhpSets()
    ->withPaths([
                    __DIR__ . '/src',
                ])
    ->withRootFiles();
