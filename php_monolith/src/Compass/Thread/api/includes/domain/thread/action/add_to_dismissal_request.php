<?php

declare(strict_types = 1);

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;

/**
 * Action для создания треда для заявки увольнения
 */
class Domain_Thread_Action_AddToDismissalRequest {

	/**
	 * добавляем тред к заявке увольнения
	 *
	 * @return array
	 * @throws \returnException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @long
	 */
	public static function do(int $user_id, int $dismissal_request_id, bool $is_need_thread_attach = false):array {

		// проверяем, может тред у заявки уже имеется
		$response = Gateway_Socket_Company::getMetaForCreateThread($user_id, $dismissal_request_id, "dismissal_request");
		if ($response["is_exist"] == 1) {
			return Type_Thread_Meta::getOne($response["thread_map"]);
		}
		$dismissal_request_created_at = $response["created_at"];      // время создания заявки

		// получаем данные чата найма/увольнения
		$conversation_data = Gateway_Socket_Conversation::getDataForCreateThreadOnHireRequest();

		// получаем информацию о пользователях
		$user_info_list = Gateway_Bus_CompanyCache::getShortMemberList([$user_id]);

		// добавляем к users помимо участников чата также создателя заявки и треда
		$users = array_map(function(\CompassApp\Domain\Member\Struct\Short $user_info) {

			return ["user_id" => $user_info->user_id, "role" => $user_info->role];
		}, $user_info_list);
		$users = array_merge($users, $conversation_data["users"]);

		// создаем тред, передавая пустой $user_mute_info, потому что треды на данный момент больше не мьютятся,
		// поэтому мы не обращаем внимания на то, что сам диалог/группа могли быть замьючены
		$thread_meta_row = self::_createThread(
			(string) $dismissal_request_id,
			$conversation_data["conversation_map"],
			$users,
			$user_id,
			$user_id,
			$dismissal_request_created_at,
			false
		);

		// сокет-запрос для закрепления thread_map за заявкой
		if ($is_need_thread_attach) {
			Gateway_Socket_Company::setThreadMapForHireRequest($dismissal_request_id, "dismissal_request", $thread_meta_row["thread_map"]);
		}

		// отправляем события о создании треда к заявке найма
		self::_sendEventOnThreadAdd($thread_meta_row, $dismissal_request_id, $thread_meta_row["users"]);

		// инкрементим количество действий
		Domain_User_Action_IncActionCount::incThreadCreated($user_id, $conversation_data["conversation_map"]);

		return $thread_meta_row;
	}

	/**
	 * создаем тред для заявки увольнения
	 *
	 * @param string $dismissal_request_id
	 * @param string $conversation_map
	 * @param array  $users
	 * @param int    $user_id
	 * @param int    $creator_user_id
	 * @param int    $request_created_at
	 * @param bool   $is_need_follow_creator
	 *
	 * @return array
	 * @long
	 */
	protected static function _createThread(string $dismissal_request_id, string $conversation_map,
							    array  $users, int $user_id, int $creator_user_id,
							    int    $request_created_at, bool $is_need_follow_creator):array {

		// формируем участников тредов
		$users = self::_makeThreadMetaUsers($users);

		// создаем структуру для родительской сущности треда (тред привязывается к заявке)
		$parent_rel = Type_Thread_ParentRel::create(
			PARENT_ENTITY_TYPE_DISMISSAL_REQUEST,
			$creator_user_id,
			$dismissal_request_id,
			$request_created_at,
			[]
		);

		// создаем структуру для source_parent_rel
		$source_parent_rel = Type_Thread_SourceParentRel::create($conversation_map, SOURCE_PARENT_ENTITY_TYPE_CONVERSATION);

		// создаем новый тред
		return Type_Thread_Private::create(
			$users,
			$source_parent_rel,
			$parent_rel,
			$user_id,
			$creator_user_id,
			$is_need_follow_creator,
			[]
		);
	}

	/**
	 * формируем участников треда на основе участников meta сущности
	 *
	 */
	protected static function _makeThreadMetaUsers(array $users):array {

		$thread_meta_users = [];
		foreach ($users as $user_info) {
			$thread_meta_users[$user_info["user_id"]] = Type_Thread_Meta_Users::initUserSchema(THREAD_MEMBER_ACCESS_ALL);
		}

		return $thread_meta_users;
	}

	/**
	 * отправляем ws-события при создании треда у заявки увольнения
	 *
	 * @param array $thread_meta_row
	 * @param int   $dismissal_request_id
	 * @param array $users
	 *
	 * @throws ParseFatalException
	 * @throws BusFatalException
	 * @throws \parseException
	 */
	protected static function _sendEventOnThreadAdd(array $thread_meta_row, int $dismissal_request_id, array $users):void {

		// формируем список пользователей для отправки в go_sender
		$talking_user_list = Type_Thread_Meta_Users::getTalkingUserList($users);

		$routine_key = sha1((string) $dismissal_request_id);

		$prepared_thread_meta = Type_Thread_Utils::prepareThreadMetaForFormat($thread_meta_row, 0, true);
		$thread_meta_row      = Apiv1_Format::threadMeta($prepared_thread_meta);

		// отправляем ивент о прикреплении треда к заявке увольнения
		Gateway_Bus_Sender::dismissalRequestThreadAttached($talking_user_list, $dismissal_request_id, $thread_meta_row, $thread_meta_row["thread_map"], $routine_key);

		// привязываем пользователей к треду для тайпингов
		Gateway_Bus_Sender::addUsersToThread(array_keys($users), $thread_meta_row["thread_map"], $routine_key);
	}
}