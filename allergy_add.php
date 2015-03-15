<?php
//
// ����������� ������ �������� � ����� ��������
//
if (!isset($_GET['lek_id']) || !is_numeric($_GET['lek_id'])) die ('�� ������� ����������� ��������!');
require('../settings.php');
require('auth.php');
require('header.inc');
if (!isset($_SESSION['pat_id']) || !isset($_SESSION['doctor_id']) || !isset($_SESSION['date'])) die ('��� ������ � ������! ������ ������� �������?');
if (!isset($_POST['comment'])) // ��������� ���� ����������� (���������������)
{
  print ('<p>������� �������������� ���������� � ����������� �������� (�������������):</p></p><form method="post"><textarea name="comment" rows="5" cols="60"></textarea><br><input type="submit" value="��������"/></form></p>');
  include ('footer.inc');
  exit;
}
include('connect.inc');
$pat_id=$_SESSION['pat_id'];
$res=$db->query('select all_id from allergies where pat_id='.$pat_id.' and lek_id='.$_GET['lek_id']);
if ($res && $res->num_rows)
{
  $res->free();
  print ('<p>��������� �������� ��� ���������� � ������ ����������� ��� ������� ��������.<br><a href="javascript:close()">������� ����</a></p>');
  exit;
}
if (!$db->query("insert into allergies values (NULL, $pat_id, {$_SESSION['doctor_id']}, {$_GET['lek_id']}, \"{$_SESSION['date']}\", \"{$_POST['comment']}\")"))
   die ('<p>���������� ������ ������ � ����! ������: '.$db->error);
print ('<p>������ �������.<br><a href="javascript:close()">������� ����</a></p>');
include('footer.inc');
?>