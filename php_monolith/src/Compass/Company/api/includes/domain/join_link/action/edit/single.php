<?php

namespace Compass\Company;

/**
 * Класс для редактирования разовой ссылки
 */
class Domain_JoinLink_Action_Edit_Single {

	/**
	 * выполняем
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \parseException
	 */
	public static function do(Struct_Db_CompanyData_JoinLink $join_link, int|false $lives_day_count, int|false $lives_hour_count, int|null $entry_option):Struct_Db_CompanyData_JoinLink {

		$set = [];

		if ($lives_day_count !== false) {

			// если ссылка не должна протухать по времени
			if ($lives_day_count === 0) {
				$join_link->expires_at = $lives_day_count;
			} else {
				$join_link->expires_at = time() + Domain_JoinLink_Entity_Main::getLiveTimeByDayCount($lives_day_count);
			}
		}

		if ($lives_hour_count !== false) {
			$join_link->expires_at = time() + Domain_JoinLink_Entity_Main::getLiveTimeByHourCount($lives_hour_count);
		}

		if (!is_null($entry_option)) {
			$join_link->entry_option = $entry_option;
		}

		$join_link->updated_at = time();

		$set["entry_option"] = $join_link->entry_option;
		$set["expires_at"]   = $join_link->expires_at;
		$set["updated_at"]   = $join_link->updated_at;

		// обновляем
		Gateway_Db_CompanyData_JoinLinkList::set($join_link->join_link_uniq, $set);

		// получаем участников чата найма и увольнения, чтобы отправить для них событие
		$user_list         = Gateway_Socket_Conversation::getHiringConversationUserIdList();
		$talking_user_list = $user_list["talking_user_list"];

		// отправляем эвент
		Gateway_Bus_Sender::inviteLinkEdited($join_link->join_link_uniq, $talking_user_list);
		return $join_link;
	}
}
