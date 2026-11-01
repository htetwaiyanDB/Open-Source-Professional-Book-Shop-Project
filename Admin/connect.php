<?php
require_once dirname(__DIR__) . '/includes/database.php';
if (!isset($connection)) { $connection = database_connect(); }