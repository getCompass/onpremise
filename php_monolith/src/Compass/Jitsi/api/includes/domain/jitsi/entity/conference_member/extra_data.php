<?php

namespace Compass\Jitsi;

/**
 * класс для работы со структурой хранимой в поле jitsi_data . conference_member_list . data
 */
class Domain_Jitsi_Entity_ConferenceMember_ExtraData {

	/** актуальная версия структуры */
	protected const _VERSION = 1;

	/** структура схем различных версий с их историей */
	protected const _SCHEMAS_BY_VERSION = [
		1 => [
			// пространство, через которое участник присоединился к конференции
			"joining_space_id"                           => 0,

			// была ли проинкременчена статистика по участию участника в конференции
			"was_conference_membership_rating_incremented" => 0,
		],
	];

	/**
	 * инициализируем
	 *
	 * @return string[]
	 */
	public static function init(int $joining_space_id):array {

		$data                     = self::_SCHEMAS_BY_VERSION[self::_VERSION];
		$data["version"]          = self::_VERSION;
		$data["joining_space_id"] = $joining_space_id;

		// по умолчанию оставляем так
		$data["was_conference_membership_rating_incremented"] = 0;

		return $data;
	}

	/**
	 * получаем пространство, через которое участник присоединился к конференции
	 *
	 * @return int
	 */
	public static function getJoiningSpaceId(array $data):int {

		$data = self::_actualizeData($data);
		return $data["joining_space_id"];
	}

	/**
	 * была ли проинкременчена статистика по участию в конференции
	 *
	 * @return bool
	 */
	public static function wasConferenceMembershipRatingIncremented(array $data):bool {

		$data = self::_actualizeData($data);
		return boolval($data["was_conference_membership_rating_incremented"]);
	}

	/**
	 * устанавливаем флаг была ли проинкременчена статистика по участию в конференции
	 *
	 * @return array
	 */
	public static function setConferenceMembershipRatingIncrementedFlag(array $data, bool $value):array {

		$data                                               = self::_actualizeData($data);
		$data["was_conference_membership_rating_incremented"] = intval($value);

		return $data;
	}

	/**
	 * актуализируем структуру
	 */
	protected static function _actualizeData(array $structure):array {

		// сравниваем версию пришедшей структуры с текущей
		if ($structure["version"] != self::_VERSION) {

			// сливаем текущую версию data и ту, что пришла
			$structure            = array_merge(self::_SCHEMAS_BY_VERSION[self::_VERSION], $structure);
			$structure["version"] = self::_VERSION;
		}

		return $structure;
	}
}