<?php

namespace BaseFrame\Router\Middleware;

use AnalyticUtils\Domain\Counter\Entity\Main as Counter;
use AnalyticUtils\Domain\Counter\Struct\User as UserCounterStruct;
use AnalyticUtils\Domain\Counter\Entity\User as UserCounter;
use BaseFrame\Router\Request;
use BaseFrame\Url\UrlProvider;

/**
 * Подсчёт времени пользователя в приложении
 */
class ApplicationUserTimeSpent implements Main {

	// ключ активности пользователя
	// !!! никаких _last, _update_at, _expires_at, etc - пользователь по ключу не должен понять для чего он
	protected const _ACTIVITY_KEY = "activity_key";

	protected const _LAST_ACTIVITY_EXPIRES_IN = 60 * 10; // время, когда время активности истечёт

	/**
	 * подсчитываем время пользователя в приложении
	 */
	public static function handle(Request $request):Request {

		if ($request->user_id < 1) {
			return $request;
		}

		// проверяем, что в куках находится ключ активности пользователя
		if (!isset($_COOKIE[self::_ACTIVITY_KEY])) {

			self::_updateLastActivityAtToCookie();
			return $request;
		}

		// достаём из кук время последней активности пользователя
		$last_activity_at = $_COOKIE[self::_ACTIVITY_KEY];

		// проверяем параметр на корректность
		if (filter_var($last_activity_at, FILTER_VALIDATE_INT) === false || $last_activity_at > time()) {

			// обновляем время активности
			self::_updateLastActivityAtToCookie();
			return $request;
		}

		// если со времени записанной активности прошло >= 10 минут, то считаем, что пользователь был активен >= 10 минут в приложении
		if ((time() - $last_activity_at) > self::_LAST_ACTIVITY_EXPIRES_IN) {

			// отправляем запрос на инкремент счётчика
			$request = self::_addCounter($request, $request->user_id, UserCounter::PIVOT_TOTAL_ONLINE_TIME);

			// обновляем время активности
			self::_updateLastActivityAtToCookie();
			return $request;
		}

		return $request;
	}

	/**
	 * устанавливаем значение последней активности пользователя
	 */
	protected static function _updateLastActivityAtToCookie():void {

		// добавляем время жизни для куки
		$cookie_live_expires_at = time() + DAY1 * 360;

		// устанавливаем session_key для пользователя
		setcookie(self::_ACTIVITY_KEY,time(), [
			"expires"  => $cookie_live_expires_at,
			"path"     => "/",
			"domain"   => UrlProvider::pivotDomain(),
			"secure"   => true,
			"httponly" => false,
			"samesite" => "None"
		]);
	}

	/**
	 * Добавляем пользовательский счётчик
	 *
	 * @return void
	 */
	protected static function _addCounter(Request $request, int $user_id, string $row, string $action = Counter::ACTION_INCREMENT, int $value = self::_LAST_ACTIVITY_EXPIRES_IN):Request {

		$user_counter = new UserCounterStruct($user_id, $action, $row, $value);

		// добавляем счётчик пользователя
		$request->counter_list[] = $user_counter;

		return $request;
	}
}