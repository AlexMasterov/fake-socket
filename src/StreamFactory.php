<?php
declare(strict_types=1);

namespace FakeSocket;

use FakeSocket\{
    CanCopy,
    StreamException,
    StreamWrapper,
    Stream\Buffer
};
use InvalidArgumentException;

final class StreamFactory
{
    use CanCopy;

    /** @const int */
    private const MIN_PORT = 1;

    /** @const int 16bit */
    private const MAX_PORT = 65535;

    /** @var string */
    private $protocol;

    /** @var string */
    private $spec;

    /** @var string */
    private $host;

    /** @var int */
    private $port;

    /** @var int */
    private $read;

    /** @var int */
    private $readAfter;

    /** @var int */
    private $readEvery;

    /** @var int */
    private $write;

    /** @var int */
    private $writeAfter;

    /** @var int */
    private $writeEvery;

    public static function make(string $dataSourceName): self
    {
        $components = parse_url($dataSourceName);

        $host = $components['host'] ?? $components['path'];

        if (!isset($host)) {
            throw StreamException::invalidDataSource();
        }

        static $defaultSpec = Buffer::class;

        $factory = new self($defaultSpec);
        $factory->protocol = $components['scheme'] ?? 'fake';
        $factory->host = $host;

        if (isset($components['port'])) {
            $factory = $factory->withPort($components['port']);
        }

        return $factory;
    }

    public function __construct(string $spec)
    {
        if (!is_subclass_of($spec, StreamWrapper::class)) {
            throw StreamException::invalidClass($spec);
        }

        $this->spec = $spec;
    }

    public function register(): string
    {
        if (in_array($this->protocol, stream_get_wrappers())) {
            stream_wrapper_unregister($this->protocol);
        }

        stream_wrapper_register($this->protocol, $this->spec);

        if (isset($this->port)) {
            $this->host = "{$this->host}:{$this->port}";
        }

        $query = [
            'read'        => $this->read,
            'read_after'  => $this->readAfter,
            'read_every'  => $this->readEvery,
            'write'       => $this->write,
            'write_after' => $this->writeAfter,
            'write_every' => $this->writeEvery,
        ];

        $query = http_build_query($query);
        empty($query) ?: $query = "/?$query";

        return "{$this->protocol}://{$this->host}{$query}";
    }

    public function withProtocol(string $protocol): self
    {
        return $this->copy('protocol', $protocol);
    }

    public function withHost(string $host): self
    {
        return $this->copy('host', $host);
    }

    public function withPort(int $port): self
    {
        if ($port < self::MIN_PORT || $port > self::MAX_PORT) {
            throw StreamException::invalidPortRange(
                self::MIN_PORT,
                self::MAX_PORT
            );
        }

        return $this->copy('port', $port);
    }

    public function withRead(int $limit = -1): self
    {
        return $this->copy('read', $limit);
    }

    public function withWrite(int $limit = -1): self
    {
        return $this->copy('write', $limit);
    }

    public function withoutRead(): self
    {
        return $this->copy('read', 0);
    }

    public function withoutWrite(): self
    {
        return $this->copy('write', 0);
    }

    public function withReadAfter(int $limit = 0): self
    {
        return $this->copy('readAfter', $limit);
    }

    public function withWriteAfter(int $limit = 0): self
    {
        return $this->copy('writeAfter', $limit);
    }

    public function withReadEvery(int $limit = 1): self
    {
        return $this->copy('readEvery', $limit);
    }

    public function withWriteEvery(int $limit = 1): self
    {
        return $this->copy('writeEvery', $limit);
    }
}
