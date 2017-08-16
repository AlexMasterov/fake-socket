<?php
declare(strict_types=1);

namespace FakeSocket;

trait CanCopy
{
    public function copy(string $name, $value): self
    {
        $clone = clone $this;
        $clone->{$name} = $value;

        return $clone;
    }
}
