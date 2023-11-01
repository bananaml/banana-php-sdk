<?php

declare(strict_types=1);

namespace BananaDev;

use BananaDev\Contracts\APIContract;
use BananaDev\Responses\APIProjectResponse;
use BananaDev\Responses\APIProjectsResponse;
use Http\Discovery\Psr18Client;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;

class API implements APIContract
{
    const API_BASE_URL = 'https://api.banana.dev/v1';

    public function __construct(
        private readonly string $apiKey,
    ) {
        //
    }

    public function listProjects(array $query = []): APIProjectsResponse
    {
        $response = $this->call('GET', 'projects', $query);

        return new APIProjectsResponse(
            json: json_decode((string) $response->getBody(), true),
            statusCode: $response->getStatusCode(),
        );
    }

    public function getProject(string $projectId, array $query = []): APIProjectResponse
    {
        $response = $this->call('GET', 'projects/'.$projectId, $query);

        return new APIProjectResponse(
            json: json_decode((string) $response->getBody(), true),
            statusCode: $response->getStatusCode(),
        );
    }

    public function updateProject(string $projectId, array $data): APIProjectResponse
    {
        $response = $this->call('PUT', 'projects/'.$projectId, $data);

        return new APIProjectResponse(
            json: json_decode((string) $response->getBody(), true),
            statusCode: $response->getStatusCode(),
        );
    }

    private function call(string $method, string $route, array $data = [], array $headers = []): ResponseInterface
    {
        $client = new Psr18Client();

        $url = self::API_BASE_URL.'/'.trim($route, '/');

        if ($method == 'GET') {
            $url .= '?'.http_build_query($data);
        }

        $request = $client->createRequest($method, $url);

        $headers = array_merge([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-BANANA-API-KEY' => $this->apiKey,
            'X-BANANA-REQUEST-ID' => Uuid::uuid4()->toString(),
        ], $headers);

        foreach ($headers as $headerName => $headerValue) {
            $request = $request->withHeader($headerName, $headerValue);
        }

        if ($method == 'POST' || $method == 'PUT') {
            $request = $request->withBody($client->createStream(empty($data) ? '{}' : json_encode($data)));
        }

        return $client->sendRequest($request);
    }
}
