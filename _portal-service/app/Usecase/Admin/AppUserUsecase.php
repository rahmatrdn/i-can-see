<?php

namespace App\Usecase\Admin;

use App\Constants\AppUserConst;
use App\Constants\DatabaseConst;
use App\Constants\ResponseConst;
use App\Constants\UserConst;
use App\Http\Presenter\Response;
use App\Usecase\Usecase;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AppUserUsecase extends Usecase
{
    public function __construct() {}

    /**
     * Resolve the partner ID for the currently authenticated user if they are a Partner.
     */
    public function getCurrentPartnerId(): ?int
    {
        $user = Auth::user();
        if (! $user || (int) $user->access_type !== UserConst::PARTNER) {
            return null;
        }

        $partner = DB::table(DatabaseConst::REGISTRATION_PARTNER())
            ->whereNull('deleted_at')
            ->where('user_id', $user->id)
            ->first();

        return $partner?->id;
    }

    /**
     * Get all app users with filters and pagination.
     *
     * @param  array{keywords?: string, approval_status?: string|int, visual_impairment_level?: string|int, no_pagination?: bool}  $filterData
     */
    public function getAll(array $filterData = []): array
    {
        try {
            $partnerId = $this->getCurrentPartnerId();

            $query = DB::table(DatabaseConst::APP_USER().' as au')
                ->leftJoin(DatabaseConst::REGISTRATION_PARTNER().' as rp', 'au.registration_partner_id', '=', 'rp.id')
                ->select([
                    'au.*',
                    'rp.name as partner_name',
                    'rp.type as partner_type',
                ])
                ->whereNull('au.deleted_at')
                ->when($partnerId !== null, function ($query) use ($partnerId) {
                    return $query->where('au.registration_partner_id', $partnerId);
                })
                ->when($filterData['keywords'] ?? false, function ($query, $keywords) {
                    return $query->where(function ($q) use ($keywords) {
                        $q->where('au.name', 'like', '%'.$keywords.'%')
                            ->orWhere('au.phone_number', 'like', '%'.$keywords.'%')
                            ->orWhere('au.email', 'like', '%'.$keywords.'%')
                            ->orWhere('au.national_id_number', 'like', '%'.$keywords.'%')
                            ->orWhere('au.guardian_name', 'like', '%'.$keywords.'%');
                    });
                })
                ->when(isset($filterData['approval_status']) && $filterData['approval_status'] !== '' && $filterData['approval_status'] !== 'all', function ($query) use ($filterData) {
                    return $query->where('au.approval_status', (int) $filterData['approval_status']);
                })
                ->when(isset($filterData['visual_impairment_level']) && $filterData['visual_impairment_level'] !== '' && $filterData['visual_impairment_level'] !== 'all', function ($query) use ($filterData) {
                    return $query->where('au.visual_impairment_level', (int) $filterData['visual_impairment_level']);
                })
                ->orderBy('au.created_at', 'desc');

            if (! empty($filterData['no_pagination'])) {
                $data = $query->get();
            } else {
                $data = $query->paginate(20);
                if (! empty($filterData)) {
                    $data->appends($filterData);
                }
            }

            return Response::buildSuccess(['list' => $data], ResponseConst::HTTP_SUCCESS);
        } catch (Exception $e) {
            Log::error(message: $e->getMessage(), context: ['method' => __METHOD__]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * Get a single app user by ID.
     */
    public function getByID(int $id): array
    {
        try {
            $partnerId = $this->getCurrentPartnerId();

            $query = DB::table(DatabaseConst::APP_USER().' as au')
                ->leftJoin(DatabaseConst::REGISTRATION_PARTNER().' as rp', 'au.registration_partner_id', '=', 'rp.id')
                ->select([
                    'au.*',
                    'rp.name as partner_name',
                    'rp.type as partner_type',
                    'rp.pic_name as partner_pic_name',
                    'rp.pic_phone as partner_pic_phone',
                ])
                ->whereNull('au.deleted_at')
                ->where('au.id', $id)
                ->when($partnerId !== null, function ($query) use ($partnerId) {
                    return $query->where('au.registration_partner_id', $partnerId);
                });

            $data = $query->first();

            if (! $data) {
                return Response::buildErrorNotFound('App User record not found.');
            }

            return Response::buildSuccess(data: collect($data)->toArray());
        } catch (Exception $e) {
            Log::error(message: $e->getMessage(), context: ['method' => __METHOD__]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * Create a new app user registration request.
     */
    public function create(Request $data): array
    {
        $validator = Validator::make($data->all(), [
            'name' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:100',
            'phone_number' => 'nullable|string|max:50',
            'username' => 'required|string|max:255|unique:users,email|unique:app_users,email',
            'password' => 'required|string|min:6|confirmed',
            'gender' => 'nullable|in:1,2',
            'date_of_birth' => 'nullable|date',
            'national_id_number' => 'nullable|string|max:50',
            'visual_impairment_level' => 'required|in:1,2',
            'visual_impairment_cause' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:50',
            'guardian_relation' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ], [
            'username.required' => 'Username is required.',
            'username.unique' => 'Username already exists. Please choose a different username.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        $validator->validate();

        $partnerId = $this->getCurrentPartnerId();
        $isSuperadmin = Auth::user() && (int) Auth::user()->access_type === UserConst::SUPERADMIN;

        // If created by partner, default status is PENDING (0).
        $approvalStatus = AppUserConst::STATUS_PENDING;
        $targetPartnerId = $partnerId;

        if ($isSuperadmin) {
            $targetPartnerId = $data->input('registration_partner_id') ?: $partnerId;
            $approvalStatus = (int) ($data->input('approval_status', AppUserConst::STATUS_APPROVED));
        }

        $isUserActive = $approvalStatus === AppUserConst::STATUS_APPROVED ? 1 : 0;
        $isAppActive = $approvalStatus === AppUserConst::STATUS_APPROVED ? AppUserConst::ACTIVE : AppUserConst::INACTIVE;

        DB::beginTransaction();
        try {
            // 1. Create user in users table for mobile app login (inactive until verified)
            DB::table(DatabaseConst::USER())->insert([
                'name' => $data['name'],
                'email' => $data['username'],
                'password' => Hash::make($data['password']),
                'access_type' => UserConst::APP_USER,
                'is_active' => $isUserActive,
                'created_by' => Auth::user()?->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Create app user details in app_users table
            DB::table(DatabaseConst::APP_USER())->insert([
                'registration_partner_id' => $targetPartnerId,
                'name' => $data['name'],
                'nickname' => $data['nickname'] ?: null,
                'phone_number' => $data['phone_number'] ?: null,
                'email' => $data['username'],
                'gender' => $data['gender'] ? (int) $data['gender'] : null,
                'date_of_birth' => $data['date_of_birth'] ?: null,
                'national_id_number' => $data['national_id_number'] ?: null,
                'visual_impairment_level' => (int) $data['visual_impairment_level'],
                'visual_impairment_cause' => $data['visual_impairment_cause'] ?: null,
                'address' => $data['address'] ?: null,
                'city' => $data['city'] ?: null,
                'province' => $data['province'] ?: null,
                'guardian_name' => $data['guardian_name'] ?: null,
                'guardian_phone' => $data['guardian_phone'] ?: null,
                'guardian_relation' => $data['guardian_relation'] ?: null,
                'is_active' => $isAppActive,
                'approval_status' => $approvalStatus,
                'notes' => $data['notes'] ?: null,
                'created_by' => Auth::user()?->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return Response::buildSuccessCreated(
                message: 'App user registration request submitted successfully.'
            );
        } catch (Exception $e) {
            DB::rollback();
            Log::error(message: $e->getMessage(), context: ['method' => __METHOD__]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * Update an app user registration request.
     */
    public function update(Request $data, int $id): array
    {
        $validator = Validator::make($data->all(), [
            'name' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:100',
            'phone_number' => 'nullable|string|max:50',
            'email' => 'nullable|string|max:255',
            'gender' => 'nullable|in:1,2',
            'date_of_birth' => 'nullable|date',
            'national_id_number' => 'nullable|string|max:50',
            'visual_impairment_level' => 'required|in:1,2',
            'visual_impairment_cause' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:50',
            'guardian_relation' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $validator->validate();

        $partnerId = $this->getCurrentPartnerId();
        $isSuperadmin = Auth::user() && (int) Auth::user()->access_type === UserConst::SUPERADMIN;

        // Check record existence and ownership
        $existing = DB::table(DatabaseConst::APP_USER())
            ->whereNull('deleted_at')
            ->where('id', $id)
            ->when($partnerId !== null, fn ($q) => $q->where('registration_partner_id', $partnerId))
            ->first();

        if (! $existing) {
            return Response::buildErrorNotFound('App user record not found or access denied.');
        }

        DB::beginTransaction();
        try {
            $payload = [
                'name' => $data['name'],
                'nickname' => $data['nickname'] ?: null,
                'phone_number' => $data['phone_number'] ?: null,
                'email' => $data['email'] ?: null,
                'gender' => $data['gender'] ? (int) $data['gender'] : null,
                'date_of_birth' => $data['date_of_birth'] ?: null,
                'national_id_number' => $data['national_id_number'] ?: null,
                'visual_impairment_level' => (int) $data['visual_impairment_level'],
                'visual_impairment_cause' => $data['visual_impairment_cause'] ?: null,
                'address' => $data['address'] ?: null,
                'city' => $data['city'] ?: null,
                'province' => $data['province'] ?: null,
                'guardian_name' => $data['guardian_name'] ?: null,
                'guardian_phone' => $data['guardian_phone'] ?: null,
                'guardian_relation' => $data['guardian_relation'] ?: null,
                'notes' => $data['notes'] ?: null,
                'updated_by' => Auth::user()?->id,
                'updated_at' => now(),
            ];

            // If partner updates a rejected request, reset status back to PENDING for re-review
            if ($partnerId !== null && (int) $existing->approval_status === AppUserConst::STATUS_REJECTED) {
                $payload['approval_status'] = AppUserConst::STATUS_PENDING;
                $payload['rejection_reason'] = null;
            }

            // If superadmin updates, allow changing approval_status and rejection_reason
            if ($isSuperadmin) {
                if ($data->has('approval_status')) {
                    $payload['approval_status'] = (int) $data->input('approval_status');
                }
                if ($data->has('rejection_reason')) {
                    $payload['rejection_reason'] = $data->input('rejection_reason');
                }
            }

            DB::table(DatabaseConst::APP_USER())->where('id', $id)->update($payload);

            DB::commit();

            return Response::buildSuccess(message: ResponseConst::SUCCESS_MESSAGE_UPDATED);
        } catch (Exception $e) {
            DB::rollback();
            Log::error(message: $e->getMessage(), context: ['method' => __METHOD__]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * Soft-delete an app user registration request.
     */
    public function delete(int $id): array
    {
        $partnerId = $this->getCurrentPartnerId();

        $existing = DB::table(DatabaseConst::APP_USER())
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->when($partnerId !== null, fn ($q) => $q->where('registration_partner_id', $partnerId))
            ->first();

        if (! $existing) {
            return Response::buildErrorNotFound('App user record not found or access denied.');
        }

        DB::beginTransaction();
        try {
            DB::table(DatabaseConst::APP_USER())
                ->where('id', $id)
                ->update([
                    'is_active' => AppUserConst::INACTIVE,
                    'deleted_by' => Auth::user()?->id,
                    'deleted_at' => now(),
                ]);

            if (! empty($existing->email)) {
                DB::table(DatabaseConst::USER())
                    ->where('email', $existing->email)
                    ->update([
                        'is_active' => 0,
                        'deleted_by' => Auth::user()?->id,
                        'deleted_at' => now(),
                    ]);
            }

            DB::commit();

            return Response::buildSuccess(message: ResponseConst::SUCCESS_MESSAGE_DELETED);
        } catch (Exception $e) {
            DB::rollback();
            Log::error(message: $e->getMessage(), context: ['method' => __METHOD__]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * Get statistics for app user requests (total, pending, approved, rejected).
     *
     * @return array{total: int, pending: int, approved: int, rejected: int}
     */
    public function getStats(): array
    {
        try {
            $partnerId = $this->getCurrentPartnerId();

            $baseQuery = DB::table(DatabaseConst::APP_USER())
                ->whereNull('deleted_at')
                ->when($partnerId !== null, fn ($q) => $q->where('registration_partner_id', $partnerId));

            $total = (clone $baseQuery)->count();
            $pending = (clone $baseQuery)->where('approval_status', AppUserConst::STATUS_PENDING)->count();
            $approved = (clone $baseQuery)->where('approval_status', AppUserConst::STATUS_APPROVED)->count();
            $rejected = (clone $baseQuery)->where('approval_status', AppUserConst::STATUS_REJECTED)->count();

            return [
                'total' => $total,
                'pending' => $pending,
                'approved' => $approved,
                'rejected' => $rejected,
            ];
        } catch (Exception $e) {
            Log::error(message: $e->getMessage(), context: ['method' => __METHOD__]);

            return [
                'total' => 0,
                'pending' => 0,
                'approved' => 0,
                'rejected' => 0,
            ];
        }
    }

    /**
     * Verify (Approve or Reject) an app user registration request.
     */
    public function verify(int $id, int $status, ?string $rejectionReason = null): array
    {
        $existing = DB::table(DatabaseConst::APP_USER())
            ->whereNull('deleted_at')
            ->where('id', $id)
            ->first();

        if (! $existing) {
            return Response::buildErrorNotFound('App user record not found.');
        }

        if ($status === AppUserConst::STATUS_REJECTED && empty(trim((string) $rejectionReason))) {
            return Response::buildErrorService('Rejection reason is required when rejecting a user registration.');
        }

        DB::beginTransaction();
        try {
            $isApproved = $status === AppUserConst::STATUS_APPROVED;

            $payload = [
                'approval_status' => $status,
                'is_active' => $isApproved ? AppUserConst::ACTIVE : AppUserConst::INACTIVE,
                'rejection_reason' => $status === AppUserConst::STATUS_REJECTED ? trim($rejectionReason) : null,
                'updated_by' => Auth::user()?->id,
                'updated_at' => now(),
            ];

            DB::table(DatabaseConst::APP_USER())->where('id', $id)->update($payload);

            // Synchronize login status in users table upon verification
            if (! empty($existing->email)) {
                DB::table(DatabaseConst::USER())
                    ->where('email', $existing->email)
                    ->update([
                        'is_active' => $isApproved ? 1 : 0,
                        'updated_by' => Auth::user()?->id,
                        'updated_at' => now(),
                    ]);
            }

            DB::commit();

            $statusLabel = $status === AppUserConst::STATUS_APPROVED ? 'approved' : 'rejected';

            return Response::buildSuccess(
                message: "App user registration has been successfully {$statusLabel}."
            );
        } catch (Exception $e) {
            DB::rollback();
            Log::error(message: $e->getMessage(), context: ['method' => __METHOD__]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * Re-submit an app user registration request for verification (used by partner).
     */
    public function resubmit(int $id, ?string $notes = null): array
    {
        $partnerId = $this->getCurrentPartnerId();

        $existing = DB::table(DatabaseConst::APP_USER())
            ->whereNull('deleted_at')
            ->where('id', $id)
            ->when($partnerId !== null, fn ($q) => $q->where('registration_partner_id', $partnerId))
            ->first();

        if (! $existing) {
            return Response::buildErrorNotFound('App user record not found or access denied.');
        }

        DB::beginTransaction();
        try {
            $payload = [
                'approval_status' => AppUserConst::STATUS_PENDING,
                'is_active' => AppUserConst::INACTIVE,
                'rejection_reason' => null,
                'updated_by' => Auth::user()?->id,
                'updated_at' => now(),
            ];

            if (! empty(trim((string) $notes))) {
                $timestamp = now()->format('d M Y, H:i');
                $noteEntry = "[Resubmission Note - {$timestamp}]: ".trim($notes);
                $payload['notes'] = ! empty($existing->notes)
                    ? $existing->notes."\n\n".$noteEntry
                    : $noteEntry;
            }

            DB::table(DatabaseConst::APP_USER())->where('id', $id)->update($payload);

            // Keep login deactivated while re-submission is pending approval
            if (! empty($existing->email)) {
                DB::table(DatabaseConst::USER())
                    ->where('email', $existing->email)
                    ->update([
                        'is_active' => 0,
                        'updated_by' => Auth::user()?->id,
                        'updated_at' => now(),
                    ]);
            }

            DB::commit();

            return Response::buildSuccess(
                message: 'App user registration request has been successfully re-submitted for approval.'
            );
        } catch (Exception $e) {
            DB::rollback();
            Log::error(message: $e->getMessage(), context: ['method' => __METHOD__]);

            return Response::buildErrorService($e->getMessage());
        }
    }
}
