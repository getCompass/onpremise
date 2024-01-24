USE `pivot_history_logs_2022`;

-- переименовываем обратно таблицы
ALTER TABLE join_link_validate_history RENAME TO invite_link_validate_history;
ALTER TABLE join_link_accepted_history RENAME TO invite_link_accepted_history;

-- переименовываем обратно столбцы таблиц
ALTER TABLE invite_link_validate_history CHANGE COLUMN join_link_uniq invite_link_uniq VARCHAR (12) NOT NULL;
ALTER TABLE invite_link_accepted_history CHANGE COLUMN join_link_uniq invite_link_uniq VARCHAR (12) NOT NULL DEFAULT '';