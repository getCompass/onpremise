<?php

namespace Compass\Speaker;

/*
* класс для поддержки testing-кода
* работает через заголовки, чтобы удобно рулить все в одном месте
*/

/**
 * @method static forceFailedJanusRequest()
 */
class Type_System_Testing {

	// функция срабатывает перед тем как вызвать любой из методов
	public static function __callStatic(string $method, array $parameters):bool {

		// завершаем исполнение, если это не тестовое окружение
		if (!isTestServer()) {
			return false;
		}

		// подставляем _ тк все методы в классе protected
		$method = "_" . $method;

		// если такого метода нет
		if (!method_exists(__CLASS__, $method)) {
			throw new \parseException(__METHOD__ . ": attempt to call not exist method");
		}

		// запускаем метод
		return forward_static_call_array([__CLASS__, $method], $parameters);
	}

	// проваленный запрос к Janus
	public static function _forceFailedJanusRequest():bool {

		$value = getHeader("HTTP_FORCE_FAILED_JANUS_REQUEST");
		$value = intval($value);

		if ($value == 1) {
			return true;
		}

		return false;
	}
}