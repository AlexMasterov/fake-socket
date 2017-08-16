<?php
declare(strict_types=1);

namespace FakeSocket;

use FakeSocket\StreamWrapper;
use InvalidArgumentException;

final class StreamException extends InvalidArgumentException
{
    public const INVALID_CLASS = 1;
    public const INVALID_DATA_SOURCE = 2;
    public const INVALID_PORT = 3;

    public static function invalidClass(string $spec): StreamException
    {
        $interface = StreamWrapper::class;

        return new static(
            "Stream class `{$spec}` must implement `{$interface}`",
            self::INVALID_CLASS
        );
    }

    public static function invalidDataSource(): StreamException
    {
        return new static(
            'Host or path are required',
            self::INVALID_DATA_SOURCE
        );
    }

    public static function invalidPortRange(int $minPort, int $maxPort): StreamException
    {
        return new static(
            "Port Number must be in the Range from {$minPort} to {$maxPort}",
            self::INVALID_PORT
        );
    }
}
