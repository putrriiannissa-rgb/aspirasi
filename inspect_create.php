<?php
error_reporting(E_ALL);
$m=new mysqli('127.0.0.1','root','','db_pengaduan');
if($m->connect_error){
    echo 'ERR:'.$m->connect_error;
    exit(1);
}
$tables = ['users','aspirations'];
foreach ($tables as $table) {
    $res = $m->query("SHOW CREATE TABLE $table");
    if (!$res) {
        echo "SHOW CREATE TABLE failed for $table: " . $m->error . "\n";
        continue;
    }
    $row = $res->fetch_assoc();
    echo "--- $table ---\n";
    echo $row['Create Table'] . "\n\n";
}
