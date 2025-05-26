<?php

namespace AnalyticUtils\Domain\Event\Struct;

use AnalyticUtils\Domain\Event\Struct\User\ClientData;
use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс-структура пользовательского события
 */
class User implements \JsonSerializable {

	protected const _DATA_VERSION = 1;

	public int    $type;
	public string $status;
	public int    $event_time;
	public array  $data;

	/**
	 * Конструктор класса
	 *
	 * @param int        $type
	 * @param string     $status
	 * @param int        $user_id
	 * @param ClientData $app_data
	 * @param int        $company_id
	 *
	 * @throws ParseFatalException
	 */
	public function __construct(int $type, string $status, int $user_id, ClientData $app_data, int $company_id = 0) {

		if (!isset(\AnalyticUtils\Domain\Event\Entity\User::EVENT_SETTINGS_LIST[$type])) {
			throw new ParseFatalException("unknown user event");
		}

		$this->type       = $type;
		$this->status     = $status;
		$this->event_time = time();
		$this->data       = [];

		// добавляем доп информацию
		$this->_setData($user_id, $app_data, $company_id);
	}

	/**
	 * Сериализируем в JSON
	 *
	 * @return array
	 */
	public function jsonSerialize():array {

		return [
			"type"       => $this->type,
			"status"     => $this->status,
			"event_time" => $this->event_time,
			"data"       => [
				"user_id"    => $this->data["user_id"],
				"company_id" => $this->data["company_id"],
				"app_data"   => [
					"user_agent"  => $this->data["app_data"]->user_agent,
					"platform"    => $this->data["app_data"]->platform,
					"app_version" => $this->data["app_data"]->app_version,
				],
			],
		];
	}

	/**
	 * Добавить доп информацию о событии
	 *
	 * @param int        $user_id
	 * @param ClientData $app_data
	 * @param int        $company_id
	 *
	 * @return void
	 */
	private function _setData(int $user_id, ClientData $app_data, int $company_id):void {

		$this->data            = [];
		$this->data["version"] = self::_DATA_VERSION;

		$this->data["user_id"]    = $user_id;
		$this->data["company_id"] = $company_id;
		$this->data["app_data"]   = $app_data;
	}
}