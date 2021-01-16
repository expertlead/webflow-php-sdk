# PHP SDK for the Webflow CMS API

[![Build Status](https://travis-ci.com/expertlead/webflow-php-sdk.svg?branch=master)](https://travis-ci.com/expertlead/webflow-php-sdk)

Implementation based on [Webflow CMS API Reference](https://developers.webflow.com/#cms-api-reference)

## Features implemented
- Get Current Authorization Info
- List Sites
- Get Specific Site
- Publish Site
- List Domains
- List Collections
- Get Collection with Full Schema
- **Get All Items for a Collection (including paginated results)**
- **Find one or Create Item by Name**
- Get Single Item
- Create New Collection Item
- Update Collection Item
- Patch Collection Item
- Remove Collection Item

## Usage

Check https://university.webflow.com/article/using-the-webflow-cms-api on how to generate `YOUR_WEBFLOW_API_TOKEN`

### Get Current Authorization Info
```
$webflow = new \Webflow\Api('YOUR_WEBFLOW_API_TOKEN');
$webflow->info();
```

### List Sites
```
$webflow->sites();
```

### Get Specific Site
```
$webflow->site($siteId);
```

### List Domains
```
$webflow->domains($siteid);
```
__If you only have a webflow.io domain this will return an empty array []__


### List Collections
```
$webflow->collections($siteid);
```

### Get All Items for a Collection (including paginated results)
```
$webflow->itemsAll($collectionId);
```
### Get Single Item
```
$webflow->item($collectionId, $itemId);
```

### Create New Collection Item
```
$fields = [
    'name' => 'New item created via API',
    # ...
];
$webflow->createItem($collectionId, $fields);
```

### Update Collection Item
```
$webflow->updateItem($collectionId, $itemId, $fields);
```

### Remove Collection Item
```
$webflow->removeItem($collectionId, $itemId);
```

### Publish Site
Before changes go live, you must publish them to your domains.
```
$webflow->publishSite($siteId,['domains' => ['mydomain.webflow.io', 'mycustomdomain.example.com']]);
```



## Installation

```
# Install Composer
composer require expertlead/webflow-php-sdk
```
No extra dependencies! You are welcome ;)
