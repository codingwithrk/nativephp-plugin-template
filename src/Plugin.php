<?php

declare(strict_types=1);

namespace {{ namespace }};

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use {{ namespace }}\Contracts\{{ plugin }}Contract;

final class Plugin implements {{ plugin }}Contract
{
    public function __construct(
        private readonly Application $app,
    ) {
    }

    public function example(array $payload = []): array
    {
        return $this->callBridge('{{ plugin }}.Example', $payload);
    }

    public function manifest(): array
    {
        $path = dirname(__DIR__).DIRECTORY_SEPARATOR.'nativephp.json';

        if (! File::exists($path)) {
            throw new InvalidArgumentException('The {{ vendor }}/{{ package }} NativePHP manifest is missing.');
        }

        /** @var array<string, mixed> $manifest */
        $manifest = json_decode((string) File::get($path), true, flags: JSON_THROW_ON_ERROR);

        return $manifest;
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private function callBridge(string $function, array $payload): array
    {
        $bridge = $this->app->make('nativephp.mobile.bridge');

        /** @var mixed $response */
        $response = $bridge->call($function, $payload);

        if (is_array($response)) {
            /** @var array<string, mixed> $response */
            return $response;
        }

        return [
            'value' => $response,
        ];
    }
}
