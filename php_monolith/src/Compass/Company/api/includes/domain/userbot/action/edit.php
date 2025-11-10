<?php

namespace Compass\Company;

use AnalyticUtils\Domain\Event\Entity\User;
use AnalyticUtils\Domain\Event\Entity\Main;

/**
 * Класс action для редактирования бота
 */
class Domain_Userbot_Action_Edit {

	/**
	 * выполняем действие
	 *
	 * @throws Domain_Userbot_Exception_UserbotNotFound
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function do(Struct_Db_CloudCompany_Userbot $userbot,
					  string|false                   $userbot_name, string|false $short_description,
					  int|false                      $avatar_color_id, string|false $avatar_file_key,
					  int|false                      $is_react_command, string|false $webhook):void {

		// редактируем пользовательские данные бота
		self::_editProfileInfo($userbot->user_id, $short_description, $userbot_name, $avatar_color_id, $avatar_file_key);

		// редактируем данные бота
		$userbot = self::_editUserbotInfo($userbot, $avatar_color_id, $avatar_file_key, $is_react_command, $webhook);

		// редактируем бота на пивоте, если необходимо
		if ($userbot_name !== false || $avatar_color_id !== false || $avatar_file_key !== false || $is_react_command !== false || $webhook !== false) {

			$token = Domain_Userbot_Entity_Userbot::getToken($userbot->extra);
			Gateway_Socket_Pivot::editUserbot(
				$userbot->userbot_id, $token, $userbot_name, $avatar_color_id, $avatar_file_key, $is_react_command, $webhook
			);
		}

		if ($avatar_color_id === false && $avatar_file_key === false) {
			return;
		}

		// получаем всех программистов бота
		$developer_user_id_list = Domain_Member_Action_GetAllDevelopers::do();

		// получаем всех диалоги, в которые добавлен бот
		$userbot_conversation_rel_list = Gateway_Db_CompanyData_UserbotConversationRel::getByUserbotId($userbot->userbot_id);
		$conversation_map_list         = array_column($userbot_conversation_rel_list, "conversation_map");

		// отправляем ивент о редактировании бота
		$formatted_userbot = Apiv2_Format::userbot($userbot);
		Gateway_Event_Dispatcher::dispatch(Type_Event_Userbot_Edited::create(
			$formatted_userbot, $userbot->user_id, $developer_user_id_list, $conversation_map_list
		), true);
	}

	/**
	 * редактируем пользовательские данные бота
	 *
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 */
	protected static function _editProfileInfo(int       $userbot_user_id, string|false $short_description, string|false $userbot_name,
								 int|false $avatar_color_id, string|false $avatar_file_key):void {

		// если описание не менялось, то здесь дальше не идём
		if ($short_description === false) {
			return;
		}

		$set = [
			"short_description" => $short_description,
			"updated_at"        => time(),
		];
		Gateway_Db_CompanyData_MemberList::set($userbot_user_id, $set);
		Gateway_Bus_CompanyCache::clearMemberCacheByUserId($userbot_user_id);

		// если имя и аватарка бота не менялись, то отправляем ws после изменения описания
		if ($userbot_name === false && $avatar_color_id === false && $avatar_file_key === false) {

			// отправляем WS об изменении профиля бота
			$user_info = Gateway_Db_CompanyData_MemberList::getOne($userbot_user_id);
			Gateway_Bus_Sender::memberProfileUpdated($user_info, getClientLaunchUUID());
		}
	}

	/**
	 * редактируем данные бота
	 *
	 * @throws \parseException
	 * @long
	 */
	protected static function _editUserbotInfo(Struct_Db_CloudCompany_Userbot $userbot, int|false $avatar_color_id, string|false $avatar_file_key,
								 int|false                      $is_react_command, string|false $webhook):Struct_Db_CloudCompany_Userbot {

		$extra = $userbot->extra;

		// если передан цвет аватарки
		if ($avatar_color_id !== false) {
			$extra = Domain_Userbot_Entity_Userbot::setAvatarColorId($extra, $avatar_color_id);
		}

		// если передан file_key аватарки
		if ($avatar_file_key !== false) {
			$extra = Domain_Userbot_Entity_Userbot::setAvatarFileKey($extra, $avatar_file_key);
		}

		// если передан флаг реагировать на команды
		if ($is_react_command !== false) {
			$extra = Domain_Userbot_Entity_Userbot::setFlagReactCommand($extra, $is_react_command);
		}

		// если передан вебхук
		if ($webhook !== false) {
			$extra = Domain_Userbot_Entity_Userbot::setWebhook($extra, $webhook);
		}

		// если данные не поменялись, то дальше не идём
		if ($extra == $userbot->extra) {
			return $userbot;
		}

		// устанавливаем новые данные extra
		$userbot->extra = $extra;

		$set = [
			"extra"      => $extra,
			"updated_at" => time(),
		];
		Gateway_Db_CompanyData_UserbotList::set($userbot->userbot_id, $set);

		return $userbot;
	}
}