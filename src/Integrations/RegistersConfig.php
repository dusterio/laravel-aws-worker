<?php

namespace Dusterio\AwsWorker\Integrations;

/**
 * Trait RegistersConfig
 * @package Dusterio\AwsWorker\Integrations
 */
trait RegistersConfig
{

    /**
     * Registers the config file for the package.
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__ . '/../assets/config/aws-worker.php' => config_path('aws-worker.php'),
                ], 'aws-worker');
    }

}
