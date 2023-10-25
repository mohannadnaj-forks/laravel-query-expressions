<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Tpetry\QueryExpressions\Function\Date\DateFormat;

it('can format dates and don\'t use concat if not needed [Y]')
    ->expect(new DateFormat('created_at', format: 'Y'))
    ->toBeExecutable(callback: function (Blueprint $table) {
        $table->dateTime('created_at');
    })
    ->toBePgsql('to_char("created_at", \'YYYY\')')
    ->toBeSqlite('strftime(\'%Y\', "created_at")')
    ->toBeMysql('date_format(`created_at`, \'%Y\')')
    ->toBeSqlsrv('format([created_at], \'yyyy\')');

it('can format dates and don\'t use concat if not needed: [U]')
    ->expect(new DateFormat('created_at', format: 'U'))
    ->toBeExecutable(callback: function (Blueprint $table) {
        $table->dateTime('created_at');
    })
    ->toBePgsql('extract(epoch from "created_at")')
    ->toBeSqlite('strftime(\'%s\', "created_at")')
    ->toBeMysql('unix_timestamp(`created_at`)')
    ->toBeSqlsrv('datediff(second, \'1970-01-01\', [created_at])');

it('can format dates: [Y-n]')
    ->expect(new DateFormat('created_at', format: 'Y-n'))
    ->toBeExecutable(callback: function (Blueprint $table) {
        $table->dateTime('created_at');
    })
    ->toBePgsql('to_char("created_at", \'YYYY-FMMM\')')
    ->toBeSqlite('strftime(\'%Y-%m\', "created_at")')
    ->toBeMysql('date_format(`created_at`, \'%Y-%c\')')
    ->toBeSqlsrv('(concat(format([created_at], \'yyyy-\'),cast(month([created_at]) as varchar(2))))');

it('can format dates: [Y-m]')
    ->expect(new DateFormat('created_at', format: 'Y-m'))
    ->toBeExecutable(callback: function (Blueprint $table) {
        $table->dateTime('created_at');
    })
    ->toBePgsql('to_char("created_at", \'YYYY-MM\')')
    ->toBeSqlite('strftime(\'%Y-%m\', "created_at")')
    ->toBeMysql('date_format(`created_at`, \'%Y-%m\')')
    ->toBeSqlsrv('format([created_at], \'yyyy-MM\')');

it('can format dates: [Y-m-d H:i:s]')
    ->expect(new DateFormat('created_at', format: 'Y-m-d H:i:s'))
    ->toBeExecutable(callback: function (Blueprint $table) {
        $table->dateTime('created_at');
    })
    ->toBePgsql('to_char("created_at", \'YYYY-MM-DD HH24:MI:SS\')')
    ->toBeSqlite('strftime(\'%Y-%m-%d %H:%M:%S\', "created_at")')
    ->toBeMysql('date_format(`created_at`, \'%Y-%m-%d %H:%i:%s\')')
    ->toBeSqlsrv('format([created_at], \'yyyy-MM-dd HH:mm:ss\')');
