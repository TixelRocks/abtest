<?php

namespace Tixel\AbTest\Providers;

use App\Http\AbTest;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Tixel\AbTest\Commands\FindActiveTests;

class AbTestServiceProvider extends ServiceProvider {
    public function boot()
    {
        $this->commands([
            FindActiveTests::class,
        ]);

        Blade::component('abtest-ga-event', App\View\Components\AbTestGA::class);
        Blade::component('abtest-segment-event', App\View\Components\AbTestSegment::class);
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
