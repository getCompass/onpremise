<?php

namespace Compass\FileNode;

/**
 * Класс для работы с файлами видеоформата (любая первичная и последующая обработка)
 */
class Type_File_Video_Process {

	/**
	 * Делает первичную обработку видео, возвращает extra
	 *
	 * @return array
	 * @throws cs_VideoProcessFailed
	 */
	public static function doProcessOnUpload(string $part_path, int $company_id, string $company_url):array {

		// получаем file_path
		$file_path = Type_File_Utils::getFilePathFromPartPath($part_path);

		// получаем информацию об оригинальном видео
		$original_video_item = self::_getOriginalItemInfo($file_path);

		// получаем длительность видеоролика
		$video_info = Type_Extension_File::getVideoInfo($file_path);
		$duration   = self::_getDuration($file_path);

		// делаем preview-скриншот видеоролика и получаем путь до него
		$preview_screen_file_path = self::_doPreviewScreen($file_path, $video_info);

		// получаем part_path для preview-скриншота видеоролика
		$preview_part_path = Type_File_Utils::getPartPathFromFilePath($preview_screen_file_path);
		$image_extra       = Type_File_Image_Process::doProcessVideoScreen($preview_part_path, $company_id, $company_url);

		// определяем размеры для нарезки видео
		$video_version_list = self::_getVideoVersionList($part_path, VIDEO_TYPE_PROGRESSIVE, $original_video_item["width"], $original_video_item["height"]);

		// формируем и возвращаем extra
		return Type_File_Video_Extra::getExtra($part_path, $company_id, $company_url, $original_video_item, $preview_part_path, $image_extra, $video_version_list, $duration);
	}

	/**
	 * Делает полную обработку видео, возвращает extra
	 *
	 * @param string $original_part_path
	 * @param string $output_part_path
	 * @param int    $width
	 * @param int    $height
	 *
	 * @return array
	 */
	public static function doPostProcess(string $original_part_path, string $output_part_path, int $width, int $height):array {

		// генерируем пути
		$original_file_path = Type_File_Utils::getFilePathFromPartPath($original_part_path);
		$output_file_path   = Type_File_Utils::getFilePathFromPartPath($output_part_path);

		$video_info = Type_Extension_File::getVideoInfo($original_file_path);
		$is_hdr     = self::_isHdrVideo($video_info);

		// нарезаем файл
		$is_success = Type_Extension_File::doVideoResize($original_file_path, $output_file_path, $width, $height, $is_hdr);

		return [$is_success, $is_hdr];
	}

	/**
	 * Функция для получения формата видео
	 *
	 * @return int
	 */
	public static function getMainSide(int $height, int $width):int {

		return min($width, $height);
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	/**
	 * Функция для получения продолжительности видеоролика
	 *
	 * @return int
	 */
	protected static function _getDuration(string $file_path):int {

		$video_info = Type_Extension_File::getVideoInfo($file_path);
		return $video_info["duration"];
	}

	/**
	 * Функция для получения preview-скриншота видеролика
	 *
	 * @return string
	 * @throws cs_VideoProcessFailed
	 */
	protected static function _doPreviewScreen(string $file_path, array $video_info):string {

		// получаем директорию, в которой находится видеоролик
		$dirname = pathinfo($file_path, PATHINFO_DIRNAME);

		$preview_extension = ".jpg";
		$is_hdr            = self::_isHdrVideo($video_info);
		if ($is_hdr) {
			$preview_extension = ".png";
		}

		// получаем название для файла с preview-скриншотом
		$preview_screen_file_name = pathinfo($file_path, PATHINFO_FILENAME) . $preview_extension;

		// формируем путь до preview-скриншота
		$preview_screen_file_path = $dirname . "/" . $preview_screen_file_name;

		// используем extension и получаем скриншот первого кадра видео файла
		if (!Type_Extension_File::tryGetVideoScreen($file_path, $preview_screen_file_path, $is_hdr)) {

			// если не пролучилось заскринить видео то отдаем exception
			throw new cs_VideoProcessFailed();
		}

		return $preview_screen_file_path;
	}

	/**
	 * Проверяем видео на тип чтобы нарезать формат
	 * @long
	 */
	protected static function _isHdrVideo(array $video_info):bool {

		/**
		 * как выглядит HDR видео
		 * color_range = 1 - длина цветового пространства
		 * еще бывает:
		 *      AVCOL_RANGE_UNSPECIFIED = 0,
		 *      AVCOL_RANGE_MPEG        = 1,
		 *    AVCOL_RANGE_JPEG        = 2,
		 *
		 * color_primaries = 9 - выделенная цветовая палитра для Ultra-high definition television (UHDTV)
		 * еще бывает:
		 *      AVCOL_PRI_RESERVED0   = 0,
		 *      AVCOL_PRI_BT709       = 1,  ///< also ITU-R BT1361 / IEC 61966-2-4 / SMPTE RP 177 Annex B
		 *      AVCOL_PRI_UNSPECIFIED = 2,
		 *      AVCOL_PRI_RESERVED    = 3,
		 *      AVCOL_PRI_BT470M      = 4,  ///< also FCC Title 47 Code of Federal Regulations 73.682 (a)(20)
		 *      AVCOL_PRI_BT470BG     = 5,  ///< also ITU-R BT601-6 625 / ITU-R BT1358 625 / ITU-R BT1700 625 PAL & SECAM
		 *      AVCOL_PRI_SMPTE170M   = 6,  ///< also ITU-R BT601-6 525 / ITU-R BT1358 525 / ITU-R BT1700 NTSC
		 *      AVCOL_PRI_SMPTE240M   = 7,  ///< identical to above, also called "SMPTE C" even though it uses D65
		 *      AVCOL_PRI_FILM        = 8,  ///< colour filters using Illuminant C
		 *      AVCOL_PRI_BT2020      = 9,  ///< ITU-R BT2020
		 *      AVCOL_PRI_SMPTE428    = 10, ///< SMPTE ST 428-1 (CIE 1931 XYZ)
		 *      AVCOL_PRI_SMPTEST428_1 = AVCOL_PRI_SMPTE428,
		 *      AVCOL_PRI_SMPTE431    = 11, ///< SMPTE ST 431-2 (2011) / DCI P3
		 *      AVCOL_PRI_SMPTE432    = 12, ///< SMPTE ST 432-1 (2010) / P3 D65 / Display P3
		 *      AVCOL_PRI_EBU3213     = 22, ///< EBU Tech. 3213-E (nothing there) / one of JEDEC P22 group phosphors
		 *      AVCOL_PRI_JEDEC_P22   = AVCOL_PRI_EBU3213,
		 *      AVCOL_PRI_NB                ///< Not part of ABI
		 *
		 * color_trc = 18 - гамма
		 * еще бывает:
		 *      AVCOL_TRC_RESERVED0    = 0,
		 *      AVCOL_TRC_BT709        = 1,  ///< also ITU-R BT1361
		 *      AVCOL_TRC_UNSPECIFIED  = 2,
		 *      AVCOL_TRC_RESERVED     = 3,
		 *      AVCOL_TRC_GAMMA22      = 4,  ///< also ITU-R BT470M / ITU-R BT1700 625 PAL & SECAM
		 *      AVCOL_TRC_GAMMA28      = 5,  ///< also ITU-R BT470BG
		 *      AVCOL_TRC_SMPTE170M    = 6,  ///< also ITU-R BT601-6 525 or 625 / ITU-R BT1358 525 or 625 / ITU-R BT1700 NTSC
		 *      AVCOL_TRC_SMPTE240M    = 7,
		 *      AVCOL_TRC_LINEAR       = 8,  ///< "Linear transfer characteristics"
		 *      AVCOL_TRC_LOG          = 9,  ///< "Logarithmic transfer characteristic (100:1 range)"
		 *      AVCOL_TRC_LOG_SQRT     = 10, ///< "Logarithmic transfer characteristic (100 * Sqrt(10) : 1 range)"
		 *      AVCOL_TRC_IEC61966_2_4 = 11, ///< IEC 61966-2-4
		 *      AVCOL_TRC_BT1361_ECG   = 12, ///< ITU-R BT1361 Extended Colour Gamut
		 *      AVCOL_TRC_IEC61966_2_1 = 13, ///< IEC 61966-2-1 (sRGB or sYCC)
		 *      AVCOL_TRC_BT2020_10    = 14, ///< ITU-R BT2020 for 10-bit system
		 *      AVCOL_TRC_BT2020_12    = 15, ///< ITU-R BT2020 for 12-bit system
		 *      AVCOL_TRC_SMPTE2084    = 16, ///< SMPTE ST 2084 for 10-, 12-, 14- and 16-bit systems
		 *      AVCOL_TRC_SMPTEST2084  = AVCOL_TRC_SMPTE2084,
		 *      AVCOL_TRC_SMPTE428     = 17, ///< SMPTE ST 428-1
		 *      AVCOL_TRC_SMPTEST428_1 = AVCOL_TRC_SMPTE428,
		 *      AVCOL_TRC_ARIB_STD_B67 = 18, ///< ARIB STD-B67, known as "Hybrid log-gamma"
		 *      AVCOL_TRC_NB                 ///< Not part of ABI
		 *
		 * color_space - 9 - цветовое пространство - описывает, как массив значений пикселей должен отображаться на экране и предоставляет информацию о том, как значения пикселей хранятся в файле, каков диапазон и значение у пикселей
		 * еще бывает:
		 *      AVCOL_SPC_RGB         = 0,  ///< order of coefficients is actually GBR, also IEC 61966-2-1 (sRGB), YZX and ST 428-1
		 *      AVCOL_SPC_BT709       = 1,  ///< also ITU-R BT1361 / IEC 61966-2-4 xvYCC709 / derived in SMPTE RP 177 Annex B
		 *      AVCOL_SPC_UNSPECIFIED = 2,
		 *      AVCOL_SPC_RESERVED    = 3,  ///< reserved for future use by ITU-T and ISO/IEC just like 15-255 are
		 *      AVCOL_SPC_FCC         = 4,  ///< FCC Title 47 Code of Federal Regulations 73.682 (a)(20)
		 *      AVCOL_SPC_BT470BG     = 5,  ///< also ITU-R BT601-6 625 / ITU-R BT1358 625 / ITU-R BT1700 625 PAL & SECAM / IEC 61966-2-4 xvYCC601
		 *      AVCOL_SPC_SMPTE170M   = 6,  ///< also ITU-R BT601-6 525 / ITU-R BT1358 525 / ITU-R BT1700 NTSC / functionally identical to above
		 *      AVCOL_SPC_SMPTE240M   = 7,  ///< derived from 170M primaries and D65 white point, 170M is derived from BT470 System M's primaries
		 *      AVCOL_SPC_YCGCO       = 8,  ///< used by Dirac / VC-2 and H.264 FRext, see ITU-T SG16
		 *      AVCOL_SPC_YCOCG       = AVCOL_SPC_YCGCO,
		 *      AVCOL_SPC_BT2020_NCL  = 9,  ///< ITU-R BT2020 non-constant luminance system
		 *      AVCOL_SPC_BT2020_CL   = 10, ///< ITU-R BT2020 constant luminance system
		 *      AVCOL_SPC_SMPTE2085   = 11, ///< SMPTE 2085, Y'D'zD'x
		 *      AVCOL_SPC_CHROMA_DERIVED_NCL = 12, ///< Chromaticity-derived non-constant luminance system
		 *      AVCOL_SPC_CHROMA_DERIVED_CL = 13, ///< Chromaticity-derived constant luminance system
		 *      AVCOL_SPC_ICTCP       = 14, ///< ITU-R BT.2100-0, ICtCp
		 *      AVCOL_SPC_NB                ///< Not part of ABI
		 */
		if ($video_info["color_range"] == 1 &&
			$video_info["color_primaries"] == 9 &&
			$video_info["color_trc"] == 18 &&
			$video_info["color_space"] == 9) {

			return true;
		}

		return false;
	}

	/**
	 * Определяем размеры для нарезки видео
	 *
	 * @param string $file_path
	 * @param int    $video_type
	 * @param int    $width
	 * @param int    $height
	 *
	 * @return array
	 */
	protected static function _getVideoVersionList(string $file_path, int $video_type, int $width, int $height):array {

		// получаем директорию, расширение и название видеофайла
		$dirname          = pathinfo($file_path, PATHINFO_DIRNAME);
		$file_name        = pathinfo($file_path, PATHINFO_FILENAME);
		$main_side        = self::getMainSide($height, $width);
		$original_quality = self::_getOriginalQuality($main_side);

		// пробегаемся по необходимым размерам
		$output = [];
		foreach (Type_File_Video_Main::VIDEO_FORMAT_LIST as $version_side_x) {

			// если необходимая сторона < стороны оригинального видео добавляем элемент в output массив
			if ($version_side_x < $main_side && $original_quality !== $version_side_x) {

				// получаем размеры для нарезки видео
				[$new_width, $new_height] = self::_getVideoDimensions($width, $height, $original_quality, $version_side_x);

				$version_part_path = self::_getVideoPathPath($version_side_x, $dirname, $file_name);
				$key               = Type_File_Video_Main::getVideoItemKey($version_side_x, $video_type);
				$output[$key]      = self::_formatVideoVersionItem($version_part_path, $new_width, $new_height, $version_side_x);
			}
		}

		return $output;
	}

	/**
	 * Получить part_path для видео
	 *
	 * @return string
	 */
	protected static function _getVideoPathPath(int $main_side, string $dirname, string $file_name):string {

		$postfix = "v{$main_side}p";

		// формируем part_path
		$version_file_path = "{$dirname}/{$file_name}_{$postfix}.mp4";
		return Type_File_Utils::getPartPathFromFilePath($version_file_path);
	}

	/**
	 * Получить размеры видео
	 *
	 * @return array
	 */
	protected static function _getVideoDimensions(int $original_width, int $original_height, int $original_main_side_size,
								    int $version_side_x):array {

		// высчитываем размеры для нарезанного видео
		$ratio = $version_side_x * 100 / $original_main_side_size;

		$new_width  = (int) round($original_width * $ratio / 100);
		$new_height = (int) round($original_height * $ratio / 100);

		// округляем до четного
		$new_width  = ($new_width % 2) == 0 ? $new_width : --$new_width;
		$new_height = ($new_height % 2) == 0 ? $new_height : --$new_height;

		return [$new_width, $new_height];
	}

	/**
	 * Функция для получения информации об оригинальном видео
	 *
	 * @return array
	 */
	protected static function _getOriginalItemInfo(string $file_path):array {

		// получаем информацию о файле
		$video_info = Type_Extension_File::getVideoInfo($file_path);

		// инициализириуем массив для ответа
		$output = [
			"width"  => $video_info["width"],
			"height" => $video_info["height"],
			"ratio"  => 0,
		];

		// получаем размер файла
		$output["size_kb"] = Type_File_Utils::getFileSizeKb($file_path);

		// если ширина больше 0 то вычисляем ratio
		if ($output["width"] > 0) {
			$output["ratio"] = $output["height"] / $output["width"];
		}

		return $output;
	}

	/**
	 * Формирует структуру версии видео
	 *
	 * @return array
	 */
	protected static function _formatVideoVersionItem(string $part_path, int $width, int $height, int $format):array {

		return [
			"video_type" => VIDEO_TYPE_PROGRESSIVE,
			"status"     => VIDEO_UPLOAD_STATUS_WIP,
			"part_path"  => $part_path,
			"width"      => $width,
			"height"     => $height,
			"format"     => $format,
		];
	}

	/**
	 * Получить исходное качество
	 *
	 * @return int
	 */
	protected static function _getOriginalQuality(int $main_side):int {

		$closest = 0;

		foreach (Type_File_Video_Main::VIDEO_FORMAT_LIST as $item) {

			if ($closest === 0 || abs($main_side - $closest) > abs($item - $main_side)) {
				$closest = $item;
			}
		}

		return $closest;
	}
}