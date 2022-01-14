<?php

namespace Tixel\AbTest\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Tixel\AbTest\Commands\FindActiveTests;
use Tixel\AbTest\Components\AbTestGA;
use Tixel\AbTest\Components\AbTestSegment;
use Tixel\AbTest\AbTest;

class AbTestServiceProvider extends ServiceProvider {
    public function boot()
    {
        $this->commands([
            FindActiveTests::class,
        ]);

        Blade::component('abtest-ga-event', AbTestGA::class);
        Blade::component('abtest-segment-event', AbTestSegment::class);
        Blade::directive('abTest', function($expression) {
            return "<?php echo abTest($expression); ?>";
        });
    }

    public function register()
    {
        $this->app->bind(AbTest::class, function (Application $app) {
            $version = $app['request']->query(AbTest::COOKIE_NAME, $app['request']->cookie(AbTest::COOKIE_NAME));

            return new AbTest($version);
        });
    }
}
