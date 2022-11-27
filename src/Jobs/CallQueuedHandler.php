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
        if (property_exists($command, 'class') && $this->hasExpired($command->class, $job->timestamp())) {
            throw new ExpiredJobException("Job {$command->class} has already expired");
        }

        return parent::dispatchThroughMiddleware($job, $command);
    }

    /**
     * @param $className
     * @param $queuedAt
     * @return bool
     */
    protected function hasExpired($className, $queuedAt) {
        if (! property_exists($className, 'retention')) {
            return false;
        }

        return time() > $queuedAt + $className::$retention;
    }
}
