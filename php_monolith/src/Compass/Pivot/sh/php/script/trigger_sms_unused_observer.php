<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

$interval = Type_Script_InputParser::getArgumentValue("interval", Type_Script_InputParser::TYPE_STRING, "hour", false);
$time     = Type_Script_InputParser::getArgumentValue("time", Type_Script_InputParser::TYPE_INT, time(), false);

$interval_value = match ($interval) {
	"hour"  => Domain_User_Action_LookForUnusedSmsActions::HOUR_CHECK,
	"day"   => Domain_User_Action_LookForUnusedSmsActions::DAY_CHECK,
	default => throw new \Exception("интервал должен быть hour или day"),
};

Domain_User_Action_LookForUnusedSmsActions::run($time, $interval_value);