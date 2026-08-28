# Quick Start - Horizon & Octane

## ⚡ TL;DR

This project now includes **Laravel Horizon** (queue monitoring) and **Octane** (high-performance app server).

## 🚀 Quick Setup

### Step 1: Run Migrations

```bash
php artisan migrate
```

This creates:
- `jobs` table - For database queues
- `failed_jobs` table - For failed job tracking
- Horizon tables (if published)

### Step 2: Start Everything

```bash
# Option 1: Run all services together
npm run start

# Option 2: Run individually in separate terminals
# Terminal 1
php artisan octane:start --server=roadrunner --workers=4 --watch

# Terminal 2
php artisan horizon

# Terminal 3
npm run dev
```

### Step 3: Access Horizon Dashboard

Navigate to: **`http://localhost:8000/horizon`**

Login with your admin credentials.

## 📊 What You Get

| Feature | Benefit |
|---------|---------|
| **Octane** | 2-8x faster request handling |
| **Horizon Dashboard** | Real-time queue monitoring at `/horizon` |
| **Job Queuing** | Async background tasks via queues |
| **Worker Processes** | 4 configurable worker processes |
| **Task Workers** | Background job workers |
| **Auto Reload** | File watching in development mode |

## 🎯 Key Commands

### Octane Commands
```bash
# Start Octane (development with watch mode)
php artisan octane:start --server=roadrunner --workers=4 --watch

# Start Octane (production - no watch)
php artisan octane:start --server=roadrunner --workers=8

# Stop Octane
php artisan octane:stop

# Reload workers
php artisan octane:reload
```

### Horizon Commands
```bash
# Start Horizon dashboard
php artisan horizon

# Stop Horizon
php artisan horizon:terminate

# Pause all queues
php artisan horizon:pause

# Resume queues
php artisan horizon:unpause

# Restart workers
php artisan horizon:restart

# View failed jobs
php artisan queue:failed
```

### Queue Commands
```bash
# Process jobs (for non-Horizon setup)
php artisan queue:work --queue=default,notifications,reports

# Retry failed jobs
php artisan queue:retry all

# Flush all jobs
php artisan queue:flush
```

## 📝 Using Queues

### Dispatch a Job

```php
// In a controller or service
use App\Jobs\SendNotificationEmail;

// Dispatch immediately
SendNotificationEmail::dispatch($email, $subject, $message);

// Dispatch with delay (10 seconds)
SendNotificationEmail::dispatch($email, $subject, $message)->delay(10);

// Dispatch to specific queue
SendNotificationEmail::dispatch($email, $subject, $message)->onQueue('notifications');
```

### Available Jobs

1. **SendNotificationEmail** - Send emails asynchronously
   ```php
   SendNotificationEmail::dispatch('user@example.com', 'Hello', 'Welcome!');
   ```

2. **GenerateReport** - Generate reports in background
   ```php
   GenerateReport::dispatch('sales', auth()->id(), ['date' => '2024-01-01']);
   ```

3. **ProcessData** - Process bulk data
   ```php
   ProcessData::dispatch('import', $csvData);
   ```

## ⚙️ Configuration

### Environment Variables (.env)

```env
# Queue Connection (database or redis)
QUEUE_CONNECTION=database
CACHE_STORE=database

# Horizon Settings
HORIZON_PROCESSES=1
HORIZON_NOTIFICATION_PROCESSES=2
HORIZON_REPORT_PROCESSES=1

# Octane Settings
OCTANE_WORKERS=4
OCTANE_TASK_WORKERS=4
OCTANE_MAX_REQUESTS=500
OCTANE_WATCH=true
```

### Config Files

- **`config/horizon.php`** - Horizon queue configuration
- **`config/octane.php`** - Octane server configuration

## 🔍 Monitoring

### Horizon Dashboard

Access at: `http://localhost:8000/horizon`

**Shows:**
- Real-time job processing
- Queue metrics
- Failed job history
- Worker status
- Job throughput

### View Logs

```bash
# Follow Laravel logs
php artisan tail

# View failed jobs
php artisan queue:failed

# View Octane logs
tail -f storage/logs/octane.log
```

## 🛑 Troubleshooting

### Horizon Dashboard Not Loading

```bash
# Re-publish assets
php artisan horizon:publish

# Clear cache
php artisan cache:clear
```

### Jobs Not Processing

```bash
# Check queue status
php artisan queue:failed

# View specific failed job
php artisan queue:failed-table

# Restart Horizon
php artisan horizon:restart
```

### High CPU Usage

```bash
# Reduce workers
OCTANE_WORKERS=2

# Increase sleep time
HORIZON_SLEEP=5
```

### Memory Issues

```bash
# Monitor memory
php artisan tinker
>>> memory_get_usage(true) / 1024 / 1024 . ' MB'

# Reload workers after N requests
OCTANE_RELOAD_REQUESTS=500

# Reload if memory exceeds
OCTANE_RELOAD_MB=256
```

## 📚 Learn More

- **Full Guide:** See `HORIZON_OCTANE_SETUP.md` for comprehensive documentation
- **Horizon Docs:** https://laravel.com/docs/horizon
- **Octane Docs:** https://laravel.com/docs/octane
- **Queue Docs:** https://laravel.com/docs/queues

## ✅ Checklist

- [ ] Run `php artisan migrate`
- [ ] Update `.env` with `QUEUE_CONNECTION=database`
- [ ] Run `npm run start`
- [ ] Access `http://localhost:8000/horizon`
- [ ] Test dispatching a job
- [ ] Monitor in Horizon dashboard

---

**Everything is ready!** Start with `npm run start` and visit `/horizon` to see it in action.
