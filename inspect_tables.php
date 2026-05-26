<?php
$m = new mysqli('127.0.0.1', 'root', '', 'db_pengaduan');
if ($m->connect_error) {
    echo 'ERR:' . $m->connect_error;
    exit(1);
}
$res = $m->query('SHOW TABLES');
if (!$res) {
    echo 'ERR:' . $m->error;
    exit(1);
}
while ($row = $res->fetch_row()) {
    echo $row[0] . "\n";
}
