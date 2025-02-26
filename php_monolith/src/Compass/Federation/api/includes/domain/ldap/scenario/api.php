<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\BlockException;

/**
 * класс содержит логику аутентификации по протоколу LDAP
 * @package Compass\Federation
 */
class Domain_Ldap_Scenario_Api {

	/**
	 * пытаемся аутентифицировать учетную запись в LDAP
	 *
	 * @param string $username
	 * @param string $password
	 *
	 * @return string
	 * @throws BlockException
	 * @throws \queryException
	 * @throws Domain_Ldap_Exception_ProtocolError
	 * @throws ParseFatalException
	 */
	public static function tryAuthenticate(string $username, string $password):string {

		// проверяем, достигнут ли лимит неудачных попыток аутентификации для этого IP адреса
		$antispam_block_key = Type_Antispam_Ip::overrideBlockKeyLimit(Type_Antispam_Ip::LDAP_FAILED_TRY_AUTHENTICATE, Domain_Ldap_Entity_Config::getLimitOfIncorrectAuthAttempts());
		Type_Antispam_Ip::check($antispam_block_key);

		try {
			$entry = Domain_Ldap_Action_Authenticate::try($username, $password);
		} catch (Domain_Ldap_Exception_ProtocolError $e) {

			Type_Antispam_Ip::checkAndIncrementBlock($antispam_block_key);

			// сохраняем провальную попытку
			Domain_Ldap_Entity_AuthToken::save("", $username, "", Domain_Ldap_Entity_AuthToken::STATUS_LDAP_AUTH_FAILED, []);
			Domain_Ldap_Entity_Logger::log("Неудачная попытка аутентификации [username: $username]", ["error_num" => $e->getErrorNumber(), "message" => $e->getMessage()]);

			throw $e;
		}

		// подготавливаем найденную учетную запись
		$entry          = self::_prepareAvatarBlob($entry);
		$prepared_entry = Domain_Ldap_Entity_Utils::prepareEntry($entry);
		[$user_unique_attribute_value, $dn] = Domain_Ldap_Entity_Utils::parseEntryAttributes($prepared_entry, Domain_Ldap_Entity_Config::getUserUniqueAttribute());

		// проверяем что по уникальному атрибуту совпали с переданными данными для авторизации
		$normalized_username                    = mb_strtolower($username);
		$normalized_user_unique_attribute_value = mb_strtolower($user_unique_attribute_value);
		if ($normalized_username !== $normalized_user_unique_attribute_value) {

			// если не совпали, значит нашли не свою запись
			throw new Domain_Ldap_Exception_ProtocolError_FilterError("incorrect ldap.user_search_filter");
		}

		// сохраняем попытку аутентификации
		$auth_token_data = Domain_Ldap_Entity_AuthToken_Data::initData();
		$auth_token_data = Domain_Ldap_Entity_AuthToken_Data::setEntry($auth_token_data, $entry);
		$auth_token      = Domain_Ldap_Entity_AuthToken::save($user_unique_attribute_value, $username, $dn, Domain_Ldap_Entity_AuthToken::STATUS_LDAP_AUTH_COMPLETE, $auth_token_data);

		return $auth_token->ldap_auth_token;
	}

	/**
	 * подготавливаем avatar blob, кодируя его в base64
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	protected static function _prepareAvatarBlob(array $entry):array {

		// проверяем, мапится ли аватар в приложение
		// если мапится, то ничего не делаем
		$avatar_mapped_field = Domain_Sso_Entity_CompassMapping_Config::getMappedFieldContent(Domain_Sso_Entity_CompassMapping_Config::MAPPED_FIELD_AVATAR);
		if (mb_strlen($avatar_mapped_field) == 0) {
			return $entry;
		}

		// приводим к нижнему регистру
		$avatar_mapped_field = trim(mb_strtolower($avatar_mapped_field), "{}");

		// ищем параметр, где хранится аватар
		foreach ($entry as $entry_field => $entry_field_values) {

			// приводим к нижнему регистру
			$entry_field = mb_strtolower($entry_field);

			// если это не параметр с аватаром, то пропускаем
			if ($entry_field !== $avatar_mapped_field) {
				continue;
			}

			// нашли параметр, проверяем, что есть значение
			// если значений нет, то пропускаем
			if ($entry_field_values["count"] == 0) {
				continue;
			}

			// иначе для каждого значения кодируем blob в base64, если он уже не в таком формате
			for ($i = 0; $i < $entry_field_values["count"]; $i++) {

				// если это уже base64, то не трогаем
				if (base64_decode($entry_field_values[$i], true) !== false) {
					continue;
				}

				// кодируем в base64
				$entry[$entry_field][$i] = base64_encode($entry_field_values[$i]);
			}
		}

		return $entry;
	}
}