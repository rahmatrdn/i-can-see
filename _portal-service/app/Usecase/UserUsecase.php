<?php

namespace App\Usecase;

use App\Constants\DatabaseConst;
use App\Constants\ResponseConst;
use App\Constants\UserConst;
use App\Http\Presenter\Response;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserUsecase extends Usecase
{
    public function __construct() {}

    public function getAll(array $filterData = []): array
    {
        try {
            $query = DB::table(DatabaseConst::USER())
                ->whereNull('deleted_at')
                ->whereIn('access_type', array_keys(UserConst::getAppAccessTypes()))
                ->when($filterData['keywords'] ?? false, function ($query, $keywords) {
                    return $query->where(function ($q) use ($keywords) {
                        $q->where('name', 'like', '%'.$keywords.'%')
                            ->orWhere('email', 'like', '%'.$keywords.'%');
                    });
                })
                ->when($filterData['access_type'] ?? false, function ($query, $accessType) {
                    if ($accessType !== 'all') {
                        return $query->where('access_type', $accessType);
                    }
                })
                ->orderBy('created_at', 'desc');

            $data = empty($filterData['no_pagination'])
                ? $query->paginate(20)
                : $query->get();

            if (! empty($filterData) && method_exists($data, 'appends')) {
                $data->appends($filterData);
            }

            return Response::buildSuccess(
                [
                    'list' => $data,
                ],
                ResponseConst::HTTP_SUCCESS
            );
        } catch (Exception $e) {
            Log::error(
                message: $e->getMessage(),
                context: [
                    'method' => __METHOD__,
                ]
            );

            return Response::buildErrorService($e->getMessage());
        }
    }

    public function getByID(int $id): array
    {
        try {
            $data = DB::table(DatabaseConst::USER())
                ->whereNull('deleted_at')
                ->where('id', $id)
                ->first();

            return Response::buildSuccess(
                data: collect($data)->toArray()
            );
        } catch (Exception $e) {
            Log::error(
                message: $e->getMessage(),
                context: [
                    'method' => __METHOD__,
                ]
            );

            return Response::buildErrorService($e->getMessage());
        }
    }

    public function create(Request $data): array
    {
        $validator = Validator::make($data->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'access_type' => 'required|in:'.implode(',', array_keys(UserConst::getAppAccessTypes())),
        ]);

        $validator->validate();

        DB::beginTransaction();
        try {
            DB::table(DatabaseConst::USER())
                ->insert([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'access_type' => $data['access_type'],
                    'password' => UserConst::DEFAULT_PASSWORD,
                    'is_active' => 1,
                    'created_by' => Auth::user()?->id,
                    'created_at' => now(),
                ]);

            DB::commit();

            return Response::buildSuccessCreated();
        } catch (Exception $e) {
            DB::rollback();

            Log::error(
                message: $e->getMessage(),
                context: [
                    'method' => __METHOD__,
                ]
            );

            return Response::buildErrorService($e->getMessage());
        }
    }

    public function update(Request $data, int $id): array|Exception
    {
        $validator = Validator::make($data->all(), [
            'name' => 'required|min:4',
            'email' => 'required|email|unique:users,email,'.$id,
            'access_type' => 'required|in:'.implode(',', array_keys(UserConst::getAppAccessTypes())),
        ]);

        $validator->validate();

        $update = [
            'name' => $data['name'],
            'email' => $data['email'],
            'access_type' => $data['access_type'],
            'updated_by' => Auth::user()?->id,
            'updated_at' => now(),
        ];

        DB::beginTransaction();

        try {
            DB::table(DatabaseConst::USER())
                ->where('id', $id)
                ->update($update);

            DB::commit();

            return Response::buildSuccess(
                message: ResponseConst::SUCCESS_MESSAGE_UPDATED
            );
        } catch (Exception $e) {
            DB::rollback();

            Log::error(
                message: $e->getMessage(),
                context: [
                    'method' => __METHOD__,
                ]
            );

            return Response::buildErrorService($e->getMessage());
        }
    }

    public function delete(int $id): array
    {
        DB::beginTransaction();

        try {
            $delete = DB::table(DatabaseConst::USER())
                ->where('id', $id)
                ->update([
                    'deleted_by' => Auth::user()?->id,
                    'deleted_at' => now(),
                ]);

            if (! $delete) {
                DB::rollback();
                throw new Exception('FAILED DELETE DATA');
            }

            DB::commit();

            return Response::buildSuccess(
                message: ResponseConst::SUCCESS_MESSAGE_DELETED
            );
        } catch (Exception $e) {
            DB::rollback();

            Log::error(
                message: $e->getMessage(),
                context: [
                    'method' => __METHOD__,
                ]
            );

            return Response::buildErrorService($e->getMessage());
        }
    }

    public function changePassword(array $data): array
    {
        $userID = Auth::user()?->id;

        $validator = Validator::make($data, [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:6', 'different:current_password'],
        ]);

        $customAttributes = [
            'current_password' => 'Password Lama',
            'password' => 'Password Baru',
        ];
        $validator->setAttributeNames($customAttributes);
        $validator->validate();

        DB::beginTransaction();

        try {
            $locked = DB::table(DatabaseConst::USER())
                ->where('id', $userID)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->first(['id']);

            if (! $locked) {
                DB::rollback();

                throw new Exception('FAILED LOCKED DATA');
            }

            DB::table(DatabaseConst::USER())
                ->where('id', $userID)
                ->update([
                    'password' => Hash::make($data['password']),
                    'updated_by' => $userID,
                    'updated_at' => now(),
                ]);

            DB::commit();

            return Response::buildSuccess(
                message: ResponseConst::SUCCESS_MESSAGE_UPDATED
            );
        } catch (Exception $e) {
            DB::rollback();

            Log::error(
                message: $e->getMessage(),
                context: [
                    'method' => __METHOD__,
                ]
            );

            return Response::buildErrorService($e->getMessage());
        }
    }

    public function resetPassword(int $id): array
    {
        $defaultPassword = UserConst::DEFAULT_PASSWORD;

        DB::beginTransaction();

        try {
            DB::table(DatabaseConst::USER())
                ->where('id', $id)
                ->update([
                    'password' => Hash::make($defaultPassword),
                    'updated_by' => Auth::user()?->id,
                    'updated_at' => now(),
                ]);

            DB::commit();

            return Response::buildSuccess(
                message: 'Password berhasil direset ke '.UserConst::DEFAULT_PASSWORD
            );
        } catch (Exception $e) {
            DB::rollback();

            Log::error(
                message: $e->getMessage(),
                context: [
                    'method' => __METHOD__,
                ]
            );

            return Response::buildErrorService($e->getMessage());
        }
    }
}
