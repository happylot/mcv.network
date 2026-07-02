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
            ->assertSee('Ba vai trò. Một sàn giao dịch. Không trung gian thừa.', false)
            ->assertSee('account_type=advertiser', false)
            ->assertSee('account_type=publisher', false)
            ->assertSee('account_type=agency', false);
    }
}
