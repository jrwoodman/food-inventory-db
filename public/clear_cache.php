<?php
// Clear PHP opcache
// DELETE THIS FILE after running it once!

if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "Opcache cleared successfully!\n";
} else {
    echo "Opcache is not enabled.\n";
}

if (function_exists('opcache_invalidate')) {
    opcache_invalidate(__DIR__ . '/../src/models/Ingredient.php', true);
    echo "Ingredient.php invalidated.\n";
}

phpinfo(INFO_GENERAL);
?>
