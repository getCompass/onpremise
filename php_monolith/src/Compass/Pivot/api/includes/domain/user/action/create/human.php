<?php

namespace Compass\Pivot;

/**
 * Действие создание пользователя-человека
 */
class Domain_User_Action_Create_Human extends Domain_User_Action_Create {

	/** @var int класс создает пользователей-человеков */
	protected const _NPC_TYPE = Type_User_Main::NPC_TYPE_HUMAN;

	/**
	 * Создаем пользователя-человека.
	 * Такая же функция, что и в родительском классе, но с верными исключениями.
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
	 * @throws Domain_User_Exception_PhoneNumberBinding
	 * @throws \BaseFrame\Exception\Domain\InvalidPhoneNumber
	 * @throws cs_DamagedActionException
	 * @throws \queryException
	 */
	public static function do(string $phone_number, string $user_agent, string $ip, string $full_name, string $avatar_file_map, array $extra, int $set_user_id = 0, int $default_partner_id = 0):Struct_Db_PivotUser_User {

		return static::effect(static::store(static::prepare(...func_get_args()), $set_user_id, $default_partner_id))->user;
	}

	/**
	 * Создаем сущность пользователя человека.
	 *
	 * @throws Domain_User_Exception_PhoneNumberBinding
	 * @throws cs_DamagedActionException
	 */
	public static function store(Struct_User_Action_Create_Prepare $data, int $set_user_id = 0, int $default_partner_id = 0):Struct_User_Action_Create_Store {

		$parent_data = parent::store($data, $set_user_id, $default_partner_id);

		// цепляем номер телефона к пользователю
		static::_bindPhoneUniq($parent_data->user->user_id, $parent_data->prepare_data->phone_number_hash);

		Gateway_Db_PivotHistoryLogs_UserChangePhoneHistory::insert($parent_data->user->user_id, "", $data->phone_number, "", $data->action_time, 0);
		Gateway_Db_PivotUser_UserSecurity::insert($parent_data->user->user_id, $data->phone_number, $data->action_time, 0);

		// добавляем запись в таблицу последних регистраций
		static::_insertToLastRegistration($parent_data->user->user_id, $default_partner_id, $data->ip_address);

		return $parent_data;
	}

	/**
	 * Обновляет запись для нового номера телефона.
	 * @throws Domain_User_Exception_PhoneNumberBinding
	 */
	#[\JetBrains\PhpStorm\ArrayShape([Struct_Db_PivotPhone_PhoneUniq::class, "bool"])]
	protected static function _bindPhoneUniq(int $user_id, string $phone_number_hash):array {

		$is_number_reused = false;

		/** начало транзакции */
		Gateway_Db_PivotPhone_PhoneUniqList::beginTransaction();

		try {

			// получаем запись на чтение с блокировкой
			$phone_uniq       = Gateway_Db_PivotPhone_PhoneUniqList::getForUpdate($phone_number_hash);
			$is_number_reused = true;

			// проверим, что номер на текущий момент ни за кем не закреплен
			if ($phone_uniq->user_id !== 0) {

				Gateway_Db_PivotPhone_PhoneUniqList::rollback();
				throw new Domain_User_Exception_PhoneNumberBinding("phone number belong to another user");
			}

			// фиксируем пользователя в историю и обновляем запись
			$phone_uniq->previous_user_list[] = $user_id;
			Gateway_Db_PivotPhone_PhoneUniqList::set($phone_number_hash, [
				"user_id"            => $user_id,
				"binding_count"      => $phone_uniq->binding_count + 1,
				"last_binding_at"    => time(),
				"updated_at"         => time(),
				"previous_user_list" => $phone_uniq->previous_user_list,
			]);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {

			// записи нет, это нормально, скорее всего номер новый
			$phone_uniq = Gateway_Db_PivotPhone_PhoneUniqList::insertOrUpdate($phone_number_hash, $user_id, time(), time(), 1, time(), 0, [$user_id]);
		}

		Gateway_Db_PivotPhone_PhoneUniqList::commitTransaction();
		/** конец транзакции */

		return [$phone_uniq, $is_number_reused];
	}

	/**
	 * Добавляем запись в таблицу последних регистраций.
	 */
	protected static function _insertToLastRegistration(int $user_id, int $partner_id, string $ip_address):void {

		$normalized_ip     = ip2long($ip_address);
		$autonomous_system = new Struct_Db_PivotSystem_AutonomousSystem(0, 0, 0, "", "");

		// если ip нормально конверта
		if ($normalized_ip !== false) {

			try {

				// пытаемся из базы прочитать, возможно as извастная
				$autonomous_system = Gateway_Db_PivotSystem_AutonomousSystem::getOne(ip2long($ip_address));
			} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {
			}
		}

		// формируем экстру для записи
		$extra = Domain_User_Entity_RegistrationExtra::initExtra();
		$extra = Domain_User_Entity_RegistrationExtra::set($extra, $ip_address, $autonomous_system->code, $autonomous_system->country_code, $autonomous_system->name);

		// вставляем запись в историю
		Gateway_Db_PivotData_LastRegisteredUser::insert($user_id, $partner_id, $extra);
	}

	/**
	 * Совершаем все необходимые действия после создания пользователя
	 *
	 * @param Struct_User_Action_Create_Store $data
	 *
	 * @return Struct_User_Action_Create_Store
	 * @throws \queryException
	 */
	public static function effect(Struct_User_Action_Create_Store $data):Struct_User_Action_Create_Store {

		// добавляем в историю действие о регистрации нового пользователя
		Domain_User_Entity_UserActionComment::addUserRegisterAction($data->user->user_id, $data->prepare_data->phone_number, $data->prepare_data->ip_address);

		// отправляем в crm событие о регистрации пользователя
		Domain_Crm_Entity_Event_UserRegistration::create($data->user->user_id);

		return $data;
	}
}
