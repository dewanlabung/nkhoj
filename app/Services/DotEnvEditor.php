<?php

namespace App\Services;

use Illuminate\Support\Str;

class DotEnvEditor
{
    private string $path;

    public function __construct()
    {
        $this->path = base_path('.env');
    }

    public function write(array $values): void
    {
        if (!file_exists($this->path)) {
            return;
        }

        $content = file_get_contents($this->path);

        foreach ($values as $key => $value) {
            $key = strtoupper($key);
            $formatted = $this->formatValue($value);

            if (Str::contains($content, $key . '=')) {
                preg_match("/($key=)(.*?)(\n|\Z)/msi", $content, $matches);
                $content = str_replace(
                    $matches[1] . $matches[2],
                    $matches[1] . $formatted,
                    $content,
                );
            } else {
                $content .= "\n$key=$formatted";
            }
        }

        file_put_contents($this->path, $content);
    }

    private function formatValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'null';
        }
        if ($value === false || $value === 0) {
            return 'false';
        }
        if ($value === true || $value === 1) {
            return 'true';
        }

        $value = trim((string) $value);

        if (preg_match('/\s/', $value) || Str::contains($value, '#')) {
            $value = str_replace('"', "'", $value);
            $value = '"' . $value . '"';
        }

        return $value;
    }
}
