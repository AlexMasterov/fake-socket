<?php
declare(strict_types=1);

namespace FakeSocket;

interface StreamWrapper
{
    public function stream_eof();

    public function stream_tell();

    public function stream_write($data);

    public function stream_read($count);

    public function stream_seek($offset, $whence);

    public function stream_stat();

    public function stream_truncate($new_size);

    public function stream_set_option($option, $arg1, $arg2);
}
