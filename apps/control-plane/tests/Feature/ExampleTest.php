<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_homepage_shows_marketing_site(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('Performance Advertising', false);
    }
}
