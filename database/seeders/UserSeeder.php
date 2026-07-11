<?php

declare(strict_types=1);

use WTD\Database\Connection;
use WTD\Database\Seeder;

return new class implements Seeder {
    public function run(Connection $connection): void
    {
        $connection->table('users')->insert([
            'name' => 'Admin User',
            'email' => 'admin@example.test',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
};
