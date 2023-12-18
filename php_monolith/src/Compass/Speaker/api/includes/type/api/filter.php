<?php

namespace Compass\Speaker;

// класс для форматирования названия сущностей, строго по соглашениям
class Type_Api_Filter {

	protected const _MAX_REASON_LENGTH  = 256;   // максимальная длина reason
	protected const _MAX_NETWORK_LENGTH = 1024;  // максимальная длина network

	// фильтрует reason
	public static function sanitizeReason(string $reason):string {

		// обрезаем до макс длины
		$reason = mb_substr($reason, 0, self::_MAX_REASON_LENGTH);

		// убираем лишнее
		$reason = preg_replace("/[^\s,.\w\-\(\)\{\}]/uism", "", $reason);

		return $reason;
	}

	// фильтрует network
	public static function sanitizeNetwork(string $network):string {

		// обрезаем до макс длины
		$network = mb_substr($network, 0, self::_MAX_NETWORK_LENGTH);

		// убираем лишнее
		$network = preg_replace("/[^\"\:\s,.\w\-\(\)\{\}]/uism", "", $network);

		return $network;
	}
}