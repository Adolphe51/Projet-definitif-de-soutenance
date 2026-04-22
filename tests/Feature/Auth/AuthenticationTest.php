<?php

namespace Tests\Feature\Auth;

use App\Models\SecuritySession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_valid_credentials_start_the_otp_flow(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('otp.send'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('otp.verify.form'));
        $response->assertSessionHas('otp_email', $user->email);
        $response->assertSessionHas('pending_auth', fn (array $pendingAuth) => (int) $pendingAuth['user_id'] === (int) $user->id);
    }

    public function test_invalid_password_does_not_start_the_otp_flow(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('otp.send'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionMissing('pending_auth');
        $this->assertDatabaseCount('auth_codes', 0);
    }

    public function test_otp_send_is_rate_limited_after_the_configured_threshold(): void
    {
        Cache::flush();
        Mail::fake();

        $user = User::factory()->create();

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->post(route('otp.send'), [
                'email' => $user->email,
                'password' => 'password',
            ])->assertRedirect(route('otp.verify.form'));
        }

        $response = $this->post(route('otp.send'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');

        $this->get(route('login'))
            ->assertOk()
            ->assertSeeText('Adresse email professionnelle');
    }

    public function test_otp_send_rate_limit_returns_json_for_api_clients(): void
    {
        Cache::flush();
        Mail::fake();

        $user = User::factory()->create();

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->post(route('otp.send'), [
                'email' => $user->email,
                'password' => 'password',
            ]);
        }

        $this->postJson(route('otp.send'), [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertStatus(429)
            ->assertJson([
                'blocked' => true,
            ]);
    }

    public function test_authenticated_users_can_logout_with_a_valid_security_session(): void
    {
        $user = User::factory()->create();
        $token = 'test-access-token';
        $userAgent = 'CyberGuard Test Agent';
        $language = 'fr';

        SecuritySession::create([
            'user_id' => $user->id,
            'access_token_hash' => hash('sha256', $token),
            'refresh_token_hash' => hash('sha256', 'test-refresh-token'),
            'ip_address' => '127.0.0.1',
            'user_agent' => $userAgent,
            'device_fingerprint' => hash('sha256', implode('|', ['127.0.0.1', $userAgent, $language])),
            'expires_at' => now()->addHour(),
            'last_activity_at' => now(),
            'is_revoked' => false,
        ]);

        $response = $this
            ->withHeader('User-Agent', $userAgent)
            ->withHeader('Accept-Language', $language)
            ->withSession(['_token' => 'csrf-token'])
            ->withCookie('access_token', $token)
            ->post(route('logout'), ['_token' => 'csrf-token']);

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('security_sessions', [
            'user_id' => $user->id,
            'access_token_hash' => hash('sha256', $token),
            'is_revoked' => true,
        ]);
    }
}
