<?php

declare(strict_types=1);

namespace {{ namespace }}\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array<string, mixed> example(array<string, mixed> $payload = [])
 * @method static array<string, mixed> manifest()
 *
 * @see \{{ namespace }}\Plugin
 */
final class {{ plugin }} extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return '{{ package }}';
    }
}
