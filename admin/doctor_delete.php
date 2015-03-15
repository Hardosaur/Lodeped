<?php
//
// �������� ������� ������ �������
//
require('../../settings.php');
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
// �������� �� ������� ������ � ��
//
// �������� ������� Contracts
$res=$db->query ('select contract from contracts where doctor_id = "'.$_GET['doctor_id'].'" && valid>0');
if (!$res) die ('Query error: '.$db->error);
if ($res->num_rows)
{ // ���� ������
  print ('<p>�������� ������� ������ ������� ����������! ������� '.$res->num_rows.' �������� ��������� � ����������!</p>');
  $res->free();
}
else
{
  $res->free();
  // �������� ��������� ������ ������
  // ���� ������ ���
  if ($db->query('delete from doctors where doctor_id='.$_GET['doctor_id'])) print ('<p>������ ������� �������.</p>');
  else print ('<p>������ ��������! '.$db->error.'</p>');
}
print ('<p><a href="doctors.php">��������� � ������ ��������</a></p>');
include ('../footer.inc');
?>