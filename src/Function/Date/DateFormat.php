<?php

declare(strict_types=1);

namespace Tpetry\QueryExpressions\Function\Date;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Grammar;
use Tpetry\QueryExpressions\Concerns\IdentifiesDriver;
use Tpetry\QueryExpressions\Concerns\StringizeExpression;

class DateFormat implements Expression
{
    use IdentifiesDriver;
    use StringizeExpression;

    public function __construct(
        private readonly string|Expression $expression,
        private readonly string $format
    ) {
    }

    public function getValue(Grammar $grammar): string
    {
        $expression = $this->stringize($grammar, $this->expression);
        $format = $this->getDateFormat($grammar, $this->format);

        return match ($this->identify($grammar)) {
            'mysql' => "date_format({$expression}, '{$format}')",
            'sqlite' => "strftime('{$format}', {$expression})",
            'pgsql' => "to_char({$expression}, '{$format}')",
            'sqlsrv' => "format({$expression}, '{$format}')",
        };
    }

    protected function getDateFormat(Grammar $grammar, string $format): string
    {
        $expression = '';

        foreach (str_split($format) as $char) {
            $expression .= $this->getDateFormatChar($grammar, $char);
        }

        return $expression;
    }

    protected function getDateFormatChar(Grammar $grammar, string $char): string
    {
        return match ($this->identify($grammar)) {
            'mysql' => match ($char) {
                'Y' => '%Y',
                'y' => '%y',
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
                default => $char,
            },
            'sqlite' => match ($char) {
                'Y' => '%Y',
                // No format for 2 digit year number.
                'y' => '%Y',
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
                default => $char,
            },
            'pgsql' => match ($char) {
                'Y' => 'YYYY',
                'y' => 'YY',
                'M' => 'Mon',
                'm' => 'MM',
                // No format for Numeric representation of a month, without leading zeros.
                'n' => 'MM',
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
                default => $char,
            },
            'sqlsrv' => match ($char) {
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
                default => $char,
            },
        };
    }
}
