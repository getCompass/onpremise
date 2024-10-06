<?php /** @noinspection SSBasedInspection */

/**
 * Референс взят с библиотеки github.com/jumbojett/OpenID-Connect-PHP
 */

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Server\ServerProvider;
use phpseclib3\Crypt\RSA;
use phpseclib3\Math\BigInteger;
use stdClass;

/**
 * клиент для работы по протоколу OpenID Connect
 *
 * @package Compass\Federation
 */
class Gateway_Sso_Oidc_Client {

	/**
	 * @var string
	 */
	private string $client_id;

	/**
	 * @var string
	 */
	private string $client_secret;

	/**
	 * @var string
	 */
	private string $state;

	/**
	 * @var string
	 */
	private string $nonce;

	/**
	 * @var bool
	 */
	private bool $verify_peer = true;

	/**
	 * @var bool
	 */
	private bool $verify_host = true;

	/**
	 * @var array
	 */
	private array $provider_config = [];

	/**
	 * @var string
	 */
	protected string $access_token;

	/**
	 * @var string
	 */
	private string $refresh_token;

	/**
	 * @var string
	 */
	protected string $id_token;

	/**
	 * @var object
	 */
	private object $token_response;

	/**
	 * @var array
	 */
	private array $scope_list = [];

	/**
	 * @var int|null
	 */
	private int|null $response_code;

	/**
	 * @var string|null
	 */
	private string|null $response_content_type;

	/**
	 * @var array
	 */
	private array $response_types = [];

	/**
	 * @var array
	 */
	private array $auth_params = [];

	/**
	 * @var int timeout (seconds)
	 */
	protected int $time_out = 60;

	/**
	 * @var int leeway (seconds)
	 */
	private int $leeway = 300;

	/**
	 * @var array holds response types
	 */
	private array $additional_jwks = [];

	/**
	 * @var object holds verified jwt claims
	 */
	protected array|object $verified_claims = [];

	/**
	 * @var string ссылка для редиректа
	 */
	private string $redirect_url;

	/**
	 * @var int defines which URL-encoding http_build_query() uses
	 */
	protected int $enc_type = PHP_QUERY_RFC1738;

	/**
	 * @var bool Enable or disable upgrading to HTTPS by paying attention to HTTP header HTTP_UPGRADE_INSECURE_REQUESTS
	 */
	protected bool $http_upgrade_insecure_requests = true;

	/**
	 * @var array list of supported auth methods
	 */
	private array $token_endpoint_auth_methods_supported = ["client_secret_basic"];

	public function __construct(string $client_id, string $client_secret) {

		$this->client_id     = $client_id;
		$this->client_secret = $client_secret;
	}

	public function setVerifyPeer(bool $verify_peer):void {

		if (!ServerProvider::isDev()) {
			throw new ParseFatalException("only dev env");
		}

		$this->verify_peer = $verify_peer;
	}

	public function setVerifyHost(bool $verify_host):void {

		if (!ServerProvider::isDev()) {
			throw new ParseFatalException("only dev env");
		}

		$this->verify_host = $verify_host;
	}

	/**
	 * Устанавливает http заголовок HTTP_UPGRADE_INSECURE_REQUESTS
	 */
	public function setHttpUpgradeInsecureRequests(bool $http_upgrade_insecure_requests):void {

		$this->http_upgrade_insecure_requests = $http_upgrade_insecure_requests;
	}

	/**
	 * Устанавливаем конфиг провайдера
	 */
	public function setProviderConfig(array $config):void {

		$this->provider_config = $config;
	}

	/**
	 * Устанавливаем URL для редиректа
	 */
	public function setRedirectURL(string $url):void {

		$this->redirect_url = $url;
	}

	/**
	 * Добавляем scope
	 */
	public function addScope(array $scope):void {

		$this->scope_list = array_merge($this->scope_list, $scope);
	}

	/**
	 * Получаем URL для прохождения авторизации
	 *
	 * @return string
	 */
	public function getAuthorizationURL(string $state, string $nonce):string {

		$auth_endpoint = $this->_getProviderConfigValue("authorization_endpoint");
		$response_type = "code";

		$auth_params = array_merge($this->auth_params, [
			"response_type" => $response_type,
			"redirect_uri"  => $this->_getRedirectURL(),
			"client_id"     => $this->client_id,
			"nonce"         => $nonce,
			"state"         => $state,
			"scope"         => "openid",
		]);

		// если клиент содержит доп. scopes
		if (count($this->scope_list) > 0) {
			$auth_params["scope"] = implode(" ", array_merge($this->scope_list, [$auth_params["scope"]]));
		}

		// если клиент содержит доп. response_type
		if (count($this->response_types) > 0) {
			$auth_params["response_type"] = implode(" ", array_merge($this->response_types, [$auth_params["response_type"]]));
		}

		return $auth_endpoint . (strpos($auth_endpoint, "?") === false ? "?" : "&") . http_build_query($auth_params, "", "&", $this->enc_type);
	}

	/**
	 * Получаем значение из конфига провайдера по ключу
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	protected function _getProviderConfigValue(string $key):mixed {

		return $this->provider_config[$key];
	}

	/**
	 * Получаем ссылку для редиректа
	 *
	 * @return string
	 */
	protected function _getRedirectURL():string {

		return $this->redirect_url;
	}

	/**
	 * Получаем client_secret
	 *
	 * @return string
	 */
	protected function _getClientSecret():string {

		return $this->client_secret;
	}

	/**
	 * Осуществляем аутентификацию с помощью кода
	 *
	 * @throws Gateway_Sso_Oidc_Exception
	 * @long
	 */
	public function authenticateByCode(string $code, string $received_state):object {

		$token_json = $this->requestTokens($code);

		// Throw an error if the server returns one
		if (isset($token_json->error)) {
			if (isset($token_json->error_description)) {
				throw new Gateway_Sso_Oidc_Exception($token_json->error_description);
			}
			throw new Gateway_Sso_Oidc_Exception("Got response: " . $token_json->error);
		}

		if ($received_state !== $this->state) {
			throw new Gateway_Sso_Oidc_Exception("Unable to determine state");
		}

		if (!property_exists($token_json, "id_token")) {
			throw new Gateway_Sso_Oidc_Exception("User did not authorize openid scope.");
		}

		$id_token         = $token_json->id_token;
		$id_token_headers = $this->decodeJWT($id_token);

		if (isset($id_token_headers->enc)) {

			// Handle JWE
			$id_token = $this->_handleJweResponse($id_token);
		}

		$this->verifySignatures($id_token);

		$this->id_token     = $id_token;
		$this->access_token = $token_json->access_token;

		$claims = $this->decodeJWT($id_token, 1);
		if (!$this->verifyJWTClaims($claims, $token_json->access_token)) {
			throw new Gateway_Sso_Oidc_Exception('Unable to verify JWT claims');
		}

		$this->token_response  = $token_json;
		$this->verified_claims = $claims;
		if (isset($token_json->refresh_token)) {
			$this->refresh_token = $token_json->refresh_token;
		}

		return $token_json;
	}

	/**
	 * Запрашиваем токены
	 *
	 * @param string   $code
	 * @param string[] $headers Extra HTTP headers to pass to the token endpoint
	 *
	 * @long
	 */
	public function requestTokens(string $code, array $headers = []):object {

		$token_params = [
			"grant_type"    => "authorization_code",
			"code"          => $code,
			"redirect_uri"  => $this->_getRedirectURL(),
			"client_id"     => $this->client_id,
			"client_secret" => $this->client_secret,
		];

		$token_endpoint                        = $this->_getProviderConfigValue("token_endpoint");
		$token_endpoint_auth_methods_supported = $this->_getProviderConfigValue("token_endpoint_auth_methods_supported");

		$authorization_header = null;

		if ($this->isAuthMethodSupports("client_secret_basic", $token_endpoint_auth_methods_supported)) {

			$authorization_header = 'Authorization: Basic ' . base64_encode(urlencode($this->client_id) . ':' . urlencode($this->client_secret));
			unset($token_params['client_secret'], $token_params['client_id']);
		}

		if ($this->isAuthMethodSupports("client_secret_jwt", $token_endpoint_auth_methods_supported)) {

			$client_assertion_type = $this->_getProviderConfigValue("client_assertion_type");

			if (isset($this->providerConfig["client_assertion"])) {
				$client_assertion = $this->_getProviderConfigValue("client_assertion");
			} else {
				$client_assertion = $this->getJWTClientAssertion($this->_getProviderConfigValue("token_endpoint"));
			}

			$token_params["client_assertion_type"] = $client_assertion_type;
			$token_params["client_assertion"]      = $client_assertion;
			unset($token_params["client_secret"]);
		}

		// Convert token params to string format
		$token_params = http_build_query($token_params, '', '&', $this->enc_type);

		if (!is_null($authorization_header)) {
			$headers[] = $authorization_header;
		}

		$this->token_response = json_decode($this->fetchURL($token_endpoint, $token_params, $headers), false);

		return $this->token_response;
	}

	/**
	 * Проверяем, поддерживается ли способ аутентификации
	 *
	 * @return bool
	 */
	public function isAuthMethodSupports(string $auth_method, array $token_endpoint_auth_methods_supported):bool {

		if (!in_array($auth_method, $this->token_endpoint_auth_methods_supported, true)) {
			return false;
		}

		return in_array($auth_method, $token_endpoint_auth_methods_supported, true);
	}

	/**
	 * @return string
	 * @throws \Exception
	 */
	protected function getJWTClientAssertion(string $aud):string {

		$jti                = hash('sha256', bin2hex(random_bytes(64)));
		$now                = time();
		$header             = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
		$payload            = json_encode([
			'sub' => $this->client_id,
			'iss' => $this->client_id,
			'aud' => $aud,
			'jti' => $jti,
			'exp' => $now + 3600,
			'iat' => $now,
		]);
		$base64_url_header  = $this->urlEncode($header);
		$base64_url_payload = $this->urlEncode($payload);

		$signature            = hash_hmac('sha256', $base64_url_header . "." . $base64_url_payload, $this->client_secret, true);
		$base64_url_signature = $this->urlEncode($signature);

		return $base64_url_header . "." . $base64_url_payload . "." . $base64_url_signature;
	}

	/**
	 * @return string
	 */
	protected function urlEncode(string $str):string {

		$enc = base64_encode($str);
		$enc = rtrim($enc, "=");
		return strtr($enc, "+/", "-_");
	}

	/**
	 * совершаем curl запрос
	 *
	 * @return string
	 * @long
	 */
	protected function fetchURL(string $url, string $post_body = null, array $headers = []):string {

		$ch = curl_init();
		if ($post_body !== null) {

			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body);

			$content_type = 'application/x-www-form-urlencoded';
			if (is_object(json_decode($post_body, false))) {
				$content_type = 'application/json';
			}
			$headers[] = "Content-Type: $content_type";
		}

		if (count($headers) > 0) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}

		curl_setopt($ch, CURLOPT_URL, $url);

		if (isset($this->httpProxy)) {
			curl_setopt($ch, CURLOPT_PROXY, $this->httpProxy);
		}

		curl_setopt($ch, CURLOPT_HEADER, 0);

		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

		if (isset($this->certPath)) {
			curl_setopt($ch, CURLOPT_CAINFO, $this->certPath);
		}

		if ($this->verify_host) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		} else {
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		}

		if ($this->verify_peer) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		} else {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // nosemgrep
		}

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		curl_setopt($ch, CURLOPT_TIMEOUT, $this->time_out);

		$output = curl_exec($ch);

		$info                        = curl_getinfo($ch);
		$this->response_code         = $info['http_code'];
		$this->response_content_type = $info['content_type'];

		if ($output === false) {
			throw new Gateway_Sso_Oidc_Exception('Curl error: (' . curl_errno($ch) . ') ' . curl_error($ch));
		}

		curl_close($ch);

		return $output;
	}

	/**
	 * @param string $jwt     encoded JWT
	 * @param int    $section the section we would like to decode
	 *
	 * @return object
	 */
	protected function decodeJWT(string $jwt, int $section = 0):stdClass {

		$parts = explode('.', $jwt);
		return json_decode(self::base64urlDecode($parts[$section]), false);
	}

	/**
	 * A wrapper around base64_decode which decodes Base64URL-encoded data,
	 * which is not the same alphabet as base64.
	 *
	 * @param string $base64_url
	 *
	 * @return bool|string
	 */
	public static function base64urlDecode(string $base64_url):bool|string {

		return base64_decode(self::_b64url2b64($base64_url));
	}

	/**
	 * Per RFC4648, "base64 encoding with URL-safe and filename-safe
	 * alphabet".  This just replaces characters 62 and 63.  None of the
	 * reference implementations seem to restore the padding if necessary,
	 * but we'll do it anyway.
	 *
	 * @param string $base64_url
	 *
	 * @return string
	 */
	protected static function _b64url2b64(string $base64_url):string {

		// "Shouldn't" be necessary, but why not
		$padding = strlen($base64_url) % 4;
		if ($padding > 0) {
			$base64_url .= str_repeat("=", 4 - $padding);
		}
		return strtr($base64_url, "-_", "+/");
	}

	/**
	 * @param string $jwt encoded JWT
	 *
	 * @return void
	 * @throws Gateway_Sso_Oidc_Exception
	 */
	public function verifySignatures(string $jwt):void {

		if (!$this->_getProviderConfigValue('jwks_uri')) {
			throw new Gateway_Sso_Oidc_Exception ('Unable to verify signature due to no jwks_uri being defined');
		}
		if (!$this->verifyJWTSignature($jwt)) {
			throw new Gateway_Sso_Oidc_Exception ('Unable to verify signature');
		}
	}

	/**
	 * @param string $jwt encoded JWT
	 *
	 * @return bool
	 * @throws Gateway_Sso_Oidc_Exception
	 * @long
	 */
	public function verifyJWTSignature(string $jwt):bool {

		$parts = explode(".", $jwt);
		if (!isset($parts[0])) {
			throw new Gateway_Sso_Oidc_Exception("Error missing part 0 in token");
		}

		$signature = self::base64urlDecode(array_pop($parts));
		if (false === $signature || "" === $signature) {
			throw new Gateway_Sso_Oidc_Exception("Error decoding signature from token");
		}
		$header = json_decode(self::base64urlDecode($parts[0]), false);
		if (!is_object($header)) {
			throw new Gateway_Sso_Oidc_Exception("Error decoding JSON from token header");
		}
		if (!isset($header->alg)) {
			throw new Gateway_Sso_Oidc_Exception("Error missing signature type in token header");
		}

		$payload = implode(".", $parts);
		switch ($header->alg) {
			case "RS256":
			case "PS256":
			case "PS512":
			case "RS384":
			case "RS512":

				$hashType      = "sha" . substr($header->alg, 2);
				$signatureType = $header->alg === "PS256" || $header->alg === "PS512" ? "PSS" : "";
				if (isset($header->jwk)) {

					$jwk = $header->jwk;
					$this->verifyJWKHeader($jwk);
				} else {

					$jwks = json_decode($this->fetchURL($this->_getProviderConfigValue('jwks_uri')), false);
					if ($jwks === null) {
						throw new Gateway_Sso_Oidc_Exception('Error decoding JSON from jwks_uri');
					}
					$jwk = $this->_getKeyForHeader($jwks->keys, $header);
				}

				$verified = $this->_verifyRSAJWTSignature($hashType,
					$jwk,
					$payload, $signature, $signatureType);
				break;
			case "HS256":
			case "HS512":
			case "HS384":

				$hashType = "SHA" . substr($header->alg, 2);
				$verified = $this->_verifyHMACJWTSignature($hashType, $this->_getClientSecret(), $payload, $signature);
				break;
			default:

				throw new Gateway_Sso_Oidc_Exception('No support for signature type: ' . $header->alg);
		}

		return $verified;
	}

	/**
	 * Запрашиваем обновленный access_token с помощью refresh_token
	 *
	 * @param string $refresh_token
	 *
	 * @return mixed
	 * @throws Gateway_Sso_Oidc_Exception
	 * @long
	 */
	public function refreshToken(string $refresh_token):mixed {

		$token_endpoint                        = $this->_getProviderConfigValue("token_endpoint");
		$token_endpoint_auth_methods_supported = $this->_getProviderConfigValue("token_endpoint_auth_methods_supported");

		$headers = [];

		$grant_type = "refresh_token";

		$token_params = [
			"grant_type"    => $grant_type,
			"refresh_token" => $refresh_token,
			"client_id"     => $this->client_id,
			"client_secret" => $this->client_secret,
			"scope"         => implode(" ", $this->scope_list),
		];

		if ($this->isAuthMethodSupports("client_secret_basic", $token_endpoint_auth_methods_supported)) {

			$headers = ["Authorization: Basic " . base64_encode(urlencode($this->client_id) . ":" . urlencode($this->client_secret))];
			unset($token_params["client_secret"], $token_params["client_id"]);
		}

		if ($this->isAuthMethodSupports("client_secret_jwt", $token_endpoint_auth_methods_supported)) {

			$client_assertion_type = $this->_getProviderConfigValue("client_assertion_type");
			$client_assertion      = $this->getJWTClientAssertion($this->_getProviderConfigValue("token_endpoint"));

			$token_params["grant_type"]            = "urn:ietf:params:oauth:grant-type:token-exchange";
			$token_params["subject_token"]         = $refresh_token;
			$token_params["audience"]              = $this->client_id;
			$token_params["subject_token_type"]    = "urn:ietf:params:oauth:token-type:refresh_token";
			$token_params["requested_token_type"]  = "urn:ietf:params:oauth:token-type:access_token";
			$token_params['client_assertion_type'] = $client_assertion_type;
			$token_params['client_assertion']      = $client_assertion;

			unset($token_params["client_secret"], $token_params["client_id"]);
		}

		$token_params = http_build_query($token_params, "", "&", $this->enc_type);
		$json         = json_decode($this->fetchURL($token_endpoint, $token_params, $headers), false);

		if (isset($json->access_token)) {
			$this->access_token = $json->access_token;
		}

		if (isset($json->refresh_token)) {
			$this->refresh_token = $json->refresh_token;
		}

		return $json;
	}

	/**
	 * @throws Gateway_Sso_Oidc_Exception
	 * @noinspection PhpMissingParameterTypeInspection
	 */
	protected function _verifyRSAJWTSignature(string $hashType, stdClass $key, $payload, $signature, $signatureType):bool {

		if (!(property_exists($key, 'n') && property_exists($key, 'e'))) {
			throw new Gateway_Sso_Oidc_Exception('Malformed key object');
		}

		/** @noinspection PhpParamsInspection */
		$key = RSA::load([
			'publicExponent' => new BigInteger(base64_decode(self::_b64url2b64($key->e)), 256),
			'modulus'        => new BigInteger(base64_decode(self::_b64url2b64($key->n)), 256),
			'isPublicKey'    => true,
		])
			->withHash($hashType);
		if ($signatureType === 'PSS') {
			$key = $key->withMGFHash($hashType)
				->withPadding(RSA::SIGNATURE_PSS);
		} else {
			$key = $key->withPadding(RSA::SIGNATURE_PKCS1);
		}
		return $key->verify($payload, $signature);
	}

	/**
	 * @param object      $claims
	 * @param string|null $accessToken
	 *
	 * @return bool
	 * @throws Gateway_Sso_Oidc_Exception
	 */
	protected function verifyJWTClaims(object $claims, string $accessToken = null):bool {

		$expected_at_hash = "";
		if (isset($claims->at_hash, $accessToken)) {

			if (isset($this->getIdTokenHeader()->alg) && $this->getIdTokenHeader()->alg !== "none") {
				$bit = substr($this->getIdTokenHeader()->alg, 2, 3);
			} else {
				$bit = "256";
			}
			$len              = ((int) $bit) / 16;
			$expected_at_hash = $this->urlEncode(substr(hash("sha" . $bit, $accessToken, true), 0, $len));
		}

		return (($this->_validateIssuer($claims->iss))
			&& (($claims->aud === $this->client_id) || in_array($this->client_id, $claims->aud, true))
			&& (!isset($claims->nonce) || $claims->nonce === $this->nonce)
			&& (!isset($claims->exp) || ((is_int($claims->exp)) && ($claims->exp >= time() - $this->leeway)))
			&& (!isset($claims->nbf) || ((is_int($claims->nbf)) && ($claims->nbf <= time() + $this->leeway)))
			&& (!isset($claims->at_hash) || !isset($accessToken) || $claims->at_hash === $expected_at_hash)
		);
	}

	/**
	 * @throws Gateway_Sso_Oidc_Exception
	 */
	protected function verifyJWKHeader(mixed $jwk):void {

		throw new Gateway_Sso_Oidc_Exception("Self signed JWK header is not valid");
	}

	protected function _verifyHMACJWTSignature(string $hashType, string $key, string $payload, string $signature):bool {

		$expected = hash_hmac($hashType, $payload, $key, true);
		return hash_equals($signature, $expected);
	}

	/**
	 * @throws Gateway_Sso_Oidc_Exception
	 * @long
	 */
	protected function _getKeyForHeader(array $keys, stdClass $header):mixed {

		foreach ($keys as $key) {

			if ($key->kty === 'RSA') {

				if (!isset($header->kid) || $key->kid === $header->kid) {
					return $key;
				}
			} else {

				if (isset($key->alg) && $key->alg === $header->alg && $key->kid === $header->kid) {
					return $key;
				}
			}
		}
		if ($this->additional_jwks) {

			foreach ($this->additional_jwks as $key) {

				if ($key->kty === 'RSA') {
					if (!isset($header->kid) || $key->kid === $header->kid) {
						return $key;
					}
				} else {
					if (isset($key->alg) && $key->alg === $header->alg && $key->kid === $header->kid) {
						return $key;
					}
				}
			}
		}
		if (isset($header->kid)) {
			throw new Gateway_Sso_Oidc_Exception('Unable to find a key for (algorithm, kid):' . $header->alg . ', ' . $header->kid . ')');
		}

		throw new Gateway_Sso_Oidc_Exception('Unable to find a key for RSA');
	}

	/**
	 * @param string $jwe The JWE to decrypt
	 *
	 * @return string the JWT payload
	 * @throws Gateway_Sso_Oidc_Exception
	 */
	protected function _handleJweResponse(string $jwe):string {

		throw new Gateway_Sso_Oidc_Exception("JWE response is not supported, please extend the class and implement this method");
	}

	public function setOriginalState(string $state):void {

		$this->state = $state;
	}

	public function setOriginalNonce(string $nonce):void {

		$this->nonce = $nonce;
	}

	/**
	 * @return object
	 */
	public function getIdTokenHeader():stdClass {

		return $this->decodeJWT($this->id_token);
	}

	/**
	 * @param string $iss
	 *
	 * @return bool
	 */
	protected function _validateIssuer(string $iss):bool {

		return $iss === $this->_getProviderConfigValue("issuer");
	}

	public function getAccessToken():string {

		return $this->access_token;
	}

	public function setAccessToken(string $access_token):void {

		$this->access_token = $access_token;
	}

	public function getRefreshToken():string {

		return $this->refresh_token;
	}

	public function getIdToken():string {

		return $this->id_token;
	}

	public function getResponseCode():int {

		return $this->response_code;
	}

	public function getResponseContentType():?string {

		return $this->response_content_type;
	}

	/**
	 * запрашиваем информацию об учетной записи
	 *
	 * @return array
	 * @throws Gateway_Sso_Oidc_Exception
	 * @long
	 */
	public function requestUserInfo():array {

		$user_info_endpoint_url = $this->_getProviderConfigValue("userinfo_endpoint");
		$schema                 = "openid";

		$user_info_endpoint_url .= "?schema=" . $schema;
		$headers                = [
			"Authorization: Bearer $this->access_token",
			'Accept: application/json',
		];

		$response = $this->fetchURL($user_info_endpoint_url, null, $headers);
		if ($this->getResponseCode() !== 200) {
			throw new Gateway_Sso_Oidc_Exception("The communication to retrieve user data has failed with status code " . $this->getResponseCode());
		}

		if ($this->getResponseContentType() === 'application/jwt') {

			$jwtHeaders = $this->decodeJWT($response);
			if (isset($jwtHeaders->enc)) {
				$jwt = $this->_handleJweResponse($response);
			} else {
				$jwt = $response;
			}

			$this->verifySignatures($jwt);
			$claims = $this->decodeJWT($jwt, 1);
			if (!$this->verifyJWTClaims($claims)) {
				throw new Gateway_Sso_Oidc_Exception("Invalid JWT signature");
			}

			$user_info = (array) $claims;
		} else {
			$user_info = fromJson($response);
		}

		return $user_info;
	}
}