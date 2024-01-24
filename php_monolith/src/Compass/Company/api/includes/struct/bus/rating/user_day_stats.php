<?php

declare(strict_types = 1);

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс-структура содержащая информацию о статистике пользователя за день
 */
class Struct_Bus_Rating_UserDayStats {

	public int   $user_id;
	public array $data;

	/**
	 * Struct_Bus_Rating_UserDayStats constructor.
	 */
	public function __construct(int $user_id, array $data) {

		$this->user_id = $user_id;
		$this->_setData($data);
	}

	##########################################################
	# region data structure
	##########################################################

	// текущая версия
	protected const _DATA_VERSION = 2;

	// структура схем различных версий с их историей
	protected const _DATA_SCHEMAS = [

		2 => [
			"general_count"              => 0,
			"conversation_message_count" => 0,
			"thread_message_count"       => 0,
			"file_count"                 => 0,
			"reaction_count"             => 0,
			"voice_count"                => 0,
			"call_count"                 => 0,
			"respect_count"              => 0,
			"exactingness_count"         => 0,
		],
	];

	/**
	 * актуализируем содержимое data
	 *
	 * @return array
	 *
	 * @throws \parseException
	 */
	protected function _setData(array $data):self {

		if (!isset($data["version"])) {
			throw new ParseFatalException("incorrect structure of data");
		}

		// сравниваем версию пришедшей data с текущей
		if ($data["version"] != self::_DATA_VERSION) {

			// если пришла версия выше чем здесь, то лучше свалиться – чтобы актуализировали и здесь
			if ($data["version"] > self::_DATA_VERSION) {
				throw new ParseFatalException("please, actualize data schema in class – " . __CLASS__);
			}

			// сливаем текущую версию data и ту, что пришла
			$data            = array_merge(self::_DATA_SCHEMAS[self::_DATA_VERSION], $data);
			$data["version"] = self::_DATA_VERSION;
		}

		$this->data = $data;

		return $this;
	}

	# endregion
	##########################################################

	##########################################################
	# region своего рода геттеры)
	##########################################################

	/**
	 * Получить general_count
	 */
	public function getGeneralCount():int {

		return $this->data["general_count"];
	}

	/**
	 * Получить conversation_message_count
	 */
	public function getConversationMessageCount():int {

		return $this->data["conversation_message_count"];
	}

	/**
	 * Получить thread_message_count
	 */
	public function getThreadMessageCount():int {

		return $this->data["thread_message_count"];
	}

	/**
	 * Получить file_count
	 */
	public function getFileCount():int {

		return $this->data["file_count"];
	}

	/**
	 * Получить reaction_count
	 */
	public function getReactionCount():int {

		return $this->data["reaction_count"];
	}

	/**
	 * Получить voice_count
	 */
	public function getVoiceCount():int {

		return $this->data["voice_count"];
	}

	/**
	 * Получить call_count
	 */
	public function getCallCount():int {

		return $this->data["call_count"];
	}

	/**
	 * Получить respect_count
	 */
	public function getRespectCount():int {

		return $this->data["respect_count"];
	}

	/**
	 * Получить exactingness_count
	 */
	public function getExactingnessCount():int {

		return $this->data["exactingness_count"];
	}

	# endregion
	##########################################################
}
