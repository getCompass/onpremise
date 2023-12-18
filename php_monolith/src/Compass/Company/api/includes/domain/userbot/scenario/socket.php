<?php

namespace Compass\Company;

use AnalyticUtils\Domain\Event\Entity\User;
use AnalyticUtils\Domain\Event\Entity\Main;

/**
 * сценарии пользовательских ботов для socket методов
 */
class Domain_Userbot_Scenario_Socket {

	/**
	 * получаем информацию по пользователям для бота
	 *
	 * @throws \returnException
	 */
	public static function getUserInfo(int $count, int $offset):array {

		// достаём пользователей
		$member_list = Gateway_Db_CompanyData_MemberList::getAllActiveMember($count, $offset);

		// фильтруем, нам нужны только реальные люди
		$filtered_member_list = [];
		foreach ($member_list as $member) {

			// убираем удаливших аккаунт
			if (\CompassApp\Domain\Member\Entity\Extra::getIsDeleted($member->extra)) {
				continue;
			}

			if (\CompassApp\Domain\User\Main::isHuman($member->npc_type)) {
				$filtered_member_list[] = $member;
			}
		}

		// получаем file_key аватарок пользователей
		$file_key_list = array_filter(array_column($filtered_member_list, "avatar_file_key"), "strlen");

		// для списка полученных пользователей получаем file_url аватарок
		$file_list     = Gateway_Socket_FileBalancer::getFiles($file_key_list, true);
		$file_url_list = [];
		foreach ($file_list as $file_row) {
			$file_url_list[$file_row["file_key"]] = $file_row["url"];
		}

		$formatted_user_list = [];
		foreach ($filtered_member_list as $member) {

			$formatted_user_list[] = [
				"user_id"         => (int) $member->user_id,
				"full_name"       => (string) $member->full_name,
				"avatar_file_url" => (string) ($file_url_list[$member->avatar_file_key] ?? ""),
			];
		}

		return $formatted_user_list;
	}

	/**
	 * получаем userbot_id бота
	 */
	public static function getUserbotId(int $user_id):string {

		$userbot_list = Gateway_Db_CompanyData_UserbotList::getByUserIdList([$user_id]);

		return $userbot_list[0]->userbot_id;
	}

	/**
	 * получаем статус бота
	 */
	public static function getUserbotStatus(int $user_id):string {

		$userbot_list = Gateway_Db_CompanyData_UserbotList::getByUserIdList([$user_id]);

		return $userbot_list[0]->status_alias;
	}

	/**
	 * кикаем бота из группы
	 */
	public static function kickUserbotFromGroup(int $user_id, string $conversation_map):string {

		$userbot_list = Gateway_Db_CompanyData_UserbotList::getByUserIdList([$user_id]);
		$userbot      = $userbot_list[0];

		Gateway_Db_CompanyData_UserbotConversationRel::delete($userbot->userbot_id, $conversation_map);

		return $userbot_list[0]->userbot_id;
	}

	/**
	 * обновляем список команд бота
	 *
	 * @throws Domain_Userbot_Exception_IncorrectParam
	 * @throws Domain_Userbot_Exception_UserbotNotFound
	 * @throws \parseException
	 */
	public static function updateCommandList(string $userbot_id, array $command_list):void {

		// проверяем список команд на корректность
		Domain_Userbot_Entity_Validator::assertCorrectCommandsLimit($command_list);
		Domain_Userbot_Entity_Validator::assertCorrectCommandLength($command_list);

		// достаём бота
		$userbot = Gateway_Db_CompanyData_UserbotList::getOne($userbot_id);

		// каждую команду проверяем на корректность
		$filtered_command_list = [];
		foreach ($command_list as $command) {

			$command = Domain_Userbot_Entity_Sanitizer::sanitizeCommand($command);

			// если первый символ команды начинается не со слэша
			if (!Domain_Userbot_Entity_Userbot::isFormatCommand($command)) {
				$command = "/" . $command;
			}

			$filtered_command_list[] = $command;
		}

		// команды не должны совпасть
		$filtered_command_list = array_unique($filtered_command_list);

		// устанавливаем новый список команд для бота
		$userbot->extra = Domain_Userbot_Entity_Userbot::setCommandList($userbot->extra, $filtered_command_list);

		// обновляем
		Gateway_Db_CompanyData_UserbotList::set($userbot_id, [
			"extra"      => $userbot->extra,
			"updated_at" => time(),
		]);

		// получаем всех программистов бота
		$developer_user_id_list = Domain_Member_Action_GetAllDevelopers::do();

		// получаем все диалоги, в которые добавлен бот
		$userbot_conversation_rel_list = Gateway_Db_CompanyData_UserbotConversationRel::getByUserbotId($userbot->userbot_id);
		$conversation_map_list         = array_column($userbot_conversation_rel_list, "conversation_map");

		// отправляем ивент об обновлении списка команд бота
		Gateway_Event_Dispatcher::dispatch(Type_Event_Userbot_CommandListUpdated::create(
			Apiv2_Format::userbot($userbot), $userbot->user_id, $developer_user_id_list, $conversation_map_list
		), true);
	}

	/**
	 * получаем список команд бота
	 *
	 * @throws Domain_Userbot_Exception_UserbotNotFound
	 */
	public static function getCommandList(string $userbot_id):array {

		// достаём бота
		$userbot = Gateway_Db_CompanyData_UserbotList::getOne($userbot_id);

		// получаем список команд бота
		return Domain_Userbot_Entity_Userbot::getCommandList($userbot->extra);
	}

	/**
	 * получаем группы бота
	 *
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \cs_DecryptHasFailed
	 * @throws \returnException
	 */
	public static function getGroupList(string $userbot_id):array {

		// получаем список связи бота и его диалогов
		$userbot_conversation_rel_list = Gateway_Db_CompanyData_UserbotConversationRel::getByUserbotId($userbot_id);

		// получаем список ключей групп бота
		$conversation_map_list = array_column($userbot_conversation_rel_list, "conversation_map");

		// получаем данные по группам для бота
		$group_info_list = Gateway_Socket_Conversation::getUserbotGroupInfoList($conversation_map_list);

		// собираем ключи аватарок групп
		$avatar_key_list = [];
		foreach ($group_info_list as $group) {

			if (!isEmptyString($group["avatar_file_key"])) {
				$avatar_key_list[] = $group["avatar_file_key"];
			}
		}

		// получаем url на файлы аватарок групп
		$file_list     = Gateway_Socket_FileBalancer::getFiles($avatar_key_list);
		$file_url_list = [];
		foreach ($file_list as $file_row) {
			$file_url_list[$file_row["file_key"]] = $file_row["url"];
		}

		// отдаём отформатированный ответ
		$output = [];
		foreach ($group_info_list as $group) {

			$output[] = [
				"conversation_key" => (string) $group["conversation_key"],
				"group_name"       => (string) $group["group_name"],
				"avatar_file_url"  => (string) ($file_url_list[$group["avatar_file_key"]] ?? ""),
			];
		}

		return $output;
	}

	/**
	 * выполнить команду
	 *
	 * @noinspection PhpUnusedParameterInspection
	 * @throws Domain_Userbot_Exception_IncorrectParam
	 * @throws Domain_Userbot_Exception_UserbotNotFound
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \parseException
	 * @throws \returnException
	 * @long         - switch..case
	 */
	public static function doCommand(string $token, string $command_text, string $user_id, string $conversation_key, string $message_key):void {

		// !!! ничего не творим на паблике
		if (!isTestServer() && !isStageServer()) {
			return;
		}

		[$userbot_id] = Gateway_Socket_Pivot::getUserbotInfo($token);
		$userbot = Gateway_Db_CompanyData_UserbotList::getOne($userbot_id);

		switch (Domain_Userbot_Action_PreparePatternCommand::do($command_text)) {

			case "hello bot": // тестовая команда для тестов

				$message_text = "Приветствую тебя, человек. Я есть бот, личинка Скайнета";
				Gateway_Socket_Conversation::sendMessageToConversation($userbot->user_id, $conversation_key, $message_text);
				break;
			case "send to thread":

				$message_text = "Hello, human :smirk_cat:";
				Gateway_Socket_Thread::sendMessageToThread($userbot->user_id, $message_key, $message_text);
				break;
			case "add reaction":

				$reaction = ":sunny:";
				Gateway_Socket_Conversation::userbotAddReaction($userbot->user_id, $message_key, $reaction);
				break;
			case "remove reaction":

				$reaction = ":sunny:";
				Gateway_Socket_Conversation::userbotRemoveReaction($userbot->user_id, $message_key, $reaction);
				break;
			case "update command":

				$command_list = explode(",", $command_text);
				Domain_Userbot_Scenario_Socket::updateCommandList($userbot->userbot_id, $command_list);
				break;
			default:
				// !!! exception не нужон
				// если команда нам неизвестна, то ничего не делаем
				break;
		}
	}
}