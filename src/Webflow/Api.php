<?php

namespace Webflow;

use Webflow\WebflowException;
use GuzzleHttp\Client;

class Api {

  const WEBFLOW_API_ENDPOINT = 'https://api.webflow.com';

  private $client;

  function __construct(
      $token,
      $version = '1.0.0'
  ) {
      if (empty($token)) {
        throw new WebflowException('token');
      }

      $this->client = new Client([
        'base_uri' => self::WEBFLOW_API_ENDPOINT,
        'headers' => [
          'Authorization' => "Bearer {$token}",
          'accept-version' => $version,
          'Accept' => 'application/json',
          'Content-Type' => 'application/json',
        ]
      ]);

      return $this;
  }

  // Meta

  public function info() {
    return $this->client->get('/info')->getBody();
  }

  public function sites() {
    return $this->client->get('/sites')->getBody();
  }

  public function site(string $siteId) {
    return $this->client->get("/sites/{$siteId}")->getBody();
  }

  public function domains(string $siteId) {
    return $this->client->get("/sites/{$siteId}/domains")->getBody();
  }

  public function publishSite(string $siteId, array $domains) {
    return $this->client->post(`/sites/${siteId}/publish`, $domains);
  }

  // Collections

  public function collections(string $siteId) {
    return $this->client->get("/sites/{$siteId}/collections")->getBody();
  }

  public function collection(string $collectionId) {
    return $this->client->get("/collections/{$collectionId}")->getBody();
  }

  // Items

  public function items(string $collectionId) {
    return $this->client->get("/collections/{$collectionId}/items")->getBody();
  }

  public function item(string $collectionId, string $itemId) {
    return $this->client->get("/collections/{$collectionId}/items/{$itemId}")->getBody();
  }

  public function createItem(string $collectionId, array $data) {
    return $this->client->post("/collections/{$collectionId}/items", [
        'json' => [
            'fields' => $data,
        ],
    ])->getBody();
  }

  public function updateItem(string $collectionId, string $itemId, array $data) {
    return $this->client->put("/collections/{$collectionId}/items/{$itemId}", $data);
  }

  public function removeItem(string $collectionId, $itemId) {
    return $this->client->delete("/collections/{$collectionId}/items/{$itemId}")->getBody();
  }

}
