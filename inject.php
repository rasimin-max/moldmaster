<?php

$dir = __DIR__ . '/app/Filament/Widgets';
$files = glob($dir . '/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    if (strpos($content, 'function canView') === false) {
        $replacement = "
    public static function canView(): bool
    {
        try {
            \$activeWidgets = app(\App\Settings\GeneralSettings::class)->active_widgets ?? [];
            return empty(\$activeWidgets) || in_array(class_basename(static::class), \$activeWidgets);
        } catch (\Throwable \$e) {
            return true;
        }
    }
";
        // Insert after the first '{' (which is the class opening brace)
        $content = preg_replace('/\{/', "{" . $replacement, $content, 1);
        file_put_contents($file, $content);
        echo "Updated $file\n";
    }
}
