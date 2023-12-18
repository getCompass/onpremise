USE `pivot_history_logs_2022`;

-- переименовываем таблицы
ALTER TABLE invite_link_validate_history RENAME TO join_link_validate_history;
ALTER TABLE invite_link_accepted_history RENAME TO join_link_accepted_history;

-- переименовываем столбцы таблиц
ALTER TABLE join_link_validate_history CHANGE COLUMN invite_link_uniq join_link_uniq VARCHAR (12) NOT NULL DEFAULT '';
ALTER TABLE join_link_accepted_history CHANGE COLUMN invite_link_uniq join_link_uniq VARCHAR (12) NOT NULL DEFAULT '';