<?php

namespace Compass\Userbot;

/**
 * Сценарии бота для сокетов
 *
 * Class Domain_Userbot_Scenario_Socket
 */
class Domain_Userbot_Scenario_Socket {

	/**
	 * отправляем команду
	 * @long
	 */
	public static function sendCommand(string $token, string $command, string $webhook, int $user_id, string $message_id, string $group_id):void {

		// достаём бота
		$userbot = Gateway_Bus_UserbotCache::get($token);

		// проверяем, что бот включён и реагирует на команды
		if ($userbot->status != Domain_Userbot_Entity_Userbot::STATUS_ENABLE || $userbot->is_react_command == 0) {
			return;
		}

		// определяем из extra версию webhook бота
		$webhook_version = Domain_Userbot_Entity_Userbot::getWebhookVersion($userbot->extra);

		// определяем какой user_id передаём (в формате "User-{ID}" или int-значение)
		$user_id = $webhook_version < Domain_Userbot_Entity_Userbot::USERBOT_WEBHOOK_VERSION_2 ? Domain_Userbot_Action_ConvertServiceUserId::to($user_id) : $user_id;

		$format_class = match ($webhook_version) {
			Domain_Userbot_Entity_Userbot::USERBOT_WEBHOOK_VERSION_1 => Apiv1_Format::class,
			Domain_Userbot_Entity_Userbot::USERBOT_WEBHOOK_VERSION_2 => Apiv2_Format::class,
			default => Apiv3_Format::class,
		};

		// формируем параметры для запроса
		$params = [
			"token"           => $token,
			"secret_key"      => $userbot->secret_key,
			"command"         => $command,
			"webhook"         => $webhook,
			"user_id"         => $user_id,
			"message_id"      => $message_id,
			"group_id"        => $group_id,
			"type"            => isEmptyString($group_id) ? $format_class::SINGLE_CONVERSATION_TYPE : $format_class::GROUP_CONVERSATION_TYPE,
			"webhook_version" => $webhook_version,
		];

		$command_obj = new Struct_Db_UserbotMain_Command(0, 0, 0, time(), $params);
		Gateway_Db_UserbotMain_CommandQueue::insert($command_obj);
	}
}