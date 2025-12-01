<?php
// index.php (raíz)
session_start();
require_once __DIR__ . '/router.php';
Router::dispatch();
