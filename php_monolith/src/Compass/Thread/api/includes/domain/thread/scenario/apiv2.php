<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use CompassApp\Domain\Member\Entity\Member;
use CompassApp\Domain\Member\Entity\Permission;

/**
 * Сценарии треда для API
 */
class Domain_Thread_Scenario_Apiv2 {

	/**
	 * метод для получения thread_meta и thread_menu запрошенных тредов
	 *
	 * @param int   $user_id
	 * @param int   $user_role
	 * @param array $thread_map_list
	 *
	 * @return array
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \parseException
	 */
	public static function getMetaAndMenuBatching(int $user_id, int $user_role, array $thread_map_list):array {

		// пробуем получить данные о метах тредов
		$data = Helper_Threads::getMetaListIfUserMember($thread_map_list, $user_id);

		$meta_list                  = $data["allowed_meta_list"];
		$not_access_thread_map_list = $data["not_allowed_thread_map_list"];

		// отправляем задачу на отписывание от тредов
		Type_Phphooker_Main::doUnfollowThreadList($not_access_thread_map_list, $user_id);

		// получаем только доступные треды
		$allowed_thread_map_list = array_column($meta_list, "thread_map");
		$meta_list               = array_combine($allowed_thread_map_list, $meta_list);

		$dynamic_list = Type_Thread_Dynamic::getList($allowed_thread_map_list);

		// удаляем информацию о плашке прочитанности из тредов синг диалогов, где оппонент с правом скрытия статуса сообщения
		$last_read_messages = Domain_Thread_Action_PrepareLastReadMessages::do($dynamic_list);

		// получаем конкретные записи из меню, игнорируя скрытые
		$menu_list = Type_Thread_Menu::getMenuItems($user_id, $allowed_thread_map_list);

		// формируем ответ
		[$frontend_thread_meta_list, $action_user_id_list] = self::_makeGetMetaBatchingOutput($user_id, $meta_list, $last_read_messages);

		$frontend_thread_menu_list = [];
		foreach ($menu_list as $item) {

			// форматируем сущность thread_menu
			$prepared_thread_menu        = Type_Thread_Utils::prepareThreadMenuForFormat($item);
			$frontend_thread_menu_list[] = Apiv2_Format::threadMenu($prepared_thread_menu);
		}

		return [$frontend_thread_meta_list, $frontend_thread_menu_list, $action_user_id_list];
	}

	/**
	 * метод для формироывния ответа для списка мет тредов
	 *
	 * @param int                             $user_id
	 * @param array                           $thread_meta_list
	 * @param Struct_Thread_LastReadMessage[] $last_read_messages
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	protected static function _makeGetMetaBatchingOutput(int $user_id, array $thread_meta_list, array $last_read_messages):array {

		$prepared_meta_row_list = [];
		$action_user_id_list    = [];

		foreach ($thread_meta_list as $item) {

			// приводим сущность threads под формат frontend
			$prepared_meta_row = Type_Thread_Utils::prepareThreadMetaForFormat($item, $user_id);
			$prepared_meta_row = Apiv2_Format::threadMetaV2($prepared_meta_row, $last_read_messages[$item["thread_map"]]);

			// добавляем пользователей в actions users
			// включая последних прочитавших, так как они могли потерять доступ к треду
			$action_user_id_list = array_merge(
				$action_user_id_list,
				Type_Thread_Meta::getActionUsersList($item),
				$last_read_messages[$item["thread_map"]]->first_read_participant_list);

			$prepared_meta_row_list[] = $prepared_meta_row;
		}

		return [$prepared_meta_row_list, $action_user_id_list];
	}

}