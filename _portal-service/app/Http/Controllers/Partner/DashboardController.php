<?php

namespace App\Http\Controllers\Partner;

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
        $accessType = (int) ($user?->access_type ?? UserConst::PARTNER);

        $modules = $this->sidebarMenuUsecase->getDashboardModules(
            accessType: $accessType
        );

        $allowedRoutes = [
            'partner.app_users.index',
        ];

        $modules = collect($modules['data'] ?? [])
            ->filter(fn ($menu) => in_array($menu->route_name, $allowedRoutes, true))
            ->values()
            ->all();

        $partner = DB::table(DatabaseConst::REGISTRATION_PARTNER())
            ->whereNull('deleted_at')
            ->where('user_id', $user->id)
            ->first();

        $stats = $this->appUserUsecase->getStats();

        return view('_partner.dashboard', [
            'modules' => $modules,
            'partner' => $partner,
            'stats' => $stats,
        ]);
    }
}
