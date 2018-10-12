<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class Whois extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function get_request_to_api_requires_at_least_one_domain()
    {
        $this->get('/api/whois');
        $this->assertStatus(400);
    }
}
