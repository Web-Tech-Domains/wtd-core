<?php

declare(strict_types=1);

namespace WTD\Mail;

final class TemplateRenderer
{
    /**
     * @param array<string, scalar|null> $data
     */
    public function render(string $template, array $data = []): string
    {
        foreach ($data as $key => $value) {
            $template = str_replace('{{ ' . $key . ' }}', (string) $value, $template);
            $template = str_replace('{{' . $key . '}}', (string) $value, $template);
        }

        return $template;
    }

    /**
     * @param array<string, scalar|null> $data
     */
    public function markdown(string $markdown, array $data = []): string
    {
        $rendered = $this->render($markdown, $data);
        $html = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $rendered) ?? $rendered;
        $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html) ?? $html;
        $paragraphs = array_filter(array_map('trim', preg_split("/\R{2,}/", $html) ?: []));

        return implode("\n", array_map(
            static fn (string $line): string => str_starts_with($line, '<h1>') ? $line : '<p>' . $line . '</p>',
            $paragraphs,
        ));
    }
}
