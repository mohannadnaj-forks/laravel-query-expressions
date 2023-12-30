<?php

declare(strict_types=1);

namespace Tpetry\QueryExpressions\Function\Date;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Grammar;
use Illuminate\Database\Query\Expression as QueryExpression;
use Tpetry\QueryExpressions\Function\String\Ltrim;
use Tpetry\QueryExpressions\Language\CaseGroup;
use Tpetry\QueryExpressions\Language\CaseRule;
use Tpetry\QueryExpressions\Operator\Comparison\Equal;
use Tpetry\QueryExpressions\Operator\Comparison\GreaterThan;
use Tpetry\QueryExpressions\Operator\Comparison\LessThan;
use Tpetry\QueryExpressions\Operator\Comparison\LessThanOrEqual;
use Tpetry\QueryExpressions\Operator\Logical\CondAnd;
use Tpetry\QueryExpressions\Value\Value;

/**
 * @property-read string|\Illuminate\Database\Query\Expression $expression
 *
 * @uses \Tpetry\QueryExpressions\Concerns\IdentifiesDriver
 * @uses \Tpetry\QueryExpressions\Concerns\StringizeExpression
 */
trait EmulatedDateFormatTrait
{
    protected function getEmulatedExpression(Grammar $grammar, string|Expression $emulatedCharacter): Expression
    {
        if ($grammar->isExpression($emulatedCharacter)) {
            $emulatedCharacter = $this->stringize($grammar, $emulatedCharacter);
        }

        /** @var string $emulatedCharacter */
        return new QueryExpression(sprintf(
            $emulatedCharacter,
            ...array_fill(
                start_index: 0,
                count: substr_count($emulatedCharacter, '%s'),
                value: $this->stringize($grammar, $this->expression)
            )
        ));
    }

    protected function getEmulatableCharacter(Grammar $grammar, string $character): string|Expression|null
    {
        return match ($this->identify($grammar)) {
            'mysql' => $this->getEmulatableCharacterForMysql($character),
            'sqlite' => $this->getEmulatableCharacterForSqlite($character),
            'pgsql' => $this->getEmulatableCharacterForPgsql($character),
            'sqlsrv' => $this->getEmulatableCharacterForSqlsrv($character),
        };
    }

    protected function getEmulatableCharacterForMysql(string $character): string|Expression|null
    {
        return match ($character) {
            'a' => (new CaseGroup(
                when: [
                    new CaseRule(
                        new Value('am'),
                        new LessThan(
                            new QueryExpression('HOUR(%s)'),
                            new Value('12'),
                        )
                    ),
                ],
                else: new Value('pm'),
            )),
            'g' => 'HOUR(%s) %% 12',
            'G' => 'HOUR(%s)',
            't' => 'DAY(LAST_DAY(%s))',
            'U' => 'UNIX_TIMESTAMP(%s)',
            'w' => '(DAYOFWEEK(%s) + 5) %% 7 + 1',
            default => null,
        };
    }

    protected function getEmulatableCharacterForSqlite(string $character): string|Expression|null
    {
        return match ($character) {
            'A' => (new CaseGroup(
                when: [
                    new CaseRule(
                        new Value('AM'),
                        new LessThan(
                            new QueryExpression('STRFTIME(\'%%H\', %s)'),
                            new Value('12'),
                        )
                    ),
                ],
                else: new Value('PM'),
            )),
            'a' => (new CaseGroup(
                when: [
                    new CaseRule(
                        new Value('am'),
                        new LessThan(
                            new QueryExpression('STRFTIME(\'%%H\', %s)'),
                            new Value('12'),
                        )
                    ),
                ],
                else: new Value('pm'),
            )),
            'D' => (new CaseGroup(
                when: [
                    new CaseRule(
                        new Value('Sun'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%w\', %s)'),
                            new Value('0'),
                        )
                    ),
                    new CaseRule(
                        new Value('Mon'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%w\', %s)'),
                            new Value('1'),
                        )
                    ),
                    new CaseRule(
                        new Value('Tue'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%w\', %s)'),
                            new Value('2'),
                        )
                    ),
                    new CaseRule(
                        new Value('Wed'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%w\', %s)'),
                            new Value('3'),
                        )
                    ),
                    new CaseRule(
                        new Value('Thu'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%w\', %s)'),
                            new Value('4'),
                        )
                    ),
                    new CaseRule(
                        new Value('Fri'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%w\', %s)'),
                            new Value('5'),
                        )
                    ),
                    new CaseRule(
                        new Value('Sat'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%w\', %s)'),
                            new Value('6'),
                        )
                    ),
                ],
            )),
            'F' => (new CaseGroup(
                when: [
                    new CaseRule(
                        new Value('January'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%m\', %s)'),
                            new Value('01'),
                        )
                    ),
                    new CaseRule(
                        new Value('February'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%m\', %s)'),
                            new Value('02'),
                        )
                    ),
                    new CaseRule(
                        new Value('March'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%m\', %s)'),
                            new Value('03'),
                        )
                    ),
                    new CaseRule(
                        new Value('April'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%m\', %s)'),
                            new Value('04'),
                        )
                    ),
                    new CaseRule(
                        new Value('May'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%m\', %s)'),
                            new Value('05'),
                        )
                    ),
                    new CaseRule(
                        new Value('June'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%m\', %s)'),
                            new Value('06'),
                        )
                    ),
                    new CaseRule(
                        new Value('July'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%m\', %s)'),
                            new Value('07'),
                        )
                    ),
                    new CaseRule(
                        new Value('August'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%m\', %s)'),
                            new Value('08'),
                        )
                    ),
                    new CaseRule(
                        new Value('September'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%m\', %s)'),
                            new Value('09'),
                        )
                    ),
                    new CaseRule(
                        new Value('October'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%m\', %s)'),
                            new Value('10'),
                        )
                    ),
                    new CaseRule(
                        new Value('November'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%m\', %s)'),
                            new Value('11'),
                        )
                    ),
                    new CaseRule(
                        new Value('December'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%m\', %s)'),
                            new Value('12'),
                        )
                    ),
                ],
            )),
            'g' => (new CaseGroup(
                when: [
                    new CaseRule(
                        new QueryExpression('LTRIM(STRFTIME(\'%%H\', %s) - 12, \'0\')'),
                        new GreaterThan(
                            new QueryExpression('STRFTIME(\'%%H\', %s)'),
                            new Value('12'),
                        )
                    ),
                ],
                else: new QueryExpression('LTRIM(STRFTIME(\'%%H\', %s), \'0\')'),
            )),
            'G' => (new Ltrim(
                new QueryExpression('STRFTIME(\'%%H\', %s)'),
                '0',
            )),
            'h' => (new CaseGroup(
                when: [
                    new CaseRule(
                        new QueryExpression('STRFTIME(\'%%H\', %s) - 12'),
                        new GreaterThan(
                            new QueryExpression('STRFTIME(\'%%H\', %s)'),
                            new Value('12'),
                        )
                    ),
                ],
                else: new QueryExpression('STRFTIME(\'%%H\', %s)'),
            )),
            'j' => (new Ltrim(
                new QueryExpression('STRFTIME(\'%%d\', %s)'),
                '0',
            )),
            'l' => (new CaseGroup(
                when: [
                    new CaseRule(
                        new Value('Sunday'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%w\', %s)'),
                            new Value('0'),
                        )
                    ),
                    new CaseRule(
                        new Value('Monday'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%w\', %s)'),
                            new Value('1'),
                        )
                    ),
                    new CaseRule(
                        new Value('Tuesday'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%w\', %s)'),
                            new Value('2'),
                        )
                    ),
                    new CaseRule(
                        new Value('Wednesday'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%w\', %s)'),
                            new Value('3'),
                        )
                    ),
                    new CaseRule(
                        new Value('Thursday'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%w\', %s)'),
                            new Value('4'),
                        )
                    ),
                    new CaseRule(
                        new Value('Friday'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%w\', %s)'),
                            new Value('5'),
                        )
                    ),
                    new CaseRule(
                        new Value('Saturday'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%w\', %s)'),
                            new Value('6'),
                        )
                    ),
                ],
            )),
            'M' => (new CaseGroup(
                when: [
                    new CaseRule(
                        new Value('Jan'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%m\', %s)'),
                            new Value('01'),
                        )
                    ),
                    new CaseRule(
                        new Value('Feb'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%m\', %s)'),
                            new Value('02'),
                        )
                    ),
                    new CaseRule(
                        new Value('Mar'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%m\', %s)'),
                            new Value('03'),
                        )
                    ),
                    new CaseRule(
                        new Value('Apr'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%m\', %s)'),
                            new Value('04'),
                        )
                    ),
                    new CaseRule(
                        new Value('May'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%m\', %s)'),
                            new Value('05'),
                        )
                    ),
                    new CaseRule(
                        new Value('Jun'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%m\', %s)'),
                            new Value('06'),
                        )
                    ),
                    new CaseRule(
                        new Value('Jul'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%m\', %s)'),
                            new Value('07'),
                        )
                    ),
                    new CaseRule(
                        new Value('Aug'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%m\', %s)'),
                            new Value('08'),
                        )
                    ),
                    new CaseRule(
                        new Value('Sep'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%m\', %s)'),
                            new Value('09'),
                        )
                    ),
                    new CaseRule(
                        new Value('Oct'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%m\', %s)'),
                            new Value('10'),
                        )
                    ),
                    new CaseRule(
                        new Value('Nov'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%m\', %s)'),
                            new Value('11'),
                        )
                    ),
                    new CaseRule(
                        new Value('Dec'),
                        new Equal(
                            new QueryExpression('STRFTIME(\'%%m\', %s)'),
                            new Value('12'),
                        )
                    ),
                ],
            )),
            'n' => (new Ltrim(
                new QueryExpression('STRFTIME(\'%%m\', %s)'),
                '0',
            )),
            'o' => (new CaseGroup(
                when: [
                    new CaseRule(
                        result: new QueryExpression('STRFTIME(\'%%Y\', %s) - 1'),
                        condition: new CondAnd(
                            new Equal(
                                new QueryExpression('STRFTIME(\'%%m\', %s)'),
                                new Value('01'),
                            ),
                            new LessThanOrEqual(
                                new QueryExpression('STRFTIME(\'%%d\', %s)'),
                                new Value('03'),
                            ),
                        ),
                    ),
                ],
                else: new QueryExpression('STRFTIME(\'%%Y\', %s)'),
            )),
            't' => 'STRFTIME(\'%%d\', DATE(%s, \'+1 month\', \'start of month\', \'-1 day\'))',
            'w' => '(STRFTIME(\'%%w\', %s) + 6) %% 7 + 1',
            'W' => '(STRFTIME(\'%%j\', %s, \'weekday 0\', \'-3 days\') - 1) / 7 + 1',
            'y' => 'SUBSTR(STRFTIME(\'%%Y\', %s), 3, 2)',
            default => null,
        };
    }

    protected function getEmulatableCharacterForPgsql(string $character): string|Expression|null
    {
        return match ($character) {
            'a' => new CaseGroup(
                when: [
                    new CaseRule(
                        result: new Value('am'),
                        condition: new LessThan(
                            new QueryExpression('EXTRACT(HOUR FROM %s)::INTEGER'),
                            new Value('12'),
                        ),
                    ),
                ],
                else: new Value('pm'),
            ),
            'F' => 'TRIM(TO_CHAR(%s, \'Month\'))',
            'g' => '(EXTRACT(HOUR FROM %s)::INTEGER %% 12)',
            'G' => 'CAST(EXTRACT(HOUR FROM %s)::INTEGER AS VARCHAR(2))',
            'l' => 'TRIM(TO_CHAR(%s, \'Day\'))',
            'o' => new CaseGroup(
                when: [
                    new CaseRule(
                        result: new QueryExpression('EXTRACT(YEAR FROM %s)::INTEGER - 1'),
                        condition: new CondAnd(
                            new Equal(
                                new QueryExpression('EXTRACT(MONTH FROM %s)::INTEGER'),
                                new Value('1'),
                            ),
                            new LessThanOrEqual(
                                new QueryExpression('EXTRACT(DAY FROM %s)::INTEGER'),
                                new Value('3'),
                            ),
                        ),
                    ),
                ],
                else: new QueryExpression('EXTRACT(YEAR FROM %s)::INTEGER'),
            ),
            't' => 'EXTRACT(DAY FROM DATE_TRUNC(\'month\', %s) + INTERVAL \'1 month - 1 day\')::INTEGER',
            'U' => 'EXTRACT(EPOCH FROM %s)::INTEGER',
            'w' => 'EXTRACT(DOW FROM %s)::INTEGER',
            default => null,
        };
    }

    protected function getEmulatableCharacterForSqlsrv(string $character): string|Expression|null
    {
        return match ($character) {
            'a' => 'LOWER(FORMAT(%s, \'tt\'))',
            'F' => 'DATENAME(MONTH, %s)',
            'g' => '(CAST(DATEPART(HOUR, %s) AS VARCHAR(2)) %% 12)',
            'G' => 'CAST(DATEPART(HOUR, %s) AS VARCHAR(2))',
            'j' => 'CAST(DAY(%s) AS VARCHAR(2))',
            'l' => 'DATENAME(WEEKDAY, %s)',
            'M' => 'LEFT(DATENAME(MONTH, %s), 3)',
            'n' => 'CAST(MONTH(%s) AS VARCHAR(2))',
            'o' => '(CASE WHEN MONTH(%s) = 1 AND DAY(%s) <= 3 THEN YEAR(%s) - 1 ELSE YEAR(%s) END)',
            't' => 'CAST(DAY(EOMONTH(%s)) AS VARCHAR(2))',
            'U' => 'DATEDIFF(SECOND, \'1970-01-01\', %s)',
            'w' => '(CAST(DATEPART(WEEKDAY, %s) AS VARCHAR(2)) + 5) %% 7 + 1',
            'W' => 'CAST(DATEPART(ISO_WEEK, %s) AS VARCHAR(2))',
            'y' => 'RIGHT(CAST(YEAR(%s) AS VARCHAR(4)), 2)',
            default => null,
        };
    }
}
