<?php
//
// ��������� ������ � ��������, ������� ������
//
require('../../settings.php');
$WINDOW_TITLE = '������ �������';
include('../header.inc');
if (!isset($_GET['doctor_id']) || !is_numeric($_GET['doctor_id'])) // ��� ������������ ���������
{
  print ('<p>�� ����� ����������� ��������! (������ ������� �������?)</p>');
  print ('<a href="doctors.php">��������� � ������ ��������</a>');
  include ('../footer.inc');
  exit;
}
require('../auth.php');
require ('../access.inc');
check_access_level (0);
$db = new mysqli($dbhost,$dbuser,$dbpass,$dbname);
if (mysqli_connect_errno()) die ('Connect error: '.mysqli_connect_error());
$db->query ('set names cp1251');
//
if (isset($_POST['name'])) // �������� ������ ��� ����������
{
  if (!(isset($_POST['surname']) && isset($_POST['lastname']) && isset($_POST['category']) && isset($_POST['dep_id']) && isset($_POST['doctor_pass'])&& isset($_POST['access_level'])))
     die ('Not enough data!');
  $q = 'update doctors set doctor_pass="'.$_POST['doctor_pass'].'", surname="'.$_POST['surname'].'", name="'.$_POST['name'].
                  '", lastname="'.$_POST['lastname'].'", category='.$_POST['category'].', speciality="'.$_POST['speciality'].'", dep_id='.$_POST['dep_id'].', access_level='.$_POST['access_level'].
                  ', color = "'.$_POST['color'].'" where doctor_id='.$_GET['doctor_id'];
//  print ($q);
  if (!$db->query($q))
                  print ('<p><font color="red">���������� ������ �� ���������!</font></p>');
  print ('<p><a href="doctors.php">��������� � ������ ��������</a></p>');
  include ('../footer.inc');
  exit;
}
//
// ������ ���, ��������� ������� �����
$res=$db->query ('select * from doctors where doctor_id = "'.$_GET['doctor_id'].'"');
if (!$res) die ('Query error: '.$db->error);
if (!($row = $res->fetch_object())) die ('No doctors with specified ID!');
print <<<END
<h1>�������� ������ �������</h1>
<p><form method="post">
<table class="left"><col align=right><col align=left>
<tr><td>�������:<td><input type='text' name='surname' size='30' maxlength='30' value='$row->surname'>
<tr><td>���:<td><input type='text' name='name' size='30' maxlength='30' value='$row->name'>
<tr><td>��������:<td><input type='text' name='lastname' size='30' maxlength='30' value='$row->lastname'>
<tr><td>������:<td><input type='text' name='doctor_pass' size='30' maxlength='30' value='$row->doctor_pass'>
<tr><td>������� �������:<td><input type='text' name='access_level' size='3' maxlength='1' value='$row->access_level'></tr>
<tr><td colspan="2">0 - �������������, 1 - �����-������������, 2 - �������, 3 - ������������</td></tr>
<tr><td>���������:<td><input type='text' name='category' size='3' maxlength='3' value='$row->category'>
<tr><td>������ �������:<td><input type='text' name='speciality' size='30' maxlength='30' value='$row->speciality'>
END;
$dep = $row->dep_id; // �������� ��� ����������� ������
$dcolor = $row->color;
$res->free();
$res=$db->query('select * from departments');
if (!$res) die ('Query error: '.$db->error);
print ('<tr><td>���������:');
print ('<td><select name="dep_id" size="1">');
while ($row = $res->fetch_object())
  if ($row->dep_id == $dep) print ("<option selected value='$row->dep_id'>$row->title</option>\n");
  else print ("<option value='$row->dep_id'>$row->title</option>\n");
$res->free();
print ('</select>');
print ('<tr><td>���� (� ������ ���������):</td><td><select name="color" size="1">');
$colorlist = array (array ('000000','������'), array ('0090D0','�������'),array ('0000B0','�����'),array('009000','�������'),array('A00000','�������'),array('A0A000','������'),array('C07000','���������'),array('A000CC','����������'));
foreach ($colorlist as $color)
{
  if ($dcolor == $color[0]) print ('<option selected ');
  else print ('<option ');
  print ('value="'.$color[0].'" style="color: #'.$color[0].'">'.$color[1].'</option>');
}
print ('</select></table>');
print ('<input class="button" type="submit" value="��������"></form>&nbsp;<input type="button" value="������" onClick="javascript:history.go(-1)">');
include ('../footer.inc');
?>