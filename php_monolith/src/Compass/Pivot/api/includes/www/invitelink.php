<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\BlockException;

/**
 * группа методов для общения с внешними сервисами
 */
class Www_Invitelink extends \BaseFrame\Controller\Www {

	// поддерживаемые методы. регистр не имеет значение
	public const ALLOW_METHODS = [
		"getInfo",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * метод для получения информации о ссылке-приглашении
	 *
	 * @return array
	 * @throws \busException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyNotExist
	 * @throws cs_UserAlreadyInCompany
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \userAccessException|\BaseFrame\Exception\Request\ParamException
	 */
	public function getInfo():array {

		$link = $this->post(\Formatter::TYPE_STRING, "link");

		try {

			[
				$inviter_user_id, $inviter_full_name, $inviter_avatar_url,
				$avatar_color_id, $inviter_avatar_image_version_list,
			] = Domain_Www_Scenario_Api::getInviteLinkInfo($link);
		} catch (cs_JoinLinkIsUsed | cs_JoinLinkIsNotActive | cs_UserNotFound | BlockException
		| cs_IncorrectJoinLink | cs_JoinLinkNotFound | Domain_Company_Exception_IsHibernated
		| Domain_Company_Exception_IsRelocating | Domain_Company_Exception_IsNotServed) {

			return $this->error(1609001, "Invite link information not available");
		}

		return $this->ok(Www_Format::inviteLinkInfo($inviter_user_id, $inviter_full_name, $inviter_avatar_url, $avatar_color_id, $inviter_avatar_image_version_list));
	}
}