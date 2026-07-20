<?php

declare(strict_types=1);

namespace Compass\Pivot;

use BaseFrame\Siem\SiemProvider;

/**
 * Класс событий SIEM
 */
class Domain_Analytic_Entity_Siem
{
	private const _EVENT_TYPE_LOGIN_SUCCESS           = "user.login.success";
	private const _EVENT_TYPE_LOGIN_FAIL              = "user.login.fail";
	private const _EVENT_TYPE_PASSWORD_CHANGE_SUCCESS = "user.password.change_success";
	private const _EVENT_TYPE_PASSWORD_CHANGE_FAIL    = "user.password.change_fail";
	private const _EVENT_TYPE_LOGOUT                  = "user.session.logout";
	private const _EVENT_TYPE_SESSION_INVALIDATION    = "user.session.invalidation";

	/**
	 * Произошла успешная попытка входа
	 */
	public static function loginSuccess(int $user_id, string $device_id, string $ip, string $device_name, string $app_version): void
	{

		$data = [
			"user_id"     => $user_id,
			"device_id"   => $device_id,
			"device_name" => $device_name,
			"app_version" => $app_version,
			"ip_address"  => $ip,
		];

		SiemProvider::driver(CURRENT_MODULE)->send(__NAMESPACE__, self::_EVENT_TYPE_LOGIN_SUCCESS, $data);
	}

	/**
	 * Провалена попытка входа
	 */
	public static function loginFail(int $user_id, string $device_id, string $ip, string $device_name, string $app_version): void
	{

		$data = [
			"user_id"     => $user_id,
			"device_id"   => $device_id,
			"device_name" => $device_name,
			"app_version" => $app_version,
			"ip_address"  => $ip,
		];

		SiemProvider::driver(CURRENT_MODULE)->send(__NAMESPACE__, self::_EVENT_TYPE_LOGIN_FAIL, $data);
	}

	/**
	 * Изменили пароль
	 */
	public static function passwordChangeSuccess(int $user_id, string $device_id, string $ip, string $device_name, string $app_version): void
	{

		$data = [
			"user_id"     => $user_id,
			"device_id"   => $device_id,
			"device_name" => $device_name,
			"app_version" => $app_version,
			"ip_address"  => $ip,
		];

		SiemProvider::driver(CURRENT_MODULE)->send(__NAMESPACE__, self::_EVENT_TYPE_PASSWORD_CHANGE_SUCCESS, $data);
	}

	/**
	 * Провал смены пароля
	 */
	public static function passwordChangeFail(int $user_id, string $device_id, string $ip, string $device_name, string $app_version): void
	{

		$data = [
			"user_id"     => $user_id,
			"device_id"   => $device_id,
			"device_name" => $device_name,
			"app_version" => $app_version,
			"ip_address"  => $ip,
		];

		SiemProvider::driver(CURRENT_MODULE)->send(__NAMESPACE__, self::_EVENT_TYPE_PASSWORD_CHANGE_FAIL, $data);
	}

	/**
	 * Произошел логаут
	 */
	public static function logout(int $user_id, string $device_id, string $ip, string $device_name, string $app_version): void
	{

		$data = [
			"user_id"     => $user_id,
			"device_id"   => $device_id,
			"ip_address"  => $ip,
			"device_name" => $device_name,
			"app_version" => $app_version,
		];

		SiemProvider::driver(CURRENT_MODULE)->send(__NAMESPACE__, self::_EVENT_TYPE_LOGOUT, $data);
	}

	/**
	 * Произошла инвалидация сессии
	 */
	public static function sessionInvalidation(int $user_id, string $device_id, string $ip, string $device_name, string $app_version, string $invalidated_device_id, string $invalidated_ip, string $invalidated_device_name, string $invalidated_app_version): void
	{

		$data = [
			"user_id"                 => $user_id,
			"device_id"               => $device_id,
			"ip_address"              => $ip,
			"device_name"             => $device_name,
			"app_version"             => $app_version,
			"invalidate_session_data" => [
				"device_id"   => $invalidated_device_id,
				"ip_address"  => $invalidated_ip,
				"device_name" => $invalidated_device_name,
				"app_version" => $invalidated_app_version,
			]

		];

		SiemProvider::driver(CURRENT_MODULE)->send(__NAMESPACE__, self::_EVENT_TYPE_SESSION_INVALIDATION, $data);
	}
}
