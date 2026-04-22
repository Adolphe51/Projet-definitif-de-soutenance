<?php

namespace Tests\Feature\Auth;

use App\Http\Middleware\Auth\EnhancedCsrfProtection;
use App\Models\SecuritySession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_enhanced_csrf_protection_rejects_requests_without_a_matching_token(): void
    {
        $session = app('session.store');
        $session->start();

        $request = Request::create(route('logout'), 'POST');
        $request->setLaravelSession($session);
        $request->headers->set('User-Agent', 'CyberGuard Security Test');

        $middleware = new class(app(), app('encrypter')) extends EnhancedCsrfProtection
        {
            public function tokensMatchPublic(Request $request): bool
            {
                return $this->tokensMatch($request);
            }
        };

        $this->assertFalse($middleware->tokensMatchPublic($request));

        $requestWithToken = Request::create(route('logout'), 'POST', [
            '_token' => $session->token(),
        ]);
        $requestWithToken->setLaravelSession($session);
        $requestWithToken->headers->set('User-Agent', 'CyberGuard Security Test');

        $this->assertTrue($middleware->tokensMatchPublic($requestWithToken));
    }

    public function test_verified_otp_sets_a_secure_cookie_when_required_by_configuration(): void
    {
        config()->set('session.secure', true);

        Mail::fake();

        $user = User::factory()->create([
            'email' => 'cookie@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->post(route('otp.send'), [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $mail = Mail::sent(\App\Mail\OTPMail::class)->first();
        $code = $mail->code;

        $response = $this
            ->withSession([
                'pending_auth' => [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'created_at' => now()->timestamp,
                ],
                'otp_email' => $user->email,
            ])
            ->post(route('otp.verify'), [
                'email' => $user->email,
                'code' => $code,
            ]);

        $response->assertRedirect(route('admin.dashboard'));

        $cookies = $response->headers->getCookies();
        $accessTokenCookie = collect($cookies)->first(fn ($cookie) => $cookie->getName() === 'access_token');

        $this->assertNotNull($accessTokenCookie);
        $this->assertTrue($accessTokenCookie->isSecure());
        $this->assertTrue($accessTokenCookie->isHttpOnly());
        $this->assertSame('strict', strtolower((string) $accessTokenCookie->getSameSite()));
    }

    public function test_verified_otp_allows_a_non_secure_cookie_for_http_demonstrations(): void
    {
        config()->set('session.secure', false);

        Mail::fake();

        $user = User::factory()->create([
            'email' => 'cookie-http@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->post(route('otp.send'), [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $mail = Mail::sent(\App\Mail\OTPMail::class)->first();
        $code = $mail->code;

        $response = $this
            ->withSession([
                'pending_auth' => [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'created_at' => now()->timestamp,
                ],
                'otp_email' => $user->email,
            ])
            ->post(route('otp.verify'), [
                'email' => $user->email,
                'code' => $code,
            ]);

        $response->assertRedirect(route('admin.dashboard'));

        $cookies = $response->headers->getCookies();
        $accessTokenCookie = collect($cookies)->first(fn ($cookie) => $cookie->getName() === 'access_token');

        $this->assertNotNull($accessTokenCookie);
        $this->assertFalse($accessTokenCookie->isSecure());
        $this->assertTrue($accessTokenCookie->isHttpOnly());
        $this->assertSame('strict', strtolower((string) $accessTokenCookie->getSameSite()));
    }

    public function test_expired_security_session_cannot_access_protected_routes(): void
    {
        $user = User::factory()->create();
        $token = 'expired-access-token';
        $userAgent = 'CyberGuard Expired Session Test';
        $language = 'fr';

        SecuritySession::create([
            'user_id' => $user->id,
            'access_token_hash' => hash('sha256', $token),
            'refresh_token_hash' => hash('sha256', 'expired-refresh-token'),
            'ip_address' => '127.0.0.1',
            'user_agent' => $userAgent,
            'device_fingerprint' => hash('sha256', implode('|', ['127.0.0.1', $userAgent, $language])),
            'expires_at' => now()->subMinute(),
            'last_activity_at' => now()->subMinutes(5),
            'is_revoked' => false,
        ]);

        $response = $this
            ->withHeader('User-Agent', $userAgent)
            ->withHeader('Accept-Language', $language)
            ->withCookie('access_token', $token)
            ->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('session');
    }
}
