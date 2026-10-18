<?php

use App\Constants\AppUserConst;
use App\Constants\UserConst;
use App\Models\User;
use App\Usecase\Admin\SidebarMenuUsecase;
use App\Usecase\UserUsecase;
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
        $table->unsignedBigInteger('created_by')->nullable();
        $table->unsignedBigInteger('updated_by')->nullable();
        $table->unsignedBigInteger('deleted_by')->nullable();
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

    Schema::create('app_users', function ($table) {
        $table->id();
        $table->unsignedBigInteger('registration_partner_id')->nullable();
        $table->string('name');
        $table->string('nickname')->nullable();
        $table->string('phone_number')->nullable();
        $table->string('email')->nullable();
        $table->tinyInteger('gender')->nullable();
        $table->date('date_of_birth')->nullable();
        $table->string('national_id_number')->nullable();
        $table->tinyInteger('visual_impairment_level')->default(1);
        $table->string('visual_impairment_cause')->nullable();
        $table->text('address')->nullable();
        $table->string('city')->nullable();
        $table->string('province')->nullable();
        $table->string('guardian_name')->nullable();
        $table->string('guardian_phone')->nullable();
        $table->string('guardian_relation')->nullable();
        $table->string('device_id')->nullable();
        $table->text('fcm_token')->nullable();
        $table->string('app_version')->nullable();
        $table->timestamp('last_active_at')->nullable();
        $table->tinyInteger('is_active')->default(1);
        $table->tinyInteger('approval_status')->default(0);
        $table->text('rejection_reason')->nullable();
        $table->text('notes')->nullable();
        $table->unsignedBigInteger('created_by')->nullable();
        $table->unsignedBigInteger('updated_by')->nullable();
        $table->unsignedBigInteger('deleted_by')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });

    $this->sidebarMock = $this->mock(SidebarMenuUsecase::class, function ($mock) {
        $mock->shouldReceive('getDashboardModules')
            ->andReturn([
                'success' => true,
                'data' => [
                    (object) [
                        'id' => 1,
                        'label' => 'Pengguna Aplikasi',
                        'route_name' => 'admin.users.index',
                        'icon' => null,
                        'children' => [],
                    ],
                ],
            ]);

        $mock->shouldReceive('getGroupKeys')->andReturn(['utama']);
        $mock->shouldReceive('getMenusForSidebar')->andReturn(['success' => true, 'data' => []]);
    });
});

test('user usecase getStats returns expected structure and accurate counts', function () {
    // Insert test users
    DB::table('users')->insert([
        [
            'name' => 'Super Admin One',
            'email' => 'super1@test.com',
            'password' => 'secret',
            'access_type' => UserConst::SUPERADMIN,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'name' => 'Partner One',
            'email' => 'partner1@test.com',
            'password' => 'secret',
            'access_type' => UserConst::PARTNER,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'name' => 'Partner Inactive',
            'email' => 'partner_inactive@test.com',
            'password' => 'secret',
            'access_type' => UserConst::PARTNER,
            'is_active' => 0,
            'created_at' => now()->subMonths(2),
            'updated_at' => now()->subMonths(2),
        ],
    ]);

    $usecase = app(UserUsecase::class);
    $stats = $usecase->getStats();

    expect($stats)->toBeArray()
        ->toHaveKeys([
            'total',
            'superadmin',
            'partner',
            'active',
            'inactive',
            'new_this_month',
            'recent_users',
        ]);

    expect($stats['total'])->toBe(3);
    expect($stats['superadmin'])->toBe(1);
    expect($stats['partner'])->toBe(2);
    expect($stats['active'])->toBe(2);
    expect($stats['inactive'])->toBe(1);
    expect($stats['new_this_month'])->toBe(2);
    expect($stats['recent_users'])->toHaveCount(3);
    expect($stats['recent_users'][0]->name)->toBe('Super Admin One');
});

test('admin dashboard renders user statistics section for superadmin', function () {
    $user = User::factory()->make([
        'id' => 1,
        'name' => 'Super User Test',
        'access_type' => UserConst::SUPERADMIN,
    ]);

    DB::table('users')->insert([
        'id' => 1,
        'name' => 'Super User Test',
        'email' => 'superuser@test.com',
        'password' => 'secret',
        'access_type' => UserConst::SUPERADMIN,
        'is_active' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('app_users')->insert([
        'name' => 'App User Demo',
        'phone_number' => '08123456789',
        'visual_impairment_level' => 1,
        'approval_status' => AppUserConst::STATUS_APPROVED,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($user)->get(route('admin.dashboard'));

    $response->assertSuccessful()
        ->assertSee('Statistik Pengguna')
        ->assertSee('Pengguna Portal')
        ->assertSee('Pengguna Aktif')
        ->assertSee('Pengguna Tunanetra')
        ->assertSee('Pengguna Portal Terbaru')
        ->assertSeeText('Distribusi & Status')
        ->assertSee('Kelola Pengguna')
        ->assertSee('Tambah Pengguna')
        ->assertSee(route('admin.users.index'))
        ->assertSee(route('admin.users.add'))
        ->assertSee(route('admin.app_users.index'));
});

test('partner accessing admin dashboard sees registration overview instead of admin stats', function () {
    $user = User::factory()->make([
        'id' => 2,
        'name' => 'Partner User Test',
        'access_type' => UserConst::PARTNER,
    ]);

    DB::table('users')->insert([
        'id' => 2,
        'name' => 'Partner User Test',
        'email' => 'partneruser@test.com',
        'password' => 'secret',
        'access_type' => UserConst::PARTNER,
        'is_active' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('registration_partners')->insert([
        'id' => 1,
        'user_id' => 2,
        'name' => 'Mitra Foundation',
        'type' => 'foundation',
        'pic_name' => 'Budi',
        'pic_phone' => '081234567890',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($user)->get(route('admin.dashboard'));

    $response->assertSuccessful()
        ->assertSee('Registration Requests Overview')
        ->assertDontSee('Pengguna Portal Terbaru');
});
