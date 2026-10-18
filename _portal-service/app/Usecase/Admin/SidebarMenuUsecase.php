<?php

namespace App\Usecase\Admin;

use App\Constants\DatabaseConst;
use App\Constants\ResponseConst;
use App\Constants\UserConst;
use App\Http\Presenter\Response;
use App\Usecase\Usecase;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SidebarMenuUsecase extends Usecase
{
    /**
     * Create a new class instance.
     */
    public function __construct() {}

    /**
     * Get all sidebar menus with pagination and filters.
     *
     * @param  array{keywords?: string, group?: string, no_pagination?: bool}  $filterData
     */
    public function getAll(array $filterData = []): array
    {
        try {
            $query = DB::table(DatabaseConst::SIDEBAR_MENU().' as sm')
                ->leftJoin(DatabaseConst::SIDEBAR_MENU().' as parent', 'sm.parent_id', '=', 'parent.id')
                ->select(
                    'sm.*',
                    'parent.label as parent_label',
                    DB::raw('(SELECT COUNT(*) FROM '.DatabaseConst::SIDEBAR_MENU_ACCESS().' WHERE sidebar_menu_id = sm.id) as access_count')
                )
                ->whereNull('sm.deleted_at')
                ->when($filterData['keywords'] ?? false, function ($query, $keywords) {
                    return $query->where('sm.label', 'like', '%'.$keywords.'%');
                })
                ->when($filterData['group'] ?? false, function ($query, $group) {
                    return $query->where('sm.group', $group);
                })
                ->orderBy('sm.group')
                ->orderBy('sm.sort_order');

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
     * Get a single sidebar menu record by ID.
     */
    public function getByID(int $id): array
    {
        try {
            $data = DB::table(DatabaseConst::SIDEBAR_MENU())
                ->whereNull('deleted_at')
                ->where('id', $id)
                ->first();

            return Response::buildSuccess(data: collect($data)->toArray());
        } catch (Exception $e) {
            Log::error(message: $e->getMessage(), context: ['method' => __METHOD__]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * Get all parent menus (no parent_id) for dropdown options.
     */
    public function getParentOptions(): array
    {
        try {
            $data = DB::table(DatabaseConst::SIDEBAR_MENU())
                ->whereNull('deleted_at')
                ->whereNull('parent_id')
                ->orderBy('group')
                ->orderBy('sort_order')
                ->get(['id', 'label', 'group'])
                ->toArray();

            return Response::buildSuccess(data: $data);
        } catch (Exception $e) {
            Log::error(message: $e->getMessage(), context: ['method' => __METHOD__]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * Create a new sidebar menu item.
     */
    public function create(Request $data): array
    {
        $validator = Validator::make($data->all(), [
            'label' => 'required|string|max:255',
            'group' => ['required', Rule::in($this->getGroupKeys())],
            'route_name' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'parent_id' => 'nullable|integer',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|in:0,1',
        ]);

        $validator->validate();

        DB::beginTransaction();
        try {
            DB::table(DatabaseConst::SIDEBAR_MENU())->insert([
                'parent_id' => $data['parent_id'] ?: null,
                'label' => $data['label'],
                'route_name' => $data['route_name'] ?: null,
                'icon' => $data['icon'] ?: null,
                'group' => $data['group'],
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'is_active' => (int) ($data['is_active'] ?? 1),
                'created_by' => Auth::user()?->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            $this->flushSidebarCache();

            return Response::buildSuccessCreated();
        } catch (Exception $e) {
            DB::rollback();
            Log::error(message: $e->getMessage(), context: ['method' => __METHOD__]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * Update an existing sidebar menu item.
     */
    public function update(Request $data, int $id): array
    {
        $validator = Validator::make($data->all(), [
            'label' => 'required|string|max:255',
            'group' => ['required', Rule::in($this->getGroupKeys())],
            'route_name' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'parent_id' => 'nullable|integer',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|in:0,1',
        ]);

        $validator->validate();

        DB::beginTransaction();
        try {
            DB::table(DatabaseConst::SIDEBAR_MENU())->where('id', $id)->update([
                'parent_id' => $data['parent_id'] ?: null,
                'label' => $data['label'],
                'route_name' => $data['route_name'] ?: null,
                'icon' => $data['icon'] ?: null,
                'group' => $data['group'],
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'is_active' => (int) ($data['is_active'] ?? 1),
                'updated_by' => Auth::user()?->id,
                'updated_at' => now(),
            ]);

            DB::commit();
            $this->flushSidebarCache();

            return Response::buildSuccess(message: ResponseConst::SUCCESS_MESSAGE_UPDATED);
        } catch (Exception $e) {
            DB::rollback();
            Log::error(message: $e->getMessage(), context: ['method' => __METHOD__]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * Soft-delete a sidebar menu item.
     */
    public function delete(int $id): array
    {
        DB::beginTransaction();
        try {
            $deleted = DB::table(DatabaseConst::SIDEBAR_MENU())->where('id', $id)->update([
                'deleted_by' => Auth::user()?->id,
                'deleted_at' => now(),
            ]);

            if (! $deleted) {
                DB::rollback();
                throw new Exception('FAILED DELETE DATA');
            }

            DB::commit();
            $this->flushSidebarCache();

            return Response::buildSuccess(message: ResponseConst::SUCCESS_MESSAGE_DELETED);
        } catch (Exception $e) {
            DB::rollback();
            Log::error(message: $e->getMessage(), context: ['method' => __METHOD__]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * Get access_types currently assigned to a sidebar menu item.
     */
    public function getAccesses(int $sidebarMenuId): array
    {
        try {
            $data = DB::table(DatabaseConst::SIDEBAR_MENU_ACCESS())
                ->where('sidebar_menu_id', $sidebarMenuId)
                ->pluck('access_type')
                ->toArray();

            return Response::buildSuccess(data: $data);
        } catch (Exception $e) {
            Log::error(message: $e->getMessage(), context: ['method' => __METHOD__]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * Sync access types for a sidebar menu item (delete all then re-insert).
     *
     * @param  array<int>  $accessTypes
     */
    public function syncAccess(int $sidebarMenuId, array $accessTypes): array
    {
        DB::beginTransaction();
        try {
            DB::table(DatabaseConst::SIDEBAR_MENU_ACCESS())
                ->where('sidebar_menu_id', $sidebarMenuId)
                ->delete();

            $inserts = array_map(fn ($type) => [
                'sidebar_menu_id' => $sidebarMenuId,
                'access_type' => (int) $type,
                'created_by' => Auth::user()?->id,
                'created_at' => now(),
            ], $accessTypes);

            if (! empty($inserts)) {
                DB::table(DatabaseConst::SIDEBAR_MENU_ACCESS())->insert($inserts);
            }

            DB::commit();
            $this->flushSidebarCache();

            return Response::buildSuccess(message: ResponseConst::SUCCESS_MESSAGE_UPDATED);
        } catch (Exception $e) {
            DB::rollback();
            Log::error(message: $e->getMessage(), context: ['method' => __METHOD__]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * Get all sidebar menu groups from the database (cached).
     *
     * @return array{success: bool, data: array<object>}
     */
    public function getGroups(): array
    {
        try {
            $data = Cache::remember('sidebar_menu_groups', now()->addWeek(), function () {
                return DB::table(DatabaseConst::SIDEBAR_MENU_GROUP())
                    ->orderBy('sort_order')
                    ->get()
                    ->toArray();
            });

            return Response::buildSuccess(data: $data);
        } catch (Exception $e) {
            Log::error(message: $e->getMessage(), context: ['method' => __METHOD__]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * Get only the group key strings (e.g. ['utama']).
     *
     * @return array<string>
     */
    public function getGroupKeys(): array
    {
        $groups = $this->getGroups();

        return array_column((array) ($groups['data'] ?? []), 'key');
    }

    /**
     * Get top-level sidebar modules for the dashboard "Modul Aplikasi" section.
     * Uses group "utama", excludes the dashboard link itself.
     *
     * @return array{success: bool, data: array<object>}
     */
    public function getDashboardModules(int $accessType): array
    {
        try {
            $result = $this->getMenusForSidebar($accessType, 'utama');
            $modules = collect($result['data'] ?? [])
                ->filter(fn ($menu) => ! empty($menu->route_name) && $menu->route_name !== 'admin.dashboard')
                ->values()
                ->toArray();

            return Response::buildSuccess(data: $modules);
        } catch (Exception $e) {
            Log::error(message: $e->getMessage(), context: ['method' => __METHOD__]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * Build hierarchical menu structure for sidebar rendering.
     * Returns parents with their children nested, filtered by access_type and group.
     *
     * @return array{success: bool, data: array<object>}
     */
    public function getMenusForSidebar(int $accessType, string $group): array
    {
        $cacheKey = "sidebar_menus_{$accessType}_{$group}";

        try {
            $nested = Cache::remember($cacheKey, now()->addDay(), function () use ($accessType, $group) {
                $accessibleIds = DB::table(DatabaseConst::SIDEBAR_MENU_ACCESS())
                    ->where('access_type', $accessType)
                    ->pluck('sidebar_menu_id')
                    ->toArray();

                if (empty($accessibleIds)) {
                    return [];
                }

                $allMenus = DB::table(DatabaseConst::SIDEBAR_MENU())
                    ->whereNull('deleted_at')
                    ->where('is_active', 1)
                    ->where('group', $group)
                    ->whereIn('id', $accessibleIds)
                    ->orderBy('sort_order')
                    ->get();

                $parents = $allMenus->whereNull('parent_id')->values();

                return $parents->map(function ($parent) use ($allMenus) {
                    $children = $allMenus->where('parent_id', $parent->id)->values();
                    $parent->children = $children;

                    return $parent;
                })->toArray();
            });

            return Response::buildSuccess(data: $nested);
        } catch (Exception $e) {
            Log::error(message: $e->getMessage(), context: ['method' => __METHOD__]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * Get all active menus grouped by group, with is_enabled flag for the given access_type.
     * Returns structure: ['utama' => [...menus], ...]
     * Each menu has children nested under parents, and an is_enabled boolean.
     *
     * @return array{success: bool, data: array<string, array<object>>}
     */
    public function getAllMenusForRole(int $accessType): array
    {
        try {
            $enabledIds = DB::table(DatabaseConst::SIDEBAR_MENU_ACCESS())
                ->where('access_type', $accessType)
                ->pluck('sidebar_menu_id')
                ->toArray();

            $allMenus = DB::table(DatabaseConst::SIDEBAR_MENU())
                ->whereNull('deleted_at')
                ->orderBy('group')
                ->orderBy('sort_order')
                ->get();

            $groups = $this->getGroupKeys();
            $result = [];

            foreach ($groups as $group) {
                $groupMenus = $allMenus->where('group', $group);
                $parents = $groupMenus->whereNull('parent_id')->values();

                $nested = $parents->map(function ($parent) use ($groupMenus, $enabledIds) {
                    $parent->is_enabled = in_array($parent->id, $enabledIds);
                    $children = $groupMenus->where('parent_id', $parent->id)->values();
                    $parent->children = $children->map(function ($child) use ($enabledIds) {
                        $child->is_enabled = in_array($child->id, $enabledIds);

                        return $child;
                    });

                    return $parent;
                });

                $result[$group] = $nested->toArray();
            }

            return Response::buildSuccess(data: $result);
        } catch (Exception $e) {
            Log::error(message: $e->getMessage(), context: ['method' => __METHOD__]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * Replace all menu access records for a given access_type with the provided menu IDs.
     *
     * @param  array<int>  $menuIds
     */
    public function syncMenusForRole(int $accessType, array $menuIds): array
    {
        DB::beginTransaction();
        try {
            DB::table(DatabaseConst::SIDEBAR_MENU_ACCESS())
                ->where('access_type', $accessType)
                ->delete();

            $inserts = array_map(fn ($id) => [
                'sidebar_menu_id' => (int) $id,
                'access_type' => $accessType,
                'created_by' => Auth::user()?->id,
                'created_at' => now(),
            ], $menuIds);

            if (! empty($inserts)) {
                DB::table(DatabaseConst::SIDEBAR_MENU_ACCESS())->insert($inserts);
            }

            DB::commit();
            $this->flushSidebarCache();

            return Response::buildSuccess(message: ResponseConst::SUCCESS_MESSAGE_UPDATED);
        } catch (Exception $e) {
            DB::rollback();
            Log::error(message: $e->getMessage(), context: ['method' => __METHOD__]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * Flush all sidebar menu cache keys for every access_type + group combination.
     */
    public function flushSidebarCache(): void
    {
        Cache::forget('sidebar_menu_groups');

        $groupKeys = DB::table(DatabaseConst::SIDEBAR_MENU_GROUP())->pluck('key')->toArray();
        $accessTypes = [
            UserConst::SUPERADMIN,
            UserConst::PARTNER,
        ];

        foreach ($accessTypes as $accessType) {
            foreach ($groupKeys as $group) {
                Cache::forget("sidebar_menus_{$accessType}_{$group}");
            }
        }
    }

    /**
     * Flush and rebuild sidebar menu cache for all roles and groups.
     *
     * @return array{success: bool, message?: string, data?: array{refreshed: array<int, array{access_type: int, group: string}>, count: int}}
     */
    public function refreshSidebarCache(): array
    {
        try {
            $this->flushSidebarCache();

            $accessTypes = [
                UserConst::SUPERADMIN,
                UserConst::PARTNER,
            ];

            $groups = $this->getGroupKeys();
            $refreshed = [];

            foreach ($accessTypes as $accessType) {
                foreach ($groups as $groupKey) {
                    $this->getMenusForSidebar($accessType, $groupKey);
                    $refreshed[] = [
                        'access_type' => $accessType,
                        'group' => $groupKey,
                    ];
                }
            }

            return Response::buildSuccess(
                data: [
                    'refreshed' => $refreshed,
                    'count' => count($refreshed),
                ],
                message: 'Cache menu sidebar berhasil diperbarui.',
            );
        } catch (Exception $e) {
            Log::error(message: $e->getMessage(), context: ['method' => __METHOD__]);

            return Response::buildErrorService($e->getMessage());
        }
    }
}
