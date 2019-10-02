<?php

namespace Dusterio\AwsWorker\Integrations;

use Dusterio\AwsWorker\Wrappers\WorkerInterface;
use Dusterio\AwsWorker\Wrappers\DefaultWorker;
use Dusterio\AwsWorker\Wrappers\Laravel53Worker;
use Dusterio\AwsWorker\Wrappers\Laravel6Worker;

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
        '6\.[01]\.\d+' => Laravel6Worker::class
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
        $this->app->bind(WorkerInterface::class, $this->findWorkerClass($this->app->version()));
    }
}
