<?php

namespace Compass\Pivot;

/**
 * класс для форматирования сущностей под формат API
 *
 * в коде мы оперируем своими структурами и понятиями
 * к этому классу обращаемся строго за отдачей результата в API
 * для форматирования стандартных сущностей
 */
class Www_Format {

	// информация по ссылке-приглашению
	public static function inviteLinkInfo(int $inviter_user_id, string $inviter_full_name, string $inviter_avatar_url, int $avatar_color_id):array {

		return [
			"inviter_user_id"    => (int) $inviter_user_id,
			"inviter_full_name"  => (string) $inviter_full_name,
			"inviter_avatar_url" => (string) $inviter_avatar_url,
			"avatar_color"       => (string) \BaseFrame\Domain\User\Avatar::getColorOutput($avatar_color_id),
		];
	}
}