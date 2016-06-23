<?php

namespace Dusterio\AwsWorker\Integrations;

use Dusterio\PlainSqs\Sqs\Connector;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\QueueManager;

/**
 * Class CustomQueueServiceProvider
 * @package App\Providers
 */
class LumenServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() == 'production') return;

        $this->addRoutes();
    }

    /**
     * @return void
     */
    protected function addRoutes()
    {
        $this->app->post('/worker/schedule', 'Dusterio\AwsWorker\Controllers\WorkerController@schedule');
        $this->app->post('/worker/queue', 'Dusterio\AwsWorker\Controllers\WorkerController@queue');
    }

    /**
     * @return void
     */
    public function boot()
    {
        $this->app->singleton(QueueManager::class, function() {
            return new QueueManager($this->app);
        });
    }
}