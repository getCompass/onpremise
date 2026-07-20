<?php

namespace Compass\Federation;

require_once __DIR__ . "/../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1); // nosemgrep
set_time_limit(0);

/**
 * Класс для обновления связи пользователя на новое значение user_unique_attribute
 */
class Migrate_User_To_New_User_Unique_Attribute
{
	/** @var bool флаг тестового режима (по умолчанию true - без внесения изменений) */
	private static bool $_is_dry_run = true;

	/** @var string старое значение аттрибута */
	private static string $_old_unique_attribute;

	/** @var string новое значение аттрибута */
	private static string $_new_unique_attribute;

	/**
	 * Выводит инструкцию по использованию скрипта
	 */
	private static function showUsage(): void
	{

		echo "Использование скрипта:\n";
		echo "--dry=1|0 Режим скрипта. 1 - тестовый. 0 - вносим изменения (по умолчанию 1)\n";
		echo "--old_unique_attribute=attribute1  Предыдущее значение поля ldap.user_unique_attribute по которому ранне авторизовывались пользователи\n";
		echo "--new_unique_attribute=attribute2  Текущее значение поля ldap.user_unique_attribute по которому должны будут теперь авторизоваться пользователи после выполнения скрипта.\n
                Необходимо для дополнительной проверки\n";
	}

	/**
	 * Парсит аргументы командной строки
	 * @return bool Успешность парсинга аргументов
	 */
	private static function parseArgs(): bool
	{

		$options = getopt("", ["dry::", "old_unique_attribute::", "new_unique_attribute::", "help::"]);

		// проверяем, запущен ли скрипт без аргументов или с флагом help
		if (empty($options) || isset($options["help"])) {
			self::showUsage();
			return false;
		}

		// если флаг dry явно установлен в 0, только тогда выключаем тестовый режим
		self::$_is_dry_run = !isset($options["dry"]) || (bool) $options["dry"];

		// парсим прошлое значение unique_attribute
		if (!isset($options["old_unique_attribute"]) || $options["old_unique_attribute"] === "") {

			console(redText("Некорректное значение поля: old_unique_attribute"));
			return false;
		}
		self::$_old_unique_attribute = $options["old_unique_attribute"];

		// парсим новое значение unique_attribute
		if (!isset($options["new_unique_attribute"]) || $options["new_unique_attribute"] === "") {

			console(redText("Некорректное значение поля: new_unique_attribute"));
			return false;
		}

		// убеждаемся что пользователь точно не ошибся в значении
		if ($options["new_unique_attribute"] != Domain_Ldap_Entity_Config::getUserUniqueAttribute()) {

			console(redText("Переданное значение поля new_unique_attribute не совпадает с тем, что сейчас выставлено на сервере в ldap.user_unique_attribute"));
			return false;
		}
		self::$_new_unique_attribute = $options["new_unique_attribute"];

		// проверяем что прошлое и новое значение отличаются
		if (self::$_new_unique_attribute == self::$_old_unique_attribute) {

			console(redText("Одинаковое значение полей old_unique_attribute и new_unique_attribute, миграция не требуется"));
			return false;
		}

		return true;
	}

	/**
	 * Основной метод выполнения скрипта
	 */
	public static function doWork(): void
	{

		// Если отключена возможность авторизации через LDAP, выходим
		if (!Gateway_Socket_Pivot::isLdapAuthAvailable()) {

			console("Возможность авторизации через ldap отключена. Скрипт завершается");
			return;
		}

		// парсим аргументы
		if (!self::parseArgs()) {
			return;
		}

		// получаем список всех пользователей compass через ldap
		$account_user_rel_list = Domain_Ldap_Entity_AccountUserRel::getAll();

		// устанавливаем соединение с LDAP
		$client = Domain_Ldap_Entity_Client::resolve(
			Domain_Ldap_Entity_Config::getServerHost(),
			Domain_Ldap_Entity_Config::getServerPort(),
			Domain_Ldap_Entity_Config::getUseSslFlag(),
			Domain_Ldap_Entity_Client_RequireCertStrategy::convertStringToConst(Domain_Ldap_Entity_Config::getRequireCertStrategy()),
		);
		if (!$client->bind(Domain_Ldap_Entity_Config::getUserSearchAccountDn(), Domain_Ldap_Entity_Config::getUserSearchAccountPassword())) {

			console("Механизм ldap.account_updating завершился неудачей. Не удалось аутентифицироваться в LDAP под учетной записью [username: %s]", Domain_Ldap_Entity_Config::getUserSearchAccountDn());
			return;
		}

		$filter                     = "(objectClass=person)";
		$user_profile_update_filter = Domain_Ldap_Entity_Config::getUserProfileUpdateFilter();
		if (mb_strlen($user_profile_update_filter) > 0) {
			$filter = $user_profile_update_filter;
		}

		// получаем список всех учетных записей в LDAP со всеми атрибутами, с пагинацией
		[$count, $entry_list] = $client->searchEntries(
			Domain_Ldap_Entity_Config::getUserSearchBase(),
			$filter,
			Domain_Ldap_Entity_Config::getUserSearchPageSize()
		);

		$entry_list = array_map(static fn (array $entry) => Domain_Ldap_Entity_Utils::prepareEntry($entry), $entry_list);

		$entry_list_count = count($entry_list);
		console("Всего пользователей в ldap {$entry_list_count}");

		// закрываем соединение
		$client->unbind();

		// получаем только активные аккаунты
		$found_account_users_list = self::_getExistProfileList($account_user_rel_list, $entry_list);

		$found_account_users_list_count = count($found_account_users_list);
		console("Всего пользователей со связью ldap в компас {$found_account_users_list_count}");
		if ($found_account_users_list_count == 0) {
			console("Не найдено пользователей со связью в Compass. Убедитесь, что значение переданного параметра old_unique_attribute соответствует тому, по которому пользователи авторизовывались ранее");
			return;
		}

		$user_list = self::_checkWhatUniqueAttributeIsUnique($found_account_users_list);
		if (count($user_list) > 1) {

			$user_list_json = toJson($user_list);
			console("Переданный аттрибут new_unique_attribute совпал у пользователей: {$user_list_json}");
			return;
		}

		// идем обновлять uid
		self::_migrateUserList($found_account_users_list);
	}

	/**
	 * Фильтруем список связей «учетная запись LDAP» <–> «Compass пользователей»
	 */
	protected static function _getExistProfileList(array $account_user_rel_list, array $found_entry_list): array
	{

		// из списка связей сделаем словарь, чтобы можно быстрей получить нужную запись по uid
		// uid приводим к нижнему регистру, чтобы сравнение было без учета регистра
		$account_user_rel_map = [];
		foreach ($account_user_rel_list as $account_user_rel) {

			$uid                        = mb_strtolower((string) $account_user_rel->uid);
			$account_user_rel_map[$uid] = $account_user_rel;
		}

		// из списка учетных записей сделаем словарь, чтобы можно быстрей получить нужную запись по uid
		// ищем по старому значению поля (тому что сейчас записано в базе)
		// значение старого атрибута также приводим к нижнему регистру
		$old_unique_attribute = mb_strtolower(self::$_old_unique_attribute);

		$found_entry_map = [];
		foreach ($found_entry_list as $entry) {

			if (!isset($entry[$old_unique_attribute])) {
				continue;
			}

			$uid                   = mb_strtolower((string) $entry[$old_unique_attribute]);
			$found_entry_map[$uid] = $entry;
		}

		// массив аккаунтов, что нашли в компасе
		$found_account_users_list = [];

		// ищем только активные аккаунты
		foreach ($found_entry_map as $uid => $entry) {

			// если для такого аккаунта не существует связи в приложении, то пропускаем
			if (!isset($account_user_rel_map[$uid])) {
				continue;
			}

			// если активный, добавляем его
			if (!Domain_Ldap_Entity_AccountChecker::isDisabledAccount($entry)) {

				$item = [
					"entry"            => $entry,
					"account_user_rel" => $account_user_rel_map[$uid],
				];

				$found_account_users_list[] = $item;
			}
		}

		return $found_account_users_list;
	}

	/**
	 * Проверяем что в массиве пользователей, все значения нового переданного поля уникальные и не совпадают
	 */
	protected static function _checkWhatUniqueAttributeIsUnique(array $found_account_users_list): array
	{

		$unique_attribute_user_list = [];

		// проходимся по всем пользователям
		foreach ($found_account_users_list as $key => $account) {

			$unique_attribute_user_list[$account["account_user_rel"]->uid][] = $account["account_user_rel"]->user_id;
		}

		foreach ($unique_attribute_user_list as $uid => $user_id_list) {

			if (count($user_id_list) > 1) {
				return $user_id_list;
			}
		}

		return [];
	}

	/**
	 * Обновляем uid в бд
	 */
	protected static function _migrateUserList(array $found_account_users_list): void
	{

		// проходимся по всем пользователям
		foreach ($found_account_users_list as $key => $account) {

			$user_name = $account["account_user_rel"]->username;
			$old_uid   = $account["account_user_rel"]->uid;
			$new_uid   = $account["entry"][mb_strtolower(self::$_new_unique_attribute)];

			if (self::$_is_dry_run) {

				console("Планируем обновить uid для пользователя {$user_name} с {$old_uid} на {$new_uid}");
			} else {

				Gateway_Db_LdapData_LdapAccountUserRel::set(
					$old_uid,
					[
						"uid"        => $new_uid,
						"updated_at" => time(),
					]
				);

				Gateway_Db_LdapData_TotpUserRel::set(
					$old_uid,
					[
						"uid"        => $new_uid,
						"updated_at" => time(),
					]
				);

				try {

					Gateway_Db_LdapData_MailUserRel::set(
						$old_uid,
						[
							"uid"        => $new_uid,
							"updated_at" => time(),
						]
					);
				} catch (\PDOException $e) {

					// если это дубликат
					if ($e->getCode() != 23000) {
						throw $e;
					}
				}

				console("Обновили uid для пользователя {$user_name} с {$old_uid} на {$new_uid}");
			}

			$user_id                        = $account["account_user_rel"]->user_id;
			$found_account_users_list_count = count($found_account_users_list);
			$counter                        = $key + 1;

			console("{$user_id} [$counter/$found_account_users_list_count]");
		}
	}
}

// начинаем выполнение
Migrate_User_To_New_User_Unique_Attribute::doWork();
