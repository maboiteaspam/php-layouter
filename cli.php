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

$console = new Cli('Silex - C Edition', '0.1');


#region Command lines declaration
$console
    ->register('cache:init')
    ->setDescription('Generate fs cache')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
        $registries = [
            'assets.fs'=> $app['assets.fs']->registry,
            'layout.fs'=> $app['layout.fs']->registry,
            'modern.fs'=> $app['modern.fs']->registry,
            'capsule.schema'=> $app['capsule.schema']->registry,
        ];

        foreach ($registries as $name=>$registry) {
            /* @var $registry \C\FS\Registry */
            $registry->clearCached();
        }
        $app['capsule.schema']->loadSchemas();
        foreach ($registries as $name=>$registry) {
            /* @var $registry \C\FS\Registry */
            $dump = $app['assets.fs']->registry->build()->saveToCache();
            echo "$name signed with ".$dump['signature']."\n";
        }
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

        $updated = [];
        $assets = [
            'assets.fs'=>$app['assets.fs']->registry,
            'layout.fs'=>$app['layout.fs']->registry,
            'modern.fs'=>$app['modern.fs']->registry,
            'capsule.schema'=> $app['capsule.schema']->registry,
        ];

        foreach ($assets as $registry) {
            /* @var $registry \C\FS\Registry */
            $registry->loadFromCache();
        }

        if ($change==='unlink'){
            foreach ($assets as $name=>$registry) {
                /* @var $registry \C\FS\Registry */
                $item = $registry->get($file);
                if ($item) {
                    \C\Misc\Utils::stdout("removed from $name");
                    $registry->removeItem($file);
                    $updated[] = $name;
                }
            }
        } else if($change==='change'){
            foreach ($assets as $name=>$registry) {
                /* @var $registry \C\FS\Registry */
                $item = $registry->get($file);
                if ($item) {
                    \C\Misc\Utils::stdout("updated in $name");
                    $registry->refreshItem($file);
                    $updated[] = $name;
                }
            }
        } else if($change==='add' || $change==='addDir'){
            foreach ($assets as $name=>$registry) {
                /* @var $registry \C\FS\Registry */
                if ($registry->isInRegisteredPaths($file)) {
                    \C\Misc\Utils::stdout("added to $name");
                    $registry->addItem($file);
                    $updated[] = $name;
                }
            }
        }

        if (!count($updated)) {
            \C\Misc\Utils::stderr("not updated");
        } else {
            foreach ($updated as $name) {
                $assets[$name]->saveToCache();
            }
        }
    })
;
$console
    ->register('fs-cache:dump')
    ->setDescription('Show FS cache paths')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
        $res = [];
        $app['capsule.schema']->loadSchemas();
        $assets = [
            'assets.fs'=>$app['assets.fs']->registry,
            'layout.fs'=>$app['layout.fs']->registry,
            'modern.fs'=>$app['modern.fs']->registry,
            'capsule.schema'=> $app['capsule.schema']->registry,
        ];
        foreach ($assets as $name=>$registry) {
            /* @var $registry \C\FS\Registry */
            $res [] = $registry->dump();
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
