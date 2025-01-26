<?php

namespace Lwekuiper\StatamicMailchimp\Tests;

use Statamic\Facades\Site;
use Statamic\Facades\Addon;
use Statamic\Facades\Config;
use Statamic\Testing\AddonTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Lwekuiper\StatamicMailchimp\ServiceProvider;

abstract class TestCase extends AddonTestCase
{
    use WithFaker;

    protected string $addonServiceProvider = ServiceProvider::class;

    protected function setSites($sites)
    {
        Site::setSites($sites);

        Config::set('statamic.system.multisite', Site::hasMultiple());
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('statamic.mailchimp.store_directory', __DIR__.'/__fixtures__/resources/mailchimp');
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        // Assume the pro edition within tests
        $app['config']->set('statamic.editions.pro', true);

        // Statamic::pushCpRoutes(function () {
        //     return require_once realpath(__DIR__ . '/../routes/cp.php');
        // });

        // $app['config']->set('statamic.api.resources', [
        //     'collections' => true,
        //     'navs' => true,
        //     'taxonomies' => true,
        //     'assets' => true,
        //     'globals' => true,
        //     'forms' => true,
        //     'users' => true,
        // ]);
    }

    protected function assertEveryItemIsInstanceOf($class, $items)
    {
        if ($items instanceof \Illuminate\Support\Collection) {
            $items = $items->all();
        }

        $matches = 0;

        foreach ($items as $item) {
            if ($item instanceof $class) {
                $matches++;
            }
        }

        $this->assertEquals(count($items), $matches, 'Failed asserting that every item is an instance of '.$class);
    }
}
