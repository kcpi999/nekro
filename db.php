<?php

$dsn = $database['db_type'] . ':host=' . $database['host']
    . ';port=' . $database['port']
    . ';dbname=' . $database['dbname']
    . ';charset=' . $database['charset'];

$opt = array(
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
);

$pdo = new PDO($dsn, $database['user'], $database['password'], $opt);
$GLOBALS['pdo'] = $pdo;
