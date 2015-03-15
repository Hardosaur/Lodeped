<?php
//
// ����������� ������ ��������, ��������� � ���� ������
//
require('../../settings.php');
$WINDOW_TITLE = '�������';
include('../header.inc');
require('../auth.php');
require ('../access.inc');
check_access_level (0);
?>
<h1>������� ������������ ������ "����"</h1>
<table class="light">
<?php

// ����� ������� � ����������� �� ��������

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
  print ("<td>���������: $row->category</td>");
  print ("<td>������ �������: $row->speciality</td>");
  print ("<td>[$row->title]</td>");
  print ("<td><a href='doctor_edit.php?doctor_id=$row->doctor_id'>�������������</a></td>");

  print ("<td><a href='doctor_delete.php?doctor_id=$row->doctor_id'>�������</a></td>");
}
print ('</table>');
print ("<input class='button' type='button' value='�������� ����� ������� ������' onClick='javascript:document.location=\"doctor_add.php\"'>");
include ('../footer.inc');
?>