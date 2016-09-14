<?php

namespace Dusterio\AwsWorker\Wrappers;

use Illuminate\Queue\Worker;
use Illuminate\Queue\WorkerOptions;

/**
 * Class Laravel53Worker
 * @package Dusterio\AwsWorker\Wrappers
 */
class Laravel53Worker implements WorkerInterface
{
    /**
     * DefaultWorker constructor.
     * @param Worker $worker
     */
    public function __construct(Worker $worker)
    {
        $this->worker = $worker;
    }

    /**
     * @param $queue
     * @param $job
     * @param array $options
     * @return void
     */
    public function process($queue, $job, array $options)
    {
        $workerOptions = new WorkerOptions($options['delay'], 128, 60, 3, $options['maxTries']);

        $this->worker->process(
            $queue, $job, $workerOptions
        );
    }
}
