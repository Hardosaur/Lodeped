<?php
//
// ���������� ����������� ������ � ������� �������
//
if (!isset($_GET['osm_type']) || !isset($_GET['id']) || !isset($_GET['add'])) die ('������������ ����������!');
$osm_type=$_GET['osm_type'];
$id=$_GET['id'];
$add=trim($_GET['add']);
if (!is_numeric($osm_type) || !is_numeric($id) || !strlen($add)) die ('�������� ���������!');
require('../settings.php');
require('auth.php');
include('connect.inc');
// ������ ��������, ������� ����� ����������
$res=$db->query ('select type, value from osm_template where osm_type='.$osm_type.' and id='.$id);
if (!$res || !$res->num_rows) die ('���������� ����� ������ � ���� ��� ����������!');
$row=$res->fetch_row();
if ($row[0]!='select') die ('��������� �������� �� ����� ��� select! ��������� ����������.');
$value = $row[1];
// ���������, �� �������� �� ������������ �������� ��� ������������ ����
$vars=explode(';',$value);
foreach ($vars as $var) if (!strcmp($add,$var)) die ('��������� �������� ��� ������� � ������! ���������� �� ����� ������.');
$value.=';'.$add;
$value=$db->real_escape_string($value);
// ���������
if (!$db->query ('update osm_template set value="'.$value.'" where id='.$id)) die ('���������� ������ ���������. ������: '.$db->error);
print ('<p>������ ��������� �������.<br><a href="javascript:close()">������� ����</a></p>');
?>