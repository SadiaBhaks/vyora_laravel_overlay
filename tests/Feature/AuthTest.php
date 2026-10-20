<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsStore;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase, BuildsStore;

    public function test_homepage_and_register_page_render(): void
    {
        $this->get('/')->assertOk()->assertSee('VYORA');
        $this->get('/register')->assertOk()->assertSee('Create Account');
    }

    public function test_user_can_register_and_role_cannot_be_mass_assigned(): void
    {
        $this->post('/register', ['name' => 'Ada Lovelace', 'email' => 'ada@example.test', 'password' => 'Password123', 'password_confirmation' => 'Password123', 'role' => 'admin'])->assertRedirect();
        $user = User::where('email', 'ada@example.test')->firstOrFail();
        $this->assertSame('customer', $user->role);
        $this->assertAuthenticatedAs($user);
    }

    public function test_weak_password_is_rejected(): void
    {
        $this->post('/register', ['name' => 'A', 'email' => 'a@example.test', 'password' => 'short', 'password_confirmation' => 'short'])->assertSessionHasErrors('password');
    }

    public function test_login_and_logout(): void
    {
        $user = $this->makeUser();
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors();
        $this->post('/login', ['email' => $user->email, 'password' => 'Password123'])->assertRedirect();
        $this->assertAuthenticatedAs($user);
        $this->post('/logout')->assertRedirect();
        $this->assertGuest();
    }

    public function test_account_routes_require_login(): void
    {
        foreach (['/account', '/account/orders', '/account/wishlist', '/account/addresses'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
    }

    public function test_password_reset_request_does_not_reveal_whether_email_exists(): void
    {
        $known = $this->post('/forgot-password', ['email' => $this->makeUser()->email]);
        $unknown = $this->post('/forgot-password', ['email' => 'nobody@example.test']);
        $this->assertSame($known->getSession()->get('status'), $unknown->getSession()->get('status'));
    }
}
