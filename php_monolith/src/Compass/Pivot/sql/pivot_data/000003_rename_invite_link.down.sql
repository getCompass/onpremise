USE `pivot_data`;

-- переименовываем обратно таблицы
ALTER TABLE company_join_link_user_rel RENAME TO company_invite_link_user_rel;
ALTER TABLE company_join_link_rel_0 RENAME TO company_invite_link_rel_0;
ALTER TABLE company_join_link_rel_1 RENAME TO company_invite_link_rel_1;
ALTER TABLE company_join_link_rel_2 RENAME TO company_invite_link_rel_2;
ALTER TABLE company_join_link_rel_3 RENAME TO company_invite_link_rel_3;
ALTER TABLE company_join_link_rel_4 RENAME TO company_invite_link_rel_4;
ALTER TABLE company_join_link_rel_5 RENAME TO company_invite_link_rel_5;
ALTER TABLE company_join_link_rel_6 RENAME TO company_invite_link_rel_6;
ALTER TABLE company_join_link_rel_7 RENAME TO company_invite_link_rel_7;
ALTER TABLE company_join_link_rel_8 RENAME TO company_invite_link_rel_8;
ALTER TABLE company_join_link_rel_9 RENAME TO company_invite_link_rel_9;
ALTER TABLE company_join_link_rel_a RENAME TO company_invite_link_rel_a;
ALTER TABLE company_join_link_rel_b RENAME TO company_invite_link_rel_b;
ALTER TABLE company_join_link_rel_c RENAME TO company_invite_link_rel_c;
ALTER TABLE company_join_link_rel_d RENAME TO company_invite_link_rel_d;
ALTER TABLE company_join_link_rel_e RENAME TO company_invite_link_rel_e;
ALTER TABLE company_join_link_rel_f RENAME TO company_invite_link_rel_f;

-- переименовываем столбцы таблиц
ALTER TABLE company_invite_link_user_rel CHANGE COLUMN join_link_uniq invite_link_uniq VARCHAR (12) NOT NULL DEFAULT '';
ALTER TABLE company_invite_link_rel_0 CHANGE COLUMN join_link_uniq invite_link_uniq VARCHAR (12) NOT NULL DEFAULT '';
ALTER TABLE company_invite_link_rel_1 CHANGE COLUMN join_link_uniq invite_link_uniq VARCHAR (12) NOT NULL DEFAULT '';
ALTER TABLE company_invite_link_rel_2 CHANGE COLUMN join_link_uniq invite_link_uniq VARCHAR (12) NOT NULL DEFAULT '';
ALTER TABLE company_invite_link_rel_3 CHANGE COLUMN join_link_uniq invite_link_uniq VARCHAR (12) NOT NULL DEFAULT '';
ALTER TABLE company_invite_link_rel_4 CHANGE COLUMN join_link_uniq invite_link_uniq VARCHAR (12) NOT NULL DEFAULT '';
ALTER TABLE company_invite_link_rel_5 CHANGE COLUMN join_link_uniq invite_link_uniq VARCHAR (12) NOT NULL DEFAULT '';
ALTER TABLE company_invite_link_rel_6 CHANGE COLUMN join_link_uniq invite_link_uniq VARCHAR (12) NOT NULL DEFAULT '';
ALTER TABLE company_invite_link_rel_7 CHANGE COLUMN join_link_uniq invite_link_uniq VARCHAR (12) NOT NULL DEFAULT '';
ALTER TABLE company_invite_link_rel_8 CHANGE COLUMN join_link_uniq invite_link_uniq VARCHAR (12) NOT NULL DEFAULT '';
ALTER TABLE company_invite_link_rel_9 CHANGE COLUMN join_link_uniq invite_link_uniq VARCHAR (12) NOT NULL DEFAULT '';
ALTER TABLE company_invite_link_rel_a CHANGE COLUMN join_link_uniq invite_link_uniq VARCHAR (12) NOT NULL DEFAULT '';
ALTER TABLE company_invite_link_rel_b CHANGE COLUMN join_link_uniq invite_link_uniq VARCHAR (12) NOT NULL DEFAULT '';
ALTER TABLE company_invite_link_rel_c CHANGE COLUMN join_link_uniq invite_link_uniq VARCHAR (12) NOT NULL DEFAULT '';
ALTER TABLE company_invite_link_rel_d CHANGE COLUMN join_link_uniq invite_link_uniq VARCHAR (12) NOT NULL DEFAULT '';
ALTER TABLE company_invite_link_rel_e CHANGE COLUMN join_link_uniq invite_link_uniq VARCHAR (12) NOT NULL DEFAULT '';
ALTER TABLE company_invite_link_rel_f CHANGE COLUMN join_link_uniq invite_link_uniq VARCHAR (12) NOT NULL DEFAULT '';