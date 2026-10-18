<?php

namespace App\Http\Controllers\Partner;

use App\Constants\AppUserConst;
use App\Constants\ResponseConst;
use App\Http\Controllers\Controller;
use App\Usecase\Admin\AppUserUsecase;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AppUserController extends Controller
{
    protected array $page = [
        'route' => 'app-users',
        'title' => 'App Users (Visual Impairment)',
    ];

    protected string $baseRedirect;

    public function __construct(
        protected AppUserUsecase $usecase
    ) {
        $this->baseRedirect = 'partner/'.$this->page['route'];
    }

    /**
     * Display a listing of app user registrations / requests submitted by this partner.
     */
    public function index(Request $request): View|Response
    {
        $filterData = [
            'keywords' => $request->get('keywords'),
            'approval_status' => $request->get('approval_status'),
            'visual_impairment_level' => $request->get('visual_impairment_level'),
        ];

        $data = $this->usecase->getAll($filterData);
        $list = $data['data']['list'] ?? [];
        $stats = $this->usecase->getStats();

        return view('_partner.app-users.index', [
            'data' => $list,
            'stats' => $stats,
            'page' => $this->page,
            'keywords' => $request->get('keywords'),
            'approval_status' => $request->get('approval_status'),
            'visual_impairment_level' => $request->get('visual_impairment_level'),
            'statuses' => AppUserConst::getApprovalStatuses(),
            'levels' => AppUserConst::getVisualImpairmentLevels(),
        ]);
    }

    /**
     * Show the form for registering a new visually impaired app user.
     */
    public function add(): View|Response
    {
        return view('_partner.app-users.add', [
            'page' => $this->page,
            'levels' => AppUserConst::getVisualImpairmentLevels(),
            'genders' => AppUserConst::getGenders(),
        ]);
    }

    /**
     * Store a new app user registration request.
     */
    public function doCreate(Request $request): RedirectResponse
    {
        $process = $this->usecase->create(data: $request);

        if ($process['success']) {
            return redirect()->route('partner.app_users.index')
                ->with('success', $process['message'] ?? 'Registration request submitted successfully.');
        }

        return redirect()->back()->withInput()
            ->with('error', $process['message'] ?? ResponseConst::DEFAULT_ERROR_MESSAGE);
    }

    /**
     * Display detailed information of an app user request.
     */
    public function detail(int $id): View|RedirectResponse|Response
    {
        $data = $this->usecase->getByID($id);

        if (empty($data['data'])) {
            return redirect()->route('partner.app_users.index')
                ->with('error', $data['message'] ?? 'App user record not found.');
        }

        return view('_partner.app-users.detail', [
            'data' => (object) $data['data'],
            'page' => $this->page,
        ]);
    }

    /**
     * Show the form for editing an app user request.
     */
    public function update(int $id): View|RedirectResponse|Response
    {
        $data = $this->usecase->getByID($id);

        if (empty($data['data'])) {
            return redirect()->route('partner.app_users.index')
                ->with('error', $data['message'] ?? 'App user record not found.');
        }

        return view('_partner.app-users.update', [
            'data' => (object) $data['data'],
            'page' => $this->page,
            'levels' => AppUserConst::getVisualImpairmentLevels(),
            'genders' => AppUserConst::getGenders(),
            'statuses' => AppUserConst::getApprovalStatuses(),
        ]);
    }

    /**
     * Update an app user registration request.
     */
    public function doUpdate(Request $request, int $id): RedirectResponse
    {
        $process = $this->usecase->update(data: $request, id: $id);

        if ($process['success']) {
            return redirect()->route('partner.app_users.index')
                ->with('success', $process['message'] ?? ResponseConst::SUCCESS_MESSAGE_UPDATED);
        }

        return redirect()->back()->withInput()
            ->with('error', $process['message'] ?? ResponseConst::DEFAULT_ERROR_MESSAGE);
    }

    /**
     * Soft-delete an app user registration request.
     */
    public function delete(int $id): RedirectResponse
    {
        $process = $this->usecase->delete($id);

        if ($process['success']) {
            return redirect()->route('partner.app_users.index')
                ->with('success', $process['message'] ?? ResponseConst::SUCCESS_MESSAGE_DELETED);
        }

        return redirect()->route('partner.app_users.index')
            ->with('error', $process['message'] ?? ResponseConst::DEFAULT_ERROR_MESSAGE);
    }

    /**
     * Re-submit a rejected app user registration request for verification.
     */
    public function resubmit(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'resubmission_note' => 'nullable|string|max:1000',
        ]);

        $process = $this->usecase->resubmit(
            id: $id,
            notes: $request->input('resubmission_note')
        );

        if ($process['success']) {
            return redirect()->route('partner.app_users.detail', $id)
                ->with('success', $process['message'] ?? 'Registration request re-submitted successfully.');
        }

        return redirect()->back()
            ->with('error', $process['message'] ?? ResponseConst::DEFAULT_ERROR_MESSAGE);
    }
}
