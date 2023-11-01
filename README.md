# Banana.dev PHP SDK

## Using it

Refer to our [main docs](https://docs.banana.dev) for running inference with your AI apps on Banana.dev.
Refer to our [API docs](https://docs.api.banana.dev) for interacting with the API.

To get started:

```sh
composer require banana-dev/banana-dev
```

```php
<?php

use BananaDev/Client;
use BananaDev/API;

$client = new Client("your api key", "https://project-slug.run.banana.dev");
$api = new API("your api key");
```

## Testing

```sh
mkdir example
cd example
composer init --name=banana-dev/example --description="" --author="" --autoload=src/ --repository='{"type":"path","url":"../"}' --license="" --require="banana-dev/banana-dev @dev" --require="guzzlehttp/guzzle:^7.8" --stability=dev --no-interaction
```

Say yes to the discovery prompt.
```sh
composer install
```

Create a `test.php` within `example/src` with your own API key.
```php
<?php

require __DIR__ . '/../vendor/autoload.php';

use BananaDev\API;

$api = new API("11111111-1111-1111-1111-111111111111");

$projects = $api->listProjects();
var_dump($projects);

$project = $api->getProject($projects->json['results'][0]['id']);
var_dump($project);

$updated = $api->updateProject($project->json['id'], [
    'maxReplicas' => 3,
]);
var_dump($updated);
```

## Development

Install Homebrew

```sh
curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh
```

Install PHP and Composer

```sh
brew install php composer
```

Install dependencies

```sh
composer install
```

Run tests

```sh
composer test
```

Format code
```sh
composer format
```
