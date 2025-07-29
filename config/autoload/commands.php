<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */
return [
    Hyperf\Database\Commands\Migrations\MigrateCommand::class,
    Hyperf\Database\Commands\Migrations\FreshCommand::class,
    Hyperf\Database\Commands\Migrations\InstallCommand::class,
    Hyperf\Database\Commands\Migrations\RefreshCommand::class,
    Hyperf\Database\Commands\Migrations\ResetCommand::class,
    Hyperf\Database\Commands\Migrations\RollbackCommand::class,
    Hyperf\Database\Commands\Migrations\StatusCommand::class,
    Hyperf\Database\Commands\Seeders\SeedCommand::class,
    Hyperf\Database\Commands\Seeders\GenSeederCommand::class,
];
