<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\InvalidMail;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\DomainException;
use BaseFrame\Exception\Gateway\DBShardingNotFoundException;
use BaseFrame\Exception\Gateway\QueryFatalException;
use BaseFrame\Exception\Request\AnswerCommandException;
use BaseFrame\Exception\Request\BlockException;
use BaseFrame\System\Mail;

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
	public static function tryAuthenticate(string $username, string $password):Struct_Db_LdapData_LdapAuth {

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
		$entry          = self::_prepareBinaryData($entry);

		[$user_login_attribute_value, $user_unique_attribute_value, $dn]
			= Domain_Ldap_Entity_Utils::parseEntryAttributes(
			$prepared_entry,
			Domain_Ldap_Entity_Config::getUserLoginAttribute(),
			Domain_Ldap_Entity_Config::getUserUniqueAttribute());

		// проверяем что по уникальному атрибуту совпали с переданными данными для авторизации
		$normalized_username                   = mb_strtolower($username);
		$normalized_user_login_attribute_value = mb_strtolower($user_login_attribute_value);
		if ($normalized_username !== $normalized_user_login_attribute_value) {

			// если не совпали, значит нашли не свою запись
			throw new Domain_Ldap_Exception_ProtocolError_FilterError("incorrect ldap.user_search_filter");
		}

		// сохраняем попытку аутентификации
		$auth_token_data = Domain_Ldap_Entity_AuthToken_Data::initData();
		$auth_token_data = Domain_Ldap_Entity_AuthToken_Data::setEntry($auth_token_data, $entry);
		$auth_token      = Domain_Ldap_Entity_AuthToken::save($user_unique_attribute_value, $username, $dn, Domain_Ldap_Entity_AuthToken::STATUS_LDAP_AUTH_COMPLETE, $auth_token_data);

		return $auth_token;
	}

	/**
	 * пытаемся аутентифицировать учетную запись в LDAP
	 *
	 * @param string       $username
	 * @param string       $password
	 * @param string|false $mail_confirm_story_map
	 *
	 * @return string
	 * @throws AnswerCommandException
	 * @throws BlockException
	 * @throws DBShardingNotFoundException
	 * @throws Domain_Ldap_Exception_Mail_ConfirmStoryNotFound
	 * @throws Domain_Ldap_Exception_Mail_LdapMailNotFound
	 * @throws Domain_Ldap_Exception_Mail_StageIsInvalid
	 * @throws Domain_Ldap_Exception_ProtocolError
	 * @throws ParseFatalException
	 * @throws QueryFatalException
	 * @throws ReturnFatalException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function getToken(string $username, string $password, string|false $mail_confirm_story_map):string {

		$antispam_block_key = Type_Antispam_Ip::overrideBlockKeyLimit(Type_Antispam_Ip::LDAP_FAILED_TRY_AUTHENTICATE, Domain_Ldap_Entity_Config::getLimitOfIncorrectAuthAttempts());

		// получаем запись из ldap
		$auth_token = self::tryAuthenticate($username, $password);
		$config_2fa = Domain_Ldap_Entity_2faConfig::instance();

		// если 2fa отключено, просто отдаем токен
		if (!$config_2fa->authorization_2fa_enabled) {
			return $auth_token->ldap_auth_token;
		}

		// если передали мапу истории, то проверяем, правильный ли этап. Если да, отдаем токен
		if ($mail_confirm_story_map) {

			$mail_confirm_story = Domain_Ldap_Entity_Mail_ConfirmStory::get($mail_confirm_story_map);

			if ($mail_confirm_story->stage !== Domain_Ldap_Entity_Mail_ConfirmStory::STAGE_GET_LDAP_AUTH_TOKEN) {
				throw new Domain_Ldap_Exception_Mail_StageIsInvalid();
			}

			return $mail_confirm_story->ldap_auth_token;
		}

		// если в конфиге есть поле с почтой, то получаем ее из LDAP записи и привязываем к аккаунту
		if ($config_2fa->mail_mapped_field !== "") {

			self::_bindLdapMail(
				$auth_token->uid,
				$config_2fa->mail_mapped_field,
				Domain_Ldap_Entity_AuthToken_Data::getEntry($auth_token->data)
			);
		}

		// получаем почту
		try {

			$mail_user_rel = Domain_Ldap_Entity_Mail_UserRel::get($auth_token->uid);
			$mail          = $mail_user_rel->mail;
		} catch (Domain_Ldap_Exception_Mail_UserRelNotFound) {
			$mail = null;
		}

		// создаем новую попытку подтверждения почты
		$ldap_mail_confirm_story_info = self::_createConfirmMailStory($auth_token, $mail);

		Type_Antispam_Ip::checkAndIncrementBlock($antispam_block_key);
		throw new AnswerCommandException("need_confirm_ldap_mail", $ldap_mail_confirm_story_info);
	}

	/**
	 * Привязать почту с LDAP
	 *
	 * @param string $uid
	 * @param string $mail_mapped_field
	 * @param array  $entry
	 *
	 * @return void
	 * @throws DBShardingNotFoundException
	 * @throws Domain_Ldap_Exception_Mail_LdapMailNotFound
	 * @throws ParseFatalException
	 * @throws QueryFatalException
	 */
	public static function _bindLdapMail(string $uid, string $mail_mapped_field, array $entry):void {

		$mail_mapped_field = trim($mail_mapped_field, "{}");
		$prepared_entry    = Domain_Ldap_Entity_Utils::prepareEntry($entry);

		if (!isset($prepared_entry[$mail_mapped_field])) {
			throw new Domain_Ldap_Exception_Mail_LdapMailNotFound();
		}

		try {

			$ldap_mail = Domain_Ldap_Entity_Utils::getUniqueAttributeValue($prepared_entry, $mail_mapped_field);
			$ldap_mail = new Mail($ldap_mail);
		} catch (InvalidMail) {
			throw new Domain_Ldap_Exception_Mail_LdapMailNotFound();
		}

		Domain_Ldap_Entity_Mail_UserRel::create(
			$uid,
			Domain_Ldap_Entity_Mail_UserRel::MAIL_SOURCE_LDAP,
			$ldap_mail->mail(),
			true
		);
	}

	/**
	 * Создать новую запись подтверждения почты
	 *
	 * @param Struct_Db_LdapData_LdapAuth $auth_token
	 * @param string|false                $mail
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\DBShardingNotFoundException
	 * @throws \BaseFrame\Exception\Gateway\QueryFatalException
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected static function _createConfirmMailStory(Struct_Db_LdapData_LdapAuth $auth_token, ?string $mail = null):array {

		// если почты нет, то перекидываем на этап добавления новой
		$stage = $mail
			? Domain_Ldap_Entity_Mail_ConfirmStory::STAGE_CONFIRM_CURRENT_MAIL
			: Domain_Ldap_Entity_Mail_ConfirmStory::STAGE_ENTER_NEW_MAIL;

		// создаем новую историю подтверждения почты
		$mail_confirm_story = Domain_Ldap_Entity_Mail_ConfirmStory::create(
			$auth_token->ldap_auth_token,
			$auth_token->uid,
			$stage
		);

		$code_available_attempts = 0;
		$next_resend_at          = 0;

		// если почта была передана, отправляем письмо с кодом
		if ($mail) {

			$mail_confirm_via_code_story = Domain_Ldap_Entity_Mail_ConfirmViaCodeStory::create(
				$mail_confirm_story->mail_confirm_story_id,
				$mail
			)->sendConfirmCode($mail_confirm_story->stage);

			$code_available_attempts = Domain_Ldap_Entity_Mail_ConfirmViaCodeStory::MAX_ERROR_COUNT;
			$next_resend_at          = $mail_confirm_via_code_story->next_resend_at;
			$mail                    = new Mail($mail);
		}

		return Onpremiseweb_Format::ldapMailConfirmStoryInfo($mail_confirm_story, $mail, $code_available_attempts, $next_resend_at);
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

				// если это ссылка, то пропускаем
				if (mb_strpos($entry_field_values[$i], "http") === 0) {
					continue;
				}

				// кодируем в base64
				$entry[$entry_field][$i] = base64_encode($entry_field_values[$i]);
			}
		}

		return $entry;
	}

	/**
	 * Переводим бинарные данные в вид, который можно сохранять в базу
	 *
	 * @param array $entry
	 *
	 * @return array
	 */
	protected static function _prepareBinaryData(array $entry):array {

		if (isset($entry["objectsid"])) {
			$entry["objectsid"][0] = Domain_Ldap_Entity_Utils::sidBinToString($entry["objectsid"][0]);
		}

		if (isset($entry["objectguid"])) {
			$entry["objectguid"][0] = Domain_Ldap_Entity_Utils::uuidBinToString($entry["objectguid"][0]);
		}

		return $entry;
	}
}