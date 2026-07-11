<?php

declare(strict_types=1);

use WTD\Database\Blueprint;
use WTD\Database\Migration;
use WTD\Database\Schema;

return new class implements Migration {
    public function up(Schema $schema): void
    {
        $schema->create('blogs', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down(Schema $schema): void
    {
        $schema->dropIfExists('blogs');
    }
};
