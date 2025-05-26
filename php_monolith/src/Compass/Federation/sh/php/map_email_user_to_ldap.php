<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;
use cs_SocketRequestIsFailed;

require_once __DIR__ . "/../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1); // nosemgrep
set_time_limit(0);

/**
 * Класс для обновления связей между пользователями и LDAP аккаунтами
 * Читает данные из JSON файла и создает записи в базе данных
 */
class Update_Map_Email_User_To_Ldap {

	/** @var string Путь к файлу с данными по умолчанию */
	private const DEFAULT_USERS_FILE = __DIR__ . "/users.json";

	/** @var bool Флаг тестового режима (по умолчанию true - без внесения изменений) */
	private static bool $isDryRun = true;

	/** @var string Путь к файлу с данными */
	private static string $filePath;

	/**
	 * Выводит инструкцию по использованию скрипта
	 */
	private static function showUsage():void {

		echo "Использование скрипта:\n";
		echo "--dry=1|0         Режим скрипта. 1 - тестовый. 0 - вносим изменения (по умолчанию 1)\n";
		echo "--file_path=path  Путь к JSON файлу с данными (по умолчанию ./users.json)\n";
	}

	/**
	 * Парсит аргументы командной строки
	 * @return bool Успешность парсинга аргументов
	 */
	private static function parseArgs():bool {

		$options = getopt("", ["dry::", "file_path::", "help::"]);

		// Проверяем, запущен ли скрипт без аргументов или с флагом help
		if (empty($options) || isset($options["help"])) {
			self::showUsage();
			return false;
		}

		// Если флаг dry явно установлен в 0, только тогда выключаем тестовый режим
		self::$isDryRun = !isset($options["dry"]) || (bool) $options["dry"];
		self::$filePath = $options["file_path"] ?? self::DEFAULT_USERS_FILE;

		return true;
	}

	/**
	 * Валидирует данные из JSON файла
	 *
	 * @param array $data Данные из файла
	 *
	 * @return array Массив ошибок валидации
	 */
	private static function validateData(array $data):array {

		$errors       = [];
		$user_id_list = [];
		$uid_list     = [];

		foreach ($data as $user_id => $uid) {

			// проверка user_id
			if (!is_numeric($user_id) || $user_id <= 0 || $user_id >= 1000000) {
				$errors[] = "Некорректный ID пользователя: $user_id";
			}
			if (in_array($user_id, $user_id_list)) {
				$errors[] = "Дублирующийся ID пользователя: $user_id";
			}
			$user_id_list[] = $user_id;

			// проверка username
			if (!is_string($uid) || empty($uid)) {
				$errors[] = "Некорректное имя пользователя для ID $user_id";
			}
			if (in_array($uid, $uid_list)) {
				$errors[] = "Дублирующееся имя пользователя: $uid";
			}
			$uid_list[] = $uid;
		}

		return $errors;
	}

	/**
	 * Основной метод выполнения скрипта
	 */
	public static function doWork():void {

		// парсим аргументы
		if (!self::parseArgs()) {
			return;
		}

		// проверяем существование файла
		if (!file_exists(self::$filePath)) {
			console("Ошибка: Файл " . self::$filePath . " не найден");
			return;
		}

		// читаем и декодируем JSON
		$jsonContent = file_get_contents(self::$filePath);
		$data        = json_decode($jsonContent, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			console("Ошибка: Некорректный JSON файл");
			return;
		}

		// валидируем данные
		$errors = self::validateData($data);
		if (!empty($errors)) {
			console("Найдены ошибки в данных:");
			foreach ($errors as $error) {
				console("- " . $error);
			}
			return;
		}

		// обрабатываем данные
		console("Режим: " . (self::$isDryRun ? "тестовый" : "рабочий, вносим изменения в бд"));
		console("---");
		foreach ($data as $user_id => $uid) {

			// пробуем получить пользователя в компасе
			try {
				$user_info = Gateway_Socket_Pivot::getUserInfo($user_id);
			} catch (RowNotFoundException) {

				console("[x] Не нашли зарегистрированного пользователя с compass_user_id = $user_id, пропускаем");
				continue;
			}
			$full_name = $user_info["full_name"];

			// проверяем что пользователя еще нет в бд
			try {

				$account_user_rel = Domain_Ldap_Entity_AccountUserRel::get($uid);
			} catch (Domain_Ldap_Exception_UserRelationship_NotFound) {

				// проверяем что нет записей с этим user_id
				try {
					Gateway_Db_LdapData_LdapAccountUserRel::getOneByUserID($user_id);
				} catch (RowNotFoundException) {

					// если не нашли, то все ок, так и должно быть
					console("[-] Обработка: uid = $uid, compass_user_id = $user_id, full_name = $full_name");

					if (!self::$isDryRun) {
						Domain_Ldap_Entity_AccountUserRel::create($uid, $user_id, $full_name, "");
						console("[V] Done");
					}
					continue;
				}

				// если нашли, то пишем
				console("[x] Пользователь с compass_user_id = $user_id уже зарегистрирован с uid = $uid и full_name = $full_name, пропускаем");
				continue;
			}

			// если такой уже есть, просто пропускаем его
			$found_user_id = $account_user_rel->user_id;
			console("[x] Пользователь с UID = $uid уже зарегистрирован с compass_user_id = $found_user_id и full_name = $full_name, пропускаем");
		}
	}
}

// начинаем выполнение
Update_Map_Email_User_To_Ldap::doWork();