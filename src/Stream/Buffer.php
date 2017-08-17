<?php
declare(strict_types=1);

namespace FakeSocket\Stream;

use FakeSocket\StreamWrapper;

final class Buffer implements StreamWrapper
{
    /** @var string */
    private $stream = '';

    /** @var int */
    private $position = 0;

    /** @var int */
    private $readCount = 0;

    /** @var int */
    private $writeCount = 0;

    /** @var array */
    private $limits = [
        'read'        => -1,
        'read_after'  => 0,
        'read_every'  => 1,
        'write'       => -1,
        'write_after' => 0,
        'write_every' => 1,
    ];

    public function stream_open($path, $mode, $options, &$opened_path)
    {
        $components = parse_url($path);

        static $query = [];

        if (isset($components['query'])) {
            parse_str($components['query'], $query);
        }

        $this->configureLimits($query);

        return true;
    }

    public function stream_eof()
    {
        return $this->position >= mb_strlen($this->stream);
    }

    public function stream_tell()
    {
        return $this->position;
    }

    public function stream_write($data)
    {
        ++$this->writeCount;

        if (!$this->canWrite()) {
            return false;
        }

        $left = mb_substr($this->stream, 0, $this->position);

        $this->stream = "{$left}{$data}";
        $this->position += $bytesWritten = mb_strlen($data);

        return $bytesWritten;
    }

    public function stream_read($count)
    {
        ++$this->readCount;

        if (!$this->canRead()) {
            return false;
        }

        $data = mb_substr($this->stream, $this->position, $count);

        if (false === $data) {
            return false;
        }

        $this->position += mb_strlen($data);

        return $data;
    }

    public function stream_seek($offset, $whence)
    {
        static $values = [SEEK_SET, SEEK_CUR, SEEK_END];

        if (in_array($whence, $values)) {
            if (SEEK_END === $whence) {
                $offset = mb_strlen($this->stream) + $offset;
            }

            if ($offset >= 0) {
                $this->position = $offset;
                return true;
            }
        }

        return false;
    }

    public function stream_stat()
    {
        return [];
    }

    public function stream_truncate($new_size)
    {
        $currentLength = mb_strlen($this->stream);

        if ($new_size > $currentLength) {
            $multiplier = $new_size - $currentLength;
            $this->stream .= str_repeat(chr(0), $multiplier);

            return true;
        }

        $this->stream = substr($this->stream, 0, $new_size);

        return true;
    }

    public function stream_set_option($option, $arg1, $arg2)
    {
        return true;
    }

    private function configureLimits(array $query): void
    {
        foreach (array_keys($this->limits) as $limit) {
            if (!isset($query[$limit])) {
                continue;
            }

            if (false !== strrpos($limit, 'every')) {
                $query[$limit] = max(1, (int) $query[$limit]);
            }

            $this->limits[$limit] = (int) $query[$limit];
        }
    }

    private function canRead(): bool
    {
        $noLimit = $this->readCount !== $this->limits['read'] + 1;

        return $noLimit
            && $this->readCount > $this->limits['read_after']
            && $this->readCount % $this->limits['read_every'] === 0;
    }

    private function canWrite(): bool
    {
        $noLimit = $this->writeCount !== $this->limits['write'] + 1;

        return $noLimit
            && $this->writeCount > $this->limits['write_after']
            && $this->writeCount % $this->limits['write_every'] === 0;
    }
}
