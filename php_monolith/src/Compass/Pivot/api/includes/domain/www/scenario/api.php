<?php

namespace Compass\Pivot;

/**
 * Сценарии для API www
 *
 */
class Domain_Www_Scenario_Api {

	protected const _STUB_PAGE_TYPE       = 1; // заглушка-страницы приглашение
	protected const _IMPERSONAL_PAGE_TYPE = 2; // обезличенная-страница приглашение
	protected const _PERSONAL_PAGE_TYPE   = 3; // персональная-страница приглашение

	// список доступных страниц-приглашений
	protected const _ALLOW_PAGE_TYPE = [
		self::_STUB_PAGE_TYPE,
		self::_IMPERSONAL_PAGE_TYPE,
		self::_PERSONAL_PAGE_TYPE,
	];

	/**
	 * Сценарий получения информации о ссылке-приглашении
	 *
	 * @param string $link
	 *
	 * @return array
	 * @throws Domain_Company_Exception_IsHibernated
	 * @throws Domain_Company_Exception_IsRelocating
	 * @throws \blockException
	 * @throws \busException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyNotExist
	 * @throws cs_IncorrectJoinLink
	 * @throws cs_JoinLinkIsNotActive
	 * @throws cs_JoinLinkIsUsed
	 * @throws cs_JoinLinkNotFound
	 * @throws cs_UserAlreadyInCompany
	 * @throws cs_UserNotFound
	 * @throws cs_blockException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \userAccessException
	 */
	public static function getInviteLinkInfo(string $link):array {

		Type_Antispam_Ip::check(Type_Antispam_Ip::INCORRECT_INVITELINK);

		// валидируем ссылку-приглашение и получаем информацию о приглашающем пользователе
		try {

			/** @var Struct_Db_PivotUser_User $inviter_user_info */
			[$invite_link_rel_row, $company, $inviter_user_info] = Domain_Company_Action_JoinLink_ValidateLegacy::do(0, $link);
		} catch (cs_IncorrectJoinLink|cs_JoinLinkNotFound $e) {

			// инкрементим блокировку, если ссылка некорректная или не существует
			Type_Antispam_Ip::checkAndIncrementBlock(Type_Antispam_Ip::INCORRECT_INVITELINK);
			throw $e;
		}

		// получаем аватар
		$avatar_file_url = "";
		if (mb_strlen($inviter_user_info->avatar_file_map) > 0) {

			$file_list = Gateway_Socket_PivotFileBalancer::getFileList([$inviter_user_info->avatar_file_map]);

			// проверяем что список файлов не пустой
			if (count($file_list) > 0) {

				$file            = array_shift($file_list);
				$image_version   = array_shift($file["data"]["image_version_list"]);
				$avatar_file_url = $image_version["url"];
			}
		}

		// получаем цвет аватара, на случай если у пользователя нет аватара
		$avatar_color_id = Type_User_Main::getAvatarColorId($inviter_user_info->extra);

		return [$inviter_user_info->user_id, $inviter_user_info->full_name, $avatar_file_url, $avatar_color_id];
	}

	/**
	 * Сценарий сохранения аналитики по страницам ссылки-приглашения
	 *
	 * @param string $page_type
	 *
	 * @return void
	 * @throws cs_IncorrectJoinLinkPageType
	 */
	public static function saveAnalyticsInviteLinkPage(string $page_type):void {

		// проверяем что page_type соответствует доступным
		if (!in_array($page_type, self::_ALLOW_PAGE_TYPE)) {
			throw new cs_IncorrectJoinLinkPageType();
		}

		$user_agent = getUa();

		// сохраняем аналитику
		Type_Www_Analytics_InvitelinkPage::save($page_type, $user_agent);
	}
}