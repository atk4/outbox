<?php

declare(strict_types=1);

namespace Atk4\Outbox\Demos;

$isRootProject = file_exists(__DIR__ . '/../vendor/autoload.php');
/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require dirname(__DIR__, $isRootProject ? 1 : 4) . '/vendor/autoload.php';
if (!$isRootProject && !class_exists(\Atk4\Outbox\Tests\FakeMailer::class)) {
    throw new \Error('Demos can be run only if Atk4/Outbox is a root composer project or if dev files are autoloaded');
}

$loader->setClassMapAuthoritative(false);
$loader->setPsr4('Atk4\Outbox\Demos\\', __DIR__ . '/_includes');
unset($isRootProject, $loader);
