<?php

namespace Compass\Pivot;

/**
 * класс для загрузки аватара пользователя нестандартным способом
 * @package Compass\Pivot
 */
class Domain_User_Action_Avatar_Upload {

	/**
	 * загружаем аватар имея его содержимое закордированное в base64
	 *
	 * @return string
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \cs_CurlError
	 */
	public static function uploadFileBase64Encoded(string $file_name, string $mime_type, string $base64_encoded_file_content):string {

		// получаем файловую ноду, куда осуществим загрузку
		$file_source = FILE_SOURCE_AVATAR;
		$node_url = Gateway_Socket_PivotFileBalancer::getNodeForUpload($file_source);

		// загружаем
		return Gateway_Socket_FileNode::uploadFileBase64Avatar($node_url, $base64_encoded_file_content,  $mime_type,  $file_name,  $file_source);
	}

	/**
	 * загружаем аватар по его прямой ссылке
	 *
	 * @return string
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \cs_CurlError
	 */
	public static function uploadFileUrl(string $file_name, string $file_url):string {

		// получаем файловую ноду, куда осуществим загрузку
		$file_source = FILE_SOURCE_AVATAR;
		$node_url = Gateway_Socket_PivotFileBalancer::getNodeForUpload($file_source);

		return Gateway_Socket_FileNode::uploadFileByUrl($node_url, $file_url, $file_source, $file_name, 0, "");
	}
}