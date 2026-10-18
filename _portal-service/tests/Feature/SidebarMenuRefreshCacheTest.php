<?php

use App\Models\User;
use App\Usecase\Admin\SidebarMenuUsecase;

test('guest cannot access sidebar menu refresh cache endpoint', function () {
    $this->get(route('admin.sidebar_menu.refresh_cache'))
        ->assertRedirect(route('login'));
});

test('sidebar menu refresh cache route is registered', function () {
    expect(route('admin.sidebar_menu.refresh_cache'))->toContain('/admin/sidebar-menu/refresh-cache');
});

test('authenticated superadmin can trigger sidebar menu cache refresh', function () {
    $user = User::factory()->make(['id' => 1, 'access_type' => 1]);

    $this->mock(SidebarMenuUsecase::class, function ($mock) {
        $mock->shouldReceive('refreshSidebarCache')
            ->once()
            ->andReturn([
                'success' => true,
                'message' => 'Cache menu sidebar berhasil diperbarui.',
                'data' => ['refreshed' => [], 'count' => 0],
            ]);
    });

    $this->actingAs($user)
        ->get(route('admin.sidebar_menu.refresh_cache'))
        ->assertRedirect(route('admin.sidebar_menu.index'))
        ->assertSessionHas('success', 'Cache menu sidebar berhasil diperbarui.');
});
