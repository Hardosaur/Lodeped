<?php
//
// FIELD_ADD.PHP
// ���������� � ���� ���� "select" ������ �������� ����������� ������
// ������� ������: id=xxx, ��� xxx - id ����
//                 text=
//
require('header.inc');
require('../settings.php');
require('auth.php');
require('connect.inc');
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) die ('���������� ������: id ���� �� ������ ��� �������!');
if (!isset($_GET['text']) || !strlen($_GET['text'])) die ('����������� ����� ���� ��� �� ������!');
$res = $db->query ('select * from osm_fields where id='.$_GET['id']);
if (!$res || !$res->num_rows) die ('������ ������ ���� ����� ����� �������! '.$db->error);
$field=$res->fetch_object();
$res->free();
if ($field->type != 'select') die ('������ ��������� ���� ���� '.$field->type.'!');
$field->value.=$delim.$db->real_escape_string($_GET['text']);
$query = 'update osm_fields set value="'.$field->value.'" where id='.$_GET['id'];
print ($query);
if (!$db->query($query)) die ('������ ���������� ���� ������! '.$db->error);
print ('<p>������ ������� ���������! <a href="javascript:window.close()">�������</a></p>');
require ('footer.inc');
?>