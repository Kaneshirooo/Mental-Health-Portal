<?php
$dir = __DIR__;
$files = glob($dir . '/*.php');
$count = 0;
foreach ($files as $file) {
    if (is_file($file)) {
        $content = file_get_contents($file);
        $new_content = str_replace('<link rel="stylesheet" href="styles.css?v=2.1">', '<link rel="stylesheet" href="styles.css?v=2.2">', $content);
        if ($new_content !== $content) {
            file_put_contents($file, $new_content);
            $count++;
        }
    }
}
echo "Updated $count files to v2.2\n";
