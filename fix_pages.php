<?php

$files = glob('app/Filament/Pages/*.php');

foreach ($files as $file) {
    if (strpos($file, 'SystemSettingsPage') !== false) {
        continue;
    }
    
    $content = file_get_contents($file);
    
    if (strpos($content, 'HasPageShield') === false) {
        // Find the class declaration and implements clause (if any)
        $pattern = '/class\s+(\w+)\s+extends\s+Page\s*(implements\s+[^{]+)?\s*\{/s';
        
        $content = preg_replace_callback($pattern, function ($matches) {
            $classDec = $matches[0];
            return $classDec . "\n    use \BezhanSalleh\FilamentShield\Traits\HasPageShield;\n";
        }, $content);
        
        file_put_contents($file, $content);
        echo "Added HasPageShield to $file\n";
    }
}
echo "Done.\n";
