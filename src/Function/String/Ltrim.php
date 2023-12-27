<?php

declare(strict_types=1);

namespace Tpetry\QueryExpressions\Function\String;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Grammar;
use Tpetry\QueryExpressions\Concerns\IdentifiesDriver;
use Tpetry\QueryExpressions\Concerns\StringizeExpression;

class Ltrim implements Expression
{
    use IdentifiesDriver;
    use StringizeExpression;

    public function __construct(
        private readonly string|Expression $expression,
        private readonly string $characters = ' ',
    ) {
    }

    public function getValue(Grammar $grammar): string
    {
        $expression = $this->stringize($grammar, $this->expression);
        $characters = $grammar->quoteString($this->characters);

        return match ($this->identify($grammar)) {
            'mysql' => "LTRIM($expression, {$characters})",
            'sqlite' => "LTRIM($expression, {$characters})",
            'pgsql' => "LTRIM($expression, {$characters})",
            'sqlsrv' => "LTRIM($expression, {$characters})",
        };
    }
}
