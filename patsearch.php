<?php
//
// Поиск пациентов по началу строки
//
require('../settings.php');
require('connect.inc');
if (!isset($_GET['s']) || !strlen($_GET['s'])) die('<p><a href="pat_all.php" class="small">закрыть быстрый поиск</a></p>');
//$search=addslashes ();
$search=addslashes(iconv('UTF-8', 'Windows-1251', urldecode($_GET['s'])));
$res=$db->query ('select pat_id, surname, name, lastname, birth from patients where surname like "'.$search.'%"');
if (!$res || !$res->num_rows) die('<p class="small">Ничего не найдено</p><p><a href="pat_all.php" class="small">(закрыть быстрый поиск)</a></p>');
while ($row = $res->fetch_object())
{
  $birth=explode('-',$row->birth);
  print ('<a href="patient.php?pat_id='.$row->pat_id.'" target="_blank">'.$row->surname.' '.$row->name.' '.$row->lastname.', '.$birth[2].'.'.$birth[1].'.'.$birth[0].'</a><br/>');
}
$res->free();
print ('<p><a href="pat_all.php" class="small">(закрыть быстрый поиск)</a></p>');
?>