<?php

namespace Compass\FileNode;

/**
 * Класс для обработки картинок
 */
class Type_File_Image_Process {

	// массив с шириной картинок для первичной обработки
	public const _UPLOAD_IMAGE_SIZE_LIST = [
		1280,
		720,
		400,
		180,
		80,
	];

	// массив с шириной картинок для постобработки
	protected const _POST_UPLOAD_IMAGE_SIZE_LIST = [
		1600,
		1280,
		1080,
		720,
		400,
		300,
		180,
		80,
	];

	// общий массив всех exif полей
	protected const _EXIF_FIELD_LIST = [
		"bands", "format", "coding", "interpretation", "xoffset", "yoffsetset", "xres", "yres", "filename", "vips-loader", "jpeg-multiscan", "exif-data", "resolution-unit", "exif-ifd0-Make", "exif-ifd0-Model", "exif-ifd0-Orientation", "exif-ifd0-XResolution", "exif-ifd0-YResolution", "exif-ifd0-ResolutionUnit", "exif-ifd0-DateTime", "exif-ifd0-Artist", "exif-ifd0-YCbCrPositioning", "exif-ifd0-Copyright", "exif-ifd1-Compression", "exif-ifd1-XResolution", "exif-ifd1-YResolution", "exif-ifd1-ResolutionUnit", "exif-ifd2-ExposureTime", "exif-ifd2-FNumber", "exif-ifd2-ExposureProgram", "exif-ifd2-ISOSpeedRatings", "exif-ifd2-ExifVersion", "exif-ifd2-DateTimeOriginal", "exif-ifd2-DateTimeDigitized", "exif-ifd2-ComponentsConfiguration", "exif-ifd2-ShutterSpeedValue", "exif-ifd2-ApertureValue", "exif-ifd2-ExposureBiasValue", "exif-ifd2-MeteringMode", "exif-ifd2-Flash", "exif-ifd2-FocalLength", "exif-ifd2-MakerNote", "exif-ifd2-UserComment", "exif-ifd2-SubsecTime", "exif-ifd2-SubSecTimeOriginal", "exif-ifd2-SubSecTimeDigitized", "exif-ifd2-FlashPixVersion", "exif-ifd2-ColorSpace", "exif-ifd2-PixelXDimension", "exif-ifd2-PixelYDimension", "exif-ifd2-FocalPlaneXResolution", "exif-ifd2-FocalPlaneYResolution", "exif-ifd2-FocalPlaneResolutionUnit", "exif-ifd2-CustomRendered", "exif-ifd2-ExposureMode", "exif-ifd2-WhiteBalance", "exif-ifd2-SceneCaptureType", "exif-ifd3-GPSVersionID", "exif-ifd4-InteroperabilityIndex", "exif-ifd4-InteroperabilityVersion", "jpeg-thumbnail-data", "orientation", "xmp-data", "icc-profile-data",
	];

	// массив exif полей которые следует оставлять
	protected const _ALLOW_EXIF_FIELD_LIST = [
		"width",
		"height",
		"icc-profile-data",
	];

	// делает первичную обработку картинки, возвращает extra
	public static function doProcessOnUpload(string $part_path, int $company_id, string $company_url, string $parent_file_key = ""):array {

		return self::_doProcess(self::_UPLOAD_IMAGE_SIZE_LIST, $part_path, $company_id, $company_url, true, $parent_file_key);
	}

	// делает полную обработку картинки, возвращает extra
	public static function doPostProcess(string $part_path, int $company_id, string $company_url, array $extra):array {

		return self::_doProcess(self::_POST_UPLOAD_IMAGE_SIZE_LIST, $part_path, $company_id, $company_url, false, "", $extra);
	}

	// делает полную обработку картинки, возвращает extra
	public static function doProcessVideoScreen(string $part_path, int $company_id, string $company_url):array {

		return self::_doProcess(self::_UPLOAD_IMAGE_SIZE_LIST, $part_path, $company_id, $company_url, true);
	}

	// метод для кропа картинки
	public static function doCropImage(string $buffer, int $x_offset, int $y_offset, int $width, int $height, string $output_file_path):string {

		// получаем полный путь к картинке в файловой системе
		$image = vips_image_new_from_buffer($buffer)["out"];

		// кропаем изображение
		$cropped_image = self::_doCropImage($image, $x_offset, $y_offset, $width, $height);
		if ($cropped_image === false) {

			throw new \ParseException("File with file_part = {$output_file_path} could not be cropped");
		}

		// сохраняем кропнутое
		$output_file_path = self::_doSaveImage($output_file_path, $cropped_image);
		if (mb_strlen($output_file_path) < 1) {

			throw new \ParseException("File with file_part = {$output_file_path} could not be save after cropped");
		}

		unset($image); // очищаем ресурс так как он нам больше не нужен

		return $output_file_path;
	}

	/**
	 * Метод для кропа картинки
	 *
	 * @param mixed $image
	 * @param int   $x
	 * @param int   $y
	 * @param int   $width
	 * @param int   $height
	 *
	 * @return false|mixed
	 */
	protected static function _doCropImage(mixed $image, int $x, int $y, int $width, int $height):mixed {

		// нарезаем изображение
		$resized_image = vips_call("crop", $image, $x, $y, $width, $height);
		if ($resized_image == -1) {
			return false;
		}

		return $resized_image["out"];
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Делает обработку картинку в зависимости от переданных параметров
	 *
	 * @param array  $size_list
	 * @param string $part_path
	 * @param int    $company_id
	 * @param string $company_url
	 * @param bool   $process_orig
	 * @param string $parent_file_key
	 * @param array  $extra
	 *
	 * @return array
	 * @throws cs_FileProcessFailed
	 * @long большое количество параметров
	 */
	protected static function _doProcess(
		array  $size_list,
		string $part_path,
		int    $company_id,
		string $company_url,
		bool   $process_orig,
		string $parent_file_key = "",
		array  $extra = []):array {

		// получаем полный путь к картинке в файловой системе и mime_type
		$file_path = Type_File_Utils::getFilePathFromPartPath($part_path);
		$mime_type = Type_File_Utils::getMimeType($file_path);

		$vips_output = match ($mime_type) {
			"image/png" => vips_image_new_from_file($file_path, ["access" => "sequential-unbuffered"]),
			default => vips_image_new_from_file($file_path),
		};

		// иногда оно может вернуть не массив, поэтому проверяем, прежде чем дергать
		if (!is_array($vips_output) || !isset($vips_output["out"])) {
			throw new cs_FileProcessFailed();
		}

		$image = $vips_output["out"];
		if ($image === null) {
			throw new cs_FileProcessFailed();
		}

		if ($process_orig) {
			$image = self::_doOriginalImageProcess($file_path, $image);
		}

		// получаем ширину, высоту и размер оригинального изображения
		$width            = vips_image_get($image, "width")["out"];
		$height           = vips_image_get($image, "height")["out"];
		$original_size_kb = Type_File_Utils::getFileSizeKb($file_path);

		// получаем список нарезанных изображений
		$image_size_list = Type_File_Image_Extra::getImageSizeListFromExtra($extra);
		$image_size_list = self::_getImageSizeList($size_list, $width, $part_path, $image, $image_size_list);

		// очищаем ресурс так как он нам больше не нужен
		unset($image);

		// формируем extra
		$original_image_item = self::_formatImageSizeItem($part_path, $width, $height, $original_size_kb);
		return Type_File_Image_Extra::getExtra($part_path, $company_id, $company_url, $original_image_item, $image_size_list, $parent_file_key, $extra);
	}

	// метод для обработки оригинального файла
	// @mixed - $image является ресурсом
	// в ответе также возвращается изображение - ресурс
	protected static function _doOriginalImageProcess(string $file_path, mixed $image):mixed {

		// выставляем поворот изображения
		$image = vips_call("autorot", $image)["out"];
		if ($image === null) {
			throw new cs_FileProcessFailed();
		}

		// удаляем лишние exif поля
		foreach (self::_EXIF_FIELD_LIST as $v) {

			if (!in_array($v, self::_ALLOW_EXIF_FIELD_LIST)) {

				if (vips_image_get_typeof($image, $v) != -1) {
					vips_image_remove($image, $v);
				}
			}
		}

		// копируем в память и перезаписываем оригинал
		$temp = vips_image_copy_memory($image)["out"];
		self::_doSaveImage($file_path, $temp);

		return $temp;
	}

	// получаем список нарезанных изображений
	// @mixed - image это ресурс
	protected static function _getImageSizeList(array $size_list, int $width, string $part_path, mixed $image, array $image_size_list = []):array {

		foreach ($size_list as $new_width) {

			if ($new_width >= $width || self::_isExistInImageSizeList($image_size_list, $new_width)) {
				continue;
			}

			$new_postfix      = "w{$new_width}";
			$output_file_path = self::_getNewPathFile($part_path, $new_postfix);
			if (file_exists($output_file_path)) {
				continue;
			}

			// ресайзим и сохраняем изображение
			$image_size_item = self::_doResizeAndSaveImage($output_file_path, $new_width, $width, $image);
			if (count($image_size_item) < 1) {
				continue;
			}

			$image_size_list[] = $image_size_item;
		}

		return $image_size_list;
	}

	// если изображение существует в списке
	protected static function _isExistInImageSizeList(array $image_size_list, int $width):bool {

		foreach ($image_size_list as $v) {

			if ($width == $v["width"]) {
				return true;
			}
		}

		return false;
	}

	// получаем путь для нового файла
	protected static function _getNewPathFile(string $part_path, string $postfix):string {

		$dir       = pathinfo($part_path, PATHINFO_DIRNAME);
		$filename  = pathinfo($part_path, PATHINFO_FILENAME);
		$extension = pathinfo($part_path, PATHINFO_EXTENSION);

		return Type_File_Utils::getFilePathFromPartPath("{$dir}/{$filename}_{$postfix}.{$extension}");
	}

	// функция для ресайза и сохранения файла, отдает size_item
	// @mixed - image это ресурс
	protected static function _doResizeAndSaveImage(string $output_file_path, int $new_width, int $width, mixed $image):array {

		// резайзим изображение
		$scale         = $new_width / $width;
		$resized_image = self::_doResizeImage($scale, $image);
		if ($resized_image === false) {
			return [];
		}

		// получаем финальные ширину и высоту, чтобы наверняка знать точную
		$new_width  = vips_image_get($resized_image, "width")["out"];
		$new_height = vips_image_get($resized_image, "height")["out"];

		// сохраняем изображение
		$output_file_path = self::_doSaveImage($output_file_path, $resized_image, true);
		if (mb_strlen($output_file_path) < 1) {
			return [];
		}

		// получаем размер картинки и путь
		$size_kb       = Type_File_Utils::getFileSizeKb($output_file_path);
		$new_part_path = Type_File_Utils::getPartPathFromFilePath($output_file_path);

		// пормируем image_size_item и отдаем его
		return self::_formatImageSizeItem($new_part_path, $new_width, $new_height, $size_kb);
	}

	// метод для ресайза картинки
	// @mixed - $image является ресурсом
	protected static function _doResizeImage(float $scale, mixed $image):mixed {

		// нарезаем изображение
		$resized_image = vips_call("resize", $image, $scale);
		if ($resized_image == -1) {
			return false;
		}

		return $resized_image["out"];
	}

	// метод для сохранения картинки
	// @mixed - $image является ресурсом
	protected static function _doSaveImage(string $output_file_path, mixed $image, bool $is_try_again = false):string {

		// если файл формата heic принудительно конвертим в jpg
		$image_extension = Type_File_Utils::getExtension($output_file_path);

		// Q - качество изображения для jpg (90 - оптимальный вариант, когда качество и размер приближены к исходному изображению)
		$result = vips_image_write_to_file($image, $output_file_path, ["Q" => 90]);
		if ($image_extension == "heic") {
			$result = -1;
		}

		// сохраняем по пути
		if ($result == -1) {

			if (!$is_try_again) {
				return "";
			}

			// добавляем к изображение jpg чтобы попробовать сохранить картинку в формате jpg
			$output_file_path = $output_file_path . ".jpg";

			// Q - качество изображения для jpg (90 - оптимальный вариант, когда качество и размер приближены к исходному изображению)
			$result = vips_image_write_to_file($image, $output_file_path, ["Q" => 90]);
			if ($result == -1) {

				Type_System_Admin::log("vips_image_write_to_file error", "[" . __METHOD__ . "] File {$output_file_path} - error", true);
				return "";
			}
		}

		return $output_file_path;
	}

	// формирует структура версии изображения
	protected static function _formatImageSizeItem(string $part_path, int $width, int $height, int $size_kb):array {

		return [
			"part_path" => $part_path,
			"width"     => $width,
			"height"    => $height,
			"size_kb"   => $size_kb,
		];
	}
}