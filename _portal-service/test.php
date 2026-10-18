<?php

$obj = new stdClass;
$obj->konfigurasi_up_id = 1;
$data = ['data' => $obj];
$data = $data['data'] ?? [];
echo gettype($data);
