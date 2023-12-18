<?php

namespace Compass\FileNode;

/**
 * Сценарии для сокет методов
 */
class Domain_File_Scenario_Socket {

	/**
	 * заменяем превью у видео
	 *
	 * @param string $video_file_key
	 * @param string $replace_preview_file_key
	 *
	 * @throws Domain_File_Exception_FileNotFound
	 */
	public static function replacePreviewForWelcomeVideo(string $video_file_key, string $replace_preview_file_key):void {

		// заменяем превью у видео
		Domain_File_Action_ReplaceVideoPreview::do($video_file_key, $replace_preview_file_key);
	}
}
