<?php

namespace Compass\Pivot;

// настройки тарифов
$CONFIG["TARIFF"] = [
	"member_count" => [
		"payment_period"     => 60 * 60 * 24 * 7,
		"postpayment_period" => 60 * 60 * 24 * 7,
		"trial_period"       => 60 * 60 * 24 * 30
	],
];

return $CONFIG;
