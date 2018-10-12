<?php

namespace Tests\Feature;

use Tests\TestCase;
// use Illuminate\Foundation\Testing\WithFaker;
// use Illuminate\Foundation\Testing\RefreshDatabase;

class WhoisTest extends TestCase
{
    /**
     * Test api response
     *
     * @return void
     */
    public function test_get_request_to_api_requires_at_least_one_domain()
    {
        $response = $this->get('/api/whois');
        $response->assertStatus(400);
        $response = $this->json('GET','/api/whois',['query'=>'ifmo.su']);
        $response->assertStatus(200)
        ->assertJSON([
            'ifmo.su' => false
        ]);
        $response = $this->json('GET','/api/whois',['query'=>'ifmo','domains'=>'ru,su']);
        $response->assertStatus(200)
        ->assertJSON([
            'ifmo.su' => false,
            'ifmo.ru' => false
        ]);
    }
}
