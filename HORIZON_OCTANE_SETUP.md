# Laravel Horizon & Octane Setup Guide

This document provides comprehensive instructions for using Laravel Horizon (queue monitoring) and Octane (high-performance application server) in the UREA project.

## Table of Contents

1. [Overview](#overview)
2. [Installation](#installation)
3. [Configuration](#configuration)
4. [Running Horizon](#running-horizon)
5. [Running Octane](#running-octane)
6. [Queue Management](#queue-management)
7. [Monitoring](#monitoring)
8. [Best Practices](#best-practices)
9. [Troubleshooting](#troubleshooting)

---

## Overview

### Laravel Horizon

**Horizon** is a beautiful web-based dashboard for monitoring Laravel Redis queues. It provides:

- Real-time queue monitoring
- Job failure tracking
- Performance metrics
- Worker process management
- Pause/Resume queue capabilities

**Key Features:**
- Dashboard at `/horizon`
- Multiple queue support (default, notifications, reports)
- Automatic job failure handling
- Queue metrics and analytics

### Laravel Octane

**Octane** is a high-performance application server that uses RoadRunner, dramatically improving request handling performance. It provides:

- 2-8x faster request handling
- Persistent application container
- Background task workers
- Hot-reload support during development
- WebSocket support

**Key Features:**
- RoadRunner server engine
- 4 worker processes (configurable)
- Automatic worker reloading
- Task workers for background jobs
- Development file watching

---

## Installation

### Prerequisites

- PHP 8.3+
- Redis server running locally or remotely
- Composer

### Step 1: Install Packages

The packages are already added to `composer.json`:
- `laravel/horizon` - Queue monitoring
- `laravel/octane` - Application server
- `spiral/roadrunner-cli` - RoadRunner engine

Install them:

```bash
composer install
```

### Step 2: Verify Configuration Files

The following configuration files have been created:

- `config/horizon.php` - Horizon queue configuration
- `config/octane.php` - Octane server configuration

### Step 3: Update Environment Variables

Update your `.env` file with these settings:

```env
# Queue Configuration
QUEUE_CONNECTION=redis
CACHE_STORE=redis

# Horizon Configuration
HORIZON_DOMAIN=null
HORIZON_PATH=horizon
HORIZON_ENVIRONMENT=local
HORIZON_PROCESSES=1
HORIZON_NOTIFICATION_PROCESSES=2
HORIZON_REPORT_PROCESSES=1
HORIZON_AUTO_SCALING=false

# Octane Configuration
OCTANE_SERVER=roadrunner
OCTANE_WORKERS=4
OCTANE_TASK_WORKERS=4
OCTANE_MAX_REQUESTS=500
OCTANE_WATCH=true
OCTANE_CACHE_TTL=60
OCTANE_RELOAD_REQUESTS=500
OCTANE_RELOAD_MB=256
OCTANE_CONTAINER_RESET=true
OCTANE_MAX_EXECUTION_TIME=60
```

### Step 4: Install Horizon Assets (Optional)

If you want the UI dashboard:

```bash
php artisan horizon:install
```

### Step 5: Create Required Tables

```bash
php artisan migrate
```

---

## Configuration

### Horizon Configuration (`config/horizon.php`)

**Queue Workers:**

```php
'workers' => [
    [
        'name' => 'default',
        'connection' => 'redis',
        'queue' => ['default'],
        'processes' => 1,      // Number of processes
        'tries' => 3,          // Failed job retry attempts
        'timeout' => 60,       // Job timeout in seconds
        'sleep' => 3,          // Sleep duration between jobs
    ],
]
```

**Key Settings:**

- `processes` - Number of worker processes per queue
- `tries` - Maximum retry attempts for failed jobs
- `timeout` - Maximum execution time for a single job
- `sleep` - Delay between checking for new jobs

### Octane Configuration (`config/octane.php`)

**Key Settings:**

```php
'workers' => 4,                    // Number of worker processes
'task_workers' => 4,               // Background task workers
'max_requests' => 500,             // Reload after N requests
'watch' => true,                   // Watch for file changes
'reload' => [
    'requests' => 500,             // Reload workers after requests
    'mb' => 256,                   // Reload if memory > MB
],
```

---

## Running Horizon

### Start Horizon Dashboard

```bash
# Development (with file watching)
php artisan horizon

# Production (without watching)
php artisan horizon --production
```

### Access the Dashboard

Navigate to: `http://localhost:8000/horizon`

**Dashboard Features:**
- Real-time job monitoring
- Queue metrics
- Failed job history
- Worker status
- Pause/Resume queues

### Horizon Commands

```bash
# Pause all queues
php artisan horizon:pause

# Resume queues
php artisan horizon:unpause

# Restart workers
php artisan horizon:restart

# Terminate Horizon
php artisan horizon:terminate

# View Horizon status
php artisan horizon:status
```

---

## Running Octane

### Start Octane Server

```bash
# Development (with watch mode and 4 workers)
npm run octane

# Production (8 workers, no watch)
npm run octane:prod

# Or directly
php artisan octane:start --server=roadrunner --workers=4 --watch
```

### Access Application

The application will be available at: `http://localhost:8000`

### Octane Commands

```bash
# Start with specific workers
php artisan octane:start --workers=8

# Start with max execution time limit
php artisan octane:start --max-execution-time=120

# Stop Octane
php artisan octane:stop

# Reload workers
php artisan octane:reload
```

---

## Queue Management

### Creating Queued Jobs

Example job (`app/Jobs/SendNotificationEmail.php`):

```php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNotificationEmail implements ShouldQueue
{
    use Queueable;

    public function __construct($email, $message)
    {
        $this->email = $email;
        $this->message = $message;
        $this->onQueue('notifications');  // Route to notifications queue
    }

    public function handle()
    {
        // Send email
    }
}
```

### Dispatching Jobs

```php
// Dispatch immediately
SendNotificationEmail::dispatch($email, $message);

// Dispatch with delay (10 seconds)
SendNotificationEmail::dispatch($email, $message)->delay(10);

// Dispatch with specific queue
SendNotificationEmail::dispatch($email, $message)->onQueue('notifications');

// Dispatch in batch
Bus::batch([
    new SendNotificationEmail($email1, $msg1),
    new SendNotificationEmail($email2, $msg2),
])->dispatch();
```

### Pre-built Jobs

The project includes these jobs:

1. **SendNotificationEmail** - Send notification emails
   - Queue: `notifications`
   - Timeout: 120 seconds

2. **GenerateReport** - Generate reports asynchronously
   - Queue: `reports`
   - Timeout: 300 seconds
   - Ideal for heavy reports

3. **ProcessData** - Process bulk data
   - Queue: `default`
   - Timeout: 60 seconds

---

## Monitoring

### Real-time Monitoring

Access the Horizon dashboard:
- URL: `/horizon`
- Requires authentication as admin user
- Shows all queues in real-time

### Metrics Tracked

- **Throughput** - Jobs processed per minute
- **Job Distribution** - Jobs by queue and status
- **Failed Jobs** - Errors and stack traces
- **Processing Time** - Average execution duration
- **Retry Count** - Automatic retry attempts

### View Logs

```bash
# Watch Laravel logs
php artisan tail

# View failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

---

## Combined Setup

### Run Everything Together (Development)

```bash
# Using npm script (recommended)
npm run start

# Or manually in separate terminals:

# Terminal 1: Octane server
php artisan octane:start --server=roadrunner --workers=4 --watch

# Terminal 2: Horizon queue monitoring
php artisan horizon

# Terminal 3: Vite dev server
npm run dev
```

### Run Everything Together (Production)

```bash
npm run prod

# Or manually:
php artisan octane:start --server=roadrunner --workers=8
php artisan horizon --production
```

---

## Best Practices

### 1. Queue Design

```php
// Good: Specific, named queues
$this->dispatch(new SendEmail())->onQueue('emails');
$this->dispatch(new GenerateReport())->onQueue('reports');
$this->dispatch(new ProcessData())->onQueue('default');

// Bad: Everything on default queue
$this->dispatch(new Job());
```

### 2. Job Timeout Configuration

```php
// Set timeout per job
public function __construct()
{
    $this->timeout = 300; // 5 minutes for reports
}

// Or per queue in horizon config
'timeout' => 60, // 1 minute for default queue
```

### 3. Error Handling

```php
class MyJob implements ShouldQueue
{
    public function handle()
    {
        try {
            // Do work
        } catch (Exception $e) {
            $this->fail($e);
        }
    }

    public function failed(Exception $exception)
    {
        Log::error('Job failed', ['error' => $exception->message]);
        // Send notification to admin
    }
}
```

### 4. Container Reset in Octane

For jobs that modify state:

```php
if (app()->bound(Octane::class)) {
    app(Octane::class)->reset('connections.database');
}
```

### 5. Memory Management

- Configure `OCTANE_RELOAD_MB` to reload workers when memory exceeds threshold
- Set `OCTANE_MAX_REQUESTS` to reload workers periodically
- Monitor with Horizon dashboard

---

## Troubleshooting

### Horizon Dashboard Not Loading

```bash
# Verify Horizon installation
php artisan horizon:install

# Check Redis connection
php artisan tinker
>>> Redis::ping()

# Verify auth middleware
php artisan route:list | grep horizon
```

### Jobs Not Processing

```bash
# Check queue status
php artisan queue:failed

# View failed jobs
php artisan queue:failed-table

# Verify queue connection in .env
QUEUE_CONNECTION=redis

# Test Redis
redis-cli ping
# Expected: PONG
```

### Octane Not Starting

```bash
# Install RoadRunner if missing
./vendor/bin/rr get

# Check PHP version
php -v
# Requires PHP 8.3+

# Verify PHP extensions
php -m | grep sockets
php -m | grep posix
```

### Memory Issues

```bash
# Monitor memory usage
php artisan tinker
>>> memory_get_usage(true) / 1024 / 1024 . ' MB'

# Configure Octane reload
OCTANE_RELOAD_MB=256
OCTANE_RELOAD_REQUESTS=500
```

### High CPU Usage

```bash
# Reduce worker count
OCTANE_WORKERS=2

# Increase sleep duration
HORIZON_SLEEP=5

# Check for infinite loops in jobs
php artisan queue:failed
```

---

## Performance Tips

1. **Use Redis for Caching:**
   ```env
   CACHE_STORE=redis
   ```

2. **Enable Octane Watching (Development Only):**
   ```env
   OCTANE_WATCH=true
   ```

3. **Configure Appropriate Worker Counts:**
   - Use CPU count as guide: `OCTANE_WORKERS=4`
   - Separate task workers: `OCTANE_TASK_WORKERS=4`

4. **Monitor Queue Depth:**
   - Check Horizon dashboard regularly
   - Add more workers if queue depth increases

5. **Use Batches for Related Jobs:**
   ```php
   Bus::batch([...jobs...])->dispatch();
   ```

---

## Additional Resources

- [Laravel Horizon Docs](https://laravel.com/docs/horizon)
- [Laravel Octane Docs](https://laravel.com/docs/octane)
- [RoadRunner Docs](https://roadrunner.dev/)
- [Redis Documentation](https://redis.io/documentation)

---

## Support

For issues or questions:

1. Check the Horizon dashboard at `/horizon`
2. Review Laravel logs with `php artisan tail`
3. Check Redis connection: `redis-cli ping`
4. Review job failures in Horizon dashboard
5. Check system resources: `top` or Task Manager
