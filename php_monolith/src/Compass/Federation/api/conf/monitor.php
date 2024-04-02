<?php

namespace Compass\Federation;

// настройки мониторинга, если true, то настройка включена
// передаются в порядке: логи, метрики, трейсы
$CONFIG["MONITOR"] = [
	"FLAG" => [
		false,	// собираем логи
		false,	// собираем метрики
		false 	// собираем трейсы
	]
];

return $CONFIG;