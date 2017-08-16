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
        'read' => -1,
        'write' => -1,
    ];

    public function stream_open($path, $mode, $options, &$opened_path)
    {
        $components = parse_url($path);

        static $query = [];

        if (isset($components['query'])) {
            parse_str($components['query'], $query);
        }

        $this->limits['read'] = (int) ($query['read_limit'] ?? -1);
        $this->limits['write'] = (int) ($query['write_limit'] ?? -1);

        return true;
    }

    public function stream_eof()
    {
        return $this->position >= strlen($this->stream);
    }

    public function stream_tell()
    {
        return $this->position;
    }

    public function stream_write($data)
    {
        if (!$this->canWrite()) {
            return false;
        }

        $left = substr($this->stream, 0, $this->position);

        $this->stream = "{$left}{$data}";
        $this->position += $bytesWritten = strlen($data);

        ++$this->writeCount;

        return $bytesWritten;
    }

    public function stream_read($count)
    {
        if (!$this->canRead()) {
            return false;
        }

        $data = substr($this->stream, $this->position, $count);

        if (false === $data) {
            return false;
        }

        $this->position += strlen($data);

        ++$this->readCount;

        return $data;
    }

    public function stream_seek($offset, $whence)
    {
        static $values = [SEEK_SET, SEEK_CUR, SEEK_END];

        if (in_array($whence, $values)) {
            if (SEEK_END === $whence) {
                $offset = strlen($this->stream) + $offset;
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
        $currentLength = strlen($this->stream);

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

    private function canRead(): bool
    {
        return $this->readCount >= $this->limits['read'];
    }

    private function canWrite(): bool
    {
        return $this->writeCount >= $this->limits['write'];
    }
}
