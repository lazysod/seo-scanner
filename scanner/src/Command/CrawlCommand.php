<?php

namespace App\Command;

use App\Crawler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CrawlCommand extends Command
{
    protected static $defaultName = 'crawl';

    public function __construct()
    {
        parent::__construct(); // ✅ This is the missing piece
    }

protected function configure(): void
{
    $this
        ->setName('crawl') // ✅ This line is required
        ->setDescription('Crawl a website and extract SEO-relevant data.')
        ->addArgument('url', InputArgument::REQUIRED, 'The starting URL to crawl')
        ->addOption('depth', 'd', InputOption::VALUE_OPTIONAL, 'Maximum crawl depth', 2);
}

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $url = $input->getArgument('url');
        $depth = (int) $input->getOption('depth');

        $output->writeln("Starting crawl of {$url} to depth {$depth}...");

        $crawler = new Crawler();
        $crawler->crawl($url, $depth);

        $data = json_decode(file_get_contents('output/crawl.json'), true);

        if (!is_array($data)) {
            $output->writeln('<error>crawl.json is invalid or empty.</error>');
            return Command::FAILURE;
        }

        $output->writeln("Crawl complete. Found " . count($data) . " pages.");
        return Command::SUCCESS;
    }
}