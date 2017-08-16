<?php
declare(strict_types=1);

namespace FakeSocket\Tests\Stream;

use FakeSocket\StreamFactory;
use PHPUnit\Framework\TestCase;

final class BufferTest extends TestCase
{
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
    public function it_writes_in_stream_correctly()
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

    private function getDataFromStream(/** resource */ $stream): array
    {
        ['wrapper_data' => $wrapperData] = stream_get_meta_data($stream);

        $getData = function () {
            return get_object_vars($this);
        };

        return $getData->call($wrapperData);
    }

    private function getStreamBuffer(/** resource */ $stream): string
    {
        ['stream' => $stream] = $this->getDataFromStream($stream);

        return $stream;
    }
}
