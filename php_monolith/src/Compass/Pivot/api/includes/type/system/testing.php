<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Server\ServerProvider;

/**
 * класс для поддержки testing-кода
 * работает через заголовки, чтобы удобно рулить все в одном месте
 *
 * @uses Type_System_Testing::_isForceExitTaskNotExist()
 *
 * @method static isForceExitTaskNotExist
 * @method static isIgnoreMailAuthScenarioException
 * @method static isIgnoreMailScenarioException
 * @method static isMailScenarioException
 * @method static isIgnoreMailResendException
 */
class Type_System_Testing {

	/**
	 * функция срабатывает перед тем как вызвать любой из методов
	 *
	 * @throws \parseException
	 */
	public static function __callStatic(string $name, array $arguments):mixed {

		// все методы онли для тестового сервера
		ServerProvider::assertTest();

		// подставляем "_" так как все методы в классе protected
		$method = "_" . $name;

		// если такого метода нет
		if (!method_exists(__CLASS__, $method)) {
			throw new ParseFatalException(__METHOD__ . ": attempt to call not exist method");
		}

		// запускаем метод
		return forward_static_call_array([__CLASS__, $method], $arguments);
	}

	/**
	 * отсутствует ли таска на выход из компании?
	 */
	protected static function _isForceExitTaskNotExist():bool {

		$value = getHeader("HTTP_FORCE_EXIT_TASK_NOT_EXIST");

		$value = (int) $value;
		return $value == 1;
	}

	/**
	 * нужно ли игнорировать проверки на full/short сценарии в аутентификации через почту
	 */
	protected static function _isIgnoreMailAuthScenarioException():bool {

		$value = getHeader("HTTP_IGNORE_MAIL_AUTH_SCENARIO_EXCEPTION");

		$value = (int) $value;
		return $value == 1;
	}

	/**
	 * нужно ли кинуть ошибку проверки на full/short при сценарии через почту
	 */
	protected static function _isMailScenarioException():bool {

		$value = getHeader("HTTP_MAIL_SCENARIO_EXCEPTION");

		$value = (int) $value;
		return $value == 1;
	}

	/**
	 * нужно ли игнорировать проверки на full/short при сценарии через почту
	 */
	protected static function _isIgnoreMailScenarioException():bool {

		$value = getHeader("HTTP_IGNORE_MAIL_SCENARIO_EXCEPTION");

		$value = (int) $value;
		return $value == 1;
	}

	/**
	 * нужно ли игнорировать проверку переотправки
	 */
	protected static function _isIgnoreMailResendException():bool {

		$value = getHeader("HTTP_IGNORE_MAIL_RESEND_EXCEPTION");

		$value = (int) $value;
		return $value == 1;
	}

}