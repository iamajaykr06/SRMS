<?php
declare(strict_types=1);

$name = $_GET['name'] ?? 'Guest';

echo "Hello " . htmlspecialchars($name);