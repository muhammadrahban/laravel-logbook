<?php

namespace Rahban\LaravelLogbook\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase;
use Rahban\LaravelLogbook\Models\LogbookEntry;
use Rahban\LaravelLogbook\Providers\LogbookServiceProvider;
use Rahban\LaravelLogbook\Facades\Logbook;

class LogbookTest extends TestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [LogbookServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Logbook' => Logbook::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    public function test_can_log_custom_event()
    {
        Logbook::event('test.event', ['key' => 'value'], 1);

        $this->assertDatabaseHas('logbook_entries', [
            'type' => 'event',
            'event_name' => 'test.event',
            'user_id' => 1,
        ]);

        $entry = LogbookEntry::where('event_name', 'test.event')->first();
        $this->assertEquals(['key' => 'value'], $entry->event_data);
    }

    public function test_middleware_logs_requests()
    {
        $this->get('/test-route');

        $this->assertDatabaseHas('logbook_entries', [
            'type' => 'request',
            'method' => 'GET',
            'endpoint' => 'test-route',
        ]);
    }

    public function test_cleanup_command_works()
    {
        // Create old entries
        LogbookEntry::factory()->count(5)->create([
            'created_at' => now()->subDays(100)
        ]);

        // Create recent entries
        LogbookEntry::factory()->count(3)->create([
            'created_at' => now()->subDays(10)
        ]);

        $this->assertEquals(8, LogbookEntry::count());

        $this->artisan('logbook:cleanup', ['--days' => 90, '--force' => true]);

        $this->assertEquals(3, LogbookEntry::count());
    }

    public function test_stats_calculation()
    {
        LogbookEntry::factory()->count(10)->request()->create(['status_code' => 200]);
        LogbookEntry::factory()->count(2)->request()->create(['status_code' => 404]);
        LogbookEntry::factory()->count(3)->event()->create();

        $stats = app('logbook')->getStats();

        $this->assertEquals(12, $stats['total_requests']);
        $this->assertEquals(3, $stats['total_events']);
        $this->assertEquals(16.67, $stats['error_rate']); // 2 errors out of 12 requests
    }

    public function test_config_masking_works()
    {
        config(['logbook.mask_fields' => ['password', 'secret']]);

        $service = app('logbook');
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('maskSensitiveData');
        $method->setAccessible(true);

        $data = [
            'username' => 'john',
            'password' => 'secret123',
            'secret' => 'api-key',
            'public' => 'data'
        ];

        $masked = $method->invoke($service, $data);

        $this->assertEquals('john', $masked['username']);
        $this->assertEquals('***MASKED***', $masked['password']);
        $this->assertEquals('***MASKED***', $masked['secret']);
        $this->assertEquals('data', $masked['public']);
    }
}
