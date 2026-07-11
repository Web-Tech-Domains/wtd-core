<?php

declare(strict_types=1);

use WTD\Database\Blueprint;
use WTD\Database\Migration;
use WTD\Database\Schema;

return new class implements Migration {
    public function up(Schema $schema): void
    {
        $schema->create('users', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->timestamps();
        });
    }

    public function down(Schema $schema): void
    {
        $schema->dropIfExists('users');
    }
};
