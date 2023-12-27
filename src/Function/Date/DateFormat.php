<?php

declare(strict_types=1);

namespace Tpetry\QueryExpressions\Function\Date;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Grammar;
use Tpetry\QueryExpressions\Concerns\IdentifiesDriver;
use Tpetry\QueryExpressions\Concerns\StringizeExpression;
use Tpetry\QueryExpressions\Function\String\Concat;
use Tpetry\QueryExpressions\Value\Value;

class DateFormat implements Expression
{
    use DirectDateFormatTrait;
    use EmulatedDateFormatTrait;
    use IdentifiesDriver;
    use StringizeExpression;

    public function __construct(
        private readonly string|Expression $expression,
        private readonly string $format
    ) {
    }

    public function getValue(Grammar $grammar): string
    {
        /** @var non-empty-array<int, Expression> $expressions */
        $expressions = [];

        $characters = $this->getFormatCharacters();

        foreach ($characters as $character) {
            $emulatedCharacter = $this->getEmulatableCharacter($grammar, $character);
            $formatCharacter = $this->formatCharacters[$this->identify($grammar)][$character] ?? null;

            if ($emulatedCharacter) {
                $expressions[] = $this->getEmulatedExpression($grammar, $emulatedCharacter);
            } elseif ($formatCharacter) {
                $expressions[] = $this->getDateFormatExpression($grammar, $character);
            } else {
                $expressions[] = $this->getCharacterExpression($character);
            }
        }

        return count($expressions) == 1 ?
            (string) $expressions[0]->getValue($grammar) : (new Concat($expressions))->getValue($grammar);
    }

    protected function getCharacterExpression(string $character): Expression
    {
        $isEscaped = str_starts_with($character, '\\') && strlen($character) > 1;

        return new Value(
            $isEscaped ? substr($character, 1) : $character,
        );
    }

    /**
     * @return array<int, string>
     */
    protected function getFormatCharacters(): array
    {
        $characters = str_split($this->format);

        return array_reduce(
            array_keys($characters),
            function (array $characters, int $index) {
                if ($characters[$index] == '\\') {
                    $characters[$index + 1] = $characters[$index].($characters[$index + 1] ?? null);
                    unset($characters[$index]);
                }

                return $characters;
            },
            $characters
        );
    }
}
