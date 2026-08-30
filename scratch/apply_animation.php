<?php
$files = glob('app/Filament/Widgets/*.php');
foreach ($files as $file) {
    $content = file_get_contents($file);
    if (strpos($content, 'extends ChartWidget') !== false) {
        if (strpos($content, 'protected function getOptions(): array') !== false) {
            // It has getOptions()
            if (strpos($content, "'animation' =>") === false) {
                // Add animation before scales or plugins
                $content = preg_replace(
                    "/(protected function getOptions\(\): array\s*\{\s*return \[\s*)/",
                    "$1'animation' => [\n                'duration' => 2500,\n                'easing' => 'easeOutCubic',\n            ],\n            ",
                    $content
                );
                file_put_contents($file, $content);
                echo "Updated $file\n";
            }
        } else {
            // It doesn't have getOptions()
            $methods = "    protected function getOptions(): array\n    {\n        return [\n            'animation' => [\n                'duration' => 2500,\n                'easing' => 'easeOutCubic',\n            ],\n        ];\n    }\n}\n";
            $content = preg_replace("/\}\s*$/", "\n$methods", $content);
            file_put_contents($file, $content);
            echo "Added getOptions to $file\n";
        }
    }
}
