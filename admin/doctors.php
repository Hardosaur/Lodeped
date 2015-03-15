<?php
//
// Отображение списка докторов, имеющихся в базе данных
//
require('../../settings.php');
$WINDOW_TITLE = 'Доктора';
include('../header.inc');
require('../auth.php');
require ('../access.inc');
check_access_level (0);
?>
<h1>Доктора медицинского центра "ЛОДЭ"</h1>
<table class="light">
<?php

// Вывод таблицы с информацией по докторам

$db = new mysqli($dbhost,$dbuser,$dbpass,$dbname);
if (mysqli_connect_errno()) die ('Connect error: '.mysqli_connect_error());
$db->query ('set names cp1251');
$result=$db->query ('select doctors.doctor_id, doctors.surname, doctors.name, doctors.lastname, doctors.category, doctors.speciality, departments.title '.
                    'from doctors, departments '.
                    'where doctors.dep_id = departments.dep_id');
if (!$result) die ('Query error: '.$db->error);
$tr='odd';
while ($row = $result->fetch_object())
{
  if ($tr == 'odd') $tr='even';else $tr='odd';
  print ("<tr class='$tr'><td>$row->doctor_id</td><td>$row->surname $row->name $row->lastname</td>");
  print ("<td>Категория: $row->category</td>");
  print ("<td>Учёная степень: $row->speciality</td>");
  print ("<td>[$row->title]</td>");
  print ("<td><a href='doctor_edit.php?doctor_id=$row->doctor_id'>редактировать</a></td>");

  print ("<td><a href='doctor_delete.php?doctor_id=$row->doctor_id'>удалить</a></td>");
}
print ('</table>');
print ("<input class='button' type='button' value='Добавить новую учетную запись' onClick='javascript:document.location=\"doctor_add.php\"'>");
include ('../footer.inc');
?>