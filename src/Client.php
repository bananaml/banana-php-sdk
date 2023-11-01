<?php

declare(strict_types=1);

namespace BananaDev;

use BananaDev\Contracts\ClientContract;
use BananaDev\Exceptions\ClientException;
use Http\Discovery\Psr18Client;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;

class Client implements ClientContract
{
    const MAX_BACKOFF_INTERVAL = 3000;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $url,
        private readonly string $verbosity = 'DEBUG',
    ) {
        //
    }

    public function warmup(): void
    {
        $this->call('/_k/warmup', [], [], new ClientOptions(retry: false));
    }

    public function call(string $route, array $json = [], array $headers = [], ClientOptions $options = null): ClientResponse
    {
        if (! $options) {
            $options = new ClientOptions();
        }

        $endpoint = $this->url.'/'.ltrim($route, '/');

        $headers = array_merge([
            'Content-Type' => 'application/json',
            'X-BANANA-API-KEY' => $this->apiKey,
            'X-BANANA-REQUEST-ID' => Uuid::uuid4()->toString(),
        ], $headers);

        $backoffIntervalMs = 100;
        $start = time();
        $firstCall = true;

        while (true) {
            if (time() - $start > $options->retryTimeoutMs) {
                throw new ClientException('Retry timeout exceeded');
            }

            if (! $firstCall) {
                if ($this->verbosity === 'DEBUG') {
                    error_log('Retrying...');
                }
            }

            $backoffIntervalMs = min($backoffIntervalMs * 2, self::MAX_BACKOFF_INTERVAL); // at most wait MAX_BACKOFF_INTERVAL ms

            $response = $this->makeRequest($endpoint, $json, $headers);

            $status = $response->getStatusCode();
            $body = (string) $response->getBody();

            if ($this->verbosity === 'DEBUG' && $status !== 200) {
                error_log('Status code: '.$status.PHP_EOL);
                error_log($body.PHP_EOL);
            }

            // success case -> return json and metadata
            if ($status === 200) {
                try {
                    $json = json_decode($body, true);

                    return new ClientResponse($status, $json, $response->getHeaders());
                } catch (\Exception $e) {
                    throw new ClientException($body);
                }
            }

            // user at their quota -> retry
            elseif ($status === 400) { // user at their quota, retry
                if (! $options->retry) {
                    throw new ClientException($body);
                }
                usleep($backoffIntervalMs * 1000);

                continue;
            }

            // bad auth || endpoint doesn't exist || payload too large -> throw
            elseif ($status === 401 || $status === 404 || $status === 413) {
                throw new ClientException($body);
            }

            // banana is a teapot -> throw
            elseif ($status === 418) {
                throw new ClientException('banana is a teapot');
            }

            // potassium threw locked error -> retry
            elseif ($status === 423) {
                if (! $options->retry) {
                    $message = $body.PHP_EOL;
                    $message .= '423 errors are returned by Potassium when your server(s) are all busy handling GPU endpoints.'.PHP_EOL;
                    $message .= 'In most cases, you just want to retry later. Running $client->call() with the retry=true argument handles this for you.';
                    throw new ClientException($message);
                }
                usleep($backoffIntervalMs * 1000);

                continue;
            }

            // user's server had an unrecoverable error -> throw
            elseif ($status === 500) {
                throw new ClientException($body);
            }

            // banana had a temporary error -> retry
            elseif ($status === 503) { // banana had a temporary error, retry
                if (! $options->retry) {
                    throw new ClientException($body);
                }
                usleep($backoffIntervalMs * 1000);

                continue;
            }

            // gateway timeout -> throw
            elseif ($status === 504) {
                $message = 'Reached request timeout limit. To avoid this we recommend using an app.background() handler in your Potassium app.';
                throw new ClientException($message);
            }

            // unexpected status code -> throw
            else {
                throw new ClientException('Unexpected HTTP response code: '.$status);
            }
        }
    }

    private function makeRequest(string $url, array $data, array $headers): ResponseInterface
    {
        $client = new Psr18Client();

        $request = $client->createRequest('POST', $url);

        foreach ($headers as $headerName => $headerValue) {
            $request = $request->withHeader($headerName, $headerValue);
        }

        $request = $request->withBody($client->createStream(empty($data) ? '{}' : json_encode($data)));

        return $client->sendRequest($request);
    }
}
