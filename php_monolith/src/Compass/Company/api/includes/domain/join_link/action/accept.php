<?php

namespace Compass\Company;

/**
 * Класс описывает действие принятия ссылки-приглашения в пространство
 */
class Domain_JoinLink_Action_Accept {

	/**
	 * Выполняем действие
	 *
	 * @return array
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function do(int $user_id, string $user_full_name, string $user_avatar_file_key, Struct_Db_CompanyData_JoinLink $join_link, string $comment, string $locale):array {

		/** @var Struct_Db_CompanyData_JoinLink $join_link */
		[$join_link, $is_trial_activated] = self::_updateTariff($join_link->creator_user_id, $join_link);

		// если это single-инвайт со статусом active, то помечаем его заюзанным
		$status = Domain_JoinLink_Action_UseInvite::do($join_link->join_link_uniq);

		// добавляем в entry_list
		[$entry_id, $entry_type] = Domain_User_Entity_Entry::addEntryList($user_id);

		// тут создаем заявку на найм
		$hiring_request_id = Domain_HiringRequest_Action_Create::do(
			$join_link->creator_user_id, $user_id, $user_full_name, $user_avatar_file_key,
			$join_link->join_link_uniq, $entry_id, $join_link->entry_option, $comment, $locale
		);

		// добавляем в entry_invite_link_list
		Domain_User_Entity_Entry::addWithInviteLink($entry_id, $join_link->join_link_uniq, $join_link->creator_user_id);

		return [$join_link, $is_trial_activated, $status, $entry_id, $entry_type, $hiring_request_id];
	}

	/**
	 * Обновляем тарифный план пространства
	 *
	 * @return array
	 */
	protected static function _updateTariff(int $user_id, Struct_Db_CompanyData_JoinLink $join_link):array {

		$is_trial_activated = false;

		// если пользователь принял ссылку после которой вступает в пространство сразу как участник
		if ($join_link->entry_option === Domain_JoinLink_Entity_Main::ENTRY_OPTION_JOIN_AS_MEMBER) {

			[$can_increase, $is_trial_activated] = Gateway_Socket_Pivot::increaseMemberCountLimit($user_id);

			// если увеличивать количество участников нельзя - заявка ВСЕГДА отправляется на модерацию
			if (!$can_increase) {
				$join_link->entry_option = Domain_JoinLink_Entity_Main::ENTRY_OPTION_NEED_POSTMODERATION;
			}
		}

		return [$join_link, $is_trial_activated];
	}
}
