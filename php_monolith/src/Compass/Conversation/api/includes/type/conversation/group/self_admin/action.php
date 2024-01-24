<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ReturnFatalException;
use CompassApp\Domain\Member\Entity\Permission;

/**
 * класс, для проверки самнозначения на админа
 */
class Type_Conversation_Group_SelfAdmin_Action {

	/**
	 * Выполним самоназначение на админа
	 *
	 * @param string $conversation_map
	 * @param int    $user_id
	 * @param int    $user_role
	 * @param int    $user_permissions
	 *
	 * @return array
	 * @throws ReturnFatalException
	 * @throws \parseException
	 * @throws cs_Conversation_IsGroupIfOwnerExist
	 * @throws cs_UserIsNotMember
	 * @long
	 */
	public static function do(string $conversation_map, int $user_id, int $user_role, int $user_permissions):array {

		Gateway_Db_CompanyConversation_Main::beginTransaction();

		// получаем мету диалога
		$meta_row = Gateway_Db_CompanyConversation_ConversationMetaLegacy::getForUpdate($conversation_map);

		if (!self::_isMember($meta_row, $user_id)) {

			Gateway_Db_CompanyConversation_Main::rollback();
			throw new cs_UserIsNotMember();
		}

		// если пользователь админ выходим
		if (self::_isAdmin($meta_row, $user_id)) {

			Gateway_Db_CompanyConversation_Main::rollback();
			return $meta_row;
		}

		// если администратор всех групп
		if (Permission::isGroupAdministrator($user_role, $user_permissions)) {

			self::_setRoleAdmin($meta_row, $user_id, $conversation_map);
			Gateway_Db_CompanyConversation_Main::commitTransaction();
			self::_changeLeftMenuAndEvent($user_id, $conversation_map);

			return $meta_row;
		}

		// проверим есть ли администратор в группе
		if (self::_adminExist($meta_row)) {

			Gateway_Db_CompanyConversation_Main::rollback();
			throw new cs_Conversation_IsGroupIfOwnerExist();
		}

		self::_setRoleAdmin($meta_row, $user_id, $conversation_map);
		Gateway_Db_CompanyConversation_Main::commitTransaction();

		// выполним дополнительные действия
		self::_changeLeftMenuAndEvent($user_id, $conversation_map);
		return $meta_row;
	}

	/**
	 * Проверим является ли пользователь участником группы
	 *
	 * @param array $meta_row
	 * @param int   $user_id
	 *
	 * @return  bool
	 */
	protected static function _isMember(array $meta_row, int $user_id):bool {

		// выбрасываем исключение, если пользователь не является участником группы
		if (!Type_Conversation_Meta_Users::isMember($user_id, $meta_row["users"])) {

			return false;
		}

		return true;
	}

	/**
	 * Проверим является ли пользователь администратором группы
	 *
	 * @param array $meta_row
	 * @param int   $user_id
	 *
	 * @return bool
	 */
	protected static function _isAdmin(array $meta_row, int $user_id):bool {

		// если пользователь является администратором в группе, выходим и ничего не делаем
		if (Type_Conversation_Meta_Users::isOwnerMember($user_id, $meta_row["users"])) {

			return true;
		}

		return false;
	}

	/**
	 * Проверим есть ли администратор в группе
	 *
	 * @param array $meta_row
	 *
	 * @return bool
	 */
	protected static function _adminExist(array $meta_row):bool {

		// если обычный сотрудник в компании, проверяем наличие администратора в группе, если он есть бросаем кастомную ошибку
		$owner_list = Type_Conversation_Meta_Users::getOwners($meta_row["users"]);
		if (count($owner_list) > 0) {

			return true;
		}
		return false;
	}

	/**
	 * Установим роль администратора
	 */
	protected static function _setRoleAdmin(array $meta_row, int $user_id, string $conversation_map):void {

		// устанавливаем новую роль участнику
		$meta_row["users"][$user_id] = Type_Conversation_Meta_Users::setUserRole(
			$meta_row["users"][$user_id],
			Type_Conversation_Meta_Users::ROLE_OWNER
		);

		// обновляем запись
		Gateway_Db_CompanyConversation_ConversationMetaLegacy::set($conversation_map, [
			"users"      => $meta_row["users"],
			"updated_at" => time(),
		]);
	}

	/**
	 * поменяем левое меню и запушим событие в систему
	 *
	 * @param int    $user_id
	 * @param string $conversation_map
	 *
	 * @throws \parseException
	 */
	protected static function _changeLeftMenuAndEvent(int $user_id, string $conversation_map):void {

		Domain_User_Action_Conversation_UpdateLeftMenu::do($user_id, $conversation_map, [
			"role" => Type_Conversation_Meta_Users::ROLE_OWNER,
		]);

		// пушим событие, что пользователь сменил роль
		Gateway_Event_Dispatcher::dispatch(Type_Event_UserConversation_UserRoleChanged::create(
			$user_id, $conversation_map, Type_Conversation_Meta_Users::ROLE_OWNER, time()
		));

		// очищаем кэш-мета для всех тредов текущего диалога
		Type_Phphooker_Main::sendClearThreadMetaCache($conversation_map);
	}
}