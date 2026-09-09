<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_app_shell_renders(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('PDF-Gutschein erstellen');
        $response->assertSee('Bild aus Shopify auswaehlen');
    }
}
