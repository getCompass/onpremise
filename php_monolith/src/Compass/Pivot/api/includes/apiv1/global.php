<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\ParamException;

/**
 * контроллер для технических методов клиента
 */
class Apiv1_Global extends \BaseFrame\Controller\Api {

	// поддерживаемые методы. регистр не имеет значение
	public const ALLOW_METHODS = [
		"doStart",
		"getCountryFlagList",
		"getPublicDocuments",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * Метод передает информацию о клиенте и загружает параметры, начальное состояние приложения
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \busException
	 * @throws cs_AnswerCommand
	 * @throws cs_UserNotFound
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws \userAccessException
	 * @long
	 */
	public function doStart():array {

		$lang        = $this->post(\Formatter::TYPE_STRING, "lang");
		$app_version = $this->post(\Formatter::TYPE_STRING, "app_version");

		try {

			[
				$app_config,
				$server_time,
				$time_zone,
				$ws_token,
				$ws_url,
				$billing_url,
				$notification_preferences,
				$call_preferences,
				$announcement_initial_token,
				$userbot_preferences,
				$client_connection_token,
				$captcha_public_key,
				$captcha_public_data,
			]
				= Domain_User_Scenario_Api::doStart($this->user_id, $this->session_uniq, $app_version);
		} catch (cs_PlatformNotFound) {
			throw new ParamException(__METHOD__ . ": unsupported platform");
		} catch (cs_CompanyIncorrectDeviceId) {
			throw new ParamException(__METHOD__ . ": invalid device id");
		}

		$this->action->profile();
		$this->action->announcementStart($announcement_initial_token ?? "");

		// пишем что пользователь зашел
		Type_User_Auth_History::doStart($this->user_id);
		Type_User_Auth_Analytics::save($this->user_id, getUa(), getDeviceId(), $lang, $server_time, $time_zone);

		Type_User_ActionAnalytics::send($this->user_id, Type_User_ActionAnalytics::START_APP);

		return $this->ok(Apiv1_Format::doStart(
			$app_config,
			$lang,
			$server_time,
			$time_zone,
			$ws_token,
			$ws_url,
			$billing_url,
			$notification_preferences,
			$call_preferences,
			$userbot_preferences,
			$client_connection_token,
			$captcha_public_key,
			$captcha_public_data,
		));
	}

	/**
	 * Отдаем список флагов стран
	 */
	public function getCountryFlagList():array {

		// получаем список флагов
		$country_flag_list = Domain_User_Scenario_Api::getFlagList();

		return $this->ok(Apiv1_Format::countryFlagList($country_flag_list));
	}

	/**
	 * Метод передает контент и информацию о публичных файлах Оферты и Конфиденциальности
	 *
	 * @throws \paramException
	 * @throws \parseException|\cs_RowIsEmpty
	 */
	public function getPublicDocuments():array {

		$lang = $this->post(\Formatter::TYPE_STRING, "lang");

		if (!Type_Lang_Document::isCorrectLang($lang)) {
			$lang = Type_Lang_Document::DEFAULT_LANG;
		}

		try {
			$public_documents = Domain_Document_Scenario_Api::getPublicDocuments($lang);
		} catch (cs_PublicDocumentNotFound) {
			throw new ParamException("document not found");
		}

		return $this->ok([
			"public_document_list" => (array) Apiv1_Format::publicDocuments($public_documents),
		]);
	}
}