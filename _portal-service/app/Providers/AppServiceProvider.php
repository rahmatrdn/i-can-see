<?php

namespace App\Providers;

use App\Usecase\Admin\SidebarMenuUsecase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Livewire\Blaze\Blaze;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blaze::optimize()
            ->in(resource_path('views/components'), fold: true)
            ->in(resource_path('views/components/admin/modal.blade.php'), fold: false);

        View::composer('_admin._layout.sidebar.*', function ($view) {
            if (! Auth::check()) {
                return;
            }

            $usecase = app(SidebarMenuUsecase::class);
            $accessType = Auth::user()->access_type;
            $groups = $usecase->getGroupKeys();
            $sidebarMenus = [];

            foreach ($groups as $group) {
                $result = $usecase->getMenusForSidebar($accessType, $group);
                $sidebarMenus[$group] = $result['data'] ?? [];
            }

            $view->with('sidebarMenus', $sidebarMenus);
        });
    }
}
