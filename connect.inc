<?php
$db = new mysqli($dbhost,$dbuser,$dbpass,$dbname);
if (!$db || mysqli_connect_errno()) die ('Connect error: '.mysqli_connect_error());
$db->query ('set names cp1251');
?>