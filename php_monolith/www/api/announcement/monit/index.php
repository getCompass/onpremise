<?php

// main
use Compass\Announcement\Domain_Monitor_Scenario_Socket;

require_once __DIR__ . "/../../../../start.php";

$metrics = Domain_Monitor_Scenario_Socket::collect();
showAjax($metrics);