<?php

namespace Dusterio\AwsWorker\Controllers;

use Dusterio\AwsWorker\Jobs\AwsJob;
use Illuminate\Http\Request;
use Illuminate\Queue\Jobs\SqsJob;
use Illuminate\Queue\Worker;
use Illuminate\Support\Facades\App;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Console\Scheduling\Schedule;
use Aws\Sqs\SqsClient;
use Illuminate\Support\Arr;

class WorkerController extends LaravelController
{
    /**
     * @param Request $request
     * @return array
     */
    public function schedule(Request $request)
    {
        $laravel = App::getInstance();

        // Istantiating the Console kernel causes schedule() method to load all console tasks
        App::make(Kernel::class);

        // The fresh instance of schedule now contains console tasks
        $schedule = App::make(Schedule::class);

        $events = $schedule->dueEvents($laravel);
        $eventsRan = 0;
        $messages = [];

        foreach ($events as $event) {
            if (! $event->filtersPass($laravel)) {
                continue;
            }

            $messages[] = 'Running: '.$event->getSummaryForDisplay();

            $event->run($laravel);

            ++$eventsRan;
        }

        if (count($events) === 0 || $eventsRan === 0) {
            $messages[] = 'No scheduled commands are ready to run.';
        }

        return [
            'code' => 200,
            'message' => $messages
        ];
    }

    /**
     * @param Request $request
     * @param Worker $worker
     * @return array
     */
    public function queue(Request $request, Worker $worker)
    {
        $connectionName = 'sqs';
        $laravel = App::getInstance();
        $config = array_merge([
            'version' => 'latest',
            'http' => [
                'timeout' => 60,
                'connect_timeout' => 60
            ]
        ], $laravel['config']["queue.connections.sqs"]);

        $client = new SqsClient($config, $config['queue'], Arr::get($config, 'prefix', ''));
        $job = new AwsJob($laravel, $client, $connectionName, ['Body' => '', 'ReceiptHandle' => 'ASASAS']);

        $worker->process(
            $connectionName, $job, 0, 0
        );

        return [
            'code' => 200,
            'message' => 'Yo'
        ];
    }
}
