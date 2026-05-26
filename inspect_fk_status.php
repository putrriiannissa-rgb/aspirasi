<?php
error_reporting(E_ALL);
$m=new mysqli('127.0.0.1','root','','db_pengaduan');
if($m->connect_error){
    echo 'ERR:'.$m->connect_error;
    exit(1);
}
$tables = ['users','aspirations'];
foreach ($tables as $table) {
    $res = $m->query("SHOW TABLE STATUS LIKE '$table'");
    if (!$res) {
        echo "SHOW TABLE STATUS failed for $table: " . $m->error . "\n";
        continue;
    }
    $row = $res->fetch_assoc();
    echo "Table: $table\n";
    print_r($row);
    echo "\n";
}
