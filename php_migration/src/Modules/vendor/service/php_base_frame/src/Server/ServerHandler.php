<?php

namespace BaseFrame\Server;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Класс для работы с серверами
 */
class ServerHandler {

	/**
	 * теги по типу окружения
	 */
	public const DEV_TAG        = "dev";
	public const CI_TAG         = "ci";
	public const MASTER_TAG     = "master";
	public const STAGE_TAG      = "stage";
	public const PRODUCTION_TAG = "production";

	/**
	 * теги по виду продукта
	 */
	public const ON_PREMISE_TAG = "on-premise";
	public const SAAS_TAG       = "saas";

	/**
	 * кастомные теги
	 */
	public const LOCAL_TAG       = "local";
	public const INTEGRATION_TAG = "integration";

	/**
	 * группа по типу окружения
	 */
	protected const _ENVIRONMENT_GROUP_TAG_LIST = [
		self::DEV_TAG,
		self::CI_TAG,
		self::MASTER_TAG,
		self::STAGE_TAG,
		self::PRODUCTION_TAG,
	];

	/**
	 * группа тегов по виду продукта
	 */
	protected const _PRODUCT_GROUP_TAG_LIST = [
		self::ON_PREMISE_TAG,
		self::SAAS_TAG,
	];

	private static ServerHandler|null $_instance = null;
	private array                     $_server_tag_list;

	/**
	 * Server constructor.
	 *
	 * @throws ReturnFatalException
	 */
	private function __construct(array $server_tag_list) {

		if (count($server_tag_list) < 2) {
			throw new ReturnFatalException("incorrect server tag list!");
		}

		// проверяем корректность тега типа окружения
		$environment_tag_list = array_flip(self::_ENVIRONMENT_GROUP_TAG_LIST);
		if (!isset($environment_tag_list[$server_tag_list[0]])) {
			throw new ReturnFatalException("incorrect server environment tag list!");
		}

		// проверяем корректность тега по виду продукта
		$product_tag_list = array_flip(self::_PRODUCT_GROUP_TAG_LIST);
		if (!isset($product_tag_list[$server_tag_list[1]])) {
			throw new ReturnFatalException("incorrect server product tag list!");
		}

		$this->_server_tag_list = $server_tag_list;
	}

	/**
	 * инициализируем синглтон
	 *
	 */
	public static function init(array $server_tag_list):static {

		if (!is_null(static::$_instance)) {
			return static::$_instance;
		}

		return static::$_instance = new static($server_tag_list);
	}

	/**
	 * Возвращает экземпляр класса.
	 */
	public static function instance():static {

		if (is_null(static::$_instance)) {
			throw new ReturnFatalException("need to initialized before using");
		}

		return static::$_instance;
	}

	/**
	 * получаем server_tag_list
	 *
	 */
	public function tagList():array {

		return $this->_server_tag_list;
	}
}
