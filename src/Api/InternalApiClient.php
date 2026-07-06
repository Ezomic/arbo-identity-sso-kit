<?php

namespace RobbinThijssen\IdentitySsoKit\Api;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

/**
 * Base for machine-to-machine calls into another app's internal API,
 * authenticated with a Sanctum personal access token — a separate
 * mechanism from the user-facing SSO JWT on purpose (long-lived service
 * credential vs. one-shot login handoff).
 */
abstract class InternalApiClient
{
    abstract protected function baseUrl(): string;

    abstract protected function token(): string;

    protected function get(string $uri, array $query = []): array
    {
        return $this->handle($this->http()->get($uri, $query), 'GET', $uri);
    }

    protected function post(string $uri, array $data = []): array
    {
        return $this->handle($this->http()->post($uri, $data), 'POST', $uri);
    }

    protected function put(string $uri, array $data = []): array
    {
        return $this->handle($this->http()->put($uri, $data), 'PUT', $uri);
    }

    protected function patch(string $uri, array $data = []): array
    {
        return $this->handle($this->http()->patch($uri, $data), 'PATCH', $uri);
    }

    protected function delete(string $uri, array $data = []): array
    {
        return $this->handle($this->http()->delete($uri, $data), 'DELETE', $uri);
    }

    protected function postFile(string $uri, UploadedFile $file, array $data = []): array
    {
        $request = $this->http()->attach('file', $file->getContent(), $file->getClientOriginalName());

        return $this->handle($request->post($uri, $data), 'POST', $uri);
    }

    protected function http(): PendingRequest
    {
        return Http::withToken($this->token())->baseUrl(rtrim($this->baseUrl(), '/').'/api');
    }

    private function handle(Response $response, string $method, string $uri): array
    {
        if ($response->failed()) {
            throw InternalApiException::fromResponse($method, $uri, $response->status(), $response->body());
        }

        return $response->json() ?? [];
    }
}
