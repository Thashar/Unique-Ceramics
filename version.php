<?php
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate');
echo json_encode(['v' => filemtime(__DIR__ . '/assets/css/main.css')]);
