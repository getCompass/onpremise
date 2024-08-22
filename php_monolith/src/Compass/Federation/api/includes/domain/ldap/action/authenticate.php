<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс описывающий действие попытки аутентификации в учетную запись LDAP
 * @package Compass\Federation
 */
class Domain_Ldap_Action_Authenticate {

	/**
	 * @return array
	 * @throws Domain_Ldap_Exception_ProtocolError
	 * @throws ParseFatalException
	 */
	public static function try(string $username, string $password):array {

		// валидируем username:password
		$client = Domain_Ldap_Entity_Client::resolve(Domain_Ldap_Entity_Config::getServerHost(), Domain_Ldap_Entity_Config::getServerPort());

		// патаемся пройти аутентификацию по переданным кредам учетной записи LDAP
		$client = self::_tryBind($client, $username, $password);

		// получаем информацию об учетной записе
		[$count, $entry_list] = $client->searchEntries(Domain_Ldap_Entity_Config::getUserSearchBase(), Domain_Ldap_Entity_Utils::formatUserFilter(Domain_Ldap_Entity_Config::getUserUniqueAttribute(), $username), 1);

		// закрываем соединение
		$client->unbind();

		// проверяем наличие результатов
		if ($count === 0) {
			throw new ParseFatalException("unexpected behaviour, account not found");
		}

		return $entry_list[0];
	}

	/**
	 * патаемся пройти аутентификацию в учетной записи LDAP
	 *
	 * @return Domain_Ldap_Entity_Client_Interface
	 * @throws Domain_Ldap_Exception_ProtocolError
	 */
	protected static function _tryBind(Domain_Ldap_Entity_Client_Interface $client, string $username, string $password):Domain_Ldap_Entity_Client_Interface {

		try {

			// сперва пытаемся аутентифицироваться с помощью ldap_bind сформировав DN учетной записи
			$client->bind(Domain_Ldap_Entity_Utils::makeUserDn(Domain_Ldap_Entity_Config::getUserSearchBase(), Domain_Ldap_Entity_Config::getUserUniqueAttribute(), $username), $password);

			// если не упало исключение, то успех – завершаем функцию
			return $client;
		} catch (Domain_Ldap_Exception_ProtocolError $e) {

			// сохраняем исключение и дальше пробуем альтернативный вариант для Active Directory
			$exception = $e;
		}

		// проверяем, что в LDAP-конфиге есть информация об учетной записи для поиска
		if (Domain_Ldap_Entity_Config::getUserSearchAccountDn() === "") {

			// такой информации нет, значит завершаем с ошибкой
			throw $exception;
		}

		try {

			// пытаемся авторизоваться из под аккаунта для поиска
			$client->bind(Domain_Ldap_Entity_Config::getUserSearchAccountDn(), Domain_Ldap_Entity_Config::getUserSearchAccountPassword());
		} catch (Domain_Ldap_Exception_ProtocolError) {

			// больше шансов нет – кидаем первоначальное исключение
			throw $exception;
		}

		// пытаемся найти DN целевой учетной записи, в которую пытаются авторизоваться
		[$count, $entry_list] = $client->searchEntries(Domain_Ldap_Entity_Config::getUserSearchBase(), Domain_Ldap_Entity_Utils::formatUserFilter(Domain_Ldap_Entity_Config::getUserUniqueAttribute(), $username), 1);

		// если не удалось ничего найти
		if ($count < 1) {
			throw  $exception;
		}

		// если в LDAP-конфиге есть информацию об учетной записи для поиска, то с ее помощью
		$dn = Domain_Ldap_Entity_Utils::getDnAttribute($entry_list[0]);

		// получили DN целевой учетной записи и пытаемся финально авторизоваться
		$client->bind($dn, $password);

		return $client;
	}
}