use
company_data;

/* @formatter:off */

-- переименовываем таблицы
ALTER TABLE invite_link_list RENAME TO join_link_list;
ALTER TABLE entry_invite_link_list RENAME TO entry_join_link_list;

-- переименовываем столбцы таблиц
ALTER TABLE join_link_list CHANGE COLUMN invite_link_uniq join_link_uniq VARCHAR (12) NOT NULL;
ALTER TABLE entry_join_link_list CHANGE COLUMN invite_link_uniq join_link_uniq VARCHAR (12) NOT NULL DEFAULT '';
ALTER TABLE hiring_request CHANGE COLUMN invite_link_uniq join_link_uniq VARCHAR (12) NOT NULL DEFAULT '';