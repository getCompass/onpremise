<?php declare(strict_types=1);

namespace Compass\Pivot;

/**
 * Шлюз для работы с лицензионными файлами on-premise решений.
 */
class Gateway_Lic_OnPremise {

	/**
	 * Возвращает ключ-подпись для текущей лицензии.
	 */
	public static function getSignKey():string {

		return "mEgY5V0IPdTbFZ6BxoZuoqKLXrGCZuEZ";
	}

	/**
	 * Возвращает ключ-подпись для текущей лицензии.
	 */
	public static function getLicenseKey():string {

		return "WtYnXwhlpfwbbC7XrM6x1ipJRwBwiDX1";
	}

	/**
	 * Возвращает срок действия лицензии.
	 */
	public static function getExpirationDate():int {

		return 1704056400;
	}

	/**
	 * Возвращает текущий статус лицензии.
	 */
	public static function isActive():bool {

		return static::getExpirationDate() > time();
	}

	/**
	 * Возвращает максимальное число участников, доступных по лицензии.
	 */
	public static function getUserLimit():int {

		return 500;
	}
}