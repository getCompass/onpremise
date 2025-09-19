<?php

namespace Compass\FileBalancer;

/**
 * класс описывающий событие event.file.status_updated версии 1
 */
class Gateway_Bus_Sender_Event_FileStatusUpdated_V1 extends Gateway_Bus_Sender_Event_Abstract
{
	/** @var int название метода */
	protected const _WS_EVENT = "event.file.status_updated";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"file" => \Entity_Validator_Structure::TYPE_OBJECT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(array $prepared_file_row): Struct_Sender_Event
	{

		return self::_buildEvent([
			"file" => (object) Apiv2_Format::file($prepared_file_row)
		]);
	}
}
