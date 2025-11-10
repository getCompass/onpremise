<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Request\EndpointAccessDeniedException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Restrictions\Exception\ActionRestrictedException;

/**
 * контроллер для методов профиля компаса
 */
class Apiv1_Pivot_Profile extends \BaseFrame\Controller\Api
{
	// поддерживаемые методы. регистр не имеет значение
	public const ALLOW_METHODS = [
		"set",
		"doClearAvatar",
		"delete",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * Метод обновления профиля
	 */
	public function set(): array
	{

		$name            = $this->post(\Formatter::TYPE_STRING, "name", false);
		$avatar_file_key = $this->post(\Formatter::TYPE_STRING, "avatar_file_key", false);

		$avatar_file_map = false;
		if ($avatar_file_key !== false) {
			$avatar_file_map = Type_Pack_Main::replaceKeyWithMap("file_key", $avatar_file_key);
		}

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::PROFILE_SET);

		if ($name !== false && !Type_Restrictions_Config::isNameChangeEnabled()) {
			return $this->error(855, "action is restricted");
		}

		if ($avatar_file_map !== false && !Type_Restrictions_Config::isAvatarChangeEnabled()) {
			return $this->error(855, "action is restricted");
		}

		try {
			$user_info = Domain_User_Scenario_Api::setProfile($this->user_id, $name, $avatar_file_map);
		} catch (\cs_InvalidProfileName) {
			return $this->error(205, "invalid name");
		} catch (cs_InvalidAvatarFileMap) {
			throw new ParamException("invalid file map");
		} catch (cs_FileIsNotImage) {
			return $this->error(705, "File is not image");
		} catch (\cs_RowIsEmpty) {
			throw new ParamException("user not found");
		} catch (Domain_User_Exception_AvatarIsDeleted) {
			throw new ParamException("avatar is deleted");
		} catch (ActionRestrictedException) {
			return $this->error(855, "action is restricted");
		}

		$this->action->profile();
		return $this->ok([
			"user" => (array) Apiv1_Pivot_Format::user($user_info),
		]);
	}

	/**
	 * Метод очистки аватара
	 *
	 * @throws \parseException
	 * @throws BlockException
	 */
	public function doClearAvatar(): array
	{

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::PROFILE_SET);

		try {
			Domain_User_Scenario_Api::doClearAvatar($this->user_id);
		} catch (ActionRestrictedException) {
			return $this->error(855, "action is restricted");
		}

		return $this->ok();
	}

	/**
	 * удаляем аккаунт пользователя
	 *
	 * @throws BlockException
	 * @throws BusFatalException
	 * @throws EndpointAccessDeniedException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws cs_AnswerCommand
	 * @throws cs_blockException|\busException
	 */
	public function delete(): array
	{

		$two_fa_key                      = $this->post(\Formatter::TYPE_STRING, "two_fa_key", false);
		$confirm_mail_password_story_key = $this->post(\Formatter::TYPE_STRING, "confirm_mail_password_story_key", false);

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::PROFILE_DELETE);

		try {
			Domain_User_Scenario_Api::deleteProfile($this->user_id, $this->session_uniq, $two_fa_key, $confirm_mail_password_story_key);
		} catch (cs_TwoFaIsInvalid | cs_WrongTwoFaKey | cs_UnknownKeyType | cs_TwoFaTypeIsInvalid | cs_TwoFaInvalidUser | cs_TwoFaInvalidCompany) {
			return $this->error(2302, "2fa key is not valid");
		} catch (cs_TwoFaIsNotActive | cs_TwoFaIsFinished | cs_TwoFaIsExpired) {
			return $this->error(2303, "2fa key is not active");
		} catch (Domain_User_Exception_Confirmation_Mail_IsExpired) {
			return $this->error(1118002, "mail confirmation key is expired");
		} catch (Domain_User_Exception_Confirmation_Mail_IsNotConfirmed | Domain_User_Exception_Confirmation_Mail_IsActive |
		Domain_User_Exception_Confirmation_Mail_NotSuccess) {
			return $this->error(1118001, "mail confirmation key is active");
		} catch (Domain_User_Exception_Mail_NotFound) {
			return $this->error(1118003, "mail not found");
		} catch (cs_UserPhoneSecurityNotFound | cs_PhoneNumberNotFound) {
			return $this->error(1118004, "phone not found");
		} catch (Domain_User_Exception_Mail_NotFoundOnSso) {
			return $this->error(1118005, "mail not found");
		} catch (Domain_User_Exception_ProfileDeletionDisabled) {
			return $this->error(1118005, "profile deletion disabled");
		} catch (cs_UserAlreadyBlocked) {
			throw new ParamException("user is already blocked");
		} catch (Domain_User_Exception_IsOnpremiseRoot) {
			throw new ParamException("action not available for this user");
		} catch (Domain_User_Exception_Confirmation_Mail_InvalidMailPasswordStoryKey | Domain_User_Exception_Confirmation_Mail_InvalidUser |
		Domain_User_Exception_Confirmation_Mail_IsInvalidType | \cs_UnpackHasFailed | \cs_DecryptHasFailed | \cs_RowIsEmpty) {
			throw new ParamException("mail confirmation key is invalid");
		} catch (cs_UserNotFound) {
			throw new ParamException("user not found");
		}

		return $this->ok();
	}
}
