<?php
echo "Error reporting: " . error_reporting() . "\n";
echo "Display errors: " . ini_get('display_errors') . "\n";
echo "Log errors: " . ini_get('log_errors') . "\n";
echo "Error log file: " . ini_get('error_log') . "\n";

error_log("TEST LOG MESSAGE FROM check_errors.php");
echo "\nTest error_log() called. Check your error log for: TEST LOG MESSAGE\n";

// Also write to a file we control
$log_file = __DIR__ . '/../logs/debug.log';
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Test write\n", FILE_APPEND);
echo "Also wrote to: " . $log_file . "\n";
?>
