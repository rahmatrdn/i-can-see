<?php

namespace App\Http\Controllers\Admin;

use App\Constants\ResponseConst;
use App\Constants\UserConst;
use App\Http\Controllers\Controller;
use App\Usecase\Admin\SidebarMenuUsecase;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SidebarMenuController extends Controller
{
    protected array $page = [
        'route' => 'sidebar-menu',
        'title' => 'Manajemen Menu Sidebar',
    ];

    protected string $baseRedirect;

    public function __construct(
        protected SidebarMenuUsecase $usecase
    ) {
        $this->baseRedirect = 'admin/'.$this->page['route'];
    }

    public function index(Request $request): View|Response
    {
        $data = $this->usecase->getAll([
            'keywords' => $request->get('keywords'),
            'group' => $request->get('group'),
        ]);
        $data = $data['data']['list'] ?? [];

        $groups = $this->usecase->getGroups()['data'] ?? [];

        return view('_admin.sidebar-menu.index', [
            'data' => $data,
            'page' => $this->page,
            'keywords' => $request->get('keywords'),
            'group' => $request->get('group'),
            'groups' => $groups,
        ]);
    }

    public function add(): View|Response
    {
        $parentOptions = $this->usecase->getParentOptions();
        $parentOptions = $parentOptions['data'] ?? [];

        $groups = $this->usecase->getGroups()['data'] ?? [];

        return view('_admin.sidebar-menu.add', [
            'page' => $this->page,
            'parentOptions' => $parentOptions,
            'groups' => $groups,
        ]);
    }

    public function doCreate(Request $request): RedirectResponse
    {
        $process = $this->usecase->create(data: $request);

        if ($process['success']) {
            return redirect()
                ->route('admin.sidebar_menu.index')
                ->with('success', ResponseConst::SUCCESS_MESSAGE_CREATED);
        }

        return redirect()
            ->back()
            ->withInput()
            ->with('error', $process['message'] ?? ResponseConst::DEFAULT_ERROR_MESSAGE);
    }

    public function update(int $id): View|RedirectResponse|Response
    {
        $data = $this->usecase->getByID($id);

        if (empty($data['data'])) {
            return redirect()
                ->intended($this->baseRedirect)
                ->with('error', ResponseConst::DEFAULT_ERROR_MESSAGE);
        }

        $parentOptions = $this->usecase->getParentOptions();
        $parentOptions = $parentOptions['data'] ?? [];

        $groups = $this->usecase->getGroups()['data'] ?? [];

        return view('_admin.sidebar-menu.update', [
            'data' => (object) $data['data'],
            'page' => $this->page,
            'parentOptions' => $parentOptions,
            'groups' => $groups,
        ]);
    }

    public function doUpdate(Request $request, int $id): RedirectResponse
    {
        $process = $this->usecase->update(data: $request, id: $id);

        if ($process['success']) {
            return redirect()
                ->route('admin.sidebar_menu.index')
                ->with('success', ResponseConst::SUCCESS_MESSAGE_UPDATED);
        }

        return redirect()
            ->back()
            ->withInput()
            ->with('error', $process['message'] ?? ResponseConst::DEFAULT_ERROR_MESSAGE);
    }

    public function delete(int $id): RedirectResponse
    {
        $process = $this->usecase->delete($id);

        if ($process['success']) {
            return redirect()
                ->route('admin.sidebar_menu.index')
                ->with('success', ResponseConst::SUCCESS_MESSAGE_DELETED);
        }

        return redirect()
            ->back()
            ->with('error', $process['message'] ?? ResponseConst::DEFAULT_ERROR_MESSAGE);
    }

    public function access(int $id): View|RedirectResponse|Response
    {
        $data = $this->usecase->getByID($id);

        if (empty($data['data'])) {
            return redirect()
                ->intended($this->baseRedirect)
                ->with('error', ResponseConst::DEFAULT_ERROR_MESSAGE);
        }

        $accesses = $this->usecase->getAccesses($id);
        $accesses = $accesses['data'] ?? [];

        return view('_admin.sidebar-menu.access', [
            'data' => (object) $data['data'],
            'page' => $this->page,
            'accesses' => $accesses,
            'accessTypes' => UserConst::getAccessTypes(),
        ]);
    }

    public function doAccess(Request $request, int $id): RedirectResponse
    {
        $process = $this->usecase->syncAccess(
            sidebarMenuId: $id,
            accessTypes: $request->input('access_types', []),
        );

        if ($process['success']) {
            return redirect()
                ->route('admin.sidebar_menu.access', $id)
                ->with('success', ResponseConst::SUCCESS_MESSAGE_UPDATED);
        }

        return redirect()
            ->back()
            ->with('error', $process['message'] ?? ResponseConst::DEFAULT_ERROR_MESSAGE);
    }

    public function roleAccess(int $accessType): View|RedirectResponse|Response
    {
        $accessTypes = UserConst::getAccessTypes();

        if (! isset($accessTypes[$accessType])) {
            return redirect()
                ->intended($this->baseRedirect)
                ->with('error', ResponseConst::DEFAULT_ERROR_MESSAGE);
        }

        $menusByGroup = $this->usecase->getAllMenusForRole($accessType);
        $menusByGroup = $menusByGroup['data'] ?? [];

        $groups = $this->usecase->getGroups()['data'] ?? [];

        return view('_admin.sidebar-menu.role-access', [
            'page' => $this->page,
            'accessType' => $accessType,
            'roleName' => $accessTypes[$accessType],
            'menusByGroup' => $menusByGroup,
            'accessTypes' => $accessTypes,
            'groups' => $groups,
        ]);
    }

    public function doRoleAccess(Request $request, int $accessType): RedirectResponse
    {
        $process = $this->usecase->syncMenusForRole(
            accessType: $accessType,
            menuIds: $request->input('menu_ids', []),
        );

        if ($process['success']) {
            return redirect()
                ->route('admin.sidebar_menu.role_access', $accessType)
                ->with('success', ResponseConst::SUCCESS_MESSAGE_UPDATED);
        }

        return redirect()
            ->back()
            ->with('error', $process['message'] ?? ResponseConst::DEFAULT_ERROR_MESSAGE);
    }

    public function refreshCache(): RedirectResponse
    {
        $process = $this->usecase->refreshSidebarCache();

        if ($process['success']) {
            return redirect()
                ->route('admin.sidebar_menu.index')
                ->with('success', $process['message'] ?? ResponseConst::SUCCESS_MESSAGE_UPDATED);
        }

        return redirect()
            ->back()
            ->with('error', $process['message'] ?? ResponseConst::DEFAULT_ERROR_MESSAGE);
    }
}
