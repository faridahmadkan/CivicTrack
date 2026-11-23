<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SuspendedAccountAuthenticationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_suspended_account_cannot_start_an_authenticated_session(): void
    {
        $user = User::factory()->suspended()->create([
            'email' => 'suspended@example.test',
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect()
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'account_status' => 'suspended',
        ]);
    }
}
