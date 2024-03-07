<?php

namespace Compass\Pivot;

/**
 * Объект с подготовленной информацией для создания пользователя
 *
 * Class Struct_User_Action_Create_Prepare
 */
class Struct_User_Action_Create_Prepare {

	/**
	 * Struct_User_Action_Create_Prepare constructor.
	 *
	 * @param Struct_Db_PivotUser_User $draft_user
	 * @param string                   $phone_number
	 * @param string                   $phone_number_hash
	 * @param int                      $action_time
	 * @param string                   $user_agent
	 * @param string                   $ip_address
	 */
	public function __construct(
		/**
		 * подготовленный черновик объекта пользователя
		 */
		public Struct_Db_PivotUser_User $draft_user,

		/**
		 * номер телефона пользователя в чистом виде
		 */
		public string                   $phone_number,

		/**
		 * хэш-сумма от номера телефона
		 */
		public string                   $phone_number_hash,

		/**
		 * хэш-сумма пароля
		 */
		public string                   $password_hash,

		/**
		 * номер телефона пользователя в чистом виде
		 */
		public string                   $mail,

		/**
		 * хэш-сумма от почты
		 */
		public string                   $mail_hash,

		/**
		 * время выполнения действия
		 */
		public int                      $action_time,

		/**
		 * user-agent пользователя при регистрации
		 */
		public string                   $user_agent,

		/**
		 * ip_address пользователя при регистрации
		 */
		public string                   $ip_address,
	) {
	}
}