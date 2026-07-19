-- MySQL dump 10.13  Distrib 8.4.9, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: hotel_main
-- ------------------------------------------------------
-- Server version	8.4.9
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `abandoned_carts`
--

DROP TABLE IF EXISTS `abandoned_carts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `abandoned_carts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guest_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guest_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cart_data` json NOT NULL,
  `recovery_token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recovered_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `abandoned_carts_session_id_unique` (`session_id`),
  UNIQUE KEY `abandoned_carts_recovery_token_unique` (`recovery_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `accounting_periods`
--

DROP TABLE IF EXISTS `accounting_periods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `accounting_periods` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `year` smallint unsigned NOT NULL,
  `month` tinyint unsigned NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `locked_at` timestamp NULL DEFAULT NULL,
  `locked_by_user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accounting_periods_property_id_year_month_unique` (`property_id`,`year`,`month`),
  KEY `accounting_periods_locked_by_user_id_foreign` (`locked_by_user_id`),
  CONSTRAINT `accounting_periods_locked_by_user_id_foreign` FOREIGN KEY (`locked_by_user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `accounting_periods_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admin_users`
--

DROP TABLE IF EXISTS `admin_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'support',
  `permissions` json DEFAULT NULL,
  `two_factor_secret_encrypted` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `two_factor_recovery_codes` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `allotments`
--

DROP TABLE IF EXISTS `allotments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `allotments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `travel_agent_id` bigint unsigned DEFAULT NULL,
  `company_id` bigint unsigned DEFAULT NULL,
  `room_type_id` bigint unsigned NOT NULL,
  `rate_plan_id` bigint unsigned DEFAULT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `rooms_blocked` smallint unsigned NOT NULL,
  `rooms_picked_up` smallint unsigned NOT NULL DEFAULT '0',
  `release_date` date DEFAULT NULL,
  `negotiated_rate` decimal(12,2) DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `allotments_property_id_foreign` (`property_id`),
  KEY `allotments_travel_agent_id_foreign` (`travel_agent_id`),
  KEY `allotments_company_id_foreign` (`company_id`),
  KEY `allotments_room_type_id_foreign` (`room_type_id`),
  KEY `allotments_rate_plan_id_foreign` (`rate_plan_id`),
  CONSTRAINT `allotments_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `allotments_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `allotments_rate_plan_id_foreign` FOREIGN KEY (`rate_plan_id`) REFERENCES `rate_plans` (`id`),
  CONSTRAINT `allotments_room_type_id_foreign` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`),
  CONSTRAINT `allotments_travel_agent_id_foreign` FOREIGN KEY (`travel_agent_id`) REFERENCES `travel_agents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ap_bill_lines`
--

DROP TABLE IF EXISTS `ap_bill_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ap_bill_lines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bill_id` bigint unsigned NOT NULL,
  `account_id` bigint unsigned NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT '1.00',
  `unit_price` decimal(12,2) NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `tax_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `withholding_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ap_bill_lines_bill_id_foreign` (`bill_id`),
  KEY `ap_bill_lines_account_id_foreign` (`account_id`),
  CONSTRAINT `ap_bill_lines_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `chart_of_accounts` (`id`),
  CONSTRAINT `ap_bill_lines_bill_id_foreign` FOREIGN KEY (`bill_id`) REFERENCES `ap_bills` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ap_bills`
--

DROP TABLE IF EXISTS `ap_bills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ap_bills` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `supplier_id` bigint unsigned NOT NULL,
  `bill_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `vendor_invoice_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issued_at` date NOT NULL,
  `due_at` date NOT NULL,
  `subtotal` decimal(14,2) NOT NULL,
  `tax_total` decimal(14,2) NOT NULL DEFAULT '0.00',
  `withholding_total` decimal(14,2) NOT NULL DEFAULT '0.00',
  `grand_total` decimal(14,2) NOT NULL,
  `paid_total` decimal(14,2) NOT NULL DEFAULT '0.00',
  `balance` decimal(14,2) NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `attachments` json DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ap_bills_property_id_bill_no_unique` (`property_id`,`bill_no`),
  KEY `ap_bills_supplier_id_foreign` (`supplier_id`),
  CONSTRAINT `ap_bills_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ap_bills_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `ap_suppliers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ap_payments`
--

DROP TABLE IF EXISTS `ap_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ap_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bill_id` bigint unsigned NOT NULL,
  `paid_at` date NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `journal_entry_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ap_payments_bill_id_foreign` (`bill_id`),
  KEY `ap_payments_journal_entry_id_foreign` (`journal_entry_id`),
  CONSTRAINT `ap_payments_bill_id_foreign` FOREIGN KEY (`bill_id`) REFERENCES `ap_bills` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ap_payments_journal_entry_id_foreign` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ap_suppliers`
--

DROP TABLE IF EXISTS `ap_suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ap_suppliers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `legal_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `npwp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_pph23` tinyint(1) NOT NULL DEFAULT '0',
  `pph23_rate` decimal(6,3) NOT NULL DEFAULT '2.000',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ap_suppliers_property_id_foreign` (`property_id`),
  CONSTRAINT `ap_suppliers_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `api_idempotency_keys`
--

DROP TABLE IF EXISTS `api_idempotency_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `api_idempotency_keys` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `response` json DEFAULT NULL,
  `http_status` smallint unsigned DEFAULT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `api_idempotency_keys_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `approval_requests`
--

DROP TABLE IF EXISTS `approval_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `approval_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `requester_id` bigint unsigned NOT NULL,
  `action_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` json NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `approver_id` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approver_notes` text COLLATE utf8mb4_unicode_ci,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `approval_requests_property_id_foreign` (`property_id`),
  KEY `approval_requests_requester_id_foreign` (`requester_id`),
  KEY `approval_requests_approver_id_foreign` (`approver_id`),
  CONSTRAINT `approval_requests_approver_id_foreign` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`),
  CONSTRAINT `approval_requests_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `approval_requests_requester_id_foreign` FOREIGN KEY (`requester_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ar_accounts`
--

DROP TABLE IF EXISTS `ar_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ar_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `account_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_id` bigint unsigned DEFAULT NULL,
  `travel_agent_id` bigint unsigned DEFAULT NULL,
  `channel_id` bigint unsigned DEFAULT NULL,
  `guest_id` bigint unsigned DEFAULT NULL,
  `credit_limit` decimal(14,2) NOT NULL DEFAULT '0.00',
  `balance_cached` decimal(14,2) NOT NULL DEFAULT '0.00',
  `payment_terms_days` smallint unsigned NOT NULL DEFAULT '30',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ar_accounts_property_id_foreign` (`property_id`),
  KEY `ar_accounts_company_id_foreign` (`company_id`),
  KEY `ar_accounts_travel_agent_id_foreign` (`travel_agent_id`),
  KEY `ar_accounts_channel_id_foreign` (`channel_id`),
  KEY `ar_accounts_guest_id_foreign` (`guest_id`),
  CONSTRAINT `ar_accounts_channel_id_foreign` FOREIGN KEY (`channel_id`) REFERENCES `channels` (`id`),
  CONSTRAINT `ar_accounts_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `ar_accounts_guest_id_foreign` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`),
  CONSTRAINT `ar_accounts_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ar_accounts_travel_agent_id_foreign` FOREIGN KEY (`travel_agent_id`) REFERENCES `travel_agents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ar_invoice_lines`
--

DROP TABLE IF EXISTS `ar_invoice_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ar_invoice_lines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint unsigned NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT '1.00',
  `unit_price` decimal(12,2) NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `tax_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ar_invoice_lines_invoice_id_foreign` (`invoice_id`),
  CONSTRAINT `ar_invoice_lines_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `ar_invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ar_invoices`
--

DROP TABLE IF EXISTS `ar_invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ar_invoices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `ar_account_id` bigint unsigned NOT NULL,
  `invoice_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `issued_at` date NOT NULL,
  `due_at` date NOT NULL,
  `subtotal` decimal(14,2) NOT NULL,
  `tax_total` decimal(14,2) NOT NULL DEFAULT '0.00',
  `grand_total` decimal(14,2) NOT NULL,
  `paid_total` decimal(14,2) NOT NULL DEFAULT '0.00',
  `balance` decimal(14,2) NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `attachments` json DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ar_invoices_property_id_invoice_no_unique` (`property_id`,`invoice_no`),
  KEY `ar_invoices_ar_account_id_foreign` (`ar_account_id`),
  CONSTRAINT `ar_invoices_ar_account_id_foreign` FOREIGN KEY (`ar_account_id`) REFERENCES `ar_accounts` (`id`),
  CONSTRAINT `ar_invoices_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ar_payments`
--

DROP TABLE IF EXISTS `ar_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ar_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint unsigned NOT NULL,
  `paid_at` date NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `journal_entry_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ar_payments_invoice_id_foreign` (`invoice_id`),
  KEY `ar_payments_journal_entry_id_foreign` (`journal_entry_id`),
  CONSTRAINT `ar_payments_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `ar_invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ar_payments_journal_entry_id_foreign` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ari_sync_log`
--

DROP TABLE IF EXISTS `ari_sync_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ari_sync_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `channel_id` bigint unsigned NOT NULL,
  `operation` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload_summary` json DEFAULT NULL,
  `response_summary` json DEFAULT NULL,
  `error` text COLLATE utf8mb4_unicode_ci,
  `attempt` int unsigned NOT NULL DEFAULT '1',
  `started_at` timestamp NULL DEFAULT NULL,
  `finished_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ari_sync_log_channel_id_created_at_index` (`channel_id`,`created_at`),
  CONSTRAINT `ari_sync_log_channel_id_foreign` FOREIGN KEY (`channel_id`) REFERENCES `channels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `assets`
--

DROP TABLE IF EXISTS `assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `asset_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `room_id` bigint unsigned DEFAULT NULL,
  `serial_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vendor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `purchased_at` date DEFAULT NULL,
  `purchase_cost` decimal(14,2) DEFAULT NULL,
  `useful_life_years` smallint unsigned DEFAULT NULL,
  `residual_value` decimal(14,2) NOT NULL DEFAULT '0.00',
  `accumulated_depreciation` decimal(14,2) NOT NULL DEFAULT '0.00',
  `depreciation_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'straight_line',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `disposed_at` date DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `photos` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `assets_asset_no_unique` (`asset_no`),
  KEY `assets_property_id_foreign` (`property_id`),
  KEY `assets_room_id_foreign` (`room_id`),
  CONSTRAINT `assets_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assets_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `attendance_logs`
--

DROP TABLE IF EXISTS `attendance_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendance_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `date` date NOT NULL,
  `clock_in` datetime DEFAULT NULL,
  `clock_out` datetime DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'present',
  `overtime_minutes` smallint unsigned NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attendance_logs_employee_id_date_unique` (`employee_id`,`date`),
  KEY `attendance_logs_date_index` (`date`),
  CONSTRAINT `attendance_logs_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `audit_log_checkpoints`
--

DROP TABLE IF EXISTS `audit_log_checkpoints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_log_checkpoints` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `checkpoint_date` date NOT NULL,
  `first_entry_id` bigint unsigned NOT NULL,
  `last_entry_id` bigint unsigned NOT NULL,
  `entries_count` int unsigned NOT NULL,
  `cumulative_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `exported_at` timestamp NULL DEFAULT NULL,
  `export_destination` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `audit_log_checkpoints_checkpoint_date_unique` (`checkpoint_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `user_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `auditable_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `auditable_id` bigint unsigned DEFAULT NULL,
  `before` json DEFAULT NULL,
  `after` json DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `previous_hash` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entry_hash` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `audit_logs_auditable_type_auditable_id_index` (`auditable_type`,`auditable_id`),
  KEY `audit_logs_action_created_at_index` (`action`,`created_at`),
  KEY `audit_logs_property_id_index` (`property_id`),
  KEY `audit_logs_entry_hash_index` (`entry_hash`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bank_accounts`
--

DROP TABLE IF EXISTS `bank_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bank_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `coa_account_id` bigint unsigned NOT NULL,
  `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_holder` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'IDR',
  `swift_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bank_accounts_property_id_foreign` (`property_id`),
  KEY `bank_accounts_coa_account_id_foreign` (`coa_account_id`),
  CONSTRAINT `bank_accounts_coa_account_id_foreign` FOREIGN KEY (`coa_account_id`) REFERENCES `chart_of_accounts` (`id`),
  CONSTRAINT `bank_accounts_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bank_statement_lines`
--

DROP TABLE IF EXISTS `bank_statement_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bank_statement_lines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `statement_id` bigint unsigned NOT NULL,
  `transaction_date` date NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `debit` decimal(16,2) NOT NULL DEFAULT '0.00',
  `credit` decimal(16,2) NOT NULL DEFAULT '0.00',
  `balance` decimal(16,2) DEFAULT NULL,
  `reference_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `matched_journal_line_id` bigint unsigned DEFAULT NULL,
  `is_reconciled` tinyint(1) NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bank_statement_lines_statement_id_foreign` (`statement_id`),
  KEY `bank_statement_lines_matched_journal_line_id_foreign` (`matched_journal_line_id`),
  CONSTRAINT `bank_statement_lines_matched_journal_line_id_foreign` FOREIGN KEY (`matched_journal_line_id`) REFERENCES `journal_lines` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bank_statement_lines_statement_id_foreign` FOREIGN KEY (`statement_id`) REFERENCES `bank_statements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bank_statements`
--

DROP TABLE IF EXISTS `bank_statements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bank_statements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bank_account_id` bigint unsigned NOT NULL,
  `statement_date` date NOT NULL,
  `period_from` date NOT NULL,
  `period_to` date NOT NULL,
  `opening_balance` decimal(16,2) NOT NULL,
  `closing_balance` decimal(16,2) NOT NULL,
  `source_file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'imported',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bank_statements_bank_account_id_foreign` (`bank_account_id`),
  CONSTRAINT `bank_statements_bank_account_id_foreign` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `booking_access_tokens`
--

DROP TABLE IF EXISTS `booking_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `booking_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reservation_id` bigint unsigned NOT NULL,
  `token_hashed` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purpose` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `booking_access_tokens_token_hashed_unique` (`token_hashed`),
  KEY `booking_access_tokens_reservation_id_foreign` (`reservation_id`),
  CONSTRAINT `booking_access_tokens_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `budget_lines`
--

DROP TABLE IF EXISTS `budget_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `budget_lines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `budget_period_id` bigint unsigned NOT NULL,
  `account_id` bigint unsigned NOT NULL,
  `month` tinyint unsigned NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `budget_lines_budget_period_id_account_id_month_unique` (`budget_period_id`,`account_id`,`month`),
  KEY `budget_lines_account_id_foreign` (`account_id`),
  CONSTRAINT `budget_lines_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `chart_of_accounts` (`id`),
  CONSTRAINT `budget_lines_budget_period_id_foreign` FOREIGN KEY (`budget_period_id`) REFERENCES `budget_periods` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `budget_periods`
--

DROP TABLE IF EXISTS `budget_periods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `budget_periods` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `year` smallint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `budget_periods_property_id_year_unique` (`property_id`,`year`),
  CONSTRAINT `budget_periods_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cancellation_policies`
--

DROP TABLE IF EXISTS `cancellation_policies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cancellation_policies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_refundable` tinyint(1) NOT NULL DEFAULT '1',
  `rules` json NOT NULL,
  `display_text` text COLLATE utf8mb4_unicode_ci,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cancellation_policies_property_id_code_unique` (`property_id`,`code`),
  CONSTRAINT `cancellation_policies_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `carbon_footprints`
--

DROP TABLE IF EXISTS `carbon_footprints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `carbon_footprints` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `reservation_id` bigint unsigned DEFAULT NULL,
  `energy_kwh` decimal(14,2) NOT NULL DEFAULT '0.00',
  `water_liters` decimal(14,2) NOT NULL DEFAULT '0.00',
  `waste_kg` decimal(14,2) NOT NULL DEFAULT '0.00',
  `co2e_kg` decimal(14,2) NOT NULL DEFAULT '0.00',
  `breakdown` json DEFAULT NULL,
  `period_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `carbon_footprints_reservation_id_foreign` (`reservation_id`),
  KEY `carbon_footprints_property_id_period_date_index` (`property_id`,`period_date`),
  CONSTRAINT `carbon_footprints_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `carbon_footprints_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cashier_shifts`
--

DROP TABLE IF EXISTS `cashier_shifts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cashier_shifts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `cashier_id` bigint unsigned NOT NULL,
  `opened_at` timestamp NOT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `opening_float` decimal(14,2) NOT NULL DEFAULT '0.00',
  `expected_cash` decimal(14,2) NOT NULL DEFAULT '0.00',
  `actual_cash` decimal(14,2) NOT NULL DEFAULT '0.00',
  `cash_variance` decimal(14,2) NOT NULL DEFAULT '0.00',
  `breakdown` json DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cashier_shifts_cashier_id_foreign` (`cashier_id`),
  KEY `cashier_shifts_property_id_opened_at_index` (`property_id`,`opened_at`),
  CONSTRAINT `cashier_shifts_cashier_id_foreign` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`id`),
  CONSTRAINT `cashier_shifts_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `channel_conflicts`
--

DROP TABLE IF EXISTS `channel_conflicts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `channel_conflicts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `channel_id` bigint unsigned DEFAULT NULL,
  `conflict_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` json NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `resolved_by_user_id` bigint unsigned DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `resolution_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `channel_conflicts_property_id_foreign` (`property_id`),
  KEY `channel_conflicts_channel_id_foreign` (`channel_id`),
  KEY `channel_conflicts_resolved_by_user_id_foreign` (`resolved_by_user_id`),
  CONSTRAINT `channel_conflicts_channel_id_foreign` FOREIGN KEY (`channel_id`) REFERENCES `channels` (`id`),
  CONSTRAINT `channel_conflicts_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `channel_conflicts_resolved_by_user_id_foreign` FOREIGN KEY (`resolved_by_user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `channel_parity_alerts`
--

DROP TABLE IF EXISTS `channel_parity_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `channel_parity_alerts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `room_type_id` bigint unsigned NOT NULL,
  `channel_id` bigint unsigned DEFAULT NULL,
  `check_date` date NOT NULL,
  `direct_rate` decimal(14,2) NOT NULL,
  `channel_rate` decimal(14,2) NOT NULL,
  `gap_amount` decimal(14,2) NOT NULL,
  `gap_pct` decimal(8,4) NOT NULL,
  `severity` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'low',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `action_taken` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resolved_by_user_id` bigint unsigned DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `channel_parity_alerts_room_type_id_foreign` (`room_type_id`),
  KEY `channel_parity_alerts_channel_id_foreign` (`channel_id`),
  KEY `channel_parity_alerts_resolved_by_user_id_foreign` (`resolved_by_user_id`),
  KEY `channel_parity_alerts_property_id_status_check_date_index` (`property_id`,`status`,`check_date`),
  CONSTRAINT `channel_parity_alerts_channel_id_foreign` FOREIGN KEY (`channel_id`) REFERENCES `channels` (`id`) ON DELETE SET NULL,
  CONSTRAINT `channel_parity_alerts_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `channel_parity_alerts_resolved_by_user_id_foreign` FOREIGN KEY (`resolved_by_user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `channel_parity_alerts_room_type_id_foreign` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `channel_room_mappings`
--

DROP TABLE IF EXISTS `channel_room_mappings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `channel_room_mappings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `channel_id` bigint unsigned NOT NULL,
  `room_type_id` bigint unsigned NOT NULL,
  `rate_plan_id` bigint unsigned NOT NULL,
  `channel_room_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `channel_rate_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `config` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `crm_channel_rt_rp_unique` (`channel_id`,`room_type_id`,`rate_plan_id`),
  KEY `channel_room_mappings_room_type_id_foreign` (`room_type_id`),
  KEY `channel_room_mappings_rate_plan_id_foreign` (`rate_plan_id`),
  CONSTRAINT `channel_room_mappings_channel_id_foreign` FOREIGN KEY (`channel_id`) REFERENCES `channels` (`id`) ON DELETE CASCADE,
  CONSTRAINT `channel_room_mappings_rate_plan_id_foreign` FOREIGN KEY (`rate_plan_id`) REFERENCES `rate_plans` (`id`),
  CONSTRAINT `channel_room_mappings_room_type_id_foreign` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `channels`
--

DROP TABLE IF EXISTS `channels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `channels` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `adapter_class` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `credentials_encrypted` text COLLATE utf8mb4_unicode_ci,
  `config` json DEFAULT NULL,
  `hotel_id_at_channel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `two_way_sync` tinyint(1) NOT NULL DEFAULT '1',
  `last_sync_at` timestamp NULL DEFAULT NULL,
  `last_sync_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `channels_property_id_code_unique` (`property_id`,`code`),
  CONSTRAINT `channels_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chart_of_accounts`
--

DROP TABLE IF EXISTS `chart_of_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chart_of_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `code` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `normal_balance` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chart_of_accounts_property_id_code_unique` (`property_id`,`code`),
  KEY `chart_of_accounts_parent_id_foreign` (`parent_id`),
  CONSTRAINT `chart_of_accounts_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `chart_of_accounts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chart_of_accounts_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `companies`
--

DROP TABLE IF EXISTS `companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `companies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `legal_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `npwp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ID',
  `credit_limit` decimal(14,2) NOT NULL DEFAULT '0.00',
  `payment_terms_days` smallint unsigned NOT NULL DEFAULT '30',
  `contract_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contract_start` date DEFAULT NULL,
  `contract_end` date DEFAULT NULL,
  `negotiated_rates` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `companies_property_id_foreign` (`property_id`),
  CONSTRAINT `companies_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `daily_flash_reports`
--

DROP TABLE IF EXISTS `daily_flash_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `daily_flash_reports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `report_date` date NOT NULL,
  `rooms_kpi` json NOT NULL,
  `revenue_breakdown` json NOT NULL,
  `tax_breakdown` json NOT NULL,
  `payment_breakdown` json NOT NULL,
  `source_mix` json NOT NULL,
  `total_revenue` decimal(14,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `daily_flash_reports_property_id_report_date_unique` (`property_id`,`report_date`),
  CONSTRAINT `daily_flash_reports_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `deployment_heartbeats`
--

DROP TABLE IF EXISTS `deployment_heartbeats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `deployment_heartbeats` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `license_key_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deployment_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `version` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rooms_count` int unsigned DEFAULT NULL,
  `active_bookings` int unsigned DEFAULT NULL,
  `queue_jobs_pending` int unsigned DEFAULT NULL,
  `queue_jobs_failed_24h` int unsigned DEFAULT NULL,
  `errors_24h` int unsigned DEFAULT NULL,
  `db_size_mb` int unsigned DEFAULT NULL,
  `uptime_pct_24h` decimal(5,2) DEFAULT NULL,
  `received_at` timestamp NOT NULL,
  `source_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `deployment_heartbeats_license_key_hash_index` (`license_key_hash`),
  KEY `deployment_heartbeats_deployment_id_index` (`deployment_id`),
  KEY `deployment_heartbeats_received_at_index` (`received_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `document_templates`
--

DROP TABLE IF EXISTS `document_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `locale` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'id',
  `header_html` longtext COLLATE utf8mb4_unicode_ci,
  `body_html` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `footer_html` longtext COLLATE utf8mb4_unicode_ci,
  `css` json DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `document_templates_property_id_type_locale_name_unique` (`property_id`,`type`,`locale`,`name`),
  CONSTRAINT `document_templates_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `door_lock_events`
--

DROP TABLE IF EXISTS `door_lock_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `door_lock_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `room_id` bigint unsigned NOT NULL,
  `reservation_id` bigint unsigned DEFAULT NULL,
  `guest_id` bigint unsigned DEFAULT NULL,
  `event_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payload` json DEFAULT NULL,
  `occurred_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `door_lock_events_room_id_foreign` (`room_id`),
  KEY `door_lock_events_reservation_id_foreign` (`reservation_id`),
  KEY `door_lock_events_guest_id_foreign` (`guest_id`),
  KEY `door_lock_events_property_id_occurred_at_index` (`property_id`,`occurred_at`),
  CONSTRAINT `door_lock_events_guest_id_foreign` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`),
  CONSTRAINT `door_lock_events_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `door_lock_events_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`),
  CONSTRAINT `door_lock_events_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dynamic_pricing_log`
--

DROP TABLE IF EXISTS `dynamic_pricing_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dynamic_pricing_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `rule_id` bigint unsigned DEFAULT NULL,
  `target_date` date NOT NULL,
  `room_type_id` bigint unsigned NOT NULL,
  `channel_id` bigint unsigned DEFAULT NULL,
  `price_before` decimal(14,2) NOT NULL,
  `price_after` decimal(14,2) NOT NULL,
  `trigger_reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `metrics_snapshot` json NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dynamic_pricing_log_rule_id_foreign` (`rule_id`),
  KEY `dynamic_pricing_log_room_type_id_foreign` (`room_type_id`),
  KEY `dynamic_pricing_log_channel_id_foreign` (`channel_id`),
  KEY `dynamic_pricing_log_property_id_target_date_index` (`property_id`,`target_date`),
  CONSTRAINT `dynamic_pricing_log_channel_id_foreign` FOREIGN KEY (`channel_id`) REFERENCES `channels` (`id`) ON DELETE SET NULL,
  CONSTRAINT `dynamic_pricing_log_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dynamic_pricing_log_room_type_id_foreign` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`),
  CONSTRAINT `dynamic_pricing_log_rule_id_foreign` FOREIGN KEY (`rule_id`) REFERENCES `dynamic_pricing_rules` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dynamic_pricing_rules`
--

DROP TABLE IF EXISTS `dynamic_pricing_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dynamic_pricing_rules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `room_type_id` bigint unsigned DEFAULT NULL,
  `channel_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trigger_metric` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `operator` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `threshold_low` decimal(8,2) NOT NULL,
  `threshold_high` decimal(8,2) DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action_value` decimal(8,2) NOT NULL,
  `min_price_floor` decimal(14,2) DEFAULT NULL,
  `max_price_ceiling` decimal(14,2) DEFAULT NULL,
  `lookahead_days` tinyint unsigned NOT NULL DEFAULT '30',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_applied_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dynamic_pricing_rules_room_type_id_foreign` (`room_type_id`),
  KEY `dynamic_pricing_rules_channel_id_foreign` (`channel_id`),
  KEY `dynamic_pricing_rules_property_id_is_active_index` (`property_id`,`is_active`),
  CONSTRAINT `dynamic_pricing_rules_channel_id_foreign` FOREIGN KEY (`channel_id`) REFERENCES `channels` (`id`) ON DELETE SET NULL,
  CONSTRAINT `dynamic_pricing_rules_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dynamic_pricing_rules_room_type_id_foreign` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `e_faktur_records`
--

DROP TABLE IF EXISTS `e_faktur_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `e_faktur_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `nsfp` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `faktur_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issued_at` date NOT NULL,
  `source_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_id` bigint unsigned NOT NULL,
  `buyer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `buyer_npwp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dpp` decimal(14,2) NOT NULL,
  `ppn` decimal(14,2) NOT NULL,
  `lines` json NOT NULL,
  `coretax_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `coretax_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `coretax_response` json DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `e_faktur_records_nsfp_unique` (`nsfp`),
  KEY `e_faktur_records_source_type_source_id_index` (`source_type`,`source_id`),
  KEY `e_faktur_records_property_id_issued_at_index` (`property_id`,`issued_at`),
  CONSTRAINT `e_faktur_records_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `e_registration_cards`
--

DROP TABLE IF EXISTS `e_registration_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `e_registration_cards` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `reservation_id` bigint unsigned NOT NULL,
  `guest_id` bigint unsigned NOT NULL,
  `signed_at` timestamp NULL DEFAULT NULL,
  `signature_image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verified_by_staff_id` bigint unsigned DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `submitted_data` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `e_registration_cards_property_id_foreign` (`property_id`),
  KEY `e_registration_cards_reservation_id_foreign` (`reservation_id`),
  KEY `e_registration_cards_guest_id_foreign` (`guest_id`),
  KEY `e_registration_cards_verified_by_staff_id_foreign` (`verified_by_staff_id`),
  CONSTRAINT `e_registration_cards_guest_id_foreign` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `e_registration_cards_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `e_registration_cards_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `e_registration_cards_verified_by_staff_id_foreign` FOREIGN KEY (`verified_by_staff_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `efaktur_records`
--

DROP TABLE IF EXISTS `efaktur_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `efaktur_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `invoice_id` bigint unsigned DEFAULT NULL,
  `invoice_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nomor_faktur` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kode_transaksi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kode_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `npwp_penjual` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `npwp_pembeli` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dpp` decimal(14,2) DEFAULT NULL,
  `ppn` decimal(14,2) DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `request_payload` json DEFAULT NULL,
  `response_payload` json DEFAULT NULL,
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `ref_tahun` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref_bulan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref_jenis` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancelled_by_user_id` bigint unsigned DEFAULT NULL,
  `cancel_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `efaktur_records_nomor_faktur_unique` (`nomor_faktur`),
  KEY `efaktur_records_cancelled_by_user_id_foreign` (`cancelled_by_user_id`),
  KEY `efaktur_records_source_type_source_id_index` (`source_type`,`source_id`),
  KEY `efaktur_records_property_id_status_index` (`property_id`,`status`),
  KEY `efaktur_records_nomor_faktur_index` (`nomor_faktur`),
  KEY `efaktur_records_invoice_no_index` (`invoice_no`),
  CONSTRAINT `efaktur_records_cancelled_by_user_id_foreign` FOREIGN KEY (`cancelled_by_user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `efaktur_records_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `employee_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nik` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `marital_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dependents_count` tinyint unsigned NOT NULL DEFAULT '0',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `npwp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bpjs_kesehatan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bpjs_tk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `joined_at` date NOT NULL,
  `terminated_at` date DEFAULT NULL,
  `basic_salary` decimal(14,2) NOT NULL,
  `position_allowance` decimal(14,2) NOT NULL DEFAULT '0.00',
  `transport_allowance` decimal(14,2) NOT NULL DEFAULT '0.00',
  `meal_allowance` decimal(14,2) NOT NULL DEFAULT '0.00',
  `other_allowances` json DEFAULT NULL,
  `employment_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'permanent',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employees_employee_no_unique` (`employee_no`),
  KEY `employees_property_id_foreign` (`property_id`),
  KEY `employees_user_id_foreign` (`user_id`),
  CONSTRAINT `employees_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `event_menu_items`
--

DROP TABLE IF EXISTS `event_menu_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_menu_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qty` int unsigned NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `subtotal` decimal(14,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `event_menu_items_event_id_foreign` (`event_id`),
  CONSTRAINT `event_menu_items_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `event_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `function_room_id` bigint unsigned NOT NULL,
  `company_id` bigint unsigned DEFAULT NULL,
  `primary_contact_guest_id` bigint unsigned DEFAULT NULL,
  `event_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `setup` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expected_attendees` smallint unsigned NOT NULL,
  `venue_rate` decimal(14,2) NOT NULL DEFAULT '0.00',
  `fnb_total` decimal(14,2) NOT NULL DEFAULT '0.00',
  `addons_total` decimal(14,2) NOT NULL DEFAULT '0.00',
  `grand_total` decimal(14,2) NOT NULL DEFAULT '0.00',
  `deposit_paid` decimal(14,2) NOT NULL DEFAULT '0.00',
  `balance` decimal(14,2) NOT NULL DEFAULT '0.00',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inquiry',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `av_equipment` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `events_event_no_unique` (`event_no`),
  KEY `events_property_id_foreign` (`property_id`),
  KEY `events_function_room_id_foreign` (`function_room_id`),
  KEY `events_company_id_foreign` (`company_id`),
  KEY `events_primary_contact_guest_id_foreign` (`primary_contact_guest_id`),
  CONSTRAINT `events_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `events_function_room_id_foreign` FOREIGN KEY (`function_room_id`) REFERENCES `function_rooms` (`id`),
  CONSTRAINT `events_primary_contact_guest_id_foreign` FOREIGN KEY (`primary_contact_guest_id`) REFERENCES `guests` (`id`),
  CONSTRAINT `events_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `folio_charges`
--

DROP TABLE IF EXISTS `folio_charges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `folio_charges` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `folio_id` bigint unsigned NOT NULL,
  `property_id` bigint unsigned NOT NULL,
  `charge_date` date NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qty` int unsigned NOT NULL DEFAULT '1',
  `unit_price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `amount` decimal(14,2) NOT NULL,
  `tax_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `is_taxable` tinyint(1) NOT NULL DEFAULT '1',
  `is_void` tinyint(1) NOT NULL DEFAULT '0',
  `void_reason` text COLLATE utf8mb4_unicode_ci,
  `source_type_id` bigint unsigned DEFAULT NULL,
  `source_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_ref` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `posted_by_user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `folio_charges_property_id_foreign` (`property_id`),
  KEY `folio_charges_posted_by_user_id_foreign` (`posted_by_user_id`),
  KEY `folio_charges_folio_id_charge_date_index` (`folio_id`,`charge_date`),
  KEY `folio_charges_category_index` (`category`),
  CONSTRAINT `folio_charges_folio_id_foreign` FOREIGN KEY (`folio_id`) REFERENCES `folios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `folio_charges_posted_by_user_id_foreign` FOREIGN KEY (`posted_by_user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `folio_charges_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2277 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `folio_payments`
--

DROP TABLE IF EXISTS `folio_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `folio_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `folio_id` bigint unsigned NOT NULL,
  `property_id` bigint unsigned NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_id` bigint unsigned DEFAULT NULL,
  `reference_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mdr_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `gateway_payload` json DEFAULT NULL,
  `is_void` tinyint(1) NOT NULL DEFAULT '0',
  `void_reason` text COLLATE utf8mb4_unicode_ci,
  `cashier_id` bigint unsigned DEFAULT NULL,
  `shift_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `folio_payments_property_id_foreign` (`property_id`),
  KEY `folio_payments_cashier_id_foreign` (`cashier_id`),
  KEY `folio_payments_folio_id_payment_date_index` (`folio_id`,`payment_date`),
  KEY `folio_payments_provider_id_foreign` (`provider_id`),
  KEY `folio_payments_shift_id_foreign` (`shift_id`),
  CONSTRAINT `folio_payments_cashier_id_foreign` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`id`),
  CONSTRAINT `folio_payments_folio_id_foreign` FOREIGN KEY (`folio_id`) REFERENCES `folios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `folio_payments_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`),
  CONSTRAINT `folio_payments_provider_id_foreign` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `folio_payments_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `cashier_shifts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=674 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `folios`
--

DROP TABLE IF EXISTS `folios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `folios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `reservation_id` bigint unsigned DEFAULT NULL,
  `guest_id` bigint unsigned DEFAULT NULL,
  `company_id` bigint unsigned DEFAULT NULL,
  `folio_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'guest',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `total_charges` decimal(14,2) NOT NULL DEFAULT '0.00',
  `total_payments` decimal(14,2) NOT NULL DEFAULT '0.00',
  `balance` decimal(14,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'IDR',
  `opened_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `closed_at` timestamp NULL DEFAULT NULL,
  `cashier_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `folios_property_id_folio_no_unique` (`property_id`,`folio_no`),
  KEY `folios_reservation_id_foreign` (`reservation_id`),
  KEY `folios_guest_id_foreign` (`guest_id`),
  KEY `folios_company_id_foreign` (`company_id`),
  KEY `folios_cashier_id_foreign` (`cashier_id`),
  KEY `folios_folio_no_index` (`folio_no`),
  CONSTRAINT `folios_cashier_id_foreign` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`id`),
  CONSTRAINT `folios_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `folios_guest_id_foreign` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`),
  CONSTRAINT `folios_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `folios_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=674 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `function_rooms`
--

DROP TABLE IF EXISTS `function_rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `function_rooms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacity_classroom` smallint unsigned DEFAULT NULL,
  `capacity_theatre` smallint unsigned DEFAULT NULL,
  `capacity_banquet` smallint unsigned DEFAULT NULL,
  `capacity_ushape` smallint unsigned DEFAULT NULL,
  `size_sqm` smallint unsigned DEFAULT NULL,
  `amenities` json DEFAULT NULL,
  `photos` json DEFAULT NULL,
  `half_day_rate` decimal(12,2) DEFAULT NULL,
  `full_day_rate` decimal(12,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `function_rooms_property_id_code_unique` (`property_id`,`code`),
  CONSTRAINT `function_rooms_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fx_rates`
--

DROP TABLE IF EXISTS `fx_rates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fx_rates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `base_currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quote_currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rate_date` date NOT NULL,
  `rate` decimal(18,8) NOT NULL,
  `source` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fx_rates_base_currency_quote_currency_rate_date_unique` (`base_currency`,`quote_currency`,`rate_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gds_bookings`
--

DROP TABLE IF EXISTS `gds_bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gds_bookings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `gds` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `booking_locator` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reservation_id` bigint unsigned DEFAULT NULL,
  `payload` json NOT NULL,
  `received_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gds_bookings_gds_booking_locator_unique` (`gds`,`booking_locator`),
  KEY `gds_bookings_property_id_foreign` (`property_id`),
  KEY `gds_bookings_reservation_id_foreign` (`reservation_id`),
  CONSTRAINT `gds_bookings_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `gds_bookings_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gift_vouchers`
--

DROP TABLE IF EXISTS `gift_vouchers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gift_vouchers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `face_value` decimal(14,2) NOT NULL,
  `balance` decimal(14,2) NOT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'IDR',
  `valid_from` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `issued_to_guest_id` bigint unsigned DEFAULT NULL,
  `issued_to_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issued_to_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issued_by_user_id` bigint unsigned DEFAULT NULL,
  `purchased_via_folio_id` bigint unsigned DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `message` text COLLATE utf8mb4_unicode_ci,
  `issued_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gift_vouchers_code_unique` (`code`),
  KEY `gift_vouchers_property_id_foreign` (`property_id`),
  KEY `gift_vouchers_issued_to_guest_id_foreign` (`issued_to_guest_id`),
  KEY `gift_vouchers_issued_by_user_id_foreign` (`issued_by_user_id`),
  KEY `gift_vouchers_purchased_via_folio_id_foreign` (`purchased_via_folio_id`),
  CONSTRAINT `gift_vouchers_issued_by_user_id_foreign` FOREIGN KEY (`issued_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `gift_vouchers_issued_to_guest_id_foreign` FOREIGN KEY (`issued_to_guest_id`) REFERENCES `guests` (`id`) ON DELETE SET NULL,
  CONSTRAINT `gift_vouchers_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `gift_vouchers_purchased_via_folio_id_foreign` FOREIGN KEY (`purchased_via_folio_id`) REFERENCES `folios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `goods_receipt_lines`
--

DROP TABLE IF EXISTS `goods_receipt_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `goods_receipt_lines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `gr_id` bigint unsigned NOT NULL,
  `stock_item_id` bigint unsigned DEFAULT NULL,
  `quantity_received` decimal(14,3) NOT NULL,
  `quantity_accepted` decimal(14,3) NOT NULL DEFAULT '0.000',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `goods_receipt_lines_gr_id_foreign` (`gr_id`),
  KEY `goods_receipt_lines_stock_item_id_foreign` (`stock_item_id`),
  CONSTRAINT `goods_receipt_lines_gr_id_foreign` FOREIGN KEY (`gr_id`) REFERENCES `goods_receipts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `goods_receipt_lines_stock_item_id_foreign` FOREIGN KEY (`stock_item_id`) REFERENCES `stock_items` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `goods_receipts`
--

DROP TABLE IF EXISTS `goods_receipts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `goods_receipts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `gr_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `po_id` bigint unsigned NOT NULL,
  `received_by` bigint unsigned NOT NULL,
  `received_date` date NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `goods_receipts_gr_number_unique` (`gr_number`),
  KEY `goods_receipts_property_id_foreign` (`property_id`),
  KEY `goods_receipts_po_id_foreign` (`po_id`),
  KEY `goods_receipts_received_by_foreign` (`received_by`),
  CONSTRAINT `goods_receipts_po_id_foreign` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `goods_receipts_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `goods_receipts_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `group_block_rooms`
--

DROP TABLE IF EXISTS `group_block_rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `group_block_rooms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `group_block_id` bigint unsigned NOT NULL,
  `room_type_id` bigint unsigned NOT NULL,
  `rooms_count` smallint unsigned NOT NULL,
  `rooms_picked_up` smallint unsigned NOT NULL DEFAULT '0',
  `rate` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `group_block_rooms_group_block_id_foreign` (`group_block_id`),
  KEY `group_block_rooms_room_type_id_foreign` (`room_type_id`),
  CONSTRAINT `group_block_rooms_group_block_id_foreign` FOREIGN KEY (`group_block_id`) REFERENCES `group_blocks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `group_block_rooms_room_type_id_foreign` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `group_blocks`
--

DROP TABLE IF EXISTS `group_blocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `group_blocks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `company_id` bigint unsigned DEFAULT NULL,
  `block_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `group_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `rooms_count` smallint unsigned NOT NULL,
  `negotiated_rate` decimal(12,2) DEFAULT NULL,
  `cutoff_date` date DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tentative',
  `master_folio_id` bigint unsigned DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_blocks_block_code_unique` (`block_code`),
  KEY `group_blocks_property_id_foreign` (`property_id`),
  KEY `group_blocks_company_id_foreign` (`company_id`),
  KEY `group_blocks_master_folio_id_foreign` (`master_folio_id`),
  CONSTRAINT `group_blocks_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `group_blocks_master_folio_id_foreign` FOREIGN KEY (`master_folio_id`) REFERENCES `folios` (`id`),
  CONSTRAINT `group_blocks_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `guest_profiles`
--

DROP TABLE IF EXISTS `guest_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `guest_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `guest_id` bigint unsigned NOT NULL,
  `total_stays` smallint unsigned NOT NULL DEFAULT '0',
  `total_nights` smallint unsigned NOT NULL DEFAULT '0',
  `total_lifetime_value` decimal(16,2) NOT NULL DEFAULT '0.00',
  `avg_daily_rate` decimal(14,2) NOT NULL DEFAULT '0.00',
  `avg_fnb_spend_per_stay` decimal(14,2) NOT NULL DEFAULT '0.00',
  `avg_spa_spend_per_stay` decimal(14,2) NOT NULL DEFAULT '0.00',
  `avg_ancillary_spend` decimal(14,2) NOT NULL DEFAULT '0.00',
  `preferred_room_type_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preferred_floor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preferred_bed_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preferred_check_in_day` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avg_party_size` tinyint unsigned NOT NULL DEFAULT '1',
  `typically_books_breakfast` tinyint(1) NOT NULL DEFAULT '0',
  `typically_uses_spa` tinyint(1) NOT NULL DEFAULT '0',
  `typically_uses_fnb` tinyint(1) NOT NULL DEFAULT '0',
  `avg_lead_days` smallint unsigned NOT NULL DEFAULT '0',
  `primary_booking_source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avg_stay_length` tinyint unsigned NOT NULL DEFAULT '1',
  `visit_frequency` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `upsell_score` tinyint unsigned NOT NULL DEFAULT '0',
  `churn_risk_score` tinyint unsigned NOT NULL DEFAULT '0',
  `loyalty_score` tinyint unsigned NOT NULL DEFAULT '0',
  `avg_review_score` decimal(4,2) DEFAULT NULL,
  `total_reviews` smallint unsigned NOT NULL DEFAULT '0',
  `sentiment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_built_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `guest_profiles_guest_id_unique` (`guest_id`),
  CONSTRAINT `guest_profiles_guest_id_foreign` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `guest_requests`
--

DROP TABLE IF EXISTS `guest_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `guest_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `reservation_id` bigint unsigned DEFAULT NULL,
  `guest_id` bigint unsigned DEFAULT NULL,
  `room_id` bigint unsigned DEFAULT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `priority` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `assignee_id` bigint unsigned DEFAULT NULL,
  `opened_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `responded_at` timestamp NULL DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `response_minutes` smallint unsigned DEFAULT NULL,
  `resolution_minutes` smallint unsigned DEFAULT NULL,
  `resolution_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `guest_requests_reservation_id_foreign` (`reservation_id`),
  KEY `guest_requests_guest_id_foreign` (`guest_id`),
  KEY `guest_requests_room_id_foreign` (`room_id`),
  KEY `guest_requests_assignee_id_foreign` (`assignee_id`),
  KEY `guest_requests_property_id_status_index` (`property_id`,`status`),
  CONSTRAINT `guest_requests_assignee_id_foreign` FOREIGN KEY (`assignee_id`) REFERENCES `users` (`id`),
  CONSTRAINT `guest_requests_guest_id_foreign` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`),
  CONSTRAINT `guest_requests_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `guest_requests_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`),
  CONSTRAINT `guest_requests_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `guests`
--

DROP TABLE IF EXISTS `guests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `guests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned DEFAULT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ID',
  `nationality` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_expires_at` date DEFAULT NULL,
  `address_line1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `province` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_vip` tinyint(1) NOT NULL DEFAULT '0',
  `is_blacklisted` tinyint(1) NOT NULL DEFAULT '0',
  `blacklist_reason` text COLLATE utf8mb4_unicode_ci,
  `preferences` json DEFAULT NULL,
  `tags` json DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `marketing_consent` tinyint(1) NOT NULL DEFAULT '0',
  `forgotten_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `guests_property_id_index` (`property_id`),
  KEY `guests_email_index` (`email`),
  KEY `guests_phone_index` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=771 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hk_tasks`
--

DROP TABLE IF EXISTS `hk_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hk_tasks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `room_id` bigint unsigned NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `assignee_id` bigint unsigned DEFAULT NULL,
  `scheduled_date` date NOT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `duration_minutes` smallint unsigned DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `photos` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hk_tasks_room_id_foreign` (`room_id`),
  KEY `hk_tasks_assignee_id_foreign` (`assignee_id`),
  KEY `hk_tasks_property_id_scheduled_date_status_index` (`property_id`,`scheduled_date`,`status`),
  CONSTRAINT `hk_tasks_assignee_id_foreign` FOREIGN KEY (`assignee_id`) REFERENCES `users` (`id`),
  CONSTRAINT `hk_tasks_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hk_tasks_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inspection_checklists`
--

DROP TABLE IF EXISTS `inspection_checklists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inspection_checklists` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `room_id` bigint unsigned NOT NULL,
  `inspector_id` bigint unsigned DEFAULT NULL,
  `inspected_at` timestamp NULL DEFAULT NULL,
  `overall_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `items` json NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inspection_checklists_property_id_foreign` (`property_id`),
  KEY `inspection_checklists_room_id_foreign` (`room_id`),
  KEY `inspection_checklists_inspector_id_foreign` (`inspector_id`),
  CONSTRAINT `inspection_checklists_inspector_id_foreign` FOREIGN KEY (`inspector_id`) REFERENCES `users` (`id`),
  CONSTRAINT `inspection_checklists_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inspection_checklists_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inventory`
--

DROP TABLE IF EXISTS `inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `room_type_id` bigint unsigned NOT NULL,
  `date` date NOT NULL,
  `total` smallint unsigned NOT NULL DEFAULT '0',
  `sold` smallint unsigned NOT NULL DEFAULT '0',
  `blocked` smallint unsigned NOT NULL DEFAULT '0',
  `out_of_order` smallint unsigned NOT NULL DEFAULT '0',
  `overbooking_allowance` smallint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventory_property_id_room_type_id_date_unique` (`property_id`,`room_type_id`,`date`),
  KEY `inventory_room_type_id_foreign` (`room_type_id`),
  KEY `inventory_property_id_date_index` (`property_id`,`date`),
  CONSTRAINT `inventory_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_room_type_id_foreign` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3315 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `journal_entries`
--

DROP TABLE IF EXISTS `journal_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `journal_entries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `entry_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `posted_at` date NOT NULL,
  `period_year` smallint unsigned NOT NULL,
  `period_month` tinyint unsigned NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_id` bigint unsigned DEFAULT NULL,
  `total_debit` decimal(16,2) NOT NULL,
  `total_credit` decimal(16,2) NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'posted',
  `created_by_user_id` bigint unsigned DEFAULT NULL,
  `posted_by_user_id` bigint unsigned DEFAULT NULL,
  `voided_by_user_id` bigint unsigned DEFAULT NULL,
  `voided_at` timestamp NULL DEFAULT NULL,
  `void_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `journal_entries_property_id_entry_no_unique` (`property_id`,`entry_no`),
  KEY `journal_entries_created_by_user_id_foreign` (`created_by_user_id`),
  KEY `journal_entries_posted_by_user_id_foreign` (`posted_by_user_id`),
  KEY `journal_entries_voided_by_user_id_foreign` (`voided_by_user_id`),
  KEY `journal_entries_property_id_period_year_period_month_index` (`property_id`,`period_year`,`period_month`),
  KEY `journal_entries_source_type_source_id_index` (`source_type`,`source_id`),
  CONSTRAINT `journal_entries_created_by_user_id_foreign` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `journal_entries_posted_by_user_id_foreign` FOREIGN KEY (`posted_by_user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `journal_entries_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `journal_entries_voided_by_user_id_foreign` FOREIGN KEY (`voided_by_user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `journal_lines`
--

DROP TABLE IF EXISTS `journal_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `journal_lines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `journal_entry_id` bigint unsigned NOT NULL,
  `account_id` bigint unsigned NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `debit` decimal(16,2) NOT NULL DEFAULT '0.00',
  `credit` decimal(16,2) NOT NULL DEFAULT '0.00',
  `original_amount` decimal(16,2) DEFAULT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'NULL = base currency (IDR)',
  `tax_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `line_no` smallint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `journal_lines_journal_entry_id_foreign` (`journal_entry_id`),
  KEY `journal_lines_account_id_index` (`account_id`),
  CONSTRAINT `journal_lines_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `chart_of_accounts` (`id`),
  CONSTRAINT `journal_lines_journal_entry_id_foreign` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kb_articles`
--

DROP TABLE IF EXISTS `kb_articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kb_articles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `tags` json DEFAULT NULL,
  `locale` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'id',
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `views_count` int unsigned NOT NULL DEFAULT '0',
  `author_user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kb_articles_slug_unique` (`slug`),
  KEY `kb_articles_author_user_id_foreign` (`author_user_id`),
  KEY `kb_articles_property_id_index` (`property_id`),
  CONSTRAINT `kb_articles_author_user_id_foreign` FOREIGN KEY (`author_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `landmarks`
--

DROP TABLE IF EXISTS `landmarks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `landmarks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `province` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ID',
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `photos` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `landmarks_slug_unique` (`slug`),
  KEY `landmarks_city_country_index` (`city`,`country`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `leave_balances`
--

DROP TABLE IF EXISTS `leave_balances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leave_balances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `year` smallint unsigned NOT NULL,
  `total_annual` tinyint unsigned NOT NULL DEFAULT '12',
  `used_annual` tinyint unsigned NOT NULL DEFAULT '0',
  `total_sick` tinyint unsigned NOT NULL DEFAULT '12',
  `used_sick` tinyint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `leave_balances_employee_id_year_unique` (`employee_id`,`year`),
  KEY `leave_balances_property_id_foreign` (`property_id`),
  CONSTRAINT `leave_balances_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `leave_balances_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `leave_requests`
--

DROP TABLE IF EXISTS `leave_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leave_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_days` tinyint unsigned NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leave_requests_property_id_foreign` (`property_id`),
  KEY `leave_requests_employee_id_foreign` (`employee_id`),
  KEY `leave_requests_approved_by_foreign` (`approved_by`),
  CONSTRAINT `leave_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leave_requests_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `leave_requests_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `license_events`
--

DROP TABLE IF EXISTS `license_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `license_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` json DEFAULT NULL,
  `source_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `error` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `license_events_event_created_at_index` (`event`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `linen_items`
--

DROP TABLE IF EXISTS `linen_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `linen_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `initial_stock` int unsigned NOT NULL DEFAULT '0',
  `current_stock` int unsigned NOT NULL DEFAULT '0',
  `damaged` int unsigned NOT NULL DEFAULT '0',
  `last_audit_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `linen_items_property_id_foreign` (`property_id`),
  CONSTRAINT `linen_items_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `linen_transactions`
--

DROP TABLE IF EXISTS `linen_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `linen_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `linen_item_id` bigint unsigned NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int unsigned NOT NULL,
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `staff_id` bigint unsigned DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `linen_transactions_property_id_foreign` (`property_id`),
  KEY `linen_transactions_linen_item_id_foreign` (`linen_item_id`),
  KEY `linen_transactions_staff_id_foreign` (`staff_id`),
  CONSTRAINT `linen_transactions_linen_item_id_foreign` FOREIGN KEY (`linen_item_id`) REFERENCES `linen_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `linen_transactions_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `linen_transactions_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `local_licenses`
--

DROP TABLE IF EXISTS `local_licenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `local_licenses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `license_key_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token_encrypted` text COLLATE utf8mb4_unicode_ci,
  `fingerprint` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `install_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paired_at` timestamp NULL DEFAULT NULL,
  `last_heartbeat_attempt_at` timestamp NULL DEFAULT NULL,
  `last_heartbeat_success_at` timestamp NULL DEFAULT NULL,
  `grace_until` timestamp NULL DEFAULT NULL,
  `valid_until` timestamp NULL DEFAULT NULL,
  `plan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `features` json DEFAULT NULL,
  `max_rooms` int unsigned DEFAULT NULL,
  `max_users` int unsigned DEFAULT NULL,
  `max_properties` int unsigned DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaired',
  `degrade_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lost_and_found`
--

DROP TABLE IF EXISTS `lost_and_found`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lost_and_found` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `room_id` bigint unsigned DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `found_location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `found_date` date NOT NULL,
  `found_by_user_id` bigint unsigned DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'stored',
  `claimed_by_guest_id` bigint unsigned DEFAULT NULL,
  `claimed_date` date DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lost_and_found_property_id_foreign` (`property_id`),
  KEY `lost_and_found_room_id_foreign` (`room_id`),
  KEY `lost_and_found_found_by_user_id_foreign` (`found_by_user_id`),
  KEY `lost_and_found_claimed_by_guest_id_foreign` (`claimed_by_guest_id`),
  CONSTRAINT `lost_and_found_claimed_by_guest_id_foreign` FOREIGN KEY (`claimed_by_guest_id`) REFERENCES `guests` (`id`),
  CONSTRAINT `lost_and_found_found_by_user_id_foreign` FOREIGN KEY (`found_by_user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `lost_and_found_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lost_and_found_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `loyalty_members`
--

DROP TABLE IF EXISTS `loyalty_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loyalty_members` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `guest_id` bigint unsigned NOT NULL,
  `membership_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tier_id` bigint unsigned DEFAULT NULL,
  `points_balance` int unsigned NOT NULL DEFAULT '0',
  `lifetime_points` int unsigned NOT NULL DEFAULT '0',
  `enrolled_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tier_expires_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `loyalty_members_guest_id_unique` (`guest_id`),
  UNIQUE KEY `loyalty_members_membership_no_unique` (`membership_no`),
  KEY `loyalty_members_property_id_foreign` (`property_id`),
  KEY `loyalty_members_tier_id_foreign` (`tier_id`),
  CONSTRAINT `loyalty_members_guest_id_foreign` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`),
  CONSTRAINT `loyalty_members_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `loyalty_members_tier_id_foreign` FOREIGN KEY (`tier_id`) REFERENCES `loyalty_tiers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `loyalty_tiers`
--

DROP TABLE IF EXISTS `loyalty_tiers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loyalty_tiers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `points_threshold` int unsigned NOT NULL DEFAULT '0',
  `benefits` json DEFAULT NULL,
  `rate_discount_pct` decimal(6,3) NOT NULL DEFAULT '0.000',
  `display_order` smallint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `loyalty_tiers_property_id_slug_unique` (`property_id`,`slug`),
  CONSTRAINT `loyalty_tiers_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `loyalty_transactions`
--

DROP TABLE IF EXISTS `loyalty_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loyalty_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `member_id` bigint unsigned NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `points` int NOT NULL,
  `source_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_id` bigint unsigned DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `loyalty_transactions_member_id_foreign` (`member_id`),
  CONSTRAINT `loyalty_transactions_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `loyalty_members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `marketing_campaigns`
--

DROP TABLE IF EXISTS `marketing_campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `marketing_campaigns` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `channel` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `template_id` bigint unsigned DEFAULT NULL,
  `audience_filter` json DEFAULT NULL,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `recipients_count` int unsigned NOT NULL DEFAULT '0',
  `sent_count` int unsigned NOT NULL DEFAULT '0',
  `opened_count` int unsigned NOT NULL DEFAULT '0',
  `clicked_count` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `marketing_campaigns_property_id_foreign` (`property_id`),
  KEY `marketing_campaigns_template_id_foreign` (`template_id`),
  CONSTRAINT `marketing_campaigns_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `marketing_campaigns_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `message_templates` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `message_templates`
--

DROP TABLE IF EXISTS `message_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `message_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `channel` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `variables` json DEFAULT NULL,
  `locale` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'id',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `message_templates_property_id_foreign` (`property_id`),
  CONSTRAINT `message_templates_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `message_threads`
--

DROP TABLE IF EXISTS `message_threads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `message_threads` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `guest_id` bigint unsigned DEFAULT NULL,
  `reservation_id` bigint unsigned DEFAULT NULL,
  `channel` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `external_thread_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `assignee_id` bigint unsigned DEFAULT NULL,
  `sentiment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_message_at` timestamp NULL DEFAULT NULL,
  `unread_count` smallint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `message_threads_property_id_foreign` (`property_id`),
  KEY `message_threads_guest_id_foreign` (`guest_id`),
  KEY `message_threads_reservation_id_foreign` (`reservation_id`),
  KEY `message_threads_assignee_id_foreign` (`assignee_id`),
  CONSTRAINT `message_threads_assignee_id_foreign` FOREIGN KEY (`assignee_id`) REFERENCES `users` (`id`),
  CONSTRAINT `message_threads_guest_id_foreign` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`),
  CONSTRAINT `message_threads_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `message_threads_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `thread_id` bigint unsigned NOT NULL,
  `direction` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `attachments` json DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'delivered',
  `raw_payload` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `messages_thread_id_foreign` (`thread_id`),
  CONSTRAINT `messages_thread_id_foreign` FOREIGN KEY (`thread_id`) REFERENCES `message_threads` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `night_audits`
--

DROP TABLE IF EXISTS `night_audits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `night_audits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `audit_date` date NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `run_by_user_id` bigint unsigned DEFAULT NULL,
  `summary` json DEFAULT NULL,
  `error_log` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `night_audits_property_id_audit_date_unique` (`property_id`,`audit_date`),
  KEY `night_audits_run_by_user_id_foreign` (`run_by_user_id`),
  KEY `night_audits_audit_date_index` (`audit_date`),
  CONSTRAINT `night_audits_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `night_audits_run_by_user_id_foreign` FOREIGN KEY (`run_by_user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `notification_logs`
--

DROP TABLE IF EXISTS `notification_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `channel` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notifiable_id` bigint unsigned DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `provider_used` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_message_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `error` text COLLATE utf8mb4_unicode_ci,
  `idempotency_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `metadata` json DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `notification_logs_idempotency_key_unique` (`idempotency_key`),
  KEY `notification_logs_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`),
  KEY `notification_logs_property_id_event_status_index` (`property_id`,`event`,`status`),
  CONSTRAINT `notification_logs_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nsfp_pools`
--

DROP TABLE IF EXISTS `nsfp_pools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nsfp_pools` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `range_start` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `range_end` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `current_serial` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `nsfp_pools_property_id_foreign` (`property_id`),
  CONSTRAINT `nsfp_pools_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ota_virtual_cards`
--

DROP TABLE IF EXISTS `ota_virtual_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ota_virtual_cards` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `reservation_id` bigint unsigned NOT NULL,
  `channel_id` bigint unsigned DEFAULT NULL,
  `card_holder_masked` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `card_number_encrypted` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `card_brand` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expires_on` date NOT NULL,
  `cvv_encrypted` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount_authorized` decimal(14,2) NOT NULL,
  `amount_charged` decimal(14,2) NOT NULL DEFAULT '0.00',
  `valid_from` date NOT NULL,
  `valid_until` date NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `charge_attempts` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ota_virtual_cards_reservation_id_foreign` (`reservation_id`),
  KEY `ota_virtual_cards_channel_id_foreign` (`channel_id`),
  KEY `ota_virtual_cards_property_id_status_index` (`property_id`,`status`),
  CONSTRAINT `ota_virtual_cards_channel_id_foreign` FOREIGN KEY (`channel_id`) REFERENCES `channels` (`id`),
  CONSTRAINT `ota_virtual_cards_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ota_virtual_cards_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `out_of_order_periods`
--

DROP TABLE IF EXISTS `out_of_order_periods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `out_of_order_periods` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `room_id` bigint unsigned NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `work_order_id` bigint unsigned DEFAULT NULL,
  `created_by_user_id` bigint unsigned DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `cleared_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `out_of_order_periods_room_id_foreign` (`room_id`),
  KEY `out_of_order_periods_work_order_id_foreign` (`work_order_id`),
  KEY `out_of_order_periods_created_by_user_id_foreign` (`created_by_user_id`),
  KEY `out_of_order_periods_property_id_from_date_to_date_index` (`property_id`,`from_date`,`to_date`),
  CONSTRAINT `out_of_order_periods_created_by_user_id_foreign` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `out_of_order_periods_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `out_of_order_periods_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`),
  CONSTRAINT `out_of_order_periods_work_order_id_foreign` FOREIGN KEY (`work_order_id`) REFERENCES `work_orders` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `owner_statements`
--

DROP TABLE IF EXISTS `owner_statements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `owner_statements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `room_id` bigint unsigned DEFAULT NULL,
  `owner_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `year` smallint unsigned NOT NULL,
  `month` tinyint unsigned NOT NULL,
  `gross_revenue` decimal(14,2) NOT NULL,
  `mgmt_fee_pct` decimal(6,3) NOT NULL DEFAULT '20.000',
  `mgmt_fee_amount` decimal(14,2) NOT NULL,
  `expenses_total` decimal(14,2) NOT NULL DEFAULT '0.00',
  `net_payable_to_owner` decimal(14,2) NOT NULL,
  `breakdown` json DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `sent_at` timestamp NULL DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `owner_statements_property_id_room_id_year_month_unique` (`property_id`,`room_id`,`year`,`month`),
  KEY `owner_statements_room_id_foreign` (`room_id`),
  CONSTRAINT `owner_statements_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `owner_statements_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `payslips`
--

DROP TABLE IF EXISTS `payslips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payslips` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `year` smallint unsigned NOT NULL,
  `month` tinyint unsigned NOT NULL,
  `basic_salary` decimal(14,2) NOT NULL,
  `allowances_total` decimal(14,2) NOT NULL,
  `overtime_pay` decimal(14,2) NOT NULL DEFAULT '0.00',
  `service_charge` decimal(14,2) NOT NULL DEFAULT '0.00',
  `gross_total` decimal(14,2) NOT NULL,
  `bpjs_kesehatan_employee` decimal(14,2) NOT NULL DEFAULT '0.00',
  `bpjs_tk_employee` decimal(14,2) NOT NULL DEFAULT '0.00',
  `pph_21` decimal(14,2) NOT NULL DEFAULT '0.00',
  `deductions_total` decimal(14,2) NOT NULL,
  `net_salary` decimal(14,2) NOT NULL,
  `breakdown` json DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payslips_employee_id_year_month_unique` (`employee_id`,`year`,`month`),
  CONSTRAINT `payslips_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pb1_rates`
--

DROP TABLE IF EXISTS `pb1_rates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pb1_rates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `region_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `region_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rate` decimal(6,3) NOT NULL,
  `effective_from` date NOT NULL,
  `effective_until` date DEFAULT NULL,
  `source_law` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pb1_rates_region_code_effective_from_index` (`region_code`,`effective_from`),
  KEY `pb1_rates_region_code_index` (`region_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `performance_reviews`
--

DROP TABLE IF EXISTS `performance_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `performance_reviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `reviewer_id` bigint unsigned NOT NULL,
  `review_date` date NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `scores` json NOT NULL,
  `strengths` text COLLATE utf8mb4_unicode_ci,
  `improvements` text COLLATE utf8mb4_unicode_ci,
  `overall_rating` tinyint unsigned DEFAULT NULL,
  `goals` json DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `acknowledged_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `performance_reviews_property_id_foreign` (`property_id`),
  KEY `performance_reviews_employee_id_foreign` (`employee_id`),
  KEY `performance_reviews_reviewer_id_foreign` (`reviewer_id`),
  CONSTRAINT `performance_reviews_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `performance_reviews_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `performance_reviews_reviewer_id_foreign` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `plans`
--

DROP TABLE IF EXISTS `plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `monthly_price_idr` decimal(14,2) DEFAULT NULL,
  `yearly_price_idr` decimal(14,2) DEFAULT NULL,
  `per_room_price_idr` decimal(14,2) DEFAULT NULL,
  `max_rooms` int unsigned DEFAULT NULL,
  `max_users` int unsigned DEFAULT NULL,
  `max_properties` int unsigned NOT NULL DEFAULT '1',
  `features` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_default_signup` tinyint(1) NOT NULL DEFAULT '0',
  `display_order` smallint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plans_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `points_of_interest`
--

DROP TABLE IF EXISTS `points_of_interest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `points_of_interest` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned DEFAULT NULL,
  `landmark_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `distance_meters` smallint unsigned DEFAULT NULL,
  `rating` tinyint unsigned DEFAULT NULL,
  `photos` json DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `opening_hours` json DEFAULT NULL,
  `is_recommended` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `points_of_interest_slug_unique` (`slug`),
  KEY `points_of_interest_landmark_id_foreign` (`landmark_id`),
  KEY `points_of_interest_property_id_index` (`property_id`),
  CONSTRAINT `points_of_interest_landmark_id_foreign` FOREIGN KEY (`landmark_id`) REFERENCES `landmarks` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pos_categories`
--

DROP TABLE IF EXISTS `pos_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pos_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `outlet_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_order` smallint unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pos_categories_outlet_id_foreign` (`outlet_id`),
  CONSTRAINT `pos_categories_outlet_id_foreign` FOREIGN KEY (`outlet_id`) REFERENCES `pos_outlets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pos_laundry_orders`
--

DROP TABLE IF EXISTS `pos_laundry_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pos_laundry_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `guest_id` bigint unsigned DEFAULT NULL,
  `reservation_id` bigint unsigned DEFAULT NULL,
  `room_id` bigint unsigned DEFAULT NULL,
  `order_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'received',
  `items` json NOT NULL,
  `total_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `payment_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `received_by` bigint unsigned DEFAULT NULL,
  `delivered_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pos_laundry_orders_order_number_unique` (`order_number`),
  KEY `pos_laundry_orders_property_id_foreign` (`property_id`),
  KEY `pos_laundry_orders_guest_id_foreign` (`guest_id`),
  KEY `pos_laundry_orders_reservation_id_foreign` (`reservation_id`),
  KEY `pos_laundry_orders_room_id_foreign` (`room_id`),
  KEY `pos_laundry_orders_received_by_foreign` (`received_by`),
  KEY `pos_laundry_orders_delivered_by_foreign` (`delivered_by`),
  CONSTRAINT `pos_laundry_orders_delivered_by_foreign` FOREIGN KEY (`delivered_by`) REFERENCES `users` (`id`),
  CONSTRAINT `pos_laundry_orders_guest_id_foreign` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`),
  CONSTRAINT `pos_laundry_orders_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pos_laundry_orders_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`),
  CONSTRAINT `pos_laundry_orders_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`),
  CONSTRAINT `pos_laundry_orders_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pos_menu_items`
--

DROP TABLE IF EXISTS `pos_menu_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pos_menu_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `outlet_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(12,2) NOT NULL,
  `is_taxable` tinyint(1) NOT NULL DEFAULT '1',
  `tax_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PPN_OUT',
  `cogs` decimal(12,2) DEFAULT NULL,
  `modifiers` json DEFAULT NULL,
  `photos` json DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pos_menu_items_outlet_id_code_unique` (`outlet_id`,`code`),
  KEY `pos_menu_items_category_id_foreign` (`category_id`),
  CONSTRAINT `pos_menu_items_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `pos_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pos_menu_items_outlet_id_foreign` FOREIGN KEY (`outlet_id`) REFERENCES `pos_outlets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pos_order_items`
--

DROP TABLE IF EXISTS `pos_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pos_order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `menu_item_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `qty` int unsigned NOT NULL DEFAULT '1',
  `modifiers` json DEFAULT NULL,
  `subtotal` decimal(14,2) NOT NULL,
  `sent_to_kitchen` tinyint(1) NOT NULL DEFAULT '0',
  `sent_at` timestamp NULL DEFAULT NULL,
  `is_void` tinyint(1) NOT NULL DEFAULT '0',
  `void_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pos_order_items_order_id_foreign` (`order_id`),
  KEY `pos_order_items_menu_item_id_foreign` (`menu_item_id`),
  CONSTRAINT `pos_order_items_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `pos_menu_items` (`id`),
  CONSTRAINT `pos_order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `pos_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pos_order_payments`
--

DROP TABLE IF EXISTS `pos_order_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pos_order_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pos_order_payments_order_id_foreign` (`order_id`),
  CONSTRAINT `pos_order_payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `pos_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pos_orders`
--

DROP TABLE IF EXISTS `pos_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pos_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `outlet_id` bigint unsigned NOT NULL,
  `property_id` bigint unsigned NOT NULL,
  `table_id` bigint unsigned DEFAULT NULL,
  `order_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'dine_in',
  `folio_id` bigint unsigned DEFAULT NULL,
  `reservation_id` bigint unsigned DEFAULT NULL,
  `server_id` bigint unsigned DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `subtotal` decimal(14,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `service_charge` decimal(14,2) NOT NULL DEFAULT '0.00',
  `tax_total` decimal(14,2) NOT NULL DEFAULT '0.00',
  `grand_total` decimal(14,2) NOT NULL DEFAULT '0.00',
  `paid_total` decimal(14,2) NOT NULL DEFAULT '0.00',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pos_orders_outlet_id_foreign` (`outlet_id`),
  KEY `pos_orders_property_id_foreign` (`property_id`),
  KEY `pos_orders_table_id_foreign` (`table_id`),
  KEY `pos_orders_folio_id_foreign` (`folio_id`),
  KEY `pos_orders_reservation_id_foreign` (`reservation_id`),
  KEY `pos_orders_server_id_foreign` (`server_id`),
  KEY `pos_orders_order_no_index` (`order_no`),
  CONSTRAINT `pos_orders_folio_id_foreign` FOREIGN KEY (`folio_id`) REFERENCES `folios` (`id`),
  CONSTRAINT `pos_orders_outlet_id_foreign` FOREIGN KEY (`outlet_id`) REFERENCES `pos_outlets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pos_orders_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`),
  CONSTRAINT `pos_orders_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`),
  CONSTRAINT `pos_orders_server_id_foreign` FOREIGN KEY (`server_id`) REFERENCES `users` (`id`),
  CONSTRAINT `pos_orders_table_id_foreign` FOREIGN KEY (`table_id`) REFERENCES `pos_tables` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pos_outlets`
--

DROP TABLE IF EXISTS `pos_outlets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pos_outlets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'restaurant',
  `charge_to_room_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `takeaway_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `config` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pos_outlets_property_id_code_unique` (`property_id`,`code`),
  CONSTRAINT `pos_outlets_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pos_recipes`
--

DROP TABLE IF EXISTS `pos_recipes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pos_recipes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `menu_item_id` bigint unsigned NOT NULL,
  `stock_item_id` bigint unsigned NOT NULL,
  `qty_per_serving` decimal(14,4) NOT NULL,
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pos_recipes_menu_item_id_stock_item_id_unique` (`menu_item_id`,`stock_item_id`),
  KEY `pos_recipes_stock_item_id_foreign` (`stock_item_id`),
  CONSTRAINT `pos_recipes_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `pos_menu_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pos_recipes_stock_item_id_foreign` FOREIGN KEY (`stock_item_id`) REFERENCES `stock_items` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pos_tables`
--

DROP TABLE IF EXISTS `pos_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pos_tables` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `outlet_id` bigint unsigned NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `seats` tinyint unsigned NOT NULL DEFAULT '2',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pos_tables_outlet_id_foreign` (`outlet_id`),
  CONSTRAINT `pos_tables_outlet_id_foreign` FOREIGN KEY (`outlet_id`) REFERENCES `pos_outlets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `preventive_maintenance_schedules`
--

DROP TABLE IF EXISTS `preventive_maintenance_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `preventive_maintenance_schedules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `asset_id` bigint unsigned NOT NULL,
  `frequency` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `next_due_at` date NOT NULL,
  `last_done_at` date DEFAULT NULL,
  `checklist` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `preventive_maintenance_schedules_property_id_foreign` (`property_id`),
  KEY `preventive_maintenance_schedules_asset_id_foreign` (`asset_id`),
  CONSTRAINT `preventive_maintenance_schedules_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`),
  CONSTRAINT `preventive_maintenance_schedules_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `promo_code_usages`
--

DROP TABLE IF EXISTS `promo_code_usages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `promo_code_usages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `promo_code_id` bigint unsigned NOT NULL,
  `reservation_id` bigint unsigned DEFAULT NULL,
  `guest_id` bigint unsigned DEFAULT NULL,
  `property_id` bigint unsigned NOT NULL,
  `discount_applied` decimal(14,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'IDR',
  `used_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `promo_code_usages_reservation_id_foreign` (`reservation_id`),
  KEY `promo_code_usages_guest_id_foreign` (`guest_id`),
  KEY `promo_code_usages_property_id_foreign` (`property_id`),
  KEY `promo_code_usages_promo_code_id_used_at_index` (`promo_code_id`,`used_at`),
  CONSTRAINT `promo_code_usages_guest_id_foreign` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`) ON DELETE SET NULL,
  CONSTRAINT `promo_code_usages_promo_code_id_foreign` FOREIGN KEY (`promo_code_id`) REFERENCES `promo_codes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `promo_code_usages_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `promo_code_usages_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `promo_codes`
--

DROP TABLE IF EXISTS `promo_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `promo_codes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount_value` decimal(14,2) NOT NULL,
  `valid_from` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `usage_limit` int unsigned DEFAULT NULL,
  `usage_count` int unsigned NOT NULL DEFAULT '0',
  `rules` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `promo_codes_code_unique` (`code`),
  KEY `promo_codes_property_id_foreign` (`property_id`),
  CONSTRAINT `promo_codes_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `properties`
--

DROP TABLE IF EXISTS `properties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `properties` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `brand` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `legal_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `npwp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_pkp` tinyint(1) NOT NULL DEFAULT '0',
  `nsfp_series` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `region_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ID',
  `province` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `district` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `timezone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Asia/Jakarta',
  `currency_default` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'IDR',
  `locale_default` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'id',
  `star_rating` tinyint unsigned DEFAULT NULL,
  `total_rooms` smallint unsigned NOT NULL DEFAULT '0',
  `check_in_time` time NOT NULL DEFAULT '14:00:00',
  `check_out_time` time NOT NULL DEFAULT '12:00:00',
  `owner_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `theme` json DEFAULT NULL,
  `settings` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `properties_slug_unique` (`slug`),
  KEY `properties_region_code_index` (`region_code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `property_translations`
--

DROP TABLE IF EXISTS `property_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `property_translations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `locale` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `property_translations_property_id_locale_key_unique` (`property_id`,`locale`,`key`),
  CONSTRAINT `property_translations_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `provider_feature_assignments`
--

DROP TABLE IF EXISTS `provider_feature_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `provider_feature_assignments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `feature` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_id` bigint unsigned NOT NULL,
  `default_model` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `config` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `provider_feature_assignments_property_id_feature_unique` (`property_id`,`feature`),
  KEY `provider_feature_assignments_provider_id_foreign` (`provider_id`),
  CONSTRAINT `provider_feature_assignments_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `provider_feature_assignments_provider_id_foreign` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `providers`
--

DROP TABLE IF EXISTS `providers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `providers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `integration_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_format` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `base_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `api_key_encrypted` text COLLATE utf8mb4_unicode_ci,
  `secret_encrypted` text COLLATE utf8mb4_unicode_ci,
  `extra_headers` json DEFAULT NULL,
  `extra_config` json DEFAULT NULL,
  `default_model` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `capabilities` json DEFAULT NULL,
  `pricing` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `display_order` smallint unsigned NOT NULL DEFAULT '0',
  `test_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_tested_at` timestamp NULL DEFAULT NULL,
  `test_message` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `providers_property_id_integration_type_slug_unique` (`property_id`,`integration_type`,`slug`),
  KEY `providers_property_id_integration_type_is_active_index` (`property_id`,`integration_type`,`is_active`),
  CONSTRAINT `providers_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `purchase_order_lines`
--

DROP TABLE IF EXISTS `purchase_order_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_order_lines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `po_id` bigint unsigned NOT NULL,
  `stock_item_id` bigint unsigned DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(14,3) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `total` decimal(14,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_order_lines_po_id_foreign` (`po_id`),
  KEY `purchase_order_lines_stock_item_id_foreign` (`stock_item_id`),
  CONSTRAINT `purchase_order_lines_po_id_foreign` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_order_lines_stock_item_id_foreign` FOREIGN KEY (`stock_item_id`) REFERENCES `stock_items` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `purchase_orders`
--

DROP TABLE IF EXISTS `purchase_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `po_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `vendor_id` bigint unsigned NOT NULL,
  `pr_id` bigint unsigned DEFAULT NULL,
  `ordered_by` bigint unsigned NOT NULL,
  `order_date` date NOT NULL,
  `expected_date` date DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `total` decimal(14,2) NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_orders_po_number_unique` (`po_number`),
  KEY `purchase_orders_property_id_foreign` (`property_id`),
  KEY `purchase_orders_vendor_id_foreign` (`vendor_id`),
  KEY `purchase_orders_pr_id_foreign` (`pr_id`),
  KEY `purchase_orders_ordered_by_foreign` (`ordered_by`),
  CONSTRAINT `purchase_orders_ordered_by_foreign` FOREIGN KEY (`ordered_by`) REFERENCES `users` (`id`),
  CONSTRAINT `purchase_orders_pr_id_foreign` FOREIGN KEY (`pr_id`) REFERENCES `purchase_requests` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_orders_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_orders_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `ap_suppliers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `purchase_request_lines`
--

DROP TABLE IF EXISTS `purchase_request_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_request_lines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pr_id` bigint unsigned NOT NULL,
  `stock_item_id` bigint unsigned DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(14,3) NOT NULL,
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pcs',
  `estimated_price` decimal(14,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_request_lines_pr_id_foreign` (`pr_id`),
  KEY `purchase_request_lines_stock_item_id_foreign` (`stock_item_id`),
  CONSTRAINT `purchase_request_lines_pr_id_foreign` FOREIGN KEY (`pr_id`) REFERENCES `purchase_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_request_lines_stock_item_id_foreign` FOREIGN KEY (`stock_item_id`) REFERENCES `stock_items` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `purchase_requests`
--

DROP TABLE IF EXISTS `purchase_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `pr_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `requested_by` bigint unsigned NOT NULL,
  `department` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `required_date` date DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_requests_pr_number_unique` (`pr_number`),
  KEY `purchase_requests_property_id_foreign` (`property_id`),
  KEY `purchase_requests_requested_by_foreign` (`requested_by`),
  CONSTRAINT `purchase_requests_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_requests_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rate_overrides`
--

DROP TABLE IF EXISTS `rate_overrides`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rate_overrides` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `room_type_id` bigint unsigned NOT NULL,
  `rate_plan_id` bigint unsigned DEFAULT NULL,
  `channel_id` bigint unsigned DEFAULT NULL,
  `override_date` date NOT NULL,
  `price` decimal(14,2) NOT NULL,
  `min_price` decimal(14,2) DEFAULT NULL,
  `max_price` decimal(14,2) DEFAULT NULL,
  `min_stay` tinyint unsigned NOT NULL DEFAULT '1',
  `max_stay` tinyint unsigned DEFAULT NULL,
  `closed_to_arrival` tinyint(1) NOT NULL DEFAULT '0',
  `closed_to_departure` tinyint(1) NOT NULL DEFAULT '0',
  `stop_sell` tinyint(1) NOT NULL DEFAULT '0',
  `source` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `created_by_user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ro_rt_ch_date_unique` (`property_id`,`room_type_id`,`channel_id`,`override_date`),
  KEY `rate_overrides_room_type_id_foreign` (`room_type_id`),
  KEY `rate_overrides_rate_plan_id_foreign` (`rate_plan_id`),
  KEY `rate_overrides_channel_id_foreign` (`channel_id`),
  KEY `rate_overrides_created_by_user_id_foreign` (`created_by_user_id`),
  KEY `rate_overrides_property_id_override_date_index` (`property_id`,`override_date`),
  CONSTRAINT `rate_overrides_channel_id_foreign` FOREIGN KEY (`channel_id`) REFERENCES `channels` (`id`) ON DELETE SET NULL,
  CONSTRAINT `rate_overrides_created_by_user_id_foreign` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `rate_overrides_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rate_overrides_rate_plan_id_foreign` FOREIGN KEY (`rate_plan_id`) REFERENCES `rate_plans` (`id`) ON DELETE SET NULL,
  CONSTRAINT `rate_overrides_room_type_id_foreign` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rate_plans`
--

DROP TABLE IF EXISTS `rate_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rate_plans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_refundable` tinyint(1) NOT NULL DEFAULT '1',
  `breakfast_included` tinyint(1) NOT NULL DEFAULT '0',
  `cancellation_policy` json DEFAULT NULL,
  `is_derived` tinyint(1) NOT NULL DEFAULT '0',
  `parent_rate_plan_id` bigint unsigned DEFAULT NULL,
  `cancellation_policy_id` bigint unsigned DEFAULT NULL,
  `derive_modifier_pct` decimal(6,3) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rate_plans_property_id_code_unique` (`property_id`,`code`),
  KEY `rate_plans_parent_rate_plan_id_foreign` (`parent_rate_plan_id`),
  KEY `rate_plans_cancellation_policy_id_foreign` (`cancellation_policy_id`),
  CONSTRAINT `rate_plans_cancellation_policy_id_foreign` FOREIGN KEY (`cancellation_policy_id`) REFERENCES `cancellation_policies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `rate_plans_parent_rate_plan_id_foreign` FOREIGN KEY (`parent_rate_plan_id`) REFERENCES `rate_plans` (`id`) ON DELETE SET NULL,
  CONSTRAINT `rate_plans_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rate_shopper_snapshots`
--

DROP TABLE IF EXISTS `rate_shopper_snapshots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rate_shopper_snapshots` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `provider_id` bigint unsigned DEFAULT NULL,
  `check_date` date NOT NULL,
  `shopped_for_date` date NOT NULL,
  `competitor_set` json NOT NULL,
  `our_rate` decimal(14,2) DEFAULT NULL,
  `avg_competitor_rate` decimal(14,2) DEFAULT NULL,
  `rate_index` decimal(6,3) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rate_shopper_snapshots_provider_id_foreign` (`provider_id`),
  KEY `rate_shopper_snapshots_property_id_shopped_for_date_index` (`property_id`,`shopped_for_date`),
  CONSTRAINT `rate_shopper_snapshots_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rate_shopper_snapshots_provider_id_foreign` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rates`
--

DROP TABLE IF EXISTS `rates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `room_type_id` bigint unsigned NOT NULL,
  `rate_plan_id` bigint unsigned NOT NULL,
  `date` date NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'IDR',
  `min_los` smallint unsigned NOT NULL DEFAULT '1',
  `max_los` smallint unsigned DEFAULT NULL,
  `cta` tinyint(1) NOT NULL DEFAULT '0',
  `ctd` tinyint(1) NOT NULL DEFAULT '0',
  `closed` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rates_property_id_room_type_id_rate_plan_id_date_unique` (`property_id`,`room_type_id`,`rate_plan_id`,`date`),
  KEY `rates_room_type_id_foreign` (`room_type_id`),
  KEY `rates_rate_plan_id_foreign` (`rate_plan_id`),
  KEY `rates_property_id_date_index` (`property_id`,`date`),
  CONSTRAINT `rates_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rates_rate_plan_id_foreign` FOREIGN KEY (`rate_plan_id`) REFERENCES `rate_plans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rates_room_type_id_foreign` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13172 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `referral_codes`
--

DROP TABLE IF EXISTS `referral_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `referral_codes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `owner_guest_id` bigint unsigned NOT NULL,
  `code` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL,
  `referrer_reward_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `referee_discount_pct` decimal(6,3) NOT NULL DEFAULT '0.000',
  `uses_count` int unsigned NOT NULL DEFAULT '0',
  `uses_limit` int unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `referral_codes_code_unique` (`code`),
  KEY `referral_codes_property_id_foreign` (`property_id`),
  KEY `referral_codes_owner_guest_id_foreign` (`owner_guest_id`),
  CONSTRAINT `referral_codes_owner_guest_id_foreign` FOREIGN KEY (`owner_guest_id`) REFERENCES `guests` (`id`),
  CONSTRAINT `referral_codes_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `referral_redemptions`
--

DROP TABLE IF EXISTS `referral_redemptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `referral_redemptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `referral_code_id` bigint unsigned NOT NULL,
  `reservation_id` bigint unsigned NOT NULL,
  `discount_applied` decimal(14,2) NOT NULL,
  `reward_credited` decimal(14,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `referral_redemptions_referral_code_id_foreign` (`referral_code_id`),
  KEY `referral_redemptions_reservation_id_foreign` (`reservation_id`),
  CONSTRAINT `referral_redemptions_referral_code_id_foreign` FOREIGN KEY (`referral_code_id`) REFERENCES `referral_codes` (`id`),
  CONSTRAINT `referral_redemptions_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reservation_addons`
--

DROP TABLE IF EXISTS `reservation_addons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservation_addons` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reservation_id` bigint unsigned NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qty` int unsigned NOT NULL DEFAULT '1',
  `unit_price` decimal(12,2) NOT NULL,
  `subtotal` decimal(14,2) NOT NULL,
  `date_apply` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reservation_addons_reservation_id_foreign` (`reservation_id`),
  CONSTRAINT `reservation_addons_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reservation_guests`
--

DROP TABLE IF EXISTS `reservation_guests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservation_guests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reservation_room_id` bigint unsigned NOT NULL,
  `guest_id` bigint unsigned NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reservation_guests_reservation_room_id_foreign` (`reservation_room_id`),
  KEY `reservation_guests_guest_id_foreign` (`guest_id`),
  CONSTRAINT `reservation_guests_guest_id_foreign` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`),
  CONSTRAINT `reservation_guests_reservation_room_id_foreign` FOREIGN KEY (`reservation_room_id`) REFERENCES `reservation_rooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reservation_rooms`
--

DROP TABLE IF EXISTS `reservation_rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservation_rooms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reservation_id` bigint unsigned NOT NULL,
  `room_type_id` bigint unsigned NOT NULL,
  `rate_plan_id` bigint unsigned NOT NULL,
  `room_id` bigint unsigned DEFAULT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `adults` smallint unsigned NOT NULL DEFAULT '1',
  `children` smallint unsigned NOT NULL DEFAULT '0',
  `subtotal` decimal(14,2) NOT NULL,
  `per_night_rates` json DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'booked',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reservation_rooms_reservation_id_foreign` (`reservation_id`),
  KEY `reservation_rooms_room_type_id_foreign` (`room_type_id`),
  KEY `reservation_rooms_rate_plan_id_foreign` (`rate_plan_id`),
  KEY `reservation_rooms_room_id_foreign` (`room_id`),
  KEY `reservation_rooms_check_in_index` (`check_in`),
  CONSTRAINT `reservation_rooms_rate_plan_id_foreign` FOREIGN KEY (`rate_plan_id`) REFERENCES `rate_plans` (`id`),
  CONSTRAINT `reservation_rooms_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservation_rooms_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`),
  CONSTRAINT `reservation_rooms_room_type_id_foreign` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1001 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `ref` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `primary_guest_id` bigint unsigned NOT NULL,
  `company_id` bigint unsigned DEFAULT NULL,
  `travel_agent_id` bigint unsigned DEFAULT NULL,
  `source` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'direct',
  `source_ref` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `nights` smallint unsigned NOT NULL,
  `adults` smallint unsigned NOT NULL DEFAULT '1',
  `children` smallint unsigned NOT NULL DEFAULT '0',
  `children_ages` json DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'confirmed',
  `total_room` decimal(14,2) NOT NULL DEFAULT '0.00',
  `total_addons` decimal(14,2) NOT NULL DEFAULT '0.00',
  `service_charge` decimal(14,2) NOT NULL DEFAULT '0.00',
  `tax_total` decimal(14,2) NOT NULL DEFAULT '0.00',
  `grand_total` decimal(14,2) NOT NULL DEFAULT '0.00',
  `balance` decimal(14,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'IDR',
  `promo_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `special_requests` text COLLATE utf8mb4_unicode_ci,
  `notes_internal` text COLLATE utf8mb4_unicode_ci,
  `arrival_time` timestamp NULL DEFAULT NULL,
  `pre_checkin_complete` tinyint(1) NOT NULL DEFAULT '0',
  `checked_in_at` timestamp NULL DEFAULT NULL,
  `checked_out_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancellation_reason` text COLLATE utf8mb4_unicode_ci,
  `cancellation_penalty` decimal(14,2) NOT NULL DEFAULT '0.00',
  `created_by_user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reservations_ref_unique` (`ref`),
  KEY `reservations_primary_guest_id_foreign` (`primary_guest_id`),
  KEY `reservations_company_id_foreign` (`company_id`),
  KEY `reservations_travel_agent_id_foreign` (`travel_agent_id`),
  KEY `reservations_created_by_user_id_foreign` (`created_by_user_id`),
  KEY `reservations_property_id_check_in_index` (`property_id`,`check_in`),
  KEY `reservations_property_id_status_index` (`property_id`,`status`),
  KEY `reservations_property_id_source_index` (`property_id`,`source`),
  CONSTRAINT `reservations_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `reservations_created_by_user_id_foreign` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `reservations_primary_guest_id_foreign` FOREIGN KEY (`primary_guest_id`) REFERENCES `guests` (`id`),
  CONSTRAINT `reservations_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservations_travel_agent_id_foreign` FOREIGN KEY (`travel_agent_id`) REFERENCES `travel_agents` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1001 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `reservation_id` bigint unsigned DEFAULT NULL,
  `guest_id` bigint unsigned DEFAULT NULL,
  `rating` tinyint unsigned DEFAULT NULL,
  `category_ratings` json DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `source` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'internal',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reviews_property_id_foreign` (`property_id`),
  KEY `reviews_reservation_id_foreign` (`reservation_id`),
  KEY `reviews_guest_id_foreign` (`guest_id`),
  CONSTRAINT `reviews_guest_id_foreign` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`),
  CONSTRAINT `reviews_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `room_types`
--

DROP TABLE IF EXISTS `room_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `room_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `max_occupancy` smallint unsigned NOT NULL DEFAULT '2',
  `max_adults` smallint unsigned NOT NULL DEFAULT '2',
  `max_children` smallint unsigned NOT NULL DEFAULT '0',
  `extra_bed_capacity` smallint unsigned NOT NULL DEFAULT '0',
  `base_rate` decimal(12,2) NOT NULL DEFAULT '0.00',
  `amenities` json DEFAULT NULL,
  `photos` json DEFAULT NULL,
  `size_sqm` smallint unsigned DEFAULT NULL,
  `view` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bed_config` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smoking` tinyint(1) NOT NULL DEFAULT '0',
  `display_order` smallint unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_types_property_id_code_unique` (`property_id`,`code`),
  UNIQUE KEY `room_types_property_id_slug_unique` (`property_id`,`slug`),
  CONSTRAINT `room_types_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rooms`
--

DROP TABLE IF EXISTS `rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rooms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `room_type_id` bigint unsigned NOT NULL,
  `number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `floor` tinyint unsigned DEFAULT NULL,
  `view` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hk_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'clean',
  `fo_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'vacant',
  `is_smoking` tinyint(1) NOT NULL DEFAULT '0',
  `is_accessible` tinyint(1) NOT NULL DEFAULT '0',
  `features` json DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rooms_property_id_number_unique` (`property_id`,`number`),
  KEY `rooms_room_type_id_foreign` (`room_type_id`),
  KEY `rooms_property_id_hk_status_index` (`property_id`,`hk_status`),
  KEY `rooms_property_id_fo_status_index` (`property_id`,`fo_status`),
  CONSTRAINT `rooms_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rooms_room_type_id_foreign` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `seo_keywords`
--

DROP TABLE IF EXISTS `seo_keywords`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seo_keywords` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `keyword` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `current_rank` int unsigned DEFAULT NULL,
  `last_checked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `seo_keywords_property_id_foreign` (`property_id`),
  CONSTRAINT `seo_keywords_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `seo_pages`
--

DROP TABLE IF EXISTS `seo_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seo_pages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `route_pattern` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `h1` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `intro_html` longtext COLLATE utf8mb4_unicode_ci,
  `outro_html` longtext COLLATE utf8mb4_unicode_ci,
  `content_html` longtext COLLATE utf8mb4_unicode_ci,
  `faq_json` json DEFAULT NULL,
  `meta_json` json DEFAULT NULL,
  `locale` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'id',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `last_generated_at` timestamp NULL DEFAULT NULL,
  `regenerate_after` timestamp NULL DEFAULT NULL,
  `views_total` int unsigned NOT NULL DEFAULT '0',
  `views_30d` int unsigned NOT NULL DEFAULT '0',
  `conversions_30d` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seo_pages_url_unique` (`url`),
  KEY `seo_pages_property_id_status_index` (`property_id`,`status`),
  CONSTRAINT `seo_pages_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `seo_redirects`
--

DROP TABLE IF EXISTS `seo_redirects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seo_redirects` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `source_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `http_status` smallint unsigned NOT NULL DEFAULT '301',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seo_redirects_property_id_source_path_unique` (`property_id`,`source_path`),
  CONSTRAINT `seo_redirects_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `service_charge_distributions`
--

DROP TABLE IF EXISTS `service_charge_distributions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `service_charge_distributions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `year` smallint unsigned NOT NULL,
  `month` tinyint unsigned NOT NULL,
  `total_collected` decimal(14,2) NOT NULL,
  `admin_share_pct` decimal(6,3) NOT NULL DEFAULT '0.000',
  `staff_share_amount` decimal(14,2) NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `distributed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `service_charge_distributions_property_id_year_month_unique` (`property_id`,`year`,`month`),
  CONSTRAINT `service_charge_distributions_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shift_schedules`
--

DROP TABLE IF EXISTS `shift_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shift_schedules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `date` date NOT NULL,
  `shift_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shift_schedules_employee_id_date_unique` (`employee_id`,`date`),
  KEY `shift_schedules_property_id_foreign` (`property_id`),
  CONSTRAINT `shift_schedules_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `shift_schedules_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `spa_appointments`
--

DROP TABLE IF EXISTS `spa_appointments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `spa_appointments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `treatment_id` bigint unsigned NOT NULL,
  `therapist_id` bigint unsigned DEFAULT NULL,
  `cabin_id` bigint unsigned DEFAULT NULL,
  `guest_id` bigint unsigned DEFAULT NULL,
  `reservation_id` bigint unsigned DEFAULT NULL,
  `folio_id` bigint unsigned DEFAULT NULL,
  `start_at` datetime NOT NULL,
  `end_at` datetime NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'booked',
  `price` decimal(12,2) NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `spa_appointments_property_id_foreign` (`property_id`),
  KEY `spa_appointments_treatment_id_foreign` (`treatment_id`),
  KEY `spa_appointments_therapist_id_foreign` (`therapist_id`),
  KEY `spa_appointments_cabin_id_foreign` (`cabin_id`),
  KEY `spa_appointments_guest_id_foreign` (`guest_id`),
  KEY `spa_appointments_reservation_id_foreign` (`reservation_id`),
  KEY `spa_appointments_folio_id_foreign` (`folio_id`),
  CONSTRAINT `spa_appointments_cabin_id_foreign` FOREIGN KEY (`cabin_id`) REFERENCES `spa_cabins` (`id`),
  CONSTRAINT `spa_appointments_folio_id_foreign` FOREIGN KEY (`folio_id`) REFERENCES `folios` (`id`),
  CONSTRAINT `spa_appointments_guest_id_foreign` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`),
  CONSTRAINT `spa_appointments_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `spa_appointments_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`),
  CONSTRAINT `spa_appointments_therapist_id_foreign` FOREIGN KEY (`therapist_id`) REFERENCES `spa_therapists` (`id`),
  CONSTRAINT `spa_appointments_treatment_id_foreign` FOREIGN KEY (`treatment_id`) REFERENCES `spa_treatments` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `spa_cabins`
--

DROP TABLE IF EXISTS `spa_cabins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `spa_cabins` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'single',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `spa_cabins_property_id_foreign` (`property_id`),
  CONSTRAINT `spa_cabins_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `spa_membership_usages`
--

DROP TABLE IF EXISTS `spa_membership_usages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `spa_membership_usages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `membership_id` bigint unsigned NOT NULL,
  `spa_appointment_id` bigint unsigned NOT NULL,
  `discount_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `spa_membership_usages_membership_id_foreign` (`membership_id`),
  KEY `spa_membership_usages_spa_appointment_id_foreign` (`spa_appointment_id`),
  CONSTRAINT `spa_membership_usages_membership_id_foreign` FOREIGN KEY (`membership_id`) REFERENCES `spa_memberships` (`id`) ON DELETE CASCADE,
  CONSTRAINT `spa_membership_usages_spa_appointment_id_foreign` FOREIGN KEY (`spa_appointment_id`) REFERENCES `spa_appointments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `spa_memberships`
--

DROP TABLE IF EXISTS `spa_memberships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `spa_memberships` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `guest_id` bigint unsigned NOT NULL,
  `membership_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `plan_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `auto_renew` tinyint(1) NOT NULL DEFAULT '0',
  `price` decimal(14,2) NOT NULL,
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `spa_memberships_membership_number_unique` (`membership_number`),
  KEY `spa_memberships_property_id_foreign` (`property_id`),
  KEY `spa_memberships_guest_id_foreign` (`guest_id`),
  CONSTRAINT `spa_memberships_guest_id_foreign` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`),
  CONSTRAINT `spa_memberships_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `spa_therapists`
--

DROP TABLE IF EXISTS `spa_therapists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `spa_therapists` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `specialties` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `spa_therapists_property_id_foreign` (`property_id`),
  CONSTRAINT `spa_therapists_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `spa_treatments`
--

DROP TABLE IF EXISTS `spa_treatments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `spa_treatments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `duration_minutes` smallint unsigned NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `inclusions` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `spa_treatments_property_id_code_unique` (`property_id`,`code`),
  CONSTRAINT `spa_treatments_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stock_items`
--

DROP TABLE IF EXISTS `stock_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `sku` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pcs',
  `current_qty` decimal(14,3) NOT NULL DEFAULT '0.000',
  `reorder_point` decimal(14,3) NOT NULL DEFAULT '0.000',
  `average_cost` decimal(14,2) NOT NULL DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stock_items_property_id_sku_unique` (`property_id`,`sku`),
  CONSTRAINT `stock_items_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stock_movements`
--

DROP TABLE IF EXISTS `stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_movements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `stock_item_id` bigint unsigned NOT NULL,
  `movement_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qty` decimal(14,3) NOT NULL,
  `unit_cost` decimal(14,2) NOT NULL DEFAULT '0.00',
  `reference_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` bigint unsigned DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `performed_by_user_id` bigint unsigned DEFAULT NULL,
  `moved_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_movements_stock_item_id_foreign` (`stock_item_id`),
  KEY `stock_movements_performed_by_user_id_foreign` (`performed_by_user_id`),
  CONSTRAINT `stock_movements_performed_by_user_id_foreign` FOREIGN KEY (`performed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_movements_stock_item_id_foreign` FOREIGN KEY (`stock_item_id`) REFERENCES `stock_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `survey_responses`
--

DROP TABLE IF EXISTS `survey_responses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `survey_responses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `survey_id` bigint unsigned NOT NULL,
  `reservation_id` bigint unsigned DEFAULT NULL,
  `guest_id` bigint unsigned DEFAULT NULL,
  `answers` json NOT NULL,
  `nps_score` tinyint unsigned DEFAULT NULL,
  `sentiment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `survey_responses_survey_id_foreign` (`survey_id`),
  KEY `survey_responses_reservation_id_foreign` (`reservation_id`),
  KEY `survey_responses_guest_id_foreign` (`guest_id`),
  CONSTRAINT `survey_responses_guest_id_foreign` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`),
  CONSTRAINT `survey_responses_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`),
  CONSTRAINT `survey_responses_survey_id_foreign` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `surveys`
--

DROP TABLE IF EXISTS `surveys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `surveys` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trigger` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'post_stay',
  `questions` json NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `surveys_property_id_slug_unique` (`property_id`,`slug`),
  CONSTRAINT `surveys_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sustainability_metrics`
--

DROP TABLE IF EXISTS `sustainability_metrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sustainability_metrics` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `measurement_date` date NOT NULL,
  `metric` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` decimal(16,4) NOT NULL,
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sustainability_metrics_property_id_measurement_date_metric_index` (`property_id`,`measurement_date`,`metric`),
  CONSTRAINT `sustainability_metrics_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tenant_domains`
--

DROP TABLE IF EXISTS `tenant_domains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tenant_domains` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `domain` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `ssl_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenant_domains_domain_unique` (`domain`),
  KEY `tenant_domains_tenant_id_foreign` (`tenant_id`),
  CONSTRAINT `tenant_domains_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tenant_invoices`
--

DROP TABLE IF EXISTS `tenant_invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tenant_invoices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subscription_id` bigint unsigned DEFAULT NULL,
  `invoice_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `issued_at` date NOT NULL,
  `due_at` date NOT NULL,
  `subtotal` decimal(14,2) NOT NULL,
  `tax_total` decimal(14,2) NOT NULL DEFAULT '0.00',
  `grand_total` decimal(14,2) NOT NULL,
  `paid_total` decimal(14,2) NOT NULL DEFAULT '0.00',
  `balance` decimal(14,2) NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `line_items` json DEFAULT NULL,
  `payment_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenant_invoices_invoice_no_unique` (`invoice_no`),
  KEY `tenant_invoices_subscription_id_foreign` (`subscription_id`),
  KEY `tenant_invoices_tenant_id_foreign` (`tenant_id`),
  CONSTRAINT `tenant_invoices_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `tenant_subscriptions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tenant_invoices_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tenant_subscriptions`
--

DROP TABLE IF EXISTS `tenant_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tenant_subscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `plan_id` bigint unsigned NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `current_period_start` date NOT NULL,
  `current_period_end` date NOT NULL,
  `trial_ends_at` date DEFAULT NULL,
  `billing_cycle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'monthly',
  `price_paid_idr` decimal(14,2) DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_subscriptions_plan_id_foreign` (`plan_id`),
  KEY `tenant_subscriptions_tenant_id_status_index` (`tenant_id`,`status`),
  CONSTRAINT `tenant_subscriptions_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`),
  CONSTRAINT `tenant_subscriptions_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tenants`
--

DROP TABLE IF EXISTS `tenants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tenants` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'trial',
  `plan_id` bigint unsigned DEFAULT NULL,
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `current_period_ends_at` timestamp NULL DEFAULT NULL,
  `suspended_at` timestamp NULL DEFAULT NULL,
  `churned_at` timestamp NULL DEFAULT NULL,
  `max_rooms` int unsigned DEFAULT NULL,
  `max_users` int unsigned DEFAULT NULL,
  `database_name` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `db_host` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `storage_disk_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `feature_overrides` json DEFAULT NULL,
  `feature_overrides_locked` json DEFAULT NULL,
  `last_active_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `churn_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lifecycle_events` json DEFAULT NULL,
  `provisioned` tinyint(1) NOT NULL DEFAULT '0',
  `provisioned_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenants_slug_unique` (`slug`),
  UNIQUE KEY `tenants_database_name_unique` (`database_name`),
  KEY `tenants_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `travel_agents`
--

DROP TABLE IF EXISTS `travel_agents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `travel_agents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `iata_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default_commission_pct` decimal(6,3) NOT NULL DEFAULT '0.000',
  `credit_limit` decimal(14,2) NOT NULL DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `travel_agents_property_id_foreign` (`property_id`),
  CONSTRAINT `travel_agents_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `property_id` bigint unsigned DEFAULT NULL,
  `two_factor_secret_encrypted` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `two_factor_recovery_codes` text COLLATE utf8mb4_unicode_ci,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_property_id_index` (`property_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `voucher_redemptions`
--

DROP TABLE IF EXISTS `voucher_redemptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `voucher_redemptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `voucher_id` bigint unsigned NOT NULL,
  `folio_id` bigint unsigned DEFAULT NULL,
  `reservation_id` bigint unsigned DEFAULT NULL,
  `amount` decimal(14,2) NOT NULL,
  `redeemed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `redeemed_by_user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `voucher_redemptions_voucher_id_foreign` (`voucher_id`),
  KEY `voucher_redemptions_folio_id_foreign` (`folio_id`),
  KEY `voucher_redemptions_reservation_id_foreign` (`reservation_id`),
  KEY `voucher_redemptions_redeemed_by_user_id_foreign` (`redeemed_by_user_id`),
  CONSTRAINT `voucher_redemptions_folio_id_foreign` FOREIGN KEY (`folio_id`) REFERENCES `folios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `voucher_redemptions_redeemed_by_user_id_foreign` FOREIGN KEY (`redeemed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `voucher_redemptions_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `voucher_redemptions_voucher_id_foreign` FOREIGN KEY (`voucher_id`) REFERENCES `gift_vouchers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `waitlist_entries`
--

DROP TABLE IF EXISTS `waitlist_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `waitlist_entries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `guest_id` bigint unsigned DEFAULT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `rooms` smallint unsigned NOT NULL DEFAULT '1',
  `preferred_room_type_id` bigint unsigned DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'waiting',
  `notified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `waitlist_entries_property_id_foreign` (`property_id`),
  KEY `waitlist_entries_guest_id_foreign` (`guest_id`),
  KEY `waitlist_entries_preferred_room_type_id_foreign` (`preferred_room_type_id`),
  CONSTRAINT `waitlist_entries_guest_id_foreign` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`),
  CONSTRAINT `waitlist_entries_preferred_room_type_id_foreign` FOREIGN KEY (`preferred_room_type_id`) REFERENCES `room_types` (`id`),
  CONSTRAINT `waitlist_entries_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `webhook_deliveries`
--

DROP TABLE IF EXISTS `webhook_deliveries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `webhook_deliveries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `webhook_id` bigint unsigned NOT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` json NOT NULL,
  `attempt` int unsigned NOT NULL DEFAULT '1',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `http_status` smallint unsigned DEFAULT NULL,
  `response_body` text COLLATE utf8mb4_unicode_ci,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `webhook_deliveries_webhook_id_foreign` (`webhook_id`),
  KEY `webhook_deliveries_event_id_index` (`event_id`),
  CONSTRAINT `webhook_deliveries_webhook_id_foreign` FOREIGN KEY (`webhook_id`) REFERENCES `webhooks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `webhooks`
--

DROP TABLE IF EXISTS `webhooks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `webhooks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret_encrypted` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `events` json NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `failed_consecutive` int unsigned NOT NULL DEFAULT '0',
  `last_delivered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `webhooks_property_id_foreign` (`property_id`),
  CONSTRAINT `webhooks_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wna_logs`
--

DROP TABLE IF EXISTS `wna_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wna_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `reservation_id` bigint unsigned DEFAULT NULL,
  `guest_id` bigint unsigned NOT NULL,
  `check_in_date` date NOT NULL,
  `check_out_date` date NOT NULL,
  `passport_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nationality` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `passport_expires_at` date DEFAULT NULL,
  `visa_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `arrival_card_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reported_at_imigrasi_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `reported_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wna_logs_reservation_id_foreign` (`reservation_id`),
  KEY `wna_logs_guest_id_foreign` (`guest_id`),
  KEY `wna_logs_property_id_check_in_date_index` (`property_id`,`check_in_date`),
  CONSTRAINT `wna_logs_guest_id_foreign` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`),
  CONSTRAINT `wna_logs_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wna_logs_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `work_orders`
--

DROP TABLE IF EXISTS `work_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `work_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `wo_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `asset_id` bigint unsigned DEFAULT NULL,
  `room_id` bigint unsigned DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `assignee_id` bigint unsigned DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `reported_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `cost_material` decimal(14,2) NOT NULL DEFAULT '0.00',
  `cost_labor` decimal(14,2) NOT NULL DEFAULT '0.00',
  `material_used` json DEFAULT NULL,
  `photos_before` json DEFAULT NULL,
  `photos_after` json DEFAULT NULL,
  `resolution` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `work_orders_wo_no_unique` (`wo_no`),
  KEY `work_orders_property_id_foreign` (`property_id`),
  KEY `work_orders_asset_id_foreign` (`asset_id`),
  KEY `work_orders_room_id_foreign` (`room_id`),
  KEY `work_orders_assignee_id_foreign` (`assignee_id`),
  CONSTRAINT `work_orders_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`),
  CONSTRAINT `work_orders_assignee_id_foreign` FOREIGN KEY (`assignee_id`) REFERENCES `users` (`id`),
  CONSTRAINT `work_orders_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `work_orders_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping events for database 'hotel_main'
--

--
-- Dumping routines for database 'hotel_main'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-05 17:33:43
