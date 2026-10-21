<?php
// Test if PHP can execute Python at all
echo "<h2>Testing Python Execution from PHP</h2>";

// Test 1: Simple Python command
$testCommand = "python -c \"print('Hello from Python')\" 2>&1";
$output = shell_exec($testCommand);
echo "<p><strong>Simple Python test:</strong> " . htmlspecialchars($output) . "</p>";

// Test 2: Using full path to Python
$testCommand2 = "\"C:\\Program Files\\Python38\\python.exe\" -c \"print('Hello from Python 3.8')\" 2>&1";
$output2 = shell_exec($testCommand2);
echo "<p><strong>Python 3.8 test:</strong> " . htmlspecialchars($output2) . "</p>";

// Test 3: Check if pip is working
$testCommand3 = "python -m pip --version 2>&1";
$output3 = shell_exec($testCommand3);
echo "<p><strong>Pip version:</strong> " . htmlspecialchars($output3) . "</p>";
?>