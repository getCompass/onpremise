<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;

/**
 * Контроллер для методов изменения профиля компании
 */
class Apiv1_Profile extends \BaseFrame\Controller\Api {

	const ALLOW_METHODS = [
		"setName",
		"setAvatar",
		"setBaseInfo",
	];

	public const MEMBER_ACTIVITY_METHOD_LIST = [
		"setName",
		"setAvatar",
		"setBaseInfo",
	];

	/**
	 * Изменение имени компании
	 */
	public function setName():array {

		$name = $this->post(\Formatter::TYPE_STRING, "name");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::COMPANY_SET_PROFILE);

		try {

			Gateway_Bus_CollectorAgent::init()->inc("row11");
			$name = Domain_Company_Scenario_Api::setName($this->user_id, $this->role, $this->permissions, $name);
		} catch (cs_CompanyIncorrectName) {

			Gateway_Bus_CollectorAgent::init()->inc("row12");
			return $this->error(650, "Incorrect company name");
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {

			Gateway_Bus_CollectorAgent::init()->inc("row13");
			return $this->error(655, "User is not a company owner");
		}

		Gateway_Bus_CollectorAgent::init()->inc("row14");
		return $this->ok([
			"name" => (string) $name,
		]);
	}

	/**
	 * Изменение цвета аватара компании
	 */
	public function setAvatar():array {

		$avatar_color_id = $this->post(\Formatter::TYPE_INT, "avatar_color_id", Domain_Company_Entity_Validator::AVATAR_COLOR_GREEN_ID);

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::COMPANY_SET_PROFILE);

		try {

			Gateway_Bus_CollectorAgent::init()->inc("row15");
			Domain_Company_Scenario_Api::setAvatar($this->user_id, $this->role, $this->permissions, $avatar_color_id);
		} catch (cs_CompanyIncorrectAvatarColorId) {

			Gateway_Bus_CollectorAgent::init()->inc("row16");
			return $this->error(651, "Incorrect company avatar_color_id");
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {

			Gateway_Bus_CollectorAgent::init()->inc("row17");
			return $this->error(655, "User is not a company owner");
		}

		Gateway_Bus_CollectorAgent::init()->inc("row18");
		return $this->ok([
			"avatar_color_id" => (int) $avatar_color_id,
		]);
	}

	/**
	 * Изменяем основные данные профиля компании
	 *
	 * @throws \blockException
	 * @throws paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function setBaseInfo():array {

		$name            = $this->post(\Formatter::TYPE_STRING, "name", false);
		$avatar_color_id = $this->post(\Formatter::TYPE_INT, "avatar_color_id", false);

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::COMPANY_SET_PROFILE);

		try {
			[$current_name, $current_avatar_color_id] = Domain_Company_Scenario_Api::setBaseInfo(
				$this->user_id, $this->role, $this->permissions, $name, $avatar_color_id);
		} catch (cs_CompanyIncorrectName) {
			return $this->error(650, "Incorrect company name");
		} catch (cs_CompanyIncorrectAvatarColorId) {
			return $this->error(651, "Incorrect company avatar_color_id");
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			return $this->error(655, "User is not a company owner");
		}

		return $this->ok([
			"name"            => (string) $current_name,
			"avatar_color_id" => (int) $current_avatar_color_id,
		]);
	}
}
