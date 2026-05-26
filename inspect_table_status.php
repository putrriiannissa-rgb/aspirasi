<?php
error_reporting(E_ALL);
$m=new mysqli('127.0.0.1','root','','db_pengaduan');
if($m->connect_error){
    echo 'ERR:'.$m->connect_error;
    exit(1);
}
$res=$m->query('SHOW TABLE STATUS LIKE "users"');
if(!$res){
    echo 'ERR:'.$m->error;
    exit(1);
}
$row=$res->fetch_assoc();
print_r($row);
