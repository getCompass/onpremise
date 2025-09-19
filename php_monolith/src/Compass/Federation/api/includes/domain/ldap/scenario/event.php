<?php

namespace Compass\Federation;

use BaseFrame\Exception\BaseException;
use BaseFrame\Exception\Domain\ParseFatalException;
use Exception;
use BaseFrame\Exception\Domain\ReturnFatalException;
use cs_SocketRequestIsFailed;

/**
 * события LDAP
 * @package Compass\Federation
 */
class Domain_Ldap_Scenario_Event {

	/**
	 * запускаем механизм автоматической блокировки пользователей Compass в ответ на блокировку связанных
	 * с ними учетных записей в LDAP каталоге
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \parseException
	 *
	 * @long
	 */
	#[Type_Task_Attribute_Executor(Type_Event_Ldap_AccountChecker::EVENT_TYPE, Struct_Event_Ldap_AccountChecker::class)]
	public static function checkingAccounts(Struct_Event_Ldap_AccountChecker $event_data):Type_Task_Struct_Response {

		// если отключен механизм автоматической блокировки Compass пользователей
		// или отключена возможность авторизации через LDAP
		if (!Domain_Ldap_Entity_Config::isAccountDisablingMonitoringEnabled() || !Gateway_Socket_Pivot::isLdapAuthAvailable()) {

			// проверим через 5 минут
			$next_time = time() + 5 * 60;
			return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_NEXT_ITERATION_REQUIRED, $next_time);
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

			Domain_Ldap_Entity_Logger::log(sprintf("Механизм ldap.account_checking завершился неудачей. Не удалось аутентифицироваться в LDAP под учетной записью [username: %s]", Domain_Ldap_Entity_Config::getUserSearchAccountDn()));

			// выполним задачу снова через интервал указанный в конфиге
			$interval = Domain_Ldap_Entity_Config::getAccountDisablingMonitoringInterval();
			return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_NEXT_ITERATION_REQUIRED, time() + Domain_Ldap_Entity_Utils::convertIntervalToSec($interval));
		}

		// список атрибутов, которые интересуют нас в контексте функции
		$attribute_list = [Domain_Ldap_Entity_Config::getUserUniqueAttribute(), "pwdAccountLockedTime", "accountStatus", "nsAccountLock", "userAccountControl"];

		// получаем список всех учетных записей в LDAP, с пагинацией
		[$count, $entry_list] = $client->searchEntries(Domain_Ldap_Entity_Config::getUserSearchBase(), "(objectClass=person)", Domain_Ldap_Entity_Config::getUserSearchPageSize(), $attribute_list);
		$entry_list = array_map(static fn(array $entry) => Domain_Ldap_Entity_Utils::prepareEntry($entry), $entry_list);

		// закрываем соединение
		$client->unbind();

		// определяем список отключенных и удаленных LDAP аккаунтов со связями
		[$disabled_account_user_rel_list, $deleted_account_user_rel_list] = Domain_Ldap_Entity_AccountChecker::filterAccountList($account_user_rel_list, $entry_list);

		// получаем объект класса, который будет блокировать пользователя Compass у которого отключили связанную учетную запись LDAP
		// и запускаем блокировку для всех таких аккаунтов
		self::_blockDisabledAccountList($disabled_account_user_rel_list);

		// получаем объект класса, который будет блокировать пользователя Compass у которого удалили связанную учетную запись LDAP
		// и запускаем блокировку для всех таких аккаунтов
		self::_blockDeletedAccountList($deleted_account_user_rel_list);

		// выполним задачу снова через интервал указанный в конфиге
		$interval = Domain_Ldap_Entity_Config::getAccountDisablingMonitoringInterval();
		return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_NEXT_ITERATION_REQUIRED, time() + Domain_Ldap_Entity_Utils::convertIntervalToSec($interval));
	}

	/**
	 * Запускаем механизм автоматического обновления пользователей Compass в ответ на обновление связанных
	 * с ними учетных записей в LDAP каталоге
	 *
	 * @long
	 */
	#[Type_Task_Attribute_Executor(Type_Event_Ldap_ProfileUpdater::EVENT_TYPE, Struct_Event_Ldap_ProfileUpdater::class)]
	public static function updatingProfiles(Struct_Event_Ldap_ProfileUpdater $event_data):Type_Task_Struct_Response {

		// если отключен механизм автоматического обновления пользователей
		// или отключена возможность авторизации через LDAP
		if (!Domain_Ldap_Entity_Config::isProfileUpdateEnabled() || !Gateway_Socket_Pivot::isLdapAuthAvailable()) {

			// проверим через 30 минут
			$next_time = time() + 30 * 60;
			return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_NEXT_ITERATION_REQUIRED, $next_time);
		}

		// получаем интервал для синхронизации
		$interval = Domain_Ldap_Entity_Config::getProfileUpdateInterval();

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

			Domain_Ldap_Entity_Logger::log(sprintf("Механизм ldap.account_updating завершился неудачей. Не удалось аутентифицироваться в LDAP под учетной записью [username: %s]", Domain_Ldap_Entity_Config::getUserSearchAccountDn()));

			// выполним задачу снова через интервал указанный в конфиге
			return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_NEXT_ITERATION_REQUIRED, time() + Domain_Ldap_Entity_Utils::convertIntervalToSec($interval));
		}

		// Текущее время
		$current_time = new \DateTime("now", new \DateTimeZone("UTC"));

		// Вычисляем время начала интервала (текущее время минус интервал)
		$interval_in_second = Domain_Ldap_Entity_Utils::convertIntervalToSec($interval);
		$start_time         = clone $current_time;
		$start_time->modify("-$interval_in_second seconds");

		// добавляем буфер на возможный лаг в обновлении записи в ад
		$start_time->modify("-180 seconds");

		// Форматируем время начала интервала для LDAP
		$timestamp = $start_time->format("YmdHis.0\Z");

		// получаем время следующей проверки
		$next_time = time();

		// добавляем в фильтр модификатор для времени поиска:
		// ищем либо по whenChanged (AD), либо по modifyTimestamp (FreeIPA/OpenLDAP)
		$filter                       = "(&(objectClass=person)(|(whenChanged>=$timestamp)(modifyTimestamp>=$timestamp)))";
		$user_profile_update_filter = Domain_Ldap_Entity_Config::getUserProfileUpdateFilter();
		if (mb_strlen($user_profile_update_filter) > 0) {
			$filter = "(&{$user_profile_update_filter}(|(whenChanged>=$timestamp)(modifyTimestamp>=$timestamp)))";
		}

		// получаем список всех учетных записей в LDAP со всеми атрибутами, с пагинацией
		[$count, $entry_list] = $client->searchEntries(
			Domain_Ldap_Entity_Config::getUserSearchBase(),
			$filter,
			Domain_Ldap_Entity_Config::getUserSearchPageSize()
		);
		$entry_list = array_map(static fn(array $entry) => Domain_Ldap_Entity_Utils::prepareEntry($entry), $entry_list);

		// закрываем соединение
		$client->unbind();

		// получаем только активные аккаунты
		$found_account_users_list = self::_getExistProfileList($account_user_rel_list, $entry_list);

		// актуализируем данные
		self::_updateCompassProfileList($found_account_users_list);;

		// выполним задачу снова через интервал указанный в конфиге
		return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_NEXT_ITERATION_REQUIRED, $next_time + Domain_Ldap_Entity_Utils::convertIntervalToSec($interval));
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
	 * @throws ReturnFatalException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws ParseFatalException
	 */
	protected static function _updateCompassProfileList(array $found_account_users_list):void {

		// ищем отключенные аккаунты
		foreach ($found_account_users_list as $account) {

			$ldap_account_data = Domain_Ldap_Entity_AccountData::parse($account["entry"], $account["account_user_rel"]->username);

			try {
				Gateway_Socket_Pivot::actualizeProfileData($account["account_user_rel"]->user_id, self::_prepareLdapAccountData($ldap_account_data->format()));
			} catch (cs_SocketRequestIsFailed) {
				Domain_Ldap_Entity_Logger::log(sprintf("Механизм ldap.account_updating завершился неудачей. Не смогли обновить данные для пользователя [user_id: %s]", $account["account_user_rel"]->user_id));
			}
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

	/**
	 * блокируем пользователей, чьи учетные записи были помечаны заблокированным в LDAP каталоге
	 *
	 * @param Struct_Db_LdapData_LdapAccountUserRel[] $disabled_account_user_rel_list
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	protected static function _blockDisabledAccountList(array $disabled_account_user_rel_list):void {

		// получаем объект класса, который будет блокировать пользователя Compass у которого отключили связанную учетную запись LDAP
		$blocker = Domain_Ldap_Entity_UserBlocker::resolveBlocker(Domain_Ldap_Entity_Config::getUserBlockingLevelOnAccountDisabling());

		// пробегаемся по каждому аккаунту и выполняем блокировку
		foreach ($disabled_account_user_rel_list as $disabled_account_user_rel) {

			try {

				// блокируем
				$blocker->run($disabled_account_user_rel->user_id);
			} catch (BaseException|Exception $e) {

				// отлавливаем исключения, логируем и пропускаем такого пользователя, чтобы не ронять механизм
				Domain_Ldap_Entity_Logger::log("Блокировка отключенного аккаунта для user_id $disabled_account_user_rel->user_id закончилась неудачей", [
					"message" => $e->getMessage(),
				]);
				continue;
			}

			// обновляем статус связи, помечая связь заблокированной
			Domain_Ldap_Entity_AccountUserRel::setStatus($disabled_account_user_rel->uid, Domain_Ldap_Entity_AccountUserRel::STATUS_DISABLED);
		}
	}

	/**
	 * блокируем пользователей, чьи учетные записи были удалены в LDAP каталоге
	 *
	 * @param Struct_Db_LdapData_LdapAccountUserRel[] $deleted_account_user_rel_list
	 *
	 * @throws ParseFatalException
	 */
	protected static function _blockDeletedAccountList(array $deleted_account_user_rel_list):void {

		// получаем объект класса, который будет блокировать пользователя Compass у которого удалили связанную учетную запись LDAP
		$blocker = Domain_Ldap_Entity_UserBlocker::resolveBlocker(Domain_Ldap_Entity_Config::getUserBlockingLevelOnAccountRemoving());

		// пробегаемся по каждому аккаунту и выполняем блокировку
		foreach ($deleted_account_user_rel_list as $deleted_account_user_rel) {

			try {

				// блокируем
				$blocker->run($deleted_account_user_rel->user_id);
			} catch (BaseException|Exception $e) {

				// отлавливаем исключения, логируем и пропускаем такого пользователя, чтобы не ронять механизм
				Domain_Ldap_Entity_Logger::log("Блокировка удаленного аккаунта для user_id $deleted_account_user_rel->user_id закончилась неудачей", [
					"message" => $e->getMessage(),
				]);
				continue;
			}

			// удаляем связь
			Domain_Ldap_Entity_AccountUserRel::delete($deleted_account_user_rel->user_id);
		}
	}
}