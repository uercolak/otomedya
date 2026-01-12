-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 11 Oca 2026, 20:57:31
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `otomedya`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `contents`
--

CREATE TABLE `contents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `base_text` text DEFAULT NULL,
  `media_type` varchar(20) DEFAULT NULL,
  `media_path` varchar(255) DEFAULT NULL,
  `media_id` bigint(20) UNSIGNED DEFAULT NULL,
  `template_id` int(11) UNSIGNED DEFAULT NULL,
  `meta_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta_json`)),
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `contents`
--

INSERT INTO `contents` (`id`, `user_id`, `title`, `base_text`, `media_type`, `media_path`, `media_id`, `template_id`, `meta_json`, `created_at`, `updated_at`) VALUES
(12, 3, 'testt', 'testt', NULL, NULL, NULL, NULL, NULL, '2026-01-10 14:04:03', '2026-01-10 14:04:03'),
(13, 3, 'testtt', 'testtt', NULL, NULL, NULL, NULL, NULL, '2026-01-10 14:16:51', '2026-01-10 14:16:51'),
(14, 3, 'test', 'test face + instagram', 'image', 'uploads/2026/01/1768044973_6718dd2396db9eb523c0.png', NULL, NULL, NULL, '2026-01-10 14:36:13', '2026-01-10 14:36:13');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `content_variants`
--

CREATE TABLE `content_variants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `content_id` bigint(20) UNSIGNED NOT NULL,
  `platform` varchar(50) NOT NULL,
  `text` text DEFAULT NULL,
  `thumbnail_media_id` bigint(20) UNSIGNED DEFAULT NULL,
  `extra_meta` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(60) NOT NULL,
  `payload_json` longtext NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `priority` int(11) NOT NULL DEFAULT 100,
  `run_at` datetime NOT NULL,
  `locked_at` datetime DEFAULT NULL,
  `locked_by` varchar(80) DEFAULT NULL,
  `attempts` int(11) NOT NULL DEFAULT 0,
  `max_attempts` int(11) NOT NULL DEFAULT 3,
  `last_error` longtext DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `jobs`
--

INSERT INTO `jobs` (`id`, `type`, `payload_json`, `status`, `priority`, `run_at`, `locked_at`, `locked_by`, `attempts`, `max_attempts`, `last_error`, `created_at`, `updated_at`) VALUES
(13, 'publish_post', '{\"publish_id\":8,\"platform\":\"facebook\",\"account_id\":26,\"content_id\":12,\"post_type\":\"post\"}', 'failed', 100, '2026-01-10 14:06:42', NULL, NULL, 3, 3, 'Meta error (403) (#200) If posting to a group, requires app being installed in the group, and \\\n          either publish_to_groups permission with user token, or both pages_read_engagement \\\n          and pages_manage_posts permission with page token; If posting to a page, \\\n          requires both pages_read_engagement and pages_manage_posts as an admin with \\\n          sufficient administrative permission | code=200 subcode= trace=AV7J7IqCi1MNlpkJTfglUGp', '2026-01-10 14:04:03', '2026-01-10 14:06:43'),
(14, 'publish_post', '{\"publish_id\":9,\"platform\":\"facebook\",\"account_id\":28,\"content_id\":13,\"post_type\":\"post\"}', 'done', 100, '2026-01-10 14:18:00', NULL, NULL, 0, 3, NULL, '2026-01-10 14:16:51', '2026-01-10 14:18:03'),
(15, 'publish_post', '{\"publish_id\":10,\"platform\":\"instagram\",\"account_id\":27,\"content_id\":14,\"post_type\":\"post\"}', 'done', 100, '2026-01-10 14:38:00', NULL, NULL, 0, 3, NULL, '2026-01-10 14:36:13', '2026-01-10 14:38:10'),
(16, 'publish_post', '{\"publish_id\":11,\"platform\":\"facebook\",\"account_id\":28,\"content_id\":14,\"post_type\":\"post\"}', 'done', 100, '2026-01-10 14:38:00', NULL, NULL, 0, 3, NULL, '2026-01-10 14:36:13', '2026-01-10 14:38:18');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `job_attempts`
--

CREATE TABLE `job_attempts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `job_id` bigint(20) UNSIGNED NOT NULL,
  `attempt_no` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `started_at` datetime NOT NULL,
  `finished_at` datetime DEFAULT NULL,
  `error` longtext DEFAULT NULL,
  `response_json` longtext DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `job_attempts`
--

INSERT INTO `job_attempts` (`id`, `job_id`, `attempt_no`, `status`, `started_at`, `finished_at`, `error`, `response_json`, `created_at`) VALUES
(20, 13, 1, 'failed', '2026-01-10 14:06:00', '2026-01-10 14:06:01', 'Meta error (403) (#200) If posting to a group, requires app being installed in the group, and \\\n          either publish_to_groups permission with user token, or both pages_read_engagement \\\n          and pages_manage_posts permission with page token; If posting to a page, \\\n          requires both pages_read_engagement and pages_manage_posts as an admin with \\\n          sufficient administrative permission | code=200 subcode= trace=AL03FWajJTARY2iOaCMzE38\n#0 C:\\xampp\\htdocs\\otomedya\\app\\Services\\MetaPublishService.php(195): App\\Services\\MetaPublishService->post(\'/93005582685605...\', Array)\n#1 C:\\xampp\\htdocs\\otomedya\\app\\Queue\\Handlers\\PublishPostHandler.php(245): App\\Services\\MetaPublishService->publishFacebookPage(Array)\n#2 C:\\xampp\\htdocs\\otomedya\\app\\Commands\\QueueWork.php(80): App\\Queue\\Handlers\\PublishPostHandler->handle(Array)\n#3 C:\\xampp\\htdocs\\otomedya\\vendor\\codeigniter4\\framework\\system\\CLI\\Commands.php(74): App\\Commands\\QueueWork->run(Array)\n#4 C:\\xampp\\htdocs\\otomedya\\vendor\\codeigniter4\\framework\\system\\CLI\\Console.php(47): CodeIgniter\\CLI\\Commands->run(\'queue:work\', Array)\n#5 C:\\xampp\\htdocs\\otomedya\\vendor\\codeigniter4\\framework\\system\\Boot.php(388): CodeIgniter\\CLI\\Console->run()\n#6 C:\\xampp\\htdocs\\otomedya\\vendor\\codeigniter4\\framework\\system\\Boot.php(133): CodeIgniter\\Boot::runCommand(Object(CodeIgniter\\CLI\\Console))\n#7 C:\\xampp\\htdocs\\otomedya\\spark(87): CodeIgniter\\Boot::bootSpark(Object(Config\\Paths))\n#8 {main}', NULL, '2026-01-10 14:06:00'),
(21, 13, 2, 'failed', '2026-01-10 14:06:11', '2026-01-10 14:06:12', 'Meta error (403) (#200) If posting to a group, requires app being installed in the group, and \\\n          either publish_to_groups permission with user token, or both pages_read_engagement \\\n          and pages_manage_posts permission with page token; If posting to a page, \\\n          requires both pages_read_engagement and pages_manage_posts as an admin with \\\n          sufficient administrative permission | code=200 subcode= trace=A6xMb0SuRAKbP_ZDJgDMjBy\n#0 C:\\xampp\\htdocs\\otomedya\\app\\Services\\MetaPublishService.php(195): App\\Services\\MetaPublishService->post(\'/93005582685605...\', Array)\n#1 C:\\xampp\\htdocs\\otomedya\\app\\Queue\\Handlers\\PublishPostHandler.php(245): App\\Services\\MetaPublishService->publishFacebookPage(Array)\n#2 C:\\xampp\\htdocs\\otomedya\\app\\Commands\\QueueWork.php(80): App\\Queue\\Handlers\\PublishPostHandler->handle(Array)\n#3 C:\\xampp\\htdocs\\otomedya\\vendor\\codeigniter4\\framework\\system\\CLI\\Commands.php(74): App\\Commands\\QueueWork->run(Array)\n#4 C:\\xampp\\htdocs\\otomedya\\vendor\\codeigniter4\\framework\\system\\CLI\\Console.php(47): CodeIgniter\\CLI\\Commands->run(\'queue:work\', Array)\n#5 C:\\xampp\\htdocs\\otomedya\\vendor\\codeigniter4\\framework\\system\\Boot.php(388): CodeIgniter\\CLI\\Console->run()\n#6 C:\\xampp\\htdocs\\otomedya\\vendor\\codeigniter4\\framework\\system\\Boot.php(133): CodeIgniter\\Boot::runCommand(Object(CodeIgniter\\CLI\\Console))\n#7 C:\\xampp\\htdocs\\otomedya\\spark(87): CodeIgniter\\Boot::bootSpark(Object(Config\\Paths))\n#8 {main}', NULL, '2026-01-10 14:06:11'),
(22, 13, 3, 'failed', '2026-01-10 14:06:42', '2026-01-10 14:06:43', 'Meta error (403) (#200) If posting to a group, requires app being installed in the group, and \\\n          either publish_to_groups permission with user token, or both pages_read_engagement \\\n          and pages_manage_posts permission with page token; If posting to a page, \\\n          requires both pages_read_engagement and pages_manage_posts as an admin with \\\n          sufficient administrative permission | code=200 subcode= trace=AV7J7IqCi1MNlpkJTfglUGp\n#0 C:\\xampp\\htdocs\\otomedya\\app\\Services\\MetaPublishService.php(195): App\\Services\\MetaPublishService->post(\'/93005582685605...\', Array)\n#1 C:\\xampp\\htdocs\\otomedya\\app\\Queue\\Handlers\\PublishPostHandler.php(245): App\\Services\\MetaPublishService->publishFacebookPage(Array)\n#2 C:\\xampp\\htdocs\\otomedya\\app\\Commands\\QueueWork.php(80): App\\Queue\\Handlers\\PublishPostHandler->handle(Array)\n#3 C:\\xampp\\htdocs\\otomedya\\vendor\\codeigniter4\\framework\\system\\CLI\\Commands.php(74): App\\Commands\\QueueWork->run(Array)\n#4 C:\\xampp\\htdocs\\otomedya\\vendor\\codeigniter4\\framework\\system\\CLI\\Console.php(47): CodeIgniter\\CLI\\Commands->run(\'queue:work\', Array)\n#5 C:\\xampp\\htdocs\\otomedya\\vendor\\codeigniter4\\framework\\system\\Boot.php(388): CodeIgniter\\CLI\\Console->run()\n#6 C:\\xampp\\htdocs\\otomedya\\vendor\\codeigniter4\\framework\\system\\Boot.php(133): CodeIgniter\\Boot::runCommand(Object(CodeIgniter\\CLI\\Console))\n#7 C:\\xampp\\htdocs\\otomedya\\spark(87): CodeIgniter\\Boot::bootSpark(Object(Config\\Paths))\n#8 {main}', NULL, '2026-01-10 14:06:42'),
(23, 14, 1, 'success', '2026-01-10 14:18:00', '2026-01-10 14:18:03', NULL, NULL, '2026-01-10 14:18:00'),
(24, 15, 1, 'success', '2026-01-10 14:38:01', '2026-01-10 14:38:10', NULL, NULL, '2026-01-10 14:38:01'),
(25, 16, 1, 'success', '2026-01-10 14:38:10', '2026-01-10 14:38:18', NULL, NULL, '2026-01-10 14:38:10');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `logs`
--

CREATE TABLE `logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `level` varchar(20) NOT NULL,
  `channel` varchar(50) NOT NULL,
  `message` varchar(255) NOT NULL,
  `context_json` longtext DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `logs`
--

INSERT INTO `logs` (`id`, `level`, `channel`, `message`, `context_json`, `user_id`, `ip`, `user_agent`, `created_at`) VALUES
(1, 'info', 'queue', 'job.reserved', '{\"job_id\":11,\"type\":\"publish_post\",\"worker\":\"User:10972\",\"pid\":10972,\"_event\":\"job.reserved\"}', NULL, NULL, NULL, '2026-01-10 13:28:00'),
(2, 'info', 'queue', 'publish.started', '{\"publish_id\":6,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"none\",\"_event\":\"publish.started\"}', 3, NULL, NULL, '2026-01-10 13:28:00'),
(3, 'error', 'queue', 'job.failed', '{\"job_id\":11,\"type\":\"publish_post\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-10 13:28:11\",\"hata\":\"Meta error (403) (#200) If posting to a group, requires app being installed in the group, and \\\\\\n          either publish_to_groups permission with user token, or both pages_read_engagement \\\\\\n          and pages_manage_posts permission with page token; If posting to a page, \\\\\\n          requires both pages_read_engagement and pages_manage_posts as an admin with \\\\\\n          sufficient administrative permission | code=200 subcode= trace=AGGrj3w4n554IPU0jvXRc9_\",\"worker\":\"User:10972\",\"pid\":10972,\"_event\":\"job.failed\"}', NULL, NULL, NULL, '2026-01-10 13:28:01'),
(4, 'info', 'queue', 'job.reserved', '{\"job_id\":11,\"type\":\"publish_post\",\"worker\":\"User:10972\",\"pid\":10972,\"_event\":\"job.reserved\"}', NULL, NULL, NULL, '2026-01-10 13:28:11'),
(5, 'info', 'queue', 'publish.started', '{\"publish_id\":6,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"none\",\"_event\":\"publish.started\"}', 3, NULL, NULL, '2026-01-10 13:28:11'),
(6, 'error', 'queue', 'job.failed', '{\"job_id\":11,\"type\":\"publish_post\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-10 13:28:42\",\"hata\":\"Meta error (403) (#200) If posting to a group, requires app being installed in the group, and \\\\\\n          either publish_to_groups permission with user token, or both pages_read_engagement \\\\\\n          and pages_manage_posts permission with page token; If posting to a page, \\\\\\n          requires both pages_read_engagement and pages_manage_posts as an admin with \\\\\\n          sufficient administrative permission | code=200 subcode= trace=AQPqDL_UZ5b1G1QFWYkE4nI\",\"worker\":\"User:10972\",\"pid\":10972,\"_event\":\"job.failed\"}', NULL, NULL, NULL, '2026-01-10 13:28:12'),
(7, 'info', 'queue', 'job.reserved', '{\"job_id\":11,\"type\":\"publish_post\",\"worker\":\"User:10972\",\"pid\":10972,\"_event\":\"job.reserved\"}', NULL, NULL, NULL, '2026-01-10 13:28:42'),
(8, 'info', 'queue', 'publish.started', '{\"publish_id\":6,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"none\",\"_event\":\"publish.started\"}', 3, NULL, NULL, '2026-01-10 13:28:42'),
(9, 'error', 'queue', 'job.failed', '{\"job_id\":11,\"type\":\"publish_post\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-10 13:28:42\",\"hata\":\"Meta error (403) (#200) If posting to a group, requires app being installed in the group, and \\\\\\n          either publish_to_groups permission with user token, or both pages_read_engagement \\\\\\n          and pages_manage_posts permission with page token; If posting to a page, \\\\\\n          requires both pages_read_engagement and pages_manage_posts as an admin with \\\\\\n          sufficient administrative permission | code=200 subcode= trace=AIPXeAqJJF67t19u2Levx4B\",\"worker\":\"User:10972\",\"pid\":10972,\"_event\":\"job.failed\"}', NULL, NULL, NULL, '2026-01-10 13:28:43'),
(10, 'info', 'queue', 'job.reserved', '{\"job_id\":12,\"type\":\"publish_post\",\"worker\":\"User:19228\",\"pid\":19228,\"_event\":\"job.reserved\"}', NULL, NULL, NULL, '2026-01-10 13:59:01'),
(11, 'info', 'queue', 'publish.started', '{\"publish_id\":7,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"none\",\"_event\":\"publish.started\"}', 3, NULL, NULL, '2026-01-10 13:59:01'),
(12, 'error', 'queue', 'job.failed', '{\"job_id\":12,\"type\":\"publish_post\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-10 13:59:12\",\"hata\":\"Meta error (403) (#200) If posting to a group, requires app being installed in the group, and \\\\\\n          either publish_to_groups permission with user token, or both pages_read_engagement \\\\\\n          and pages_manage_posts permission with page token; If posting to a page, \\\\\\n          requires both pages_read_engagement and pages_manage_posts as an admin with \\\\\\n          sufficient administrative permission | code=200 subcode= trace=AqpIse3VCmsPPC3Tg7ws6Dt\",\"worker\":\"User:19228\",\"pid\":19228,\"_event\":\"job.failed\"}', NULL, NULL, NULL, '2026-01-10 13:59:02'),
(13, 'info', 'queue', 'job.reserved', '{\"job_id\":12,\"type\":\"publish_post\",\"worker\":\"User:19228\",\"pid\":19228,\"_event\":\"job.reserved\"}', NULL, NULL, NULL, '2026-01-10 13:59:12'),
(14, 'info', 'queue', 'publish.started', '{\"publish_id\":7,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"none\",\"_event\":\"publish.started\"}', 3, NULL, NULL, '2026-01-10 13:59:12'),
(15, 'error', 'queue', 'job.failed', '{\"job_id\":12,\"type\":\"publish_post\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-10 13:59:43\",\"hata\":\"Meta error (403) (#200) If posting to a group, requires app being installed in the group, and \\\\\\n          either publish_to_groups permission with user token, or both pages_read_engagement \\\\\\n          and pages_manage_posts permission with page token; If posting to a page, \\\\\\n          requires both pages_read_engagement and pages_manage_posts as an admin with \\\\\\n          sufficient administrative permission | code=200 subcode= trace=A6rcCuzgjAgX47JCzu9hzhc\",\"worker\":\"User:19228\",\"pid\":19228,\"_event\":\"job.failed\"}', NULL, NULL, NULL, '2026-01-10 13:59:13'),
(16, 'info', 'queue', 'job.reserved', '{\"job_id\":12,\"type\":\"publish_post\",\"worker\":\"User:19228\",\"pid\":19228,\"_event\":\"job.reserved\"}', NULL, NULL, NULL, '2026-01-10 13:59:43'),
(17, 'info', 'queue', 'publish.started', '{\"publish_id\":7,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"none\",\"_event\":\"publish.started\"}', 3, NULL, NULL, '2026-01-10 13:59:43'),
(18, 'error', 'queue', 'job.failed', '{\"job_id\":12,\"type\":\"publish_post\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-10 13:59:43\",\"hata\":\"Meta error (403) (#200) If posting to a group, requires app being installed in the group, and \\\\\\n          either publish_to_groups permission with user token, or both pages_read_engagement \\\\\\n          and pages_manage_posts permission with page token; If posting to a page, \\\\\\n          requires both pages_read_engagement and pages_manage_posts as an admin with \\\\\\n          sufficient administrative permission | code=200 subcode= trace=Ae2otrJ-AwxH99OSXq4sdk6\",\"worker\":\"User:19228\",\"pid\":19228,\"_event\":\"job.failed\"}', NULL, NULL, NULL, '2026-01-10 13:59:44'),
(19, 'info', 'queue', 'job.reserved', '{\"job_id\":13,\"type\":\"publish_post\",\"worker\":\"User:16008\",\"pid\":16008,\"_event\":\"job.reserved\"}', NULL, NULL, NULL, '2026-01-10 14:06:00'),
(20, 'info', 'queue', 'publish.started', '{\"publish_id\":8,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"none\",\"_event\":\"publish.started\"}', 3, NULL, NULL, '2026-01-10 14:06:00'),
(21, 'error', 'queue', 'job.failed', '{\"job_id\":13,\"type\":\"publish_post\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-10 14:06:11\",\"hata\":\"Meta error (403) (#200) If posting to a group, requires app being installed in the group, and \\\\\\n          either publish_to_groups permission with user token, or both pages_read_engagement \\\\\\n          and pages_manage_posts permission with page token; If posting to a page, \\\\\\n          requires both pages_read_engagement and pages_manage_posts as an admin with \\\\\\n          sufficient administrative permission | code=200 subcode= trace=AL03FWajJTARY2iOaCMzE38\",\"worker\":\"User:16008\",\"pid\":16008,\"_event\":\"job.failed\"}', NULL, NULL, NULL, '2026-01-10 14:06:01'),
(22, 'info', 'queue', 'job.reserved', '{\"job_id\":13,\"type\":\"publish_post\",\"worker\":\"User:16008\",\"pid\":16008,\"_event\":\"job.reserved\"}', NULL, NULL, NULL, '2026-01-10 14:06:11'),
(23, 'info', 'queue', 'publish.started', '{\"publish_id\":8,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"none\",\"_event\":\"publish.started\"}', 3, NULL, NULL, '2026-01-10 14:06:11'),
(24, 'error', 'queue', 'job.failed', '{\"job_id\":13,\"type\":\"publish_post\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-10 14:06:42\",\"hata\":\"Meta error (403) (#200) If posting to a group, requires app being installed in the group, and \\\\\\n          either publish_to_groups permission with user token, or both pages_read_engagement \\\\\\n          and pages_manage_posts permission with page token; If posting to a page, \\\\\\n          requires both pages_read_engagement and pages_manage_posts as an admin with \\\\\\n          sufficient administrative permission | code=200 subcode= trace=A6xMb0SuRAKbP_ZDJgDMjBy\",\"worker\":\"User:16008\",\"pid\":16008,\"_event\":\"job.failed\"}', NULL, NULL, NULL, '2026-01-10 14:06:12'),
(25, 'info', 'queue', 'job.reserved', '{\"job_id\":13,\"type\":\"publish_post\",\"worker\":\"User:16008\",\"pid\":16008,\"_event\":\"job.reserved\"}', NULL, NULL, NULL, '2026-01-10 14:06:42'),
(26, 'info', 'queue', 'publish.started', '{\"publish_id\":8,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"none\",\"_event\":\"publish.started\"}', 3, NULL, NULL, '2026-01-10 14:06:42'),
(27, 'error', 'queue', 'job.failed', '{\"job_id\":13,\"type\":\"publish_post\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-10 14:06:42\",\"hata\":\"Meta error (403) (#200) If posting to a group, requires app being installed in the group, and \\\\\\n          either publish_to_groups permission with user token, or both pages_read_engagement \\\\\\n          and pages_manage_posts permission with page token; If posting to a page, \\\\\\n          requires both pages_read_engagement and pages_manage_posts as an admin with \\\\\\n          sufficient administrative permission | code=200 subcode= trace=AV7J7IqCi1MNlpkJTfglUGp\",\"worker\":\"User:16008\",\"pid\":16008,\"_event\":\"job.failed\"}', NULL, NULL, NULL, '2026-01-10 14:06:43'),
(28, 'info', 'queue', 'job.reserved', '{\"job_id\":14,\"type\":\"publish_post\",\"worker\":\"User:20456\",\"pid\":20456,\"_event\":\"job.reserved\"}', NULL, NULL, NULL, '2026-01-10 14:18:00'),
(29, 'info', 'queue', 'publish.started', '{\"publish_id\":9,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"none\",\"_event\":\"publish.started\"}', 3, NULL, NULL, '2026-01-10 14:18:00'),
(30, 'info', 'queue', 'publish.succeeded', '{\"publish_id\":9,\"remote_id\":\"930055826856053_122106117435191800\",\"permalink\":\"https:\\/\\/www.facebook.com\\/122106117441191800\\/posts\\/122106117435191800\",\"_event\":\"publish.succeeded\"}', 3, NULL, NULL, '2026-01-10 14:18:03'),
(31, 'info', 'queue', 'job.succeeded', '{\"job_id\":14,\"type\":\"publish_post\",\"deneme\":1,\"worker\":\"User:20456\",\"pid\":20456,\"_event\":\"job.succeeded\"}', NULL, NULL, NULL, '2026-01-10 14:18:03'),
(32, 'info', 'queue', 'job.reserved', '{\"job_id\":15,\"type\":\"publish_post\",\"worker\":\"User:1148\",\"pid\":1148,\"_event\":\"job.reserved\"}', NULL, NULL, NULL, '2026-01-10 14:38:01'),
(33, 'info', 'queue', 'publish.started', '{\"publish_id\":10,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"image\",\"_event\":\"publish.started\"}', 3, NULL, NULL, '2026-01-10 14:38:01'),
(34, 'info', 'queue', 'publish.succeeded', '{\"publish_id\":10,\"remote_id\":\"18090751255951449\",\"creation_id\":\"17847831369653299\",\"permalink\":\"https:\\/\\/www.instagram.com\\/p\\/DTVClz4AHfV\\/\",\"_event\":\"publish.succeeded\"}', 3, NULL, NULL, '2026-01-10 14:38:10'),
(35, 'info', 'queue', 'job.succeeded', '{\"job_id\":15,\"type\":\"publish_post\",\"deneme\":1,\"worker\":\"User:1148\",\"pid\":1148,\"_event\":\"job.succeeded\"}', NULL, NULL, NULL, '2026-01-10 14:38:10'),
(36, 'info', 'queue', 'job.reserved', '{\"job_id\":16,\"type\":\"publish_post\",\"worker\":\"User:1148\",\"pid\":1148,\"_event\":\"job.reserved\"}', NULL, NULL, NULL, '2026-01-10 14:38:10'),
(37, 'info', 'queue', 'publish.started', '{\"publish_id\":11,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"image\",\"_event\":\"publish.started\"}', 3, NULL, NULL, '2026-01-10 14:38:10'),
(38, 'info', 'queue', 'publish.succeeded', '{\"publish_id\":11,\"remote_id\":\"930055826856053_122106121281191800\",\"permalink\":\"https:\\/\\/www.facebook.com\\/122106117441191800\\/posts\\/122106121281191800\",\"_event\":\"publish.succeeded\"}', 3, NULL, NULL, '2026-01-10 14:38:18'),
(39, 'info', 'queue', 'job.succeeded', '{\"job_id\":16,\"type\":\"publish_post\",\"deneme\":1,\"worker\":\"User:1148\",\"pid\":1148,\"_event\":\"job.succeeded\"}', NULL, NULL, NULL, '2026-01-10 14:38:18');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `media`
--

CREATE TABLE `media` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `type` varchar(20) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `width` int(11) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `meta_media_jobs`
--

CREATE TABLE `meta_media_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `publish_id` int(10) UNSIGNED DEFAULT NULL,
  `social_account_id` int(10) UNSIGNED NOT NULL,
  `ig_user_id` varchar(64) NOT NULL,
  `page_id` varchar(64) DEFAULT NULL,
  `creation_id` varchar(64) NOT NULL,
  `type` enum('post','reels','story') NOT NULL,
  `media_kind` enum('image','video') DEFAULT NULL,
  `media_url` text NOT NULL,
  `caption` text DEFAULT NULL,
  `status` enum('created','processing','published','failed') NOT NULL DEFAULT 'created',
  `status_code` varchar(32) DEFAULT NULL,
  `published_media_id` varchar(64) DEFAULT NULL,
  `published_at` datetime DEFAULT NULL,
  `attempts` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `next_retry_at` datetime DEFAULT NULL,
  `last_error` text DEFAULT NULL,
  `last_response_json` longtext DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `meta_tokens`
--

CREATE TABLE `meta_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `access_token` text NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  `consent_accepted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Tablo döküm verisi `meta_tokens`
--

INSERT INTO `meta_tokens` (`id`, `user_id`, `access_token`, `expires_at`, `consent_accepted_at`, `created_at`, `updated_at`) VALUES
(17, 3, 'EAAZASuqVoDHwBQfA9dPQv4ZAyMpxhGtTQsgu7E79e2jKuVyaIFtHcAIgnsqbZA5bcGNy3c5IGftFCZBNzkr7KZAZCmWEYwJR1NryNaxGgNUfyjZCIU5U2jPDCGiZB3GUFc31GMgoWf3SdPeevuTTEC46c8fEYHvZBXFiv1WBqbOQlmyIPrXO02y7hsyywz5pK', NULL, '2026-01-10 14:41:22', '2026-01-10 14:40:48', '2026-01-10 14:42:51');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `migrations`
--

CREATE TABLE `migrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `migrations`
--

INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
(1, '2025-12-08-103443', 'App\\Database\\Migrations\\CreateUsersTable', 'default', 'App', 1765190297, 1),
(2, '2025-12-08-103537', 'App\\Database\\Migrations\\CreateMediaAndTemplatesTables', 'default', 'App', 1765190297, 1),
(3, '2025-12-08-103611', 'App\\Database\\Migrations\\CreateContentsTables', 'default', 'App', 1765190297, 1),
(4, '2025-12-08-103624', 'App\\Database\\Migrations\\CreateSocialAccountsTables', 'default', 'App', 1765190297, 1),
(5, '2025-12-08-103649', 'App\\Database\\Migrations\\CreateScheduledPostsAndLogsTables', 'default', 'App', 1765190297, 1),
(6, '2025-12-13-201444', 'App\\Database\\Migrations\\AddStatusToUsers', 'default', 'App', 1765656941, 2),
(8, '2025-12-14-102725', 'App\\Database\\Migrations\\CreateLogsTable', 'default', 'App', 1765708265, 3);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `publishes`
--

CREATE TABLE `publishes` (
  `id` int(10) UNSIGNED NOT NULL,
  `job_id` int(10) UNSIGNED DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `platform` varchar(32) NOT NULL,
  `content_kind` varchar(16) DEFAULT NULL,
  `media_kind` varchar(16) DEFAULT NULL,
  `account_id` int(10) UNSIGNED NOT NULL,
  `content_id` int(10) UNSIGNED NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'queued',
  `schedule_at` datetime DEFAULT NULL,
  `idempotency_key` varchar(80) DEFAULT NULL,
  `remote_id` varchar(120) DEFAULT NULL,
  `published_at` datetime DEFAULT NULL,
  `error` varchar(255) DEFAULT NULL,
  `meta_json` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `publishes`
--

INSERT INTO `publishes` (`id`, `job_id`, `user_id`, `platform`, `content_kind`, `media_kind`, `account_id`, `content_id`, `status`, `schedule_at`, `idempotency_key`, `remote_id`, `published_at`, `error`, `meta_json`, `created_at`, `updated_at`) VALUES
(8, 13, 3, 'facebook', NULL, NULL, 26, 12, 'failed', '2026-01-10 14:06:00', '3eaaee52ddc578b8439f92344f3e9599cecbd60262d46e51dfce8884d746793a', NULL, NULL, 'Meta error (403) (#200) If posting to a group, requires app being installed in the group, and \\\n          either publish_to_groups permission with user token, or both pages_read_engagement \\\n          and pages_manage_posts permission with page token; If ', NULL, '2026-01-10 14:04:03', '2026-01-10 14:06:43'),
(9, 14, 3, 'facebook', NULL, NULL, 28, 13, 'published', '2026-01-10 14:18:00', '574ec39b5cee549727e022b3cef7cd4f87df5deb70c48fbdd86e7e49efc70558', '930055826856053_122106117435191800', '2026-01-10 14:18:03', NULL, '{\"meta\":{\"published_id\":\"930055826856053_122106117435191800\",\"permalink\":\"https://www.facebook.com/122106117441191800/posts/122106117435191800\"}}', '2026-01-10 14:16:51', '2026-01-10 14:18:03'),
(10, 15, 3, 'instagram', NULL, NULL, 27, 14, 'published', '2026-01-10 14:38:00', '1f1baf13536867dfc6425a56dd115436a35467ee7460769811e406e347ee9d65', '18090751255951449', '2026-01-10 14:38:10', NULL, '{\"meta\":{\"creation_id\":\"17847831369653299\",\"published_id\":\"18090751255951449\",\"permalink\":\"https://www.instagram.com/p/DTVClz4AHfV/\"}}', '2026-01-10 14:36:13', '2026-01-10 14:38:10'),
(11, 16, 3, 'facebook', NULL, NULL, 28, 14, 'published', '2026-01-10 14:38:00', '31afc53bc1d15a68700507c81c307c02be119feefa5510da74cf68e9e864f965', '930055826856053_122106121281191800', '2026-01-10 14:38:18', NULL, '{\"meta\":{\"published_id\":\"930055826856053_122106121281191800\",\"permalink\":\"https://www.facebook.com/122106117441191800/posts/122106121281191800\"}}', '2026-01-10 14:36:13', '2026-01-10 14:38:18');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `scheduled_posts`
--

CREATE TABLE `scheduled_posts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `publish_id` bigint(20) UNSIGNED DEFAULT NULL,
  `content_id` bigint(20) UNSIGNED NOT NULL,
  `social_account_id` int(11) UNSIGNED NOT NULL,
  `platform` varchar(50) NOT NULL,
  `scheduled_at` datetime NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'scheduled',
  `publish_response` text DEFAULT NULL,
  `retry_count` int(11) NOT NULL DEFAULT 0,
  `last_attempt_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `social_accounts`
--

CREATE TABLE `social_accounts` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `platform` varchar(50) NOT NULL,
  `external_id` varchar(191) NOT NULL,
  `meta_page_id` varchar(64) DEFAULT NULL,
  `access_token` text DEFAULT NULL,
  `token_expires_at` datetime DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `avatar_url` varchar(500) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `social_accounts`
--

INSERT INTO `social_accounts` (`id`, `user_id`, `platform`, `external_id`, `meta_page_id`, `access_token`, `token_expires_at`, `name`, `username`, `avatar_url`, `created_at`, `updated_at`) VALUES
(29, 3, 'instagram', '17841479598962980', '930055826856053', NULL, NULL, 'Mehmet Çokyiğit', 'sosyalmedyaplanla', 'https://scontent.fadb6-3.fna.fbcdn.net/v/t51.82787-15/609654187_17846189679653299_3340274568125121732_n.jpg?_nc_cat=111&ccb=1-7&_nc_sid=7d201b&_nc_ohc=kA37YmZloDkQ7kNvwEfB1Bs&_nc_oc=Admw3FUrcTGBji-Gecea2cKUtldAa4FoOWpIwJI5FVL1KCioGpD49-6JwMmNgPyNT4o&_nc_zt=23&_nc_ht=scontent.fadb6-3.fna&edm=AL-3X8kEAAAA&_nc_gid=McYgA3bId7gnCQp2_rHUwA&oh=00_Afo8bauj_3zl4GTbY1atYRp6uTfsbwPsKQctyyqIidPhjQ&oe=69680736', '2026-01-10 14:42:51', '2026-01-10 14:42:51'),
(30, 3, 'facebook', '930055826856053', '930055826856053', NULL, NULL, 'Sosyalmedyaplanla.com', NULL, NULL, '2026-01-10 14:42:51', '2026-01-10 14:42:51');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `social_account_tokens`
--

CREATE TABLE `social_account_tokens` (
  `id` int(10) UNSIGNED NOT NULL,
  `social_account_id` int(10) UNSIGNED NOT NULL,
  `provider` varchar(32) NOT NULL,
  `access_token` text NOT NULL,
  `token_type` varchar(20) DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `scope` text DEFAULT NULL,
  `meta_json` longtext DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `social_account_tokens`
--

INSERT INTO `social_account_tokens` (`id`, `social_account_id`, `provider`, `access_token`, `token_type`, `expires_at`, `scope`, `meta_json`, `created_at`, `updated_at`) VALUES
(25, 29, 'meta', 'EAAZASuqVoDHwBQSLrUgWvZBO6dUj6wKpzkTG02YePrgx9x1KNiEUObpKrGYXm7gDNPF2Gr8jL993KpjZBOSEB5CVinejfV0pNRU0aXrZAqcbZAg1o7K0WlHMlyktEh1MFTbYcHWovq0wtKEo4oNvqYOQ7IdDbHty9YPSTvWIWMpFoEmlPITbwW7j261aZAmlFTiHBN', 'page', NULL, NULL, '{\"page_id\":\"930055826856053\",\"page_name\":\"Sosyalmedyaplanla.com\"}', '2026-01-10 14:42:51', '2026-01-10 14:42:51'),
(26, 30, 'meta', 'EAAZASuqVoDHwBQSLrUgWvZBO6dUj6wKpzkTG02YePrgx9x1KNiEUObpKrGYXm7gDNPF2Gr8jL993KpjZBOSEB5CVinejfV0pNRU0aXrZAqcbZAg1o7K0WlHMlyktEh1MFTbYcHWovq0wtKEo4oNvqYOQ7IdDbHty9YPSTvWIWMpFoEmlPITbwW7j261aZAmlFTiHBN', 'page', NULL, NULL, '{\"page_id\":\"930055826856053\",\"page_name\":\"Sosyalmedyaplanla.com\"}', '2026-01-10 14:42:51', '2026-01-10 14:42:51');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `social_tokens`
--

CREATE TABLE `social_tokens` (
  `id` int(11) UNSIGNED NOT NULL,
  `social_account_id` int(11) UNSIGNED NOT NULL,
  `access_token` text NOT NULL,
  `refresh_token` text DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `scopes` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `templates`
--

CREATE TABLE `templates` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `platform_type` varchar(50) DEFAULT NULL,
  `base_media_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'user',
  `status` varchar(20) DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `status`, `created_at`, `updated_at`) VALUES
(2, 'test admin', 'root@root.com', '$2y$10$IebEUvMec38dHJN6qrPdZ.jv7CMiRqBg0yFNvaD8uFgByngj40eAS', 'admin', 'active', '2025-12-08 11:10:47', '2025-12-13 13:15:38'),
(3, 'USER', 'user@user.com', '$2y$10$jcsBiNiZGdVUrVEVG2wT5et1hWgtMqEyeH7WvPOvDlJFhZq/H2GE.', 'user', 'active', '2025-12-13 22:37:01', '2025-12-16 21:25:15'),
(4, 'test2', 'test2@test.com', '$2y$10$VwBW.mQpU6OE8sOAQOaiXOWbpbgiLV9YYFvyRpZ5oeBTQyGl7oKGy', 'user', 'active', '2025-12-13 20:08:34', '2025-12-13 20:26:52'),
(5, 'test2', 'test3@test.com', '$2y$10$XI8DpwOt3V5Cktn7dWjqT.liFZF7Srx5Sju7Zj/Jx2UtdY2Q2xFe6', 'user', 'active', '2025-12-13 20:27:51', '2025-12-14 10:02:14');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `user_consents`
--

CREATE TABLE `user_consents` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `consent_key` varchar(64) NOT NULL,
  `consent_version` varchar(16) NOT NULL,
  `accepted_at` datetime NOT NULL,
  `ip_address` varchar(64) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `user_consents`
--

INSERT INTO `user_consents` (`id`, `user_id`, `consent_key`, `consent_version`, `accepted_at`, `ip_address`, `user_agent`) VALUES
(1, 3, 'meta_oauth_authorization', 'v1', '2025-12-22 21:55:32', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `contents`
--
ALTER TABLE `contents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contents_media_id_foreign` (`media_id`),
  ADD KEY `idx_contents_user_id` (`user_id`),
  ADD KEY `idx_contents_template_id` (`template_id`);

--
-- Tablo için indeksler `content_variants`
--
ALTER TABLE `content_variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `content_variants_content_id_foreign` (`content_id`),
  ADD KEY `content_variants_thumbnail_media_id_foreign` (`thumbnail_media_id`);

--
-- Tablo için indeksler `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_jobs_priority_runat` (`priority`,`run_at`),
  ADD KEY `idx_jobs_locked` (`locked_at`,`locked_by`),
  ADD KEY `idx_jobs_status_runat` (`status`,`run_at`);

--
-- Tablo için indeksler `job_attempts`
--
ALTER TABLE `job_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_job_attempts_job_id` (`job_id`),
  ADD KEY `idx_job_attempts_created_at` (`created_at`);

--
-- Tablo için indeksler `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_logs_level_channel` (`level`,`channel`),
  ADD KEY `idx_logs_user_id` (`user_id`),
  ADD KEY `idx_logs_created_at` (`created_at`);

--
-- Tablo için indeksler `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `media_user_id_foreign` (`user_id`);

--
-- Tablo için indeksler `meta_media_jobs`
--
ALTER TABLE `meta_media_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_creation_id` (`creation_id`),
  ADD KEY `idx_status_retry` (`status`,`next_retry_at`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_social_account` (`social_account_id`),
  ADD KEY `idx_publish_id` (`publish_id`);

--
-- Tablo için indeksler `meta_tokens`
--
ALTER TABLE `meta_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user` (`user_id`);

--
-- Tablo için indeksler `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `publishes`
--
ALTER TABLE `publishes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_publishes_user_idem` (`user_id`,`idempotency_key`),
  ADD KEY `idx_publishes_job_id` (`job_id`),
  ADD KEY `idx_publishes_user_id` (`user_id`),
  ADD KEY `idx_publishes_platform_account` (`platform`,`account_id`),
  ADD KEY `idx_publishes_status_schedule` (`status`,`schedule_at`),
  ADD KEY `idx_publishes_created_at` (`created_at`),
  ADD KEY `idx_publishes_idempotency` (`idempotency_key`,`platform`,`account_id`),
  ADD KEY `idx_publishes_user_schedule` (`user_id`,`schedule_at`),
  ADD KEY `idx_publishes_user_status` (`user_id`,`status`);

--
-- Tablo için indeksler `scheduled_posts`
--
ALTER TABLE `scheduled_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `scheduled_posts_content_id_foreign` (`content_id`),
  ADD KEY `scheduled_posts_social_account_id_foreign` (`social_account_id`),
  ADD KEY `idx_publish_id` (`publish_id`);

--
-- Tablo için indeksler `social_accounts`
--
ALTER TABLE `social_accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `social_accounts_user_id_foreign` (`user_id`);

--
-- Tablo için indeksler `social_account_tokens`
--
ALTER TABLE `social_account_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_tokens_account_provider` (`social_account_id`,`provider`),
  ADD KEY `idx_tokens_expires` (`expires_at`);

--
-- Tablo için indeksler `social_tokens`
--
ALTER TABLE `social_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `social_tokens_social_account_id_foreign` (`social_account_id`);

--
-- Tablo için indeksler `templates`
--
ALTER TABLE `templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `templates_base_media_id_foreign` (`base_media_id`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Tablo için indeksler `user_consents`
--
ALTER TABLE `user_consents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_user_consent` (`user_id`,`consent_key`,`consent_version`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `contents`
--
ALTER TABLE `contents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Tablo için AUTO_INCREMENT değeri `content_variants`
--
ALTER TABLE `content_variants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Tablo için AUTO_INCREMENT değeri `job_attempts`
--
ALTER TABLE `job_attempts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Tablo için AUTO_INCREMENT değeri `logs`
--
ALTER TABLE `logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- Tablo için AUTO_INCREMENT değeri `media`
--
ALTER TABLE `media`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `meta_media_jobs`
--
ALTER TABLE `meta_media_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `meta_tokens`
--
ALTER TABLE `meta_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Tablo için AUTO_INCREMENT değeri `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Tablo için AUTO_INCREMENT değeri `publishes`
--
ALTER TABLE `publishes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Tablo için AUTO_INCREMENT değeri `scheduled_posts`
--
ALTER TABLE `scheduled_posts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `social_accounts`
--
ALTER TABLE `social_accounts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- Tablo için AUTO_INCREMENT değeri `social_account_tokens`
--
ALTER TABLE `social_account_tokens`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Tablo için AUTO_INCREMENT değeri `social_tokens`
--
ALTER TABLE `social_tokens`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `templates`
--
ALTER TABLE `templates`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Tablo için AUTO_INCREMENT değeri `user_consents`
--
ALTER TABLE `user_consents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `contents`
--
ALTER TABLE `contents`
  ADD CONSTRAINT `contents_media_id_foreign` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  ADD CONSTRAINT `contents_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  ADD CONSTRAINT `contents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Tablo kısıtlamaları `content_variants`
--
ALTER TABLE `content_variants`
  ADD CONSTRAINT `content_variants_content_id_foreign` FOREIGN KEY (`content_id`) REFERENCES `contents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `content_variants_thumbnail_media_id_foreign` FOREIGN KEY (`thumbnail_media_id`) REFERENCES `media` (`id`) ON DELETE CASCADE ON UPDATE SET NULL;

--
-- Tablo kısıtlamaları `job_attempts`
--
ALTER TABLE `job_attempts`
  ADD CONSTRAINT `fk_job_attempts_job_id` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Tablo kısıtlamaları `media`
--
ALTER TABLE `media`
  ADD CONSTRAINT `media_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE SET NULL;

--
-- Tablo kısıtlamaları `scheduled_posts`
--
ALTER TABLE `scheduled_posts`
  ADD CONSTRAINT `scheduled_posts_content_id_foreign` FOREIGN KEY (`content_id`) REFERENCES `contents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `scheduled_posts_social_account_id_foreign` FOREIGN KEY (`social_account_id`) REFERENCES `social_accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Tablo kısıtlamaları `social_accounts`
--
ALTER TABLE `social_accounts`
  ADD CONSTRAINT `social_accounts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Tablo kısıtlamaları `social_account_tokens`
--
ALTER TABLE `social_account_tokens`
  ADD CONSTRAINT `fk_tokens_account` FOREIGN KEY (`social_account_id`) REFERENCES `social_accounts` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `social_tokens`
--
ALTER TABLE `social_tokens`
  ADD CONSTRAINT `social_tokens_social_account_id_foreign` FOREIGN KEY (`social_account_id`) REFERENCES `social_accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Tablo kısıtlamaları `templates`
--
ALTER TABLE `templates`
  ADD CONSTRAINT `templates_base_media_id_foreign` FOREIGN KEY (`base_media_id`) REFERENCES `media` (`id`) ON DELETE CASCADE ON UPDATE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
