<?php

declare(strict_types=1);

namespace Project\Helpers;

final class Helpers
{
    public static function rootNamespacePart(string $className): string
    {
        return explode('\\', $className, 2)[0] ?? '';
    }

    public static function shortClassName(string $className): string
    {
        $parts = explode('\\', $className);

        return array_pop($parts);
    }
}
