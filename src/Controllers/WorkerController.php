<?php

namespace Dusterio\AwsWorker\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Console\Scheduling\Schedule;

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
     * @return array
     */
    public function queue(Request $request)
    {
        return [
            'code' => 200,
            'message' => 'Yo'
        ];
    }
}
