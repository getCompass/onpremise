<?php

namespace Compass\Company;

/**
 * Класс для создания основных ссылок-инвайтов
 */
class Domain_JoinLink_Action_Create_Main extends Domain_JoinLink_Action_Create_Default {

	protected const _ENTRY_OPTION        = Domain_JoinLink_Entity_Main::ENTRY_OPTION_JOIN_AS_MEMBER; // вступаем сразу как участник
	protected const _CAN_USE_COUNT       = 50;
	protected const _LIVES_DAY_COUNT     = 7;
	protected const _MAX_MAIN_LINK_COUNT = 10000; // задаем максимум по одновременно существующим активным собственным ссылкам
	protected const _JOIN_LINK_TYPE      = Domain_JoinLink_Entity_Main::TYPE_MAIN;

	/**
	 * выполняем
	 *
	 * @throws \queryException
	 * @throws \returnException
	 * @throws cs_ExceededCountActiveInvite
	 * @throws \parseException
	 */
	public static function do(int $creator_user_id, int $lived_day_count = self::_LIVES_DAY_COUNT, int $can_user_count = self::_CAN_USE_COUNT):Struct_Db_CompanyData_JoinLink {

		if (self::isActiveMainLinkAlreadyCreatedByUser($creator_user_id)) {
			throw new cs_ExceededCountActiveInvite();
		}

		return self::_do(
			$creator_user_id,
			self::_JOIN_LINK_TYPE,
			Domain_JoinLink_Entity_Main::getLiveTimeByDayCount($lived_day_count),
			$can_user_count,
			self::_ENTRY_OPTION
		);
	}

	/**
	 * Проверяем нет ли уже активной main ссылки от этого пользователя
	 */
	public static function isActiveMainLinkAlreadyCreatedByUser(int $creator_user_id):bool {

		// получаем все активные main ссылки
		$invite_link_list = Gateway_Db_CompanyData_JoinLinkList::getByTypeAndStatus(
			[self::_JOIN_LINK_TYPE], Domain_JoinLink_Entity_Main::STATUS_ACTIVE, time(), self::_MAX_MAIN_LINK_COUNT);

		// ищем собственную ссылку
		foreach ($invite_link_list as $link) {

			if ($link->creator_user_id == $creator_user_id) {
				return true;
			}
		}

		return false;
	}
}
