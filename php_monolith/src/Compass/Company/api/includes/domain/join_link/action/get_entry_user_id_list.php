<?php

namespace Compass\Company;

/**
 * Класс для получения списка вступивших по ссылке пользователей
 */
class Domain_JoinLink_Action_GetEntryUserIdList {

	/**
	 * выполняем
	 */
	public static function do(string $join_link_uniq):array {

		$entry_invite_link_list = Gateway_Db_CompanyData_EntryJoinLinkList::getAllByJoinLinkUniq($join_link_uniq);
		$entry_id_list          = array_column($entry_invite_link_list, "entry_id");

		$entry_list = Gateway_Db_CompanyData_EntryList::getList($entry_id_list);

		// сортируем вступивших пользователей по дате вступления
		usort($entry_list, function(Struct_Db_CompanyData_Entry $entry_a, Struct_Db_CompanyData_Entry $entry_b) {

			return $entry_a->created_at < $entry_b->created_at ? 1 : -1;
		});

		// собираем айдишники
		$entry_user_id_list = array_column($entry_list, "user_id");

		// получаем только тех, кто уже вступил в компанию
		$short_member_list = Gateway_Bus_CompanyCache::getShortMemberList(array_unique($entry_user_id_list));

		return array_keys($short_member_list);
	}
}
