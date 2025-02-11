-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         10.4.32-MariaDB - mariadb.org binary distribution
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.6.0.6765
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para base
CREATE DATABASE IF NOT EXISTS `base` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `base`;

-- Volcando estructura para tabla base.failed_jobs
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla base.failed_jobs: ~0 rows (aproximadamente)

-- Volcando estructura para tabla base.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla base.migrations: ~5 rows (aproximadamente)
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '2014_10_12_000000_create_users_table', 1),
	(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
	(3, '2019_08_19_000000_create_failed_jobs_table', 1),
	(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
	(5, '2023_06_07_000001_create_pulse_tables', 1),
	(6, '2024_07_09_222147_create_permission_tables', 1);

-- Volcando estructura para tabla base.model_has_permissions
CREATE TABLE IF NOT EXISTS `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla base.model_has_permissions: ~0 rows (aproximadamente)

-- Volcando estructura para tabla base.model_has_roles
CREATE TABLE IF NOT EXISTS `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla base.model_has_roles: ~0 rows (aproximadamente)

-- Volcando estructura para tabla base.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla base.password_reset_tokens: ~0 rows (aproximadamente)

-- Volcando estructura para tabla base.permissions
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla base.permissions: ~0 rows (aproximadamente)

-- Volcando estructura para tabla base.personal_access_tokens
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla base.personal_access_tokens: ~0 rows (aproximadamente)

-- Volcando estructura para tabla base.pulse_aggregates
CREATE TABLE IF NOT EXISTS `pulse_aggregates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `bucket` int(10) unsigned NOT NULL,
  `period` mediumint(8) unsigned NOT NULL,
  `type` varchar(255) NOT NULL,
  `key` mediumtext NOT NULL,
  `key_hash` binary(16) GENERATED ALWAYS AS (unhex(md5(`key`))) VIRTUAL,
  `aggregate` varchar(255) NOT NULL,
  `value` decimal(20,2) NOT NULL,
  `count` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pulse_aggregates_bucket_period_type_aggregate_key_hash_unique` (`bucket`,`period`,`type`,`aggregate`,`key_hash`),
  KEY `pulse_aggregates_period_bucket_index` (`period`,`bucket`),
  KEY `pulse_aggregates_type_index` (`type`),
  KEY `pulse_aggregates_period_type_aggregate_bucket_index` (`period`,`type`,`aggregate`,`bucket`)
) ENGINE=InnoDB AUTO_INCREMENT=404 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla base.pulse_aggregates: ~127 rows (aproximadamente)
INSERT INTO `pulse_aggregates` (`id`, `bucket`, `period`, `type`, `key`, `aggregate`, `value`, `count`) VALUES
	(1, 1729716000, 60, 'slow_query', '["alter table `model_has_permissions` add constraint `model_has_permissions_permission_id_foreign` foreign key (`permission_id`) references `permissions` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:54"]', 'count', 1.00, NULL),
	(2, 1729715760, 360, 'slow_query', '["alter table `model_has_permissions` add constraint `model_has_permissions_permission_id_foreign` foreign key (`permission_id`) references `permissions` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:54"]', 'count', 1.00, NULL),
	(3, 1729715040, 1440, 'slow_query', '["alter table `model_has_permissions` add constraint `model_has_permissions_permission_id_foreign` foreign key (`permission_id`) references `permissions` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:54"]', 'count', 1.00, NULL),
	(4, 1729707840, 10080, 'slow_query', '["alter table `model_has_permissions` add constraint `model_has_permissions_permission_id_foreign` foreign key (`permission_id`) references `permissions` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:54"]', 'count', 1.00, NULL),
	(5, 1729716000, 60, 'slow_query', '["alter table `model_has_roles` add constraint `model_has_roles_role_id_foreign` foreign key (`role_id`) references `roles` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:78"]', 'count', 1.00, NULL),
	(6, 1729715760, 360, 'slow_query', '["alter table `model_has_roles` add constraint `model_has_roles_role_id_foreign` foreign key (`role_id`) references `roles` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:78"]', 'count', 1.00, NULL),
	(7, 1729715040, 1440, 'slow_query', '["alter table `model_has_roles` add constraint `model_has_roles_role_id_foreign` foreign key (`role_id`) references `roles` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:78"]', 'count', 1.00, NULL),
	(8, 1729707840, 10080, 'slow_query', '["alter table `model_has_roles` add constraint `model_has_roles_role_id_foreign` foreign key (`role_id`) references `roles` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:78"]', 'count', 1.00, NULL),
	(9, 1729716000, 60, 'slow_query', '["alter table `role_has_permissions` add constraint `role_has_permissions_permission_id_foreign` foreign key (`permission_id`) references `permissions` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:101"]', 'count', 1.00, NULL),
	(10, 1729715760, 360, 'slow_query', '["alter table `role_has_permissions` add constraint `role_has_permissions_permission_id_foreign` foreign key (`permission_id`) references `permissions` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:101"]', 'count', 1.00, NULL),
	(11, 1729715040, 1440, 'slow_query', '["alter table `role_has_permissions` add constraint `role_has_permissions_permission_id_foreign` foreign key (`permission_id`) references `permissions` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:101"]', 'count', 1.00, NULL),
	(12, 1729707840, 10080, 'slow_query', '["alter table `role_has_permissions` add constraint `role_has_permissions_permission_id_foreign` foreign key (`permission_id`) references `permissions` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:101"]', 'count', 1.00, NULL),
	(13, 1729716000, 60, 'slow_query', '["alter table `role_has_permissions` add constraint `role_has_permissions_role_id_foreign` foreign key (`role_id`) references `roles` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:101"]', 'count', 1.00, NULL),
	(14, 1729715760, 360, 'slow_query', '["alter table `role_has_permissions` add constraint `role_has_permissions_role_id_foreign` foreign key (`role_id`) references `roles` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:101"]', 'count', 1.00, NULL),
	(15, 1729715040, 1440, 'slow_query', '["alter table `role_has_permissions` add constraint `role_has_permissions_role_id_foreign` foreign key (`role_id`) references `roles` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:101"]', 'count', 1.00, NULL),
	(16, 1729707840, 10080, 'slow_query', '["alter table `role_has_permissions` add constraint `role_has_permissions_role_id_foreign` foreign key (`role_id`) references `roles` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:101"]', 'count', 1.00, NULL),
	(17, 1729716000, 60, 'slow_query', '["alter table `model_has_permissions` add constraint `model_has_permissions_permission_id_foreign` foreign key (`permission_id`) references `permissions` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:54"]', 'max', 1065.00, NULL),
	(18, 1729715760, 360, 'slow_query', '["alter table `model_has_permissions` add constraint `model_has_permissions_permission_id_foreign` foreign key (`permission_id`) references `permissions` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:54"]', 'max', 1065.00, NULL),
	(19, 1729715040, 1440, 'slow_query', '["alter table `model_has_permissions` add constraint `model_has_permissions_permission_id_foreign` foreign key (`permission_id`) references `permissions` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:54"]', 'max', 1065.00, NULL),
	(20, 1729707840, 10080, 'slow_query', '["alter table `model_has_permissions` add constraint `model_has_permissions_permission_id_foreign` foreign key (`permission_id`) references `permissions` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:54"]', 'max', 1065.00, NULL),
	(21, 1729716000, 60, 'slow_query', '["alter table `model_has_roles` add constraint `model_has_roles_role_id_foreign` foreign key (`role_id`) references `roles` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:78"]', 'max', 1874.00, NULL),
	(22, 1729715760, 360, 'slow_query', '["alter table `model_has_roles` add constraint `model_has_roles_role_id_foreign` foreign key (`role_id`) references `roles` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:78"]', 'max', 1874.00, NULL),
	(23, 1729715040, 1440, 'slow_query', '["alter table `model_has_roles` add constraint `model_has_roles_role_id_foreign` foreign key (`role_id`) references `roles` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:78"]', 'max', 1874.00, NULL),
	(24, 1729707840, 10080, 'slow_query', '["alter table `model_has_roles` add constraint `model_has_roles_role_id_foreign` foreign key (`role_id`) references `roles` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:78"]', 'max', 1874.00, NULL),
	(25, 1729716000, 60, 'slow_query', '["alter table `role_has_permissions` add constraint `role_has_permissions_permission_id_foreign` foreign key (`permission_id`) references `permissions` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:101"]', 'max', 4408.00, NULL),
	(26, 1729715760, 360, 'slow_query', '["alter table `role_has_permissions` add constraint `role_has_permissions_permission_id_foreign` foreign key (`permission_id`) references `permissions` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:101"]', 'max', 4408.00, NULL),
	(27, 1729715040, 1440, 'slow_query', '["alter table `role_has_permissions` add constraint `role_has_permissions_permission_id_foreign` foreign key (`permission_id`) references `permissions` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:101"]', 'max', 4408.00, NULL),
	(28, 1729707840, 10080, 'slow_query', '["alter table `role_has_permissions` add constraint `role_has_permissions_permission_id_foreign` foreign key (`permission_id`) references `permissions` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:101"]', 'max', 4408.00, NULL),
	(29, 1729716000, 60, 'slow_query', '["alter table `role_has_permissions` add constraint `role_has_permissions_role_id_foreign` foreign key (`role_id`) references `roles` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:101"]', 'max', 1208.00, NULL),
	(30, 1729715760, 360, 'slow_query', '["alter table `role_has_permissions` add constraint `role_has_permissions_role_id_foreign` foreign key (`role_id`) references `roles` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:101"]', 'max', 1208.00, NULL),
	(31, 1729715040, 1440, 'slow_query', '["alter table `role_has_permissions` add constraint `role_has_permissions_role_id_foreign` foreign key (`role_id`) references `roles` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:101"]', 'max', 1208.00, NULL),
	(32, 1729707840, 10080, 'slow_query', '["alter table `role_has_permissions` add constraint `role_has_permissions_role_id_foreign` foreign key (`role_id`) references `roles` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:101"]', 'max', 1208.00, NULL),
	(33, 1729716180, 60, 'user_request', '1', 'count', 2.00, NULL),
	(34, 1729716120, 360, 'user_request', '1', 'count', 2.00, NULL),
	(35, 1729715040, 1440, 'user_request', '1', 'count', 2.00, NULL),
	(36, 1729707840, 10080, 'user_request', '1', 'count', 2.00, NULL),
	(41, 1729716180, 60, 'cache_miss', 'spatie.permission.cache', 'count', 1.00, NULL),
	(42, 1729716120, 360, 'cache_miss', 'spatie.permission.cache', 'count', 1.00, NULL),
	(43, 1729715040, 1440, 'cache_miss', 'spatie.permission.cache', 'count', 1.00, NULL),
	(44, 1729707840, 10080, 'cache_miss', 'spatie.permission.cache', 'count', 1.00, NULL),
	(45, 1729716180, 60, 'cache_hit', 'spatie.permission.cache', 'count', 10.00, NULL),
	(46, 1729716120, 360, 'cache_hit', 'spatie.permission.cache', 'count', 36.00, NULL),
	(47, 1729715040, 1440, 'cache_hit', 'spatie.permission.cache', 'count', 36.00, NULL),
	(48, 1729707840, 10080, 'cache_hit', 'spatie.permission.cache', 'count', 36.00, NULL),
	(85, 1729716240, 60, 'cache_hit', 'spatie.permission.cache', 'count', 16.00, NULL),
	(149, 1729716300, 60, 'cache_hit', 'spatie.permission.cache', 'count', 6.00, NULL),
	(173, 1729716360, 60, 'cache_hit', 'spatie.permission.cache', 'count', 3.00, NULL),
	(185, 1729716420, 60, 'cache_hit', 'spatie.permission.cache', 'count', 1.00, NULL),
	(187, 1729888080, 60, 'slow_request', '["GET","\\/","Closure"]', 'count', 1.00, NULL),
	(188, 1729887840, 360, 'slow_request', '["GET","\\/","Closure"]', 'count', 1.00, NULL),
	(189, 1729887840, 1440, 'slow_request', '["GET","\\/","Closure"]', 'count', 1.00, NULL),
	(190, 1729879200, 10080, 'slow_request', '["GET","\\/","Closure"]', 'count', 1.00, NULL),
	(191, 1729888080, 60, 'slow_query', '["select * from `users` where `id` = ? and `users`.`deleted_at` is null limit 1","storage\\\\framework\\\\views\\\\77a42f121767e508dd438b8f9259f42b.php:24"]', 'count', 1.00, NULL),
	(192, 1729887840, 360, 'slow_query', '["select * from `users` where `id` = ? and `users`.`deleted_at` is null limit 1","storage\\\\framework\\\\views\\\\77a42f121767e508dd438b8f9259f42b.php:24"]', 'count', 1.00, NULL),
	(193, 1729887840, 1440, 'slow_query', '["select * from `users` where `id` = ? and `users`.`deleted_at` is null limit 1","storage\\\\framework\\\\views\\\\77a42f121767e508dd438b8f9259f42b.php:24"]', 'count', 1.00, NULL),
	(194, 1729879200, 10080, 'slow_query', '["select * from `users` where `id` = ? and `users`.`deleted_at` is null limit 1","storage\\\\framework\\\\views\\\\77a42f121767e508dd438b8f9259f42b.php:24"]', 'count', 1.00, NULL),
	(195, 1729888080, 60, 'slow_request', '["GET","\\/","Closure"]', 'max', 12883.00, NULL),
	(196, 1729887840, 360, 'slow_request', '["GET","\\/","Closure"]', 'max', 12883.00, NULL),
	(197, 1729887840, 1440, 'slow_request', '["GET","\\/","Closure"]', 'max', 12883.00, NULL),
	(198, 1729879200, 10080, 'slow_request', '["GET","\\/","Closure"]', 'max', 12883.00, NULL),
	(199, 1729888080, 60, 'slow_query', '["select * from `users` where `id` = ? and `users`.`deleted_at` is null limit 1","storage\\\\framework\\\\views\\\\77a42f121767e508dd438b8f9259f42b.php:24"]', 'max', 1285.00, NULL),
	(200, 1729887840, 360, 'slow_query', '["select * from `users` where `id` = ? and `users`.`deleted_at` is null limit 1","storage\\\\framework\\\\views\\\\77a42f121767e508dd438b8f9259f42b.php:24"]', 'max', 1285.00, NULL),
	(201, 1729887840, 1440, 'slow_query', '["select * from `users` where `id` = ? and `users`.`deleted_at` is null limit 1","storage\\\\framework\\\\views\\\\77a42f121767e508dd438b8f9259f42b.php:24"]', 'max', 1285.00, NULL),
	(202, 1729879200, 10080, 'slow_query', '["select * from `users` where `id` = ? and `users`.`deleted_at` is null limit 1","storage\\\\framework\\\\views\\\\77a42f121767e508dd438b8f9259f42b.php:24"]', 'max', 1285.00, NULL),
	(203, 1729888320, 60, 'user_request', '1', 'count', 5.00, NULL),
	(204, 1729888200, 360, 'user_request', '1', 'count', 5.00, NULL),
	(205, 1729887840, 1440, 'user_request', '1', 'count', 5.00, NULL),
	(206, 1729879200, 10080, 'user_request', '1', 'count', 5.00, NULL),
	(223, 1729888320, 60, 'cache_miss', 'spatie.permission.cache', 'count', 1.00, NULL),
	(224, 1729888200, 360, 'cache_miss', 'spatie.permission.cache', 'count', 1.00, NULL),
	(225, 1729887840, 1440, 'cache_miss', 'spatie.permission.cache', 'count', 1.00, NULL),
	(226, 1729879200, 10080, 'cache_miss', 'spatie.permission.cache', 'count', 1.00, NULL),
	(227, 1729888320, 60, 'cache_hit', 'spatie.permission.cache', 'count', 9.00, NULL),
	(228, 1729888200, 360, 'cache_hit', 'spatie.permission.cache', 'count', 16.00, NULL),
	(229, 1729887840, 1440, 'cache_hit', 'spatie.permission.cache', 'count', 21.00, NULL),
	(230, 1729879200, 10080, 'cache_hit', 'spatie.permission.cache', 'count', 21.00, NULL),
	(263, 1729888380, 60, 'cache_hit', 'spatie.permission.cache', 'count', 6.00, NULL),
	(287, 1729888440, 60, 'cache_hit', 'spatie.permission.cache', 'count', 1.00, NULL),
	(291, 1729888560, 60, 'cache_hit', 'spatie.permission.cache', 'count', 1.00, NULL),
	(292, 1729888560, 360, 'cache_hit', 'spatie.permission.cache', 'count', 4.00, NULL),
	(295, 1729888680, 60, 'cache_hit', 'spatie.permission.cache', 'count', 1.00, NULL),
	(299, 1729888800, 60, 'cache_hit', 'spatie.permission.cache', 'count', 2.00, NULL),
	(307, 1729889100, 60, 'cache_hit', 'spatie.permission.cache', 'count', 1.00, NULL),
	(308, 1729888920, 360, 'cache_hit', 'spatie.permission.cache', 'count', 1.00, NULL),
	(311, 1729889340, 60, 'cache_hit', 'spatie.permission.cache', 'count', 2.00, NULL),
	(312, 1729889280, 360, 'cache_hit', 'spatie.permission.cache', 'count', 3.00, NULL),
	(313, 1729889280, 1440, 'cache_hit', 'spatie.permission.cache', 'count', 10.00, NULL),
	(314, 1729889280, 10080, 'cache_hit', 'spatie.permission.cache', 'count', 10.00, NULL),
	(319, 1729889460, 60, 'cache_hit', 'spatie.permission.cache', 'count', 1.00, NULL),
	(323, 1729889820, 60, 'cache_hit', 'spatie.permission.cache', 'count', 4.00, NULL),
	(324, 1729889640, 360, 'cache_hit', 'spatie.permission.cache', 'count', 7.00, NULL),
	(339, 1729889880, 60, 'cache_hit', 'spatie.permission.cache', 'count', 3.00, NULL),
	(348, 1730746800, 60, 'slow_request', '["GET","\\/","Closure"]', 'count', 1.00, NULL),
	(349, 1730746800, 360, 'slow_request', '["GET","\\/","Closure"]', 'count', 1.00, NULL),
	(350, 1730746080, 1440, 'slow_request', '["GET","\\/","Closure"]', 'count', 1.00, NULL),
	(351, 1730746080, 10080, 'slow_request', '["GET","\\/","Closure"]', 'count', 1.00, NULL),
	(352, 1730746800, 60, 'slow_request', '["GET","\\/","Closure"]', 'max', 2743.00, NULL),
	(353, 1730746800, 360, 'slow_request', '["GET","\\/","Closure"]', 'max', 2743.00, NULL),
	(354, 1730746080, 1440, 'slow_request', '["GET","\\/","Closure"]', 'max', 2743.00, NULL),
	(355, 1730746080, 10080, 'slow_request', '["GET","\\/","Closure"]', 'max', 2743.00, NULL),
	(356, 1730746800, 60, 'user_request', '1', 'count', 2.00, NULL),
	(357, 1730746800, 360, 'user_request', '1', 'count', 2.00, NULL),
	(358, 1730746080, 1440, 'user_request', '1', 'count', 6.00, NULL),
	(359, 1730746080, 10080, 'user_request', '1', 'count', 7.00, NULL),
	(364, 1730747340, 60, 'user_request', '1', 'count', 1.00, NULL),
	(365, 1730747160, 360, 'user_request', '1', 'count', 4.00, NULL),
	(368, 1730747400, 60, 'user_request', '1', 'count', 2.00, NULL),
	(372, 1730747400, 60, 'slow_request', '["GET","\\/iniciar","App\\\\Http\\\\Controllers\\\\DespachoController@getIniciar"]', 'count', 1.00, NULL),
	(373, 1730747160, 360, 'slow_request', '["GET","\\/iniciar","App\\\\Http\\\\Controllers\\\\DespachoController@getIniciar"]', 'count', 1.00, NULL),
	(374, 1730746080, 1440, 'slow_request', '["GET","\\/iniciar","App\\\\Http\\\\Controllers\\\\DespachoController@getIniciar"]', 'count', 1.00, NULL),
	(375, 1730746080, 10080, 'slow_request', '["GET","\\/iniciar","App\\\\Http\\\\Controllers\\\\DespachoController@getIniciar"]', 'count', 1.00, NULL),
	(376, 1730747400, 60, 'slow_user_request', '1', 'count', 1.00, NULL),
	(377, 1730747160, 360, 'slow_user_request', '1', 'count', 1.00, NULL),
	(378, 1730746080, 1440, 'slow_user_request', '1', 'count', 1.00, NULL),
	(379, 1730746080, 10080, 'slow_user_request', '1', 'count', 1.00, NULL),
	(380, 1730747400, 60, 'exception', '["InvalidArgumentException","app\\\\Http\\\\Controllers\\\\DespachoController.php:11"]', 'count', 1.00, NULL),
	(381, 1730747160, 360, 'exception', '["InvalidArgumentException","app\\\\Http\\\\Controllers\\\\DespachoController.php:11"]', 'count', 1.00, NULL),
	(382, 1730746080, 1440, 'exception', '["InvalidArgumentException","app\\\\Http\\\\Controllers\\\\DespachoController.php:11"]', 'count', 1.00, NULL),
	(383, 1730746080, 10080, 'exception', '["InvalidArgumentException","app\\\\Http\\\\Controllers\\\\DespachoController.php:11"]', 'count', 1.00, NULL),
	(388, 1730747400, 60, 'slow_request', '["GET","\\/iniciar","App\\\\Http\\\\Controllers\\\\DespachoController@getIniciar"]', 'max', 1733.00, NULL),
	(389, 1730747160, 360, 'slow_request', '["GET","\\/iniciar","App\\\\Http\\\\Controllers\\\\DespachoController@getIniciar"]', 'max', 1733.00, NULL),
	(390, 1730746080, 1440, 'slow_request', '["GET","\\/iniciar","App\\\\Http\\\\Controllers\\\\DespachoController@getIniciar"]', 'max', 1733.00, NULL),
	(391, 1730746080, 10080, 'slow_request', '["GET","\\/iniciar","App\\\\Http\\\\Controllers\\\\DespachoController@getIniciar"]', 'max', 1733.00, NULL),
	(392, 1730747400, 60, 'exception', '["InvalidArgumentException","app\\\\Http\\\\Controllers\\\\DespachoController.php:11"]', 'max', 1730747446.00, NULL),
	(393, 1730747160, 360, 'exception', '["InvalidArgumentException","app\\\\Http\\\\Controllers\\\\DespachoController.php:11"]', 'max', 1730747446.00, NULL),
	(394, 1730746080, 1440, 'exception', '["InvalidArgumentException","app\\\\Http\\\\Controllers\\\\DespachoController.php:11"]', 'max', 1730747446.00, NULL),
	(395, 1730746080, 10080, 'exception', '["InvalidArgumentException","app\\\\Http\\\\Controllers\\\\DespachoController.php:11"]', 'max', 1730747446.00, NULL),
	(396, 1730747460, 60, 'user_request', '1', 'count', 1.00, NULL),
	(400, 1730747940, 60, 'user_request', '1', 'count', 1.00, NULL),
	(401, 1730747880, 360, 'user_request', '1', 'count', 1.00, NULL),
	(402, 1730747520, 1440, 'user_request', '1', 'count', 1.00, NULL);

-- Volcando estructura para tabla base.pulse_entries
CREATE TABLE IF NOT EXISTS `pulse_entries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` int(10) unsigned NOT NULL,
  `type` varchar(255) NOT NULL,
  `key` mediumtext NOT NULL,
  `key_hash` binary(16) GENERATED ALWAYS AS (unhex(md5(`key`))) VIRTUAL,
  `value` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pulse_entries_timestamp_index` (`timestamp`),
  KEY `pulse_entries_type_index` (`type`),
  KEY `pulse_entries_key_hash_index` (`key_hash`),
  KEY `pulse_entries_timestamp_type_key_hash_value_index` (`timestamp`,`type`,`key_hash`,`value`)
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla base.pulse_entries: ~93 rows (aproximadamente)
INSERT INTO `pulse_entries` (`id`, `timestamp`, `type`, `key`, `value`) VALUES
	(1, 1729716018, 'slow_query', '["alter table `model_has_permissions` add constraint `model_has_permissions_permission_id_foreign` foreign key (`permission_id`) references `permissions` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:54"]', 1065),
	(2, 1729716020, 'slow_query', '["alter table `model_has_roles` add constraint `model_has_roles_role_id_foreign` foreign key (`role_id`) references `roles` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:78"]', 1874),
	(3, 1729716023, 'slow_query', '["alter table `role_has_permissions` add constraint `role_has_permissions_permission_id_foreign` foreign key (`permission_id`) references `permissions` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:101"]', 4408),
	(4, 1729716027, 'slow_query', '["alter table `role_has_permissions` add constraint `role_has_permissions_role_id_foreign` foreign key (`role_id`) references `roles` (`id`) on delete cascade","database\\\\migrations\\\\2024_07_09_222147_create_permission_tables.php:101"]', 1208),
	(5, 1729716213, 'user_request', '1', NULL),
	(6, 1729716214, 'user_request', '1', NULL),
	(7, 1729716219, 'cache_miss', 'spatie.permission.cache', NULL),
	(8, 1729716220, 'cache_hit', 'spatie.permission.cache', NULL),
	(9, 1729716220, 'cache_hit', 'spatie.permission.cache', NULL),
	(10, 1729716221, 'cache_hit', 'spatie.permission.cache', NULL),
	(11, 1729716221, 'cache_hit', 'spatie.permission.cache', NULL),
	(12, 1729716221, 'cache_hit', 'spatie.permission.cache', NULL),
	(13, 1729716222, 'cache_hit', 'spatie.permission.cache', NULL),
	(14, 1729716222, 'cache_hit', 'spatie.permission.cache', NULL),
	(15, 1729716225, 'cache_hit', 'spatie.permission.cache', NULL),
	(16, 1729716230, 'cache_hit', 'spatie.permission.cache', NULL),
	(17, 1729716235, 'cache_hit', 'spatie.permission.cache', NULL),
	(18, 1729716247, 'cache_hit', 'spatie.permission.cache', NULL),
	(19, 1729716252, 'cache_hit', 'spatie.permission.cache', NULL),
	(20, 1729716272, 'cache_hit', 'spatie.permission.cache', NULL),
	(21, 1729716283, 'cache_hit', 'spatie.permission.cache', NULL),
	(22, 1729716284, 'cache_hit', 'spatie.permission.cache', NULL),
	(23, 1729716285, 'cache_hit', 'spatie.permission.cache', NULL),
	(24, 1729716286, 'cache_hit', 'spatie.permission.cache', NULL),
	(25, 1729716286, 'cache_hit', 'spatie.permission.cache', NULL),
	(26, 1729716287, 'cache_hit', 'spatie.permission.cache', NULL),
	(27, 1729716287, 'cache_hit', 'spatie.permission.cache', NULL),
	(28, 1729716288, 'cache_hit', 'spatie.permission.cache', NULL),
	(29, 1729716288, 'cache_hit', 'spatie.permission.cache', NULL),
	(30, 1729716289, 'cache_hit', 'spatie.permission.cache', NULL),
	(31, 1729716289, 'cache_hit', 'spatie.permission.cache', NULL),
	(32, 1729716290, 'cache_hit', 'spatie.permission.cache', NULL),
	(33, 1729716297, 'cache_hit', 'spatie.permission.cache', NULL),
	(34, 1729716300, 'cache_hit', 'spatie.permission.cache', NULL),
	(35, 1729716307, 'cache_hit', 'spatie.permission.cache', NULL),
	(36, 1729716327, 'cache_hit', 'spatie.permission.cache', NULL),
	(37, 1729716332, 'cache_hit', 'spatie.permission.cache', NULL),
	(38, 1729716342, 'cache_hit', 'spatie.permission.cache', NULL),
	(39, 1729716352, 'cache_hit', 'spatie.permission.cache', NULL),
	(40, 1729716362, 'cache_hit', 'spatie.permission.cache', NULL),
	(41, 1729716367, 'cache_hit', 'spatie.permission.cache', NULL),
	(42, 1729716382, 'cache_hit', 'spatie.permission.cache', NULL),
	(43, 1729716476, 'cache_hit', 'spatie.permission.cache', NULL),
	(44, 1729888101, 'slow_request', '["GET","\\/","Closure"]', 12883),
	(45, 1729888112, 'slow_query', '["select * from `users` where `id` = ? and `users`.`deleted_at` is null limit 1","storage\\\\framework\\\\views\\\\77a42f121767e508dd438b8f9259f42b.php:24"]', 1285),
	(46, 1729888343, 'user_request', '1', NULL),
	(47, 1729888343, 'user_request', '1', NULL),
	(48, 1729888347, 'user_request', '1', NULL),
	(49, 1729888357, 'user_request', '1', NULL),
	(50, 1729888359, 'user_request', '1', NULL),
	(51, 1729888366, 'cache_miss', 'spatie.permission.cache', NULL),
	(52, 1729888367, 'cache_hit', 'spatie.permission.cache', NULL),
	(53, 1729888368, 'cache_hit', 'spatie.permission.cache', NULL),
	(54, 1729888368, 'cache_hit', 'spatie.permission.cache', NULL),
	(55, 1729888369, 'cache_hit', 'spatie.permission.cache', NULL),
	(56, 1729888369, 'cache_hit', 'spatie.permission.cache', NULL),
	(57, 1729888370, 'cache_hit', 'spatie.permission.cache', NULL),
	(58, 1729888371, 'cache_hit', 'spatie.permission.cache', NULL),
	(59, 1729888373, 'cache_hit', 'spatie.permission.cache', NULL),
	(60, 1729888378, 'cache_hit', 'spatie.permission.cache', NULL),
	(61, 1729888388, 'cache_hit', 'spatie.permission.cache', NULL),
	(62, 1729888393, 'cache_hit', 'spatie.permission.cache', NULL),
	(63, 1729888396, 'cache_hit', 'spatie.permission.cache', NULL),
	(64, 1729888397, 'cache_hit', 'spatie.permission.cache', NULL),
	(65, 1729888398, 'cache_hit', 'spatie.permission.cache', NULL),
	(66, 1729888439, 'cache_hit', 'spatie.permission.cache', NULL),
	(67, 1729888454, 'cache_hit', 'spatie.permission.cache', NULL),
	(68, 1729888611, 'cache_hit', 'spatie.permission.cache', NULL),
	(69, 1729888731, 'cache_hit', 'spatie.permission.cache', NULL),
	(70, 1729888851, 'cache_hit', 'spatie.permission.cache', NULL),
	(71, 1729888852, 'cache_hit', 'spatie.permission.cache', NULL),
	(72, 1729889151, 'cache_hit', 'spatie.permission.cache', NULL),
	(73, 1729889391, 'cache_hit', 'spatie.permission.cache', NULL),
	(74, 1729889392, 'cache_hit', 'spatie.permission.cache', NULL),
	(75, 1729889511, 'cache_hit', 'spatie.permission.cache', NULL),
	(76, 1729889866, 'cache_hit', 'spatie.permission.cache', NULL),
	(77, 1729889868, 'cache_hit', 'spatie.permission.cache', NULL),
	(78, 1729889873, 'cache_hit', 'spatie.permission.cache', NULL),
	(79, 1729889878, 'cache_hit', 'spatie.permission.cache', NULL),
	(80, 1729889883, 'cache_hit', 'spatie.permission.cache', NULL),
	(81, 1729889909, 'cache_hit', 'spatie.permission.cache', NULL),
	(82, 1729889914, 'cache_hit', 'spatie.permission.cache', NULL),
	(83, 1730746823, 'slow_request', '["GET","\\/","Closure"]', 2743),
	(84, 1730746835, 'user_request', '1', NULL),
	(85, 1730746836, 'user_request', '1', NULL),
	(86, 1730747367, 'user_request', '1', NULL),
	(87, 1730747442, 'user_request', '1', NULL),
	(88, 1730747446, 'slow_request', '["GET","\\/iniciar","App\\\\Http\\\\Controllers\\\\DespachoController@getIniciar"]', 1733),
	(89, 1730747446, 'slow_user_request', '1', NULL),
	(90, 1730747446, 'user_request', '1', NULL),
	(91, 1730747446, 'exception', '["InvalidArgumentException","app\\\\Http\\\\Controllers\\\\DespachoController.php:11"]', 1730747446),
	(92, 1730747478, 'user_request', '1', NULL),
	(93, 1730747977, 'user_request', '1', NULL);

-- Volcando estructura para tabla base.pulse_values
CREATE TABLE IF NOT EXISTS `pulse_values` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` int(10) unsigned NOT NULL,
  `type` varchar(255) NOT NULL,
  `key` mediumtext NOT NULL,
  `key_hash` binary(16) GENERATED ALWAYS AS (unhex(md5(`key`))) VIRTUAL,
  `value` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pulse_values_type_key_hash_unique` (`type`,`key_hash`),
  KEY `pulse_values_timestamp_index` (`timestamp`),
  KEY `pulse_values_type_index` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla base.pulse_values: ~0 rows (aproximadamente)

-- Volcando estructura para tabla base.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla base.roles: ~0 rows (aproximadamente)

-- Volcando estructura para tabla base.role_has_permissions
CREATE TABLE IF NOT EXISTS `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla base.role_has_permissions: ~0 rows (aproximadamente)

-- Volcando estructura para tabla base.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla base.users: ~1 rows (aproximadamente)
INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 'Marco Antonio Espinoza Rojas', 'marco.espinoza@correos.gob.bo', NULL, '$2y$10$VOn55.vOVzJM1CaXAJu9WeI8IpfNSO1B0ngvH30tRxha1JdQVL9KG', NULL, '2024-10-24 00:43:33', '2024-10-24 00:43:33', NULL);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
