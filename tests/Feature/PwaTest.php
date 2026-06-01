<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function manifest_json_existe_e_tem_conteudo_correto(): void
    {
        $path = public_path('manifest.json');
        $this->assertFileExists($path, 'public/manifest.json não encontrado');

        $manifest = json_decode(file_get_contents($path), true);
        $this->assertNotNull($manifest, 'manifest.json não contém JSON válido');
        $this->assertEquals('Smart Listiq', $manifest['name']);
        $this->assertEquals('standalone',   $manifest['display']);
        $this->assertEquals('#2dd4bf',      $manifest['theme_color']);
        $this->assertNotEmpty($manifest['icons']);
        $this->assertCount(2, $manifest['shortcuts']);
    }

    /** @test */
    public function sw_js_existe(): void
    {
        $this->assertFileExists(public_path('sw.js'), 'public/sw.js não encontrado');
    }

    /** @test */
    public function offline_html_existe(): void
    {
        $this->assertFileExists(public_path('offline.html'), 'public/offline.html não encontrado');
    }

    /** @test */
    public function layout_contem_meta_tags_pwa(): void
    {
        $user = User::factory()->create();

        $html = $this->actingAs($user)->get('/listas')->getContent();

        $this->assertStringContainsString('rel="manifest"',               $html);
        $this->assertStringContainsString('apple-touch-icon',             $html);
        $this->assertStringContainsString('apple-mobile-web-app-capable', $html);
        $this->assertStringContainsString('sw.js',                        $html);
    }
}
