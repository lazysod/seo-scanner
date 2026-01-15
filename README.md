# SEO Scanner

SEO Scanner is a PHP command-line tool for crawling websites and analyzing SEO data. It uses Symfony Console for CLI commands and outputs crawl and analysis results to the `output/` directory.

## Features
- Crawl a website and save the results as JSON or HTML
- Analyze crawled data for SEO insights
- Extensible and easy to use

## Requirements
- PHP 8.0 or higher
- Composer (for dependency management)

## Installation
1. Clone this repository or download the source code.
2. Install dependencies with Composer:
   ```bash
   composer install
   ```

## Usage
All commands are run from the `seo-scanner` directory using the CLI entry point:

```bash
php bin/seo-map.php [command] [options]
```

### Commands

#### 1. Crawl a Website
Crawl a website and save the results to the `output/` directory.

```bash
php bin/seo-map.php crawl [url] [--output=filename.json]
```
- `url`: The website URL to crawl (e.g., https://example.com)
- `--output`: (Optional) Output filename (default: crawl.json)

**Example:**
```bash
php bin/seo-map.php crawl https://example.com --output=eden_crawl.json
```

#### 2. Analyze a Crawl
Analyze a previously crawled JSON file for SEO insights.

```bash
php bin/seo-map.php analyze [input.json] [--report=report.html]
```
- `input.json`: The crawl data file to analyze
- `--report`: (Optional) Output HTML report filename (default: homepage.html)

**Example:**
```bash
php bin/seo-map.php analyze output/eden_crawl.json --report=homepage.html
```

## Output
- Crawled data is saved in the `output/` directory as JSON.
- Analysis reports are saved as HTML in the `output/` directory.

## Development
- Main source code is in `src/`
- Commands are in `src/Command/`
- Entry point is `bin/seo-map.php`

## License
See LICENSE file for details.
