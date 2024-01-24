<?php

// main
use Compass\FileNode\Domain_Monitor_Scenario_Socket;

// main
require_once __DIR__ . "/../../../start.php";

$metrics = Domain_Monitor_Scenario_Socket::collect();
showAjax($metrics);