<?php

declare(strict_types = 1);

namespace Compass\Company;

use AnalyticUtils\Domain\Event\Entity\User;
use AnalyticUtils\Domain\Event\Entity\Main;

/**
 * Класс обработки сценариев событий.
 */
class Domain_Userbot_Scenario_Event {

	/**
	 * получили сообщение в диалоге
	 *
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \returnException
	 * @long
	 */
	#[Type_Attribute_EventListener(Type_Event_Userbot_OnMessageReceived::EVENT_TYPE, trigger_extra: ["group" => Type_Attribute_EventListener::SLOW_GROUP])]
	public static function onMessageReceived(Struct_Event_Userbot_OnMessageReceived $event_data):void {

		$userbot_id_list   = $event_data->userbot_id_list;
		$sender_id         = $event_data->sender_id;
		$conversation_map  = $event_data->conversation_map;
		$message_text_list = $event_data->message_text_list;

		// получаем информацию о пользователе
		$member_info = Gateway_Bus_CompanyCache::getMember($sender_id);

		// если npc не человек, то стопим
		if (!Type_User_Main::isHuman($member_info->npc_type)) {
			return;
		}

		// получаем инфу по ботам
		$userbot_list = Gateway_Db_CompanyData_UserbotList::getList($userbot_id_list);

		$userbot_list_by_token = [];
		$message_list_by_token = [];
		foreach ($userbot_list as $userbot) {

			// проверяем статус бота, если бот не включён, то команду не отправляем
			if ($userbot->status_alias != Domain_Userbot_Entity_Userbot::STATUS_ENABLE) {
				continue;
			}

			// проверяем, должен ли бот реагировать на команды
			if (!Domain_Userbot_Entity_Userbot::isReactCommand($userbot->extra)) {
				continue;
			}

			// получаем токен и команды бота
			$token = Domain_Userbot_Entity_Userbot::getToken($userbot->extra);

			// матчим текст сообщений с командами бота
			foreach ($message_text_list as $message_map => $message_text) {

				// если команда бота совпала с текстом сообщения, то это сообщение отправляется на вебхук бота
				if (self::_matchCommand($message_text)) {

					$message_list_by_token[$token][$message_map] = $message_text;
					$userbot_list_by_token[$token]               = $userbot;
				}
			}
		}

		$conversation_key = isEmptyString($conversation_map) ? "" : Type_Pack_Conversation::doEncrypt($conversation_map);

		// отправляем полученные команды на вебхуки ботов, кто имеет присланные команды
		$is_failed = self::_trySendCommand($userbot_list_by_token, $message_list_by_token, $sender_id, $conversation_key);

		// одна ошибка и ты ошибся
		// если же успешно отправили команду - логируем успех
		$action_status = $is_failed === false ? Main::STATUS_SUCCESS : Main::STATUS_FAIL;
	}

	/**
	 * матч команд и текста сообщения
	 */
	protected static function _matchCommand(string $message_text):bool {

		// если сообщение начинается с /, то сразу отправляем его боту
		return mb_strpos($message_text, "/") === 0;
	}

	/**
	 * отправляем команды ботам
	 */
	protected static function _trySendCommand(array $userbot_list_by_token, array $message_list_by_token, int $sender_id, string $conversation_key):bool {

		$is_failed = false;

		// для тех ботов, у кого совпала команда с текстом сообщения - отправляем для них запрос в php_userbot
		foreach ($message_list_by_token as $token => $message_list) {

			foreach ($message_list as $message_map => $message_text) {

				$userbot     = $userbot_list_by_token[$token];
				$webhook     = Domain_Userbot_Entity_Userbot::getWebhook($userbot->extra);
				$message_key = Type_Pack_Message::doEncrypt($message_map);

				try {

					Gateway_Socket_Userbot::sendCommand($message_text, $token, $webhook, $sender_id, $message_key, $conversation_key);
				} catch (\Exception|\Error $e) {

					$is_failed = true;

					Gateway_Bus_CollectorAgent::init()->inc("row66"); // ошибка: не отправилась команда во внешний модуль php_userbot

					Type_System_Admin::log("send_command_to_userbot", ["error" => $e->getMessage()]);
					Gateway_Notice_Sender::sendGroup(NOTICE_CHANNEL_KEY, "error on send command to php_userbot. Error: " . $e->getMessage());

					// если что-то произошло, то не останавливаем для других ботов
					continue;
				}
			}
		}

		return $is_failed;
	}
}
