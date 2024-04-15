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
	public static function begin(int $user_id, string $sso_auth_token, string $signature, string|bool $join_link):array {

		// проверяем, что нет текущей активной сессии
		Domain_User_Entity_Validator::assertNotLoggedIn($user_id);

		// проверяем, что способ аут-ции через SSO включен
		Domain_User_Entity_Auth_Method::assertMethodEnabled(Domain_User_Entity_Auth_Method::METHOD_SSO);

		// валидируем токен
		/** @var Struct_User_Auth_Sso_AccountData $sso_account_data */
		[$sso_account_user_id_rel, $sso_account_data] = Gateway_Socket_Federation::validateSsoAuthToken($sso_auth_token, $signature);

		// определяем под каким user_id залогиним пользователя
		[$auth_user_id, $mail_phone_uniq_has_sso_account] = Domain_User_Action_Auth_Sso::resolveUser($sso_account_user_id_rel, $sso_account_data);

		// инкрементим блокировку
		$auth_user_id > 0 && Type_Antispam_User::throwIfBlocked($auth_user_id, Type_Antispam_User::AUTH_SSO);

		// валидируем ссылку-приглашение, если она передана
		try {
			$validation_result = Domain_Link_Action_OnPremiseWeb::validateJoinLinkIfNeeded($join_link, $auth_user_id);
		} catch (cs_UserAlreadyInCompany) {
			$validation_result = null;
		}
		$join_link_uniq = is_null($validation_result) ? false : $validation_result->invite_link_rel->join_link_uniq;

		// создаем попытку аутентификации через SSO
		$story = Domain_User_Action_Auth_Sso::begin($sso_auth_token, $auth_user_id);

		// в зависимости от кейса регистрируем и/или авторизуем пользователя
		$is_need_to_create_user = $story->isNeedToCreateUser();

		/** @var Struct_Integration_Notifier_Response_OnUserRegistered|null $integration_response */
		[$user_id, $integration_response] = $is_need_to_create_user
			? static::_confirmNotRegisteredUserAuthentication($sso_account_data, $join_link_uniq)
			: static::_confirmRegisteredUserAuthentication($story, $join_link_uniq);

		// если это было не создание аккаунта, то проверяем не сменилась ли почта
		// и актуализируем, если сменилась
		if (!$is_need_to_create_user) {

			$was_actualize = self::_actualizeMailIfNeeded($user_id, $sso_account_data);

			// если удалось актуализировать почту, то помечаем что в mail_uniq или phone_uniq
			// требуется актуализировать флаг has_sso_account
			$mail_phone_uniq_has_sso_account = !$was_actualize;
		}

		// если почта/номер телефона не помечаны как привязанные к sso аккаунту, то привяжем
		if (!$mail_phone_uniq_has_sso_account) {
			Domain_User_Action_Auth_Sso::updateHasSsoAccountFlag($user_id, $sso_account_data);
		}

		// если был создан пользователь
		if ($auth_user_id === 0 && $user_id > 0) {
			Gateway_Socket_Federation::createUserRelationship($sso_auth_token, $user_id);
		}

		// выдаем пользовательскую сессию
		Type_Session_Main::doLoginSession($user_id);

		// устанавливаем, что аутентификация прошла успешно
		$story->handleSuccess($user_id);
		Gateway_Db_PivotHistoryLogs_UserAuthHistory::insert($story->getAuthMap(), $user_id, Domain_User_Entity_AuthStory::HISTORY_AUTH_STATUS_SUCCESS, time(), 0);

		// если при наличии интеграции – там произошло обновление профиля, то не просим в клиенте заполнять профиль
		$is_need_to_create_user = !is_null($integration_response) && in_array(Domain_Integration_Entity_Notifier::ACTION_UPDATE_USER_PROFILE, array_column($integration_response->action_list, "action"))
			? false : $is_need_to_create_user;

		return [
			Domain_Solution_Action_GenerateAuthenticationToken::exec($user_id, join_link_uniq: $join_link_uniq),
			$is_need_to_create_user,
			Type_User_Main::get($user_id),
			!is_null($integration_response) ? $integration_response->action_list : [],
		];
	}

	/**
	 * Выполняет кусок логики подтверждения аутентификации для уже зарегистрированного пользователя.
	 */
	protected static function _confirmRegisteredUserAuthentication(Domain_User_Entity_AuthStory $story, string|false $join_link_uniq):array {

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

		return [$user_id, null];
	}

	/**
	 * Выполняет кусок логики для создания нового пользователя и подтверждения аутентификации.
	 */
	protected static function _confirmNotRegisteredUserAuthentication(Struct_User_Auth_Sso_AccountData $sso_account_data, string|false $join_link_uniq):array {

		// без ссылки не создаем нового пользователя
		if ($join_link_uniq === false) {
			throw new CaseException(1000, "registration is not allowed without invite");
		}

		try {

			// получаем приглашение, оно должно существовать,
			// поскольку данные были получены и сверены из кэша
			$join_link_rel_row = Gateway_Db_PivotData_CompanyJoinLinkRel::get($join_link_uniq);
		} catch (\cs_RowIsEmpty) {
			throw new ReturnFatalException("invite not found");
		}

		// проверим, что наше приглашение подходит для создание пользвоателя
		Domain_Link_Entity_Link::validateBeforeRegistration($join_link_rel_row);

		// подготавливаем данные, полученные об аккаунте из SSO
		$phone_number = Domain_User_Action_Auth_Sso::preparePhoneNumber($sso_account_data->phone_number);
		$mail         = Domain_User_Action_Auth_Sso::prepareMail($sso_account_data->mail);
		$full_name    = trim($sso_account_data->first_name . " " . $sso_account_data->last_name);

		// регистрируем и отмечаем в истории событие
		$user                 = Domain_User_Action_Create_Human::do($phone_number, $mail, "", getUa(), getIp(), $full_name, "", [], 0, 0);
		$integration_response = Domain_Integration_Entity_Notifier::onUserRegistered(new Struct_Integration_Notifier_Request_OnUserRegistered(
			user_id: $user->user_id,
			auth_method: Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_AUTH_BY_SSO,
			registered_by_phone_number: $phone_number,
			registered_by_mail: $mail,
			join_link_uniq: $join_link_rel_row->join_link_uniq,
		));
		Type_Phphooker_Main::sendUserAccountLog($user->user_id, Type_User_Analytics::REGISTERED);

		return [$user->user_id, $integration_response];
	}

	/**
	 * актуализируем
	 *
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected static function _actualizeMailIfNeeded(int $user_id, Struct_User_Auth_Sso_AccountData $account_data):bool {

		// если из sso не вернулась почта, то ничего не делаем
		if ($account_data->mail == "") {
			return false;
		}

		// получаем текущую почту пользователя
		$user_mail = Domain_User_Entity_Mail::getByUserId($user_id);

		// сравниваем текущую почту с почтой из SSO
		// если совпадает, то ничего не делаем
		$sso_mail = Domain_User_Action_Auth_Sso::prepareMail($account_data->mail);
		if ($sso_mail == $user_mail) {
			return false;
		}

		// иначе пытаемся сменить
		try {
			Domain_User_Entity_Mail::change($user_id, $user_mail, $sso_mail);
		} catch (Domain_User_Exception_Mail_BelongAnotherUser) {

			// не удалось сменить – такая почта принадлежит другому пользователю
			// ничего не делаем в таком случае
			return false;
		}

		return true;
	}

}