<?php declare(strict_types = 1);

namespace Compass\Announcement;

/**
 * Сценарии анонсов для API
 */
class Domain_Announcement_Scenario_Api {

	/**
	 * Максимальное кол-во анонсов за раз
	 */
	public const MAX_GET_ANNOUNCEMENT_COUNT = 500;

	/**
	 * Получить список публичных блокирующих анонсов.
	 *
	 * Если передан инициализирующий токен, то пытается получить из него ид пользователя
	 * и дополнительно вернуть персональные блокирующие анонсы.
	 *
	 * @param string $token
	 *
	 * @return array
	 *
	 * @throws \userAccessException
	 */
	public static function getPublicList(string $token):array {

		// проверяем, что токен пуст или валидный
		if ($token !== "" && !static::_validateInitialToken($token)) {
			throw new \userAccessException("Token is invalid");
		}

		// получаем ид пользователя из токена
		$user_id = static::_resolveUserIdFromInitialToken($token);

		return Domain_Announcement_Action_GetPublicList::do($user_id, self::MAX_GET_ANNOUNCEMENT_COUNT, 0);
	}

	/**
	 * Проверяет публичный токен
	 *
	 * @param string $token
	 *
	 * @return bool
	 */
	protected static function _validateInitialToken(string $token):bool {

		$payload = Type_Jwt_Main::getPayloadFromToken($token);

		$user_id = $payload["id"] ?? 0;

		if ($user_id === 0) {
			return false;
		}

		$payload = [
			"id" => $user_id,
		];

		return $token === Type_Jwt_Main::generate(SALT_INITIAL_ANNOUNCEMENT_TOKEN, $payload);
	}

	/**
	 * Получить ид пользователя из инициирующего токена.
	 *
	 * @param string $token
	 *
	 * @return int
	 */
	protected static function _resolveUserIdFromInitialToken(string $token):int {

		if ($token === "") {
			return 0;
		}

		return Type_Jwt_Main::getPayloadFromToken($token)["id"];
	}

	/**
	 * Получить все анонсы
	 *
	 * @param int $user_id
	 *
	 * @return Struct_Db_AnnouncementMain_Announcement[]
	 */
	public static function getList(int $user_id):array {

		return Domain_Announcement_Action_GetList::do($user_id, self::MAX_GET_ANNOUNCEMENT_COUNT, 0);
	}

	/**
	 * Выполняет прочтение анонса.
	 *
	 * @param int $user_id
	 * @param int $announcement_id
	 *
	 * @throws \paramException
	 */
	public static function read(int $user_id, int $announcement_id):void {

		try {
			$announcement = Gateway_Db_AnnouncementMain_Announcement::get($announcement_id);
		} catch (\cs_RowIsEmpty) {
			throw new \paramException("passed non-existing announcement id");
		}

		// если анонс нельзя отметить прочитанным, то ничего не делаем больше
		if (in_array($announcement->type, Domain_Announcement_Entity::getBlockingTypes())) {
			return;
		}

		// определяем является ли пользователь получателем
		if (!Domain_Announcement_Entity_Receiver::isUserReceiver($user_id, $announcement)) {
			throw new \paramException("you're not a announcement receiver");
		}

		Domain_Announcement_Action_Read::do($announcement, $user_id);
	}
}
