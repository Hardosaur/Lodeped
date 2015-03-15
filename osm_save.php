<?php
require('../settings.php');
require('auth.php');
include('connect.inc');
// �������� ��������
if (!isset($_POST['pat_id']) || !is_numeric($_POST['pat_id'])) die ('��� ���������� ���������! (������ ������� �������?)');
$res=$db->query('select name from patients where pat_id='.$_POST['pat_id']);
if (!$res || !$res->num_rows) die ('�������� ID ��������! ��� ������ � ����!');
$res->free();
// ���������� �������� ����
$date = sprintf("%4d-%02d-%02d",$_POST['Date3'],$_POST['Date2'],$_POST['Date1']);
//
// ��������� ���������� � ������
//
$_SESSION['date']=$date;
$_SESSION['pat_id']=$_POST['pat_id'];
//���������� ��������� ������ � ������ ������
$data='pat_id='.$_POST['pat_id'];
reset($_POST);
while (list($key,$value) = each($_POST))
{
  if (is_numeric($key) && strlen($value)) $data.=';'.$key.'='.$value;
}
$data=$db->real_escape_string ($data); // ��� ������� � ���������, ������� � �.�.
//
// ���������� ���������� �� �������
//
if (isset($_POST['update']))
{
  if (!isset($_POST['osm_id'])) die ('�� ������� �������� osm_id!');
  $osm_id=$_POST['osm_id'];
  $query = 'update osm_data set data="'.$data.'" where osm_id='.$_POST['osm_id'];
  if (!$db->query($query))
  {
      print ('���������� ������ � ���� �� ������! ������: '.$db->error);
      exit;
  }
  else print ('<p>������ ��������� �������!</p>');
}
else // ��� �������� ����������, ������, ��������� ����� ������
{
  $query = 'insert into osm_info values (NULL, '.$_POST['osm_type'].', '.$_POST['pat_id'].', '.$_SESSION['doctor_id'].', "'.$date.'", "")';
  //print ($query);
  if (!$db->query($query)) die ('���������� � ���� �� ������! ������: '.$db->error);
  // ������� ����� ������
  $res=$db->query('select LAST_INSERT_ID() from osm_info');
  $row=$res->fetch_array();
  $osm_id=$row[0];
  $res->free();
  // ������ � ���� ��������� �������
  $query = 'insert into osm_data values ('.$osm_id.',"'.$data.'")';
  if (!$db->query($query))
  {
      print ('���������� � ���� �� ������! ������: '.$db->error);
      $db->query('delete from osm_info where osm_id = '.$osm_id);
      exit;
  }
  else print ('<p>������ ������� � ���� �������!</p>');
}
$_SESSION['osm_id']=$osm_id;
header ('Location: diag.php'); // ������� � ���������
//print ('<p><a href="osm_print.php?osm_id='.$osm_id.'" target="_blank">������� � ������ ��������� ������� &gt;&gt;</a></p>');
//print ('<p><a href="patient.php?pat_id='.$_POST['pat_id'].'">&lt;&lt; ��������� �� �������� ��������</a></p>');
//include('footer.inc');
?>