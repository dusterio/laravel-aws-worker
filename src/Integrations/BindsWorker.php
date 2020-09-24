<?php

namespace Dusterio\AwsWorker\Integrations;

use Dusterio\AwsWorker\Wrappers\WorkerInterface;
use Dusterio\AwsWorker\Wrappers\DefaultWorker;
use Dusterio\AwsWorker\Wrappers\Laravel53Worker;
use Dusterio\AwsWorker\Wrappers\Laravel6Worker;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Queue\Worker;

/**
 * Class BindsWorker
 * @package Dusterio\AwsWorker\Integrations
 */
trait BindsWorker
{
    /**
     * @var array
     */
    protected $workerImplementations = [
        '5\.[345678]\.\d+' => Laravel53Worker::class,
        '[678]\.\d+\.\d+' => Laravel6Worker::class
    ];

    /**
     * @param $version
     * @return mixed
     */
    protected function findWorkerClass($version)
    {
        foreach ($this->workerImplementations as $regexp => $class) {
            if (preg_match('/' . $regexp . '/', $version)) return $class;
        }

        return DefaultWorker::class;
    }

    /**
     * @return void
     */
    protected function bindWorker()
    {
        // If Laravel version is 6 or above then the worker bindings change. So we initiate it here
        if ($this->app->version() >= 6) {
            $this->app->singleton(Worker::class, function () {
                $isDownForMaintenance = function () {
                    return $this->app->isDownForMaintenance();
                };

                return new Worker(
                    $this->app['queue'],
                    $this->app['events'],
                    $this->app[ExceptionHandler::class],
                    $isDownForMaintenance
                );
            });
        }

        $this->app->bind(WorkerInterface::class, $this->findWorkerClass($this->app->version()));
    }
}
