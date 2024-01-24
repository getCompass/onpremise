<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Class Struct_Event_Base
 * Базовая структура события.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_User_AuthAuthorizationSuccess extends Struct_Default {

	/** @var int идентификатор пользователя */
	public int $user_id;

	/** @var string имя девайса пользователя */
	public string $device;

	/** @var string место нахождения пользователя */
	public string $location;

	/** @var string ip адрес пользователя */
	public string $ip_address;

	/** @var string имя компании */
	public string $company;

	/** @var bool флаг 0/1, событие сразу после добавления пользователя */
	public bool $first_time;

	/** @var bool флаг 0/1, у пользователя уже имеются другие компании */
	public bool $is_first_company;

	/** @var bool флаг 0/1, является ли пользователь создателем компании */
	public bool $is_creator;

	/**
	 * Статический конструктор.
	 *
	 * @param int    $user_id
	 * @param string $device
	 * @param string $location
	 * @param string $ip_address
	 * @param string $company_name
	 * @param bool   $is_after_registration
	 * @param bool   $is_first_company
	 * @param        $is_creator
	 *
	 * @return static
	 * @throws \parseException
	 */
	public static function build(int    $user_id,
					     string $device, string $location, string $ip_address, string $company_name,
					     bool   $is_after_registration, bool $is_first_company, bool $is_creator):static {

		return new static([
			"user_id"          => $user_id,
			"device"           => $device,
			"location"         => $location,
			"ip_address"       => $ip_address,
			"company"          => $company_name,
			"first_time"       => $is_after_registration,
			"is_first_company" => $is_first_company,
			"is_creator"       => $is_creator,
		]);
	}
}
