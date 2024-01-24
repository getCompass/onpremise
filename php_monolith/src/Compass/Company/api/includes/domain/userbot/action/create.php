<?php

namespace Compass\Company;

/**
 * Класс action для создания бота
 */
class Domain_Userbot_Action_Create {

	/**
	 * выполняем действие
	 *
	 * @throws Domain_Userbot_Exception_IncorrectParam
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function do(string $userbot_name, string $short_description, int $avatar_color_id, int $is_react_command, string|false $webhook):array {

		Gateway_Bus_CollectorAgent::init()->inc("row64"); // начало создания бота

		// получаем роль и права которые будут установлены для userbot
		[$role, $permisions] = Domain_User_Action_AddUserbotToMember::getUserbotPresetRolePermissions();

		// создаём пользователя для бота на стороне pivot
		[
			$userbot_id,
			$token,
			$secret_key,
			$bot_user_id,
			$avatar_file_key,
			$npc_type,
		] = Gateway_Socket_Pivot::createUserbot($userbot_name, $avatar_color_id, $is_react_command, $webhook, $role, $permisions);

		// добавляем бота в таблицу с участниками компании
		Domain_User_Action_AddUserbotToMember::do($bot_user_id, $npc_type, $userbot_name, $avatar_file_key, $short_description);

		// добавляем бота в список ботов на стороне cloud
		$userbot = Domain_Userbot_Entity_Userbot::create(
			$userbot_id, $bot_user_id, Domain_Userbot_Entity_Userbot::STATUS_ENABLE, $is_react_command, $webhook, $token, $secret_key, $avatar_color_id
		);

		Gateway_Bus_CollectorAgent::init()->inc("row65"); // успешно завершили создание бота

		// отправляем ws-событие о создании бота
		$developer_user_id_list = Domain_Member_Action_GetAllDevelopers::do();
		Gateway_Bus_Sender::userbotCreated(Apiv2_Format::userbot($userbot), $bot_user_id, $developer_user_id_list);

		// формируем структуру с чувствительными данными
		$sensitive_data = new Struct_Domain_Userbot_SensitiveData(
			$token, $secret_key,
			$is_react_command, $webhook === false ? "" : $webhook,
			[], $avatar_color_id
		);

		return [$userbot, $sensitive_data];
	}
}