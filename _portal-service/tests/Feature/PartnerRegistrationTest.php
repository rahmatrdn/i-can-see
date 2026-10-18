<?php

use App\Constants\DatabaseConst;
use App\Constants\PartnerConst;
use App\Constants\UserConst;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('users', function ($table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->string('password');
        $table->tinyInteger('access_type')->nullable();
        $table->tinyInteger('is_active')->default(1);
        $table->timestamps();
        $table->softDeletes();
    });

    Schema::create('registration_partners', function ($table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->string('name');
        $table->string('type')->default('foundation');
        $table->string('pic_name');
        $table->string('pic_phone');
        $table->string('pic_email')->nullable();
        $table->text('address')->nullable();
        $table->string('city')->nullable();
        $table->string('province')->nullable();
        $table->string('postal_code')->nullable();
        $table->tinyInteger('verification_status')->default(0);
        $table->text('notes')->nullable();
        $table->tinyInteger('is_active')->default(1);
        $table->unsignedBigInteger('created_by')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });
});

test('partner registration page can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk()
        ->assertSee('Partner Registration')
        ->assertSee('Login Account Information')
        ->assertSee('Organization / Partner Information');
});

test('partner registration fails validation when fields are missing', function () {
    $response = $this->from(route('register'))->post(route('register.post'), []);

    $response->assertRedirect(route('register'));
    $response->assertSessionHas('error');
});

test('partner registration fails when password exceeds 8 characters', function () {
    $response = $this->from(route('register'))->post(route('register.post'), [
        'name' => 'Budi Santoso',
        'email' => 'partner_long_'.uniqid().'@example.com',
        'password' => 'morethan8chars',
        'password_confirmation' => 'morethan8chars',
        'partner_name' => 'Yayasan Mitra Netra Peduli',
        'partner_type' => PartnerConst::TYPE_FOUNDATION,
        'pic_phone' => '081234567890',
    ]);

    $response->assertRedirect(route('register'));
    $response->assertSessionHas('error');
});

test('partner can register successfully and user record is created', function () {
    $email = 'partner_test_'.uniqid().'@example.com';

    $payload = [
        'name' => 'Budi Santoso',
        'email' => $email,
        'password' => 'pass1234',
        'password_confirmation' => 'pass1234',
        'partner_name' => 'Yayasan Mitra Netra Peduli',
        'partner_type' => PartnerConst::TYPE_FOUNDATION,
        'pic_phone' => '081234567890',
        'address' => 'Jl. Kesehatan No. 10',
        'city' => 'Jakarta Selatan',
        'province' => 'DKI Jakarta',
        'postal_code' => '12345',
        'notes' => 'Pendaftaran untuk riset iCanSee',
    ];

    $response = $this->post(route('register.post'), $payload);

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('success');

    // Assert user created in users table
    $user = DB::table(DatabaseConst::USER())
        ->where('email', $email)
        ->first();

    expect($user)->not->toBeNull();
    expect($user->name)->toBe('Budi Santoso');
    expect((int) $user->access_type)->toBe(UserConst::PARTNER);

    // Assert partner created in registration_partners table
    $partner = DB::table(DatabaseConst::REGISTRATION_PARTNER())
        ->where('user_id', $user->id)
        ->first();

    expect($partner)->not->toBeNull();
    expect($partner->name)->toBe('Yayasan Mitra Netra Peduli');
    expect($partner->type)->toBe(PartnerConst::TYPE_FOUNDATION);
    expect($partner->pic_phone)->toBe('081234567890');
});
