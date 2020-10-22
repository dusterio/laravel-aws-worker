<?php

namespace Dusterio\AwsWorker\Integrations;

use Dusterio\AwsWorker\Wrappers\DefaultWorker;
use Dusterio\AwsWorker\Wrappers\Laravel53Worker;
use Dusterio\AwsWorker\Wrappers\WorkerInterface;
use Dusterio\PlainSqs\Sqs\Connector;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\QueueManager;

/**
 * Class CustomQueueServiceProvider
 * @package App\Providers
 */
class LaravelServiceProvider extends ServiceProvider
{
    use BindsWorker,
        RegistersConfig;

    /**
     * @return void
     */
    public function register()
    {
        if (!config('aws-worker.register_worker_routes')) return;

        $this->bindWorker();
        $this->addRoutes();
    }

    /**
     * @return void
     */
    protected function addRoutes()
    {
        $this->app['router']->post('/worker/schedule', 'Dusterio\AwsWorker\Controllers\WorkerController@schedule');
        $this->app['router']->post('/worker/queue', 'Dusterio\AwsWorker\Controllers\WorkerController@queue');
    }

    /**
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->registerConfig();
        }
        
        $this->app->singleton(QueueManager::class, function() {
            return new QueueManager($this->app);
        });
    }
}
