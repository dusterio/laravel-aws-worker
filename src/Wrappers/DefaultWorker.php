<?php

namespace Dusterio\AwsWorker\Wrappers;

use Illuminate\Queue\Worker;

/**
 * Class DefaultWorker
 * @package Dusterio\AwsWorker\Wrappers
 */
class DefaultWorker implements WorkerInterface
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
        $this->worker->process(
            $queue, $job, $options['maxTries'], $options['delay']
        );
    }
}
