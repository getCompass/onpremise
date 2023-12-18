<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для логирования посещения ссылок кроном парсером
 */
class Type_Logs_Cron_Parser {

	public const LOG_STATUS_SERVER_RESPONSE_ERROR = 1; // сервер вернул ошибку
	public const LOG_STATUS_IS_NOT_ALLOW_TO_PARSE = 2; // ссылку нельзя парсить
	public const LOG_STATUS_MAX_REDIRECT_GAINED   = 3; // получено максимальное количество редиректов
	public const LOG_STATUS_PARSE_ERROR           = 4; // ошибка парсинга
	public const LOG_STATUS_SUCCESS               = 5; // успех
	public const LOG_STATUS_INVALID_URL           = 6; // не валидный url

	protected const _DB_KEY    = "company_temp"; // ключ базы логов в sharding
	protected const _TABLE_KEY = "preview_parser_log";  // название таблицы с логом

	// -------------------------------------------------------
	// параметры, которые хранит в себе класс
	// -------------------------------------------------------

	protected int    $_user_id;                // идентификатор отправителя ссылки
	protected float  $_start_time;             // время создания лога
	protected string $_original_link;          // оригинальная ссылка, передана в сообщении
	protected string $_html       = "";        // html-код страницы
	protected array  $_body       = [];        // тело лога
	protected int    $_log_status = 0;      // статус задачи
	protected int    $_count      = 0;         // счетчик редиректов

	// инициализируем лог
	public function __construct(int $user_id, string $original_link) {

		$this->_user_id       = $user_id;
		$this->_start_time    = microtime(true);
		$this->_original_link = $original_link;
	}

	// -------------------------------------------------------
	// log
	// -------------------------------------------------------

	// добавить причину ошибки в лог
	public function addReason(string $error_reason, int $http_code = 0):self {

		$this->_body["message"]        = $error_reason;
		$this->_body["last_http_code"] = $http_code;

		return $this;
	}

	// устанавливаем статус парсинга
	public function setStatus(int $status):self {

		$this->_log_status = $status;

		return $this;
	}

	// создать запись в логе
	public function save():void {

		// проверяем что в ходе исполнения был выставлен статус
		if (is_null($this->_log_status)) {
			throw new ParseFatalException("Task status was not specified");
		}

		// создаем запись в логе
		ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_KEY, [
			"user_id"       => $this->_user_id,
			"status"        => $this->_log_status,
			"count"         => $this->_count,
			"created_at"    => time(),
			"total_time"    => round((microtime(true) - $this->_start_time) * 1000),
			"original_link" => $this->_original_link,
			"html"          => $this->_html,
			"body"          => $this->_body,
		]);
	}
}