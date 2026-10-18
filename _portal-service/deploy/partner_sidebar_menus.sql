-- ==============================================================================
-- Deploy Script: Partner Sidebar Menus & App Users Approval Schema
-- Generated for iCanSee Portal Service (Partner Portal: prefix 'partner.')
-- ==============================================================================

-- 1. Ensure columns exist in `app_users` for registration requests & approval
ALTER TABLE `app_users` 
    ADD COLUMN IF NOT EXISTS `approval_status` TINYINT NOT NULL DEFAULT 0 COMMENT '0: Pending, 1: Approved, 2: Rejected' AFTER `is_active`,
    ADD COLUMN IF NOT EXISTS `rejection_reason` TEXT NULL COMMENT 'Reason if registration request is rejected' AFTER `approval_status`,
    ADD INDEX IF NOT EXISTS `app_users_approval_status_index` (`approval_status`);

-- 2. Ensure group 'utama' exists in `sidebar_menu_groups`
INSERT INTO `sidebar_menu_groups` (`key`, `label`, `color`, `sort_order`, `created_at`, `updated_at`)
SELECT 'utama', 'Utama', 'blue', 10, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `sidebar_menu_groups` WHERE `key` = 'utama');

-- 3. Ensure 'Dashboard' for Partner exists in `sidebar_menus` (partner.dashboard)
INSERT INTO `sidebar_menus` (`label`, `route_name`, `icon`, `group`, `sort_order`, `is_active`, `created_at`, `updated_at`)
SELECT 'Dashboard', 'partner.dashboard', '_admin._layout.icons.sidebar.dashboard', 'utama', 1, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `sidebar_menus` WHERE `route_name` = 'partner.dashboard' AND `deleted_at` IS NULL);

-- 4. Ensure 'App Users (Visual Impairment)' for Partner exists in `sidebar_menus` (partner.app_users.index)
INSERT INTO `sidebar_menus` (`label`, `route_name`, `icon`, `group`, `sort_order`, `is_active`, `created_at`, `updated_at`)
SELECT 'App Users (Visual Impairment)', 'partner.app_users.index', '_admin._layout.icons.sidebar.user', 'utama', 2, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `sidebar_menus` WHERE `route_name` = 'partner.app_users.index' AND `deleted_at` IS NULL);

-- 5. Clean up any previous partner access pointing to admin.dashboard or admin.app_users.index
DELETE sma FROM `sidebar_menu_accesses` sma
JOIN `sidebar_menus` sm ON sma.`sidebar_menu_id` = sm.`id`
WHERE sma.`access_type` = 2 
  AND sm.`route_name` IN ('admin.dashboard', 'admin.app_users.index');

-- 6. Grant Partner (access_type = 2) access to partner.dashboard
INSERT INTO `sidebar_menu_accesses` (`sidebar_menu_id`, `access_type`, `created_at`)
SELECT sm.`id`, 2, NOW()
FROM `sidebar_menus` sm
WHERE sm.`route_name` = 'partner.dashboard' AND sm.`deleted_at` IS NULL
  AND NOT EXISTS (
      SELECT 1 FROM `sidebar_menu_accesses` sma 
      WHERE sma.`sidebar_menu_id` = sm.`id` AND sma.`access_type` = 2
  );

-- 7. Grant Partner (access_type = 2) access to partner.app_users.index
INSERT INTO `sidebar_menu_accesses` (`sidebar_menu_id`, `access_type`, `created_at`)
SELECT sm.`id`, 2, NOW()
FROM `sidebar_menus` sm
WHERE sm.`route_name` = 'partner.app_users.index' AND sm.`deleted_at` IS NULL
  AND NOT EXISTS (
      SELECT 1 FROM `sidebar_menu_accesses` sma 
      WHERE sma.`sidebar_menu_id` = sm.`id` AND sma.`access_type` = 2
  );
