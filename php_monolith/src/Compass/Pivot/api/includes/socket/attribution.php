<?php declare(strict_types = 1);

namespace Compass\Pivot;

/**
 * Контроллер сокет методов для работы с атрибуцией
 */
class Socket_Attribution extends \BaseFrame\Controller\Socket {

	// список доступных методов
	public const ALLOW_METHODS = [
		"onLandingVisit",
	];

	/**
	 * Сохраняем параметры атрибуции при посещении landing страницы
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function onLandingVisit():array {

		$guest_id            = $this->post(\Formatter::TYPE_STRING, "guest_id");
		$link                = $this->post(\Formatter::TYPE_STRING, "link");
		$utm_tag             = $this->post(\Formatter::TYPE_STRING, "utm_tag");
		$source_id           = $this->post(\Formatter::TYPE_STRING, "source_id");
		$ip_address          = $this->post(\Formatter::TYPE_STRING, "ip_address");
		$platform            = $this->post(\Formatter::TYPE_STRING, "platform");
		$platform_os         = $this->post(\Formatter::TYPE_STRING, "platform_os");
		$timezone_utc_offset = $this->post(\Formatter::TYPE_INT, "timezone_utc_offset");
		$screen_avail_width  = $this->post(\Formatter::TYPE_INT, "screen_avail_width");
		$screen_avail_height = $this->post(\Formatter::TYPE_INT, "screen_avail_height");
		$visited_at          = $this->post(\Formatter::TYPE_INT, "visited_at");

		Domain_User_Entity_Attribution::saveLandingVisit(
			$guest_id, $link, $utm_tag, $source_id, $ip_address, $platform, $platform_os,
			$timezone_utc_offset, $screen_avail_width, $screen_avail_height, $visited_at
		);

		return $this->ok();
	}
}