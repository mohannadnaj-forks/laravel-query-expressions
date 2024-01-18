<?php

declare(strict_types=1);

namespace Tpetry\QueryExpressions\Function\Date;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Grammar;
use Illuminate\Database\Query\Expression as QueryExpression;
use Tpetry\QueryExpressions\Concerns\IdentifiesDriver;
use Tpetry\QueryExpressions\Concerns\StringizeExpression;
use Tpetry\QueryExpressions\Function\String\Concat;

class DateFormat implements Expression
{
    use DirectDateFormatTrait;
    use EmulatedDateFormatTrait;
    use IdentifiesDriver;
    use StringizeExpression;

    /**
     * @var array<string>
     */
    protected array $unsupportedCharacters = [
        'B',
        'c',
        'e',
        'I',
        'L',
        'N',
        'O',
        'P',
        'p',
        'r',
        'S',
        'T',
        'u',
        'v',
        'X',
        'x',
        'z',
        'Z',
    ];

    public function __construct(
        private readonly string|Expression $expression,
        private readonly string $format
    ) {
    }

    public function getValue(Grammar $grammar): string
    {
        $expressions = $this->buildExpressions($grammar);

        return $this->concatenateExpressions($expressions, $grammar);
    }

    /**
     * @return non-empty-array<int,Expression>
     */
    protected function buildExpressions(Grammar $grammar): array
    {
        $characters = $this->getFormatCharacters();

        /** @var non-empty-array<string|Expression> $expressions */
        $expressions = array_map(function (string $character) use ($grammar) {
            $emulatedCharacter = $this->getEmulatableCharacter($grammar, $character);
            $formatCharacter = $this->formatCharacters[$this->identify($grammar)][$character] ?? null;

            if ($emulatedCharacter) {
                return $this->getEmulatedExpression($grammar, $emulatedCharacter);
            }

            if ($formatCharacter) {
                return $formatCharacter;
            }

            return $character;
        }, $characters);

        return $this->processExpressions($expressions, $grammar);
    }

    /**
     * @param  non-empty-array<string|Expression>  $expressions
     * @return non-empty-array<int,Expression>
     */
    protected function processExpressions(array $expressions, Grammar $grammar): array
    {
        $expressions = array_reduce(array_keys($expressions), function (array $expressions, int $index) use ($grammar) {
            if (is_string($expressions[$index]) && str_starts_with($expressions[$index], '\\')) {
                $expressions[$index] = new QueryExpression(
                    $grammar->quoteString(stripslashes($expressions[$index]))
                );
            }

            if (
                is_string($expressions[$index]) && is_string($expressions[$index + 1] ?? null)
            ) {
                $expressions[$index + 1] = $expressions[$index].$expressions[$index + 1];
                unset($expressions[$index]);
            }

            return $expressions;
        }, $expressions);

        $expressions = array_values(array_map(function (string|Expression $expression) use ($grammar) {
            if (is_string($expression) && mb_strlen($expression) == 1) {
                return new QueryExpression(
                    $grammar->quoteString(stripslashes($expression))
                );
            }

            if (is_string($expression)) {
                return $this->getDirectDateFormat($grammar, $expression);
            }

            return $expression;
        }, $expressions));

        /** @var non-empty-array<int,Expression> $expressions */
        return $expressions;
    }

    /**
     * @param  non-empty-array<Expression>  $expressions
     */
    protected function concatenateExpressions(array $expressions, Grammar $grammar): string
    {
        if (count($expressions) == 1) {
            return (string) $expressions[0]->getValue($grammar);
        }

        return (new Concat($expressions))->getValue($grammar);
    }

    /**
     * @return array<string>
     */
    protected function getFormatCharacters(): array
    {
        $characters = str_split($this->format);

        $characters = array_reduce(array_keys($characters), function (array $characters, int $index) {
            if ($characters[$index] == '\\') {
                $characters[$index + 1] = $characters[$index].($characters[$index + 1] ?? null);
                unset($characters[$index]);
            }

            return $characters;
        }, $characters);

        array_walk($characters, function (string $character) {
            if (in_array($character, $this->unsupportedCharacters)) {
                throw new \InvalidArgumentException(sprintf(
                    'Unsupported format character: %s',
                    $character,
                ));
            }
        });

        return $characters;
    }
}
