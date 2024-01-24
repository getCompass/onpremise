<?php

namespace Compass\Pivot;

/**
 * Действие создание пользователя-оператора
 *
 * Class Domain_User_Action_Create_Operator
 */
class Domain_User_Action_Create_Operator extends Domain_User_Action_Create {

	protected const _NPC_TYPE = Type_User_Main::NPC_TYPE_OPERATOR;

	/**
	 * Подготавливаем черновик оператора перед его созданием
	 *
	 * @throws cs_DamagedActionException
	 */
	public static function prepare(string $phone_number, string $user_agent, string $ip, string $full_name, string $avatar_file_map, array $extra):Struct_User_Action_Create_Prepare {

		// проверяем, что запускается дочерний класс с переопределенными константными значениями
		static::_assertClassValues();

		$action_time = time();
		$draft_user  = new Struct_Db_PivotUser_User(
			0,
			static::_NPC_TYPE,
			0,
			0,
			0,
			$action_time,
			0,
			0,
			"",
			$full_name,
			$avatar_file_map,
			$extra
		);

		return new Struct_User_Action_Create_Prepare($draft_user, $phone_number, "", $action_time, $user_agent, $ip);
	}

	/**
	 * Создаем сущность оператора
	 *
	 * @param Struct_User_Action_Create_Prepare $data
	 * @param int                               $set_user_id
	 * @param int                               $default_partner_id
	 *
	 * @return Struct_User_Action_Create_Store
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws cs_DamagedActionException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function store(Struct_User_Action_Create_Prepare $data, int $set_user_id = 0, int $default_partner_id = 0):Struct_User_Action_Create_Store {

		// проверяем, что запускается дочерний класс с переопределенными константными значениями
		static::_assertClassValues();

		// получаем идентификатор пользователя
		$user_id = Type_Autoincrement_Pivot::getNextId(Type_Autoincrement_Pivot::OPERATOR_USER_ID_KEY);

		// только на паблике жестко ограничиваем
		if (!isTestServer() && !isStageServer() && ($user_id < 2000 || $user_id > 3999)) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("incorrect operator user_id from autoincrement");
		}

		// создаем объект чистовичок для пользователя
		$user          = $data->draft_user;
		$user->user_id = $user_id;

		// если передали id, то поставим пользователю его
		if ($set_user_id !== 0) {
			$user->user_id = $set_user_id;
		}

		// создаем сущность пользователя в базе
		Gateway_Db_PivotUser_UserList::insert($user);

		return new Struct_User_Action_Create_Store($user, $data);
	}
}
