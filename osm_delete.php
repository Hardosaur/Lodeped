<?php
require('../settings.php');
require('auth.php');
include('header.inc');
include('connect.inc');
// �������� ��������
if (!isset($_GET['osm_id']) || !is_numeric($_GET['osm_id'])) die ('��� ���������� ���������! (������ ������� �������?)');
if (!isset($_GET['confirm'])) // ������ �������������
{
  print ('<p align=center>�� ������������� ������ ������� �������� �������?<br><a href="osm_delete.php?osm_id='.$_GET['osm_id'].'&pat_id='.$_GET['pat_id'].'&confirm=1">��</a>&nbsp;|&nbsp;<a href="patient.php?pat_id='.$_GET['pat_id'].'">���</a>');
}
else
{
  if (!$db->query('delete from osm_info where osm_id = '.$_GET['osm_id'])) die ('������ �������� ������! '.$db->error);
  if (!$db->query('delete from osm_data where osm_id = '.$_GET['osm_id'])) die ('������ �������� ������! '.$db->error);
  print ('<p>������ �� ������� ������� �������!');
}
print ('<br><a href="patient.php?pat_id='.$_GET['pat_id'].'">&lt;&lt; ��������� �� �������� ��������</a></p>');
include('footer.inc');
?>