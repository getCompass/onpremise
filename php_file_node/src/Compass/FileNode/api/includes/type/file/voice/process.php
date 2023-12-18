<?php

namespace Compass\FileNode;

/**
 * Класс для работы с голосовыми сообщениями (любая первичная и последующая обработка)
 */
class Type_File_Voice_Process {

	// делает первичную обработку голосового, возвращает extra
	public static function doProcessOnUpload(string $part_path, int $user_id, int $company_id, string $company_url):array {

		// получаем file_path
		$file_path = Type_File_Utils::getFilePathFromPartPath($part_path);

		// получаем информацию о голосовом сообщении
		$result = Type_Extension_File::getVoiceInfo($file_path);

		// получаем длительность голосовые сообщения в миллисекундах
		$duration_ms = round($result["duration"] * 1000);

		return Type_File_Voice_Extra::getExtra($part_path, $duration_ms, $result["waveform"], $user_id, $company_id, $company_url);
	}
}