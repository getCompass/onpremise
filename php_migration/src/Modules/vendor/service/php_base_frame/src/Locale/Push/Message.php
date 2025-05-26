<?php

namespace BaseFrame\Locale\Push;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс для формирование текста и локализации пуша в сообщении
 */
class Message extends Base {

	// разрешенные сущности, где может быть сообщение
	protected const _ALLOWED_ENTITY_TYPE_LIST = [];

	// неизвестный тип сообщения
	public const MESSAGE_UNKNOWN = "unknown";

	// разрешенные типы сообщений
	protected const _ALLOWED_TYPE_LIST = [];

	// типы сообщений, для которых нужен аргумент
	protected const _NEED_ARG_MESSAGE_TYPE_LIST = [];

	protected const _BASE_ARGS_COUNT = 0; // сколько изначально нужно аргументов
	protected const _BASE_LOCALE_KEY = "MESSAGE"; // базовый ключ локализации

	protected string $_entity_type; // тип сущности, к которому относится сообщение
	protected string $_type           = self::MESSAGE_UNKNOWN; // тип сообщения
	protected string $_additional_key = ""; // добавочный ключ, который можно определить в дочерних классах

	/**
	 * Конструктор
	 *
	 * @param string $entity_type
	 *
	 * @throws ParseFatalException
	 */
	public function __construct(string $entity_type) {

		parent::__construct();

		// если передали неизвестный тип сущности - отваливаемся
		if (!in_array($entity_type, static::_ALLOWED_ENTITY_TYPE_LIST)) {
			throw new ParseFatalException("unknown message entity type");
		}

		$this->_entity_type = $entity_type;
	}

	/**
	 * Установить тип сообщения
	 *
	 * @param string $type
	 *
	 * @return $this
	 * @throws ParseFatalException
	 */
	public function setType(string $type):self {

		if (!in_array($type, static::_ALLOWED_TYPE_LIST[$this->_entity_type])) {
			throw new ParseFatalException("unknown message type");
		}

		$this->_type = $type;

		if (array_key_exists($this->_type, static::_NEED_ARG_MESSAGE_TYPE_LIST)) {
			$this->_args_count += static::_NEED_ARG_MESSAGE_TYPE_LIST[$this->_type];
		}

		return $this;
	}

	/**
	 * Получить результат локализации
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	public function getLocaleResult():array {

		// добавляем тип сообщения
		$this->_locale_key = strtoupper($this->_type) . "_" . $this->_locale_key;

		// добавляем ключ, сформированный в дочерних классах
		$this->_locale_key = $this->_additional_key !== "" ? strtoupper($this->_additional_key) . "_" . $this->_locale_key : $this->_locale_key;

		// добавляем в ключ тип сущности, к которому относится сообщение
		$this->_locale_key = strtoupper($this->_entity_type) . "_" . $this->_locale_key;

		// если не знаем, что за тип сообщения - возвращаем пустой объект
		if ($this->_type == static::MESSAGE_UNKNOWN) {

			$this->_locale_key = "";
			$this->_args       = [];
			$this->_args_count = 0;
		}

		// проверяем, есть ли нужное количество аргументов и формируем результат
		return parent::getLocaleResult();
	}
}