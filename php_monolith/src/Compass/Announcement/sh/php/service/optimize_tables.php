<?php

namespace Compass\Announcement;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

if (Type_Script_InputHelper::needShowUsage()) {

	console(<<<EOF
Скрипт для запуска оптимизации таблиц анонсов. Параметры:
	--security			[OPT]	добавляет таблицу announcement_security.token_user;
	--user-relationship	[OPT]	добавляет таблицу announcement_company.company_user и announcement_user.user_company;
	--announcement		[OPT]	добавляет таблицу announcement_main.announcement, есть смысл делать только после чистки анонсов;
EOF
	);

	exit(0);
}

$need_optimize_announcement      = Type_Script_InputParser::getArgumentValue("--announcement", Type_Script_InputParser::TYPE_NONE, false, false);
$need_optimize_security          = Type_Script_InputParser::getArgumentValue("--security", Type_Script_InputParser::TYPE_NONE, false, false);
$need_optimize_user_relationship = Type_Script_InputParser::getArgumentValue("--user-relationship", Type_Script_InputParser::TYPE_NONE, false, false);

if (!$need_optimize_announcement && !$need_optimize_security && !$need_optimize_user_relationship) {

	console("не указаны таблицы для оптимизации, используй --help для получения информации");
	exit(1);
}

console("--------------------------------");
$notice_string = "Планирую оптимизировать таблицы:";
$notice_string = $need_optimize_security ? "$notice_string\n\tannouncement_security.token_user" : $notice_string;
$notice_string = $need_optimize_user_relationship ? "$notice_string\n\tannouncement_company.company_user\n\tannouncement_user.user_company" : $notice_string;
$notice_string = $need_optimize_announcement ? "$notice_string\n\tannouncement_main.announcement" : $notice_string;
console($notice_string);
console("--------------------------------");

if (!Type_Script_InputHelper::assertConfirm("подтвердите исполнение")) {

	console("исполнение прервано пользователем");
	exit(1);
}

if ($need_optimize_security) {

	console("оптимизирую таблицы токенов...");
	Gateway_Db_AnnouncementSecurity_TokenUser::optimize();
}

if ($need_optimize_user_relationship) {

	console("оптимизирую таблицы связей...");
	Gateway_Db_AnnouncementCompany_CompanyUser::optimize();
	Gateway_Db_AnnouncementUser_UserCompany::optimize();
}

if ($need_optimize_announcement) {

	console("оптимизирую таблицы записей анонсов...");
	Gateway_Db_AnnouncementMain_Announcement::optimize();
}

console("оптимизация завершена, завершаю работу");
