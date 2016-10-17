<?php

namespace Dusterio\AwsWorker\Controllers;

use Dusterio\AwsWorker\Exceptions\FailedJobException;
use Dusterio\AwsWorker\Exceptions\MalformedRequestException;
use Dusterio\AwsWorker\Jobs\AwsJob;
use Dusterio\AwsWorker\Wrappers\WorkerInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Queue\Worker;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Response;

class WorkerController extends LaravelController
{
    /**
     * @var array
     */
    protected $awsHeaders = [
        'X-Aws-Sqsd-Queue', 'X-Aws-Sqsd-Msgid', 'X-Aws-Sqsd-Receive-Count'
    ];

    /**
     * This method is nearly identical to ScheduleRunCommand shipped with Laravel, but since we are not interested
     * in console output we couldn't reuse it
     *
     * @param Container $laravel
     * @param Kernel $kernel
     * @param Schedule $schedule
     * @return array
     */
    public function schedule(Container $laravel, Kernel $kernel, Schedule $schedule)
    {
        $events = $schedule->dueEvents($laravel);
        $eventsRan = 0;
        $messages = [];

        foreach ($events as $event) {
            if (method_exists($event, 'filtersPass') && (new \ReflectionMethod($event, 'filtersPass'))->isPublic() && ! $event->filtersPass($laravel)) {
                continue;
            }

            $messages[] = 'Running: '.$event->getSummaryForDisplay();

            $event->run($laravel);

            ++$eventsRan;
        }

        if (count($events) === 0 || $eventsRan === 0) {
            $messages[] = 'No scheduled commands are ready to run.';
        }

        return $this->response($messages);
    }

    /**
     * @param Request $request
     * @param WorkerInterface $worker
     * @param Container $laravel
     * @return Response
     * @throws FailedJobException
     */
    public function queue(Request $request, WorkerInterface $worker, Container $laravel)
    {
        //$this->validateHeaders($request);
        $body = $this->validateBody($request, $laravel);

        $job = new AwsJob($laravel, $request->header('X-Aws-Sqsd-Queue'), [
            'Body' => $body,
            'MessageId' => $request->header('X-Aws-Sqsd-Msgid'),
            'ReceiptHandle' => false,
            'Attributes' => [
                'ApproximateReceiveCount' => $request->header('X-Aws-Sqsd-Receive-Count')
            ]
        ]);

        try {
            $worker->process(
                $request->header('X-Aws-Sqsd-Queue'), $job, [
                    'maxTries' => 0,
                    'delay' => 0
                ]
            );
        } catch (\Exception $e) {
            throw new FailedJobException('Worker failed executing the job', 0, $e);
        }

        return $this->response([
            'Processed ' . $job->getJobId()
        ]);
    }

    /**
     * @param Request $request
     * @throws MalformedRequestException
     */
    private function validateHeaders(Request $request)
    {
        foreach ($this->awsHeaders as $header) {
            if (! $this->hasHeader($request, $header)) {
                throw new MalformedRequestException('Missing AWS header: ' . $header);
            }
        }
    }

    /**
     * @param Request $request
     * @param $header
     * @return bool
     */
    private function hasHeader(Request $request, $header)
    {
        if (method_exists($request, 'hasHeader')) {
            return $request->hasHeader($header);
        }

        return $request->header($header, false);
    }

    /**
     * @param Request $request
     * @param Container $laravel
     * @return string
     * @throws MalformedRequestException
     */
    private function validateBody(Request $request, Container $laravel)
    {
        if (empty($request->getContent())) {
            throw new MalformedRequestException('Empty request body');
        }

        $job = json_decode($request->getContent(), true);
        if ($job === null) throw new MalformedRequestException('Unable to decode request JSON');

        if (isset($job['job']) && isset($job['data'])) return $request->getContent();

        // If the format is not the standard Laravel format, try to mimic it
        $queueId = explode('/', $request->header('X-Aws-Sqsd-Queue'));
        $queueId = array_pop($queueId);

        $class = (array_key_exists($queueId, $laravel['config']->get('sqs-plain.handlers')))
            ? $laravel['config']->get('sqs-plain.handlers')[$queueId]
            : $laravel['config']->get('sqs-plain.default-handler');

        return json_encode([
            'job' => $class . '@handle',
            'data' => $request->getContent()
        ]);
    }
    
    /**
     * @param array $messages
     * @param int $code
     * @return Response
     */
    private function response($messages = [], $code = 200)
    {
        return new Response(json_encode([
            'message' => $messages,
            'code' => $code
        ]), $code);
    }
}
