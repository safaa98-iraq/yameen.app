<?php

use App\Mail\LoginCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

function sentCode(): string
{
    $code = null;
    Mail::assertSent(LoginCodeMail::class, function ($mail) use (&$code) {
        $code = $mail->code;

        return true;
    });

    return $code;
}

it('signs in with an emailed 6-digit code and creates the account', function () {
    Mail::fake();
    $this->post('/login/code', ['email' => 'Sara@Example.com'])->assertRedirect(url('/login'));
    $code = sentCode();
    expect($code)->toMatch('/^\d{6}$/');
    expect(DB::table('login_codes')->value('code_hash'))->not->toBe($code);

    $this->post('/login/verify', ['email' => 'sara@example.com', 'code' => $code])->assertRedirect(url('/account'));
    $this->assertAuthenticated();
    expect(User::where('email', 'sara@example.com')->exists())->toBeTrue();
});

it('rejects wrong, reused and expired codes', function () {
    Mail::fake();
    $this->post('/login/code', ['email' => 'a@example.com']);
    $code = sentCode();

    $this->post('/login/verify', ['email' => 'a@example.com', 'code' => '000000'])->assertSessionHasErrors('code');
    $this->post('/login/verify', ['email' => 'a@example.com', 'code' => $code])->assertRedirect(url('/account'));
    auth()->logout();
    $this->post('/login/verify', ['email' => 'a@example.com', 'code' => $code])->assertSessionHasErrors('code');

    $this->post('/login/code', ['email' => 'b@example.com']);
    $this->travel(11)->minutes();
    $this->post('/login/verify', ['email' => 'b@example.com', 'code' => Mail::sent(LoginCodeMail::class)->last()->code])->assertSessionHasErrors('code');
});

it('locks the code after 5 wrong attempts', function () {
    Mail::fake();
    $this->post('/login/code', ['email' => 'c@example.com']);
    $code = sentCode();
    foreach (range(1, 5) as $i) {
        $this->post('/login/verify', ['email' => 'c@example.com', 'code' => '111111']);
    }
    $this->post('/login/verify', ['email' => 'c@example.com', 'code' => $code])->assertSessionHasErrors('code');
});

it('throttles code requests', function () {
    Mail::fake();
    foreach (range(1, 3) as $i) {
        $this->post('/login/code', ['email' => 'd@example.com'])->assertSessionHasNoErrors();
    }
    $this->post('/login/code', ['email' => 'd@example.com'])->assertSessionHasErrors('email');
});

it('protects the account page and allows deleting the account', function () {
    $this->get('/account')->assertRedirect(url('/login'));
    $user = User::factory()->create(['email' => 'e@example.com']);
    $this->actingAs($user)->get('/en/account')->assertOk()->assertSee('e@example.com');
    $this->delete('/account', ['confirm' => 'wrong@example.com'])->assertSessionHasErrors('confirm');
    $this->delete('/account', ['confirm' => 'e@example.com'])->assertRedirect(url('/'));
    expect(User::count())->toBe(0);
});
