use company_data;

DROP INDEX `full_name` ON `member_list`;
DROP INDEX `company_joined_at` ON `member_list`;

CREATE INDEX `npc_type.full_name` ON `member_list` (`npc_type`, `full_name`);
