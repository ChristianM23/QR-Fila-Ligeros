<?php
echo "<h1>🧪 Test Básico</h1>";
echo "<p>Si ves esto, Apache está funcionando</p>";
echo "<p><strong>REQUEST_URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "</p>";
echo "<p><strong>SCRIPT_NAME:</strong> " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "</p>";
echo "<p><strong>PHP_SELF:</strong> " . ($_SERVER['PHP_SELF'] ?? 'N/A') . "</p>";
echo "<p><strong>Archivo actual:</strong> " . __FILE__ . "</p>";
?>