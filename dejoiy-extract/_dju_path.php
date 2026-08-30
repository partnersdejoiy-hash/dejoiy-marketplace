<?php
header("Content-Type: text/plain");
echo "ABSPATH: " . ABSPATH . "
";
echo "stylesheet: " . get_stylesheet_directory() . "
";
echo "template: " . get_template_directory() . "
";
echo "
Files in themes/dejoiy:
";
$dir = get_stylesheet_directory();
if (is_dir($dir)) {
    foreach (scandir($dir) as $f) {
        if (strpos($f, 'v3') !== false || strpos($f, 'homepage') !== false) {
            echo "  " . $f . " (" . filesize($dir . "/" . $f) . " bytes)
";
        }
    }
} else {
    echo "themes dir NOT FOUND
";
}
