<?php

namespace Dusterio\AwsWorker\Jobs;

use Dusterio\AwsWorker\Exceptions\ExpiredJobException;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\CallQueuedHandler as LaravelHandler;

class CallQueuedHandler extends LaravelHandler {
    /**
     * Dispatch the given job / command through its specified middleware.
     *
     * @param  \Illuminate\Contracts\Queue\Job  $job
     * @param  mixed  $command
     * @return mixed
     */
    protected function dispatchThroughMiddleware(Job $job, $command)
    {
        if ($this->hasExpired($command, $job->timestamp())) {
            throw new ExpiredJobException("This job has already expired");
        }

        return parent::dispatchThroughMiddleware($job, $command);
    }

    /**
     * @param $command
     * @param $queuedAt
     * @return bool
     */
    protected function hasExpired($command, $queuedAt) {
        if (! property_exists($command, 'class')) {
            return false;
        }

        if (! property_exists($command->class, 'retention')) {
            return false;
        }

        return time() > $queuedAt + $command->class::$retention;
    }
}
