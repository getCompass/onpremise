<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

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
	 * @param int    $is_root
	 *
	 * @return Struct_Db_PivotUser_User
	 * @throws Domain_User_Exception_PhoneNumberBinding
	 * @throws Domain_User_Exception_Mail_BelongAnotherUser
	 * @throws \BaseFrame\Exception\Domain\InvalidPhoneNumber
	 * @throws cs_DamagedActionException
	 * @throws \queryException
	 */
	public static function do(
		string $phone_number,
		string $mail,
		string $password_hash,
		string $user_agent,
		string $ip,
		string $full_name,
		string $avatar_file_map,
		array  $extra,
		int    $set_user_id = 0,
		int    $default_partner_id = 0,
		int    $is_root = 0
	):Struct_Db_PivotUser_User {

		$prepare_data          = static::prepare(...func_get_args());
		$prepare_data->is_root = $is_root;

		return static::effect(static::store($prepare_data, $set_user_id, $default_partner_id))->user;
	}

	/**
	 * Создаем сущность пользователя человека.
	 *
	 * @throws Domain_User_Exception_PhoneNumberBinding
	 * @throws Domain_User_Exception_Mail_BelongAnotherUser
	 * @throws cs_DamagedActionException
	 */
	public static function store(Struct_User_Action_Create_Prepare $data, int $set_user_id = 0, int $default_partner_id = 0):Struct_User_Action_Create_Store {

		$parent_data = parent::store($data, $set_user_id, $default_partner_id);

		// цепляем номер телефона к пользователю, если есть
		if ($data->phone_number !== "") {
			static::_bindPhone($parent_data->user->user_id, $parent_data->prepare_data->phone_number, $parent_data->prepare_data->phone_number_hash, $data->action_time);
		}

		// цепляем почту к пользователю, если есть
		if ($data->mail !== "") {
			static::_bindMail($parent_data->user->user_id, $data->mail, $data->mail_hash, $data->password_hash, $data->action_time);
		}

		// добавляем запись в таблицу последних регистраций
		static::_insertToLastRegistration($parent_data->user->user_id, $default_partner_id, $data->ip_address);

		return $parent_data;
	}

	/**
	 * Цепляем номер телефона за пользователем
	 *
	 * @throws Domain_User_Exception_PhoneNumberBinding
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \parseException
	 * @throws \queryException
	 */
	protected static function _bindPhone(int $user_id, string $phone_number, string $phone_number_hash, int $action_time):void {

		/** начало транзакции */
		Gateway_Db_PivotPhone_PhoneUniqList::beginTransaction();

		try {

			// получаем запись на чтение с блокировкой
			$phone_uniq = Gateway_Db_PivotPhone_PhoneUniqList::getForUpdate($phone_number_hash);

			// проверим, что номер на текущий момент ни за кем не закреплен
			if ($phone_uniq->user_id !== 0) {

				Gateway_Db_PivotPhone_PhoneUniqList::rollback();
				throw new Domain_User_Exception_PhoneNumberBinding("phone number belong to another user");
			}

			// фиксируем пользователя в историю и обновляем запись
			$phone_uniq->previous_user_list[] = $user_id;
			Gateway_Db_PivotPhone_PhoneUniqList::set($phone_number_hash, [
				"user_id"            => $user_id,
				"has_sso_account"    => 0,
				"binding_count"      => $phone_uniq->binding_count + 1,
				"last_binding_at"    => time(),
				"updated_at"         => time(),
				"previous_user_list" => $phone_uniq->previous_user_list,
			]);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {

			// записи нет, это нормально, скорее всего номер новый
			Gateway_Db_PivotPhone_PhoneUniqList::insertOrUpdate($phone_number_hash, $user_id, false, time(), time(), 1, time(), 0, [$user_id]);
		}

		Gateway_Db_PivotPhone_PhoneUniqList::commitTransaction();
		/** конец транзакции */

		Gateway_Db_PivotHistoryLogs_UserChangePhoneHistory::insert($user_id, "", $phone_number, "", $action_time, 0);

		try {

			// проверяем - возможно запись уже есть
			Gateway_Db_PivotUser_UserSecurity::getOne($user_id);

			// обновляем
			Gateway_Db_PivotUser_UserSecurity::set($user_id, [
				"phone_number" => $phone_number,
				"updated_at"   => $action_time,
			]);
		} catch (\cs_RowIsEmpty) {

			// создаем
			Gateway_Db_PivotUser_UserSecurity::insert($user_id, $phone_number, "", $action_time, 0);
		}
	}

	/**
	 * Цепляем почту за пользователем
	 *
	 * @throws Domain_User_Exception_Mail_BelongAnotherUser
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected static function _bindMail(int $user_id, string $mail, string $mail_hash, string $password_hash, int $action_time):void {

		/** начало транзакции */
		Gateway_Db_PivotMail_MailUniqList::beginTransaction();

		try {

			// получаем запись на чтение с блокировкой
			$mail_uniq = Gateway_Db_PivotMail_MailUniqList::getForUpdate($mail_hash);

			// проверим, что номер на текущий момент ни за кем не закреплен
			if ($mail_uniq->user_id !== 0) {

				Gateway_Db_PivotMail_MailUniqList::rollback();
				throw new Domain_User_Exception_Mail_BelongAnotherUser("mail belong to another user");
			}

			// обновляем запись
			Gateway_Db_PivotMail_MailUniqList::set($mail_hash, [
				"user_id"         => $user_id,
				"has_sso_account" => 0,
				"updated_at"      => time(),
				"password_hash"   => $password_hash,
			]);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {

			// записи нет, это нормально, скорее всего номер новый
			Gateway_Db_PivotMail_MailUniqList::insertOrUpdate(new Struct_Db_PivotMail_MailUniq(
				$mail_hash, $user_id, false, $action_time, 0, $password_hash
			));
		}

		Gateway_Db_PivotMail_MailUniqList::commitTransaction();
		/** конец транзакции */

		try {

			// проверяем - возможно запись уже есть
			Gateway_Db_PivotUser_UserSecurity::getOne($user_id);

			// обновляем
			Gateway_Db_PivotUser_UserSecurity::set($user_id, [
				"mail"       => $mail,
				"updated_at" => $action_time,
			]);
		} catch (\cs_RowIsEmpty) {

			// создаем
			Gateway_Db_PivotUser_UserSecurity::insert($user_id, "", $mail, $action_time, 0);
		}
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

		// отправляем в партнерскую программу событие о регистрации пользователя
		Domain_Partner_Entity_Event_UserRegistered::create($data->user->user_id);

		if (!ServerProvider::isOnPremise()) {
			return $data;
		}

		// отправляем в premise-модуль событие о регистрации пользователя
		Gateway_Socket_Premise::userRegistered($data->user->user_id, $data->user->npc_type, $data->prepare_data->is_root);

		return $data;
	}
}
