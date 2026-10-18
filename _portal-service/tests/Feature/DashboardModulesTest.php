<?php

use App\Constants\UserConst;
use App\Models\User;
use App\Usecase\Admin\SidebarMenuUsecase;

test('dashboard modul aplikasi only shows starter kit modules', function () {
    $user = User::factory()->make(['id' => 1, 'access_type' => UserConst::SUPERADMIN]);

    $modules = [
        (object) [
            'id' => 3,
            'label' => 'Keuangan',
            'route_name' => 'admin.keuangan.dashboard',
            'icon' => '_admin._layout.icons.sidebar.keuangan.keuangan',
            'children' => [],
        ],
        (object) [
            'id' => 4,
            'label' => 'Pengguna Aplikasi',
            'route_name' => 'admin.users.index',
            'icon' => '_admin._layout.icons.sidebar.user',
            'children' => [],
        ],
        (object) [
            'id' => 5,
            'label' => 'Manajemen Sidebar',
            'route_name' => 'admin.sidebar_menu.index',
            'icon' => '_admin._layout.icons.sidebar.data_master',
            'children' => [],
        ],
    ];

    $this->mock(SidebarMenuUsecase::class, function ($mock) use ($modules) {
        $mock->shouldReceive('getDashboardModules')
            ->once()
            ->with(UserConst::SUPERADMIN)
            ->andReturn([
                'success' => true,
                'data' => $modules,
            ]);

        $mock->shouldReceive('getGroupKeys')
            ->andReturn(['utama']);

        $mock->shouldReceive('getMenusForSidebar')
            ->andReturn(['success' => true, 'data' => []]);
    });

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertSuccessful()
        ->assertSee('Modul Aplikasi')
        ->assertSee('Pengguna Aplikasi')
        ->assertSee('Manajemen Sidebar')
        ->assertDontSee('Keuangan');
});
