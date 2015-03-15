<?php
//
// ���������� ������� ������ �������
//
require('../../settings.php');
$WINDOW_TITLE = '����� ������';
include('../header.inc');
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
  if (!(isset($_POST['doctor_id']))) $doctor_id='NULL';
  else
  {
      if (!is_numeric($_POST['doctor_id'])) $doctor_id='NULL';
      else $doctor_id=$_POST['doctor_id'];
  }
  $q = 'insert into doctors values ('.$doctor_id.', "'.$_POST['doctor_pass'].'", '.$_POST['dep_id'].', "'.$_POST['surname'].'", "'.$_POST['name'].'", "'.$_POST['lastname'].'", '.
       $_POST['category'].', "'.$_POST['speciality'].'", '.$_POST['access_level'].', "'.$_POST['color'].'")';
//  print ($q);
  if (!$db->query($q))
                  print ('<p><font color="red">���������� ������ �� ���������!</font></p>');
  else print ('<p>������ ��������� �������.</p>');
  print ('<p><a href="doctors.php">��������� � ������ ��������</a></p>');
  include ('../footer.inc');
  exit;
}
//
// ������ ���, ��������� ������� �����
?>
<h1>�������� ����� ������� ������ �������</h1>
<p><form method=post>
<table class="light" border=0><col align=right><col align=left>
<tr><td class="left">����� (����� �������� ������):
<td><input type='text' name='doctor_id' size='30' maxlength='30' value='NULL'>
<tr><td class="left">�������:
<td><input type='text' name='surname' size='30' maxlength='30' value=''>
<tr><td class="left">���:
<td><input type='text' name='name' size='30' maxlength='30' value=''>
<tr><td class="left">��������:
<td><input type='text' name='lastname' size='30' maxlength='30' value=''>
<tr><td class="left">������:
<td><input type='text' name='doctor_pass' size='30' maxlength='30' value=''>
<tr><td class="left">������� �������:
<td><input type='text' name='access_level' size='3' maxlength='1' value='3'></tr>
<tr><td colspan="2">0 - �������������, 1 - �����-������������, 2 - �������, 3 - ������������</td></tr>
<tr><td class="left">���������:
<td><input type='text' name='category' size='3' maxlength='3' value=''> 0 - ������, 3 - ��� ���������
<tr><td class="left">������ �������:
<td><input type='text' name='speciality' size='30' maxlength='30' value=''>
<?php
$res=$db->query('select * from departments');
if (!$res) die ('Query error: '.$db->error);
print ('<tr><td class="left">���������:');
print ('<td><select name="dep_id" size="1">');
while ($row = $res->fetch_object())
  print ("<option value='$row->dep_id'>$row->title</option>\n");
$res->free();
print ('</select>');
print ('<tr><td class="left">���� (� ������ ���������):</td><td><select name="color" size="1">');
$colorlist = array (array ('000000','������'), array ('0090D0','�������'),array ('0000B0','�����'),array('009000','�������'),array('A00000','�������'),array('A0A000','������'),array('C07000','���������'),array('A000CC','����������'));
foreach ($colorlist as $color) print ('<option value="'.$color[0].'" style="color: #'.$color[0].'">'.$color[1].'</option>');
print ('</select></table>');
print ('<input class="button" type="submit" value="��������"></form>&nbsp;<input type="button" value="������" onClick="javascript:history.go(-1)">');
include ('../footer.inc');
?>