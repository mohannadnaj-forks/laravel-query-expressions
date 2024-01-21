<?php

declare(strict_types=1);

namespace Tpetry\QueryExpressions\Value;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Grammar;

class Number implements Expression
{
    public function __construct(
        private readonly int|float $value,
    ) {
    }

    public function getValue(Grammar $grammar): string
    {
        return (string) $this->value;
    }

    public static function from(int|float $value): self
    {
        return new self($value);
    }
}
