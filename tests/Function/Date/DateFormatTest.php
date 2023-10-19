<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Tpetry\QueryExpressions\Function\Date\DateFormat;

it('can format the date of a column to a Y-m')
    ->expect(new DateFormat('created_at', format: 'Y-m'))
    ->toBeExecutable(callback: function (Blueprint $table) {
        $table->dateTime('created_at');
    })
    ->toBePgsql('to_char("created_at", \'YYYY-MM\')')
    ->toBeSqlite('strftime(\'%Y-%m\', "created_at")')
    ->toBeMysql('date_format(`created_at`, \'%Y-%m\')')
    ->toBeSqlsrv('format([created_at], \'yyyy-MM\')');

it('can format the date of a column to a Y-m-d H:i:s')
    ->expect(new DateFormat('created_at', format: 'Y-m-d H:i:s'))
    ->toBeExecutable(callback: function (Blueprint $table) {
        $table->dateTime('created_at');
    })
    ->toBePgsql('to_char("created_at", \'YYYY-MM-DD HH24:MI:SS\')')
    ->toBeSqlite('strftime(\'%Y-%m-%d %H:%M:%S\', "created_at")')
    ->toBeMysql('date_format(`created_at`, \'%Y-%m-%d %H:%i:%s\')')
    ->toBeSqlsrv('format([created_at], \'yyyy-MM-dd HH:mm:ss\')');
