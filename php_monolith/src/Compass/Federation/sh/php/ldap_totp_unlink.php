<?php declare(strict_types = 1);

namespace Compass\Federation;

require_once __DIR__ . "/../../../../../start.php";

/**
 * Скрипт для принудительной отвязки TOTP от LDAP-пользователя по uid
 * После отвязки при следующей авторизации пользователю снова будет предложено привязать TOTP
 *
 * Использование:
 *   php ldap_totp_unlink.php --uid=<ldap_uid> --dry=1
 *   php ldap_totp_unlink.php --uid=<ldap_uid> --dry=0
 */
class Script_Ldap_Totp_Unlink {

	/** @var bool флаг тестового режима (по умолчанию true - без внесения изменений) */
	private static bool $is_dry_run = true;

	/** @var string uid пользователя в LDAP */
	private static string $uid = "";

	/**
	 * Точка входа
	 */
	public static function run():void {

		if (!self::_parseArgs()) {
			return;
		}

		self::_doUnlink();
	}

	/**
	 * Выводит инструкцию по использованию
	 */
	private static function _showUsage():void {

		console("Использование:");
		console("  php ldap_totp_unlink.php --uid=<ldap_uid> [--dry=0|1]");
		console("");
		console("Параметры:");
		console("  --uid    LDAP uid пользователя (обязательный)");
		console("  --dry    1 = тестовый режим без изменений (по умолчанию), 0 = вносим изменения");
	}

	/**
	 * Разбирает аргументы командной строки
	 */
	private static function _parseArgs():bool {

		$options = getopt("", ["uid:", "dry::", "help::"]);

		if (isset($options["help"]) || !isset($options["uid"]) || trim($options["uid"]) === "") {

			self::_showUsage();
			return false;
		}

		self::$uid        = trim($options["uid"]);
		self::$is_dry_run = !isset($options["dry"]) || (bool) $options["dry"];

		return true;
	}

	/**
	 * Выполняет отвязку TOTP
	 */
	private static function _doUnlink():void {

		$uid = self::$uid;

		console("Режим: " . (self::$is_dry_run ? "тестовый (изменения не вносятся)" : "рабочий"));
		console("UID: $uid");
		console("---");

		// проверяем, есть ли привязка
		try {
			Domain_Ldap_Entity_Totp_UserRel::get($uid);
		} catch (Domain_Ldap_Exception_Totp_NotBound) {

			console(greenText("[x] TOTP не привязан к пользователю с uid={$uid}, ничего не делаем."));
			return;
		}

		console("[-] Найдена привязка TOTP для uid={$uid}.");

		if (self::$is_dry_run) {

			console(yellowText("[~] Тестовый режим - изменения не вносятся."));
			return;
		}

		// удаляем TOTP-привязку
		Domain_Ldap_Entity_Totp_UserRel::delete($uid);

		// на всякий случай чистим и незавершённую настройку из кэша
		Domain_Ldap_Entity_Totp_PendingSetup::delete($uid);

		console(greenText("[V] TOTP успешно отвязан. При следующей авторизации пользователю будет предложено привязать TOTP заново."));
	}
}

Script_Ldap_Totp_Unlink::run();
