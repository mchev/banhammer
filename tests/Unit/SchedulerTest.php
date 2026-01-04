<?php

namespace Mchev\Banhammer\Tests\Unit;

use Illuminate\Console\Scheduling\Schedule;
use Mchev\Banhammer\Tests\TestCase;
use ReflectionClass;

class SchedulerTest extends TestCase
{
    protected function tearDown(): void
    {
        // Reset config after each test
        config(['ban.scheduler_enabled' => true]);
        config(['ban.scheduler_periodicity' => 'everyMinute']);
        parent::tearDown();
    }

    /**
     * Fire booted callbacks manually using reflection
     */
    protected function fireBootedCallbacks(): void
    {
        $reflection = new ReflectionClass($this->app);
        $property = $reflection->getProperty('bootedCallbacks');
        $property->setAccessible(true);
        $callbacks = $property->getValue($this->app);
        
        // Only fire the last callback (the one we just registered)
        if (!empty($callbacks)) {
            $lastCallback = end($callbacks);
            $lastCallback($this->app);
        }
    }

    /**
     * Clear schedule events to start fresh
     */
    protected function clearSchedule(): void
    {
        $schedule = $this->app->make(Schedule::class);
        $reflection = new ReflectionClass($schedule);
        $property = $reflection->getProperty('events');
        $property->setAccessible(true);
        $property->setValue($schedule, []);
    }

    public function test_scheduler_is_enabled_by_default(): void
    {
        // Clear schedule first
        $this->clearSchedule();
        
        // Ensure default config BEFORE booting
        config(['ban.scheduler_enabled' => true]);
        config(['ban.scheduler_periodicity' => 'everyMinute']);

        // Get the service provider and manually trigger boot
        $provider = $this->app->getProvider('Mchev\Banhammer\BanhammerServiceProvider');
        $provider->boot();
        
        // Manually fire booted callbacks
        $this->fireBootedCallbacks();

        // Get the schedule instance
        $schedule = $this->app->make(Schedule::class);
        
        // Get all scheduled events
        $events = $schedule->events();

        // Find the banhammer:unban command
        $banhammerEvent = collect($events)->first(function ($event) {
            return str_contains($event->command ?? '', 'banhammer:unban');
        });

        $this->assertNotNull($banhammerEvent, 'The banhammer:unban command should be scheduled by default');
    }

    public function test_scheduler_can_be_disabled(): void
    {
        // Clear schedule first
        $this->clearSchedule();
        
        // Disable scheduler BEFORE booting
        config(['ban.scheduler_enabled' => false]);

        // Get the service provider and manually trigger boot
        $provider = $this->app->getProvider('Mchev\Banhammer\BanhammerServiceProvider');
        $provider->boot();
        
        // Manually fire booted callbacks
        $this->fireBootedCallbacks();

        // Get the schedule instance
        $schedule = $this->app->make(Schedule::class);
        
        // Get all scheduled events
        $events = $schedule->events();

        // Find the banhammer:unban command
        $banhammerEvent = collect($events)->first(function ($event) {
            return str_contains($event->command ?? '', 'banhammer:unban');
        });

        $this->assertNull($banhammerEvent, 'The banhammer:unban command should not be scheduled when disabled');
    }

    public function test_scheduler_respects_periodicity_config(): void
    {
        // Clear schedule first
        $this->clearSchedule();
        
        // Set custom periodicity BEFORE booting
        config(['ban.scheduler_enabled' => true]);
        config(['ban.scheduler_periodicity' => 'everyFiveMinutes']);

        // Get the service provider and manually trigger boot
        $provider = $this->app->getProvider('Mchev\Banhammer\BanhammerServiceProvider');
        $provider->boot();
        
        // Manually fire booted callbacks
        $this->fireBootedCallbacks();

        // Get the schedule instance
        $schedule = $this->app->make(Schedule::class);
        
        // Get all scheduled events
        $events = $schedule->events();

        // Find the banhammer:unban command
        $banhammerEvent = collect($events)->first(function ($event) {
            return str_contains($event->command ?? '', 'banhammer:unban');
        });

        $this->assertNotNull($banhammerEvent, 'The banhammer:unban command should be scheduled');
        
        // Check that it's scheduled with the correct frequency
        // The expression for everyFiveMinutes is "*/5 * * * *"
        $this->assertEquals('*/5 * * * *', $banhammerEvent->expression);
    }

    public function test_scheduler_uses_every_minute_periodicity(): void
    {
        // Clear schedule first
        $this->clearSchedule();
        
        config(['ban.scheduler_enabled' => true]);
        config(['ban.scheduler_periodicity' => 'everyMinute']);

        // Get the service provider and manually trigger boot
        $provider = $this->app->getProvider('Mchev\Banhammer\BanhammerServiceProvider');
        $provider->boot();
        
        // Manually fire booted callbacks
        $this->fireBootedCallbacks();

        $schedule = $this->app->make(Schedule::class);
        $events = $schedule->events();

        $banhammerEvent = collect($events)->first(function ($event) {
            return str_contains($event->command ?? '', 'banhammer:unban');
        });

        $this->assertNotNull($banhammerEvent);
        $this->assertEquals('* * * * *', $banhammerEvent->expression);
    }

    public function test_scheduler_uses_hourly_periodicity(): void
    {
        // Clear schedule first
        $this->clearSchedule();
        
        config(['ban.scheduler_enabled' => true]);
        config(['ban.scheduler_periodicity' => 'hourly']);

        // Get the service provider and manually trigger boot
        $provider = $this->app->getProvider('Mchev\Banhammer\BanhammerServiceProvider');
        $provider->boot();
        
        // Manually fire booted callbacks
        $this->fireBootedCallbacks();

        $schedule = $this->app->make(Schedule::class);
        $events = $schedule->events();

        $banhammerEvent = collect($events)->first(function ($event) {
            return str_contains($event->command ?? '', 'banhammer:unban');
        });

        $this->assertNotNull($banhammerEvent);
        $this->assertEquals('0 * * * *', $banhammerEvent->expression);
    }

    public function test_scheduler_falls_back_to_every_minute_for_invalid_periodicity(): void
    {
        // Clear schedule first
        $this->clearSchedule();
        
        // Set invalid periodicity BEFORE booting
        config(['ban.scheduler_enabled' => true]);
        config(['ban.scheduler_periodicity' => 'invalidMethodName']);

        // Get the service provider and manually trigger boot
        $provider = $this->app->getProvider('Mchev\Banhammer\BanhammerServiceProvider');
        $provider->boot();
        
        // Manually fire booted callbacks
        $this->fireBootedCallbacks();

        // Get the schedule instance
        $schedule = $this->app->make(Schedule::class);
        
        // Get all scheduled events
        $events = $schedule->events();

        // Find the banhammer:unban command
        $banhammerEvent = collect($events)->first(function ($event) {
            return str_contains($event->command ?? '', 'banhammer:unban');
        });

        $this->assertNotNull($banhammerEvent, 'The banhammer:unban command should be scheduled even with invalid periodicity');
        
        // Should fallback to everyMinute (expression: "* * * * *")
        $this->assertEquals('* * * * *', $banhammerEvent->expression, 'Should fallback to everyMinute for invalid periodicity');
    }
}

