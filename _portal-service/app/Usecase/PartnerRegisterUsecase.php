<?php

namespace App\Usecase;

use App\Constants\DatabaseConst;
use App\Constants\PartnerConst;
use App\Constants\ResponseConst;
use App\Constants\UserConst;
use App\Http\Presenter\Response;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PartnerRegisterUsecase extends Usecase
{
    public function register(Request $data): array
    {
        $validator = Validator::make($data->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|max:8|confirmed',
            'partner_name' => 'required|string|max:255',
            'partner_type' => 'required|in:'.implode(',', array_keys(PartnerConst::getTypes())),
            'pic_phone' => 'required|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ], [
            'name.required' => 'Person in charge (PIC) name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Invalid email address format.',
            'email.unique' => 'Email is already registered. Please sign in or use another email.',
            'password.required' => 'Password is required.',
            'password.max' => 'Password must not exceed 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'partner_name.required' => 'Organization or partner name is required.',
            'partner_type.required' => 'Partner type is required.',
            'pic_phone.required' => 'Phone or WhatsApp number is required.',
        ]);

        if ($validator->fails()) {
            return Response::buildError(
                ResponseConst::HTTP_BAD_REQUEST,
                $validator->errors()->first(),
                $validator->errors()->toArray()
            );
        }

        DB::beginTransaction();
        try {
            $userId = DB::table(DatabaseConst::USER())->insertGetId([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'access_type' => UserConst::PARTNER,
                'is_active' => 1,
                'created_at' => now(),
            ]);

            DB::table(DatabaseConst::REGISTRATION_PARTNER())->insert([
                'user_id' => $userId,
                'name' => $data['partner_name'],
                'type' => $data['partner_type'],
                'pic_name' => $data['name'],
                'pic_phone' => $data['pic_phone'],
                'pic_email' => $data['email'],
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'province' => $data['province'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'verification_status' => PartnerConst::STATUS_PENDING,
                'notes' => $data['notes'] ?? null,
                'is_active' => 1,
                'created_by' => $userId,
                'created_at' => now(),
            ]);

            DB::commit();

            return Response::buildSuccessCreated(
                ['user_id' => $userId],
                'Partner registration successful! Please sign in with your account credentials.'
            );
        } catch (Exception $e) {
            DB::rollback();

            Log::error(
                message: $e->getMessage(),
                context: ['method' => __METHOD__]
            );

            return Response::buildErrorService(ResponseConst::DEFAULT_ERROR_MESSAGE);
        }
    }
}
