<?php

declare(strict_types=1);

namespace WTD\Queue;

interface Job
{
    public function handle(): void;
}
