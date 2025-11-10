<?php

namespace Compass\FileNode;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\QueryFatalException;
use BaseFrame\Server\ServerProvider;
use BaseFrame\System\File;

/** Класс описывает действие для удаления просроченных файлов */
class Domain_File_Action_DeleteExpiredFiles {

	// сколько за раз берем из базы файлов
	protected const _GET_LIMIT = 1000;

	// запрещенные к удалению типы источников файла
	protected const _RESTRICTED_FILE_SOURCE_LIST = [
		FILE_SOURCE_AVATAR_CDN,
		FILE_SOURCE_VIDEO_CDN,
		FILE_SOURCE_DOCUMENT_CDN,
		FILE_SOURCE_AVATAR,
		FILE_SOURCE_AVATAR_DEFAULT,
		FILE_SOURCE_MESSAGE_PREVIEW_IMAGE,
	];

	/**
	 * Выполняем действие
	 *
	 * @param array $override_config
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_CompanyIsHibernated
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws QueryFatalException
	 * @throws ReturnFatalException
	 * @throws \cs_SocketRequestIsFailed
	 */
	public static function do(array $override_config = []):array {

		// на дефолтной ноде ничего не удаляем
		if (IS_READONLY) {
			return [];
		}

		$auto_deletion_config     = $override_config !== [] ? $override_config : getConfig("FILE_AUTO_DELETION");
		$is_auto_deletion_enabled = $auto_deletion_config["is_enabled"] ?? false;
		$file_ttl                 = $auto_deletion_config["file_ttl"] ?? 0;

		// удаление проводится только если настройка включена
		if (!$is_auto_deletion_enabled || $file_ttl < 1) {
			return [];
		}

		// получаем отметку last_access_at, раньше которой файлы нужно удалить
		$min_last_access_at = time() - $file_ttl * 60 * 60 * 24;

		$count     = self::_GET_LIMIT;
		$offset    = 0;
		$file_list = [];
		do {

			$chunk     = Gateway_Db_FileNode_File::getBeforeLastAccessAt($min_last_access_at, $count, $offset);
			$file_list = array_merge($file_list, $chunk);
			$offset    += $count;
		} while (count($chunk) === $count);

		// фильтруем список файлов для удаления, оставляем те, что можно удалить
		$need_delete_file_type_list = $auto_deletion_config["need_delete_file_type_list"];
		$need_delete_file_list      = self::_filterFileListForDelete($file_list, $need_delete_file_type_list);

		if (count($need_delete_file_list) < 1) {
			return [];
		}

		// теперь группируем файлы по company_id, чтобы понимать, куда отправлять сокет запрос
		[$pivot_delete_file_list, $company_delete_file_list] = self::_groupFileListForDelete($need_delete_file_list);

		$company_entrypoint_list = Gateway_Socket_Pivot::getEntrypointList(array_keys($company_delete_file_list));

		// удаляем файлы
		foreach ($company_delete_file_list as $company_id => $file_list) {
			self::_deleteFileList($file_list, $company_id, $company_entrypoint_list[$company_id]);
		}

		self::_deleteFileList($pivot_delete_file_list, 0, "");

		return $need_delete_file_list;
	}

	/**
	 * Фильтруем файлы, убирая из них те, что нельзя удалять
	 *
	 * @param array $file_list
	 * @param array $need_delete_file_type_list
	 *
	 * @return array
	 */
	protected static function _filterFileListForDelete(array $file_list, array $need_delete_file_type_list):array {

		// получаем из строковых значений типов числовые
		$need_delete_file_type_list = array_intersect(Type_File_Main::FILE_TYPE_NAME, $need_delete_file_type_list);
		$need_delete_file_type_list = array_keys($need_delete_file_type_list);

		foreach ($file_list as $index => $file) {

			// дефолтные файлы не трогаем
			if ($file["is_cdn"] === 1) {
				unset($file_list[$index]);
			}

			// удаляем только те файлы, что разрешили в конфиге
			if (!in_array($file["file_type"], $need_delete_file_type_list, true)) {
				unset($file_list[$index]);
			}

			// также под удаление не попадают аватарки
			// и еще дополнительно проверяем, что это не дефолтные файлы
			if (in_array($file["file_source"], self::_RESTRICTED_FILE_SOURCE_LIST, true)) {
				unset($file_list[$index]);
			}
		}

		return array_values($file_list);
	}

	/**
	 * Группируем файлы по company_id
	 *
	 * @param array $file_list
	 *
	 * @return array
	 */
	protected static function _groupFileListForDelete(array $file_list):array {

		$company_delete_file_list = [];
		$pivot_delete_file_list   = [];

		foreach ($file_list as $file) {

			$company_id = Type_File_Default_Extra::getCompanyId($file["extra"]);

			if ($company_id === 0) {

				$pivot_delete_file_list[] = $file;
				continue;
			}

			$company_delete_file_list[$company_id][] = $file;
		}

		return [$pivot_delete_file_list, $company_delete_file_list];
	}

	/**
	 * Удаляем список файлов
	 *
	 * @param array $file_list
	 * @param int   $company_id
	 * @param int   $company_entrypoint
	 *
	 * @return void
	 * @throws QueryFatalException
	 * @throws ReturnFatalException
	 */
	protected static function _deleteFileList(array $file_list, int $company_id, string $company_entrypoint):void {

		// отправляемся в file_balancer, чтобы пометить файлы удаленными
		// на этом моменте файлы станут недоступны на клиенте
		$file_key_list = array_column($file_list, "file_key");
		try {
			Gateway_Socket_FileBalancer::setFileListDeleted($file_key_list, $company_id, $company_entrypoint);
		} catch (Gateway_Socket_Exception_CompanyIsHibernated) {

			// если компания спит, то в следующий раз попытаемся удалить
			return;
		} catch (Gateway_Socket_Exception_CompanyIsNotServed) {
			// раз компания удалена, продолжаем удаление файлов
		}

		// помечаем файлы удаленными на файловой ноде
		$set = [
			"is_deleted" => 1,
		];

		Gateway_Db_FileNode_File::updateList($file_key_list, $set);

		// удаляем помеченные файлы с сервера
		foreach ($file_list as $file) {

			File::init(PATH_WWW, $file["part_path"])->delete();

			// для картинок и видео удаляем все их превью
			match ($file["file_type"]) {
				FILE_TYPE_IMAGE => self::_deleteImageData($file),
				FILE_TYPE_VIDEO => self::_deleteVideoData($file),
				default => "",
			};
		}
	}

	/**
	 * Удалить все размеры изображений
	 *
	 * @param array $file
	 *
	 * @return void
	 */
	protected static function _deleteImageData(array $file):void {

		foreach (Type_File_Image_Extra::getImageSizeListFromExtra($file["extra"]) as $size) {
			File::init(PATH_WWW, $size["part_path"])->delete();
		}
	}

	/**
	 * Удалить все размеры видео
	 *
	 * @param array $file
	 *
	 * @return void
	 */
	protected static function _deleteVideoData(array $file):void {

		foreach (Type_File_Video_Extra::getPreviewSizeList($file["extra"]) as $size) {
			File::init(PATH_WWW, $size["part_path"])->delete();
		}

		foreach (Type_File_Video_Extra::getVideoVersionList($file["extra"]) as $version) {
			File::init(PATH_WWW, $version["part_path"])->delete();
		}

		File::init(PATH_WWW, Type_File_Video_Extra::getPreviewOriginalPartPath($file["extra"]))->delete();
	}
}