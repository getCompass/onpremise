<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\InvalidPhoneNumber;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Exception\Domain\InvalidMail;

/**
 * Сценарии для работы с безопасностью через почту на веб-сайте on-premise решений.
 */
class Domain_User_Scenario_OnPremiseWeb_Security_Mail {

	/**
	 * запускаем процесс сброса пароля
	 *
	 * @return array
	 * @throws CaseException
	 * @throws Domain_Link_Exception_TemporaryUnavailable
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\InvalidMail
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \busException
	 * @throws \userAccessException
	 * @throws cs_IncorrectJoinLink
	 * @throws cs_JoinLinkIsNotActive
	 * @throws cs_JoinLinkIsUsed
	 * @throws cs_JoinLinkNotFound
	 * @throws cs_PhoneNumberIsNotEqual
	 * @throws cs_PlatformNotFound
	 * @throws cs_RecaptchaIsRequired
	 * @throws cs_UserAlreadyInCompany
	 * @throws cs_UserAlreadyLoggedIn
	 * @throws cs_UserNotFound
	 * @throws cs_WrongRecaptcha
	 * @long
	 */
	public static function tryResetPassword(int $user_id, string $mail, string|false $grecaptcha_response, string|false $join_link):array {

		// проверяем, что нет текущей активной сессии
		Domain_User_Entity_Validator::assertNotLoggedIn($user_id);

		// форматируем почту
		$mail = (new \BaseFrame\System\Mail($mail))->mail();

		// получаем user_id по почте
		$existing_user_id = Domain_User_Action_Auth_Mail::resolveUser($mail);

		// если не нашли пользователя, то нужно обязательно проверить актуальность ссылки-приглашения
		if ($existing_user_id === 0) {

			// если пользователь не зарегистрирован, то сразу завершаем процесс
			throw new InvalidMail("");
		}

		// валидируем ссылку-приглашение, если она передана
		$validation_result = Domain_Link_Action_OnPremiseWeb::validateJoinLinkIfNeeded($join_link, $existing_user_id);

		try {

			// получаем значение из кеша, если есть, иначе дальше начнем процесс
			$auth_story = Domain_User_Entity_AuthStory::getFromSessionCache($mail)
				->assertNotExpired()
				->assertAuthParameter($mail)
				->assertType([Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_RESET_PASSWORD_BY_MAIL]);
		} catch (cs_CacheIsEmpty|cs_AuthIsExpired|Domain_User_Exception_AuthStory_AuthParameterNotEqual|Domain_User_Exception_AuthStory_TypeMismatch) {

			Domain_User_Entity_Antispam_Auth::checkIpAddressBlocksBeforeStartAuth($grecaptcha_response, true);
			$auth_story = Domain_User_Action_Auth_Mail::beginResetPassword($existing_user_id, $mail);
		}

		if (isset($validation_result)) {

			// сохраняем ссылку-приглашение если есть в кэш
			// это нам пригодится в дальнейшем, чтобы проверить переданные от клиента данные
			ShardingGateway::cache()->set(
				$auth_story->getAuthInfo()->auth_map,
				$validation_result->invite_link_rel->join_link_uniq
			);
		}

		return [
			$auth_story->getAuthInfo(),
			$validation_result ?? false,
		];
	}

	/**
	 * Проверяет код подтверждения
	 *
	 * @throws Domain_User_Exception_AuthStory_ErrorCountLimitExceeded
	 * @throws Domain_User_Exception_AuthStory_TypeMismatch
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \parseException
	 * @throws cs_AuthAlreadyFinished
	 * @throws cs_AuthIsBlocked
	 * @throws cs_AuthIsExpired
	 * @throws cs_InvalidConfirmCode
	 * @throws cs_UserAlreadyLoggedIn
	 * @throws cs_WrongAuthKey
	 * @throws cs_WrongCode
	 */
	public static function confirmResetPassword(int $user_id, string $auth_map, string $code):Struct_User_Auth_Info {

		// проверяем, что нет текущей активной сессии
		Domain_User_Entity_Validator::assertNotLoggedIn($user_id);
		Domain_User_Entity_Validator::assertValidConfirmCode($code);

		// получаем запись аутентификации
		$story = Domain_User_Entity_AuthStory::getByMap($auth_map);

		try {

			// делаем общие для всех типов аутентификаций проверки
			$story->assertNotExpired()
				->assertNotFinishedYet()
				->assertType([Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_RESET_PASSWORD_BY_MAIL]);

			// делаем проверки свойственные аутентификации по почте
			self::_throwIfCodeErrorCountLimitExceeded($story);
			$story->getAuthMailHandler()
				->assertCodeErrorCountLimitNotExceeded()
				->assertEqualCode($code);
		} catch (cs_WrongCode $e) {

			// записываем в историю ошибку подтверждения и обновляем кэш
			$story->getAuthMailHandler()->handleWrongCode();
			$story->storeInSessionCache();

			// если получен неверный код, и достигнут лимит, то кидаем ошибку лимита
			self::_throwIfCodeErrorCountLimitExceeded($story);

			throw $e;
		}

		// обновляем попытку аутентификации, указываем что код был подтвержден
		$story = Domain_User_Action_Auth_Mail::confirmResetPassword($story);

		return $story->getAuthInfo();
	}

	/**
	 * Завершаем сброс пароля
	 *
	 * @return array
	 * @throws Domain_User_Exception_AuthStory_StageNotAllowed
	 * @throws Domain_User_Exception_AuthStory_TypeMismatch
	 * @throws Domain_User_Exception_Mail_NotFound
	 * @throws Domain_User_Exception_Password_Incorrect
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws \userAccessException
	 * @throws cs_AuthAlreadyFinished
	 * @throws cs_AuthIsExpired
	 * @throws cs_DamagedActionException
	 * @throws cs_IncorrectSaltVersion
	 * @throws cs_UserAlreadyLoggedIn
	 * @throws cs_WrongAuthKey
	 */
	public static function finishResetPassword(int $user_id, string $auth_map, string $password, string|false $join_link_uniq):array {

		// проверяем, что нет текущей активной сессии
		Domain_User_Entity_Validator::assertNotLoggedIn($user_id);
		Domain_User_Entity_Password::throwIfIncorrect($password);

		// получаем запись аутентификации
		$story = Domain_User_Entity_AuthStory::getByMap($auth_map);

		// делаем общие для всех типов аутентификаций проверки
		$story->assertNotExpired()
			->assertNotFinishedYet()
			->assertType([Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_RESET_PASSWORD_BY_MAIL]);

		// делаем проверки свойственные аутентификации по почте
		$story->getAuthMailHandler()
			->assertHasCode();

		// сбрасываем пароль пользователю
		Domain_User_Action_Auth_Mail::resetPassword($story->getUserId(), $story->getAuthMailHandler()->getMail(), $password);

		// чистим кэш попыток аутентификации
		$story->clearAuthCache();

		// выдаем пользовательскую сессию
		/** @noinspection PhpUnusedLocalVariableInspection */
		[$user_id, $invite_accept_info] = self::_confirmUserAuthentification($story, $join_link_uniq);
		Type_Session_Main::doLoginSession($user_id);

		// устанавливаем, что аутентификация прошла успешно
		$story->handleSuccess($user_id, [
			"has_password" => 1,
		]);
		Gateway_Db_PivotHistoryLogs_UserAuthHistory::insert($story->getAuthMap(), $user_id, Domain_User_Entity_AuthStory::HISTORY_AUTH_STATUS_SUCCESS, time(), 0);
		[$token, ] = Domain_Solution_Action_GenerateAuthenticationToken::exec($user_id, join_link_uniq: $join_link_uniq);
		return [
			$token,
			Type_User_Main::isEmptyProfile($user_id),
		];
	}

	/**
	 * Выполняет кусок логики подтверждения аутентификации для уже зарегистрированного пользователя
	 *
	 * @return array
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \queryException
	 */
	protected static function _confirmUserAuthentification(Domain_User_Entity_AuthStory $story, string|bool $join_link_uniq):array {

		$user_id = $story->getUserId();

		if ($join_link_uniq !== false) {

			// получаем данные ссылки-приглашения из базы
			$cached_join_link_uniq = ShardingGateway::cache()->get($story->getAuthMap());

			if ($cached_join_link_uniq !== false) {

				try {

					// получаем приглашение, оно должно существовать,
					// поскольку данные были получены и сверены из кэша
					$join_link_rel_row = Gateway_Db_PivotData_CompanyJoinLinkRel::get($join_link_uniq);
				} catch (\cs_RowIsEmpty) {
					throw new ReturnFatalException("invite not found");
				}

				try {

					$validation_result  = Domain_Link_Entity_Link::validateForUser($user_id, $join_link_rel_row);
					$invite_accept_info = [
						$join_link_rel_row,
						Gateway_Bus_PivotCache::getUserInfo($user_id),
						$validation_result,
					];
				} catch (\Exception) {
					// ничего не делаем, стоит тут как-то ошибку выкинуть, но пока ничего не делаем
				}
			}
		}

		// добавляем в историю, что пользователь залогинился
		Domain_User_Entity_UserActionComment::addUserLoginAction($user_id, $story->getType(), $story->getAuthMailHandler()->getMail(), getDeviceId(), getUa());

		return [$user_id, $invite_accept_info ?? false];
	}

	/**
	 * Переотправляем проверочный код
	 *
	 * @return Struct_User_Auth_Info
	 * @throws Domain_User_Exception_AuthStory_TypeMismatch
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \parseException
	 * @throws cs_AuthAlreadyFinished
	 * @throws cs_AuthIsBlocked
	 * @throws cs_AuthIsExpired
	 * @throws cs_IncorrectSaltVersion
	 * @throws cs_ResendCodeCountLimitExceeded
	 * @throws cs_ResendWillBeAvailableLater
	 * @throws cs_UserAlreadyLoggedIn
	 * @throws cs_WrongAuthKey
	 */
	public static function resendResetPasswordCode(int $user_id, string $auth_map):Struct_User_Auth_Info {

		// проверяем, что нет текущей активной сессии
		Domain_User_Entity_Validator::assertNotLoggedIn($user_id);

		// получаем запись аутентификации
		$story = Domain_User_Entity_AuthStory::getByMap($auth_map);

		// делаем общие для всех типов аутентификаций проверки
		$story->assertNotExpired()
			->assertNotFinishedYet()
			->assertType([Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_RESET_PASSWORD_BY_MAIL]);

		// делаем проверки свойственные аутентификации по почте
		self::_throwIfCodeErrorCountLimitExceeded($story);
		self::_throwIfResendCountLimitExceeded($story);
		$story->getAuthMailHandler()
			->assertResendIsAvailable();

		// переотпрваляем код
		$story = Domain_User_Action_Auth_Mail::resendCode($story);

		return $story->getAuthInfo();
	}

	/**
	 * проверяем, достигнут ли лимит ввода неверного кода
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws cs_AuthIsBlocked
	 */
	protected static function _throwIfCodeErrorCountLimitExceeded(Domain_User_Entity_AuthStory $story):void {

		try {
			$story->getAuthMailHandler()->assertCodeErrorCountLimitNotExceeded();
		} catch (Domain_User_Exception_AuthStory_ErrorCountLimitExceeded) {
			throw new cs_AuthIsBlocked($story->getExpiresAt());
		}
	}

	/**
	 * проверяем, достигнут ли лимит переотправки кода
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws Domain_User_Exception_AuthStory_ResendCountLimitExceeded
	 */
	protected static function _throwIfResendCountLimitExceeded(Domain_User_Entity_AuthStory $story):void {

		try {
			$story->getAuthMailHandler()->assertResendCountLimitNotExceeded();
		} catch (cs_ResendCodeCountLimitExceeded) {
			throw new Domain_User_Exception_AuthStory_ResendCountLimitExceeded($story->getExpiresAt());
		}
	}
}