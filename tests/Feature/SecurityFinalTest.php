<?php

namespace Tests\Feature;

use App\Models\Jamaah;
use App\Models\Pelanggan;
use App\Models\Pendaftaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class SecurityFinalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_web_responses_include_basic_security_headers(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    public function test_dangerous_document_and_payment_file_types_are_rejected(): void
    {
        [$user, $pendaftaran, $jamaah] = $this->customerRegistrationAndJamaah();
        $dp = $pendaftaran->pembayarans()->where('jenis_pembayaran', 'DP')->firstOrFail();

        foreach (['php', 'js', 'exe', 'html', 'svg'] as $extension) {
            $file = UploadedFile::fake()->create("berbahaya.$extension", 10, 'application/octet-stream');
            $this->actingAs($user)->post(route('jamaah.dokumen.store', $jamaah), [
                'jenis_dokumen' => 'KTP',
                'file_dokumen' => $file,
            ])->assertSessionHasErrors('file_dokumen');

            $proof = UploadedFile::fake()->create("berbahaya.$extension", 10, 'application/octet-stream');
            $this->actingAs($user)->post(route('pembayaran.store', $dp), [
                'bukti_file' => $proof,
            ])->assertSessionHasErrors('bukti_file');
        }
    }

    public function test_registered_password_is_hashed_and_plain_value_is_not_stored(): void
    {
        $password = 'Password-Aman-123';

        $this->post(route('register.post'), [
            'name' => 'Pelanggan Baru',
            'email' => 'baru@example.com',
            'password' => $password,
            'password_confirmation' => $password,
            'no_hp' => '081234567891',
        ])->assertRedirect(route('dashboard'));

        $storedPassword = User::where('email', 'baru@example.com')->value('password');
        $this->assertNotSame($password, $storedPassword);
        $this->assertTrue(Hash::check($password, $storedPassword));
    }

    public function test_login_is_rate_limited_after_five_attempts(): void
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post(route('login.post'), [
                'email' => 'tidak-ada@example.com',
                'password' => 'salah-sekali',
            ])->assertSessionHasErrors('email');
        }

        $this->post(route('login.post'), [
            'email' => 'tidak-ada@example.com',
            'password' => 'salah-sekali',
        ])->assertTooManyRequests();
    }

    public function test_technical_exception_message_is_hidden_when_debug_is_disabled(): void
    {
        config(['app.debug' => false]);
        Route::middleware('web')->get('/security-test-error', function (): void {
            throw new RuntimeException('RAHASIA_DETAIL_TEKNIS');
        });

        $this->get('/security-test-error')
            ->assertInternalServerError()
            ->assertSee('Terjadi Kesalahan')
            ->assertDontSee('RAHASIA_DETAIL_TEKNIS');
    }

    private function customerRegistrationAndJamaah(): array
    {
        $user = User::factory()->create(['role' => 'pelanggan']);
        Pelanggan::create(['user_id' => $user->id]);
        $pendaftaran = Pendaftaran::factory()->create(['user_id' => $user->id]);
        $pendaftaran->ensurePaymentBills();
        $pendaftaran->pembayarans()
            ->where('jenis_pembayaran', 'DP')
            ->update(['status_pembayaran' => 'Valid']);
        $jamaah = Jamaah::factory()->create(['pendaftaran_id' => $pendaftaran->id]);

        return [$user, $pendaftaran, $jamaah];
    }
}
