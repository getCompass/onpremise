<?php

namespace Compass\Company;

/**
 * Класс для редактирования массовой ссылки
 */
class Domain_JoinLink_Action_Edit_Mass {

	/**
	 * выполняем
	 *
	 * @throws cs_InvalidParamForEditInvite
	 * @throws cs_JoinLinkNotExist
	 * @throws \parseException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @long
	 */
	public static function do(Struct_Db_CompanyData_JoinLink $join_link, int|false $lives_day_count, int|false $lives_hour_count, int|false $can_use_count, null|int $entry_option):Struct_Db_CompanyData_JoinLink {

		$set = [];

		if ($lives_day_count !== false) {

			// если ссылка не должна протухать по времени
			if ($lives_day_count === 0) {
				$set["expires_at"] = $lives_day_count;
			} else {
				$set["expires_at"] = time() + Domain_JoinLink_Entity_Main::getLiveTimeByDayCount($lives_day_count);
			}
		}
		if ($lives_hour_count !== false) {
			$set["expires_at"] = time() + Domain_JoinLink_Entity_Main::getLiveTimeByHourCount($lives_hour_count);
		}
		if ($can_use_count !== false) {
			$set["can_use_count"] = $can_use_count;
		}
		if ($entry_option !== null) {
			$set["entry_option"] = $entry_option;
		}
		if (count($set) < 1) {

			if ($join_link->type != Domain_JoinLink_Entity_Main::TYPE_MAIN) {
				throw new cs_InvalidParamForEditInvite();
			}

			$live_time         = Domain_JoinLink_Entity_Main::getLiveTimeByDayCount(Domain_JoinLink_Entity_Main::DEFAULT_MAIN_LIFE_DAY_COUNT_LEGACY);
			$set["expires_at"] = time() + $live_time;
		}
		$set["updated_at"] = time();

		// обновляем
		Gateway_Db_CompanyData_JoinLinkList::set($join_link->join_link_uniq, $set);

		// получаем участников чата найма и увольнения, чтобы отправить для них событие
		$user_list         = Gateway_Socket_Conversation::getHiringConversationUserIdList();
		$talking_user_list = $user_list["talking_user_list"];

		$join_link = Domain_JoinLink_Action_Get::do($join_link->join_link_uniq);

		// отправляем эвент
		Gateway_Bus_Sender::inviteLinkEdited($join_link->join_link_uniq, $talking_user_list);
		return $join_link;
	}
}
