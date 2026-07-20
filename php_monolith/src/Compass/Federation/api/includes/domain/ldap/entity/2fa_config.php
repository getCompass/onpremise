<?php

namespace Compass\Federation;

use BaseFrame\Server\ServerProvider;
use BaseFrame\System\Mail;

/**
 * класс конфига
 * @package Compass\Federation
 */
class Domain_Ldap_Entity_2faConfig
{
	protected const _AUTHORIZATION_2FA_METHOD_MAIL = "mail";
	protected const _AUTHORIZATION_2FA_METHOD_TOTP = "totp";

	/** @var Domain_Ldap_Entity_2faConfig|null для синглтона */
	protected static Domain_Ldap_Entity_2faConfig | null $_instance = null;

	public bool $authorization_2fa_enabled = false;

	public string $authorization_2fa_method = "mail";

	public string $mail_mapped_field = "";

	public array $mail_allowed_domains = [];

	public string $totp_issuer = "";

	protected function __construct(bool $authorization_2fa_enabled, string $authorization_2fa_method, string $mail_mapped_field, array $mail_allowed_domains, string $totp_issuer)
	{

		$this->authorization_2fa_enabled = $authorization_2fa_enabled;
		$this->authorization_2fa_method  = $authorization_2fa_method;
		$this->mail_mapped_field         = $mail_mapped_field;
		$this->mail_allowed_domains      = $mail_allowed_domains;
		$this->totp_issuer               = $totp_issuer;
	}

	/**
	 * Конфиг представлен в виде синглтона
	 *
	 * @return $this
	 */
	public static function instance(): self
	{

		// если мы на тестовом сервере - проверяем мок
		if (
			ServerProvider::isTest()
			&& $mock_config = ShardingGateway::cache()->get(Domain_Ldap_Entity_2faConfig_Mock::MOCK_KEY)
		) {

			return Domain_Ldap_Entity_2faConfig_Mock::_instanceMock(
				$mock_config["authorization_2fa_enabled"],
				$mock_config["authorization_2fa_method"],
				$mock_config["mail_mapped_field"],
				$mock_config["mail_allowed_domains"],
				$mock_config["totp_issuer"] ?? ""
			);
		}

		$config = getConfig("LDAP");

		$authorization_2fa_enabled = $config["authorization_2fa_enabled"] ?? false;
		$authorization_2fa_method  = $config["authorization_2fa_method"] ?? "mail";
		$mail_mapped_field         = $config["mail_mapped_field"] ?? "";
		$mail_allowed_domains      = $config["mail_allowed_domains"] ?? [];
		$totp_issuer               = $config["totp_issuer"] ?? "";

		if (is_null(self::$_instance)) {
			self::$_instance = new self($authorization_2fa_enabled, $authorization_2fa_method, $mail_mapped_field, $mail_allowed_domains, $totp_issuer);
		}

		return self::$_instance;
	}

	/**
	 * Проверяем, работает ли 2fa авторизация
	 * @return $this
	 * @throws Domain_Ldap_Exception_Auth_2faDisabled
	 */
	public function assertAuthorization2FaEnabled(): self
	{

		if (!$this->authorization_2fa_enabled) {
			throw new Domain_Ldap_Exception_Auth_2faDisabled();
		}

		return $this;
	}

	/**
	 * Включена ли авторизация 2fa через totp
	 */
	public function isTotpAuthMethodEnabled(): bool
	{

		return $this->authorization_2fa_method === self::_AUTHORIZATION_2FA_METHOD_TOTP;
	}

	/**
	 * Проверяем, что ручная привязка почта разрешена
	 *
	 * @return $this
	 * @throws Domain_Ldap_Exception_Mail_ManualAddDisabled
	 */
	public function assertMailManualAddEnabled(): self
	{

		if ($this->mail_mapped_field !== "") {
			throw new Domain_Ldap_Exception_Mail_ManualAddDisabled();
		}

		return $this;
	}

	/**
	 * Проверить, что домен почты разрешен
	 *
	 * @return $this
	 * @throws Domain_Ldap_Exception_Mail_DomainNotAllowed
	 */
	public function assertMailDomainAllowed(Mail $mail): self
	{

		if ($this->mail_allowed_domains === []) {
			return $this;
		}

		$domain = $mail->getDomain();

		if (!in_array($domain, $this->mail_allowed_domains)) {
			throw new Domain_Ldap_Exception_Mail_DomainNotAllowed();
		}

		return $this;
	}
}
