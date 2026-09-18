<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /** The application boots and a guest is sent to the login screen. */
    public function testGuestIsRedirectedToLogin(): void
    {
        $this->get('/')->assertRedirect(route('login'));
        $this->get('/login')->assertStatus(200);
    }
}
