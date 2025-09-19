<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use cs_SocketRequestIsFailed;

require_once __DIR__ . "/../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1); // nosemgrep
set_time_limit(0);

/**
 * Класс для обновления данных пользователя в компасе соответственно данным в ldap
 */
class Update_Ldap_User_Profile {

	/**
	 * Основной метод выполнения скрипта
	 */
	public static function doWork():void {


		// Если отключена возможность авторизации через LDAP, выходим
		if (!Gateway_Socket_Pivot::isLdapAuthAvailable()) {

			console("Возможность авторизации через ldap отключена. Скрипт завершается");
			return;
		}

		// получаем список всех ранее авторизованных пользователей compass через ldap
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

		$filter                       = "(objectClass=person)";
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

		$entry_list = array_map(static fn(array $entry) => Domain_Ldap_Entity_Utils::prepareEntry($entry), $entry_list);

		$entry_list_count = count($entry_list);
		console("Всего пользователей в ldap {$entry_list_count}");

		// закрываем соединение
		$client->unbind();

		// получаем только активные аккаунты
		$found_account_users_list = self::_getExistProfileList($account_user_rel_list, $entry_list);

		$found_account_users_list_count = count($found_account_users_list);
		console("Всего пользователей со связью ldap в компас {$found_account_users_list_count}");

		// актуализируем данные
		self::_updateCompassProfileList($found_account_users_list);;
	}

	/**
	 * Фильтруем список связей «учетная запись LDAP» <–> «Compass пользователей»
	 */
	protected static function _getExistProfileList(array $account_user_rel_list, array $found_entry_list):array {

		// оставим в списке только активные связи
		$account_user_rel_list = array_filter($account_user_rel_list, static fn(Struct_Db_LdapData_LdapAccountUserRel $account_user_rel) => $account_user_rel->status == Domain_Ldap_Entity_AccountUserRel::STATUS_ACTIVE);

		// из списка связей сделаем словарь, чтобы можно быстрей получить нужную запись по uid
		$account_user_rel_map = array_column($account_user_rel_list, null, "uid");

		// из списка учетных записей сделаем словарь, чтобы можно быстрей получить нужную запись по uid
		$found_entry_map = array_column($found_entry_list, null, mb_strtolower(Domain_Ldap_Entity_Config::getUserUniqueAttribute()));

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
	 * Обновляем сущности пользователей
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws ReturnFatalException
	 */
	protected static function _updateCompassProfileList(array $found_account_users_list):void {

		// ищем отключенные аккаунты
		foreach ($found_account_users_list as $key => $account) {

			$ldap_account_data = Domain_Ldap_Entity_AccountData::parse($account["entry"], $account["account_user_rel"]->username);

			try {

				Gateway_Socket_Pivot::actualizeProfileData($account["account_user_rel"]->user_id, self::_prepareLdapAccountData($ldap_account_data->format()));
			} catch (cs_SocketRequestIsFailed) {
				Domain_Ldap_Entity_Logger::log(sprintf("Механизм ldap.account_updating завершился неудачей. Не смогли обновить данные для пользователя [user_id: %s]", $account["account_user_rel"]->user_id));
			}

			$user_id                        = $account["account_user_rel"]->user_id;
			$found_account_users_list_count = count($found_account_users_list);
			$counter                        = $key + 1;

			console("Обновили данные для пользователя {$user_id} [$counter/$found_account_users_list_count]");
		}
	}

	/**
	 * Заменяем null на пустые значения чтобы можно было отправить сокетом
	 */
	protected static function _prepareLdapAccountData(array $data):array {

		foreach ($data as $key => $value) {

			if (!isset($value)) {
				unset($data[$key]);
			}
		}
		return $data;
	}
}

// начинаем выполнение
Update_Ldap_User_Profile::doWork();