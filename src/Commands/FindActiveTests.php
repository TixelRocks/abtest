<?php

namespace Tixel\AbTest\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class FindActiveTests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'abtest:find-files {path?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find templates that have A/B tests running';

    protected $viewsPath;

    protected $expressions = [
        '/@?abTest\(\'(.+?)\'\)/',
        '/@?abTest\("(.+?)"\)/',
    ];

    /**
     * @return Collection
     * @throws \Exception
     */
    protected function views()
    {
        if (! file_exists($this->viewsPath) || ! is_dir($this->viewsPath)) {
            throw new \Exception("Path {$this->viewsPath} doesn't exist or isn't a folder");
        }

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->viewsPath));
        $views = collect([]);

        foreach ($iterator as $file) {
            if ($file->isDir() || ! preg_match('/\.blade\.php$/', $file->getPathname())) {
                continue;
            }

            $key = str_replace($this->viewsPath, '', $file->getPathname());
            $key = ltrim($key, '//');
            $key = preg_replace('/\.blade\.php$/', '', $key);
            $key = str_replace(DIRECTORY_SEPARATOR, '.', $key);

            $views->push([
                'key' => $key,
                'path' => $file->getPathname()
            ]);
        }

        $this->line("Discovered a total of {$views->count()} view templates in {$this->viewsPath}");

        return $views;
    }

    /**
     * @param $html
     * @return Collection
     */
    protected function extractStrings($html)
    {
        return collect($this->expressions)->reduce(function($carry, $expression) use ($html) {
            preg_match_all($expression, $html, $matches, PREG_OFFSET_CAPTURE);

            $matches = array_map(function($match) use ($html) {
                return [
                    'name' => $match[0],
                    'location' => substr_count(mb_substr($html, 0, $match[1]), PHP_EOL) + 1
                ];
            }, $matches[1]);

            return $carry->concat($matches);
        }, collect([]));
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->viewsPath = $this->argument('path') ?: resource_path('views');
        $views = $this->views();

        $texts = $views->reduce(function($carry, $view) {
            $contents = file_get_contents($view['path']);

            $this->extractStrings($contents)->each(function($string) use (&$carry, $view) {
                $carry->push(array_merge($string, [
                    'file' => str_replace(base_path(''), '', $view['path']),
                    'key' => $view['key']
                ]));
            });

            return $carry;
        }, collect([]))->groupBy('key');

        $this->table(
            ['File', 'A/B Test Name', 'Lines'],
            $texts->map(function($batch) {
                return [
                    'file' => $batch->first()['file'],
                    'name' => $batch->first()['name'],
                    'lines' => $batch->pluck('location')->implode(', ')
                ];
            })->toArray()
        );
    }
}
