#!/usr/bin/env php
<?php
namespace App\Command;
require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use App\Command\CrawlCommand;
use App\Command\AnalyzeCommand; // ✅ Add this line

$application = new Application();
$application->add(new CrawlCommand());
$application->add(new AnalyzeCommand()); // ✅ Add this line

$application = new Application('SEO Mapper CLI', '0.1');
$application->add(new CrawlCommand());
$application->add(new AnalyzeCommand());
$application->run();