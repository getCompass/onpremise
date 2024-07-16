<?php

namespace Compass\Jitsi;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

/**
 * Скрипт для очистки active_conference_id для всех записей в user_active_conference_rel, где конференция находится в статусе FINISHED
 */

// получаем все записи из user_active_conference_rel
$query = "SELECT * FROM `user_active_conference_rel` WHERE `active_conference_id` != ?s LIMIT ?i";
$list  = ShardingGateway::database("jitsi_data")->getAll($query, "", 10000);

foreach ($list as $index => $row) {

	// получаем информацию о такой конференции
	$conference = Domain_Jitsi_Entity_Conference::get($row["active_conference_id"]);

	// если конференция не завершена, то пропускаем ее
	if ($conference->status !== Domain_Jitsi_Entity_Conference::STATUS_FINISHED) {
		continue;
	}

	// нашли завершенуюю конференцию
	console("Найдена завершенная конференция (temp num: $index), которая остается быть активной для пользователя!");
	!Type_Script_InputHelper::isDry() && Domain_Jitsi_Entity_UserActiveConference::onConferenceFinished($row["active_conference_id"]);
	!Type_Script_InputHelper::isDry() && console(blueText("Исправлена конференция (temp num: $index)"));
	Type_Script_InputHelper::isDry() && console(yellowText("Не исправили конференция (temp num: $index), так как включен dry-режим"));
}

console("Скрипт закончил свою работу");