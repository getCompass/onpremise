<?php

namespace Compass\Speaker;

// класс для фильтрации одинаковых сущностей внутри модулей общий для всех
class Type_Api_Validator {

	// проверяем на корректность audio/video
	public static function isCorrectAudioAndVideo(int $audio, int $video):bool {

		if (!in_array($audio, [0, 1]) || !in_array($video, [0, 1])) {
			return false;
		}

		return true;
	}

	// проверяем SDP пакет типа Offer
	public static function isOffer(array $offer):bool {

		if (isset($offer["type"], $offer["sdp"]) && $offer["type"] == "offer") {
			return true;
		}

		return false;
	}

	// проверяем данные ice-candidate, который отправил один участник звонка другому
	public static function isCandidate(array $candidate):bool {

		if (isset($candidate["candidate"])) {
			return true;
		}

		return false;
	}
}
