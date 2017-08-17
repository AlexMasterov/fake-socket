<?php
declare(strict_types=1);

namespace FakeSocket\Tests\Stream;

use FakeSocket\StreamFactory;
use FakeSocket\Tests\Stream\CanExtractWrapperStream;
use PHPUnit\Framework\TestCase;

final class BufferTest extends TestCase
{
    use CanExtractWrapperStream;

    /** @test */
    public function it_is_created_with_initial_values_correctly()
    {
        $url = StreamFactory::make('buffer')
            ->register();

        // Execute
        $stream = fopen($url, 'r+', false);

        $data = $this->getDataFromStream($stream);

        // Verify
        self::assertEmpty($data['stream']);
        self::assertSame(0, $data['position']);
        self::assertSame(0, $data['readCount']);
        self::assertSame(0, $data['writeCount']);
    }

    /** @test */
    public function it_writes_into_stream_correctly()
    {
        // Stub
        $data = 'xyz';

        $url = StreamFactory::make('buffer')
            ->withWrite()
            ->register();

        // Execute
        $stream = fopen($url, 'r+', false);
        $bytesWritten = fwrite($stream, $data);

        // Verify
        self::assertSame(strlen($data), $bytesWritten);
        self::assertSame($data, $this->getStreamBuffer($stream));
    }

    /** @test */
    public function it_read_from_stream_correctly()
    {
        // Stub
        $data = 'xyz';

        $url = StreamFactory::make('buffer')
            ->register();

        // Execute
        $stream = fopen($url, 'r+', false);

        fwrite($stream, $data);
        rewind($stream);

        $result = stream_get_contents($stream);

        // Verify
        self::assertSame($data, $result);
    }

    /** @test */
    public function it_read_eof_from_stream_correctly()
    {
        // Stub
        $data = 'xyz';

        $url = StreamFactory::make('buffer')
            ->register();

        // Execute
        $stream = fopen($url, 'r+', false);

        fwrite($stream, $data);
        fseek($stream, 10, SEEK_END);

        $result = fread($stream, strlen($data));

        // Verify
        self::assertEmpty($result);
    }

    /** @test */
    public function it_truncates_stream_correctly()
    {
        // Stub
        $data = 'xyz';
        $size = 2;
        $truncate = substr($data, 0, $size);

        $url = StreamFactory::make('buffer')
            ->register();

        // Execute
        $stream = fopen($url, 'r+', false);

        fwrite($stream, $data);
        $result = ftruncate($stream, $size);

        // Verify
        self::assertTrue($result);
        self::assertSame($truncate, $this->getStreamBuffer($stream));
    }

    /** @test */
    public function it_truncates_stream_with_large_size_correctly()
    {
        // Stub
        $data = 'xyz';
        $size = 10;
        $multiplier = $size - strlen($data);
        $truncate = $data . str_repeat(chr(0), $multiplier);

        $url = StreamFactory::make('buffer')
            ->register();

        // Execute
        $stream = fopen($url, 'r+', false);

        fwrite($stream, $data);
        $result = ftruncate($stream, $size);

        // Verify
        self::assertTrue($result);
        self::assertSame($truncate, $this->getStreamBuffer($stream));
    }

    /** @test */
    public function it_can_set_option()
    {
        // Stub
        $url = StreamFactory::make('buffer')
            ->register();

        // Execute
        $stream = fopen($url, 'r+', false);

        $result = stream_set_timeout($stream, 3, 0);

        // Verify
        self::assertTrue($result);
    }

    /** @test */
    public function it_can_only_write_n_times()
    {
        // Stub
        $data = 'xyz';

        $url = StreamFactory::make('buffer')
            ->withWrite(1)
            ->register();

        $stream = fopen($url, 'r+', false);

        // Execute
        $bytesWritten = fwrite($stream, $data);

        // Verify
        self::assertSame(strlen($data), $bytesWritten);

        // Execute
        $bytesWritten = fwrite($stream, $data);

        // Verify
        self::assertSame(0, $bytesWritten);
    }

    /** @test */
    public function it_can_only_read_n_times()
    {
        // Stub
        $data = 'xyz';

        $url = StreamFactory::make('buffer')
            ->withRead(1)
            ->register();

        $stream = fopen($url, 'r+', false);

        // Execute
        fwrite($stream, $data);
        rewind($stream);

        $result = stream_get_contents($stream);

        // Verify
        self::assertSame($data, $result);

        // Execute
        $result = stream_get_contents($stream);

        // Verify
        self::assertEmpty($result);
    }

    /** @test */
    public function it_can_only_write_every_n_times()
    {
        // Stub
        $data = 'xyz';

        $url = StreamFactory::make('buffer')
            ->withWriteEvery(2)
            ->register();

        $stream = fopen($url, 'r+', false);

        // Execute
        $bytesWritten = fwrite($stream, $data);

        // Verify
        self::assertSame(0, $bytesWritten);

        // Execute
        $bytesWritten = fwrite($stream, $data);

        // Verify
        self::assertSame(strlen($data), $bytesWritten);

        // Execute
        $bytesWritten = fwrite($stream, $data);

        // Verify
        self::assertSame(0, $bytesWritten);
    }
}
