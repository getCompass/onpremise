<?php

namespace Compass\FileNode;

/**
 * Класс обертка для cpp_extension_file.
 */
class Type_Extension_File {

	// функция для получения картинки первого кадра из видео
	// кадр из видео по пути $file_path сохранится по пути $preview_screen_file_path в формате jpg
	public static function tryGetVideoScreen(string $file_path, string $preview_screen_file_path, bool $is_hdr):bool {

		// возвращаем true в случае успешного скрина видео
		if ($is_hdr) {

			// раскрашиваем hdr видео
			return ffmpeg_exec("-i", $file_path, "-filter_complex", "zscale=t=linear:npl=300,format=gbrpf32le,tonemap=tonemap=mobius,zscale=t=bt709:p=bt709:m=bt709:d=error_diffusion,format=yuv420p", "-frames:v", "1", $preview_screen_file_path);
		}

		// нарезаем как обычно
		return ffmpeg_exec("-i", $file_path, "-frames:v", "1", $preview_screen_file_path);
	}

	/**
	 * Переместить метаданные в начало
	 * @param string $file_path
	 * @return void
	 */
	public static function moveMetadataToStart(string $file_path):void {

		$dir_path      = dirname($file_path);
		$file_name     = basename($file_path);
		$tmp_file_path = "$dir_path/tmp.$file_name";

		$is_encoded = ffmpeg_exec("-y",
			"-i", $file_path,                                        // входной файл
			"-threads", VIDEO_PROCESS_THREAD_COUNT,                  // количество потоков (обычно равно количеству ядер процессора
			"-c", "copy",                                            // параметры для аудио кодека пока не устанавливаем
			"-movflags", "+faststart",                               // перемещаем флаг moov_atom в начало
			$tmp_file_path                                           // выходное видео
		);

		// если не получилось переместить
		if (!$is_encoded) {

			// удаляем выходной файл, ffmpeg мог создать болванку для записи
			unlink($tmp_file_path);
			return;
		}

		// заменяем оригинальное видео новым
		// [from https://www.php.net/manual/en/function.rename.php]
		// If renaming a file and to exists, it will be overwritten
		rename($tmp_file_path, $file_path);

		return;
	}

	// ресайзит видео в нужный размер
	public static function doVideoResize(string $file_path, string $output_file_path, int $width, int $height, bool $is_hdr):bool {

		if ($is_hdr) {

			return ffmpeg_exec("-y",
				"-i", $file_path,                                        // входной файл
				"-threads", VIDEO_PROCESS_THREAD_COUNT,                  // количество потоков (обычно равно количеству ядер процессора
				"-vf", "scale={$width}:{$height},fps=30,zscale=t=linear:npl=300,format=gbrpf32le,tonemap=tonemap=mobius,zscale=t=bt709:p=bt709:m=bt709:d=error_diffusion,format=yuv420p", // скейлим видео и доводим количество кадров до 30
				"-c:v", "libx264", "-crf", "21", "-preset", "veryfast",  // параметры для видео кодека, пока оставил h264 возможно в будущем перейдем на webm
				// "-c:a", "copy",                                       // параметры для аудио кодека пока не устанавливаем
				"-movflags", "+faststart",                               // перемещаем флаг moov_atom в начало
				$output_file_path                                        // выходное видео
			);
		}

		return ffmpeg_exec("-y",
			"-i", $file_path,                                        // входной файл
			"-threads", VIDEO_PROCESS_THREAD_COUNT,                  // количество потоков (обычно равно количеству ядер процессора
			"-vf", "scale={$width}:{$height},fps=30", // скейлим видео и доводим количество кадров до 30
			"-c:v", "libx264", "-crf", "21", "-preset", "veryfast",  // параметры для видео кодека, пока оставил h264 возможно в будущем перейдем на webm
			// "-c:a", "copy",                                       // параметры для аудио кодека пока не устанавливаем
			"-movflags", "+faststart",                               // перемещаем флаг moov_atom в начало
			$output_file_path                                        // выходное видео
		);
	}

	// функция для получения: width, height, duration, codec_name
	// о видео по пути $file_path
	public static function getVideoInfo(string $file_path):array {

		// возвращает информацию о видео в формате:
		$video_info = video_get_info($file_path);
		if ($video_info === false) {

			return [
				"width"           => 0,
				"height"          => 0,
				"duration"        => 0,
				"codec_name"      => "",
				"color_range"     => 0,
				"color_primaries" => 0,
				"color_trc"       => 0,
				"color_space"     => 0,
			];
		}

		return [
			"height"          => isset($video_info[0]) ? $video_info[0] : 0,
			"width"           => isset($video_info[1]) ? $video_info[1] : 0,
			"duration"        => isset($video_info[2]) ? $video_info[2] : 0,
			"codec_name"      => isset($video_info[3]) ? $video_info[3] : "",
			"color_range"     => isset($video_info[4]) ? $video_info[4] : 0,
			"color_primaries" => isset($video_info[5]) ? $video_info[5] : 0,
			"color_trc"       => isset($video_info[6]) ? $video_info[6] : 0,
			"color_space"     => isset($video_info[7]) ? $video_info[7] : 0,
		];
	}

	// функция для получения длины аудио в секундах
	public static function getAudioDuration(string $file_path):int {

		// возвращает длину аудио файла в микросекундах
		$duration = audio_get_duration($file_path);

		// если не может открыть файл или аудио поток не найден
		if ($duration === false) {
			$duration = 0;
		}

		return intval($duration / 1000000);
	}

	// функция для получения waveform из аудио и длительности аудио
	// возвращает одномерный массив значений до 100 элементов
	public static function getVoiceInfo(string $file_path):array {

		// получаем информацию о звуковом файле
		$output = voice_get_info($file_path);

		// если не удалось сформировать waveform
		if ($output === false) {

			$output = [
				"waveform" => [],
				"duration" => 0,
			];
		}

		return $output;
	}
}