<?php

namespace Compass\Company;

/**
 * Класс action для удаления бота
 */
class Domain_Userbot_Action_Delete {

	/**
	 * выполняем действие
	 *
	 * @throws Domain_Userbot_Exception_UserbotNotFound
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function do(Struct_Db_CloudCompany_Userbot $userbot):int {

		// если уже удалён
		if ($userbot->status_alias == Domain_Userbot_Entity_Userbot::STATUS_DELETE) {
			return Domain_Userbot_Entity_Userbot::getDeletedAt($userbot->extra);
		}

		// помечаем бота удалённым в таблице с ботами
		$deleted_at     = time();
		$userbot->extra = Domain_Userbot_Entity_Userbot::setDeletedAt($userbot->extra, $deleted_at);
		$userbot->extra = Domain_Userbot_Entity_Userbot::setCommandList($userbot->extra, []);
		Domain_Userbot_Entity_Userbot::delete($userbot);

		// сокет-запрос на pivot для удаления бота там
		$token = Domain_Userbot_Entity_Userbot::getToken($userbot->extra);
		Gateway_Socket_Pivot::deleteUserbot($userbot->userbot_id, $token);

		// получаем всех программистов бота
		$developer_user_id_list = Domain_Member_Action_GetAllDevelopers::do();

		// получаем все диалоги, в которые добавлен бот
		$userbot_conversation_rel_list = Gateway_Db_CompanyData_UserbotConversationRel::getByUserbotId($userbot->userbot_id);
		$conversation_map_list         = array_column($userbot_conversation_rel_list, "conversation_map");

		// отправляем ивент об удалении бота
		Gateway_Event_Dispatcher::dispatch(Type_Event_Userbot_Deleted::create(
			$userbot->userbot_id, $userbot->user_id, $developer_user_id_list, $conversation_map_list, $deleted_at
		), true);

		return $deleted_at;
	}
}