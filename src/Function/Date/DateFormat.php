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
            'A' => '%p',
            'D' => '%a',
            'd' => '%d',
            'F' => '%M',
            'H' => '%H',
            'h' => '%h',
            'i' => '%i',
            'j' => '%e',
            'l' => '%W',
            'M' => '%b',
            'm' => '%m',
            'n' => '%c',
            'o' => '%x',
            's' => '%s',
            'W' => '%v',
            'Y' => '%Y',
            'y' => '%y',
        ],
        'sqlite' => [
            'd' => '%d',
            'H' => '%H',
            'i' => '%M',
            'm' => '%m',
            's' => '%S',
            'U' => '%s',
            'Y' => '%Y',
        ],
        'pgsql' => [
            'A' => 'AM',
            'd' => 'DD',
            'D' => 'Dy',
            'h' => 'HH12',
            'H' => 'HH24',
            'i' => 'MI',
            'j' => 'FMDD',
            'm' => 'MM',
            'M' => 'Mon',
            'n' => 'FMMM',
            's' => 'SS',
            'W' => 'IW',
            'y' => 'YY',
            'Y' => 'YYYY',
        ],
        'sqlsrv' => [
            'A' => 'tt',
            'd' => 'dd',
            'D' => 'ddd',
            'h' => 'hh',
            'H' => 'HH',
            'i' => 'mm',
            'm' => 'MM',
            's' => 'ss',
            'Y' => 'yyyy',
        ],
    ];

    /**
     * @var array<'mysql'|'sqlite'|'pgsql'|'sqlsrv', array<string, string>>
     */
    protected array $emulatableCharacters = [
        'mysql' => [
            'A' => '(CASE WHEN HOUR(%s) < 12 THEN \'AM\' ELSE \'PM\' END)',
            'a' => '(CASE WHEN hour(%s) < 12 THEN \'am\' ELSE \'pm\' END)',
            'g' => 'HOUR(%s) %% 12',
            'G' => 'HOUR(%s)',
            't' => 'DAY(LAST_DAY(%s))',
            'U' => 'UNIX_TIMESTAMP(%s)',
            'w' => '(DAYOFWEEK(%s) + 5) %% 7 + 1',
        ],
        'sqlite' => [
            'A' => '(CASE WHEN STRFTIME(\'%%H\', %s) < \'12\' THEN \'AM\' ELSE \'PM\' END)',
            'a' => '(CASE WHEN STRFTIME(\'%%H\', %s) < \'12\' THEN \'am\' ELSE \'pm\' END)',
            'D' => '(CASE WHEN STRFTIME(\'%%w\', %s) = \'0\' THEN \'Sun\' WHEN STRFTIME(\'%%w\', %s) = \'1\' THEN \'Mon\' WHEN STRFTIME(\'%%w\', %s) = \'2\' THEN \'Tue\' WHEN STRFTIME(\'%%w\', %s) = \'3\' THEN \'Wed\' WHEN STRFTIME(\'%%w\', %s) = \'4\' THEN \'Thu\' WHEN STRFTIME(\'%%w\', %s) = \'5\' THEN \'Fri\' WHEN STRFTIME(\'%%w\', %s) = \'6\' THEN \'Sat\' END)',
            'F' => '(CASE WHEN STRFTIME(\'%%m\', %s) = \'01\' THEN \'January\' WHEN STRFTIME(\'%%m\', %s) = \'02\' THEN \'February\' WHEN STRFTIME(\'%%m\', %s) = \'03\' THEN \'March\' WHEN STRFTIME(\'%%m\', %s) = \'04\' THEN \'April\' WHEN STRFTIME(\'%%m\', %s) = \'05\' THEN \'May\' WHEN STRFTIME(\'%%m\', %s) = \'06\' THEN \'June\' WHEN STRFTIME(\'%%m\', %s) = \'07\' THEN \'July\' WHEN STRFTIME(\'%%m\', %s) = \'08\' THEN \'August\' WHEN STRFTIME(\'%%m\', %s) = \'09\' THEN \'September\' WHEN STRFTIME(\'%%m\', %s) = \'10\' THEN \'October\' WHEN STRFTIME(\'%%m\', %s) = \'11\' THEN \'November\' WHEN STRFTIME(\'%%m\', %s) = \'12\' THEN \'December\' END)',
            'g' => '(CASE WHEN STRFTIME(\'%%H\', %s) > \'12\' THEN LTRIM(STRFTIME(\'%%H\', %s) - 12, \'0\') ELSE LTRIM(STRFTIME(\'%%H\', %s), \'0\') END)',
            'G' => 'LTRIM(STRFTIME(\'%%H\', %s), \'0\')',
            'h' => '(CASE WHEN STRFTIME(\'%%H\', %s) > \'12\' THEN STRFTIME(\'%%H\', %s) - 12 ELSE STRFTIME(\'%%H\', %s) END)',
            'j' => 'LTRIM(STRFTIME(\'%%d\', %s), \'0\')',
            'l' => '(CASE WHEN STRFTIME(\'%%w\', %s) = \'0\' THEN \'Sunday\' WHEN STRFTIME(\'%%w\', %s) = \'1\' THEN \'Monday\' WHEN STRFTIME(\'%%w\', %s) = \'2\' THEN \'Tuesday\' WHEN STRFTIME(\'%%w\', %s) = \'3\' THEN \'Wednesday\' WHEN STRFTIME(\'%%w\', %s) = \'4\' THEN \'Thursday\' WHEN STRFTIME(\'%%w\', %s) = \'5\' THEN \'Friday\' WHEN STRFTIME(\'%%w\', %s) = \'6\' THEN \'Saturday\' END)',
            'M' => '(CASE WHEN STRFTIME(\'%%m\', %s) = \'01\' THEN \'Jan\' WHEN STRFTIME(\'%%m\', %s) = \'02\' THEN \'Feb\' WHEN STRFTIME(\'%%m\', %s) = \'03\' THEN \'Mar\' WHEN STRFTIME(\'%%m\', %s) = \'04\' THEN \'Apr\' WHEN STRFTIME(\'%%m\', %s) = \'05\' THEN \'May\' WHEN STRFTIME(\'%%m\', %s) = \'06\' THEN \'Jun\' WHEN STRFTIME(\'%%m\', %s) = \'07\' THEN \'Jul\' WHEN STRFTIME(\'%%m\', %s) = \'08\' THEN \'Aug\' WHEN STRFTIME(\'%%m\', %s) = \'09\' THEN \'Sep\' WHEN STRFTIME(\'%%m\', %s) = \'10\' THEN \'Oct\' WHEN STRFTIME(\'%%m\', %s) = \'11\' THEN \'Nov\' WHEN STRFTIME(\'%%m\', %s) = \'12\' THEN \'Dec\' END)',
            'n' => 'LTRIM(STRFTIME(\'%%m\', %s), \'0\')',
            'o' => '(CASE WHEN STRFTIME(\'%%m\', %s) = \'01\' AND STRFTIME(\'%%d\', %s) <= \'03\' THEN STRFTIME(\'%%Y\', %s) - 1 ELSE STRFTIME(\'%%Y\', %s) END)',
            't' => 'STRFTIME(\'%%d\', DATE(%s, \'+1 month\', \'start of month\', \'-1 day\'))',
            'W' => '(STRFTIME(\'%%j\', %s, \'weekday 0\', \'-3 days\') - 1) / 7 + 1',
            'w' => '(STRFTIME(\'%%w\', %s) + 6) %% 7 + 1',
            'y' => 'SUBSTR(STRFTIME(\'%%Y\', %s), 3, 2)',
        ],
        'pgsql' => [
            'a' => '(CASE WHEN EXTRACT(HOUR FROM %s)::INTEGER < 12 THEN \'am\' ELSE \'pm\' END)',
            'F' => 'TRIM(TO_CHAR(%s, \'Month\'))',
            'g' => '(EXTRACT(HOUR FROM %s)::INTEGER %% 12)',
            'G' => 'CAST(EXTRACT(HOUR FROM %s)::INTEGER AS VARCHAR(2))',
            'l' => 'TRIM(TO_CHAR(%s, \'Day\'))',
            'o' => '(CASE WHEN EXTRACT(MONTH FROM %s)::INTEGER = 1 AND EXTRACT(DAY FROM %s)::INTEGER <= 3 THEN EXTRACT(YEAR FROM %s)::INTEGER - 1 ELSE EXTRACT(YEAR FROM %s)::INTEGER END)',
            't' => 'EXTRACT(DAY FROM DATE_TRUNC(\'month\', %s) + INTERVAL \'1 month - 1 day\')::INTEGER',
            'U' => 'EXTRACT(EPOCH FROM %s)::INTEGER',
            'w' => 'EXTRACT(DOW FROM %s)::INTEGER',
        ],
        'sqlsrv' => [
            'a' => '(CASE WHEN FORMAT(%s, \'tt\') = \'am\' THEN \'am\' ELSE \'pm\' END)',
            'F' => '(CASE WHEN MONTH(%s) = 1 THEN \'January\' WHEN MONTH(%s) = 2 THEN \'February\' WHEN MONTH(%s) = 3 THEN \'March\' WHEN MONTH(%s) = 4 THEN \'April\' WHEN MONTH(%s) = 5 THEN \'May\' WHEN MONTH(%s) = 6 THEN \'June\' WHEN MONTH(%s) = 7 THEN \'July\' WHEN MONTH(%s) = 8 THEN \'August\' WHEN MONTH(%s) = 9 THEN \'September\' WHEN MONTH(%s) = 10 THEN \'October\' WHEN MONTH(%s) = 11 THEN \'November\' WHEN MONTH(%s) = 12 THEN \'December\' END)',
            'g' => '(CAST(DATEPART(HOUR, %s) AS VARCHAR(2)) %% 12)',
            'G' => 'CAST(DATEPART(HOUR, %s) AS VARCHAR(2))',
            'j' => 'CAST(DAY(%s) AS VARCHAR(2))',
            'l' => '(CASE WHEN DATEPART(WEEKDAY, %s) = 1 THEN \'Sunday\' WHEN DATEPART(WEEKDAY, %s) = 2 THEN \'Monday\' WHEN DATEPART(WEEKDAY, %s) = 3 THEN \'Tuesday\' WHEN DATEPART(WEEKDAY, %s) = 4 THEN \'Wednesday\' WHEN DATEPART(WEEKDAY, %s) = 5 THEN \'Thursday\' WHEN DATEPART(WEEKDAY, %s) = 6 THEN \'Friday\' WHEN DATEPART(WEEKDAY, %s) = 7 THEN \'Saturday\' END)',
            'M' => '(CASE WHEN MONTH(%s) = 1 THEN \'Jan\' WHEN MONTH(%s) = 2 THEN \'Feb\' WHEN MONTH(%s) = 3 THEN \'Mar\' WHEN MONTH(%s) = 4 THEN \'Apr\' WHEN MONTH(%s) = 5 THEN \'May\' WHEN MONTH(%s) = 6 THEN \'Jun\' WHEN MONTH(%s) = 7 THEN \'Jul\' WHEN MONTH(%s) = 8 THEN \'Aug\' WHEN MONTH(%s) = 9 THEN \'Sep\' WHEN MONTH(%s) = 10 THEN \'Oct\' WHEN MONTH(%s) = 11 THEN \'Nov\' WHEN MONTH(%s) = 12 THEN \'Dec\' END)',
            'n' => 'CAST(MONTH(%s) AS VARCHAR(2))',
            'o' => '(CASE WHEN MONTH(%s) = 1 AND DAY(%s) <= 3 THEN YEAR(%s) - 1 ELSE YEAR(%s) END)',
            't' => 'CAST(DAY(EOMONTH(%s)) AS VARCHAR(2))',
            'U' => 'DATEDIFF(SECOND, \'1970-01-01\', %s)',
            'w' => '(CAST(DATEPART(WEEKDAY, %s) AS VARCHAR(2)) + 5) %% 7 + 1',
            'W' => 'CAST(DATEPART(ISO_WEEK, %s) AS VARCHAR(2))',
            'y' => 'RIGHT(CAST(YEAR(%s) AS VARCHAR(4)), 2)',
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

        foreach (str_split($this->format) as $character) {
            $emulatableCharacter = $this->emulatableCharacters[$this->identify($grammar)][$character] ?? null;
            $formatCharacter = $this->formatCharacters[$this->identify($grammar)][$character] ?? null;

            if ($emulatableCharacter) {
                $expressions[] = $this->getEmulatedExpression($grammar, $emulatableCharacter);
            } elseif ($formatCharacter) {
                $expressions[] = $this->getDateFormatExpression($grammar, $character);
            } else {
                $expressions[] = $this->getCharacterExpression($grammar, $character);
            }
        }

        return count($expressions) == 1 ?
            (string) $expressions[0]->getValue($grammar) : (new Concat($expressions))->getValue($grammar);
    }

    protected function getEmulatedExpression(Grammar $grammar, string $emulatableCharacter): QueryExpression
    {
        return new QueryExpression(sprintf(
            $emulatableCharacter,
            ...array_fill(
                start_index: 0,
                count: substr_count($emulatableCharacter, '%s'),
                value: $this->stringize($grammar, $this->expression)
            )
        ));
    }

    protected function getDateFormatExpression(Grammar $grammar, string $character): QueryExpression
    {
        $formatCharacter = $this->formatCharacters[$this->identify($grammar)][$character];

        return new QueryExpression(
            match ($this->identify($grammar)) {
                'mysql' => "DATE_FORMAT({$this->stringize($grammar, $this->expression)}, '{$formatCharacter}')",
                'sqlite' => "STRFTIME('{$formatCharacter}', {$this->stringize($grammar, $this->expression)})",
                'pgsql' => "TO_CHAR({$this->stringize($grammar, $this->expression)}, '{$formatCharacter}')",
                'sqlsrv' => "FORMAT({$this->stringize($grammar, $this->expression)}, '{$formatCharacter}')",
            }
        );
    }

    protected function getCharacterExpression(Grammar $grammar, string $character): QueryExpression
    {
        return new QueryExpression($grammar->quoteString($character));
    }
}
