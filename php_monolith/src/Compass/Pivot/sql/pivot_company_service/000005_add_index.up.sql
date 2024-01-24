USE `pivot_company_service`;

ALTER TABLE `company_service_task_history` ADD INDEX `created_at` (`created_at`);
