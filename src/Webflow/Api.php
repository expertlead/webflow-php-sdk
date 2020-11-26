<?php

namespace Webflow;

use Webflow\WebflowException;

class Api
{
    const WEBFLOW_API_ENDPOINT = 'https://api.webflow.com';
    const WEBFLOW_API_USERAGENT = 'Expertlead Webflow PHP SDK (https://github.com/expertlead/webflow-php-sdk)';

    private $client;
    private $token;

    private $requests;
    private $start;
    private $finish;

    private $cache = [];

    public function __construct(
        $token,
        $version = '1.0.0'
    ) {
        if (empty($token)) {
            throw new WebflowException('token');
        }

        $this->token = $token;
        $this->version = $version;

        $this->rateRemaining = 60;

        return $this;
    }

    private function request(string $path, string $method, array $data = [])
    {
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
        CURLOPT_HEADER => true,
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
        list($headers, $body) = explode("\r\n\r\n", $response, 2);
        return $this->parse($body);
    }
    private function get($path)
    {
        return $this->request($path, "GET");
    }

    private function post($path, $data)
    {
        return $this->request($path, "POST", $data);
    }

    private function put($path, $data)
    {
        return $this->request($path, "PUT", $data);
    }

    private function delete($path)
    {
        return $this->request($path, "DELETE");
    }

    private function parse($response)
    {
        $json = json_decode($response);
        if (isset($json->code) && isset($json->msg)) {
            $error = $json->msg;
            if (isset($json->problems)) {
                $error .= PHP_EOL . implode(PHP_EOL, $json->problems);
            }
            throw new \Exception($error, $json->code);
        }
        return $json;
    }

    // Meta
    public function info()
    {
        return $this->get('/info');
    }

    public function sites()
    {
        return $this->get('/sites');
    }

    public function site(string $siteId)
    {
        return $this->get("/sites/{$siteId}");
    }

    public function domains(string $siteId)
    {
        return $this->get("/sites/{$siteId}/domains");
    }

    public function publishSite(string $siteId, array $domains)
    {
        return $this->post("/sites/${siteId}/publish", $domains);
    }
    
    public function webhooks(string $siteId)
    {
        return $this->get("/sites/{$siteId}/webhooks");
    }

    public function webhook(string $siteId, string $webhookId)
    {
        return $this->get("/sites/{$siteId}/webhooks/{$webhookId}");
    }

    public function createWebhook(string $siteId, string $triggerType, string $url, ?string $filter = '')
    {
        $defaults = [
            "triggerType" => "form_submission",
            "url" => '',
            "filter" => '',
        ];

        $triggerTypes = [
            'form_submission',
            'site_publish',
            'ecomm_new_order',
            'ecomm_order_changed',
            'ecomm_inventory_changed',
            'collection_item_created',
            'collection_item_changed',
            'collection_item_delete'
        ];

        if (!in_array($triggerType, $triggerTypes)) {
            throw new \Exception(sprintf('Invalid trigger type \'%s\'. Possible values are [%s]',
                $triggerType,
                implode(',', $triggerTypes)
            ));
        }

        return $this->post("/sites/{$siteId}/webhooks",  array_merge($defaults, [
            'triggerType' => $triggerType,
            'url' => $url,
            'filter' => $filter
        ]));
    }

    public function removeWebhook(string $siteId, string $webhookId)
    {
        return $this->delete("/sites/{$siteId}/webhooks/{$webhookId}");
    }

    // Collections
    public function collections(string $siteId)
    {
        return $this->get("/sites/{$siteId}/collections");
    }

    public function collection(string $collectionId)
    {
        return $this->get("/collections/{$collectionId}");
    }

    // Items
    public function items(string $collectionId, int $offset = 0, int $limit = 100)
    {
        $query = http_build_query([
        'offset' => $offset,
        'limit' => $limit,
        ]);
        return $this->get("/collections/{$collectionId}/items?{$query}");
    }

    public function itemsAll(string $collectionId): array
    {
        $response = $this->items($collectionId);
        $items = $response->items;
        $limit = $response->limit;
        $total = $response->total;
        $pages = ceil($total / $limit);
        for ($page = 1; $page < $pages; $page++) {
            $offset = $response->limit * $page;
            $items = array_merge($items, $this->items($collectionId, $offset, $limit)->items);
        }
        return $items;
    }

    public function item(string $collectionId, string $itemId)
    {
        return $this->get("/collections/{$collectionId}/items/{$itemId}");
    }

    public function createItem(string $collectionId, array $fields, bool $live = false)
    {
        $defaults = [
            "_archived" => false,
            "_draft" => false,
        ];

        return $this->post("/collections/{$collectionId}/items" . ($live ? "?live=true" : ""), [
            'fields' => array_merge($defaults, $fields),
        ]);
    }

    public function updateItem(string $collectionId, string $itemId, array $fields, bool $live = false)
    {
        return $this->put("/collections/{$collectionId}/items/{$itemId}" . ($live ? "?live=true" : ""), [
            'fields' => $fields,
        ]);
    }

    public function removeItem(string $collectionId, $itemId)
    {
        return $this->delete("/collections/{$collectionId}/items/{$itemId}");
    }

    public function findOrCreateItemByName(string $collectionId, array $fields)
    {
        if (!isset($fields['name'])) {
            throw new WebflowException('name');
        }
        $cacheKey = "collection-{$collectionId}-items";
        $instance = $this;
        $items = $this->cache($cacheKey, function () use ($instance, $collectionId) {
            return $instance->itemsAll($collectionId);
        });
        foreach ($items as $item) {
            if (strcasecmp($item->name, $fields['name']) === 0) {
                return $item;
            }
        }
        $newItem = $this->createItem($collectionId, $fields);
        $items[] = $newItem;
        $this->cacheSet($cacheKey, $items);
        return $newItem;
    }

    private function cache($key, callable $callback)
    {
        if (!isset($this->cache[$key])) {
            $this->cache[$key] = $callback();
        }
        return $this->cache[$key];
    }

    private function cacheSet($key, $value)
    {
        $this->cache[$key] = $value;
    }
}
