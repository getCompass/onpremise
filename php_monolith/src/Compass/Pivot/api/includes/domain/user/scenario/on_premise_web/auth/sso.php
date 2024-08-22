<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Request\CaseException;

/**
 * Сценарии для работы с аутентификаций через SSO
 */
class Domain_User_Scenario_OnPremiseWeb_Auth_Sso {

	/**
	 * проводим попытку аутентификации через SSO
	 *
	 * @return array
	 * @throws CaseException
	 * @throws Domain_Link_Exception_TemporaryUnavailable
	 * @throws Domain_User_Exception_AuthStory_Expired
	 * @throws Domain_User_Exception_AuthStory_Sso_SignatureMismatch
	 * @throws Domain_User_Exception_AuthStory_Sso_UnexpectedBehaviour
	 * @throws Domain_User_Exception_AuthMethodDisabled
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws \userAccessException
	 * @throws cs_IncorrectJoinLink
	 * @throws cs_IncorrectSaltVersion
	 * @throws cs_JoinLinkIsNotActive
	 * @throws cs_JoinLinkIsUsed
	 * @throws cs_JoinLinkNotFound
	 * @throws cs_UserAlreadyInCompany
	 * @throws cs_UserAlreadyLoggedIn
	 * @throws cs_UserNotFound
	 * @long
	 */
	public static function begin(int $user_id, string $sso_auth_token, string $signature, string|bool $join_link, string $pivot_session_uniq):array {

		// проверяем, что нет текущей активной сессии
		Domain_User_Entity_Validator::assertNotLoggedIn($user_id);

		// проверяем, что способ аут-ции через SSO по протоколу OIDC включен
		Domain_User_Entity_Auth_Method::assertMethodEnabled(Domain_User_Entity_Auth_Method::METHOD_SSO);
		Domain_User_Entity_Auth_Method::assertSsoProtocol(Domain_User_Entity_Auth_Method::SSO_PROTOCOL_OIDC);

		// валидируем токен
		/** @var Struct_User_Auth_Sso_AccountData $sso_account_data */
		[$sso_account_user_id_rel, $sso_account_data] = Gateway_Socket_Federation::validateSsoOidcAuthToken($sso_auth_token, $signature);

		// проверяем, что полученные Имя Фамилия из SSO корректны
		self::_throwIfIncorrectFullName(self::_prepareFullName($sso_account_data));

		// нужно ли привязать к root пользователю sso аккаунт, если он оказался ещё не привязан
		$is_need_bind_root_user = false;
		if ($sso_account_user_id_rel == 0 && Domain_User_Entity_OnpremiseRoot::hasSsoLoginNameByList([$sso_account_data->mail, $sso_account_data->phone_number])) {

			$sso_account_user_id_rel = Domain_User_Entity_OnpremiseRoot::getUserId();
			$is_need_bind_root_user  = true;
		}

		// инкрементим блокировку
		$sso_account_user_id_rel > 0 && Type_Antispam_User::throwIfBlocked($sso_account_user_id_rel, Type_Antispam_User::AUTH_SSO);

		// валидируем ссылку-приглашение, если она передана
		try {
			$validation_result = Domain_Link_Action_OnPremiseWeb::validateJoinLinkIfNeeded($join_link, $sso_account_user_id_rel);
		} catch (cs_UserAlreadyInCompany) {
			$validation_result = null;
		}
		$join_link_uniq = is_null($validation_result) ? false : $validation_result->invite_link_rel->join_link_uniq;

		// создаем попытку аутентификации через SSO
		$story = Domain_User_Action_Auth_Sso::begin($sso_auth_token, $sso_account_user_id_rel);

		// в зависимости от кейса регистрируем и/или авторизуем пользователя
		$is_need_to_create_user = $story->isNeedToCreateUser();

		/** @var Struct_Integration_Notifier_Response_OnUserRegistered|null $integration_response */
		[$user_id, $integration_response] = $is_need_to_create_user
			? static::_confirmNotRegisteredUserAuthentication($sso_account_data, $join_link_uniq)
			: static::_confirmRegisteredUserAuthentication($story, $sso_account_data, $join_link_uniq);

		// если был создан пользователь
		if (($sso_account_user_id_rel === 0 && $user_id > 0) || $is_need_bind_root_user) {
			Gateway_Socket_Federation::createSsoOidcUserRelationship($sso_auth_token, $user_id);
		}

		// выдаем пользовательскую сессию
		Type_Session_Main::doLoginSession($user_id);

		// устанавливаем, что аутентификация прошла успешно
		$story->handleSuccess($user_id);
		Gateway_Db_PivotHistoryLogs_UserAuthHistory::insert($story->getAuthMap(), $user_id, Domain_User_Entity_AuthStory::HISTORY_AUTH_STATUS_SUCCESS, time(), 0);

		[$token,] = Domain_Solution_Action_GenerateAuthenticationToken::exec($user_id, join_link_uniq: $join_link_uniq);

		// если это регистрация без ссылки, то добавляем пользователя в первую команду
		if ($is_need_to_create_user && $join_link === false) {
			Domain_User_Action_AutoJoin::do($user_id, $pivot_session_uniq);
		}

		return [
			$token,
			$is_need_to_create_user,
			Type_User_Main::get($user_id),
			!is_null($integration_response) ? $integration_response->action_list : [],
		];
	}

	/**
	 * Выполняет кусок логики подтверждения аутентификации для уже зарегистрированного пользователя.
	 */
	protected static function _confirmRegisteredUserAuthentication(Domain_User_Entity_AuthStory $story, Struct_User_Auth_Sso_AccountData $sso_account_data, string|false $join_link_uniq):array {

		$user_id = $story->getUserId();

		if ($join_link_uniq !== false) {

			try {

				// получаем приглашение, оно должно существовать,
				// поскольку данные были получены и сверены из кэша
				$join_link_rel_row = Gateway_Db_PivotData_CompanyJoinLinkRel::get($join_link_uniq);
			} catch (\cs_RowIsEmpty) {
				throw new ReturnFatalException("invite not found");
			}

			try {

				Domain_Link_Entity_Link::validateForUser($user_id, $join_link_rel_row);
			} catch (\Exception) {
				// ничего не делаем, стоит тут как-то ошибку выкинуть, но пока ничего не делаем
			}
		}

		// добавляем в историю, что пользователь залогинился
		Domain_User_Entity_UserActionComment::addUserLoginAction($user_id, $story->getType(), $story->getAuthSsoHandler()->getAuthParameter(), getDeviceId(), getUa());

		// если включен флаг актуализиции имя фамилия после авторизации через SSO
		if (Domain_User_Entity_Auth_Config::isFullNameActualizationEnabled()) {

			// актуализируем имя фамилия для пользователя
			Domain_User_Action_UpdateProfile::do($user_id, self::_prepareFullName($sso_account_data), false);
		}

		return [$user_id, null];
	}

	/**
	 * Выполняет кусок логики для создания нового пользователя и подтверждения аутентификации.
	 */
	protected static function _confirmNotRegisteredUserAuthentication(Struct_User_Auth_Sso_AccountData $sso_account_data, string|false $join_link_uniq):array {

		// если нет автовступления и не передали ссылку, то возвращаем ошибку
		if (Domain_User_Entity_Auth_Config::getAutoJoinToTeam() === Domain_User_Entity_Auth_Config_AutoJoinEnum::DISABLED && $join_link_uniq === false) {
			throw new CaseException(1000, "registration is not allowed without invite");
		}

		// если имеется ссылка-приглашение
		$final_join_link_uniq = "";
		if ($join_link_uniq !== false) {

			try {

				// получаем приглашение, оно должно существовать,
				// поскольку данные были получены и сверены из кэша
				$join_link_rel_row    = Gateway_Db_PivotData_CompanyJoinLinkRel::get($join_link_uniq);
				$final_join_link_uniq = $join_link_rel_row->join_link_uniq;
			} catch (\cs_RowIsEmpty) {
				throw new ReturnFatalException("invite not found");
			}

			// проверим, что наше приглашение подходит для создание пользвоателя
			Domain_Link_Entity_Link::validateBeforeRegistration($join_link_rel_row);
		}

		// подготавливаем данные, полученные об аккаунте из SSO
		$full_name = trim($sso_account_data->first_name . " " . $sso_account_data->last_name);

		// регистрируем и отмечаем в истории событие
		$user                 = Domain_User_Action_Create_Human::do("", "", "", getUa(), getIp(), $full_name, "", [], 0, 0);
		$integration_response = Domain_Integration_Entity_Notifier::onUserRegistered(new Struct_Integration_Notifier_Request_OnUserRegistered(
			user_id: $user->user_id,
			auth_method: Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_AUTH_BY_SSO,
			registered_by_phone_number: $sso_account_data->phone_number,
			registered_by_mail: $sso_account_data->mail,
			join_link_uniq: $final_join_link_uniq,
		));
		Type_Phphooker_Main::sendUserAccountLog($user->user_id, Type_User_Analytics::REGISTERED);

		return [$user->user_id, $integration_response];
	}

	/**
	 * Подготавливаем Имя Фамилия, полученные от SSO провайдера
	 *
	 * @return string
	 */
	protected static function _prepareFullName(Struct_User_Auth_Sso_AccountData $sso_account_data):string {

		return trim($sso_account_data->first_name . " " . $sso_account_data->last_name);
	}

	/**
	 * Выбрасываем исключение, если пришли некорректные Имя Фамилия из SSO провайдера
	 *
	 * @throws Domain_User_Exception_AuthStory_Sso_IncorrectFullName
	 */
	protected static function _throwIfIncorrectFullName(string $full_name):void {

		if (mb_strlen($full_name) < 1) {
			throw new Domain_User_Exception_AuthStory_Sso_IncorrectFullName();
		}
	}

}