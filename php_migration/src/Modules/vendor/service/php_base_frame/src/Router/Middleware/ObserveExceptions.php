<?php

namespace BaseFrame\Router\Middleware;

use BaseFrame\Exception\ExceptionHandler;
use BaseFrame\Exception\ExceptionUtils;
use BaseFrame\Exception\RequestException;
use BaseFrame\Router\Request;

/**
 * Инициализировать контроллер
 */
class ObserveExceptions implements Main {

	/**
	 * Инициализируем класс контроллера
	 */
	public static function handle(Request $request):Request {

		$api_method = $request->extra["api_method"] ?? "undefined";
		$post_data  = $request->extra["post_data"] ?? null;
		$platform   = $request->extra["user_agent"] ?? "undefined";

		ExceptionHandler::register(static fn(object $e) => static::_notice($e, $api_method, $platform, $post_data), true);
		return $request;
	}

	/**
	 * Выполняет обработку исключения.
	 */
	protected static function _notice(object $e, string $method, string $platform, null|array $post_data):void {

		// обрабатываем только ошибки/исключения
		// если что-то другое, то просто выходим, чтобы еще сильнее не поломаться
		if (!($e instanceof \Exception) && !($e instanceof \Error)) {

			// что-то странное, сюда прилетела не ошибка/исключение
			return;
		}

		// для ошибок уровня request
		if ($e instanceof RequestException) {
			$http_code = $e->getHttpCode();
		}

		// добавляем входящие параметры
		$encoded_post = is_null($post_data) ? ["post" => "undefined"] : static::_encodePost($post_data);

		// формируем сообщение и создаем лог
		$message = ExceptionUtils::makeMessage($e, $http_code ?? HTTP_CODE_500);
		\BaseFrame\Monitor\Core::log($message, \BaseFrame\Monitor\LogLine::LOG_LEVEL_ERROR)
			->label("http_code", (string) ($http_code ?? HTTP_CODE_500))
			->label("error_code", $e->getCode())
			->label("error_message", $e->getMessage())
			->label("method", $method)
			->label("user_agent", $platform)
			->label("encoded_post", toJson($encoded_post))
			->seal();

		// закрываем обработчик передачи данных
		\BaseFrame\Monitor\Core::close();
	}

	/**
	 * Обфусцирует входящие данные запроса.
	 * @long
	 */
	protected static function _encodePost(array $post_data):array {

		$output = [];

		foreach ($post_data as $k => $v) {

			if (is_array($v) || is_object($v)) {

				$output[$k] = static::_encodePost((array) $v);
				continue;
			}

			if (is_string($v)) {

				$output[$k] = sprintf("string<%d>", mb_strlen($v));

				// добавляем первые и последние 2 символа в результирующую строку
				// если длина входной строки достаточно большая
				$output[$k] .=  mb_strlen($v) > 6
					? sprintf("::%s**%s", substr($v, 0, 2), substr($v, -2))
					: "******";

				continue;
			}

			if (is_null($v)) {

				$output[$k] = "null";
				continue;
			}

			if (!is_scalar($v)) {

				$output[$k] = "non_scalar::undefined";
				continue;
			}

			$output[$k] = sprintf("%s::%s", gettype($v), $v);
		}

		return $output;
	}
}