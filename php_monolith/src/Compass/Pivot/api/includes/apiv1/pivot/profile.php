<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Restrictions\Exception\ActionRestrictedException;

/**
 * контроллер для методов профиля компаса
 */
class Apiv1_Pivot_Profile extends \BaseFrame\Controller\Api {

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
	public function set():array {

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
	 * @throws \BaseFrame\Exception\Request\BlockException
	 */
	public function doClearAvatar():array {

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
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \busException
	 * @throws cs_AnswerCommand
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UnpackHasFailed
	 * @throws cs_UserNotFound
	 * @throws cs_blockException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws \userAccessException
	 */
	public function delete():array {

		$two_fa_key = $this->post(\Formatter::TYPE_STRING, "two_fa_key", false);

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::PROFILE_DELETE);

		try {
			Domain_User_Scenario_Api::deleteProfile($this->user_id, $two_fa_key);
		} catch (cs_TwoFaIsInvalid|cs_WrongTwoFaKey|cs_UnknownKeyType|cs_TwoFaTypeIsInvalid|cs_TwoFaInvalidUser|cs_TwoFaInvalidCompany) {
			return $this->error(2302, "2fa key is not valid");
		} catch (cs_TwoFaIsNotActive|cs_TwoFaIsFinished|cs_TwoFaIsExpired) {
			return $this->error(2303, "2fa key is not active");
		} catch (cs_UserAlreadyBlocked) {
			throw new ParamException("user is already blocked");
		} catch (cs_UserPhoneSecurityNotFound) {
			throw new ParamException("not found phone for user");
		} catch (Domain_User_Exception_IsOnpremiseRoot) {
			throw new ParamException("action not available for this user");
		}

		return $this->ok();
	}
}