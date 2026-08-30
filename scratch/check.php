<?php
$files = glob('app/Filament/Widgets/*.php');
foreach ($files as $file) {
    $content = file_get_contents($file);
    if (strpos($content, 'extends ChartWidget') !== false) {
        if (strpos($content, 'protected function getOptions(): array') !== false) {
            preg_match('/protected function getOptions\(\): array\s*\{\s*return \[\s*\'(\w+)\' => \[/', $content, $matches);
            echo $file . ' -> has getOptions, first key: ' . ($matches[1] ?? 'unknown') . PHP_EOL;
        } else {
            echo $file . ' -> NO getOptions' . PHP_EOL;
        }
    }
}
