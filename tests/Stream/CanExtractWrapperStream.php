<?php
declare(strict_types=1);

namespace FakeSocket\Tests\Stream;

trait CanExtractWrapperStream
{
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
