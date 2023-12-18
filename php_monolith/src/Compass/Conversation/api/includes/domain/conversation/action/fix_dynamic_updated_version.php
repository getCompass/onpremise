<?php

namespace Compass\Conversation;

/**
 * Правим метку обновления версий для dynamic диалога
 */
class Domain_Conversation_Action_FixDynamicUpdatedVersion {

	/**
	 * выполняем
	 *
	 * @param Struct_Db_CompanyConversation_ConversationDynamic $dynamic
	 *
	 * @return Struct_Db_CompanyConversation_ConversationDynamic
	 */
	public static function do(Struct_Db_CompanyConversation_ConversationDynamic $dynamic):Struct_Db_CompanyConversation_ConversationDynamic {

		try {
			$is_need_increment = self::_isNeedIncrementByPlatformVersion();
		} catch (cs_PlatformNotFound) {
			return $dynamic;
		}

		if (!$is_need_increment) {
			return $dynamic;
		}

		// фикс для исправления метки версий, когда та равна 0, но метка времени больше 0
		// из-за этого возник баг, что приложение не запрашивает данные диалога, потому что метка версий вернула 0 - сущность в диалоге не изменилась
		if ($dynamic->messages_updated_version == 0 && $dynamic->messages_updated_at > 0) {
			$dynamic->messages_updated_version = 1;
		}

		if ($dynamic->reactions_updated_version == 0 && $dynamic->reactions_updated_at > 0) {
			$dynamic->reactions_updated_version = 1;
		}

		if ($dynamic->threads_updated_version == 0 && $dynamic->threads_updated_at > 0) {
			$dynamic->threads_updated_version = 1;
		}

		return $dynamic;
	}

	/**
	 * получаем флаг, нужно ли инкрементить updated-версию по версии платформы
	 */
	protected static function _isNeedIncrementByPlatformVersion():bool {

		$user_agent = mb_strtolower(getUa());

		$platform = Type_Api_Platform::getPlatform($user_agent);
		preg_match("#\((.*?)\)#", $user_agent, $match);

		if (!isset($match[1])) {
			throw new cs_PlatformNotFound("passed incorrect version in user_agent");
		}

		$version = $match[1];

		return match ($platform) {
			Type_Api_Platform::PLATFORM_ANDROID => in_array($version, ANDROID_VERSION_WITH_INCREMENT_UPDATED_VERSION),
			default => false,
		};
	}
}