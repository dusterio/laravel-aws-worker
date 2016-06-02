# laravel-aws-worker
[![Build Status](https://travis-ci.org/dusterio/laravel-aws-worker.svg)](https://travis-ci.org/dusterio/laravel-aws-worker)
[![Code Climate](https://codeclimate.com/github/dusterio/laravel-aws-worker/badges/gpa.svg)](https://codeclimate.com/github/dusterio/laravel-aws-worker/badges)
[![Total Downloads](https://poser.pugx.org/dusterio/laravel-aws-worker/d/total.svg)](https://packagist.org/packages/dusterio/laravel-aws-worker)
[![Latest Stable Version](https://poser.pugx.org/dusterio/laravel-aws-worker/v/stable.svg)](https://packagist.org/packages/dusterio/laravel-aws-worker)
[![Latest Unstable Version](https://poser.pugx.org/dusterio/laravel-aws-worker/v/unstable.svg)](https://packagist.org/packages/dusterio/laravel-aws-worker)
[![License](https://poser.pugx.org/dusterio/laravel-aws-worker/license.svg)](https://packagist.org/packages/dusterio/laravel-plain-sqs)

Run Laravel (or Lumen) tasks and queue listeners inside of AWS Elastic Beanstalk workers

## Overview

Laravel documentation recommends to use supervisor for queue workers and *IX cron for scheduled tasks. However, when deploying your application to AWS Elastic Beanstalk, neither option is available.

This package helps you run your Laravel (or Lumen) jobs in AWS worker environments.

![Standard Laravel queue flow](https://www.mysenko.com/images/queues-laravel.png)
![AWS Elastic Beanstalk flow](https://www.mysenko.com/images/queues-aws_eb.png)

## Scheduled tasks

You remember how Laravel documentation advised you to invoke the task scheduler? Right, by running ```php artisan schedule:run``` on regular basis, and to do that we had to add an entry to our cron file:

```bash
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

AWS doesn't allow you to run *IX commands or add cron tasks directly. Instead, you have to make regular HTTP (POST, to be precise) requests to your worker endpoint.

Add cron.yaml to the root folder of your application (this can be a part of your repo or you could add this file right before deploying to EB - the important thing is that this file is present at the time of deployment):

```yaml
version: 1
cron:
 - name: "schedule"
   url: "/worker/schedule"
   schedule: "* * * * *"
```

From now on, AWS will do POST /worker/schedule to your endpoint every minute - kind of the same effect we achieved when editing a UNIX cron file. The important difference here is that worker environment still has to run a web process in order to run scheduled tasks.
To protect web process from unauthorized calls, 'production' environment won't have special routes. Once again, your worker application **shouldn't have environment set to production** (use 'worker' or anything else).

Your scheduled tasks should be defined in ```App\Console\Kernel::class``` - just where they normally are in Laravel, eg.:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('inspire')
              ->everyMinute();
}
```

## Queued jobs: SQS

Normally Laravel has to poll SQS for new messages, but in case of AWS Elastic Beanstalk messages will come to us – inside of POST requests from AWS daemon. Therefore, we need a custom connection driver that 

## Dependencies

* PHP >= 5.5
* Laravel (or Lumen) >= 5.2

## Installation via Composer

To install simply run:

```
composer require dusterio/laravel-aws-worker
```

Or add it to `composer.json` manually:

```json
{
    "require": {
        "dusterio/laravel-aws-worker": "~0.1"
    }
}
```

### Usage in Laravel 5

```php
// Add in your config/app.php

'providers' => [
    '...',
    'Dusterio\AwsWorker\Integrations\LaravelServiceProvider',
];
```

After adding service provider, you should be able to see two special routes that we added:

```bash
$ php artisan route:list
+--------+----------+-----------------+------+----------------------------------------------------------+------------+
| Domain | Method   | URI             | Name | Action                                                   | Middleware |
+--------+----------+-----------------+------+----------------------------------------------------------+------------+
|        | POST     | worker/queue    |      | Dusterio\AwsWorker\Controllers\WorkerController@queue    |            |
|        | POST     | worker/schedule |      | Dusterio\AwsWorker\Controllers\WorkerController@schedule |            |
+--------+----------+-----------------+------+----------------------------------------------------------+------------+
```

### Usage in Lumen 5

```php
// Add in your bootstrap/app.php
$app->register(Dusterio\AwsWorker\Integrations\LumenServiceProvider::class);
```
