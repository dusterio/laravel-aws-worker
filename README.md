# laravel-aws-worker
Run Laravel (or Lumen) tasks and queue listeners inside of AWS Elastic Beanstalk workers

## Overview

Laravel documentation recommends to use supervisor for queue workers and *IX cron for scheduled tasks. However, when deploying your application to AWS Elastic Beanstalk, neither option is available.

This package helps you run your Laravel (or Lumen) jobs in AWS worker environments.

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
