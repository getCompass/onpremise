<?php

namespace Compass\Premise;

$CONFIG["PREMISE_URL"] = [
	"license" => PUBLIC_ENTRYPOINT_LICENSE,
];

// конфиг с query для обращения к определенному модулю
$CONFIG["PREMISE_MODULE"] = [
	"license" => [
		"path" => "/api/premise/license/",
	],
];

return $CONFIG;
