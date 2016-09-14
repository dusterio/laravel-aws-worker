<?php

namespace Dusterio\AwsWorker\Jobs;

use Illuminate\Queue\Jobs\Job;
use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\Job as JobContract;

class AwsJob extends Job implements JobContract
{
    /**
     * The Amazon SQS job instance.
     *
     * @var array
     */
    protected $job;

    /**
     * Create a new job instance.
     *
     * @param  \Illuminate\Container\Container  $container
     * @param  string  $queue
     * @param  array   $job
     */
    public function __construct(Container $container,
                                $queue,
                                array $job)
    {
        $this->job = $job;
        $this->queue = $queue;
        $this->container = $container;
    }

    /**
     * Fire the job.
     *
     * @return void
     */
    public function fire()
    {
        if (method_exists($this, 'resolveAndFire')) {
            $this->resolveAndFire(json_decode($this->getRawBody(), true));
            return;
        }

        parent::fire();
    }

    /**
     * Get the raw body string for the job.
     *
     * @return string
     */
    public function getRawBody()
    {
        return $this->job['Body'];
    }

    /**
     * Actually, AWS will do this for us, we just need to mark the job as deleted
     *
     * @return void
     */
    public function delete()
    {
        parent::delete();
    }

    /**
     * AWS daemon will do this for us
     *
     * @param  int   $delay
     * @return void
     */
    public function release($delay = 0)
    {
        parent::release($delay);
    }

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    public function attempts()
    {
        return (int) $this->job['Attributes']['ApproximateReceiveCount'];
    }

    /**
     * Get the job identifier.
     *
     * @return string
     */
    public function getJobId()
    {
        return $this->job['MessageId'];
    }

    /**
     * Get the IoC container instance.
     *
     * @return \Illuminate\Container\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * We don't need an underlying SQS client instance.
     *
     * @return \Aws\Sqs\SqsClient
     */
    public function getSqs()
    {
        return null;
    }

    /**
     * Get the underlying raw SQS job.
     *
     * @return array
     */
    public function getSqsJob()
    {
        return $this->job;
    }
}
