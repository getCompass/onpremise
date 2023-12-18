<?php

namespace Compass\Company;

/**
 * Класс для удаления ссылки
 */
class Domain_JoinLink_Action_Delete {

	/**
	 * выполняем
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function do(Struct_Db_CompanyData_JoinLink $join_link, int $user_id):void {

		// удаляем ссылку
		Gateway_Db_CompanyData_JoinLinkList::set($join_link->join_link_uniq, [
			"status"     => Domain_JoinLink_Entity_Main::STATUS_DELETED,
			"updated_at" => time(),
		]);

		// помечаем ссылку-инвайт удаленной на pivot
		Gateway_Socket_Pivot::updateJoinLinkStatus($user_id, $join_link->join_link_uniq, Domain_JoinLink_Entity_Main::STATUS_DELETED);

		// получаем участников чата найма и увольнения, чтобы отправить для них событие
		$user_list         = Gateway_Socket_Conversation::getHiringConversationUserIdList();
		$talking_user_list = $user_list["talking_user_list"];

		// отправляем эвент
		Gateway_Bus_Sender::inviteLinkDeleted($join_link->join_link_uniq, $talking_user_list);
	}
}
