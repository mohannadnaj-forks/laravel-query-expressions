<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Tpetry\QueryExpressions\Dev\AddStaticFromMethodRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__.'/../src',
    ]);

    $rectorConfig->disableParallel();

    $rectorConfig->rule(AddStaticFromMethodRector::class);
};
