<?php

declare(strict_types=1);

namespace WTD\Scheduler;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

final class CronExpression
{
    /**
     * @var list<string>
     */
    private array $parts;

    public function __construct(string $expression)
    {
        $parts = preg_split('/\s+/', trim($expression));

        if ($parts === false || count($parts) !== 5) {
            throw new InvalidArgumentException('Cron expression must contain five fields.');
        }

        $this->parts = array_values($parts);
    }

    public function isDue(?DateTimeImmutable $time = null, string $timezone = 'UTC'): bool
    {
        $time = ($time ?? new DateTimeImmutable('now', new DateTimeZone($timezone)))->setTimezone(new DateTimeZone($timezone));
        [$minute, $hour, $day, $month, $weekday] = $this->parts;

        return $this->matches($minute, (int) $time->format('i'), 0, 59)
            && $this->matches($hour, (int) $time->format('G'), 0, 23)
            && $this->matches($day, (int) $time->format('j'), 1, 31)
            && $this->matches($month, (int) $time->format('n'), 1, 12)
            && $this->matches($weekday, (int) $time->format('w'), 0, 6);
    }

    private function matches(string $field, int $value, int $min, int $max): bool
    {
        foreach (explode(',', $field) as $part) {
            if ($this->matchesPart($part, $value, $min, $max)) {
                return true;
            }
        }

        return false;
    }

    private function matchesPart(string $part, int $value, int $min, int $max): bool
    {
        if ($part === '*') {
            return true;
        }

        if (str_contains($part, '/')) {
            [$range, $step] = explode('/', $part, 2);
            $step = max(1, (int) $step);
            $range = $range === '*' ? $min . '-' . $max : $range;

            if (!str_contains($range, '-')) {
                return false;
            }

            [$start, $end] = array_map('intval', explode('-', $range, 2));

            return $value >= $start && $value <= $end && (($value - $start) % $step) === 0;
        }

        if (str_contains($part, '-')) {
            [$start, $end] = array_map('intval', explode('-', $part, 2));

            return $value >= $start && $value <= $end;
        }

        return (int) $part === $value;
    }
}
