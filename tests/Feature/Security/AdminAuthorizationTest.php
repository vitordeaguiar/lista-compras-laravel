<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->is_admin = true;
        $admin->save();
        return $admin;
    }

    public function test_is_admin_nao_pode_ser_setado_via_mass_assignment(): void
    {
        $user = User::create([
            'name'     => 'Atacante',
            'email'    => 'atacante@example.com',
            'password' => 'senha-super-secreta',
            'is_admin' => true,
        ]);

        $this->assertFalse((bool) $user->fresh()->is_admin);
    }

    public function test_admin_consegue_promover_usuario(): void
    {
        $admin  = $this->admin();
        $target = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.users.toggle-admin', $target))
            ->assertRedirect();

        $this->assertTrue((bool) $target->fresh()->is_admin);
    }

    public function test_admin_consegue_rebaixar_usuario(): void
    {
        $admin  = $this->admin();
        $target = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.users.toggle-admin', $target))
            ->assertRedirect();

        $this->assertFalse((bool) $target->fresh()->is_admin);
    }

    public function test_usuario_comum_nao_acessa_painel_admin(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.index'))
            ->assertStatus(403);
    }
}
