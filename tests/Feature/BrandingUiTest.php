<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandingUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_and_authentication_pages_use_stockwise_branding(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<title>Stockwise</title>', false)
            ->assertDontSee('Karle');

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('<title>Stockwise</title>', false)
            ->assertSee('>Stockwise</span>', false)
            ->assertDontSee('Karle');

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('<title>Stockwise</title>', false)
            ->assertSee('>Stockwise</span>', false)
            ->assertDontSee('Karle');
    }
}
