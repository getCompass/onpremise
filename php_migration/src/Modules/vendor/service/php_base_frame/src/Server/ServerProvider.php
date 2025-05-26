<?php

namespace BaseFrame\Server;

/**
 * Класс-обертка для работы с серверами
 */
class ServerProvider {

	/**
	 * Закрываем конструктор.
	 */
	protected function __construct() {

	}

	/**
	 * проверяем, что это тестовый сервер
	 *
	 * @return bool
	 */
	public static function isDev():bool {

		if (self::_hasTag(ServerHandler::DEV_TAG)) {
			return true;
		}

		return false;
	}

	/**
	 * проверяем, что это CI сервер
	 *
	 * @return bool
	 */
	public static function isCi():bool {

		if (self::_hasTag(ServerHandler::CI_TAG)) {
			return true;
		}

		return false;
	}

	/**
	 * проверяем, что это Stage сервер
	 *
	 * @return bool
	 */
	public static function isStage():bool {

		if (self::_hasTag(ServerHandler::STAGE_TAG)) {
			return true;
		}

		return false;
	}

	/**
	 * проверяем, что это on-premise сервер
	 *
	 * @return bool
	 */
	public static function isOnPremise():bool {

		if (self::_hasTag(ServerHandler::ON_PREMISE_TAG)) {
			return true;
		}

		return false;
	}

	/**
	 * проверяем, что это saas сервер
	 *
	 * @return bool
	 */
	public static function isSaas():bool {

		if (self::_hasTag(ServerHandler::SAAS_TAG)) {
			return true;
		}

		return false;
	}

	/**
	 * проверяем, что это production сервер
	 *
	 * @return bool
	 */
	public static function isProduction():bool {

		if (self::_hasTag(ServerHandler::PRODUCTION_TAG)) {
			return true;
		}

		return false;
	}

	/**
	 * проверяем, что это локальный сервер
	 *
	 * @return bool
	 */
	public static function isLocal():bool {

		if (self::_hasTag(ServerHandler::LOCAL_TAG)) {
			return true;
		}

		return false;
	}

	/**
	 * проверяем, что сервер с интеграцией
	 *
	 * @return bool
	 */
	public static function isIntegration():bool {

		if (self::_hasTag(ServerHandler::INTEGRATION_TAG)) {
			return true;
		}

		return false;
	}

	/**
	 * проверяем, что это master сервер
	 *
	 * @return bool
	 */
	public static function isMaster():bool {

		if (self::_hasTag(ServerHandler::MASTER_TAG)) {
			return true;
		}

		return false;
	}

	/**
	 * проверяем, что это тестовое окружение
	 *
	 * @return bool
	 */
	public static function isTest():bool {

		if (self::isDev() || self::isCi() || self::isMaster() || self::isLocal()) {
			return true;
		}

		return false;
	}

	/**
	 * Проверка запущено ли на тестовом сервере
	 *
	 * @return void
	 * @throws \parseException
	 */
	public static function assertTest():void {

		// если запущены не на тестовом сервере
		if (!self::isTest()) {
			throw new \ParseException("called is not test server");
		}
	}

	/**
	 * Проверка запущено ли на продакшене
	 *
	 * @return void
	 * @throws \parseException
	 */
	public static function assertProduction():void {

		// если запущены не на продакшене
		if (!self::isProduction()) {
			throw new \ParseException("called is not production server");
		}
	}

	// ---------------------------------------------------
	// PROTECTED
	// ---------------------------------------------------

	/**
	 * проверяем на совпадение всех тега
	 *
	 * @param string $tag
	 *
	 * @return bool
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 */
	protected static function _hasTag(string $tag):bool {

		$server_tag_list = array_flip(ServerHandler::instance()->tagList());
		if (isset($server_tag_list[$tag])) {
			return true;
		}

		return false;
	}
}
