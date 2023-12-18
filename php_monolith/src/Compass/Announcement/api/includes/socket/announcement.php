<?php

namespace Compass\Announcement;

/**
 * Внутреннее api.
 * Служит для регистрации новых анонсов со стороны Compass.
 */
class Socket_Announcement extends \BaseFrame\Controller\Socket {

	/** @var string[] допустимые методы */
	public const ALLOW_METHODS = [
		"publish",
		"disable",
		"registerToken",
		"bindUserToCompany",
		"unbindUserFromCompany",
		"invalidateUser",
		"getExistingTypeList",
		"changeReceiverUserList",
	];

	/**
	 * Публикует новый анонс.
	 *
	 * @return array
	 *
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \paramException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public function publish():array {

		$template = $this->post(\Formatter::TYPE_JSON, "announcement");
		$source   = $this->post(\Formatter::TYPE_STRING, "source");

		$announcement = Domain_Announcement_Scenario_Socket::publish($template, $source);

		return self::ok([
			"announcement_id" => (int) $announcement->announcement_id,
		]);
	}

	/**
	 * Отключает анонс по типу.
	 *
	 * @return array
	 *
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function disable():array {

		$type         = $this->post(\Formatter::TYPE_INT, "type");
		$company_id   = $this->post(\Formatter::TYPE_INT, "company_id");
		$extra_filter = $this->post(\Formatter::TYPE_JSON, "extra_filter", []);

		Domain_Announcement_Scenario_Socket::disable($company_id, $type, $extra_filter);

		return self::ok();
	}

	/**
	 * Получаем существующие в пространстве анонсы
	 *
	 * @return array
	 *
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function getExistingTypeList():array {

		$type_list  = $this->post(\Formatter::TYPE_ARRAY_INT, "type_list");
		$company_id = $this->post(\Formatter::TYPE_INT, "company_id");

		$existing_type_list = Domain_Announcement_Scenario_Socket::getExistingTypeList($company_id, $type_list);

		return self::ok([
			"existing_type_list" => (array) $existing_type_list,
		]);
	}

	/**
	 * Инициирует новый токен подключения для пользователя.
	 *
	 * @return array
	 *
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \queryException
	 */
	public function registerToken():array {

		$user_id      = $this->post(\Formatter::TYPE_INT, "user_id");
		$device_id    = $this->post(\Formatter::TYPE_STRING, "device_id");
		$company_list = $this->post(\Formatter::TYPE_ARRAY_INT, "company_list");

		// генерируем токен доступа
		$jwt_token = Domain_User_Scenario_Socket::addToken($user_id, $device_id, $company_list);

		return self::ok([
			"authorization_token" => (string) $jwt_token,
		]);
	}

	/**
	 * Создает связь пользователь-компания.
	 * Вызывается при вступлении пользователя в компанию.
	 *
	 * @return array
	 *
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function bindUserToCompany():array {

		$user_id    = $this->post(\Formatter::TYPE_INT, "user_id");
		$company_id = $this->post(\Formatter::TYPE_INT, "company_id");

		Domain_User_Scenario_Socket::bindUserToCompany($user_id, $company_id);

		return self::ok();
	}

	/**
	 * Удаляет связь пользователь-компания.
	 * Вызывается при покидании пользователем компании.
	 *
	 * @return array
	 *
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function unbindUserFromCompany():array {

		$user_id    = $this->post(\Formatter::TYPE_INT, "user_id");
		$company_id = $this->post(\Formatter::TYPE_INT, "company_id");

		Domain_User_Scenario_Socket::unBindUserFromCompany($user_id, $company_id);

		return self::ok();
	}

	/**
	 * Удаляет все данные пользователя.
	 * Вызывается при блокировке пользователя в приложении.
	 *
	 * @return array
	 *
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function invalidateUser():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		Domain_User_Scenario_Socket::invalidateUser($user_id);

		return self::ok();
	}

	/**
	 * Поменять список получателей анонса
	 * Только для уникальных анонсов
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public function changeReceiverUserList():array {

		$add_user_id_list       = $this->post(\Formatter::TYPE_ARRAY_INT, "add_user_id_list");
		$remove_user_id_list    = $this->post(\Formatter::TYPE_ARRAY_INT, "remove_user_id_list");
		$company_id             = $this->post(\Formatter::TYPE_INT, "company_id", 0);
		$announcement_type_list = $this->post(\Formatter::TYPE_ARRAY_INT, "announcement_type_list");

		Domain_User_Scenario_Socket::changeReceiverUserList($company_id, $announcement_type_list, $add_user_id_list, $remove_user_id_list);

		return self::ok();
	}
}
