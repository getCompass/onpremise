<?php

namespace Compass\Company;

/**
 * Класс action для выключения бота
 */
class Domain_Userbot_Action_Disable {

	/**
	 * выполняем действие
	 *
	 * @throws Domain_Userbot_Exception_DeletedStatus
	 * @throws Domain_Userbot_Exception_UserbotNotFound
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function do(Struct_Db_CloudCompany_Userbot $userbot):int {

		// проверяем статус бота, возможно тот уже удалён
		if ($userbot->status_alias == Domain_Userbot_Entity_Userbot::STATUS_DELETE) {
			throw new Domain_Userbot_Exception_DeletedStatus("userbot is deleted");
		}

		// проверяем статус бота, возможно тот уже отключён
		if ($userbot->status_alias == Domain_Userbot_Entity_Userbot::STATUS_DISABLE) {
			return Domain_Userbot_Entity_Userbot::getDisabledAt($userbot->extra);
		}

		$disabled_at    = time();
		$userbot->extra = Domain_Userbot_Entity_Userbot::setDisabledAt($userbot->extra, $disabled_at);

		// помечаем бота отключённым в таблице с ботом
		Domain_Userbot_Entity_Userbot::disable($userbot);

		// сокет-запрос для отключения на php_pivot
		$token = Domain_Userbot_Entity_Userbot::getToken($userbot->extra);
		Gateway_Socket_Pivot::disableUserbot($userbot->userbot_id, $token);

		// получаем всех программистов бота
		$developer_user_id_list = Domain_Member_Action_GetAllDevelopers::do();

		// получаем всех диалоги, в которые добавлен бот
		$userbot_conversation_rel_list = Gateway_Db_CompanyData_UserbotConversationRel::getByUserbotId($userbot->userbot_id);
		$conversation_map_list         = array_column($userbot_conversation_rel_list, "conversation_map");

		// отправляем ивент о выключении бота
		Gateway_Event_Dispatcher::dispatch(Type_Event_Userbot_Disabled::create(
			$userbot->userbot_id, $userbot->user_id, $developer_user_id_list, $conversation_map_list, $disabled_at
		), true);

		return $disabled_at;
	}
}