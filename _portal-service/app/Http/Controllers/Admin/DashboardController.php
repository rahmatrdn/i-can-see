<?php

namespace App\Http\Controllers\Admin;

use App\Constants\DatabaseConst;
use App\Constants\UserConst;
use App\Http\Controllers\Controller;
use App\Usecase\Admin\AppUserUsecase;
use App\Usecase\Admin\SidebarMenuUsecase;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(
        protected SidebarMenuUsecase $sidebarMenuUsecase,
        protected AppUserUsecase $appUserUsecase
    ) {}

    public function index(): View|Response
    {
        $user = Auth::user();
        $accessType = (int) ($user?->access_type ?? 0);
        $isPartner = $accessType === UserConst::PARTNER;

        $modules = $this->sidebarMenuUsecase->getDashboardModules(
            accessType: $accessType
        );

        $allowedRoutes = [
            'admin.users.index',
            'admin.sidebar_menu.index',
            'admin.app_users.index',
        ];

        $modules = collect($modules['data'] ?? [])
            ->filter(fn ($menu) => in_array($menu->route_name, $allowedRoutes, true))
            ->values()
            ->all();

        $partner = null;
        $stats = null;

        if ($isPartner && $user) {
            $partner = DB::table(DatabaseConst::REGISTRATION_PARTNER())
                ->whereNull('deleted_at')
                ->where('user_id', $user->id)
                ->first();

            $stats = $this->appUserUsecase->getStats();
        }

        return view('_admin.dashboard', [
            'modules' => $modules,
            'isPartner' => $isPartner,
            'partner' => $partner,
            'stats' => $stats,
        ]);
    }
}
