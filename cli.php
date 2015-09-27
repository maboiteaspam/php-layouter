#!/usr/bin/env php
<?php

/**
 * Tip: If the executing fails, try php -d display_errors script.php to check syntax mistakes.
 */

$app = require("bootstrap.php");


use Symfony\Component\Console\Application as Cli;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use C\FS\LocalFs;

$console = new Cli('Silex - C Edition', '0.1');


#region Command lines declaration
$console
    ->register('cache:init')
    ->setDescription('Generate fs cache')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
        $app['assets.fs']->registry->clearCached();
        $dump = $app['assets.fs']->registry->saveToCache();
        echo "assets.fs signed with ".$dump['signature']."\n";

        $app['layout.fs']->registry->clearCached();
        $dump = $app['layout.fs']->registry->saveToCache();
        echo "layout.fs signed with ".$dump['signature']."\n";

        $app['capsule.schema']->registry->clearCached();
        $app['capsule.schema']->loadSchemas();
        $dump = $app['capsule.schema']->registry->saveToCache();
        echo "capsule.schema signed with ".$dump['signature']."\n";
    })
;
$console
    ->register('fs-cache:dump')
    ->setDescription('Show FS cache paths')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
        $res = [];
        $app['capsule.schema']->loadSchemas();
        $res [] = $app['assets.fs']->registry->dump();
        $res [] = $app['layout.fs']->registry->dump();
        $res [] = $app['capsule.schema']->registry->dump();
        echo json_encode($res);
    })
;
$console
    ->register('http:bridge')
    ->setDescription('Generate http bridge')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
        $app['assets.bridger']->generate(
            $app['assets.bridge_file_path'],
            $app['assets.bridge_type'],
            $app['assets.fs']
        );
    })
;
$console
    ->register('db:init')
    ->setDescription('Generate http bridge')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
        $connections = $app['capsule.connections'];
        foreach ($connections as $connection => $options) {
            if ($options["driver"]==='sqlite') {
                if ($options["database"]!==':memory:') {
                    $exists = LocalFs::file_exists($options['database']);
                    if (!$exists) {
                        $dir = dirname($options["database"]);
                        if (!LocalFs::is_dir($dir)) LocalFs::mkdir($dir, 0700, true);
                        LocalFs::touch($options["database"]);
                    }
                }
            }
        }
        $app['capsule.schema']->loadSchemas();
        $app['capsule.schema']->cleanDb();
        $app['capsule.schema']->initDb();
    })
;
$console
    ->register('db:refresh')
    ->setDescription('Refresh db')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
        $app['capsule.schema']->loadSchemas();
        $app['capsule.schema']->refreshDb();
    })
;

#endregion

$app->boot();
$console->run();
