<?php

namespace App\Http\Controllers\Admin;

use App\Constants\ResponseConst;
use App\Http\Controllers\Controller;
use App\Usecase\UserUsecase;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    protected array $page = [
        'route' => 'user',
        'title' => 'Pengguna Aplikasi',
    ];

    protected string $baseRedirect;

    public function __construct(
        protected UserUsecase $usecase
    ) {
        $this->baseRedirect = 'admin/'.$this->page['route'];
    }

    public function index(Request $request): View|Response
    {
        $data = $this->usecase->getAll([
            'keywords' => $request->get('keywords'),
            'access_type' => $request->get('access_type'),
        ]);
        $data = $data['data']['list'] ?? [];

        return view('_admin.users.index', [
            'data' => $data,
            'page' => $this->page,
            'keywords' => $request->get('keywords'),
            'access_type' => $request->get('access_type'),
        ]);
    }

    public function add(): View|Response
    {
        return view('_admin.users.add', [
            'page' => $this->page,
        ]);
    }

    public function doCreate(Request $request): RedirectResponse
    {
        $process = $this->usecase->create(
            data: $request,
        );

        if ($process['success']) {
            return redirect()
                ->route('admin.users.index')
                ->with('success', ResponseConst::SUCCESS_MESSAGE_CREATED);
        } else {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $process['message'] ?? ResponseConst::DEFAULT_ERROR_MESSAGE);
        }
    }

    public function detail(int $id): View|RedirectResponse|Response
    {
        $data = $this->usecase->getByID($id);

        if (empty($data['data'])) {
            return redirect()
                ->intended($this->baseRedirect)
                ->with('error', ResponseConst::DEFAULT_ERROR_MESSAGE);
        }
        $data = $data['data'] ?? [];

        return view('_admin.users.detail', [
            'data' => (object) $data,
            'page' => $this->page,
        ]);
    }

    public function update(int $id): View|RedirectResponse|Response
    {
        $data = $this->usecase->getByID($id);

        if (empty($data['data'])) {
            return redirect()
                ->intended($this->baseRedirect)
                ->with('error', ResponseConst::DEFAULT_ERROR_MESSAGE);
        }
        $data = $data['data'] ?? [];

        return view('_admin.users.update', [
            'data' => (object) $data,
            'userId' => $id,
            'page' => $this->page,
        ]);
    }

    public function doUpdate(int $id, Request $request): RedirectResponse
    {
        $process = $this->usecase->update(
            data: $request,
            id: $id,
        );

        if ($process['success']) {
            return redirect()
                ->route('admin.users.index')
                ->with('success', ResponseConst::SUCCESS_MESSAGE_UPDATED);
        } else {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $process['message'] ?? ResponseConst::DEFAULT_ERROR_MESSAGE);
        }
    }

    public function delete(int $id): RedirectResponse
    {
        $process = $this->usecase->delete(id: $id);

        if ($process['success']) {
            return redirect()
                ->route('admin.users.index')
                ->with('success', ResponseConst::SUCCESS_MESSAGE_DELETED);
        } else {
            return redirect()
                ->route('admin.users.index')
                ->with('error', $process['message'] ?? ResponseConst::DEFAULT_ERROR_MESSAGE);
        }
    }

    public function resetPassword(int $id): RedirectResponse
    {
        $resetProcess = $this->usecase->resetPassword(id: $id);

        if ($resetProcess['success']) {
            return redirect()
                ->route('admin.users.index')
                ->with('success', $resetProcess['message'] ?? 'Password berhasil direset');
        } else {
            return redirect()
                ->route('admin.users.index')
                ->with('error', $resetProcess['message'] ?? ResponseConst::DEFAULT_ERROR_MESSAGE);
        }
    }

    public function changePassword(): View
    {
        return view('_admin.profile.change_password');
    }

    public function doChangePassword(Request $request): RedirectResponse
    {
        $process = $this->usecase->changePassword($request->all());

        if ($process['success']) {
            return redirect()
                ->back()
                ->with('success', 'Password berhasil diubah.');
        } else {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $process['message'] ?? ResponseConst::DEFAULT_ERROR_MESSAGE);
        }
    }
}
