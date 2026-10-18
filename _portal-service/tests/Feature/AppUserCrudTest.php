<?php

use App\Constants\AppUserConst;
use App\Constants\DatabaseConst;
use App\Constants\UserConst;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

    Schema::create('sidebar_menu_groups', function ($table) {
        $table->id();
        $table->string('key')->unique();
        $table->string('label');
        $table->string('color')->default('blue');
        $table->smallInteger('sort_order')->default(0);
        $table->timestamps();
    });

    Schema::create('sidebar_menus', function ($table) {
        $table->id();
        $table->unsignedBigInteger('parent_id')->nullable();
        $table->string('label');
        $table->string('route_name')->nullable();
        $table->string('icon')->nullable();
        $table->string('group');
        $table->smallInteger('sort_order')->default(0);
        $table->tinyInteger('is_active')->default(1);
        $table->unsignedBigInteger('created_by')->nullable();
        $table->unsignedBigInteger('updated_by')->nullable();
        $table->unsignedBigInteger('deleted_by')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });

    Schema::create('sidebar_menu_accesses', function ($table) {
        $table->id();
        $table->unsignedBigInteger('sidebar_menu_id');
        $table->tinyInteger('access_type');
        $table->unsignedBigInteger('created_by')->nullable();
        $table->timestamps();
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

    // Seed sidebar menus for partner
    DB::table('sidebar_menu_groups')->insert([
        'id' => 1,
        'key' => 'utama',
        'label' => 'Utama',
        'color' => 'blue',
        'sort_order' => 10,
    ]);

    DB::table('sidebar_menus')->insert([
        [
            'id' => 1,
            'label' => 'Dashboard',
            'route_name' => 'partner.dashboard',
            'icon' => '_admin._layout.icons.sidebar.dashboard',
            'group' => 'utama',
            'sort_order' => 1,
            'is_active' => 1,
        ],
        [
            'id' => 2,
            'label' => 'App Users (Visual Impairment)',
            'route_name' => 'partner.app_users.index',
            'icon' => '_admin._layout.icons.sidebar.user',
            'group' => 'utama',
            'sort_order' => 2,
            'is_active' => 1,
        ],
    ]);

    DB::table('sidebar_menu_accesses')->insert([
        ['sidebar_menu_id' => 1, 'access_type' => UserConst::PARTNER],
        ['sidebar_menu_id' => 2, 'access_type' => UserConst::PARTNER],
        ['sidebar_menu_id' => 1, 'access_type' => UserConst::SUPERADMIN],
        ['sidebar_menu_id' => 2, 'access_type' => UserConst::SUPERADMIN],
    ]);
});

function createPartnerUser(): array
{
    $userId = DB::table('users')->insertGetId([
        'name' => 'Partner Representative',
        'email' => 'partner_'.uniqid().'@example.com',
        'password' => Hash::make('pass1234'),
        'access_type' => UserConst::PARTNER,
        'is_active' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $partnerId = DB::table('registration_partners')->insertGetId([
        'user_id' => $userId,
        'name' => 'Yayasan Mitra Netra Peduli',
        'type' => 'foundation',
        'pic_name' => 'Partner Representative',
        'pic_phone' => '081234567890',
        'verification_status' => 1,
        'is_active' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $userModel = User::find($userId);

    return [$userModel, $partnerId];
}

test('guest cannot access partner app users or dashboard', function () {
    $this->get(route('partner.dashboard'))->assertRedirect(route('login'));
    $this->get(route('partner.app_users.index'))->assertRedirect(route('login'));
});

test('partner can access partner dashboard and view modules', function () {
    [$partnerUser] = createPartnerUser();

    $response = $this->actingAs($partnerUser)->get(route('partner.dashboard'));

    $response->assertOk()
        ->assertSee('Yayasan Mitra Netra Peduli')
        ->assertSee('App Users (Visual Impairment)');
});

test('partner can view partner app users index page', function () {
    [$partnerUser] = createPartnerUser();

    $response = $this->actingAs($partnerUser)->get(route('partner.app_users.index'));

    $response->assertOk()
        ->assertSee('App Users (Visual Impairment)')
        ->assertSee('Register New User');
});

test('partner can submit app user registration request with pending status and creates login user', function () {
    [$partnerUser, $partnerId] = createPartnerUser();

    $payload = [
        'name' => 'Siti Nurhaliza',
        'nickname' => 'Siti',
        'phone_number' => '08987654321',
        'username' => 'sitinurhaliza',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
        'gender' => AppUserConst::GENDER_FEMALE,
        'date_of_birth' => '1998-05-12',
        'national_id_number' => '3201234567890001',
        'visual_impairment_level' => AppUserConst::LEVEL_TOTAL_BLIND,
        'visual_impairment_cause' => 'Congenital',
        'address' => 'Jl. Mawar No. 5',
        'city' => 'Bandung',
        'province' => 'Jawa Barat',
        'guardian_name' => 'Ahmad Subagyo',
        'guardian_phone' => '081122334455',
        'guardian_relation' => 'Parent',
        'notes' => 'Requires voice assistance on app launch',
    ];

    $response = $this->actingAs($partnerUser)->post(route('partner.app_users.create'), $payload);

    $response->assertRedirect(route('partner.app_users.index'));
    $response->assertSessionHas('success');

    $appUser = DB::table(DatabaseConst::APP_USER())
        ->where('name', 'Siti Nurhaliza')
        ->first();

    expect($appUser)->not->toBeNull();
    expect((int) $appUser->registration_partner_id)->toBe($partnerId);
    expect($appUser->email)->toBe('sitinurhaliza');
    expect((int) $appUser->approval_status)->toBe(AppUserConst::STATUS_PENDING);
    expect((int) $appUser->is_active)->toBe(AppUserConst::INACTIVE);
    expect((int) $appUser->visual_impairment_level)->toBe(AppUserConst::LEVEL_TOTAL_BLIND);
    expect($appUser->guardian_name)->toBe('Ahmad Subagyo');

    $authUser = DB::table(DatabaseConst::USER())
        ->where('email', 'sitinurhaliza')
        ->first();

    expect($authUser)->not->toBeNull();
    expect($authUser->name)->toBe('Siti Nurhaliza');
    expect((int) $authUser->access_type)->toBe(UserConst::APP_USER);
    expect((int) $authUser->is_active)->toBe(0);
    expect(Hash::check('secret123', $authUser->password))->toBeTrue();
});

test('partner app user registration validates unique username and password confirmation', function () {
    [$partnerUser, $partnerId] = createPartnerUser();

    DB::table(DatabaseConst::USER())->insert([
        'name' => 'Existing User',
        'email' => 'existing_user',
        'password' => Hash::make('password'),
        'access_type' => UserConst::APP_USER,
        'created_at' => now(),
    ]);

    $payload = [
        'name' => 'New User',
        'username' => 'existing_user',
        'password' => 'secret123',
        'password_confirmation' => 'mismatch',
        'visual_impairment_level' => AppUserConst::LEVEL_TOTAL_BLIND,
    ];

    $response = $this->actingAs($partnerUser)->post(route('partner.app_users.create'), $payload);

    $response->assertSessionHasErrors(['username', 'password']);
});

test('partner can update an app user registration request', function () {
    [$partnerUser, $partnerId] = createPartnerUser();

    $appUserId = DB::table('app_users')->insertGetId([
        'registration_partner_id' => $partnerId,
        'name' => 'Agus Priyono',
        'visual_impairment_level' => AppUserConst::LEVEL_LOW_VISION,
        'phone_number' => '08123456789',
        'approval_status' => AppUserConst::STATUS_PENDING,
        'is_active' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $updatePayload = [
        'name' => 'Agus Priyono Updated',
        'nickname' => 'Agus',
        'visual_impairment_level' => AppUserConst::LEVEL_LOW_VISION,
        'phone_number' => '08999999999',
        'address' => 'Jl. Baru No. 12',
        'city' => 'Surabaya',
        'province' => 'Jawa Timur',
    ];

    $response = $this->actingAs($partnerUser)
        ->post(route('partner.app_users.doUpdate', $appUserId), $updatePayload);

    $response->assertRedirect(route('partner.app_users.index'));
    $response->assertSessionHas('success');

    $updated = DB::table('app_users')->where('id', $appUserId)->first();
    expect($updated->name)->toBe('Agus Priyono Updated');
    expect($updated->phone_number)->toBe('08999999999');
});

test('partner can soft delete their registered app user', function () {
    [$partnerUser, $partnerId] = createPartnerUser();

    $appUserId = DB::table('app_users')->insertGetId([
        'registration_partner_id' => $partnerId,
        'name' => 'User To Delete',
        'visual_impairment_level' => AppUserConst::LEVEL_LOW_VISION,
        'approval_status' => AppUserConst::STATUS_PENDING,
        'is_active' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($partnerUser)
        ->delete(route('partner.app_users.delete', $appUserId));

    $response->assertRedirect(route('partner.app_users.index'));
    $response->assertSessionHas('success');

    $deleted = DB::table('app_users')->where('id', $appUserId)->first();
    expect($deleted->deleted_at)->not->toBeNull();
});

test('partner cannot see or modify other partner app users', function () {
    [$partner1] = createPartnerUser();
    [$partner2, $partner2Id] = createPartnerUser();

    $otherUserAppId = DB::table('app_users')->insertGetId([
        'registration_partner_id' => $partner2Id,
        'name' => 'Private User of Partner 2',
        'visual_impairment_level' => AppUserConst::LEVEL_TOTAL_BLIND,
        'approval_status' => AppUserConst::STATUS_PENDING,
        'is_active' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Partner 1 tries to access detail
    $response = $this->actingAs($partner1)->get(route('partner.app_users.detail', $otherUserAppId));
    $response->assertRedirect(route('partner.app_users.index'));
    $response->assertSessionHas('error');

    // Partner 1 cannot see other partner's app user in index
    $indexResponse = $this->actingAs($partner1)->get(route('partner.app_users.index'));
    $indexResponse->assertDontSee('Private User of Partner 2');
});

function createSuperAdminUser(): User
{
    $userId = DB::table('users')->insertGetId([
        'name' => 'Super Administrator',
        'email' => 'admin_'.uniqid().'@example.com',
        'password' => Hash::make('password123'),
        'access_type' => UserConst::SUPERADMIN,
        'is_active' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return User::find($userId);
}

test('super admin can view app user detail page with verification actions', function () {
    $admin = createSuperAdminUser();
    [$partner, $partnerId] = createPartnerUser();

    $appUserId = DB::table('app_users')->insertGetId([
        'registration_partner_id' => $partnerId,
        'name' => 'Pending Verification User',
        'visual_impairment_level' => AppUserConst::LEVEL_TOTAL_BLIND,
        'approval_status' => AppUserConst::STATUS_PENDING,
        'is_active' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get(route('admin.app_users.detail', $appUserId));

    $response->assertOk()
        ->assertSee('Pending Verification User')
        ->assertSee('Registration Verification Pending')
        ->assertSee('Approve Request')
        ->assertSee('Reject Request');
});

test('super admin can approve app user registration', function () {
    $admin = createSuperAdminUser();
    [$partner, $partnerId] = createPartnerUser();

    DB::table('users')->insert([
        'name' => 'To Be Approved User',
        'email' => 'tobeapproved',
        'password' => Hash::make('password'),
        'access_type' => UserConst::APP_USER,
        'is_active' => 0,
        'created_at' => now(),
    ]);

    $appUserId = DB::table('app_users')->insertGetId([
        'registration_partner_id' => $partnerId,
        'name' => 'To Be Approved User',
        'email' => 'tobeapproved',
        'visual_impairment_level' => AppUserConst::LEVEL_LOW_VISION,
        'approval_status' => AppUserConst::STATUS_PENDING,
        'rejection_reason' => 'Prior reason',
        'is_active' => AppUserConst::INACTIVE,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.app_users.detail', $appUserId))
        ->post(route('admin.app_users.approve', $appUserId));

    $response->assertRedirect(route('admin.app_users.detail', $appUserId));
    $response->assertSessionHas('success');

    $updated = DB::table('app_users')->where('id', $appUserId)->first();
    expect((int) $updated->approval_status)->toBe(AppUserConst::STATUS_APPROVED)
        ->and((int) $updated->is_active)->toBe(AppUserConst::ACTIVE)
        ->and($updated->rejection_reason)->toBeNull()
        ->and((int) $updated->updated_by)->toBe($admin->id);

    $updatedAuthUser = DB::table('users')->where('email', 'tobeapproved')->first();
    expect((int) $updatedAuthUser->is_active)->toBe(1);
});

test('super admin can reject app user registration with reason', function () {
    $admin = createSuperAdminUser();
    [$partner, $partnerId] = createPartnerUser();

    DB::table('users')->insert([
        'name' => 'To Be Rejected User',
        'email' => 'toberejected',
        'password' => Hash::make('password'),
        'access_type' => UserConst::APP_USER,
        'is_active' => 0,
        'created_at' => now(),
    ]);

    $appUserId = DB::table('app_users')->insertGetId([
        'registration_partner_id' => $partnerId,
        'name' => 'To Be Rejected User',
        'email' => 'toberejected',
        'visual_impairment_level' => AppUserConst::LEVEL_TOTAL_BLIND,
        'approval_status' => AppUserConst::STATUS_PENDING,
        'is_active' => AppUserConst::INACTIVE,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.app_users.detail', $appUserId))
        ->post(route('admin.app_users.reject', $appUserId), [
            'rejection_reason' => 'Incomplete legal guardian contact information.',
        ]);

    $response->assertRedirect(route('admin.app_users.detail', $appUserId));
    $response->assertSessionHas('success');

    $updated = DB::table('app_users')->where('id', $appUserId)->first();
    expect((int) $updated->approval_status)->toBe(AppUserConst::STATUS_REJECTED)
        ->and((int) $updated->is_active)->toBe(AppUserConst::INACTIVE)
        ->and($updated->rejection_reason)->toBe('Incomplete legal guardian contact information.')
        ->and((int) $updated->updated_by)->toBe($admin->id);

    $updatedAuthUser = DB::table('users')->where('email', 'toberejected')->first();
    expect((int) $updatedAuthUser->is_active)->toBe(0);
});

test('super admin cannot reject app user registration without reason', function () {
    $admin = createSuperAdminUser();
    [$partner, $partnerId] = createPartnerUser();

    $appUserId = DB::table('app_users')->insertGetId([
        'registration_partner_id' => $partnerId,
        'name' => 'No Reason User',
        'visual_impairment_level' => AppUserConst::LEVEL_TOTAL_BLIND,
        'approval_status' => AppUserConst::STATUS_PENDING,
        'is_active' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.app_users.detail', $appUserId))
        ->post(route('admin.app_users.reject', $appUserId), [
            'rejection_reason' => '',
        ]);

    $response->assertRedirect(route('admin.app_users.detail', $appUserId));
    $response->assertSessionHasErrors('rejection_reason');

    $unchanged = DB::table('app_users')->where('id', $appUserId)->first();
    expect((int) $unchanged->approval_status)->toBe(AppUserConst::STATUS_PENDING);
});

test('partner cannot access superadmin verification endpoints', function () {
    [$partnerUser, $partnerId] = createPartnerUser();

    $appUserId = DB::table('app_users')->insertGetId([
        'registration_partner_id' => $partnerId,
        'name' => 'Target User',
        'visual_impairment_level' => AppUserConst::LEVEL_TOTAL_BLIND,
        'approval_status' => AppUserConst::STATUS_PENDING,
        'is_active' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Partner is blocked by access_type:1 middleware
    $this->actingAs($partnerUser)
        ->post(route('admin.app_users.approve', $appUserId))
        ->assertForbidden();

    $this->actingAs($partnerUser)
        ->post(route('admin.app_users.reject', $appUserId), [
            'rejection_reason' => 'Unauthorized rejection',
        ])
        ->assertForbidden();
});

test('partner can view rejected app user detail with re-submit button and banner', function () {
    [$partnerUser, $partnerId] = createPartnerUser();

    $appUserId = DB::table('app_users')->insertGetId([
        'registration_partner_id' => $partnerId,
        'name' => 'Rejected App User',
        'visual_impairment_level' => AppUserConst::LEVEL_TOTAL_BLIND,
        'approval_status' => AppUserConst::STATUS_REJECTED,
        'rejection_reason' => 'Document is blurry and unreadable.',
        'is_active' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($partnerUser)->get(route('partner.app_users.detail', $appUserId));

    $response->assertOk()
        ->assertSee('Rejected App User')
        ->assertSee('Registration Rejected')
        ->assertSee('Document is blurry and unreadable.')
        ->assertSee('Re-submit Registration');
});

test('partner can re-submit a rejected app user registration request', function () {
    [$partnerUser, $partnerId] = createPartnerUser();

    $appUserId = DB::table('app_users')->insertGetId([
        'registration_partner_id' => $partnerId,
        'name' => 'To Be Resubmitted User',
        'visual_impairment_level' => AppUserConst::LEVEL_LOW_VISION,
        'approval_status' => AppUserConst::STATUS_REJECTED,
        'rejection_reason' => 'Missing guardian contact.',
        'notes' => 'Original registration note.',
        'is_active' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($partnerUser)
        ->from(route('partner.app_users.detail', $appUserId))
        ->post(route('partner.app_users.resubmit', $appUserId), [
            'resubmission_note' => 'Added guardian contact and valid phone number.',
        ]);

    $response->assertRedirect(route('partner.app_users.detail', $appUserId));
    $response->assertSessionHas('success');

    $updated = DB::table('app_users')->where('id', $appUserId)->first();
    expect((int) $updated->approval_status)->toBe(AppUserConst::STATUS_PENDING)
        ->and($updated->rejection_reason)->toBeNull()
        ->and($updated->notes)->toContain('Original registration note.')
        ->and($updated->notes)->toContain('Added guardian contact and valid phone number.')
        ->and((int) $updated->updated_by)->toBe($partnerUser->id);
});

test('partner cannot re-submit another partner rejected app user', function () {
    [$partner1] = createPartnerUser();
    [$partner2, $partner2Id] = createPartnerUser();

    $otherUserAppId = DB::table('app_users')->insertGetId([
        'registration_partner_id' => $partner2Id,
        'name' => 'Partner 2 Rejected User',
        'visual_impairment_level' => AppUserConst::LEVEL_TOTAL_BLIND,
        'approval_status' => AppUserConst::STATUS_REJECTED,
        'rejection_reason' => 'Invalid NIK',
        'is_active' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($partner1)
        ->post(route('partner.app_users.resubmit', $otherUserAppId), [
            'resubmission_note' => 'Hacking another partner registration',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');

    $unchanged = DB::table('app_users')->where('id', $otherUserAppId)->first();
    expect((int) $unchanged->approval_status)->toBe(AppUserConst::STATUS_REJECTED)
        ->and($unchanged->rejection_reason)->toBe('Invalid NIK');
});
