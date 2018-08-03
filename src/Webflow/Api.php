<?php

namespace Webflow;

use Webflow\WebflowException;

class Api {

  const WEBFLOW_API_ENDPOINT = 'https://api.webflow.com';
  const WEBFLOW_API_USERAGENT = 'ExpertLead Webflow PHP SDK (https://github.com/expertlead/webflow-php-sdk)';

  private $client;
  private $token;

  function __construct(
      $token,
      $version = '1.0.0'
  ) {
      if (empty($token)) {
        throw new WebflowException('token');
      }

      $this->token = $token;
      $this->version = $version;

      return $this;
  }

  private function request(string $path, string $method, array $data = []) {
    $curl = curl_init();
    $options = [
        CURLOPT_URL => self::WEBFLOW_API_ENDPOINT . $path,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_USERAGENT => self::WEBFLOW_API_USERAGENT,
        CURLOPT_HTTPHEADER => [
          "Authorization: Bearer {$this->token}",
          "accept-version: {$this->version}",
          "Accept: application/json",
          "Content-Type: application/json",
        ],
        CURLOPT_RETURNTRANSFER => true,
    ];
    if (!empty($data)) {
        $json = json_encode($data);
        $options[CURLOPT_POSTFIELDS] = $json;
        $options[CURLOPT_HTTPHEADER][] = "Content-Length: " . strlen($json);
    }
    curl_setopt_array($curl, $options);
    $response = curl_exec($curl);
    curl_close($curl);
    return $this->parse($response);

  }
  private function get($path) {
    return $this->request($path, "GET");
  }

  private function post($path, $data) {
    return $this->request($path, "POST", $data);
  }

  private function put($path, $data) {
    return $this->request($path, "PUT", $data);
  }

  private function delete($path, $data) {
    return $this->request($path, "DELETE", $data);
  }

  private function parse($response) {
    return json_decode($response);
  }
  // Meta

  public function info() {
    return $this->get('/info');
  }

  public function sites() {
    return $this->get('/sites');
  }

  public function site(string $siteId) {
    return $this->get("/sites/{$siteId}");
  }

  public function domains(string $siteId) {
    return $this->get("/sites/{$siteId}/domains");
  }

  public function publishSite(string $siteId, array $domains) {
    return $this->post(`/sites/${siteId}/publish`, $domains);
  }

  // Collections

  public function collections(string $siteId) {
    return $this->get("/sites/{$siteId}/collections");
  }

  public function collection(string $collectionId) {
    return $this->get("/collections/{$collectionId}");
  }

  // Items

  public function items(string $collectionId) {
    return $this->get("/collections/{$collectionId}/items");
  }

  public function item(string $collectionId, string $itemId) {
    return $this->get("/collections/{$collectionId}/items/{$itemId}");
  }

  public function createItem(string $collectionId, array $fields) {
    $defaults = [
      "_archived" => false,
      "_draft" => false,
    ];
    return $this->post("/collections/{$collectionId}/items", [
        'fields' => $defaults + $fields,
    ]);
  }

  public function updateItem(string $collectionId, string $itemId, array $fields) {
    return $this->put("/collections/{$collectionId}/items/{$itemId}", $fields);
  }

  public function removeItem(string $collectionId, $itemId) {
    return $this->delete("/collections/{$collectionId}/items/{$itemId}");
  }

}
