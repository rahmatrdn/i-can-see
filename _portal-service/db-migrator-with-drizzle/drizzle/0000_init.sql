CREATE TABLE `cache_locks` (
	`key` varchar(255) NOT NULL,
	`owner` varchar(255) NOT NULL,
	`expiration` bigint NOT NULL,
	CONSTRAINT `cache_locks_key` PRIMARY KEY(`key`)
);
--> statement-breakpoint
CREATE TABLE `cache` (
	`key` varchar(255) NOT NULL,
	`value` mediumtext NOT NULL,
	`expiration` bigint NOT NULL,
	CONSTRAINT `cache_key` PRIMARY KEY(`key`)
);
--> statement-breakpoint
CREATE TABLE `failed_jobs` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`uuid` varchar(255) NOT NULL,
	`connection` varchar(255) NOT NULL,
	`queue` varchar(255) NOT NULL,
	`payload` longtext NOT NULL,
	`exception` longtext NOT NULL,
	`failed_at` timestamp NOT NULL DEFAULT (now()),
	CONSTRAINT `failed_jobs_id` PRIMARY KEY(`id`),
	CONSTRAINT `failed_jobs_uuid_unique` UNIQUE(`uuid`)
);
--> statement-breakpoint
CREATE TABLE `job_batches` (
	`id` varchar(255) NOT NULL,
	`name` varchar(255) NOT NULL,
	`total_jobs` int NOT NULL,
	`pending_jobs` int NOT NULL,
	`failed_jobs` int NOT NULL,
	`failed_job_ids` longtext NOT NULL,
	`options` mediumtext,
	`cancelled_at` int,
	`created_at` int NOT NULL,
	`finished_at` int,
	CONSTRAINT `job_batches_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
CREATE TABLE `jobs` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`queue` varchar(255) NOT NULL,
	`payload` longtext NOT NULL,
	`attempts` smallint unsigned NOT NULL,
	`reserved_at` int unsigned,
	`available_at` int unsigned NOT NULL,
	`created_at` int unsigned NOT NULL,
	CONSTRAINT `jobs_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
CREATE TABLE `migrations` (
	`id` int unsigned AUTO_INCREMENT NOT NULL,
	`migration` varchar(255) NOT NULL,
	`batch` int NOT NULL,
	CONSTRAINT `migrations_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
CREATE TABLE `password_reset_tokens` (
	`email` varchar(255) NOT NULL,
	`token` varchar(255) NOT NULL,
	`created_at` timestamp,
	CONSTRAINT `password_reset_tokens_email` PRIMARY KEY(`email`)
);
--> statement-breakpoint
CREATE TABLE `sessions` (
	`id` varchar(255) NOT NULL,
	`user_id` bigint unsigned,
	`ip_address` varchar(45),
	`user_agent` text,
	`payload` longtext NOT NULL,
	`last_activity` int NOT NULL,
	CONSTRAINT `sessions_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
CREATE TABLE `sidebar_menu_accesses` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`sidebar_menu_id` bigint unsigned NOT NULL,
	`access_type` tinyint NOT NULL COMMENT '1=Superadmin,2=User',
	`created_by` bigint unsigned,
	`created_at` timestamp,
	CONSTRAINT `sidebar_menu_accesses_id` PRIMARY KEY(`id`),
	CONSTRAINT `sidebar_menu_accesses_sidebar_menu_id_access_type_unique` UNIQUE(`sidebar_menu_id`,`access_type`)
);
--> statement-breakpoint
CREATE TABLE `sidebar_menu_groups` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`key` varchar(50) NOT NULL,
	`label` varchar(100) NOT NULL,
	`color` varchar(50) NOT NULL DEFAULT 'blue',
	`sort_order` smallint NOT NULL DEFAULT 0,
	`created_at` timestamp,
	`updated_at` timestamp,
	CONSTRAINT `sidebar_menu_groups_id` PRIMARY KEY(`id`),
	CONSTRAINT `sidebar_menu_groups_key_unique` UNIQUE(`key`)
);
--> statement-breakpoint
CREATE TABLE `sidebar_menus` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`parent_id` bigint unsigned COMMENT 'Self-referencing, max 1 level deep',
	`label` varchar(255) NOT NULL,
	`route_name` varchar(255) COMMENT 'Laravel named route, e.g. admin.dashboard',
	`icon` varchar(255) COMMENT 'Icon blade include path',
	`group` varchar(50) NOT NULL COMMENT 'utama|lembaga|keuangan|bendahara',
	`sort_order` smallint NOT NULL DEFAULT 0,
	`is_active` tinyint NOT NULL DEFAULT 1,
	`created_by` bigint unsigned,
	`updated_by` bigint unsigned,
	`deleted_by` bigint unsigned,
	`created_at` timestamp,
	`updated_at` timestamp,
	`deleted_at` timestamp,
	CONSTRAINT `sidebar_menus_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
CREATE TABLE `users` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`name` varchar(255) NOT NULL,
	`email` varchar(255) NOT NULL,
	`email_verified_at` timestamp,
	`password` varchar(255) NOT NULL,
	`remember_token` varchar(100),
	`access_type` tinyint COMMENT '1: Super Admin // 2: User',
	`is_active` tinyint NOT NULL DEFAULT 1,
	`created_by` bigint unsigned,
	`created_at` timestamp,
	`updated_by` bigint unsigned,
	`updated_at` timestamp,
	`deleted_by` bigint unsigned,
	`deleted_at` timestamp,
	CONSTRAINT `users_id` PRIMARY KEY(`id`),
	CONSTRAINT `users_email_unique` UNIQUE(`email`)
);
--> statement-breakpoint
CREATE TABLE `registration_partners` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`user_id` bigint unsigned COMMENT 'Associated user login account',
	`name` varchar(255) NOT NULL COMMENT 'Partner, foundation, or organization name',
	`type` varchar(50) NOT NULL DEFAULT 'foundation' COMMENT 'Type: foundation, community, school, institution, individual, other',
	`pic_name` varchar(255) NOT NULL COMMENT 'Person in charge (PIC) full name',
	`pic_phone` varchar(50) NOT NULL COMMENT 'Primary phone or WhatsApp number of PIC',
	`pic_email` varchar(255) COMMENT 'Primary email of PIC or organization',
	`address` text COMMENT 'Full office or organization address',
	`city` varchar(100) COMMENT 'City or regency',
	`province` varchar(100) COMMENT 'Province or state',
	`postal_code` varchar(20) COMMENT 'Postal code',
	`verification_status` tinyint NOT NULL DEFAULT 1 COMMENT '0: Pending, 1: Verified, 2: Rejected',
	`notes` text COMMENT 'Additional notes or partner description',
	`is_active` tinyint NOT NULL DEFAULT 1 COMMENT '1: Active, 0: Inactive',
	`created_by` bigint unsigned,
	`created_at` timestamp,
	`updated_by` bigint unsigned,
	`updated_at` timestamp,
	`deleted_by` bigint unsigned,
	`deleted_at` timestamp,
	CONSTRAINT `registration_partners_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
CREATE TABLE `app_users` (
	`id` bigint unsigned AUTO_INCREMENT NOT NULL,
	`registration_partner_id` bigint unsigned COMMENT 'Associated registration partner or foundation',
	`name` varchar(255) NOT NULL COMMENT 'Full name of the visually impaired user',
	`nickname` varchar(100) COMMENT 'Preferred nickname / calling name',
	`phone_number` varchar(50) COMMENT 'Primary phone number for app access or emergency',
	`email` varchar(255) COMMENT 'Optional email address',
	`gender` tinyint COMMENT '1: Male, 2: Female',
	`date_of_birth` date COMMENT 'Date of birth',
	`national_id_number` varchar(50) COMMENT 'National Identity Number (NIK / KTP)',
	`visual_impairment_level` tinyint NOT NULL DEFAULT 1 COMMENT '1: Totally Blind, 2: Low Vision',
	`visual_impairment_cause` varchar(100) COMMENT 'Cause of impairment (e.g. Congenital, Illness, Accident)',
	`address` text COMMENT 'Home street address',
	`city` varchar(100) COMMENT 'City or regency',
	`province` varchar(100) COMMENT 'Province or state',
	`guardian_name` varchar(255) COMMENT 'Name of guardian or primary contact person',
	`guardian_phone` varchar(50) COMMENT 'Phone number of guardian or emergency contact',
	`guardian_relation` varchar(100) COMMENT 'Relationship with guardian (e.g. Parent, Sibling, Spouse)',
	`device_id` varchar(255) COMMENT 'Unique device identifier',
	`fcm_token` text COMMENT 'Firebase Cloud Messaging token for push notifications',
	`app_version` varchar(50) COMMENT 'Currently installed application version',
	`last_active_at` timestamp COMMENT 'Timestamp of user''s last app activity',
	`is_active` tinyint NOT NULL DEFAULT 1 COMMENT '1: Active, 0: Inactive',
	`notes` text COMMENT 'Special assistance notes or observations',
	`created_by` bigint unsigned,
	`created_at` timestamp,
	`updated_by` bigint unsigned,
	`updated_at` timestamp,
	`deleted_by` bigint unsigned,
	`deleted_at` timestamp,
	CONSTRAINT `app_users_id` PRIMARY KEY(`id`)
);
--> statement-breakpoint
ALTER TABLE `users` ADD CONSTRAINT `users_created_by_users_id_fk` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE no action ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `users` ADD CONSTRAINT `users_updated_by_users_id_fk` FOREIGN KEY (`updated_by`) REFERENCES `users`(`id`) ON DELETE no action ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `users` ADD CONSTRAINT `users_deleted_by_users_id_fk` FOREIGN KEY (`deleted_by`) REFERENCES `users`(`id`) ON DELETE no action ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `registration_partners` ADD CONSTRAINT `registration_partners_user_id_users_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE no action ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `registration_partners` ADD CONSTRAINT `registration_partners_created_by_users_id_fk` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE no action ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `registration_partners` ADD CONSTRAINT `registration_partners_updated_by_users_id_fk` FOREIGN KEY (`updated_by`) REFERENCES `users`(`id`) ON DELETE no action ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `registration_partners` ADD CONSTRAINT `registration_partners_deleted_by_users_id_fk` FOREIGN KEY (`deleted_by`) REFERENCES `users`(`id`) ON DELETE no action ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `app_users` ADD CONSTRAINT `app_users_registration_partner_id_registration_partners_id_fk` FOREIGN KEY (`registration_partner_id`) REFERENCES `registration_partners`(`id`) ON DELETE no action ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `app_users` ADD CONSTRAINT `app_users_created_by_users_id_fk` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE no action ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `app_users` ADD CONSTRAINT `app_users_updated_by_users_id_fk` FOREIGN KEY (`updated_by`) REFERENCES `users`(`id`) ON DELETE no action ON UPDATE no action;--> statement-breakpoint
ALTER TABLE `app_users` ADD CONSTRAINT `app_users_deleted_by_users_id_fk` FOREIGN KEY (`deleted_by`) REFERENCES `users`(`id`) ON DELETE no action ON UPDATE no action;--> statement-breakpoint
CREATE INDEX `cache_locks_expiration_index` ON `cache_locks` (`expiration`);--> statement-breakpoint
CREATE INDEX `cache_expiration_index` ON `cache` (`expiration`);--> statement-breakpoint
CREATE INDEX `failed_jobs_connection_queue_failed_at_index` ON `failed_jobs` (`connection`,`queue`,`failed_at`);--> statement-breakpoint
CREATE INDEX `jobs_queue_index` ON `jobs` (`queue`);--> statement-breakpoint
CREATE INDEX `sessions_user_id_index` ON `sessions` (`user_id`);--> statement-breakpoint
CREATE INDEX `sessions_last_activity_index` ON `sessions` (`last_activity`);--> statement-breakpoint
CREATE INDEX `sidebar_menu_accesses_sidebar_menu_id_index` ON `sidebar_menu_accesses` (`sidebar_menu_id`);--> statement-breakpoint
CREATE INDEX `sidebar_menus_parent_id_index` ON `sidebar_menus` (`parent_id`);--> statement-breakpoint
CREATE INDEX `sidebar_menus_group_sort_order_index` ON `sidebar_menus` (`group`,`sort_order`);--> statement-breakpoint
CREATE INDEX `registration_partners_user_id_index` ON `registration_partners` (`user_id`);--> statement-breakpoint
CREATE INDEX `registration_partners_type_index` ON `registration_partners` (`type`);--> statement-breakpoint
CREATE INDEX `registration_partners_pic_phone_index` ON `registration_partners` (`pic_phone`);--> statement-breakpoint
CREATE INDEX `registration_partners_is_active_index` ON `registration_partners` (`is_active`);--> statement-breakpoint
CREATE INDEX `app_users_registration_partner_id_index` ON `app_users` (`registration_partner_id`);--> statement-breakpoint
CREATE INDEX `app_users_phone_number_index` ON `app_users` (`phone_number`);--> statement-breakpoint
CREATE INDEX `app_users_visual_impairment_level_index` ON `app_users` (`visual_impairment_level`);--> statement-breakpoint
CREATE INDEX `app_users_is_active_index` ON `app_users` (`is_active`);--> statement-breakpoint
CREATE INDEX `app_users_last_active_at_index` ON `app_users` (`last_active_at`);