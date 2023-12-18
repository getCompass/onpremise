<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

$space_id        = Type_Script_InputParser::getArgumentValue("--space-id", Type_Script_InputParser::TYPE_INT);
$user_id         = Type_Script_InputParser::getArgumentValue("--user-id", Type_Script_InputParser::TYPE_INT);
$from_limit      = Type_Script_InputParser::getArgumentValue("--from-limit", Type_Script_InputParser::TYPE_INT);
$remain_duration = Type_Script_InputParser::getArgumentValue("--remain-duration", Type_Script_InputParser::TYPE_INT);
$label           = Domain_SpaceTariff_Plan_MemberCount_Product_ChangeDefault::GOODS_ID_LABEL;

$goods_id = "$user_id.$label.$from_limit.10.$remain_duration.$space_id";

if (!Type_Script_InputHelper::assertConfirm("активирую goods $goods_id для пространства $space_id? (у/N)")) {

	console("прервано");
	exit(1);
}

Domain_SpaceTariff_Scenario_Api::activate($user_id, $goods_id);
console("активировал");