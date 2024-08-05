<?php

namespace Compass\Federation;

// конфиг с дефолтными генераторами событий в go_event
// добавляем генератор, который будет выбрасывать событие, обработчик которого будет смотреть в базе, не пора ли запустить
// observer для пользователя
$CONFIG["GENERATOR"] = [

	// генератор механизма автоматической блокировки пользователей Compass
	// связанных с LDAP учетными записями
	Type_Generator_Ldap_AccountChecker::class,
];

return $CONFIG;