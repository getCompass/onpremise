<?php

namespace Compass\Federation;

use BaseFrame\Exception\BaseException;
use BaseFrame\Exception\Domain\ParseFatalException;
use Exception;

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
		$client = Domain_Ldap_Entity_Client::resolve(Domain_Ldap_Entity_Config::getServerHost(), Domain_Ldap_Entity_Config::getServerPort());
		if (!$client->bind(Domain_Ldap_Entity_Config::getAccountDisablingMonitoringUserDn(), Domain_Ldap_Entity_Config::getAccountDisablingMonitoringUserPassword())) {

			Domain_Ldap_Entity_Logger::log(sprintf("Механизм ldap.account_checking завершился неудачей. Не удалось аутентифицироваться в LDAP под учетной записью [username: %s]", Domain_Ldap_Entity_Config::getAccountDisablingMonitoringUserDn()));

			// выполним задачу снова через интервал указанный в конфиге
			$interval = Domain_Ldap_Entity_Config::getAccountDisablingMonitoringInterval();
			return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_NEXT_ITERATION_REQUIRED, time() + Domain_Ldap_Entity_Utils::convertIntervalToSec($interval));
		}

		// список атрибутов, которые интересуют нас в контексте функции
		$attribute_list = [Domain_Ldap_Entity_Config::getUserUniqueAttribute(), "pwdAccountLockedTime", "accountStatus", "nsAccountLock", "userAccountControl"];

		// получаем список всех учетных записей в LDAP
		[$count, $entry_list] = $client->searchEntries(Domain_Ldap_Entity_Config::getUserSearchBase(), "(objectClass=person)", $attribute_list);
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