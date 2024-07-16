<?php declare(strict_types = 1);

namespace Compass\Announcement;

use Compass\Announcement\Type_Attribute_Api_Method as ApiMethod;

/**
 * Контроллер Атрибутов
 */
#[Type_Attribute_Api_Controller]
class Apiv1_Announcement extends Apiv1_Controller {

	/**
	 * Возвращает публичные анонсы.
	 *
	 * Публичными анонсами являются
	 * — Глобальные блокирующие для всех
	 * — Глобальные блокирующие для конкретно этого пользователя
	 *
	 * На вход получает зашифрованный user_id, для получения персональных анонсов.
	 * Если токен не передан, то возвращает только общие глобальные.
	 *
	 * @return array
	 *
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \userAccessException
	 * @throws cs_blockException
	 */
	#[ApiMethod(ApiMethod::ALLOWED, ApiMethod::ALLOWED_FOR_NON_AUTHORIZED)]
	public function getPublic():array {

		$token  = $this->post(\Formatter::TYPE_STRING, "initial_token", "");

		try {
			$announcement_list = Domain_Announcement_Scenario_Api::getPublicList($token);
		} catch (\userAccessException $e) {

			// если вдруг словили ошибку доступа,
			// то блокируем пользователя по ип за попытку прислать левый токен
			Type_Antispam_Ip::checkAndIncrementBlock(Type_Antispam_Ip::WRONG_INITIAL_TOKEN);
			throw $e;
		}

		$formatted = array_map(fn(Struct_Db_AnnouncementMain_Announcement $e) => Apiv1_Format::announcement($e), $announcement_list);

		return self::ok([
			"announcement_list" => (array) $formatted,
		]);
	}

	/**
	 * Возвращает данные для создания ws-подключения со стороны клиента.
	 * Записывает токен в cookie, если он верный, чтобы клиент больше мог его не передавать.
	 *
	 * @return array
	 *
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \busException
	 * @throws \userAccessException
	 * @throws cs_blockException
	 */
	#[ApiMethod(ApiMethod::ALLOWED, ApiMethod::ALLOWED_FOR_NON_AUTHORIZED)]
	public function tryConnect():array {

		$jwt_token = $this->post(\Formatter::TYPE_STRING, "authorization_token");

		// поскольку метод доступен для неавторизованных пользователей
		// то ид получаем из переданного токена, а не из контроллера
		$user_id = Type_Auth_Main::getUserIdByToken($jwt_token, getDeviceId());

		// если получить ид из токена не удалось, то блокируем доступ к методу
		if ($user_id === 0) {

			// сначала проверяем, может быть пользователя нужно заблокировать по ip
			Type_Antispam_Ip::checkAndIncrementBlock(Type_Antispam_Ip::WRONG_AUTHORIZATION_TOKEN);
			throw new \userAccessException("passed authorization token is invalid");
		}

		try {

			// получаем и возвращаем параметры подключения
			[$token, $url] = Domain_User_Scenario_Api::getConnection($user_id);
		} catch (cs_PlatformNotFound) {
			throw new \paramException("passed unknown platform");
		}

		// записываем токен в куки пользователя, чтобы клиенты не передавали его в следующих запросах
		Type_Session_Main::setAuthToken($jwt_token);

		return $this->ok([
			"token" => (string) $token,
			"url"   => (string) $url,
		]);
	}

	/**
	 * Возвращает все доступные пользователю анонсы.
	 *
	 * @return array
	 */
	#[ApiMethod(ApiMethod::ALLOWED)]
	public function getList():array {

		$announcement_list = Domain_Announcement_Scenario_Api::getList($this->user_id);
		$formatted         = array_map(fn(Struct_Db_AnnouncementMain_Announcement $e) => Apiv1_Format::announcement($e), $announcement_list);

		return self::ok([
			"announcement_list" => (array) $formatted,
		]);
	}

	/**
	 * Отмечает анонс прочитанным.
	 *
	 * @return array
	 *
	 * @throws \paramException
	 * @throws \parseException
	 * @throws cs_blockException
	 */
	#[ApiMethod(ApiMethod::ALLOWED)]
	public function read():array {

		$announcement_id = $this->post(\Formatter::TYPE_INT, "announcement_id");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::ANNOUNCEMENT_READ);

		Domain_Announcement_Scenario_Api::read($this->user_id, $announcement_id);

		return self::ok();
	}
}