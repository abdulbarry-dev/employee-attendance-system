<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HorizonTest extends TestCase
{
    use RefreshDatabase;

    public function test_horizon_uses_redis_connection(): void
    {
        // In testing environment, queue connection is 'sync' (from phpunit.xml)
        // In production, it will be 'redis'
        $this->assertContains(config('queue.default'), ['sync', 'redis']);
        $this->assertEquals('default', config('horizon.use'));
    }

    public function test_horizon_has_multiple_queues_configured(): void
    {
        $queues = config('horizon.defaults.supervisor-1.queue');

        $this->assertIsArray($queues);
        $this->assertContains('high', $queues);
        $this->assertContains('default', $queues);
    }

    public function test_horizon_wait_thresholds_are_configured(): void
    {
        $waits = config('horizon.waits');

        $this->assertArrayHasKey('redis:default', $waits);
        $this->assertArrayHasKey('redis:high', $waits);
        $this->assertEquals(60, $waits['redis:default']);
        $this->assertEquals(30, $waits['redis:high']);
    }

    public function test_horizon_has_correct_retry_settings(): void
    {
        $supervisor = config('horizon.defaults.supervisor-1');

        $this->assertEquals(3, $supervisor['tries']);
        $this->assertEquals(60, $supervisor['timeout']);
        $this->assertEquals(128, $supervisor['memory']);
    }
}
