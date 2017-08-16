<?php
declare(strict_types=1);

namespace FakeSocket\Tests;

use FakeSocket\{
    StreamException,
    StreamFactory
};
use PHPUnit\Framework\TestCase;

final class StreamFactoryTest extends TestCase
{
    /**
     * @test
     * @dataProvider newCopyMethodData
     */
    public function it_is_immutable(string $method, ...$arguments)
    {
        $factory = StreamFactory::make('buffer');

        // Execute
        $newFactory = $factory->{$method}(...$arguments);

        // Verify
        self::assertNotSame($factory, $newFactory);
    }

    public function newCopyMethodData()
    {
        return [
            ['withProtocol', 'fake'],
            ['withHost', 'buffer'],
            ['withPort', 80],
            ['withRead', 3],
            ['withWrite', 3],
            ['withoutRead'],
            ['withoutWrite'],
        ];
    }

    /** @test */
    public function it_generates_registration_url_correctly()
    {
        // Stub
        $protocol = 'fake';
        $host = 'buffer';
        $port = 9200;
        $query = [
            'read_limit' => 0,
            'write_limit' => 0,
        ];

        // Execute
        $url = StreamFactory::make('buffer')
            ->withProtocol($protocol)
            ->withHost($host)
            ->withPort($port)
            ->withoutRead()
            ->withoutWrite()
            ->register();

        // Verify
        self::assertSame($protocol, parse_url($url, PHP_URL_SCHEME));
        self::assertSame($host, parse_url($url, PHP_URL_HOST));
        self::assertSame($port, parse_url($url, PHP_URL_PORT));

        $urlQuery = parse_url($url, PHP_URL_QUERY);
        parse_str($urlQuery, $actualQuery);

        self::assertArraySubset($query, $actualQuery);
    }

    /** @test */
    public function it_create_stream_correctly()
    {
        $url = StreamFactory::make('buffer')
            ->register();

        // Execute
        $stream = fopen($url, 'r+', false);

        // Verify
        self::assertTrue(is_resource($stream));
    }

    /** @test */
    public function it_throws_exception_when_dsn_is_invalid()
    {
        // Verify
        self::expectException(StreamException::class);
        self::expectExceptionCode(StreamException::INVALID_DATA_SOURCE);

        // Execute
        StreamFactory::make(':90');
    }

    /** @test */
    public function it_throws_exception_when_class_is_invalid()
    {
        // Verify
        self::expectException(StreamException::class);
        self::expectExceptionCode(StreamException::INVALID_CLASS);

        // Execute
        new StreamFactory(\stdClass::class);
    }

    /**
     * @test
     * @dataProvider invalidPortData
     */
    public function it_throws_exception_when_port_is_invalid(int $port)
    {
        // Verify
        self::expectException(StreamException::class);
        self::expectExceptionCode(StreamException::INVALID_PORT);

        // Execute
        StreamFactory::make('buffer')
            ->withPort($port);
    }

    public function invalidPortData()
    {
        return [
            [-1],
            [0],
            [65536],
        ];
    }

    /** @test */
    public function it_is_registered_successfully()
    {
        // Execute
        $url = StreamFactory::make('buffer')
            ->register();

        $protocol = parse_url($url, PHP_URL_SCHEME);

        // Verify
        self::assertContains($protocol, stream_get_wrappers());
    }
}
