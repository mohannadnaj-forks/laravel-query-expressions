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
    use IdentifiesDriver;
    use StringizeExpression;

    /**
     * @var array<'mysql'|'sqlite'|'pgsql'|'sqlsrv', array<string, string>>
     */
    protected array $formatCharacters = [
        'mysql' => [
            'Y' => '%Y',
            'y' => '%y',
            'o' => '%x',
            'M' => '%b',
            'm' => '%m',
            'n' => '%c',
            'F' => '%M',
            'D' => '%a',
            'd' => '%d',
            'l' => '%W',
            'j' => '%e',
            'W' => '%v',
            'H' => '%H',
            'h' => '%h',
            'i' => '%i',
            's' => '%s',
            'A' => '%p',
        ],
        'sqlite' => [
            'Y' => '%Y',
            // No format for 3 letter month name.
            'M' => '%m',
            'm' => '%m',
            // No format for month number without leading zeros.
            'n' => '%m',
            // No format for full month name.
            'F' => '%m',
            // No format for 3 letter day name.
            'D' => '%d',
            'd' => '%d',
            // No format for full day name.
            'l' => '%d',
            // no format for day of month number without leading zeros.
            'j' => '%d',
            'W' => '%W',
            'H' => '%H',
            // No format for 12 hour with leading zeros.
            'h' => '%H',
            'i' => '%M',
            's' => '%S',
            // No format for AM/PM.
            // 'A' => ''.
        ],
        'pgsql' => [
            'Y' => 'YYYY',
            'y' => 'YY',
            'M' => 'Mon',
            'n' => 'FMMM',
            'm' => 'MM',
            'F' => 'Month',
            'D' => 'Dy',
            'd' => 'DD',
            'l' => 'Day',
            // No format for Day of the month without leading zeros.
            'j' => 'DD',
            'W' => 'IW',
            'H' => 'HH24',
            'h' => 'HH12',
            'i' => 'MI',
            's' => 'SS',
            'A' => 'AM',
        ],
        'sqlsrv' => [
            'A' => 'tt',
            'd' => 'dd',
            'F' => 'MMM',
            'h' => 'hh',
            'H' => 'HH',
            'i' => 'mm',
            'm' => 'MM',
            // No format for 3 letter month name.
            'M' => 'MMM',
            's' => 'ss',
            'Y' => 'yyyy',
        ],
    ];

    /**
     * @var array<'mysql'|'sqlite'|'pgsql'|'sqlsrv', array<string, string>>
     */
    protected array $emulatableCharacters = [
        'mysql' => [
            'U' => 'unix_timestamp(%s)',
        ],
        'sqlite' => [
            'y' => 'substr(strftime(\'%Y\', %s), 3, 2)',
            'U' => 'strftime(\'%%s\', %s)',
        ],
        'pgsql' => [
            'U' => 'extract(epoch from %s)',
        ],
        'sqlsrv' => [
            'n' => 'cast(month(%s) as varchar(2))',
            'U' => 'datediff(second, \'1970-01-01\', %s)',
        ],
    ];

    public function __construct(
        private readonly string|Expression $expression,
        private readonly string $format
    ) {
    }

    public function getValue(Grammar $grammar): string
    {
        /** @var non-empty-array<int, Expression> $expressions */
        $expressions = [];

        $lastFormatExpression = '';

        foreach (str_split($this->format) as $index => $character) {
            $emulatableCharacter = $this->emulatableCharacters[$this->identify($grammar)][$character] ?? null;
            $formatCharacter = $this->formatCharacters[$this->identify($grammar)][$character] ?? null;
            $styleCharacter = ! $emulatableCharacter && ! $formatCharacter ? $character : null;

            if ($styleCharacter || $formatCharacter) {
                $lastFormatExpression .= $formatCharacter ?? $styleCharacter;
            }

            if (
                $lastFormatExpression !== '' && (
                    $emulatableCharacter || $index === strlen($this->format) - 1
                )) {
                $expressions[] = $this->getDateFormatExpression(
                    $grammar,
                    (string) $this->stringize($grammar, $this->expression),
                    $lastFormatExpression,
                );
            }

            if ($emulatableCharacter) {
                $expressions[] = new QueryExpression(sprintf(
                    $emulatableCharacter,
                    $this->stringize($grammar, $this->expression)
                ));
            }
        }

        if (count($expressions) == 1) {
            return (string) $expressions[0]->getValue($grammar);
        }

        return (new Concat($expressions))->getValue($grammar);
    }

    protected function getDateFormatExpression(Grammar $grammar, string $expression, string $format): Expression
    {
        return match ($this->identify($grammar)) {
            'mysql' => new QueryExpression("date_format({$expression}, '{$format}')"),
            'sqlite' => new QueryExpression("strftime('{$format}', {$expression})"),
            'pgsql' => new QueryExpression("to_char({$expression}, '{$format}')"),
            'sqlsrv' => new QueryExpression("format({$expression}, '{$format}')"),
        };
    }
}
