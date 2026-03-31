<?php

namespace Tests\Feature\Testing;

use Tests\TestCase;

class TestingEnvironmentTest extends TestCase
{
    public function test_testing_environment_uses_an_isolated_sqlite_database(): void
    {
        $this->assertSame('testing', app()->environment());
        $this->assertSame('sqlite', config('database.default'));
        $this->assertStringContainsString('database/testing.sqlite', (string) config('database.connections.sqlite.database'));
    }
}
