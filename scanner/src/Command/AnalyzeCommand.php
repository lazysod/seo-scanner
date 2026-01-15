<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class AnalyzeCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('analyze')
            ->setDescription('Analyze crawl.json for SEO issues')
            ->addOption('json', null, InputOption::VALUE_OPTIONAL, 'Output results as JSON');
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $data = json_decode(file_get_contents('output/crawl.json'), true);

        if (!is_array($data)) {
            $output->writeln('<error>crawl.json is missing or invalid.</error>');
            return Command::FAILURE;
        }

        $missing = [];
        $titles = [];
        $metas = [];

        foreach ($data as $page) {
            $url = $page['url'];
            $title = trim($page['title'] ?? '');
            $meta = trim($page['meta_description'] ?? '');
            $h1 = trim($page['h1'] ?? '');

            if ($title === '' || $meta === '' || $h1 === '') {
                $missing[] = [
                    'url' => $url,
                    'missing_title' => $title === '',
                    'missing_meta' => $meta === '',
                    'missing_h1' => $h1 === ''
                ];
            }

            if ($title !== '') $titles[$title][] = $url;
            if ($meta !== '') $metas[$meta][] = $url;
        }

        $duplicates = [
            'titles' => array_filter($titles, fn($urls) => count($urls) > 1),
            'metas' => array_filter($metas, fn($urls) => count($urls) > 1),
        ];

        if ($input->getOption('json')) {
            $output->writeln(json_encode([
                'missing_tags' => $missing,
                'duplicate_titles' => $duplicates['titles'],
                'duplicate_metas' => $duplicates['metas']
            ], JSON_PRETTY_PRINT));
        } else {
            $output->writeln("🔍 Missing SEO Tags:");
            foreach ($missing as $m) {
                $output->writeln("- {$m['url']}");
                if ($m['missing_title']) $output->writeln("  ❌ Missing <title>");
                if ($m['missing_meta'])  $output->writeln("  ❌ Missing <meta name=\"description\">");
                if ($m['missing_h1'])    $output->writeln("  ❌ Missing <h1>");
            }

            $output->writeln("\n🔁 Duplicate Titles:");
            foreach ($duplicates['titles'] as $title => $urls) {
                $output->writeln("- \"$title\"");
                foreach ($urls as $u) $output->writeln("  ↳ $u");
            }

            $output->writeln("\n🔁 Duplicate Meta Descriptions:");
            foreach ($duplicates['metas'] as $meta => $urls) {
                $output->writeln("- \"$meta\"");
                foreach ($urls as $u) $output->writeln("  ↳ $u");
            }
        }

        return Command::SUCCESS;
    }
}