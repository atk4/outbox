<?php

declare(strict_types=1);

namespace Atk4\Outbox\Demos;

use Atk4\Data\Persistence;

date_default_timezone_set('UTC');

require_once __DIR__ . '/init-autoloader.php';

// collect coverage for HTTP tests 1/2
if (file_exists(__DIR__ . '/CoverageUtil.php') && !class_exists(\PHPUnit\Framework\TestCase::class, false)) {
    require_once __DIR__ . '/CoverageUtil.php';
    \CoverageUtil::start();
}

$app = new App();

// collect coverage for HTTP tests 2/2
if (file_exists(__DIR__ . '/CoverageUtil.php') && !class_exists(\PHPUnit\Framework\TestCase::class, false)) {
    $app->onHook(\Atk4\Ui\App::HOOK_BEFORE_EXIT, static function () {
        \CoverageUtil::saveData();
    });
}

try {
    /** @var Persistence|Persistence\Sql $db */
    require_once __DIR__ . '/init-db.php';

    $app->db = $db;
    unset($db);
} catch (\Throwable $throwable) {
    throw new \Atk4\Ui\Exception('Database error: ' . $throwable->getMessage());
}

$app->invokeInit();
