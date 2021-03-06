<?php

namespace CascadiaPHP\Site;

use Dotenv\Dotenv;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Interop\Container\ContainerInterface;
use League\Container\ContainerInterface as LeagueContainerInterface;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use Psr\Container\ContainerInterface as PSRContainerInterface;
use Whoops\Handler\CallbackHandler;

class ServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{

    protected $provides = [
        LeagueContainerInterface::class,
        ContainerInterface::class,
        PSRContainerInterface::class,
    ];

    /**
     * Method will be invoked on registration of a service provider implementing
     * this interface. Provides ability for eager loading of Service Providers.
     *
     * @return void
     */
    public function boot()
    {
        // Load in error handling
        if (class_exists(\Whoops\Run::class, true)) {
            $whoops = new \Whoops\Run();
            if (getenv('ENVIRONMENT') !== 'live') {
                $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
            }
            $whoops->register();
        }

        // Load in .env
        try {
            // Load in additional version stuff
            $env = new Dotenv(__DIR__ . '/../', '.version');
            $env->load();

            // Load in the .env
            $env = new Dotenv(__DIR__ . '/../');
            $env->load();
            $env->required([
                'MAILCHIMP_LIST_ID',
                'MAILCHIMP_API_KEY',
                'URI',
                'DEBUG'
            ]);
        } catch (\Exception $e) {
        }
    }

    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     *
     * @return void
     */
    public function register()
    {
        $this->container->add(ContainerInterface::class, $this->container);
        $this->container->add(LeagueContainerInterface::class, $this->container);
        $this->container->add(PSRContainerInterface::class, $this->container);
    }
}
