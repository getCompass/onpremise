<?php

namespace Compass\Pivot;

/**
 * Базовый класс для действия создания пользователя, без привязки к его npc_type
 *
 * Class Domain_User_Action_Create
 */
abstract class Domain_User_Action_Create {

	/**
	 * Тип пользователя - переопределить в дочернем классе
	 */
	protected const _NPC_TYPE = null;

	/**
	 * Создаем пользователя
	 *
	 * @param string $phone_number
	 * @param string $user_agent
	 * @param string $ip
	 * @param string $full_name
	 * @param string $avatar_file_map
	 * @param array  $extra
	 * @param int    $set_user_id
	 * @param int    $default_partner_id
	 *
	 * @return Struct_Db_PivotUser_User
	 * @throws \BaseFrame\Exception\Domain\InvalidPhoneNumber
	 * @throws cs_DamagedActionException
	 */
	public static function do(string $phone_number, string $user_agent, string $ip, string $full_name, string $avatar_file_map, array $extra, int $set_user_id = 0, int $default_partner_id = 0):Struct_Db_PivotUser_User {

		return static::effect(static::store(static::prepare(...func_get_args()), $set_user_id, $default_partner_id))->user;
	}

	/**
	 * Подготавливаем ~почву~ черновик пользователя перед его созданием
	 *
	 * @param string $phone_number
	 * @param string $user_agent
	 * @param string $ip
	 * @param string $full_name
	 * @param string $avatar_file_map
	 * @param array  $extra
	 *
	 * @return Struct_User_Action_Create_Prepare
	 * @throws \BaseFrame\Exception\Domain\InvalidPhoneNumber
	 * @throws cs_DamagedActionException
	 */
	public static function prepare(string $phone_number, string $user_agent, string $ip,
						 string $full_name, string $avatar_file_map, array $extra):Struct_User_Action_Create_Prepare {

		// проверяем, что запускается дочерний класс с переопределенными константными значениями
		static::_assertClassValues();

		// достаем из номера телефона код страны
		if ($phone_number !== "") {

			$phone_number_obj = new \BaseFrame\System\PhoneNumber($phone_number);
			$country_code     = $phone_number_obj->countryCode();
			$phone_number     = $phone_number_obj->number();
		}

		$action_time = time();
		$draft_user  = new Struct_Db_PivotUser_User(
			0,
			static::_NPC_TYPE,
			0,
			0,
			dayStart($action_time),
			$action_time,
			0,
			0,
			$country_code ?? "",
			$full_name,
			$avatar_file_map,
			$extra
		);

		$phone_number_hash = Type_Hash_PhoneNumber::makeHash($phone_number);

		return new Struct_User_Action_Create_Prepare($draft_user, $phone_number, $phone_number_hash, $action_time, $user_agent, $ip);
	}

	/**
	 * Создаем сущность пользователя
	 * @throws cs_DamagedActionException
	 */
	public static function store(Struct_User_Action_Create_Prepare $data, int $set_user_id = 0, int $default_partner_id = 0):Struct_User_Action_Create_Store {

		// проверяем, что запускается дочерний класс с переопределенными константными значениями
		static::_assertClassValues();

		// получаем идентификатор пользователя
		$user_id = Type_Autoincrement_Pivot::getNextId(Type_Autoincrement_Pivot::USER_ID_KEY, 1);

		// создаем объект чистовичок для пользователя
		$user                        = $data->draft_user;
		$user->user_id               = $user_id;
		$user->invited_by_partner_id = $default_partner_id;

		// если передали id, то поставим пользователю его
		if ($set_user_id !== 0) {
			$user->user_id = $set_user_id;
		}

		// устанавливаем цвет аватара
		$user->extra = Type_User_Main::setAvatarColorId($user->extra, \BaseFrame\Domain\User\Avatar::getColorByUserId($user->user_id));

		// создаем сущность пользователя в базе
		Gateway_Db_PivotUser_UserList::insert($user);

		// убрал отсюда добавление номера телефона, потому что бота номер не нужен,
		// но записи почему-то вставляются/обновляются; унес все в класс создания пользователя-человека

		return new Struct_User_Action_Create_Store($user, $data);
	}

	/**
	 * @param Struct_User_Action_Create_Store $data
	 *
	 * @return Struct_User_Action_Create_Store
	 * @throws cs_DamagedActionException
	 */
	public static function effect(Struct_User_Action_Create_Store $data):Struct_User_Action_Create_Store {

		// проверяем, что запускается дочерний класс с переопределенными константными значениями
		static::_assertClassValues();

		// в этом случае – ничего делать не нужно, что не скажешь про npc_type human и других ...

		return $data;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Проверяем данные класса
	 *
	 * @throws cs_DamagedActionException
	 */
	protected static function _assertClassValues():void {

		if (static::_NPC_TYPE === null) {
			throw new cs_DamagedActionException();
		}
	}
}