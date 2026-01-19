/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.13-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: otomedya
-- ------------------------------------------------------
-- Server version	10.11.13-MariaDB-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `content_variants`
--

DROP TABLE IF EXISTS `content_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `content_variants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `content_id` bigint(20) unsigned NOT NULL,
  `platform` varchar(50) NOT NULL,
  `text` text DEFAULT NULL,
  `thumbnail_media_id` bigint(20) unsigned DEFAULT NULL,
  `extra_meta` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `content_variants_content_id_foreign` (`content_id`),
  KEY `content_variants_thumbnail_media_id_foreign` (`thumbnail_media_id`),
  CONSTRAINT `content_variants_content_id_foreign` FOREIGN KEY (`content_id`) REFERENCES `contents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `content_variants_thumbnail_media_id_foreign` FOREIGN KEY (`thumbnail_media_id`) REFERENCES `media` (`id`) ON DELETE CASCADE ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `content_variants`
--

LOCK TABLES `content_variants` WRITE;
/*!40000 ALTER TABLE `content_variants` DISABLE KEYS */;
/*!40000 ALTER TABLE `content_variants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contents`
--

DROP TABLE IF EXISTS `contents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `contents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `base_text` text DEFAULT NULL,
  `media_type` varchar(20) DEFAULT NULL,
  `media_path` varchar(255) DEFAULT NULL,
  `media_id` bigint(20) unsigned DEFAULT NULL,
  `template_id` int(11) unsigned DEFAULT NULL,
  `meta_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta_json`)),
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contents_media_id_foreign` (`media_id`),
  KEY `idx_contents_user_id` (`user_id`),
  KEY `idx_contents_template_id` (`template_id`),
  CONSTRAINT `contents_media_id_foreign` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `contents_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `contents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contents`
--

LOCK TABLES `contents` WRITE;
/*!40000 ALTER TABLE `contents` DISABLE KEYS */;
INSERT INTO `contents` VALUES
(1,4,NULL,NULL,'video','uploads/2026/01/1768770820_8010ac1e598f4652d563.mp4',NULL,NULL,'{\"post_type\":\"auto\",\"youtube\":{\"title\":\"testt\",\"privacy\":\"public\"}}','2026-01-19 00:13:40','2026-01-19 00:13:40'),
(2,4,'test','testt','video','uploads/2026/01/1768804736_ab9719b525b0fa562352.mp4',NULL,NULL,'{\"post_type\":\"auto\",\"youtube\":{\"title\":\"test\",\"privacy\":\"public\"}}','2026-01-19 09:38:56','2026-01-19 09:38:56'),
(3,4,'test orta uzun video','test orta uzun videoo','video','uploads/2026/01/1768809942_a9ff229b7d2e11ca9e89.mp4',NULL,NULL,'{\"post_type\":\"auto\",\"youtube\":{\"title\":\"test orta uzun videooo\",\"privacy\":\"private\"}}','2026-01-19 11:05:42','2026-01-19 11:05:42');
/*!40000 ALTER TABLE `contents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_attempts`
--

DROP TABLE IF EXISTS `job_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_attempts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` bigint(20) unsigned NOT NULL,
  `attempt_no` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `started_at` datetime NOT NULL,
  `finished_at` datetime DEFAULT NULL,
  `error` longtext DEFAULT NULL,
  `response_json` longtext DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_job_attempts_job_id` (`job_id`),
  KEY `idx_job_attempts_created_at` (`created_at`),
  CONSTRAINT `fk_job_attempts_job_id` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_attempts`
--

LOCK TABLES `job_attempts` WRITE;
/*!40000 ALTER TABLE `job_attempts` DISABLE KEYS */;
INSERT INTO `job_attempts` VALUES
(1,1,1,'failed','2026-01-19 00:15:00','2026-01-19 00:21:01','YT UPLOAD failed HTTP=408 ERR=\n#0 /var/www/otomedya/app/Queue/Handlers/PublishYouTubeHandler.php(147): App\\Queue\\Handlers\\PublishYouTubeHandler->uploadResumable()\n#1 /var/www/otomedya/app/Commands/QueueWork.php(80): App\\Queue\\Handlers\\PublishYouTubeHandler->handle()\n#2 /var/www/otomedya/vendor/codeigniter4/framework/system/CLI/Commands.php(74): App\\Commands\\QueueWork->run()\n#3 /var/www/otomedya/vendor/codeigniter4/framework/system/CLI/Console.php(47): CodeIgniter\\CLI\\Commands->run()\n#4 /var/www/otomedya/vendor/codeigniter4/framework/system/Boot.php(388): CodeIgniter\\CLI\\Console->run()\n#5 /var/www/otomedya/vendor/codeigniter4/framework/system/Boot.php(133): CodeIgniter\\Boot::runCommand()\n#6 /var/www/otomedya/spark(87): CodeIgniter\\Boot::bootSpark()\n#7 {main}',NULL,'2026-01-19 00:15:00'),
(2,1,2,'failed','2026-01-19 00:21:11','2026-01-19 00:27:13','YT UPLOAD failed HTTP=408 ERR=\n#0 /var/www/otomedya/app/Queue/Handlers/PublishYouTubeHandler.php(147): App\\Queue\\Handlers\\PublishYouTubeHandler->uploadResumable()\n#1 /var/www/otomedya/app/Commands/QueueWork.php(80): App\\Queue\\Handlers\\PublishYouTubeHandler->handle()\n#2 /var/www/otomedya/vendor/codeigniter4/framework/system/CLI/Commands.php(74): App\\Commands\\QueueWork->run()\n#3 /var/www/otomedya/vendor/codeigniter4/framework/system/CLI/Console.php(47): CodeIgniter\\CLI\\Commands->run()\n#4 /var/www/otomedya/vendor/codeigniter4/framework/system/Boot.php(388): CodeIgniter\\CLI\\Console->run()\n#5 /var/www/otomedya/vendor/codeigniter4/framework/system/Boot.php(133): CodeIgniter\\Boot::runCommand()\n#6 /var/www/otomedya/spark(87): CodeIgniter\\Boot::bootSpark()\n#7 {main}',NULL,'2026-01-19 00:21:11'),
(3,1,1,'started','2026-01-19 00:21:52',NULL,NULL,NULL,'2026-01-19 00:21:52'),
(4,1,1,'started','2026-01-19 00:27:07',NULL,NULL,NULL,'2026-01-19 00:27:07'),
(5,1,3,'failed','2026-01-19 00:27:43','2026-01-19 00:33:44','YT UPLOAD failed HTTP=408 ERR=\n#0 /var/www/otomedya/app/Queue/Handlers/PublishYouTubeHandler.php(147): App\\Queue\\Handlers\\PublishYouTubeHandler->uploadResumable()\n#1 /var/www/otomedya/app/Commands/QueueWork.php(80): App\\Queue\\Handlers\\PublishYouTubeHandler->handle()\n#2 /var/www/otomedya/vendor/codeigniter4/framework/system/CLI/Commands.php(74): App\\Commands\\QueueWork->run()\n#3 /var/www/otomedya/vendor/codeigniter4/framework/system/CLI/Console.php(47): CodeIgniter\\CLI\\Commands->run()\n#4 /var/www/otomedya/vendor/codeigniter4/framework/system/Boot.php(388): CodeIgniter\\CLI\\Console->run()\n#5 /var/www/otomedya/vendor/codeigniter4/framework/system/Boot.php(133): CodeIgniter\\Boot::runCommand()\n#6 /var/www/otomedya/spark(87): CodeIgniter\\Boot::bootSpark()\n#7 {main}',NULL,'2026-01-19 00:27:43'),
(6,2,1,'success','2026-01-19 09:41:00','2026-01-19 09:41:03',NULL,NULL,'2026-01-19 09:41:00'),
(7,3,1,'success','2026-01-19 11:07:00','2026-01-19 11:07:02',NULL,NULL,'2026-01-19 11:07:00');
/*!40000 ALTER TABLE `job_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
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
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_jobs_priority_runat` (`priority`,`run_at`),
  KEY `idx_jobs_locked` (`locked_at`,`locked_by`),
  KEY `idx_jobs_status_runat` (`status`,`run_at`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
INSERT INTO `jobs` VALUES
(1,'publish_youtube','{\"publish_id\":1,\"platform\":\"youtube\",\"account_id\":48,\"content_id\":1}','failed',100,'2026-01-19 00:27:43',NULL,NULL,3,3,'YT UPLOAD failed HTTP=408 ERR=','2026-01-19 00:13:40','2026-01-19 00:33:44'),
(2,'publish_youtube','{\"publish_id\":2,\"platform\":\"youtube\",\"account_id\":49,\"content_id\":2}','done',100,'2026-01-19 09:41:00',NULL,NULL,0,3,NULL,'2026-01-19 09:38:56','2026-01-19 09:41:03'),
(3,'publish_youtube','{\"publish_id\":3,\"platform\":\"youtube\",\"account_id\":49,\"content_id\":3}','done',100,'2026-01-19 11:07:00',NULL,NULL,0,3,NULL,'2026-01-19 11:05:42','2026-01-19 11:07:02');
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `level` varchar(20) NOT NULL,
  `channel` varchar(50) NOT NULL,
  `message` varchar(255) NOT NULL,
  `context_json` longtext DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_logs_level_channel` (`level`,`channel`),
  KEY `idx_logs_user_id` (`user_id`),
  KEY `idx_logs_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=471 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logs`
--

LOCK TABLES `logs` WRITE;
/*!40000 ALTER TABLE `logs` DISABLE KEYS */;
INSERT INTO `logs` VALUES
(1,'info','queue','job.reserved','{\"job_id\":11,\"type\":\"publish_post\",\"worker\":\"User:10972\",\"pid\":10972,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-10 13:28:00'),
(2,'info','queue','publish.started','{\"publish_id\":6,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"none\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-10 13:28:00'),
(3,'error','queue','job.failed','{\"job_id\":11,\"type\":\"publish_post\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-10 13:28:11\",\"hata\":\"Meta error (403) (#200) If posting to a group, requires app being installed in the group, and \\\\\\n          either publish_to_groups permission with user token, or both pages_read_engagement \\\\\\n          and pages_manage_posts permission with page token; If posting to a page, \\\\\\n          requires both pages_read_engagement and pages_manage_posts as an admin with \\\\\\n          sufficient administrative permission | code=200 subcode= trace=AGGrj3w4n554IPU0jvXRc9_\",\"worker\":\"User:10972\",\"pid\":10972,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-10 13:28:01'),
(4,'info','queue','job.reserved','{\"job_id\":11,\"type\":\"publish_post\",\"worker\":\"User:10972\",\"pid\":10972,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-10 13:28:11'),
(5,'info','queue','publish.started','{\"publish_id\":6,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"none\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-10 13:28:11'),
(6,'error','queue','job.failed','{\"job_id\":11,\"type\":\"publish_post\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-10 13:28:42\",\"hata\":\"Meta error (403) (#200) If posting to a group, requires app being installed in the group, and \\\\\\n          either publish_to_groups permission with user token, or both pages_read_engagement \\\\\\n          and pages_manage_posts permission with page token; If posting to a page, \\\\\\n          requires both pages_read_engagement and pages_manage_posts as an admin with \\\\\\n          sufficient administrative permission | code=200 subcode= trace=AQPqDL_UZ5b1G1QFWYkE4nI\",\"worker\":\"User:10972\",\"pid\":10972,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-10 13:28:12'),
(7,'info','queue','job.reserved','{\"job_id\":11,\"type\":\"publish_post\",\"worker\":\"User:10972\",\"pid\":10972,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-10 13:28:42'),
(8,'info','queue','publish.started','{\"publish_id\":6,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"none\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-10 13:28:42'),
(9,'error','queue','job.failed','{\"job_id\":11,\"type\":\"publish_post\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-10 13:28:42\",\"hata\":\"Meta error (403) (#200) If posting to a group, requires app being installed in the group, and \\\\\\n          either publish_to_groups permission with user token, or both pages_read_engagement \\\\\\n          and pages_manage_posts permission with page token; If posting to a page, \\\\\\n          requires both pages_read_engagement and pages_manage_posts as an admin with \\\\\\n          sufficient administrative permission | code=200 subcode= trace=AIPXeAqJJF67t19u2Levx4B\",\"worker\":\"User:10972\",\"pid\":10972,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-10 13:28:43'),
(10,'info','queue','job.reserved','{\"job_id\":12,\"type\":\"publish_post\",\"worker\":\"User:19228\",\"pid\":19228,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-10 13:59:01'),
(11,'info','queue','publish.started','{\"publish_id\":7,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"none\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-10 13:59:01'),
(12,'error','queue','job.failed','{\"job_id\":12,\"type\":\"publish_post\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-10 13:59:12\",\"hata\":\"Meta error (403) (#200) If posting to a group, requires app being installed in the group, and \\\\\\n          either publish_to_groups permission with user token, or both pages_read_engagement \\\\\\n          and pages_manage_posts permission with page token; If posting to a page, \\\\\\n          requires both pages_read_engagement and pages_manage_posts as an admin with \\\\\\n          sufficient administrative permission | code=200 subcode= trace=AqpIse3VCmsPPC3Tg7ws6Dt\",\"worker\":\"User:19228\",\"pid\":19228,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-10 13:59:02'),
(13,'info','queue','job.reserved','{\"job_id\":12,\"type\":\"publish_post\",\"worker\":\"User:19228\",\"pid\":19228,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-10 13:59:12'),
(14,'info','queue','publish.started','{\"publish_id\":7,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"none\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-10 13:59:12'),
(15,'error','queue','job.failed','{\"job_id\":12,\"type\":\"publish_post\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-10 13:59:43\",\"hata\":\"Meta error (403) (#200) If posting to a group, requires app being installed in the group, and \\\\\\n          either publish_to_groups permission with user token, or both pages_read_engagement \\\\\\n          and pages_manage_posts permission with page token; If posting to a page, \\\\\\n          requires both pages_read_engagement and pages_manage_posts as an admin with \\\\\\n          sufficient administrative permission | code=200 subcode= trace=A6rcCuzgjAgX47JCzu9hzhc\",\"worker\":\"User:19228\",\"pid\":19228,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-10 13:59:13'),
(16,'info','queue','job.reserved','{\"job_id\":12,\"type\":\"publish_post\",\"worker\":\"User:19228\",\"pid\":19228,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-10 13:59:43'),
(17,'info','queue','publish.started','{\"publish_id\":7,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"none\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-10 13:59:43'),
(18,'error','queue','job.failed','{\"job_id\":12,\"type\":\"publish_post\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-10 13:59:43\",\"hata\":\"Meta error (403) (#200) If posting to a group, requires app being installed in the group, and \\\\\\n          either publish_to_groups permission with user token, or both pages_read_engagement \\\\\\n          and pages_manage_posts permission with page token; If posting to a page, \\\\\\n          requires both pages_read_engagement and pages_manage_posts as an admin with \\\\\\n          sufficient administrative permission | code=200 subcode= trace=Ae2otrJ-AwxH99OSXq4sdk6\",\"worker\":\"User:19228\",\"pid\":19228,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-10 13:59:44'),
(19,'info','queue','job.reserved','{\"job_id\":13,\"type\":\"publish_post\",\"worker\":\"User:16008\",\"pid\":16008,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-10 14:06:00'),
(20,'info','queue','publish.started','{\"publish_id\":8,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"none\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-10 14:06:00'),
(21,'error','queue','job.failed','{\"job_id\":13,\"type\":\"publish_post\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-10 14:06:11\",\"hata\":\"Meta error (403) (#200) If posting to a group, requires app being installed in the group, and \\\\\\n          either publish_to_groups permission with user token, or both pages_read_engagement \\\\\\n          and pages_manage_posts permission with page token; If posting to a page, \\\\\\n          requires both pages_read_engagement and pages_manage_posts as an admin with \\\\\\n          sufficient administrative permission | code=200 subcode= trace=AL03FWajJTARY2iOaCMzE38\",\"worker\":\"User:16008\",\"pid\":16008,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-10 14:06:01'),
(22,'info','queue','job.reserved','{\"job_id\":13,\"type\":\"publish_post\",\"worker\":\"User:16008\",\"pid\":16008,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-10 14:06:11'),
(23,'info','queue','publish.started','{\"publish_id\":8,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"none\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-10 14:06:11'),
(24,'error','queue','job.failed','{\"job_id\":13,\"type\":\"publish_post\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-10 14:06:42\",\"hata\":\"Meta error (403) (#200) If posting to a group, requires app being installed in the group, and \\\\\\n          either publish_to_groups permission with user token, or both pages_read_engagement \\\\\\n          and pages_manage_posts permission with page token; If posting to a page, \\\\\\n          requires both pages_read_engagement and pages_manage_posts as an admin with \\\\\\n          sufficient administrative permission | code=200 subcode= trace=A6xMb0SuRAKbP_ZDJgDMjBy\",\"worker\":\"User:16008\",\"pid\":16008,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-10 14:06:12'),
(25,'info','queue','job.reserved','{\"job_id\":13,\"type\":\"publish_post\",\"worker\":\"User:16008\",\"pid\":16008,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-10 14:06:42'),
(26,'info','queue','publish.started','{\"publish_id\":8,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"none\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-10 14:06:42'),
(27,'error','queue','job.failed','{\"job_id\":13,\"type\":\"publish_post\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-10 14:06:42\",\"hata\":\"Meta error (403) (#200) If posting to a group, requires app being installed in the group, and \\\\\\n          either publish_to_groups permission with user token, or both pages_read_engagement \\\\\\n          and pages_manage_posts permission with page token; If posting to a page, \\\\\\n          requires both pages_read_engagement and pages_manage_posts as an admin with \\\\\\n          sufficient administrative permission | code=200 subcode= trace=AV7J7IqCi1MNlpkJTfglUGp\",\"worker\":\"User:16008\",\"pid\":16008,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-10 14:06:43'),
(28,'info','queue','job.reserved','{\"job_id\":14,\"type\":\"publish_post\",\"worker\":\"User:20456\",\"pid\":20456,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-10 14:18:00'),
(29,'info','queue','publish.started','{\"publish_id\":9,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"none\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-10 14:18:00'),
(30,'info','queue','publish.succeeded','{\"publish_id\":9,\"remote_id\":\"930055826856053_122106117435191800\",\"permalink\":\"https:\\/\\/www.facebook.com\\/122106117441191800\\/posts\\/122106117435191800\",\"_event\":\"publish.succeeded\"}',3,NULL,NULL,'2026-01-10 14:18:03'),
(31,'info','queue','job.succeeded','{\"job_id\":14,\"type\":\"publish_post\",\"deneme\":1,\"worker\":\"User:20456\",\"pid\":20456,\"_event\":\"job.succeeded\"}',NULL,NULL,NULL,'2026-01-10 14:18:03'),
(32,'info','queue','job.reserved','{\"job_id\":15,\"type\":\"publish_post\",\"worker\":\"User:1148\",\"pid\":1148,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-10 14:38:01'),
(33,'info','queue','publish.started','{\"publish_id\":10,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-10 14:38:01'),
(34,'info','queue','publish.succeeded','{\"publish_id\":10,\"remote_id\":\"18090751255951449\",\"creation_id\":\"17847831369653299\",\"permalink\":\"https:\\/\\/www.instagram.com\\/p\\/DTVClz4AHfV\\/\",\"_event\":\"publish.succeeded\"}',3,NULL,NULL,'2026-01-10 14:38:10'),
(35,'info','queue','job.succeeded','{\"job_id\":15,\"type\":\"publish_post\",\"deneme\":1,\"worker\":\"User:1148\",\"pid\":1148,\"_event\":\"job.succeeded\"}',NULL,NULL,NULL,'2026-01-10 14:38:10'),
(36,'info','queue','job.reserved','{\"job_id\":16,\"type\":\"publish_post\",\"worker\":\"User:1148\",\"pid\":1148,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-10 14:38:10'),
(37,'info','queue','publish.started','{\"publish_id\":11,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-10 14:38:10'),
(38,'info','queue','publish.succeeded','{\"publish_id\":11,\"remote_id\":\"930055826856053_122106121281191800\",\"permalink\":\"https:\\/\\/www.facebook.com\\/122106117441191800\\/posts\\/122106121281191800\",\"_event\":\"publish.succeeded\"}',3,NULL,NULL,'2026-01-10 14:38:18'),
(39,'info','queue','job.succeeded','{\"job_id\":16,\"type\":\"publish_post\",\"deneme\":1,\"worker\":\"User:1148\",\"pid\":1148,\"_event\":\"job.succeeded\"}',NULL,NULL,NULL,'2026-01-10 14:38:18'),
(40,'info','queue','job.reserved','{\"job_id\":17,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 13:32:02'),
(41,'info','queue','publish.started','{\"publish_id\":12,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 13:32:02'),
(42,'error','queue','job.failed','{\"job_id\":17,\"type\":\"publish_post\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-12 13:32:13\",\"hata\":\"Meta error (400) Only photo or video can be accepted as media type. | code=9004 subcode=2207052 trace=AQtt3tNhiHiRa3MBajPj6Ls\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 13:32:03'),
(43,'info','queue','job.reserved','{\"job_id\":18,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 13:32:03'),
(44,'info','queue','publish.started','{\"publish_id\":13,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 13:32:03'),
(45,'error','queue','job.failed','{\"job_id\":18,\"type\":\"publish_post\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-12 13:32:14\",\"hata\":\"Meta error (400) Missing or invalid image file | code=324 subcode=2069019 trace=ADf-hJAoIPo_rBvLedjS28E\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 13:32:04'),
(46,'info','queue','job.reserved','{\"job_id\":17,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 13:32:14'),
(47,'info','queue','publish.started','{\"publish_id\":12,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 13:32:14'),
(48,'error','queue','job.failed','{\"job_id\":17,\"type\":\"publish_post\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-12 13:32:44\",\"hata\":\"Meta error (400) Only photo or video can be accepted as media type. | code=9004 subcode=2207052 trace=ABBJAhsnmi9CMuQUgq-Ex_H\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 13:32:14'),
(49,'info','queue','job.reserved','{\"job_id\":18,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 13:32:14'),
(50,'info','queue','publish.started','{\"publish_id\":13,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 13:32:14'),
(51,'error','queue','job.failed','{\"job_id\":18,\"type\":\"publish_post\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-12 13:32:45\",\"hata\":\"Meta error (400) Missing or invalid image file | code=324 subcode=2069019 trace=Au7DvwWBjBgziVfPqhKiKy7\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 13:32:15'),
(52,'info','queue','job.reserved','{\"job_id\":17,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 13:32:45'),
(53,'info','queue','publish.started','{\"publish_id\":12,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 13:32:45'),
(54,'error','queue','job.failed','{\"job_id\":17,\"type\":\"publish_post\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-12 13:32:44\",\"hata\":\"Meta error (400) Only photo or video can be accepted as media type. | code=9004 subcode=2207052 trace=AJe_Sbf0_OC1HMLu3yrjEN0\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 13:32:46'),
(55,'info','queue','job.reserved','{\"job_id\":18,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 13:32:46'),
(56,'info','queue','publish.started','{\"publish_id\":13,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 13:32:46'),
(57,'error','queue','job.failed','{\"job_id\":18,\"type\":\"publish_post\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-12 13:32:45\",\"hata\":\"Meta error (400) Missing or invalid image file | code=324 subcode=2069019 trace=ACiwbx1J32kl-TuKnNvS-CG\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 13:32:46'),
(58,'info','queue','job.reserved','{\"job_id\":19,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 13:34:00'),
(59,'info','queue','publish.started','{\"publish_id\":14,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 13:34:00'),
(60,'error','queue','job.failed','{\"job_id\":19,\"type\":\"publish_post\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-12 13:34:11\",\"hata\":\"Meta error (400) Only photo or video can be accepted as media type. | code=9004 subcode=2207052 trace=AuSxxKDTWkgtJa3x_VkHifT\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 13:34:01'),
(61,'info','queue','job.reserved','{\"job_id\":20,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 13:34:01'),
(62,'info','queue','publish.started','{\"publish_id\":15,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 13:34:01'),
(63,'error','queue','job.failed','{\"job_id\":20,\"type\":\"publish_post\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-12 13:34:12\",\"hata\":\"Meta error (400) Missing or invalid image file | code=324 subcode=2069019 trace=A7C5FK3lCi9Oh0KCniyJ-WM\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 13:34:02'),
(64,'info','queue','job.reserved','{\"job_id\":19,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 13:34:12'),
(65,'info','queue','publish.started','{\"publish_id\":14,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 13:34:12'),
(66,'error','queue','job.failed','{\"job_id\":19,\"type\":\"publish_post\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-12 13:34:43\",\"hata\":\"Meta error (400) Only photo or video can be accepted as media type. | code=9004 subcode=2207052 trace=AwX3nKUDeI4411ridbzuAli\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 13:34:13'),
(67,'info','queue','job.reserved','{\"job_id\":20,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 13:34:13'),
(68,'info','queue','publish.started','{\"publish_id\":15,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 13:34:13'),
(69,'error','queue','job.failed','{\"job_id\":20,\"type\":\"publish_post\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-12 13:34:43\",\"hata\":\"Meta error (400) Missing or invalid image file | code=324 subcode=2069019 trace=AEQB-10ANaLLdtJzrNoCtNa\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 13:34:13'),
(70,'info','queue','job.reserved','{\"job_id\":19,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 13:34:43'),
(71,'info','queue','publish.started','{\"publish_id\":14,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 13:34:43'),
(72,'error','queue','job.failed','{\"job_id\":19,\"type\":\"publish_post\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-12 13:34:43\",\"hata\":\"Meta error (400) Only photo or video can be accepted as media type. | code=9004 subcode=2207052 trace=AvLm9sg4jhdK74fJ2vAwRD9\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 13:34:44'),
(73,'info','queue','job.reserved','{\"job_id\":20,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 13:34:44'),
(74,'info','queue','publish.started','{\"publish_id\":15,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 13:34:44'),
(75,'error','queue','job.failed','{\"job_id\":20,\"type\":\"publish_post\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-12 13:34:43\",\"hata\":\"Meta error (400) Missing or invalid image file | code=324 subcode=2069019 trace=AK1zYTNibjOMkdm0mCqwEZG\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 13:34:45'),
(76,'info','queue','job.reserved','{\"job_id\":20,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 17:00:42'),
(77,'info','queue','publish.started','{\"publish_id\":15,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 17:00:42'),
(78,'error','queue','job.failed','{\"job_id\":20,\"type\":\"publish_post\",\"deneme\":4,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-12 17:00:42\",\"hata\":\"Meta error (400) Missing or invalid image file | code=324 subcode=2069019 trace=Aajmu8LLBYHICe9DThd6U3h\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 17:00:43'),
(79,'info','queue','job.reserved','{\"job_id\":20,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 17:00:49'),
(80,'info','queue','publish.started','{\"publish_id\":15,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 17:00:49'),
(81,'error','queue','job.failed','{\"job_id\":20,\"type\":\"publish_post\",\"deneme\":5,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-12 17:00:48\",\"hata\":\"Meta error (400) Missing or invalid image file | code=324 subcode=2069019 trace=AyhSCZpLFtDPTt3ZECwqejX\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 17:00:50'),
(82,'info','queue','job.reserved','{\"job_id\":20,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 17:00:56'),
(83,'info','queue','publish.started','{\"publish_id\":15,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 17:00:56'),
(84,'error','queue','job.failed','{\"job_id\":20,\"type\":\"publish_post\",\"deneme\":6,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-12 17:00:55\",\"hata\":\"Meta error (400) Missing or invalid image file | code=324 subcode=2069019 trace=A_XhwWBRk_2nCD7YyDLqh1F\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 17:00:56'),
(85,'info','queue','job.reserved','{\"job_id\":19,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 17:01:06'),
(86,'info','queue','publish.started','{\"publish_id\":14,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 17:01:06'),
(87,'error','queue','job.failed','{\"job_id\":19,\"type\":\"publish_post\",\"deneme\":4,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-12 17:01:04\",\"hata\":\"Meta error (400) Only photo or video can be accepted as media type. | code=9004 subcode=2207052 trace=A-B1kHqqg88QvLpqiArLK2f\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 17:01:07'),
(88,'info','queue','job.reserved','{\"job_id\":20,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 17:01:21'),
(89,'info','queue','publish.started','{\"publish_id\":15,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 17:01:21'),
(90,'error','queue','job.failed','{\"job_id\":20,\"type\":\"publish_post\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-12 17:01:32\",\"hata\":\"Meta error (400) Missing or invalid image file | code=324 subcode=2069019 trace=A_3BVt2701JERS6zEaTSSGP\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 17:01:22'),
(91,'info','queue','job.reserved','{\"job_id\":20,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 17:01:32'),
(92,'info','queue','publish.started','{\"publish_id\":15,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 17:01:32'),
(93,'error','queue','job.failed','{\"job_id\":20,\"type\":\"publish_post\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-12 17:02:02\",\"hata\":\"Meta error (400) Missing or invalid image file | code=324 subcode=2069019 trace=A_GuJvaVOrTVg02qat59X7E\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 17:01:32'),
(94,'info','queue','job.reserved','{\"job_id\":20,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 17:02:02'),
(95,'info','queue','publish.started','{\"publish_id\":15,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 17:02:02'),
(96,'error','queue','job.failed','{\"job_id\":20,\"type\":\"publish_post\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-12 17:02:02\",\"hata\":\"Meta error (400) Missing or invalid image file | code=324 subcode=2069019 trace=Ae0RNNr2yARXF6O9-ggtWkY\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 17:02:03'),
(97,'info','queue','job.reserved','{\"job_id\":21,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 18:52:01'),
(98,'info','queue','publish.started','{\"publish_id\":16,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"none\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 18:52:01'),
(99,'info','queue','publish.succeeded','{\"publish_id\":16,\"remote_id\":\"930055826856053_122106714657191800\",\"permalink\":\"https:\\/\\/www.facebook.com\\/122106117441191800\\/posts\\/122106714657191800\",\"_event\":\"publish.succeeded\"}',3,NULL,NULL,'2026-01-12 18:52:04'),
(100,'info','queue','job.succeeded','{\"job_id\":21,\"type\":\"publish_post\",\"deneme\":1,\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.succeeded\"}',NULL,NULL,NULL,'2026-01-12 18:52:04'),
(101,'info','queue','job.reserved','{\"job_id\":22,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 19:04:00'),
(102,'info','queue','publish.started','{\"publish_id\":17,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 19:04:00'),
(103,'error','queue','job.failed','{\"job_id\":22,\"type\":\"publish_post\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-12 19:04:11\",\"hata\":\"Meta error (400) Only photo or video can be accepted as media type. | code=9004 subcode=2207052 trace=Ax2D0gMo6Ob6MLQlmGPbgsL\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 19:04:01'),
(104,'info','queue','job.reserved','{\"job_id\":22,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 19:04:11'),
(105,'info','queue','publish.started','{\"publish_id\":17,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 19:04:11'),
(106,'error','queue','job.failed','{\"job_id\":22,\"type\":\"publish_post\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-12 19:04:42\",\"hata\":\"Meta error (400) Only photo or video can be accepted as media type. | code=9004 subcode=2207052 trace=APXjAjdy6sesIa7OQU_yjvR\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 19:04:12'),
(107,'info','queue','job.reserved','{\"job_id\":22,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 19:04:42'),
(108,'info','queue','publish.started','{\"publish_id\":17,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 19:04:42'),
(109,'error','queue','job.failed','{\"job_id\":22,\"type\":\"publish_post\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-12 19:04:42\",\"hata\":\"Meta error (400) Only photo or video can be accepted as media type. | code=9004 subcode=2207052 trace=AVo0-U0p4ORVGaw1m6FC8Ta\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 19:04:43'),
(110,'info','queue','job.reserved','{\"job_id\":23,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 19:44:00'),
(111,'info','queue','publish.started','{\"publish_id\":18,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 19:44:00'),
(112,'error','queue','job.failed','{\"job_id\":23,\"type\":\"publish_post\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-12 19:44:11\",\"hata\":\"Meta error (400) Only photo or video can be accepted as media type. | code=9004 subcode=2207052 trace=ATtY1iD1a_plY6DCPjqEqz4\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 19:44:01'),
(113,'info','queue','job.reserved','{\"job_id\":24,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 19:44:01'),
(114,'info','queue','publish.started','{\"publish_id\":19,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 19:44:01'),
(115,'error','queue','job.failed','{\"job_id\":24,\"type\":\"publish_post\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-12 19:44:12\",\"hata\":\"Meta error (400) Missing or invalid image file | code=324 subcode=2069019 trace=AMEaxCG6kbLz4lOj9OM7_oN\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 19:44:02'),
(116,'info','queue','job.reserved','{\"job_id\":23,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 19:44:12'),
(117,'info','queue','publish.started','{\"publish_id\":18,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 19:44:12'),
(118,'error','queue','job.failed','{\"job_id\":23,\"type\":\"publish_post\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-12 19:44:43\",\"hata\":\"Meta error (400) Only photo or video can be accepted as media type. | code=9004 subcode=2207052 trace=A7BUlLg6lntmqzkU8R0y94n\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 19:44:13'),
(119,'info','queue','job.reserved','{\"job_id\":24,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 19:44:13'),
(120,'info','queue','publish.started','{\"publish_id\":19,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 19:44:13'),
(121,'error','queue','job.failed','{\"job_id\":24,\"type\":\"publish_post\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-12 19:44:43\",\"hata\":\"Meta error (400) Missing or invalid image file | code=324 subcode=2069019 trace=AJo---sKSMu8uV7FVSYXyIP\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 19:44:13'),
(122,'info','queue','job.reserved','{\"job_id\":23,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 19:44:43'),
(123,'info','queue','publish.started','{\"publish_id\":18,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 19:44:43'),
(124,'error','queue','job.failed','{\"job_id\":23,\"type\":\"publish_post\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-12 19:44:43\",\"hata\":\"Meta error (400) Only photo or video can be accepted as media type. | code=9004 subcode=2207052 trace=AObc2h2FLoWvojN0mOdPcYN\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 19:44:44'),
(125,'info','queue','job.reserved','{\"job_id\":24,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 19:44:44'),
(126,'info','queue','publish.started','{\"publish_id\":19,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 19:44:44'),
(127,'error','queue','job.failed','{\"job_id\":24,\"type\":\"publish_post\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-12 19:44:43\",\"hata\":\"Meta error (400) Missing or invalid image file | code=324 subcode=2069019 trace=AS-lP5sBnhCFzic281o1m6t\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 19:44:45'),
(128,'info','queue','job.reserved','{\"job_id\":25,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 19:50:01'),
(129,'info','queue','publish.started','{\"publish_id\":20,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 19:50:01'),
(130,'error','queue','job.failed','{\"job_id\":25,\"type\":\"publish_post\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-12 19:50:12\",\"hata\":\"Meta error (400) Only photo or video can be accepted as media type. | code=9004 subcode=2207052 trace=AD_2PBvQW345US2DcrfOtYT\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 19:50:02'),
(131,'info','queue','job.reserved','{\"job_id\":26,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 19:50:02'),
(132,'info','queue','publish.started','{\"publish_id\":21,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 19:50:02'),
(133,'error','queue','job.failed','{\"job_id\":26,\"type\":\"publish_post\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-12 19:50:12\",\"hata\":\"Meta error (400) Missing or invalid image file | code=324 subcode=2069019 trace=Am3KkNABuHcuzchZuEW7wCo\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 19:50:02'),
(134,'info','queue','job.reserved','{\"job_id\":25,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 19:50:12'),
(135,'info','queue','publish.started','{\"publish_id\":20,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 19:50:12'),
(136,'error','queue','job.failed','{\"job_id\":25,\"type\":\"publish_post\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-12 19:50:43\",\"hata\":\"Meta error (400) Only photo or video can be accepted as media type. | code=9004 subcode=2207052 trace=Afn8EoB6IiPNgVj4Q0Xw3NI\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 19:50:13'),
(137,'info','queue','job.reserved','{\"job_id\":26,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 19:50:13'),
(138,'info','queue','publish.started','{\"publish_id\":21,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 19:50:13'),
(139,'error','queue','job.failed','{\"job_id\":26,\"type\":\"publish_post\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-12 19:50:43\",\"hata\":\"Meta error (400) Missing or invalid image file | code=324 subcode=2069019 trace=A4MQWvyCrBZROmuzXi9hEqq\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 19:50:13'),
(140,'info','queue','job.reserved','{\"job_id\":25,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 19:50:43'),
(141,'info','queue','publish.started','{\"publish_id\":20,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 19:50:43'),
(142,'error','queue','job.failed','{\"job_id\":25,\"type\":\"publish_post\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-12 19:50:43\",\"hata\":\"Meta error (400) Only photo or video can be accepted as media type. | code=9004 subcode=2207052 trace=A7_q_tVWUiIyBWLejbfZ4Qb\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 19:50:44'),
(143,'info','queue','job.reserved','{\"job_id\":26,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 19:50:44'),
(144,'info','queue','publish.started','{\"publish_id\":21,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 19:50:44'),
(145,'error','queue','job.failed','{\"job_id\":26,\"type\":\"publish_post\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-12 19:50:43\",\"hata\":\"Meta error (400) Missing or invalid image file | code=324 subcode=2069019 trace=AB2HQWoF-r6aMVAsmWywa5G\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 19:50:44'),
(146,'info','queue','job.reserved','{\"job_id\":27,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 22:17:00'),
(147,'info','queue','publish.started','{\"publish_id\":22,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 22:17:00'),
(148,'error','queue','job.failed','{\"job_id\":27,\"type\":\"publish_post\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-12 22:17:11\",\"hata\":\"Meta error (400) Only photo or video can be accepted as media type. | code=9004 subcode=2207052 trace=AIQzWgHdx_Uc6cMLAwZ6PrR\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 22:17:01'),
(149,'info','queue','job.reserved','{\"job_id\":28,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 22:17:01'),
(150,'info','queue','publish.started','{\"publish_id\":23,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 22:17:01'),
(151,'error','queue','job.failed','{\"job_id\":28,\"type\":\"publish_post\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-12 22:17:12\",\"hata\":\"Meta error (400) Missing or invalid image file | code=324 subcode=2069019 trace=AW88Bmyxbm1X_f5cjySfT7x\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 22:17:02'),
(152,'info','queue','job.reserved','{\"job_id\":27,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 22:17:12'),
(153,'info','queue','publish.started','{\"publish_id\":22,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 22:17:12'),
(154,'error','queue','job.failed','{\"job_id\":27,\"type\":\"publish_post\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-12 22:17:42\",\"hata\":\"Meta error (400) Only photo or video can be accepted as media type. | code=9004 subcode=2207052 trace=AN7ev7M61XRAiTUrrjyy_Dn\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 22:17:12'),
(155,'info','queue','job.reserved','{\"job_id\":28,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 22:17:12'),
(156,'info','queue','publish.started','{\"publish_id\":23,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 22:17:12'),
(157,'error','queue','job.failed','{\"job_id\":28,\"type\":\"publish_post\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-12 22:17:43\",\"hata\":\"Meta error (400) Missing or invalid image file | code=324 subcode=2069019 trace=AjE0FM_jcCp9cegpMOY-6tW\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 22:17:13'),
(158,'info','queue','job.reserved','{\"job_id\":27,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 22:17:43'),
(159,'info','queue','publish.started','{\"publish_id\":22,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 22:17:43'),
(160,'error','queue','job.failed','{\"job_id\":27,\"type\":\"publish_post\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-12 22:17:42\",\"hata\":\"Meta error (400) Only photo or video can be accepted as media type. | code=9004 subcode=2207052 trace=AHEY8MyDR9aLpV5mf5n88W2\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 22:17:43'),
(161,'info','queue','job.reserved','{\"job_id\":28,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 22:17:43'),
(162,'info','queue','publish.started','{\"publish_id\":23,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 22:17:43'),
(163,'error','queue','job.failed','{\"job_id\":28,\"type\":\"publish_post\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-12 22:17:43\",\"hata\":\"Meta error (400) Missing or invalid image file | code=324 subcode=2069019 trace=AsZID28Hwyth_gCgpxUMgp7\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 22:17:44'),
(164,'info','queue','job.reserved','{\"job_id\":29,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 22:37:01'),
(165,'info','queue','publish.started','{\"publish_id\":24,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 22:37:01'),
(166,'error','queue','job.failed','{\"job_id\":29,\"type\":\"publish_post\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-12 22:37:12\",\"hata\":\"Meta error (400) Only photo or video can be accepted as media type. | code=9004 subcode=2207052 trace=APLNux2oZphil5hDYQzHos9\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 22:37:02'),
(167,'info','queue','job.reserved','{\"job_id\":30,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 22:37:02'),
(168,'info','queue','publish.started','{\"publish_id\":25,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 22:37:02'),
(169,'error','queue','job.failed','{\"job_id\":30,\"type\":\"publish_post\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-12 22:37:12\",\"hata\":\"Meta error (400) Missing or invalid image file | code=324 subcode=2069019 trace=AFSJoGMkr2eyTI0So0XOlEq\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 22:37:02'),
(170,'info','queue','job.reserved','{\"job_id\":29,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 22:37:12'),
(171,'info','queue','publish.started','{\"publish_id\":24,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 22:37:12'),
(172,'error','queue','job.failed','{\"job_id\":29,\"type\":\"publish_post\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-12 22:37:43\",\"hata\":\"Meta error (400) Only photo or video can be accepted as media type. | code=9004 subcode=2207052 trace=AbeE9ZjWsEGkW28U0Q9q-dh\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 22:37:13'),
(173,'info','queue','job.reserved','{\"job_id\":30,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 22:37:13'),
(174,'info','queue','publish.started','{\"publish_id\":25,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 22:37:13'),
(175,'error','queue','job.failed','{\"job_id\":30,\"type\":\"publish_post\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-12 22:37:44\",\"hata\":\"Meta error (400) Missing or invalid image file | code=324 subcode=2069019 trace=A9_7zLBff6J4GEtkq9DbwWi\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 22:37:14'),
(176,'info','queue','job.reserved','{\"job_id\":29,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 22:37:44'),
(177,'info','queue','publish.started','{\"publish_id\":24,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 22:37:44'),
(178,'error','queue','job.failed','{\"job_id\":29,\"type\":\"publish_post\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-12 22:37:43\",\"hata\":\"Meta error (400) Only photo or video can be accepted as media type. | code=9004 subcode=2207052 trace=AzQ4tuLORSzB4EP5kSc5poA\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 22:37:44'),
(179,'info','queue','job.reserved','{\"job_id\":30,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 22:37:44'),
(180,'info','queue','publish.started','{\"publish_id\":25,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 22:37:44'),
(181,'error','queue','job.failed','{\"job_id\":30,\"type\":\"publish_post\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-12 22:37:44\",\"hata\":\"Meta error (400) Missing or invalid image file | code=324 subcode=2069019 trace=AlPi93S81cHHmiCFJAHc_Hc\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 22:37:45'),
(182,'info','queue','job.reserved','{\"job_id\":29,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 22:40:09'),
(183,'info','queue','publish.started','{\"publish_id\":24,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 22:40:09'),
(184,'error','queue','job.failed','{\"job_id\":29,\"type\":\"publish_post\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-12 22:40:20\",\"hata\":\"Meta error (400) Only photo or video can be accepted as media type. | code=9004 subcode=2207052 trace=AIds6rsOSJkwOSWlUniSoPU\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 22:40:10'),
(185,'info','queue','job.reserved','{\"job_id\":29,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 22:40:20'),
(186,'info','queue','publish.started','{\"publish_id\":24,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 22:40:20'),
(187,'error','queue','job.failed','{\"job_id\":29,\"type\":\"publish_post\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-12 22:40:51\",\"hata\":\"Meta error (400) Only photo or video can be accepted as media type. | code=9004 subcode=2207052 trace=AqfXbjHqQdSQphrEMX5ENFa\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 22:40:21'),
(188,'info','queue','job.reserved','{\"job_id\":30,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 22:40:23'),
(189,'info','queue','publish.started','{\"publish_id\":25,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 22:40:23'),
(190,'error','queue','job.failed','{\"job_id\":30,\"type\":\"publish_post\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-12 22:40:33\",\"hata\":\"Meta error (400) Missing or invalid image file | code=324 subcode=2069019 trace=AcMHnfGqmXk4Zxtyh60OwqO\",\"worker\":\"ubuntu-32gb-nbg1-1:81004\",\"pid\":81004,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-12 22:40:23'),
(191,'info','queue','job.reserved','{\"job_id\":30,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:108047\",\"pid\":108047,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 22:40:33'),
(192,'info','queue','publish.started','{\"publish_id\":25,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 22:40:33'),
(193,'info','queue','publish.succeeded','{\"publish_id\":25,\"remote_id\":\"930055826856053_122106762195191800\",\"permalink\":\"https:\\/\\/www.facebook.com\\/122106117441191800\\/posts\\/122106762195191800\",\"_event\":\"publish.succeeded\"}',3,NULL,NULL,'2026-01-12 22:40:38'),
(194,'info','queue','job.succeeded','{\"job_id\":30,\"type\":\"publish_post\",\"deneme\":2,\"worker\":\"ubuntu-32gb-nbg1-1:108047\",\"pid\":108047,\"_event\":\"job.succeeded\"}',NULL,NULL,NULL,'2026-01-12 22:40:38'),
(195,'info','queue','job.reserved','{\"job_id\":29,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:108047\",\"pid\":108047,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 22:40:52'),
(196,'info','queue','publish.started','{\"publish_id\":24,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 22:40:52'),
(197,'info','queue','publish.succeeded','{\"publish_id\":24,\"remote_id\":\"18355953937202996\",\"creation_id\":\"17848136997653299\",\"permalink\":\"https:\\/\\/www.instagram.com\\/p\\/DTbDdRikeeI\\/\",\"_event\":\"publish.succeeded\"}',3,NULL,NULL,'2026-01-12 22:41:00'),
(198,'info','queue','job.succeeded','{\"job_id\":29,\"type\":\"publish_post\",\"deneme\":3,\"worker\":\"ubuntu-32gb-nbg1-1:108047\",\"pid\":108047,\"_event\":\"job.succeeded\"}',NULL,NULL,NULL,'2026-01-12 22:41:00'),
(199,'info','queue','job.reserved','{\"job_id\":28,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:108047\",\"pid\":108047,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 22:42:44'),
(200,'info','queue','publish.started','{\"publish_id\":23,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 22:42:44'),
(201,'info','queue','publish.succeeded','{\"publish_id\":23,\"remote_id\":\"930055826856053_122106763653191800\",\"permalink\":\"https:\\/\\/www.facebook.com\\/122106117441191800\\/posts\\/122106763653191800\",\"_event\":\"publish.succeeded\"}',3,NULL,NULL,'2026-01-12 22:42:49'),
(202,'info','queue','job.succeeded','{\"job_id\":28,\"type\":\"publish_post\",\"deneme\":4,\"worker\":\"ubuntu-32gb-nbg1-1:108047\",\"pid\":108047,\"_event\":\"job.succeeded\"}',NULL,NULL,NULL,'2026-01-12 22:42:49'),
(203,'info','queue','job.reserved','{\"job_id\":27,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:108047\",\"pid\":108047,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 22:42:49'),
(204,'info','queue','publish.started','{\"publish_id\":22,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 22:42:49'),
(205,'info','queue','publish.succeeded','{\"publish_id\":22,\"remote_id\":\"18090314351076924\",\"creation_id\":\"17848137117653299\",\"permalink\":\"https:\\/\\/www.instagram.com\\/p\\/DTbDrjVDFqO\\/\",\"_event\":\"publish.succeeded\"}',3,NULL,NULL,'2026-01-12 22:42:56'),
(206,'info','queue','job.succeeded','{\"job_id\":27,\"type\":\"publish_post\",\"deneme\":4,\"worker\":\"ubuntu-32gb-nbg1-1:108047\",\"pid\":108047,\"_event\":\"job.succeeded\"}',NULL,NULL,NULL,'2026-01-12 22:42:56'),
(207,'info','queue','job.reserved','{\"job_id\":26,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:108047\",\"pid\":108047,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 22:42:56'),
(208,'info','queue','publish.started','{\"publish_id\":21,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 22:42:56'),
(209,'info','queue','publish.succeeded','{\"publish_id\":21,\"remote_id\":\"930055826856053_122106763737191800\",\"permalink\":\"https:\\/\\/www.facebook.com\\/122106117441191800\\/posts\\/122106763737191800\",\"_event\":\"publish.succeeded\"}',3,NULL,NULL,'2026-01-12 22:43:00'),
(210,'info','queue','job.succeeded','{\"job_id\":26,\"type\":\"publish_post\",\"deneme\":4,\"worker\":\"ubuntu-32gb-nbg1-1:108047\",\"pid\":108047,\"_event\":\"job.succeeded\"}',NULL,NULL,NULL,'2026-01-12 22:43:00'),
(211,'info','queue','job.reserved','{\"job_id\":25,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:108047\",\"pid\":108047,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 22:43:00'),
(212,'info','queue','publish.started','{\"publish_id\":20,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 22:43:00'),
(213,'info','queue','publish.succeeded','{\"publish_id\":20,\"remote_id\":\"17845642362642159\",\"creation_id\":\"17848137141653299\",\"permalink\":\"https:\\/\\/www.instagram.com\\/p\\/DTbDs3jEfTR\\/\",\"_event\":\"publish.succeeded\"}',3,NULL,NULL,'2026-01-12 22:43:07'),
(214,'info','queue','job.succeeded','{\"job_id\":25,\"type\":\"publish_post\",\"deneme\":4,\"worker\":\"ubuntu-32gb-nbg1-1:108047\",\"pid\":108047,\"_event\":\"job.succeeded\"}',NULL,NULL,NULL,'2026-01-12 22:43:07'),
(215,'info','queue','job.reserved','{\"job_id\":31,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 22:57:00'),
(216,'info','queue','publish.started','{\"publish_id\":26,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 22:57:00'),
(217,'info','queue','job.reserved','{\"job_id\":32,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:108251\",\"pid\":108251,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 22:57:00'),
(218,'info','queue','publish.started','{\"publish_id\":27,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 22:57:00'),
(219,'info','queue','publish.succeeded','{\"publish_id\":27,\"remote_id\":\"930055826856053_122106767577191800\",\"permalink\":\"https:\\/\\/www.facebook.com\\/122106117441191800\\/posts\\/122106767577191800\",\"_event\":\"publish.succeeded\"}',3,NULL,NULL,'2026-01-12 22:57:05'),
(220,'info','queue','job.succeeded','{\"job_id\":32,\"type\":\"publish_post\",\"deneme\":1,\"worker\":\"ubuntu-32gb-nbg1-1:108251\",\"pid\":108251,\"_event\":\"job.succeeded\"}',NULL,NULL,NULL,'2026-01-12 22:57:05'),
(221,'info','queue','publish.succeeded','{\"publish_id\":26,\"remote_id\":\"18127372963524820\",\"creation_id\":\"17848138215653299\",\"permalink\":\"https:\\/\\/www.instagram.com\\/p\\/DTbFTdYj6nI\\/\",\"_event\":\"publish.succeeded\"}',3,NULL,NULL,'2026-01-12 22:57:09'),
(222,'info','queue','job.succeeded','{\"job_id\":31,\"type\":\"publish_post\",\"deneme\":1,\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.succeeded\"}',NULL,NULL,NULL,'2026-01-12 22:57:09'),
(223,'info','queue','job.reserved','{\"job_id\":33,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 23:11:00'),
(224,'info','queue','publish.started','{\"publish_id\":28,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"video\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 23:11:00'),
(225,'info','queue','job.reserved','{\"job_id\":34,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:108251\",\"pid\":108251,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-12 23:11:00'),
(226,'info','queue','publish.started','{\"publish_id\":29,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"video\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-12 23:11:00'),
(227,'info','queue','publish.deferred','{\"publish_id\":28,\"creation_id\":\"17848139073653299\",\"status_code\":\"IN_PROGRESS\",\"_event\":\"publish.deferred\"}',3,NULL,NULL,'2026-01-12 23:11:01'),
(228,'info','queue','job.succeeded','{\"job_id\":33,\"type\":\"publish_post\",\"deneme\":1,\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.succeeded\"}',NULL,NULL,NULL,'2026-01-12 23:11:01'),
(229,'info','queue','publish.succeeded','{\"publish_id\":29,\"remote_id\":\"886657437107492\",\"permalink\":\"\\/reel\\/886657437107492\\/\",\"_event\":\"publish.succeeded\"}',3,NULL,NULL,'2026-01-12 23:11:07'),
(230,'info','queue','job.succeeded','{\"job_id\":34,\"type\":\"publish_post\",\"deneme\":1,\"worker\":\"ubuntu-32gb-nbg1-1:108251\",\"pid\":108251,\"_event\":\"job.succeeded\"}',NULL,NULL,NULL,'2026-01-12 23:11:07'),
(231,'info','queue','job.reserved','{\"job_id\":35,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:118025\",\"pid\":118025,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-13 12:16:00'),
(232,'info','queue','publish.started','{\"publish_id\":30,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-13 12:16:00'),
(233,'info','queue','job.reserved','{\"job_id\":36,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-13 12:16:01'),
(234,'info','queue','publish.started','{\"publish_id\":31,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-13 12:16:01'),
(235,'info','queue','publish.succeeded','{\"publish_id\":31,\"remote_id\":\"930055826856053_122106869985191800\",\"permalink\":\"https:\\/\\/www.facebook.com\\/122106117441191800\\/posts\\/122106869985191800\",\"_event\":\"publish.succeeded\"}',3,NULL,NULL,'2026-01-13 12:16:05'),
(236,'info','queue','job.succeeded','{\"job_id\":36,\"type\":\"publish_post\",\"deneme\":1,\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.succeeded\"}',NULL,NULL,NULL,'2026-01-13 12:16:05'),
(237,'info','queue','publish.succeeded','{\"publish_id\":30,\"remote_id\":\"18002203511756029\",\"creation_id\":\"17848195743653299\",\"permalink\":\"https:\\/\\/www.instagram.com\\/p\\/DTcgvdRkTSE\\/\",\"_event\":\"publish.succeeded\"}',3,NULL,NULL,'2026-01-13 12:16:07'),
(238,'info','queue','job.succeeded','{\"job_id\":35,\"type\":\"publish_post\",\"deneme\":1,\"worker\":\"ubuntu-32gb-nbg1-1:118025\",\"pid\":118025,\"_event\":\"job.succeeded\"}',NULL,NULL,NULL,'2026-01-13 12:16:07'),
(239,'info','queue','job.reserved','{\"job_id\":37,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:156138\",\"pid\":156138,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-14 23:15:00'),
(240,'info','queue','publish.started','{\"publish_id\":32,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-14 23:15:00'),
(241,'info','queue','job.reserved','{\"job_id\":38,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-14 23:15:00'),
(242,'info','queue','publish.started','{\"publish_id\":33,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"image\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-14 23:15:00'),
(243,'info','queue','publish.succeeded','{\"publish_id\":33,\"remote_id\":\"930055826856053_122107198563191800\",\"permalink\":\"https:\\/\\/www.facebook.com\\/122106117441191800\\/posts\\/122107198563191800\",\"_event\":\"publish.succeeded\"}',3,NULL,NULL,'2026-01-14 23:15:05'),
(244,'info','queue','job.succeeded','{\"job_id\":38,\"type\":\"publish_post\",\"deneme\":1,\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.succeeded\"}',NULL,NULL,NULL,'2026-01-14 23:15:05'),
(245,'info','queue','publish.succeeded','{\"publish_id\":32,\"remote_id\":\"18072225083421224\",\"creation_id\":\"17848380210653299\",\"permalink\":\"https:\\/\\/www.instagram.com\\/p\\/DTgQ9KFDe8k\\/\",\"_event\":\"publish.succeeded\"}',3,NULL,NULL,'2026-01-14 23:15:09'),
(246,'info','queue','job.succeeded','{\"job_id\":37,\"type\":\"publish_post\",\"deneme\":1,\"worker\":\"ubuntu-32gb-nbg1-1:156138\",\"pid\":156138,\"_event\":\"job.succeeded\"}',NULL,NULL,NULL,'2026-01-14 23:15:09'),
(247,'info','queue','job.reserved','{\"job_id\":39,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:190950\",\"pid\":190950,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-15 22:40:00'),
(248,'error','queue','job.failed','{\"job_id\":39,\"type\":\"publish_post\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-15 22:40:10\",\"hata\":\"Desteklenmeyen platform: youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:190950\",\"pid\":190950,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-15 22:40:00'),
(249,'info','queue','job.reserved','{\"job_id\":39,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:190950\",\"pid\":190950,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-15 22:40:10'),
(250,'error','queue','job.failed','{\"job_id\":39,\"type\":\"publish_post\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-15 22:40:40\",\"hata\":\"Desteklenmeyen platform: youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:190950\",\"pid\":190950,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-15 22:40:10'),
(251,'info','queue','job.reserved','{\"job_id\":39,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:190950\",\"pid\":190950,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-15 22:40:40'),
(252,'error','queue','job.failed','{\"job_id\":39,\"type\":\"publish_post\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-15 22:40:40\",\"hata\":\"Desteklenmeyen platform: youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:190950\",\"pid\":190950,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-15 22:40:40'),
(253,'info','queue','job.reserved','{\"job_id\":40,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-15 23:04:00'),
(254,'info','queue','publish.started','{\"publish_id\":35,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"video\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-15 23:04:00'),
(255,'info','queue','publish.deferred','{\"publish_id\":35,\"creation_id\":\"17848503114653299\",\"status_code\":\"IN_PROGRESS\",\"_event\":\"publish.deferred\"}',3,NULL,NULL,'2026-01-15 23:04:01'),
(256,'info','queue','job.reserved','{\"job_id\":41,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:191816\",\"pid\":191816,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-15 23:04:01'),
(257,'info','queue','job.succeeded','{\"job_id\":40,\"type\":\"publish_post\",\"deneme\":1,\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.succeeded\"}',NULL,NULL,NULL,'2026-01-15 23:04:01'),
(258,'info','queue','job.reserved','{\"job_id\":42,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-15 23:04:01'),
(259,'info','queue','publish.started','{\"publish_id\":36,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"post_type\":\"video\",\"media_type\":\"video\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-15 23:04:01'),
(260,'error','queue','job.failed','{\"job_id\":42,\"type\":\"publish_post\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-15 23:04:11\",\"hata\":\"Desteklenmeyen platform: youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-15 23:04:01'),
(261,'info','queue','publish.succeeded','{\"publish_id\":36,\"remote_id\":\"907835215036827\",\"permalink\":\"\\/reel\\/907835215036827\\/\",\"_event\":\"publish.succeeded\"}',3,NULL,NULL,'2026-01-15 23:04:09'),
(262,'info','queue','job.succeeded','{\"job_id\":41,\"type\":\"publish_post\",\"deneme\":1,\"worker\":\"ubuntu-32gb-nbg1-1:191816\",\"pid\":191816,\"_event\":\"job.succeeded\"}',NULL,NULL,NULL,'2026-01-15 23:04:09'),
(263,'info','queue','job.reserved','{\"job_id\":42,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-15 23:04:11'),
(264,'error','queue','job.failed','{\"job_id\":42,\"type\":\"publish_post\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-15 23:04:41\",\"hata\":\"Desteklenmeyen platform: youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-15 23:04:11'),
(265,'info','queue','job.reserved','{\"job_id\":42,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-15 23:04:41'),
(266,'error','queue','job.failed','{\"job_id\":42,\"type\":\"publish_post\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-15 23:04:41\",\"hata\":\"Desteklenmeyen platform: youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-15 23:04:41'),
(267,'info','queue','job.reserved','{\"job_id\":43,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-15 23:50:01'),
(268,'info','queue','publish.started','{\"publish_id\":38,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"video\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-15 23:50:01'),
(269,'info','queue','job.reserved','{\"job_id\":44,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:192992\",\"pid\":192992,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-15 23:50:01'),
(270,'info','queue','publish.started','{\"publish_id\":39,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"post_type\":\"post\",\"media_type\":\"video\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-15 23:50:01'),
(271,'info','queue','publish.deferred','{\"publish_id\":38,\"creation_id\":\"17848506138653299\",\"status_code\":\"IN_PROGRESS\",\"_event\":\"publish.deferred\"}',3,NULL,NULL,'2026-01-15 23:50:02'),
(272,'info','queue','job.succeeded','{\"job_id\":43,\"type\":\"publish_post\",\"deneme\":1,\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.succeeded\"}',NULL,NULL,NULL,'2026-01-15 23:50:02'),
(273,'info','queue','job.reserved','{\"job_id\":45,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-15 23:50:02'),
(274,'error','queue','job.failed','{\"job_id\":45,\"type\":\"publish_youtube\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-15 23:50:12\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-15 23:50:02'),
(275,'info','queue','job.reserved','{\"job_id\":45,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-15 23:50:12'),
(276,'error','queue','job.failed','{\"job_id\":45,\"type\":\"publish_youtube\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-15 23:50:42\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-15 23:50:12'),
(277,'info','queue','publish.succeeded','{\"publish_id\":39,\"remote_id\":\"1641103093731591\",\"permalink\":\"\\/reel\\/1641103093731591\\/\",\"_event\":\"publish.succeeded\"}',3,NULL,NULL,'2026-01-15 23:50:20'),
(278,'info','queue','job.succeeded','{\"job_id\":44,\"type\":\"publish_post\",\"deneme\":1,\"worker\":\"ubuntu-32gb-nbg1-1:192992\",\"pid\":192992,\"_event\":\"job.succeeded\"}',NULL,NULL,NULL,'2026-01-15 23:50:20'),
(279,'info','queue','job.reserved','{\"job_id\":45,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:192992\",\"pid\":192992,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-15 23:50:42'),
(280,'info','queue','youtube.publish.started','{\"publish_id\":40,\"title\":\"test\",\"privacy\":\"public\",\"_event\":\"youtube.publish.started\"}',3,NULL,NULL,'2026-01-15 23:50:42'),
(281,'info','queue','youtube.publish.succeeded','{\"publish_id\":40,\"video_id\":\"xeEh_n2DIiI\",\"_event\":\"youtube.publish.succeeded\"}',3,NULL,NULL,'2026-01-15 23:50:44'),
(282,'info','queue','job.succeeded','{\"job_id\":45,\"type\":\"publish_youtube\",\"deneme\":3,\"worker\":\"ubuntu-32gb-nbg1-1:192992\",\"pid\":192992,\"_event\":\"job.succeeded\"}',NULL,NULL,NULL,'2026-01-15 23:50:44'),
(283,'info','queue','job.reserved','{\"job_id\":46,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-16 22:02:00'),
(284,'info','queue','publish.started','{\"publish_id\":41,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"post\",\"media_type\":\"video\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-16 22:02:00'),
(285,'info','queue','job.reserved','{\"job_id\":47,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:215344\",\"pid\":215344,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-16 22:02:00'),
(286,'info','queue','publish.started','{\"publish_id\":42,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"post_type\":\"post\",\"media_type\":\"video\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-16 22:02:00'),
(287,'info','queue','publish.deferred','{\"publish_id\":41,\"creation_id\":\"17848616430653299\",\"status_code\":\"IN_PROGRESS\",\"_event\":\"publish.deferred\"}',3,NULL,NULL,'2026-01-16 22:02:01'),
(288,'info','queue','job.succeeded','{\"job_id\":46,\"type\":\"publish_post\",\"deneme\":1,\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.succeeded\"}',NULL,NULL,NULL,'2026-01-16 22:02:01'),
(289,'info','queue','job.reserved','{\"job_id\":48,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-16 22:02:01'),
(290,'error','queue','job.failed','{\"job_id\":48,\"type\":\"publish_youtube\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-16 22:02:11\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-16 22:02:01'),
(291,'info','queue','job.reserved','{\"job_id\":48,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-16 22:02:11'),
(292,'error','queue','job.failed','{\"job_id\":48,\"type\":\"publish_youtube\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-16 22:02:41\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-16 22:02:11'),
(293,'info','queue','publish.succeeded','{\"publish_id\":42,\"remote_id\":\"911663567880645\",\"permalink\":\"\\/reel\\/911663567880645\\/\",\"_event\":\"publish.succeeded\"}',3,NULL,NULL,'2026-01-16 22:02:14'),
(294,'info','queue','job.succeeded','{\"job_id\":47,\"type\":\"publish_post\",\"deneme\":1,\"worker\":\"ubuntu-32gb-nbg1-1:215344\",\"pid\":215344,\"_event\":\"job.succeeded\"}',NULL,NULL,NULL,'2026-01-16 22:02:14'),
(295,'info','queue','job.reserved','{\"job_id\":48,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-16 22:02:41'),
(296,'error','queue','job.failed','{\"job_id\":48,\"type\":\"publish_youtube\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-16 22:02:41\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-16 22:02:41'),
(297,'info','queue','job.reserved','{\"job_id\":48,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:216080\",\"pid\":216080,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-16 22:27:01'),
(298,'error','queue','job.failed','{\"job_id\":48,\"type\":\"publish_youtube\",\"deneme\":4,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-16 22:27:01\",\"hata\":\"YT INIT failed HTTP=401 ERR= RESP=HTTP\\/2 401 \\r\\ncontent-type: application\\/json; charset=UTF-8\\r\\nx-guploader-uploadid: AJRbA5Ub3DiH1_QZ7Wqiv9gHj6K96Ul5OjbxL50zLJ2bjvs-mv7d7ETr4j-byeNlgdKo8PpdlHRTY8ylF4My-g2IEzlq-pl9vCBmhBpDnN35cKc\\r\\nwww-authenticate: Bearer realm=\\\"https:\\/\\/accounts.google.com\\/\\\", error=\\\"invalid_token\\\"\\r\\nvary: Origin\\r\\nvary: X-Origin\\r\\nvary: Referer\\r\\ncontent-length: 507\\r\\ndate: Fri, 16 Jan 2026 19:27:02 GMT\\r\\nserver: UploadServer\\r\\nalt-svc: h3=\\\":443\\\"; ma=2592000,h3-29=\\\":443\\\"; ma=2592000\\r\\n\\r\\n{\\n  \\\"error\\\": {\\n    \\\"code\\\": 401,\\n    \\\"message\\\": \\\"Request had invalid authentication credentials. Expected OAuth 2 access token, login cookie or other valid authentication credential. See https:\\/\\/developers.google.com\\/identity\\/sign-in\\/web\\/devconsole-project.\\\",\\n    \\\"errors\\\": [\\n      {\\n        \\\"message\\\": \\\"Invalid Credentials\\\",\\n        \\\"domain\\\": \\\"global\\\",\\n        \\\"reason\\\": \\\"authError\\\",\\n        \\\"location\\\": \\\"Authorization\\\",\\n        \\\"locationType\\\": \\\"header\\\"\\n      }\\n    ],\\n    \\\"status\\\": \\\"UNAUTHENTICATED\\\"\\n  }\\n}\\n\",\"worker\":\"ubuntu-32gb-nbg1-1:216080\",\"pid\":216080,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-16 22:27:02'),
(299,'info','queue','job.reserved','{\"job_id\":48,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:216080\",\"pid\":216080,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-16 22:27:08'),
(300,'error','queue','job.failed','{\"job_id\":48,\"type\":\"publish_youtube\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-16 22:27:18\",\"hata\":\"YT INIT failed HTTP=401 ERR= RESP=HTTP\\/2 401 \\r\\ncontent-type: application\\/json; charset=UTF-8\\r\\nx-guploader-uploadid: AJRbA5VEaHLckGi-ZlSGnGAg9JqY_y3SSqiIpSVVVDoIxfRZOxM1gjA4x1QNAnYkewHaDqx3EVrDqv4WD4oVMiKOQ5sQsmsNvUKzim88NEXwhKY\\r\\nwww-authenticate: Bearer realm=\\\"https:\\/\\/accounts.google.com\\/\\\", error=\\\"invalid_token\\\"\\r\\nvary: Origin\\r\\nvary: X-Origin\\r\\nvary: Referer\\r\\ncontent-length: 507\\r\\ndate: Fri, 16 Jan 2026 19:27:08 GMT\\r\\nserver: UploadServer\\r\\nalt-svc: h3=\\\":443\\\"; ma=2592000,h3-29=\\\":443\\\"; ma=2592000\\r\\n\\r\\n{\\n  \\\"error\\\": {\\n    \\\"code\\\": 401,\\n    \\\"message\\\": \\\"Request had invalid authentication credentials. Expected OAuth 2 access token, login cookie or other valid authentication credential. See https:\\/\\/developers.google.com\\/identity\\/sign-in\\/web\\/devconsole-project.\\\",\\n    \\\"errors\\\": [\\n      {\\n        \\\"message\\\": \\\"Invalid Credentials\\\",\\n        \\\"domain\\\": \\\"global\\\",\\n        \\\"reason\\\": \\\"authError\\\",\\n        \\\"location\\\": \\\"Authorization\\\",\\n        \\\"locationType\\\": \\\"header\\\"\\n      }\\n    ],\\n    \\\"status\\\": \\\"UNAUTHENTICATED\\\"\\n  }\\n}\\n\",\"worker\":\"ubuntu-32gb-nbg1-1:216080\",\"pid\":216080,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-16 22:27:08'),
(301,'info','queue','job.reserved','{\"job_id\":48,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:216080\",\"pid\":216080,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-16 22:27:18'),
(302,'error','queue','job.failed','{\"job_id\":48,\"type\":\"publish_youtube\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-16 22:27:48\",\"hata\":\"YT INIT failed HTTP=401 ERR= RESP=HTTP\\/2 401 \\r\\ncontent-type: application\\/json; charset=UTF-8\\r\\nx-guploader-uploadid: AJRbA5Wg8h2u2BT8hEaposmqPXCPymSxppN-L433ZDg2JK4nKQKUD-vVg09yooMdnxSJ9j8Ze61QrhyQMF5VUO81NxFTK5veYkMN3jC5XR2kel4\\r\\nwww-authenticate: Bearer realm=\\\"https:\\/\\/accounts.google.com\\/\\\", error=\\\"invalid_token\\\"\\r\\nvary: Origin\\r\\nvary: X-Origin\\r\\nvary: Referer\\r\\ncontent-length: 507\\r\\ndate: Fri, 16 Jan 2026 19:27:18 GMT\\r\\nserver: UploadServer\\r\\nalt-svc: h3=\\\":443\\\"; ma=2592000,h3-29=\\\":443\\\"; ma=2592000\\r\\n\\r\\n{\\n  \\\"error\\\": {\\n    \\\"code\\\": 401,\\n    \\\"message\\\": \\\"Request had invalid authentication credentials. Expected OAuth 2 access token, login cookie or other valid authentication credential. See https:\\/\\/developers.google.com\\/identity\\/sign-in\\/web\\/devconsole-project.\\\",\\n    \\\"errors\\\": [\\n      {\\n        \\\"message\\\": \\\"Invalid Credentials\\\",\\n        \\\"domain\\\": \\\"global\\\",\\n        \\\"reason\\\": \\\"authError\\\",\\n        \\\"location\\\": \\\"Authorization\\\",\\n        \\\"locationType\\\": \\\"header\\\"\\n      }\\n    ],\\n    \\\"status\\\": \\\"UNAUTHENTICATED\\\"\\n  }\\n}\\n\",\"worker\":\"ubuntu-32gb-nbg1-1:216080\",\"pid\":216080,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-16 22:27:18'),
(303,'info','queue','job.reserved','{\"job_id\":48,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:216080\",\"pid\":216080,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-16 22:27:48'),
(304,'error','queue','job.failed','{\"job_id\":48,\"type\":\"publish_youtube\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-16 22:27:48\",\"hata\":\"YT INIT failed HTTP=401 ERR= RESP=HTTP\\/2 401 \\r\\ncontent-type: application\\/json; charset=UTF-8\\r\\nx-guploader-uploadid: AJRbA5Vc6-eRYTvXe3E8XsquaakSQGjQoT9_0XeARf6V1MBojf_QxEjwWOiToP9T0a2G1EFao3sscoAnJM4czT2TVTE_0H5lEq5O6s30QsM3aQ\\r\\nwww-authenticate: Bearer realm=\\\"https:\\/\\/accounts.google.com\\/\\\", error=\\\"invalid_token\\\"\\r\\nvary: Origin\\r\\nvary: X-Origin\\r\\nvary: Referer\\r\\ncontent-length: 507\\r\\ndate: Fri, 16 Jan 2026 19:27:48 GMT\\r\\nserver: UploadServer\\r\\nalt-svc: h3=\\\":443\\\"; ma=2592000,h3-29=\\\":443\\\"; ma=2592000\\r\\n\\r\\n{\\n  \\\"error\\\": {\\n    \\\"code\\\": 401,\\n    \\\"message\\\": \\\"Request had invalid authentication credentials. Expected OAuth 2 access token, login cookie or other valid authentication credential. See https:\\/\\/developers.google.com\\/identity\\/sign-in\\/web\\/devconsole-project.\\\",\\n    \\\"errors\\\": [\\n      {\\n        \\\"message\\\": \\\"Invalid Credentials\\\",\\n        \\\"domain\\\": \\\"global\\\",\\n        \\\"reason\\\": \\\"authError\\\",\\n        \\\"location\\\": \\\"Authorization\\\",\\n        \\\"locationType\\\": \\\"header\\\"\\n      }\\n    ],\\n    \\\"status\\\": \\\"UNAUTHENTICATED\\\"\\n  }\\n}\\n\",\"worker\":\"ubuntu-32gb-nbg1-1:216080\",\"pid\":216080,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-16 22:27:48'),
(305,'info','queue','job.reserved','{\"job_id\":49,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:216080\",\"pid\":216080,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-16 22:32:00'),
(306,'info','queue','publish.started','{\"publish_id\":44,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"reels\",\"media_type\":\"video\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-16 22:32:00'),
(307,'info','queue','job.reserved','{\"job_id\":50,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-16 22:32:00'),
(308,'info','queue','publish.started','{\"publish_id\":45,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"video\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-16 22:32:00'),
(309,'info','queue','publish.deferred','{\"publish_id\":44,\"creation_id\":\"17848618941653299\",\"status_code\":\"IN_PROGRESS\",\"_event\":\"publish.deferred\"}',3,NULL,NULL,'2026-01-16 22:32:01'),
(310,'info','queue','job.succeeded','{\"job_id\":49,\"type\":\"publish_post\",\"deneme\":1,\"worker\":\"ubuntu-32gb-nbg1-1:216080\",\"pid\":216080,\"_event\":\"job.succeeded\"}',NULL,NULL,NULL,'2026-01-16 22:32:01'),
(311,'info','queue','job.reserved','{\"job_id\":51,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:216080\",\"pid\":216080,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-16 22:32:01'),
(312,'error','queue','job.failed','{\"job_id\":51,\"type\":\"publish_youtube\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-16 22:32:11\",\"hata\":\"YT INIT failed HTTP=401 ERR= RESP=HTTP\\/2 401 \\r\\ncontent-type: application\\/json; charset=UTF-8\\r\\nx-guploader-uploadid: AJRbA5VPzrRCqPIz-X4P-Jz-d16XAiGGBupJujVMcCL7q3PBaGxgUinaFHdDQVXcGmdGI1cvaCuPzi7w5_IG52CHv44volhGddKNLpYS9UxWyA\\r\\nwww-authenticate: Bearer realm=\\\"https:\\/\\/accounts.google.com\\/\\\", error=\\\"invalid_token\\\"\\r\\nvary: Origin\\r\\nvary: X-Origin\\r\\nvary: Referer\\r\\ncontent-length: 507\\r\\ndate: Fri, 16 Jan 2026 19:32:01 GMT\\r\\nserver: UploadServer\\r\\nalt-svc: h3=\\\":443\\\"; ma=2592000,h3-29=\\\":443\\\"; ma=2592000\\r\\n\\r\\n{\\n  \\\"error\\\": {\\n    \\\"code\\\": 401,\\n    \\\"message\\\": \\\"Request had invalid authentication credentials. Expected OAuth 2 access token, login cookie or other valid authentication credential. See https:\\/\\/developers.google.com\\/identity\\/sign-in\\/web\\/devconsole-project.\\\",\\n    \\\"errors\\\": [\\n      {\\n        \\\"message\\\": \\\"Invalid Credentials\\\",\\n        \\\"domain\\\": \\\"global\\\",\\n        \\\"reason\\\": \\\"authError\\\",\\n        \\\"location\\\": \\\"Authorization\\\",\\n        \\\"locationType\\\": \\\"header\\\"\\n      }\\n    ],\\n    \\\"status\\\": \\\"UNAUTHENTICATED\\\"\\n  }\\n}\\n\",\"worker\":\"ubuntu-32gb-nbg1-1:216080\",\"pid\":216080,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-16 22:32:01'),
(313,'info','queue','job.reserved','{\"job_id\":51,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:216080\",\"pid\":216080,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-16 22:32:11'),
(314,'error','queue','job.failed','{\"job_id\":51,\"type\":\"publish_youtube\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-16 22:32:42\",\"hata\":\"YT INIT failed HTTP=401 ERR= RESP=HTTP\\/2 401 \\r\\ncontent-type: application\\/json; charset=UTF-8\\r\\nx-guploader-uploadid: AJRbA5VrKgqEJMmV-6_yIc-Kkf3GMm6oKXvMnsoBoNqnkSwuLqpkX29WrgtUJllUG4d6N5TbCjR2vaL9vZ94e4PayE0tJI1JPUsw2tNc2520Vg\\r\\nwww-authenticate: Bearer realm=\\\"https:\\/\\/accounts.google.com\\/\\\", error=\\\"invalid_token\\\"\\r\\nvary: Origin\\r\\nvary: X-Origin\\r\\nvary: Referer\\r\\ncontent-length: 507\\r\\ndate: Fri, 16 Jan 2026 19:32:12 GMT\\r\\nserver: UploadServer\\r\\nalt-svc: h3=\\\":443\\\"; ma=2592000,h3-29=\\\":443\\\"; ma=2592000\\r\\n\\r\\n{\\n  \\\"error\\\": {\\n    \\\"code\\\": 401,\\n    \\\"message\\\": \\\"Request had invalid authentication credentials. Expected OAuth 2 access token, login cookie or other valid authentication credential. See https:\\/\\/developers.google.com\\/identity\\/sign-in\\/web\\/devconsole-project.\\\",\\n    \\\"errors\\\": [\\n      {\\n        \\\"message\\\": \\\"Invalid Credentials\\\",\\n        \\\"domain\\\": \\\"global\\\",\\n        \\\"reason\\\": \\\"authError\\\",\\n        \\\"location\\\": \\\"Authorization\\\",\\n        \\\"locationType\\\": \\\"header\\\"\\n      }\\n    ],\\n    \\\"status\\\": \\\"UNAUTHENTICATED\\\"\\n  }\\n}\\n\",\"worker\":\"ubuntu-32gb-nbg1-1:216080\",\"pid\":216080,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-16 22:32:12'),
(315,'info','queue','publish.succeeded','{\"publish_id\":45,\"remote_id\":\"1789393315056479\",\"permalink\":\"\\/reel\\/1789393315056479\\/\",\"_event\":\"publish.succeeded\"}',3,NULL,NULL,'2026-01-16 22:32:15'),
(316,'info','queue','job.succeeded','{\"job_id\":50,\"type\":\"publish_post\",\"deneme\":1,\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.succeeded\"}',NULL,NULL,NULL,'2026-01-16 22:32:15'),
(317,'info','queue','job.reserved','{\"job_id\":51,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:216080\",\"pid\":216080,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-16 22:32:42'),
(318,'error','queue','job.failed','{\"job_id\":51,\"type\":\"publish_youtube\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-16 22:32:42\",\"hata\":\"YT INIT failed HTTP=401 ERR= RESP=HTTP\\/2 401 \\r\\ncontent-type: application\\/json; charset=UTF-8\\r\\nx-guploader-uploadid: AJRbA5WN9lzS3D4AzmhT8K-WHmacjRegrwpUZYUBRAKNEieKNjcCTJkj9yP29jY_ayTtVo3QzzyqUfzd3uvCWgP0meCIS_uMAaUQ6crvhuGxaA\\r\\nwww-authenticate: Bearer realm=\\\"https:\\/\\/accounts.google.com\\/\\\", error=\\\"invalid_token\\\"\\r\\nvary: Origin\\r\\nvary: X-Origin\\r\\nvary: Referer\\r\\ncontent-length: 507\\r\\ndate: Fri, 16 Jan 2026 19:32:42 GMT\\r\\nserver: UploadServer\\r\\nalt-svc: h3=\\\":443\\\"; ma=2592000,h3-29=\\\":443\\\"; ma=2592000\\r\\n\\r\\n{\\n  \\\"error\\\": {\\n    \\\"code\\\": 401,\\n    \\\"message\\\": \\\"Request had invalid authentication credentials. Expected OAuth 2 access token, login cookie or other valid authentication credential. See https:\\/\\/developers.google.com\\/identity\\/sign-in\\/web\\/devconsole-project.\\\",\\n    \\\"errors\\\": [\\n      {\\n        \\\"message\\\": \\\"Invalid Credentials\\\",\\n        \\\"domain\\\": \\\"global\\\",\\n        \\\"reason\\\": \\\"authError\\\",\\n        \\\"location\\\": \\\"Authorization\\\",\\n        \\\"locationType\\\": \\\"header\\\"\\n      }\\n    ],\\n    \\\"status\\\": \\\"UNAUTHENTICATED\\\"\\n  }\\n}\\n\",\"worker\":\"ubuntu-32gb-nbg1-1:216080\",\"pid\":216080,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-16 22:32:42'),
(319,'info','queue','job.reserved','{\"job_id\":51,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:216080\",\"pid\":216080,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-16 22:46:10'),
(320,'error','queue','job.failed','{\"job_id\":51,\"type\":\"publish_youtube\",\"deneme\":4,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-16 22:46:10\",\"hata\":\"YT INIT failed HTTP=401 ERR= RESP=HTTP\\/2 401 \\r\\ncontent-type: application\\/json; charset=UTF-8\\r\\nx-guploader-uploadid: AJRbA5VAXAm2F1-iv1rbrMpTdkg9QJWksnl2tYuTB8QjuZEbPsD4NUocYEjAznLa1ry9ccLYfCajraV-2VEyR9d1lcx_Cges06trfju86DPn_g\\r\\nwww-authenticate: Bearer realm=\\\"https:\\/\\/accounts.google.com\\/\\\", error=\\\"invalid_token\\\"\\r\\nvary: Origin\\r\\nvary: X-Origin\\r\\nvary: Referer\\r\\ncontent-length: 507\\r\\ndate: Fri, 16 Jan 2026 19:46:10 GMT\\r\\nserver: UploadServer\\r\\nalt-svc: h3=\\\":443\\\"; ma=2592000,h3-29=\\\":443\\\"; ma=2592000\\r\\n\\r\\n{\\n  \\\"error\\\": {\\n    \\\"code\\\": 401,\\n    \\\"message\\\": \\\"Request had invalid authentication credentials. Expected OAuth 2 access token, login cookie or other valid authentication credential. See https:\\/\\/developers.google.com\\/identity\\/sign-in\\/web\\/devconsole-project.\\\",\\n    \\\"errors\\\": [\\n      {\\n        \\\"message\\\": \\\"Invalid Credentials\\\",\\n        \\\"domain\\\": \\\"global\\\",\\n        \\\"reason\\\": \\\"authError\\\",\\n        \\\"location\\\": \\\"Authorization\\\",\\n        \\\"locationType\\\": \\\"header\\\"\\n      }\\n    ],\\n    \\\"status\\\": \\\"UNAUTHENTICATED\\\"\\n  }\\n}\\n\",\"worker\":\"ubuntu-32gb-nbg1-1:216080\",\"pid\":216080,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-16 22:46:10'),
(321,'info','queue','job.reserved','{\"job_id\":51,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:216634\",\"pid\":216634,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-16 22:46:23'),
(322,'error','queue','job.failed','{\"job_id\":51,\"type\":\"publish_youtube\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-16 22:46:35\",\"hata\":\"YT UPLOAD failed HTTP=400 ERR=HTTP\\/2 stream 1 was not closed cleanly: PROTOCOL_ERROR (err 1) RESP=\",\"worker\":\"ubuntu-32gb-nbg1-1:216634\",\"pid\":216634,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-16 22:46:25'),
(323,'info','queue','job.reserved','{\"job_id\":51,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:216634\",\"pid\":216634,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-16 22:46:35'),
(324,'info','queue','job.reserved','{\"job_id\":51,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-16 22:46:35'),
(325,'error','queue','job.failed','{\"job_id\":51,\"type\":\"publish_youtube\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-16 22:47:05\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-16 22:46:35'),
(326,'error','queue','job.failed','{\"job_id\":51,\"type\":\"publish_youtube\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-16 22:47:06\",\"hata\":\"YT UPLOAD failed HTTP=400 ERR=HTTP\\/2 stream 1 was not closed cleanly: PROTOCOL_ERROR (err 1) RESP=\",\"worker\":\"ubuntu-32gb-nbg1-1:216634\",\"pid\":216634,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-16 22:46:36'),
(327,'info','queue','job.reserved','{\"job_id\":51,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:216634\",\"pid\":216634,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-16 22:47:06'),
(328,'error','queue','job.failed','{\"job_id\":51,\"type\":\"publish_youtube\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-16 22:47:06\",\"hata\":\"YT UPLOAD failed HTTP=400 ERR=HTTP\\/2 stream 1 was not closed cleanly: PROTOCOL_ERROR (err 1) RESP=\",\"worker\":\"ubuntu-32gb-nbg1-1:216634\",\"pid\":216634,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-16 22:47:07'),
(329,'info','queue','job.reserved','{\"job_id\":52,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:216634\",\"pid\":216634,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-16 22:51:00'),
(330,'error','queue','job.failed','{\"job_id\":52,\"type\":\"publish_youtube\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-16 22:51:11\",\"hata\":\"YT UPLOAD failed HTTP=400 ERR=HTTP\\/2 stream 1 was not closed cleanly: PROTOCOL_ERROR (err 1) RESP=\",\"worker\":\"ubuntu-32gb-nbg1-1:216634\",\"pid\":216634,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-16 22:51:01'),
(331,'info','queue','job.reserved','{\"job_id\":52,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:216634\",\"pid\":216634,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-16 22:51:11'),
(332,'error','queue','job.failed','{\"job_id\":52,\"type\":\"publish_youtube\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-16 22:51:42\",\"hata\":\"YT UPLOAD failed HTTP=400 ERR=HTTP\\/2 stream 1 was not closed cleanly: PROTOCOL_ERROR (err 1) RESP=\",\"worker\":\"ubuntu-32gb-nbg1-1:216634\",\"pid\":216634,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-16 22:51:12'),
(333,'info','queue','job.reserved','{\"job_id\":52,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:216634\",\"pid\":216634,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-16 22:51:42'),
(334,'error','queue','job.failed','{\"job_id\":52,\"type\":\"publish_youtube\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-16 22:51:42\",\"hata\":\"YT UPLOAD failed HTTP=400 ERR=HTTP\\/2 stream 1 was not closed cleanly: PROTOCOL_ERROR (err 1) RESP=\",\"worker\":\"ubuntu-32gb-nbg1-1:216634\",\"pid\":216634,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-16 22:51:43'),
(335,'info','queue','job.reserved','{\"job_id\":53,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:217764\",\"pid\":217764,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-16 23:37:00'),
(336,'error','queue','job.failed','{\"job_id\":53,\"type\":\"publish_youtube\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-16 23:43:12\",\"hata\":\"YT UPLOAD failed HTTP=408 ERR= RESP=<!DOCTYPE html>\\n<html lang=en>\\n  <meta charset=utf-8>\\n  <meta name=viewport content=\\\"initial-scale=1, minimum-scale=1, width=device-width\\\">\\n  <title>Error 408 (Request Timeout)!!1<\\/title>\\n  <style>\\n    *{margin:0;padding:0}html,code{font:15px\\/22px arial,sans-serif}html{background:#fff;color:#222;padding:15px}body{margin:7% auto 0;max-width:390px;min-height:180px;padding:30px 0 15px}* > body{background:url(\\/\\/www.google.com\\/images\\/errors\\/robot.png) 100% 5px no-repeat;padding-right:205px}p{margin:11px 0 22px;overflow:hidden}ins{color:#777;text-decoration:none}a img{border:0}@media screen and (max-width:772px){body{background:none;margin-top:0;max-width:none;padding-right:0}}#logo{background:url(\\/\\/www.google.com\\/images\\/branding\\/googlelogo\\/1x\\/googlelogo_color_150x54dp.png) no-repeat;margin-left:-5px}@media only screen and (min-resolution:192dpi){#logo{background:url(\\/\\/www.google.com\\/images\\/branding\\/googlelogo\\/2x\\/googlelogo_color_150x54dp.png) no-repeat 0% 0%\\/100% 100%;-moz-border-image:url(\\/\\/www.google.com\\/images\\/branding\\/googlelogo\\/2x\\/googlelogo_color_150x54dp.png) 0}}@media only screen and (-webkit-min-device-pixel-ratio:2){#logo{background:url(\\/\\/www.google.com\\/images\\/branding\\/googlelogo\\/2x\\/googlelogo_color_150x54dp.png) no-repeat;-webkit-background-size:100% 100%}}#logo{display:inline-block;height:54px;width:150px}\\n  <\\/style>\\n  <a href=\\/\\/www.google.com\\/><span id=logo aria-label=Google><\\/span><\\/a>\\n  <p><b>408.<\\/b> <ins>That’s an error.<\\/ins>\\n  <p>Your client has taken too long to issue its request.  <ins>That’s all we know.<\\/ins>\\n\",\"worker\":\"ubuntu-32gb-nbg1-1:217764\",\"pid\":217764,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-16 23:43:02'),
(337,'info','queue','job.reserved','{\"job_id\":53,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-16 23:43:12'),
(338,'error','queue','job.failed','{\"job_id\":53,\"type\":\"publish_youtube\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-16 23:43:42\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-16 23:43:12'),
(339,'info','queue','job.reserved','{\"job_id\":53,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:218042\",\"pid\":218042,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-16 23:43:23'),
(340,'info','queue','job.reserved','{\"job_id\":53,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-16 23:44:08'),
(341,'error','queue','job.failed','{\"job_id\":53,\"type\":\"publish_youtube\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-16 23:44:18\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-16 23:44:08'),
(342,'info','queue','job.reserved','{\"job_id\":53,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-16 23:44:14'),
(343,'error','queue','job.failed','{\"job_id\":53,\"type\":\"publish_youtube\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-16 23:44:44\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-16 23:44:14'),
(344,'info','queue','job.reserved','{\"job_id\":53,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-16 23:44:44'),
(345,'error','queue','job.failed','{\"job_id\":53,\"type\":\"publish_youtube\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-16 23:44:44\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-16 23:44:44'),
(346,'info','queue','job.reserved','{\"job_id\":54,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-16 23:46:00'),
(347,'error','queue','job.failed','{\"job_id\":54,\"type\":\"publish_youtube\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-16 23:46:10\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-16 23:46:00'),
(348,'info','queue','job.reserved','{\"job_id\":54,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-16 23:46:10'),
(349,'error','queue','job.failed','{\"job_id\":54,\"type\":\"publish_youtube\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-16 23:46:40\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-16 23:46:10'),
(350,'info','queue','job.reserved','{\"job_id\":54,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-16 23:46:40'),
(351,'error','queue','job.failed','{\"job_id\":54,\"type\":\"publish_youtube\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-16 23:46:40\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-16 23:46:40'),
(352,'error','queue','job.failed','{\"job_id\":53,\"type\":\"publish_youtube\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-16 23:43:23\",\"hata\":\"YT UPLOAD failed HTTP=408 ERR= RESP=<!DOCTYPE html>\\n<html lang=en>\\n  <meta charset=utf-8>\\n  <meta name=viewport content=\\\"initial-scale=1, minimum-scale=1, width=device-width\\\">\\n  <title>Error 408 (Request Timeout)!!1<\\/title>\\n  <style>\\n    *{margin:0;padding:0}html,code{font:15px\\/22px arial,sans-serif}html{background:#fff;color:#222;padding:15px}body{margin:7% auto 0;max-width:390px;min-height:180px;padding:30px 0 15px}* > body{background:url(\\/\\/www.google.com\\/images\\/errors\\/robot.png) 100% 5px no-repeat;padding-right:205px}p{margin:11px 0 22px;overflow:hidden}ins{color:#777;text-decoration:none}a img{border:0}@media screen and (max-width:772px){body{background:none;margin-top:0;max-width:none;padding-right:0}}#logo{background:url(\\/\\/www.google.com\\/images\\/branding\\/googlelogo\\/1x\\/googlelogo_color_150x54dp.png) no-repeat;margin-left:-5px}@media only screen and (min-resolution:192dpi){#logo{background:url(\\/\\/www.google.com\\/images\\/branding\\/googlelogo\\/2x\\/googlelogo_color_150x54dp.png) no-repeat 0% 0%\\/100% 100%;-moz-border-image:url(\\/\\/www.google.com\\/images\\/branding\\/googlelogo\\/2x\\/googlelogo_color_150x54dp.png) 0}}@media only screen and (-webkit-min-device-pixel-ratio:2){#logo{background:url(\\/\\/www.google.com\\/images\\/branding\\/googlelogo\\/2x\\/googlelogo_color_150x54dp.png) no-repeat;-webkit-background-size:100% 100%}}#logo{display:inline-block;height:54px;width:150px}\\n  <\\/style>\\n  <a href=\\/\\/www.google.com\\/><span id=logo aria-label=Google><\\/span><\\/a>\\n  <p><b>408.<\\/b> <ins>That’s an error.<\\/ins>\\n  <p>Your client has taken too long to issue its request.  <ins>That’s all we know.<\\/ins>\\n\",\"worker\":\"ubuntu-32gb-nbg1-1:218042\",\"pid\":218042,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-16 23:49:25'),
(353,'info','queue','job.reserved','{\"job_id\":54,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-16 23:54:46'),
(354,'error','queue','job.failed','{\"job_id\":54,\"type\":\"publish_youtube\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-16 23:54:56\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-16 23:54:46'),
(355,'info','queue','job.reserved','{\"job_id\":54,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-16 23:54:56'),
(356,'error','queue','job.failed','{\"job_id\":54,\"type\":\"publish_youtube\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-16 23:55:26\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-16 23:54:56'),
(357,'info','queue','job.reserved','{\"job_id\":54,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-16 23:55:26'),
(358,'error','queue','job.failed','{\"job_id\":54,\"type\":\"publish_youtube\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-16 23:55:26\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-16 23:55:26'),
(359,'info','queue','job.reserved','{\"job_id\":55,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 16:59:01'),
(360,'error','queue','job.failed','{\"job_id\":55,\"type\":\"publish_youtube\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-18 16:59:11\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 16:59:01'),
(361,'info','queue','job.reserved','{\"job_id\":55,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 16:59:11'),
(362,'error','queue','job.failed','{\"job_id\":55,\"type\":\"publish_youtube\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-18 16:59:41\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 16:59:11'),
(363,'info','queue','job.reserved','{\"job_id\":55,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 16:59:41'),
(364,'error','queue','job.failed','{\"job_id\":55,\"type\":\"publish_youtube\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-18 16:59:41\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 16:59:41'),
(365,'info','queue','job.reserved','{\"job_id\":55,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 17:00:13'),
(366,'error','queue','job.failed','{\"job_id\":55,\"type\":\"publish_youtube\",\"deneme\":4,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-18 17:00:12\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 17:00:13'),
(367,'info','queue','job.reserved','{\"job_id\":55,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 17:00:17'),
(368,'error','queue','job.failed','{\"job_id\":55,\"type\":\"publish_youtube\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-18 17:00:27\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 17:00:17'),
(369,'info','queue','job.reserved','{\"job_id\":55,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 17:00:23'),
(370,'error','queue','job.failed','{\"job_id\":55,\"type\":\"publish_youtube\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-18 17:00:33\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 17:00:23'),
(371,'info','queue','job.reserved','{\"job_id\":55,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 17:00:25'),
(372,'error','queue','job.failed','{\"job_id\":55,\"type\":\"publish_youtube\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-18 17:00:55\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 17:00:25'),
(373,'info','queue','job.reserved','{\"job_id\":55,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 17:00:55'),
(374,'error','queue','job.failed','{\"job_id\":55,\"type\":\"publish_youtube\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-18 17:00:55\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 17:00:55'),
(375,'info','queue','job.reserved','{\"job_id\":56,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 18:22:00'),
(376,'error','queue','job.failed','{\"job_id\":56,\"type\":\"publish_youtube\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-18 18:22:10\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 18:22:00'),
(377,'info','queue','job.reserved','{\"job_id\":56,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 18:22:10'),
(378,'error','queue','job.failed','{\"job_id\":56,\"type\":\"publish_youtube\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-18 18:22:40\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 18:22:10'),
(379,'info','queue','job.reserved','{\"job_id\":56,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 18:22:40'),
(380,'error','queue','job.failed','{\"job_id\":56,\"type\":\"publish_youtube\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-18 18:22:40\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 18:22:40'),
(381,'info','queue','job.reserved','{\"job_id\":57,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 19:20:00'),
(382,'error','queue','job.failed','{\"job_id\":57,\"type\":\"publish_youtube\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-18 19:20:10\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 19:20:00'),
(383,'info','queue','job.reserved','{\"job_id\":57,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 19:20:10'),
(384,'error','queue','job.failed','{\"job_id\":57,\"type\":\"publish_youtube\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-18 19:20:40\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 19:20:10'),
(385,'info','queue','job.reserved','{\"job_id\":57,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 19:20:40'),
(386,'error','queue','job.failed','{\"job_id\":57,\"type\":\"publish_youtube\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-18 19:20:40\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 19:20:40'),
(387,'info','queue','job.reserved','{\"job_id\":1,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:280960\",\"pid\":280960,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 22:03:01'),
(388,'error','queue','job.failed','{\"job_id\":1,\"type\":\"publish_youtube\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-18 22:03:11\",\"hata\":\"YT INIT failed HTTP=401 ERR=\",\"worker\":\"ubuntu-32gb-nbg1-1:280960\",\"pid\":280960,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 22:03:01'),
(389,'info','queue','job.reserved','{\"job_id\":1,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:280960\",\"pid\":280960,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 22:03:11'),
(390,'error','queue','job.failed','{\"job_id\":1,\"type\":\"publish_youtube\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-18 22:03:41\",\"hata\":\"YT INIT failed HTTP=401 ERR=\",\"worker\":\"ubuntu-32gb-nbg1-1:280960\",\"pid\":280960,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 22:03:11'),
(391,'info','queue','job.reserved','{\"job_id\":1,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 22:03:41'),
(392,'error','queue','job.failed','{\"job_id\":1,\"type\":\"publish_youtube\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-18 22:03:41\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 22:03:41'),
(393,'info','queue','job.reserved','{\"job_id\":2,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:281933\",\"pid\":281933,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 22:43:00'),
(394,'info','queue','publish.started','{\"publish_id\":2,\"platform\":\"instagram\",\"ig_user_id\":\"17841479598962980\",\"post_type\":\"reels\",\"media_type\":\"video\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-18 22:43:00'),
(395,'info','queue','job.reserved','{\"job_id\":3,\"type\":\"publish_post\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 22:43:00'),
(396,'info','queue','publish.started','{\"publish_id\":3,\"platform\":\"facebook\",\"page_id\":\"930055826856053\",\"media_type\":\"video\",\"_event\":\"publish.started\"}',3,NULL,NULL,'2026-01-18 22:43:00'),
(397,'info','queue','publish.deferred','{\"publish_id\":2,\"creation_id\":\"17848888905653299\",\"status_code\":\"IN_PROGRESS\",\"_event\":\"publish.deferred\"}',3,NULL,NULL,'2026-01-18 22:43:01'),
(398,'info','queue','job.succeeded','{\"job_id\":2,\"type\":\"publish_post\",\"deneme\":1,\"worker\":\"ubuntu-32gb-nbg1-1:281933\",\"pid\":281933,\"_event\":\"job.succeeded\"}',NULL,NULL,NULL,'2026-01-18 22:43:01'),
(399,'info','queue','job.reserved','{\"job_id\":4,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:281933\",\"pid\":281933,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 22:43:01'),
(400,'error','queue','job.failed','{\"job_id\":4,\"type\":\"publish_youtube\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-18 22:43:12\",\"hata\":\"YT INIT failed HTTP=401 ERR=\",\"worker\":\"ubuntu-32gb-nbg1-1:281933\",\"pid\":281933,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 22:43:02'),
(401,'info','queue','publish.succeeded','{\"publish_id\":3,\"remote_id\":\"1204586145141962\",\"permalink\":\"\\/reel\\/1204586145141962\\/\",\"_event\":\"publish.succeeded\"}',3,NULL,NULL,'2026-01-18 22:43:10'),
(402,'info','queue','job.succeeded','{\"job_id\":3,\"type\":\"publish_post\",\"deneme\":1,\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.succeeded\"}',NULL,NULL,NULL,'2026-01-18 22:43:10'),
(403,'info','queue','job.reserved','{\"job_id\":4,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:281933\",\"pid\":281933,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 22:43:12'),
(404,'error','queue','job.failed','{\"job_id\":4,\"type\":\"publish_youtube\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-18 22:43:42\",\"hata\":\"YT INIT failed HTTP=401 ERR=\",\"worker\":\"ubuntu-32gb-nbg1-1:281933\",\"pid\":281933,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 22:43:12'),
(405,'info','queue','job.reserved','{\"job_id\":4,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:281933\",\"pid\":281933,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 22:43:42'),
(406,'error','queue','job.failed','{\"job_id\":4,\"type\":\"publish_youtube\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-18 22:43:42\",\"hata\":\"YT INIT failed HTTP=401 ERR=\",\"worker\":\"ubuntu-32gb-nbg1-1:281933\",\"pid\":281933,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 22:43:42'),
(407,'info','queue','job.reserved','{\"job_id\":5,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:282423\",\"pid\":282423,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 23:02:00'),
(408,'info','queue','job.reserved','{\"job_id\":5,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 23:06:37'),
(409,'error','queue','job.failed','{\"job_id\":5,\"type\":\"publish_youtube\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-18 23:06:47\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 23:06:37'),
(410,'info','queue','job.reserved','{\"job_id\":5,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 23:06:47'),
(411,'error','queue','job.failed','{\"job_id\":5,\"type\":\"publish_youtube\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-18 23:07:17\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 23:06:47'),
(412,'info','queue','job.reserved','{\"job_id\":5,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 23:07:17'),
(413,'error','queue','job.failed','{\"job_id\":5,\"type\":\"publish_youtube\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-18 23:07:17\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 23:07:17'),
(414,'error','queue','job.failed','{\"job_id\":5,\"type\":\"publish_youtube\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-18 23:08:11\",\"hata\":\"YT UPLOAD failed HTTP=408 ERR=\",\"worker\":\"ubuntu-32gb-nbg1-1:282423\",\"pid\":282423,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 23:08:01'),
(415,'info','queue','job.reserved','{\"job_id\":5,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 23:08:09'),
(416,'error','queue','job.failed','{\"job_id\":5,\"type\":\"publish_youtube\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-18 23:08:19\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 23:08:09'),
(417,'info','queue','job.reserved','{\"job_id\":5,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 23:08:19'),
(418,'error','queue','job.failed','{\"job_id\":5,\"type\":\"publish_youtube\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-18 23:08:49\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 23:08:19'),
(419,'info','queue','job.reserved','{\"job_id\":5,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 23:08:49'),
(420,'error','queue','job.failed','{\"job_id\":5,\"type\":\"publish_youtube\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-18 23:08:49\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 23:08:49'),
(421,'info','queue','job.reserved','{\"job_id\":5,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 23:16:11'),
(422,'error','queue','job.failed','{\"job_id\":5,\"type\":\"publish_youtube\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-18 23:16:21\",\"hata\":\"No handler registered for job type: publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:108258\",\"pid\":108258,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 23:16:11'),
(423,'info','queue','job.reserved','{\"job_id\":5,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:282876\",\"pid\":282876,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 23:16:22'),
(424,'error','queue','job.failed','{\"job_id\":5,\"type\":\"publish_youtube\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-18 23:22:53\",\"hata\":\"YT UPLOAD failed HTTP=408 ERR=\",\"worker\":\"ubuntu-32gb-nbg1-1:282876\",\"pid\":282876,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 23:22:23'),
(425,'info','queue','job.reserved','{\"job_id\":4,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:282876\",\"pid\":282876,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 23:22:23'),
(426,'error','queue','job.failed','{\"job_id\":4,\"type\":\"publish_youtube\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-18 23:22:33\",\"hata\":\"YouTube social account bulunamadı (sa_id boş). Hesap bağlı mı?\",\"worker\":\"ubuntu-32gb-nbg1-1:282876\",\"pid\":282876,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 23:22:23'),
(427,'info','queue','job.reserved','{\"job_id\":4,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:282876\",\"pid\":282876,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 23:22:33'),
(428,'error','queue','job.failed','{\"job_id\":4,\"type\":\"publish_youtube\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-18 23:23:03\",\"hata\":\"YouTube social account bulunamadı (sa_id boş). Hesap bağlı mı?\",\"worker\":\"ubuntu-32gb-nbg1-1:282876\",\"pid\":282876,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 23:22:33'),
(429,'info','queue','job.reserved','{\"job_id\":5,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:282876\",\"pid\":282876,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 23:22:53'),
(430,'info','queue','job.reserved','{\"job_id\":4,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:283093\",\"pid\":283093,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 23:24:54'),
(431,'error','queue','job.failed','{\"job_id\":4,\"type\":\"publish_youtube\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-18 23:23:03\",\"hata\":\"YouTube social account bulunamadı (sa_id boş). Hesap bağlı mı?\",\"worker\":\"ubuntu-32gb-nbg1-1:283093\",\"pid\":283093,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 23:24:54'),
(432,'info','queue','job.reserved','{\"job_id\":5,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:283216\",\"pid\":283216,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 23:26:30'),
(433,'info','queue','job.reserved','{\"job_id\":4,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:283230\",\"pid\":283230,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 23:26:41'),
(434,'error','queue','job.failed','{\"job_id\":4,\"type\":\"publish_youtube\",\"deneme\":4,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-18 23:26:32\",\"hata\":\"YouTube social account bulunamadı (sa_id boş). Hesap bağlı mı?\",\"worker\":\"ubuntu-32gb-nbg1-1:283230\",\"pid\":283230,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 23:26:41'),
(435,'info','queue','job.reserved','{\"job_id\":5,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:283238\",\"pid\":283238,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 23:27:18'),
(436,'error','queue','job.failed','{\"job_id\":5,\"type\":\"publish_youtube\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-18 23:33:29\",\"hata\":\"YT UPLOAD failed HTTP=408 ERR=\",\"worker\":\"ubuntu-32gb-nbg1-1:283238\",\"pid\":283238,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 23:33:19'),
(437,'info','queue','job.reserved','{\"job_id\":7,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:283238\",\"pid\":283238,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 23:36:00'),
(438,'info','queue','job.reserved','{\"job_id\":6,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:283483\",\"pid\":283483,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 23:38:28'),
(439,'info','queue','job.reserved','{\"job_id\":5,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:283499\",\"pid\":283499,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 23:38:44'),
(440,'info','queue','job.reserved','{\"job_id\":4,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:283667\",\"pid\":283667,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 23:44:11'),
(441,'error','queue','job.failed','{\"job_id\":4,\"type\":\"publish_youtube\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-18 23:44:21\",\"hata\":\"YouTube social account bulunamadı (sa_id boş). Hesap bağlı mı?\",\"worker\":\"ubuntu-32gb-nbg1-1:283667\",\"pid\":283667,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 23:44:11'),
(442,'info','queue','job.reserved','{\"job_id\":4,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:283667\",\"pid\":283667,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 23:44:22'),
(443,'error','queue','job.failed','{\"job_id\":4,\"type\":\"publish_youtube\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-18 23:44:52\",\"hata\":\"YouTube social account bulunamadı (sa_id boş). Hesap bağlı mı?\",\"worker\":\"ubuntu-32gb-nbg1-1:283667\",\"pid\":283667,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 23:44:22'),
(444,'info','queue','job.reserved','{\"job_id\":7,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:283667\",\"pid\":283667,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 23:44:44'),
(445,'info','queue','job.reserved','{\"job_id\":4,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:283704\",\"pid\":283704,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 23:45:50'),
(446,'error','queue','job.failed','{\"job_id\":4,\"type\":\"publish_youtube\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-18 23:46:00\",\"hata\":\"YouTube social account bulunamadı (sa_id boş). Hesap bağlı mı?\",\"worker\":\"ubuntu-32gb-nbg1-1:283704\",\"pid\":283704,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 23:45:50'),
(447,'info','queue','job.reserved','{\"job_id\":8,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:283782\",\"pid\":283782,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 23:49:03'),
(448,'error','queue','job.failed','{\"job_id\":7,\"type\":\"publish_youtube\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-18 23:50:55\",\"hata\":\"YT UPLOAD failed HTTP=408 ERR=\",\"worker\":\"ubuntu-32gb-nbg1-1:283667\",\"pid\":283667,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 23:50:45'),
(449,'info','queue','job.reserved','{\"job_id\":8,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:283667\",\"pid\":283667,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 23:50:45'),
(450,'error','queue','job.failed','{\"job_id\":8,\"type\":\"publish_youtube\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-18 23:50:55\",\"hata\":\"YT INIT failed HTTP=400 ERR= RESP=HTTP\\/1.1 400 Bad Request\\r\\nContent-Type: application\\/json; charset=UTF-8\\r\\nX-GUploader-UploadID: AJRbA5WgICV-UE4s9EZKF6WBw_r8o3-BzMdu4Z9s3GdQvUpMV-2iZjmhpmQZYWAJRwPNHJH4C1tTA32_lgCJInLZADYoQJaQ70rVo78m3kmfcek\\r\\nVary: Origin\\r\\nVary: X-Origin\\r\\nVary: Referer\\r\\nContent-Length: 311\\r\\nDate: Sun, 18 Jan 2026 20:50:45 GMT\\r\\nServer: UploadServer\\r\\nAlt-Svc: h3=\\\":443\\\"; ma=2592000,h3-29=\\\":443\\\"; ma=2592000\\r\\n\\r\\n{\\n  \\\"error\\\": {\\n    \\\"code\\\": 400,\\n    \\\"message\\\": \\\"The user has exceeded the number of videos they may upload.\\\",\\n    \\\"errors\\\": [\\n      {\\n        \\\"message\\\": \\\"The user has exceeded the number of videos they may upload.\\\",\\n        \\\"domain\\\": \\\"youtube.video\\\",\\n        \\\"reason\\\": \\\"uploadLimitExceeded\\\"\\n      }\\n    ]\\n  }\\n}\\n\",\"worker\":\"ubuntu-32gb-nbg1-1:283667\",\"pid\":283667,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 23:50:45'),
(451,'info','queue','job.reserved','{\"job_id\":7,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:283667\",\"pid\":283667,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 23:50:55'),
(452,'error','queue','job.failed','{\"job_id\":7,\"type\":\"publish_youtube\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-18 23:51:26\",\"hata\":\"YT INIT failed HTTP=400 ERR= RESP=HTTP\\/1.1 400 Bad Request\\r\\nContent-Type: application\\/json; charset=UTF-8\\r\\nX-GUploader-UploadID: AJRbA5VdWgQLhOEUzuv6IRsqi2Vuz1TEw8EFxSy19SvcBaK_qn9anaxIgH7Xv-W5iMnO1pXXGyzLh4Z4Y6HR0dL2QDAD5061q4myfLkYqLA1JQ\\r\\nVary: Origin\\r\\nVary: X-Origin\\r\\nVary: Referer\\r\\nContent-Length: 311\\r\\nDate: Sun, 18 Jan 2026 20:50:56 GMT\\r\\nServer: UploadServer\\r\\nAlt-Svc: h3=\\\":443\\\"; ma=2592000,h3-29=\\\":443\\\"; ma=2592000\\r\\n\\r\\n{\\n  \\\"error\\\": {\\n    \\\"code\\\": 400,\\n    \\\"message\\\": \\\"The user has exceeded the number of videos they may upload.\\\",\\n    \\\"errors\\\": [\\n      {\\n        \\\"message\\\": \\\"The user has exceeded the number of videos they may upload.\\\",\\n        \\\"domain\\\": \\\"youtube.video\\\",\\n        \\\"reason\\\": \\\"uploadLimitExceeded\\\"\\n      }\\n    ]\\n  }\\n}\\n\",\"worker\":\"ubuntu-32gb-nbg1-1:283667\",\"pid\":283667,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 23:50:56'),
(453,'info','queue','job.reserved','{\"job_id\":8,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:283667\",\"pid\":283667,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 23:50:56'),
(454,'error','queue','job.failed','{\"job_id\":8,\"type\":\"publish_youtube\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-18 23:51:26\",\"hata\":\"YT INIT failed HTTP=400 ERR= RESP=HTTP\\/1.1 400 Bad Request\\r\\nContent-Type: application\\/json; charset=UTF-8\\r\\nX-GUploader-UploadID: AJRbA5W9zpy51ARiKrDbuz01D3DZNZJKO3dboFYs0xMYc0svFjpgpk73LNxPxqP-3BFY_rAeXkYt6DNEzptNn1JvV4vfBxG46q92WnZ6JgaF4A\\r\\nVary: Origin\\r\\nVary: X-Origin\\r\\nVary: Referer\\r\\nContent-Length: 311\\r\\nDate: Sun, 18 Jan 2026 20:50:56 GMT\\r\\nServer: UploadServer\\r\\nAlt-Svc: h3=\\\":443\\\"; ma=2592000,h3-29=\\\":443\\\"; ma=2592000\\r\\n\\r\\n{\\n  \\\"error\\\": {\\n    \\\"code\\\": 400,\\n    \\\"message\\\": \\\"The user has exceeded the number of videos they may upload.\\\",\\n    \\\"errors\\\": [\\n      {\\n        \\\"message\\\": \\\"The user has exceeded the number of videos they may upload.\\\",\\n        \\\"domain\\\": \\\"youtube.video\\\",\\n        \\\"reason\\\": \\\"uploadLimitExceeded\\\"\\n      }\\n    ]\\n  }\\n}\\n\",\"worker\":\"ubuntu-32gb-nbg1-1:283667\",\"pid\":283667,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 23:50:56'),
(455,'info','queue','job.reserved','{\"job_id\":7,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:283667\",\"pid\":283667,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 23:51:26'),
(456,'error','queue','job.failed','{\"job_id\":7,\"type\":\"publish_youtube\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-18 23:51:26\",\"hata\":\"YT INIT failed HTTP=400 ERR= RESP=HTTP\\/1.1 400 Bad Request\\r\\nContent-Type: application\\/json; charset=UTF-8\\r\\nX-GUploader-UploadID: AJRbA5XU1VglUhyEqTlkb_RSCnnE9mbD5vfiBPaEBV7lolYbbs_w6Rv0gkpav6AJtNkWEJUbG3Vw-SG81OiFrTP87Oed2tsFf3IJW9enrpvtiQ\\r\\nVary: Origin\\r\\nVary: X-Origin\\r\\nVary: Referer\\r\\nContent-Length: 311\\r\\nDate: Sun, 18 Jan 2026 20:51:27 GMT\\r\\nServer: UploadServer\\r\\nAlt-Svc: h3=\\\":443\\\"; ma=2592000,h3-29=\\\":443\\\"; ma=2592000\\r\\n\\r\\n{\\n  \\\"error\\\": {\\n    \\\"code\\\": 400,\\n    \\\"message\\\": \\\"The user has exceeded the number of videos they may upload.\\\",\\n    \\\"errors\\\": [\\n      {\\n        \\\"message\\\": \\\"The user has exceeded the number of videos they may upload.\\\",\\n        \\\"domain\\\": \\\"youtube.video\\\",\\n        \\\"reason\\\": \\\"uploadLimitExceeded\\\"\\n      }\\n    ]\\n  }\\n}\\n\",\"worker\":\"ubuntu-32gb-nbg1-1:283667\",\"pid\":283667,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 23:51:27'),
(457,'info','queue','job.reserved','{\"job_id\":8,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:283667\",\"pid\":283667,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-18 23:51:27'),
(458,'error','queue','job.failed','{\"job_id\":8,\"type\":\"publish_youtube\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-18 23:51:26\",\"hata\":\"YT INIT failed HTTP=400 ERR= RESP=HTTP\\/1.1 400 Bad Request\\r\\nContent-Type: application\\/json; charset=UTF-8\\r\\nX-GUploader-UploadID: AJRbA5Xf-RPNwgWtZDqPYWgnfJ-n1wntVbpJdDFhlCSPtc3fqhm4Y1hFIOEaLxEWX6j_YeaVphN2QryeMyNZ9-vZcBd6UJQALB5awvUCk4FAhP8\\r\\nVary: Origin\\r\\nVary: X-Origin\\r\\nVary: Referer\\r\\nContent-Length: 311\\r\\nDate: Sun, 18 Jan 2026 20:51:27 GMT\\r\\nServer: UploadServer\\r\\nAlt-Svc: h3=\\\":443\\\"; ma=2592000,h3-29=\\\":443\\\"; ma=2592000\\r\\n\\r\\n{\\n  \\\"error\\\": {\\n    \\\"code\\\": 400,\\n    \\\"message\\\": \\\"The user has exceeded the number of videos they may upload.\\\",\\n    \\\"errors\\\": [\\n      {\\n        \\\"message\\\": \\\"The user has exceeded the number of videos they may upload.\\\",\\n        \\\"domain\\\": \\\"youtube.video\\\",\\n        \\\"reason\\\": \\\"uploadLimitExceeded\\\"\\n      }\\n    ]\\n  }\\n}\\n\",\"worker\":\"ubuntu-32gb-nbg1-1:283667\",\"pid\":283667,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-18 23:51:27'),
(459,'info','queue','job.reserved','{\"job_id\":1,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:284146\",\"pid\":284146,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-19 00:15:00'),
(460,'error','queue','job.failed','{\"job_id\":1,\"type\":\"publish_youtube\",\"deneme\":1,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-19 00:21:11\",\"hata\":\"YT UPLOAD failed HTTP=408 ERR=\",\"worker\":\"ubuntu-32gb-nbg1-1:284146\",\"pid\":284146,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-19 00:21:01'),
(461,'info','queue','job.reserved','{\"job_id\":1,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:284146\",\"pid\":284146,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-19 00:21:11'),
(462,'info','queue','job.reserved','{\"job_id\":1,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:284829\",\"pid\":284829,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-19 00:21:52'),
(463,'info','queue','job.reserved','{\"job_id\":1,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:284977\",\"pid\":284977,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-19 00:27:07'),
(464,'error','queue','job.failed','{\"job_id\":1,\"type\":\"publish_youtube\",\"deneme\":2,\"max_deneme\":3,\"sonraki_durum\":\"queued\",\"sonraki_zaman\":\"2026-01-19 00:27:43\",\"hata\":\"YT UPLOAD failed HTTP=408 ERR=\",\"worker\":\"ubuntu-32gb-nbg1-1:284146\",\"pid\":284146,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-19 00:27:13'),
(465,'info','queue','job.reserved','{\"job_id\":1,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:284146\",\"pid\":284146,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-19 00:27:43'),
(466,'error','queue','job.failed','{\"job_id\":1,\"type\":\"publish_youtube\",\"deneme\":3,\"max_deneme\":3,\"sonraki_durum\":\"failed\",\"sonraki_zaman\":\"2026-01-19 00:27:43\",\"hata\":\"YT UPLOAD failed HTTP=408 ERR=\",\"worker\":\"ubuntu-32gb-nbg1-1:284146\",\"pid\":284146,\"_event\":\"job.failed\"}',NULL,NULL,NULL,'2026-01-19 00:33:44'),
(467,'info','queue','job.reserved','{\"job_id\":2,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:295866\",\"pid\":295866,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-19 09:41:00'),
(468,'info','queue','job.succeeded','{\"job_id\":2,\"type\":\"publish_youtube\",\"deneme\":1,\"worker\":\"ubuntu-32gb-nbg1-1:295866\",\"pid\":295866,\"_event\":\"job.succeeded\"}',NULL,NULL,NULL,'2026-01-19 09:41:03'),
(469,'info','queue','job.reserved','{\"job_id\":3,\"type\":\"publish_youtube\",\"worker\":\"ubuntu-32gb-nbg1-1:295866\",\"pid\":295866,\"_event\":\"job.reserved\"}',NULL,NULL,NULL,'2026-01-19 11:07:00'),
(470,'info','queue','job.succeeded','{\"job_id\":3,\"type\":\"publish_youtube\",\"deneme\":1,\"worker\":\"ubuntu-32gb-nbg1-1:295866\",\"pid\":295866,\"_event\":\"job.succeeded\"}',NULL,NULL,NULL,'2026-01-19 11:07:02');
/*!40000 ALTER TABLE `logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `media`
--

DROP TABLE IF EXISTS `media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `media` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `type` varchar(20) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `width` int(11) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `media_user_id_foreign` (`user_id`),
  CONSTRAINT `media_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `media`
--

LOCK TABLES `media` WRITE;
/*!40000 ALTER TABLE `media` DISABLE KEYS */;
/*!40000 ALTER TABLE `media` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `meta_media_jobs`
--

DROP TABLE IF EXISTS `meta_media_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `meta_media_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `publish_id` int(10) unsigned DEFAULT NULL,
  `social_account_id` int(10) unsigned NOT NULL,
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
  `attempts` int(10) unsigned NOT NULL DEFAULT 0,
  `next_retry_at` datetime DEFAULT NULL,
  `last_error` text DEFAULT NULL,
  `last_response_json` longtext DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_creation_id` (`creation_id`),
  KEY `idx_status_retry` (`status`,`next_retry_at`),
  KEY `idx_user` (`user_id`),
  KEY `idx_social_account` (`social_account_id`),
  KEY `idx_publish_id` (`publish_id`),
  KEY `idx_mmj_status_next` (`status`,`next_retry_at`),
  KEY `idx_mmj_creation_id` (`creation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `meta_media_jobs`
--

LOCK TABLES `meta_media_jobs` WRITE;
/*!40000 ALTER TABLE `meta_media_jobs` DISABLE KEYS */;
INSERT INTO `meta_media_jobs` VALUES
(1,3,28,31,'17841479598962980','930055826856053','17848139073653299','post','video','https://sosyalmedyaplanlama.com/uploads/2026/01/1768248546_ae54ea557fe65794a60c.mp4','video test','failed','IN_PROGRESS',NULL,NULL,0,'2026-01-12 23:11:21','meta:poll invalid row/token/ids','{\"create\":{\"id\":\"17848139073653299\"},\"status\":{\"status_code\":\"IN_PROGRESS\",\"id\":\"17848139073653299\"}}',NULL,'2026-01-12 23:11:01','2026-01-16 22:09:00'),
(2,3,35,33,'17841479598962980','930055826856053','17848503114653299','post','video','https://sosyalmedyaplanlama.com/uploads/2026/01/1768507338_037a993f6f4d98a0e9b7.mp4','testt video','failed','IN_PROGRESS',NULL,NULL,9,'2026-01-16 22:50:01','meta:poll invalid row/token/ids','{\"status_code\":\"ERROR\",\"id\":\"17848503114653299\"}',NULL,'2026-01-15 23:04:01','2026-01-16 22:50:01'),
(3,3,38,33,'17841479598962980','930055826856053','17848506138653299','post','video','https://sosyalmedyaplanlama.com/uploads/2026/01/1768510082_c515ddc8f7cacd194825.mp4','test','published','FINISHED','17934032829119956','2026-01-15 23:53:42',1,'2026-01-15 23:50:22',NULL,'{\"id\":\"17934032829119956\",\"_http_code\":200}',NULL,'2026-01-15 23:50:02','2026-01-15 23:53:42'),
(4,3,41,33,'17841479598962980','930055826856053','17848616430653299','post','video','https://sosyalmedyaplanlama.com/uploads/2026/01/1768590017_0e41d9f885b04f34d9a6.mp4','test facebook-instagram-youtube','','PUBLISHED','17846252145667989',NULL,1,NULL,NULL,'{\"status\":{\"status_code\":\"FINISHED\",\"id\":\"17848616430653299\"},\"publish\":{\"id\":\"17846252145667989\"},\"perma\":{\"permalink\":\"https://www.instagram.com/reel/DTlSMe5j4ZX/\",\"id\":\"17846252145667989\"}}',NULL,'2026-01-16 22:02:01','2026-01-16 22:09:09'),
(5,3,44,33,'17841479598962980','930055826856053','17848618941653299','reels','video','https://sosyalmedyaplanlama.com/uploads/2026/01/1768591824_7549e3b6c8111ce99b5e.mp4','test 22:29','','PUBLISHED','18139737052465713',NULL,1,NULL,NULL,'{\"status\":{\"status_code\":\"FINISHED\",\"id\":\"17848618941653299\"},\"publish\":{\"id\":\"18139737052465713\"},\"perma\":{\"permalink\":\"https://www.instagram.com/reel/DTlVoRFFIDE/\",\"id\":\"18139737052465713\"}}',NULL,'2026-01-16 22:32:01','2026-01-16 22:33:09'),
(6,3,2,44,'17841479598962980','930055826856053','17848888905653299','reels','video','https://sosyalmedyaplanlama.com/uploads/2026/01/1768765303_530f9090a93474431628.mp4','test','','PUBLISHED','18436131283110201',NULL,1,NULL,NULL,'{\"status\":{\"status_code\":\"FINISHED\",\"id\":\"17848888905653299\"},\"publish\":{\"id\":\"18436131283110201\"},\"perma\":{\"permalink\":\"https://www.instagram.com/reel/DTqgebLCvcn/\",\"id\":\"18436131283110201\"}}',NULL,'2026-01-18 22:43:01','2026-01-18 22:44:10');
/*!40000 ALTER TABLE `meta_media_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `meta_tokens`
--

DROP TABLE IF EXISTS `meta_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `meta_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `access_token` text NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  `consent_accepted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `meta_tokens`
--

LOCK TABLES `meta_tokens` WRITE;
/*!40000 ALTER TABLE `meta_tokens` DISABLE KEYS */;
INSERT INTO `meta_tokens` VALUES
(21,3,'EAAZASuqVoDHwBQaSBegZBfP6wkFZBhJL1XuzsgdpUpCtNLnpoaTfTifpi05qRzaG5GqVn9iByNhyaZCWPk6JlR0kqHhCTtXAT2S1bXZB5yZAjxjKTxPdJ1e97iljwOBv1ncC5Cl45tI833pUU35J5cIDUcxgNOEYT3PjArrZCoFZALTZCW4lXtpIN85og2ZAJG',NULL,'2026-01-18 22:00:03','2026-01-18 21:59:40','2026-01-18 22:00:07');
/*!40000 ALTER TABLE `meta_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES
(1,'2025-12-08-103443','App\\Database\\Migrations\\CreateUsersTable','default','App',1765190297,1),
(2,'2025-12-08-103537','App\\Database\\Migrations\\CreateMediaAndTemplatesTables','default','App',1765190297,1),
(3,'2025-12-08-103611','App\\Database\\Migrations\\CreateContentsTables','default','App',1765190297,1),
(4,'2025-12-08-103624','App\\Database\\Migrations\\CreateSocialAccountsTables','default','App',1765190297,1),
(5,'2025-12-08-103649','App\\Database\\Migrations\\CreateScheduledPostsAndLogsTables','default','App',1765190297,1),
(6,'2025-12-13-201444','App\\Database\\Migrations\\AddStatusToUsers','default','App',1765656941,2),
(8,'2025-12-14-102725','App\\Database\\Migrations\\CreateLogsTable','default','App',1765708265,3);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `publishes`
--

DROP TABLE IF EXISTS `publishes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `publishes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int(10) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `platform` varchar(32) NOT NULL,
  `content_kind` varchar(16) DEFAULT NULL,
  `media_kind` varchar(16) DEFAULT NULL,
  `account_id` int(10) unsigned NOT NULL,
  `content_id` int(10) unsigned NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'queued',
  `schedule_at` datetime DEFAULT NULL,
  `idempotency_key` varchar(80) DEFAULT NULL,
  `remote_id` varchar(120) DEFAULT NULL,
  `published_at` datetime DEFAULT NULL,
  `error` varchar(255) DEFAULT NULL,
  `meta_json` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_publishes_user_idem` (`user_id`,`idempotency_key`),
  KEY `idx_publishes_job_id` (`job_id`),
  KEY `idx_publishes_user_id` (`user_id`),
  KEY `idx_publishes_platform_account` (`platform`,`account_id`),
  KEY `idx_publishes_status_schedule` (`status`,`schedule_at`),
  KEY `idx_publishes_created_at` (`created_at`),
  KEY `idx_publishes_idempotency` (`idempotency_key`,`platform`,`account_id`),
  KEY `idx_publishes_user_schedule` (`user_id`,`schedule_at`),
  KEY `idx_publishes_user_status` (`user_id`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `publishes`
--

LOCK TABLES `publishes` WRITE;
/*!40000 ALTER TABLE `publishes` DISABLE KEYS */;
INSERT INTO `publishes` VALUES
(1,1,4,'youtube',NULL,NULL,48,1,'failed','2026-01-19 00:15:00','f1581f8cd352198e32dc2a12b734142344d4e09c9e5704d5b6a7c093749c9351',NULL,NULL,'YT UPLOAD failed HTTP=408 ERR=',NULL,'2026-01-19 00:13:40','2026-01-19 00:33:44'),
(2,2,4,'youtube',NULL,NULL,49,2,'published','2026-01-19 09:41:00','68792840030363a69259de1807a451d859a644f488d801b831f302e63af6ff2a','SnkNpQH6FUI','2026-01-19 09:41:03',NULL,'{\"meta\":{\"published_id\":\"SnkNpQH6FUI\",\"permalink\":\"https://youtu.be/SnkNpQH6FUI\",\"privacy\":\"public\"}}','2026-01-19 09:38:56','2026-01-19 09:41:03'),
(3,3,4,'youtube',NULL,NULL,49,3,'published','2026-01-19 11:07:00','bb6269a1817493db1110432bd171cade87379d09fc498c5bfc57ce8890fbaad8','LMSpgpT0RWY','2026-01-19 11:07:02',NULL,'{\"meta\":{\"published_id\":\"LMSpgpT0RWY\",\"permalink\":\"https://youtu.be/LMSpgpT0RWY\",\"privacy\":\"private\"}}','2026-01-19 11:05:42','2026-01-19 11:07:02');
/*!40000 ALTER TABLE `publishes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scheduled_posts`
--

DROP TABLE IF EXISTS `scheduled_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `scheduled_posts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `publish_id` bigint(20) unsigned DEFAULT NULL,
  `content_id` bigint(20) unsigned NOT NULL,
  `social_account_id` int(11) unsigned NOT NULL,
  `platform` varchar(50) NOT NULL,
  `scheduled_at` datetime NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'scheduled',
  `publish_response` text DEFAULT NULL,
  `retry_count` int(11) NOT NULL DEFAULT 0,
  `last_attempt_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `scheduled_posts_content_id_foreign` (`content_id`),
  KEY `scheduled_posts_social_account_id_foreign` (`social_account_id`),
  KEY `idx_publish_id` (`publish_id`),
  CONSTRAINT `scheduled_posts_content_id_foreign` FOREIGN KEY (`content_id`) REFERENCES `contents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `scheduled_posts_social_account_id_foreign` FOREIGN KEY (`social_account_id`) REFERENCES `social_accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scheduled_posts`
--

LOCK TABLES `scheduled_posts` WRITE;
/*!40000 ALTER TABLE `scheduled_posts` DISABLE KEYS */;
/*!40000 ALTER TABLE `scheduled_posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `social_account_tokens`
--

DROP TABLE IF EXISTS `social_account_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `social_account_tokens` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `social_account_id` int(10) unsigned NOT NULL,
  `provider` varchar(32) NOT NULL,
  `access_token` text NOT NULL,
  `refresh_token` text DEFAULT NULL,
  `token_type` varchar(20) DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `scope` text DEFAULT NULL,
  `meta_json` longtext DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tokens_account_provider` (`social_account_id`,`provider`),
  KEY `idx_tokens_expires` (`expires_at`),
  CONSTRAINT `fk_tokens_account` FOREIGN KEY (`social_account_id`) REFERENCES `social_accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `social_account_tokens`
--

LOCK TABLES `social_account_tokens` WRITE;
/*!40000 ALTER TABLE `social_account_tokens` DISABLE KEYS */;
INSERT INTO `social_account_tokens` VALUES
(40,44,'meta','EAAZASuqVoDHwBQYXM9saJjmGfnbCsJcf5wnM9dZBvadAlHGRZByoFg1kDtFBd8XUzZAw6dyOYH2Wqn1jWjonWNzJyzfb7TsimNRqXVyDRZBwCzcNC2nbK4R5ZCIMb1VNwHcStL5wRUtZCfy09eWi3ZC69ECHKHDglgLBLsWVpWeCtZCMy2KY9j2EBu05Gw95lQbmNVLqJblpB',NULL,'page',NULL,NULL,'{\"page_id\":\"930055826856053\",\"page_name\":\"Sosyalmedyaplanla.com\"}','2026-01-18 22:00:07','2026-01-18 22:00:07'),
(41,45,'meta','EAAZASuqVoDHwBQYXM9saJjmGfnbCsJcf5wnM9dZBvadAlHGRZByoFg1kDtFBd8XUzZAw6dyOYH2Wqn1jWjonWNzJyzfb7TsimNRqXVyDRZBwCzcNC2nbK4R5ZCIMb1VNwHcStL5wRUtZCfy09eWi3ZC69ECHKHDglgLBLsWVpWeCtZCMy2KY9j2EBu05Gw95lQbmNVLqJblpB',NULL,'page',NULL,NULL,'{\"page_id\":\"930055826856053\",\"page_name\":\"Sosyalmedyaplanla.com\"}','2026-01-18 22:00:07','2026-01-18 22:00:07'),
(45,49,'google','ymAzfryce4kK/PKOyqBZprsEqqaJ2JxUgvDWqiEndh2cELxj//3CskYBn1gCD3zp5HVzlwbZch0f/1m9OYQUXlI3D19/Nb0+zg1DwbbiUlSMhYsBZnU6GRRLTWtb1yxKdAkL6QWpUXKcvBSVxW7OKAAj1+jDNmP+7434WsD4sM2jCptHWZ6VE1detcCc6kT6BF1MuR+ImZ/SX4qQoQE2FER/o76QcbwKRYB0nl3yoSzHM+gxXjls1PzNGE0Wcz9RgavNDMsIiH16AfWLTuxge3G/ePK1IugSTWhDecj0Y/PF9/Phl308R6ZdSEiV9btVuSYAJM7qmSEvX+QtB64mkJfBuRuI3zuUhyEvlfwT2B0RZRIpQQFnsV10ftiPazMZQ08TIyqh8+HjZ2YIZJgknE96LqPfRA5gia4pOg2QxoJb4836Jbh1lDxfQKp/','Dv++GMVlh0GmHtgopxQyu3HQevuPZQYm7e0laEkoWD2iZsa3zXUUtw/074vwl28dTGy03g8LEmPOrVWk4ig/XhbcpB7kph5XUFU/BDMU2gMNChok7ryjWAwA3Wsge1/oSva5mmb33ULFQ7XLqfwmyqxYJc3JLSR/tyc1nZi9P/WImg9wugm5LpU+i7wPgerNQdMabLZXv9ar15c4mjfk3E8SVJwSM7njyxol2gX80eg3g5bYYKgg','Bearer','2026-01-19 12:06:59','https://www.googleapis.com/auth/youtube.readonly https://www.googleapis.com/auth/youtube.upload','{\"channel_id\":\"UCnaI1su-kcHbbPtEoZCoXhA\",\"obtained_at\":\"2026-01-19 09:37:41\"}','2026-01-19 09:37:41','2026-01-19 11:07:00');
/*!40000 ALTER TABLE `social_account_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `social_accounts`
--

DROP TABLE IF EXISTS `social_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `social_accounts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `platform` varchar(50) NOT NULL,
  `external_id` varchar(191) NOT NULL,
  `meta_page_id` varchar(64) DEFAULT NULL,
  `access_token` text DEFAULT NULL,
  `token_expires_at` datetime DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `avatar_url` varchar(500) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `social_accounts_user_id_foreign` (`user_id`),
  CONSTRAINT `social_accounts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `social_accounts`
--

LOCK TABLES `social_accounts` WRITE;
/*!40000 ALTER TABLE `social_accounts` DISABLE KEYS */;
INSERT INTO `social_accounts` VALUES
(44,3,'instagram','17841479598962980','930055826856053',NULL,NULL,'Mehmet Çokyiğit','sosyalmedyaplanla','https://scontent-fra3-2.xx.fbcdn.net/v/t51.82787-15/609654187_17846189679653299_3340274568125121732_n.jpg?_nc_cat=111&ccb=1-7&_nc_sid=7d201b&_nc_ohc=OoKIflAws3wQ7kNvwHdB_UO&_nc_oc=Adkfvj2BIf_KgoFTcftqa9rxs4LWyNSQ7r9scCjk4VB0coefqTF0WOzzkn5O5nNKxDne5E4SmwExKbpfNwIeFTfG&_nc_zt=23&_nc_ht=scontent-fra3-2.xx&edm=AL-3X8kEAAAA&_nc_gid=Rj5M4e4nBtCKSHeapyoUcA&oh=00_AfoEe7HJejYWm77IeKJRMIpMWJ8thB-jn6XpGTrqiTMrTA&oe=697303B6','2026-01-18 22:00:07','2026-01-18 22:00:07'),
(45,3,'facebook','930055826856053','930055826856053',NULL,NULL,'Sosyalmedyaplanla.com',NULL,NULL,'2026-01-18 22:00:07','2026-01-18 22:00:07'),
(49,4,'youtube','UCnaI1su-kcHbbPtEoZCoXhA',NULL,NULL,NULL,'Burkay Döner','@burkaydoner','https://yt3.ggpht.com/ytc/AIdro_k6E7UTsF655DeERRis1BYvl8uSsF973x_Fdovm3dYObcg6BnyGOi5TiYl_QS1VWQCHTQ=s88-c-k-c0x00ffffff-no-rj','2026-01-19 09:37:41','2026-01-19 09:37:41');
/*!40000 ALTER TABLE `social_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `social_tokens`
--

DROP TABLE IF EXISTS `social_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `social_tokens` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `social_account_id` int(11) unsigned NOT NULL,
  `access_token` text NOT NULL,
  `refresh_token` text DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `scopes` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `social_tokens_social_account_id_foreign` (`social_account_id`),
  CONSTRAINT `social_tokens_social_account_id_foreign` FOREIGN KEY (`social_account_id`) REFERENCES `social_accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `social_tokens`
--

LOCK TABLES `social_tokens` WRITE;
/*!40000 ALTER TABLE `social_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `social_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `templates`
--

DROP TABLE IF EXISTS `templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `templates` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `platform_type` varchar(50) DEFAULT NULL,
  `base_media_id` bigint(20) unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `templates_base_media_id_foreign` (`base_media_id`),
  CONSTRAINT `templates_base_media_id_foreign` FOREIGN KEY (`base_media_id`) REFERENCES `media` (`id`) ON DELETE CASCADE ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `templates`
--

LOCK TABLES `templates` WRITE;
/*!40000 ALTER TABLE `templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_consents`
--

DROP TABLE IF EXISTS `user_consents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_consents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `consent_key` varchar(64) NOT NULL,
  `consent_version` varchar(16) NOT NULL,
  `accepted_at` datetime NOT NULL,
  `ip_address` varchar(64) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_consent` (`user_id`,`consent_key`,`consent_version`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_consents`
--

LOCK TABLES `user_consents` WRITE;
/*!40000 ALTER TABLE `user_consents` DISABLE KEYS */;
INSERT INTO `user_consents` VALUES
(1,3,'meta_oauth_authorization','v1','2025-12-22 21:55:32','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36');
/*!40000 ALTER TABLE `user_consents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'user',
  `status` varchar(20) DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(2,'test admin','root@root.com','$2y$10$IebEUvMec38dHJN6qrPdZ.jv7CMiRqBg0yFNvaD8uFgByngj40eAS','admin','active','2025-12-08 11:10:47','2025-12-13 13:15:38'),
(3,'USER','user@user.com','$2y$10$jcsBiNiZGdVUrVEVG2wT5et1hWgtMqEyeH7WvPOvDlJFhZq/H2GE.','user','active','2025-12-13 22:37:01','2025-12-16 21:25:15'),
(4,'test2','test2@test.com','$2y$10$VwBW.mQpU6OE8sOAQOaiXOWbpbgiLV9YYFvyRpZ5oeBTQyGl7oKGy','user','active','2025-12-13 20:08:34','2025-12-13 20:26:52'),
(5,'test2','test3@test.com','$2y$10$XI8DpwOt3V5Cktn7dWjqT.liFZF7Srx5Sju7Zj/Jx2UtdY2Q2xFe6','user','active','2025-12-13 20:27:51','2025-12-14 10:02:14');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-01-19 10:12:05
