<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Scheduled tasks
|--------------------------------------------------------------------------
|
| A single cron entry runs these: * * * * * php /path/to/artisan schedule:run
| On hosting without a long-running worker, the queue is drained every minute.
|
*/

Schedule::command('queue:work --stop-when-empty --tries=3 --max-time=55')
    ->everyMinute()
    ->withoutOverlapping()
    ->when(fn () => config('queue.default') !== 'sync' && (bool) config('zytecs.notifications.scheduled_queue_worker'));

Schedule::command('zytecs:prune-submissions')->dailyAt('03:15')->withoutOverlapping();
Schedule::command('model:prune')->daily();
Schedule::command('auth:clear-resets')->hourly();
