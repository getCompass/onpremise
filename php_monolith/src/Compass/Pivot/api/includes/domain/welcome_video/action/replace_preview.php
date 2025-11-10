<?php

namespace Compass\Pivot;

/**
 * действие замены превью у видео-онбординга
 */
class Domain_WelcomeVideo_Action_ReplacePreview {

	// массив ключей заменяемых превью
	protected const _WELCOME_VIDEO_REPLACE_PREVIEW_LIST = [
		"general_conversation_welcome_video_desktop_ru" => "replace_preview_general_conversation_welcome_video_desktop_ru",
		"general_conversation_welcome_video_desktop_de" => "replace_preview_general_conversation_welcome_video_desktop_de",
		"general_conversation_welcome_video_desktop_en" => "replace_preview_general_conversation_welcome_video_desktop_en",
		"general_conversation_welcome_video_desktop_es" => "replace_preview_general_conversation_welcome_video_desktop_es",
		"general_conversation_welcome_video_desktop_fr" => "replace_preview_general_conversation_welcome_video_desktop_fr",
		"general_conversation_welcome_video_desktop_it" => "replace_preview_general_conversation_welcome_video_desktop_it",
		"general_conversation_welcome_video_mobile_ru"  => "replace_preview_general_conversation_welcome_video_mobile_ru",
		"general_conversation_welcome_video_mobile_de"  => "replace_preview_general_conversation_welcome_video_mobile_de",
		"general_conversation_welcome_video_mobile_en"  => "replace_preview_general_conversation_welcome_video_mobile_en",
		"general_conversation_welcome_video_mobile_es"  => "replace_preview_general_conversation_welcome_video_mobile_es",
		"general_conversation_welcome_video_mobile_fr"  => "replace_preview_general_conversation_welcome_video_mobile_fr",
		"general_conversation_welcome_video_mobile_it"  => "replace_preview_general_conversation_welcome_video_mobile_it",
		"threads_welcome_video_mobile_ru"               => "replace_preview_threads_welcome_video_mobile_ru",
		"threads_welcome_video_mobile_de"               => "replace_preview_threads_welcome_video_mobile_de",
		"threads_welcome_video_mobile_en"               => "replace_preview_threads_welcome_video_mobile_en",
		"threads_welcome_video_mobile_es"               => "replace_preview_threads_welcome_video_mobile_es",
		"threads_welcome_video_mobile_fr"               => "replace_preview_threads_welcome_video_mobile_fr",
		"threads_welcome_video_mobile_it"               => "replace_preview_threads_welcome_video_mobile_it",
		"threads_welcome_video_desktop_ru"              => "replace_preview_threads_welcome_video_desktop_ru",
		"threads_welcome_video_desktop_de"              => "replace_preview_threads_welcome_video_desktop_de",
		"threads_welcome_video_desktop_en"              => "replace_preview_threads_welcome_video_desktop_en",
		"threads_welcome_video_desktop_es"              => "replace_preview_threads_welcome_video_desktop_es",
		"threads_welcome_video_desktop_fr"              => "replace_preview_threads_welcome_video_desktop_fr",
		"threads_welcome_video_desktop_it"              => "replace_preview_threads_welcome_video_desktop_it",
	];

	/**
	 * выполняем
	 */
	public static function do():void {

		foreach (self::_WELCOME_VIDEO_REPLACE_PREVIEW_LIST as $welcome_video_dictionary_key => $replace_preview_dictionary_key) {

			// достаём данные видео-онбордингу
			$welcome_video = Gateway_Db_PivotSystem_DefaultFileList::get($welcome_video_dictionary_key);

			// достаём данные по заменяемому превью
			$replace_preview = Gateway_Db_PivotSystem_DefaultFileList::get($replace_preview_dictionary_key);

			// проверяем было ли уже заменено превью, если да - пропускаем
			$replace_file_hash = Type_File_Default_Extra::getReplaceFileHash($welcome_video->extra);
			if ($replace_file_hash == $replace_preview->file_hash) {
				continue;
			}

			// отправляем замену на файловую ноду
			$node_url = Gateway_Socket_PivotFileBalancer::getNodeForUpload(FILE_SOURCE_VIDEO_CDN);
			Gateway_Socket_FileNode::replacePreviewForWelcomeVideo($node_url, $welcome_video->file_key, $replace_preview->file_key);

			// записываем file_hash замененного файла превью к видео-онбордингу
			$extra = Type_File_Default_Extra::setReplaceFileHash($welcome_video->extra, $replace_preview->file_hash);
			$set   = [
				"extra" => $extra,
			];
			Gateway_Db_PivotSystem_DefaultFileList::update($welcome_video->dictionary_key, $set);
		}
	}
}