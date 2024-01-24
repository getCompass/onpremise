<?php

namespace Compass\Pivot;

/**
 * Объект с подготовленной информацией для создания пользователя
 *
 * Class Struct_User_Action_Create_Prepare
 */
class Struct_User_Action_Create_Prepare {

	/**
	 * @var Struct_Db_PivotUser_User подготовленный черновик объекта пользователя
	 */
	public Struct_Db_PivotUser_User $draft_user;

	/**
	 * @var string номер телефона пользователя в чистом виде
	 */
	public string $phone_number;

	/**
	 * @var string
	 */
	public string $phone_number_hash;

	/**
	 * @var int время выполнения действия
	 */
	public int $action_time;

	/**
	 * @var string user-agent пользователя при регистрации
	 */
	public string $user_agent;

	/**
	 * @var string ip_address пользователя при регистрации
	 */
	public string $ip_address;

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
	public function __construct(Struct_Db_PivotUser_User $draft_user,
					    string                   $phone_number,
					    string                   $phone_number_hash,
					    int                      $action_time,
					    string                   $user_agent,
					    string                   $ip_address) {

		$this->draft_user        = $draft_user;
		$this->phone_number      = $phone_number;
		$this->phone_number_hash = $phone_number_hash;
		$this->action_time       = $action_time;
		$this->user_agent        = $user_agent;
		$this->ip_address        = $ip_address;
	}

}