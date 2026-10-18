<?php

namespace App\Http\Controllers;

use App\Constants\PartnerConst;
use App\Constants\ResponseConst;
use App\Usecase\PartnerRegisterUsecase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PartnerRegisterController extends Controller
{
    public function __construct(
        protected PartnerRegisterUsecase $usecase
    ) {}

    public function register(): View
    {
        return view('_admin.auth.register', [
            'partnerTypes' => PartnerConst::getTypes(),
        ]);
    }

    public function doRegister(Request $request): RedirectResponse
    {
        $process = $this->usecase->register(data: $request);

        if ($process['success']) {
            return redirect()->route('login')
                ->with('success', $process['message'] ?? ResponseConst::SUCCESS_MESSAGE_CREATED);
        }

        return redirect()->back()->withInput()
            ->withErrors($process['data'] ?? [])
            ->with('error', $process['message'] ?? ResponseConst::DEFAULT_ERROR_MESSAGE);
    }
}
