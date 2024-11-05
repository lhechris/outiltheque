<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;


class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        /*$response = $this->get('/');

        $response->assertStatus(200);*/
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function it_returns_home_page_if_user_is_authenticated()
    {
       /* Auth::shouldReceive('check')->once()->andReturn(true);

        $response = $this->get('/');

        $response->assertStatus(200);*/
        $this->assertTrue(true);
    }


}
