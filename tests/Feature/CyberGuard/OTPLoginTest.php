<?php

namespace Tests\Feature\CyberGuard;

use App\Mail\OTPMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OTPLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_otp_sends_code_and_redirects_to_verification(): void
    {
        Mail::fake();

        $user = User::create([
            'nom' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);

        $response = $this->post('/otp/send', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('otp.verify.form'))
            ->assertSessionHas('otp_email', $user->email);

        $this->assertDatabaseHas('auth_codes', [
            'user_id' => $user->id,
            'email' => $user->email,
            'used_at' => null,
        ]);

        Mail::assertSent(OTPMail::class, function (OTPMail $mail) use ($user) {
            return $mail->hasTo($user->email) && !empty($mail->code);
        });
    }

    public function test_verify_otp_with_correct_code_authenticates_user(): void
    {
        Mail::fake();

        $user = User::create([
            'nom' => 'Test User',
            'email' => 'verify@example.com',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);

        $this->post('/otp/send', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $code = null;
        Mail::assertSent(OTPMail::class, function (OTPMail $mail) use (&$code) {
            $code = $mail->code;
            return true;
        });

        $response = $this->withSession(['otp_email' => $user->email])
            ->post('/otp/verify', [
                'email' => $user->email,
                'code' => $code,
            ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_resend_otp_requires_a_pending_authenticated_session(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'resend@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('otp.resend'), [
            'email' => $user->email,
        ]);

        $response->assertRedirect(route('login'));
        Mail::assertNothingSent();
    }

    public function test_verify_otp_requires_a_pending_authenticated_session(): void
    {
        $user = User::factory()->create([
            'email' => 'pending@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('otp.verify'), [
            'email' => $user->email,
            'code' => '12345678',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
