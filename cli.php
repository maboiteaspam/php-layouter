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
use \Symfony\Component\Console\Input\InputArgument;
use C\FS\LocalFs;

$app->register(new C\Provider\WatcherServiceProvider());

$console = new Cli('Silex - C Edition', '0.1');

#region Command lines declaration
$console
    ->register('cache:init')
    ->setDescription('Generate fs cache')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
//        $registries = [
//            'assets.fs'=> $app['assets.fs']->registry,
//            'layout.fs'=> $app['layout.fs']->registry,
//            'modern.fs'=> $app['modern.fs']->registry,
//            'intl.fs'=> $app['intl.fs']->registry,
//            'capsule.schema'=> $app['capsule.schema']->registry,
//        ];
        $watcheds = $app['watchers.watched'];

        foreach ($watcheds as $watched) {
            /* @var $watched \C\Watch\WatchedInterface */
            $watched->clearCache();
        }

        foreach ($watcheds as $watched) {
            /* @var $watched \C\Watch\WatchedInterface */
            $watched->resolveRuntime();
        }

        foreach ($watcheds as $watched) {
            /* @var $watched \C\Watch\WatchedInterface */
            $dump = $watched->build()->saveToCache();
            echo $watched->getName()." signed with ".$dump['signature']."\n";
        }

//
//
//        foreach ($registries as $name=>$registry) {
//            /* @var $registry \C\FS\Registry */
//            $registry->clearCached();
//        }
//        $app['capsule.schema']->loadSchemas();
//        foreach ($registries as $name=>$registry) {
//            /* @var $registry \C\FS\Registry */
//            $dump = $registry->build()->saveToCache();
//            echo "$name signed with ".$dump['signature']."\n";
//        }
    })
;
$console
    ->register('cache:update')
    ->setDefinition([
        new InputArgument('change', InputArgument::REQUIRED, 'Type of change'),
        new InputArgument('file', InputArgument::REQUIRED, 'The path changed'),
    ])
    ->setDescription('Update fs cache')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {

        $file = $input->getArgument('file');
        $change = $input->getArgument('change');

        $watcheds = $app['watchers.watched'];

        foreach ($watcheds as $watched) {
            /* @var $watched \C\Watch\WatchedInterface */
            $watched->loadFromCache();
        }

        foreach ($watcheds as $watched) {
            /* @var $watched \C\Watch\WatchedInterface */
            if ($watched->changed($change, $file)) {
                \C\Misc\Utils::stderr($watched->getName()." updated");
            } else {
                \C\Misc\Utils::stderr("not updated");
            }
        }
    })
;
$console
    ->register('fs-cache:dump')
    ->setDescription('Show FS cache paths')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
        $res = [];

        $watcheds = $app['watchers.watched'];

        foreach ($watcheds as $watched) {
            /* @var $watched \C\Watch\WatchedInterface */
            $watched->resolveRuntime();
        }

        foreach ($watcheds as $watched) {
            /* @var $watched \C\Watch\WatchedInterface */
            $dump = $watched->dump();
            if ($dump) $res[] = $dump;
        }
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
