<?php

namespace Tests\Feature;

use Database\Seeders\CarSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicTunnelAssetsTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected $seeder = CarSeeder::class;

    private function cssPath(): string
    {
        $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true, flags: JSON_THROW_ON_ERROR);

        return '/build/'.$manifest['resources/css/app.css']['file'];
    }

    public function test_https_tunnel_uses_public_origin_for_css_scripts_images_and_navigation(): void
    {
        $response = $this->withServerVariables([
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_HOST' => 'localhost:8000',
            'SERVER_PORT' => '8000',
            'HTTP_X_FORWARDED_HOST' => 'rental-preview.asse.devtunnels.ms',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_PORT' => '443',
        ])->get('/');
        $response->assertOk()->assertSee('https://rental-preview.asse.devtunnels.ms'.$this->cssPath(), false)
            ->assertSee('https://rental-preview.asse.devtunnels.ms/cari-mobil', false)
            ->assertSee('https://rental-preview.asse.devtunnels.ms/images/hero-mercedes.png', false)
            ->assertDontSee('http://localhost:8000/build/', false)
            ->assertDontSee('http://rental-preview.asse.devtunnels.ms', false);
        $response->assertHeader('Content-Length', (string) strlen($response->getContent()));
        $this->get('/login')->assertOk()->assertSee('https://rental-preview.asse.devtunnels.ms'.$this->cssPath(), false)
            ->assertSee('https://rental-preview.asse.devtunnels.ms/login', false);
    }

    public function test_direct_local_requests_keep_local_http_asset_urls(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1', 'HTTP_HOST' => 'localhost:8000', 'SERVER_PORT' => '8000'])->get('/login')
            ->assertOk()->assertSee('http://localhost:8000'.$this->cssPath(), false);
    }

    public function test_forwarded_headers_from_other_ips_are_not_trusted(): void
    {
        $this->withServerVariables([
            'REMOTE_ADDR' => '203.0.113.10',
            'HTTP_HOST' => 'localhost:8000',
            'SERVER_PORT' => '8000',
            'HTTP_X_FORWARDED_HOST' => 'untrusted.example',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_PORT' => '443',
        ])->get('/login')->assertOk()->assertSee('http://localhost:8000'.$this->cssPath(), false)->assertDontSee('untrusted.example');
    }
}
