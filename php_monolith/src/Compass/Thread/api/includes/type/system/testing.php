<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Server\ServerProvider;

/*
* класс для поддержки testing-кода
* работает через заголовки, чтобы удобно рулить все в одном месте
*/

/**
 * @method static forceExpireTimeToDisallowDelete()
 */
class Type_System_Testing {

	// функция срабатывает перед тем как вызвать любой из методов
	public static function __callStatic(string $method, array $parameters):bool {

		// завершаем исполнение, если это не тестовое окружение
		if (!ServerProvider::isTest()) {
			return false;
		}

		// подставляем _ тк все методы в классе protected
		$method = "_" . $method;

		// если такого метода нет
		if (!method_exists(__CLASS__, $method)) {
			throw new ParseFatalException(__METHOD__ . ": attempt to call not exist method");
		}

		// запускаем метод
		return forward_static_call_array([__CLASS__, $method], $parameters);
	}

	// удаление сообщения при вышедшем времени
	public static function _forceExpireTimeToDisallowDelete():bool {

		$value = getHeader("HTTP_FORCE_EXPIRE_TIME_TO_DISALLOW_DELETE");
		$value = intval($value);

		if ($value == 1) {
			return true;
		}

		return false;
	}
}