<?php

require_once __DIR__ . '/../Models/View.php';

class FreshExtension_wallabagButton_Controller extends Minz_ActionController
{
  /** @var WallabagButton\View */
  protected $view;

  public function __construct()
  {
    parent::__construct(\WallabagButton\View::class);
  }

  public function jsVarsAction(): void
  {
    $extension = Minz_ExtensionManager::findExtension('Wallabag Button');
    $this->view->wallabag_button_vars = json_encode(array(
      'instance_url' => FreshRSS_Context::userConf()->attributeString('wallabag_instance_url'),
      'keyboard_shortcut' => FreshRSS_Context::userConf()->hasParam("wallabag_keyboard_shortcut") ?
        FreshRSS_Context::userConf()->attributeString('wallabag_keyboard_shortcut')
        : '',
      'icons' => array(
        'added_to_wallabag' => $extension->getFileUrl('added_to_wallabag.svg'),
      ),
      'i18n' => array(
        'added_article_to_wallabag' => _t('ext.wallabagButton.notifications.added_article_to_wallabag', '%s'),
        'failed_to_add_article_to_wallabag' => _t('ext.wallabagButton.notifications.failed_to_add_article_to_wallabag', '%s'),
        'ajax_request_failed' => _t('ext.wallabagButton.notifications.ajax_request_failed'),
        'article_not_found' => _t('ext.wallabagButton.notifications.article_not_found'),
      )
    ));

    $this->view->_layout(null);
    $this->view->_path('wallabagButton/vars.js');

    header('Content-Type: application/javascript; charset=utf-8');
  }

  public function requestAccessAction(): void
  {
    $instance_url = Minz_Request::paramString('wallabag_instance_url');
    $client_id = Minz_Request::paramString('wallabag_client_id');
    $client_secret = Minz_Request::paramString('wallabag_client_secret');
    $username = Minz_Request::paramString('wallabag_username');
    $password = Minz_Request::paramString('wallabag_password');

    // Handle leading slash
    if (substr($instance_url, -1) == '/')
    {
      $instance_url = substr($instance_url, 0, -1);
    }

    FreshRSS_Context::userConf()->_attribute('wallabag_instance_url', $instance_url);
    FreshRSS_Context::userConf()->_attribute('wallabag_username', $username);
    FreshRSS_Context::userConf()->_attribute('wallabag_client_id', $client_id);
    FreshRSS_Context::userConf()->save();

    $post_data = array(
      'grant_type' => 'password',
      'client_id' => $client_id,
      'client_secret' => $client_secret,
      'username' => $username,
      'password' => html_entity_decode($password), // Minz_Request replaces & for &amp; etc. which makes the password intantly invalid
    );

    $result = $this->curlPostRequest("/oauth/v2/token", $post_data);

    if ($result['status'] == 200) {
      $access_token = $result['response']->access_token;
      $refresh_token = $result['response']->refresh_token;
      $expires_in = $result['response']->expires_in;

      $this->storeTokens($access_token, $refresh_token, $expires_in);
      FreshRSS_Context::userConf()->_attribute('wallabag_client_secret', $client_secret);
      FreshRSS_Context::userConf()->save();

      $url_redirect = array('c' => 'extension', 'a' => 'configure', 'params' => array('e' => 'Wallabag Button'));
      Minz_Request::good(_t('ext.wallabagButton.notifications.authorized_success'), $url_redirect);
      exit();
    }

    $url_redirect = array(
      'c' => 'extension',
      'a' => 'configure',
      'params' => array('e' => 'Wallabag Button')
    );
    Minz_Request::bad(_t('ext.wallabagButton.notifications.request_access_failed', $result['status']), $url_redirect);
  }

  public function revokeAccessAction(): void
  {
    FreshRSS_Context::userConf()->_attribute('wallabag_instance_url');
    FreshRSS_Context::userConf()->_attribute('wallabag_username');
    FreshRSS_Context::userConf()->_attribute('wallabag_client_id');
    FreshRSS_Context::userConf()->_attribute('wallabag_client_secret');
    FreshRSS_Context::userConf()->_attribute('wallabag_access_token');
    FreshRSS_Context::userConf()->_attribute('wallabag_refresh_token');
    FreshRSS_Context::userConf()->_attribute('wallabag_access_token_expires_at');
    FreshRSS_Context::userConf()->save();

    $url_redirect = array('c' => 'extension', 'a' => 'configure', 'params' => array('e' => 'Wallabag Button'));
    Minz_Request::good(_t('ext.wallabagButton.notifications.authorization_revoked'), $url_redirect);
  }

  /**
   * @param array<string,mixed> $data Data to be JSON-encoded and sent as response
   */
  private function sendJsonResponse(array $data): void
  {
    $this->view->_layout(null);
    $this->view->result_json = json_encode($data, JSON_UNESCAPED_UNICODE) ?: '{}';
    $this->view->_path('wallabagButton/result.json');
    header('Content-Type: application/json; charset=utf-8');
  }

  public function addAction(): void
  {
    $entry_id = Minz_Request::paramString('id');
    $entry_dao = FreshRSS_Factory::createEntryDao();
    $entry = $entry_dao->searchById($entry_id);

    if ($entry === null) {
      $this->sendJsonResponse(array('status' => 404));
      return;
    }

    $post_data = array(
      'url' => $entry->link(),
    );

    $result = $this->curlPostRequest("/api/entries", $post_data, true);
    $this->sendJsonResponse($result);
  }

  private function isAccessTokenValid(): bool
  {
    return time() < FreshRSS_Context::userConf()->attributeInt('wallabag_access_token_expires_at');
  }

  private function refreshAccessToken(): void
  {
    $client_id = FreshRSS_Context::userConf()->attributeString('wallabag_client_id');
    $client_secret = FreshRSS_Context::userConf()->attributeString('wallabag_client_secret');
    $refresh_token = FreshRSS_Context::userConf()->attributeString('wallabag_refresh_token');

    $post_data = array(
      'grant_type' => 'refresh_token',
      'client_id' => $client_id,
      'client_secret' => $client_secret,
      'refresh_token' => $refresh_token,
    );

    $result = $this->curlPostRequest("/oauth/v2/token", $post_data);
    if ($result['status'] == 200) {
      $access_token = $result['response']->access_token;
      $refresh_token = $result['response']->refresh_token;
      $expires_in = $result['response']->expires_in;

      $this->storeTokens($access_token, $refresh_token, $expires_in);
      return;
    }

    // Else let it propagate and redirect user into settings
  }

  /**
   * @return array<string>
   */
  private function getRequestHeaders(bool $with_token = false): array
  {
    if ($with_token) {
      if (!$this->isAccessTokenValid()) {
        $this->refreshAccessToken();
      }

      $access_token = FreshRSS_Context::userConf()->attributeString('wallabag_access_token');
      return array(
        'Content-Type: application/json; charset=UTF-8',
        'X-Accept: application/json',
        "Authorization: Bearer " . $access_token,
      );
    }

    return array(
      'Content-Type: application/json; charset=UTF-8',
      'X-Accept: application/json',
    );
  }

  private function getCurlBase(string $url, bool $with_token = false): \CurlHandle
  {
    $headers = $this->getRequestHeaders($with_token);
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, true);

    $cainfo_path = FreshRSS_Context::userConf()->attributeString('wallabag_cainfo_path');
    if (!empty($cainfo_path)) {
      curl_setopt($curl, CURLOPT_CAINFO, $cainfo_path);
    }

    return $curl;
  }

  /**
   * @param array<string,mixed> $post_data
   * @return array<string,mixed>
   */
  private function curlPostRequest(string $endpoint, array $post_data, bool $with_token = false): array
  {
    $instance_url = FreshRSS_Context::userConf()->attributeString('wallabag_instance_url');
    $curl = $this->getCurlBase($instance_url . $endpoint, $with_token);
    curl_setopt($curl, CURLOPT_POST, true);

    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post_data, JSON_UNESCAPED_UNICODE));

    $response = curl_exec($curl);
    $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $response_header = substr($response, 0, $header_size);
    $response_body = substr($response, $header_size);
    $response_headers = $this->httpHeaderToArray($response_header);

    return array(
      'response' => json_decode($response_body),
      'status' => curl_getinfo($curl, CURLINFO_HTTP_CODE),
      'errorCode' => isset($response_headers['x-error-code']) ? intval($response_headers['x-error-code']) : curl_getinfo($curl, CURLINFO_HTTP_CODE)
    );
  }

  /**
   * @return array<string,string>
   */
  private function httpHeaderToArray(string $header): array
  {
    $headers = array();
    $headers_parts = explode("\r\n", $header);

    foreach ($headers_parts as $header_part) {
      // skip empty header parts
      if (strlen($header_part) <= 0) {
        continue;
      }

      // Filter the beginning of the header which is the basic HTTP status code
      if (strpos($header_part, ':') !== false) {
        $header_name = substr($header_part, 0, intval(strpos($header_part, ':')));
        $header_value = substr($header_part, intval(strpos($header_part, ':')) + 1);
        $headers[$header_name] = trim($header_value);
      }
    }

    return $headers;
  }

  private function storeTokens(string $access_token, string $refresh_token, int $expires_in): void
  {
    $expires_at = new DateTime();
    $expires_at->modify("+{$expires_in} seconds");

    FreshRSS_Context::userConf()->_attribute('wallabag_access_token', $access_token);
    FreshRSS_Context::userConf()->_attribute('wallabag_refresh_token', $refresh_token);
    FreshRSS_Context::userConf()->_attribute('wallabag_access_token_expires_at', $expires_at->getTimestamp());
    FreshRSS_Context::userConf()->save();
  }
}
