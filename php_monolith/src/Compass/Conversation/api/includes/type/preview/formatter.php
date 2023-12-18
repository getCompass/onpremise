<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для формирования UrlPreview
 */
class Type_Preview_Formatter {

	public const PREVIEW_TYPE_VIDEO_YOUTUBE = 1;

	// -------------------------------------------------------
	// методы работы со структурой url_preview
	// -------------------------------------------------------

	// функция собирает общую структуру, которая затем передается в Apiv1_Format
	// @long - switch
	public static function prepareForFormat(array $url_preview, string $preview_map):array {

		// формируем массив
		$output = self::_makeOutput($url_preview, $preview_map);

		// добавляем favicon
		$output["data"]["favicon"]["file_map"] = $url_preview["data"]["favicon_file_map"] ?? "";

		// по типу превью
		return match ($url_preview["data"]["type"]) {
			PREVIEW_TYPE_SITE                                  => self::_prepareSiteDataForFormat($output, $url_preview),
			PREVIEW_TYPE_IMAGE                                 => self::_prepareImageDataForFormat($output, $url_preview),
			PREVIEW_TYPE_PROFILE                               => self::_prepareProfileDataForFormat($output, $url_preview),
			PREVIEW_TYPE_CONTENT                               => self::_prepareContentDataForFormat($output, $url_preview),
			PREVIEW_TYPE_RESOURCE, PREVIEW_TYPE_COMPASS_INVITE => self::_prepareResourceDataForFormat($output, $url_preview),
			PREVIEW_TYPE_VIDEO                                 => self::_prepareVideoDataForFormat($output, $url_preview),
			PREVIEW_TYPE_SIMPLE					   => $output,
			default                                            => throw new ParseFatalException(__CLASS__ . ": unsupported preview type"),
		};
	}

	// формируем массив для отдачи на frontend
	protected static function _makeOutput(array $url_preview, string $preview_map):array {

		$output = [
			"preview_map" => $preview_map,
			"type"        => $url_preview["data"]["type"],
			"url"         => $url_preview["data"]["url"],
			"short_url"   => $url_preview["data"]["short_url"],
			"data"        => [],
			"site_name"   => "",
		];

		if (isset($url_preview["data"]["site_name"])) {
			$output["site_name"] = $url_preview["data"]["site_name"];
		}

		return $output;
	}

	// приводим к формату превью типа site
	protected static function _prepareSiteDataForFormat(array $output, array $url_preview):array {

		$output["data"]["title"]       = $url_preview["data"]["title"];
		$output["data"]["description"] = $url_preview["data"]["description"] ?? "";

		// если имеется file_map, то добавляем его и размеры изображения в ответ
		$output = self::_addPreviewImageIfExist($output, $url_preview);

		return $output;
	}

	// приводим к формату превью типа image
	protected static function _prepareImageDataForFormat(array $output, array $url_preview):array {

		// если имеется file_map, то добавляем его и размеры изображения в ответ
		$output = self::_addPreviewImageIfExist($output, $url_preview);

		return $output;
	}

	// приводим к формату превью типа profile
	protected static function _prepareProfileDataForFormat(array $output, array $url_preview):array {

		$output["data"]["title"]       = $url_preview["data"]["title"];
		$output["data"]["description"] = $url_preview["data"]["description"] ?? "";

		// если имеется file_map, то добавляем его и размеры изображения в ответ
		$output = self::_addPreviewImageIfExist($output, $url_preview);

		return $output;
	}

	// приводим к формату превью типа content
	protected static function _prepareContentDataForFormat(array $output, array $url_preview):array {

		$output["data"]["title"]       = $url_preview["data"]["title"];
		$output["data"]["description"] = $url_preview["data"]["description"] ?? "";

		// если имеется file_map, то добавляем его и размеры изображения в ответ
		$output = self::_addPreviewImageIfExist($output, $url_preview);

		return $output;
	}

	// приводим к формату превью типа resource
	protected static function _prepareResourceDataForFormat(array $output, array $url_preview):array {

		$output["data"]["title"]       = $url_preview["data"]["title"];
		$output["data"]["description"] = $url_preview["data"]["description"] ?? "";

		// если имеется file_map, то добавляем его и размеры изображения в ответ
		$output = self::_addPreviewImageIfExist($output, $url_preview);

		return $output;
	}

	// приводим к формату превью типа video
	protected static function _prepareVideoDataForFormat(array $output, array $url_preview):array {

		$output["data"]["title"]       = $url_preview["data"]["title"];
		$output["data"]["description"] = $url_preview["data"]["description"] ?? "";

		// если имеется file_map, то добавляем его и размеры изображения в ответ
		$output = self::_addPreviewImageIfExist($output, $url_preview);

		switch ($url_preview["data"]["subtype"]) {

			case self::PREVIEW_TYPE_VIDEO_YOUTUBE:

				$output["data"]["subtype"]                   = $url_preview["data"]["subtype"];
				$output["data"]["extra"]["video_embed_url"]  = $url_preview["data"]["extra"]["video_embed_url"];
				$output["data"]["extra"]["youtube_video_id"] = $url_preview["data"]["extra"]["youtube_video_id"];
				break;

			default:
				throw new ReturnFatalException("Unknown video type");
		}

		return $output;
	}

	// добавляем картинку если существует
	protected static function _addPreviewImageIfExist(array $output, array $url_preview):array {

		$output["data"]["preview_image"]["file_map"] = $url_preview["data"]["image_file_map"] ?? "";

		// если имеется file_map, то добавляем размеры изображения в ответ
		if (isset($url_preview["data"]["image_file_map"]) && mb_strlen($url_preview["data"]["image_file_map"]) > 0) {

			// достаем размеры оригинального изображения
			$width  = \CompassApp\Pack\File::getImageWidth($url_preview["data"]["image_file_map"]);
			$height = \CompassApp\Pack\File::getImageHeight($url_preview["data"]["image_file_map"]);

			// если размеры переданы, то устанавливаем их в data
			if ($width + $height > 0) {

				$output["data"]["preview_image"]["file_width"]  = $width;
				$output["data"]["preview_image"]["file_height"] = $height;
			}
		}

		return $output;
	}

	// -------------------------------------------------------
	// методы для работы со структурой поля data
	// -------------------------------------------------------

	// версия упаковщика
	protected const _CURRENT_DATA_PACK_VERSION = 2;

	// функция собирает структуру preview для последующего хранения в зависимости от передаваемого type
	public static function prepareDataForStorageByType(int $type, string $url, string $domain, string $site_name, string $title, string $favicon_file_map, string $image_file_map, string $description):array {

		$output = [
			"type"             => $type,
			"url"              => $url,
			"short_url"        => $domain,
			"site_name"        => $site_name,
			"title"            => $title,
			"favicon_file_map" => $favicon_file_map,
			"version"          => self::_CURRENT_DATA_PACK_VERSION,
		];

		if (mb_strlen($image_file_map) > 1) {
			$output["image_file_map"] = $image_file_map;
		}
		if (mb_strlen($description) > 1) {
			$output["description"] = $description;
		}

		return $output;
	}

	// создать превью типа "image"
	public static function makeImageData(string $url, string $short_url, string $site_name, string $image_file_map, string $favicon_file_map):array {

		return [
			"type"             => PREVIEW_TYPE_IMAGE,
			"url"              => $url,
			"short_url"        => $short_url,
			"site_name"        => $site_name,
			"image_file_map"   => $image_file_map,
			"favicon_file_map" => $favicon_file_map,
			"version"          => self::_CURRENT_DATA_PACK_VERSION,
		];
	}
}
