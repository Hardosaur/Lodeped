<?php
//
// ���������� ����������� ������ � ������� ���������
//
if (!isset($_GET['table']) || !is_numeric($_GET['table']) || !isset($_GET['value'])) die ('������������ ����������!');
$value=$_GET['value'];
require('../settings.php');
require('auth.php');
include('connect.inc');
// ������ ��������, ������� ����� ����������
$res=$db->query ('select list from lek_data where tab_id='.$_GET['table']);
if (!$res || !$res->num_rows) die ('���������� ����� ������ � ���� ��� ����������!');
$row=$res->fetch_row();
// ���������, �� �������� �� ������������ �������� ��� ������������ ����
$lines=explode(';',$row[0]);
foreach ($lines as $line)
        if (!strcasecmp($value,$line))
        {
            $res->free();
            print ('<p>��������� �������� ��� ���������� � ���� ������.<br><a href="javascript:close()">������� ����</a></p>');
            exit;
        }
$value=$row[0].';'.$value;
$value=$db->real_escape_string($value);
// ���������
$res->free();
if (!$db->query ('update lek_data set list="'.$value.'" where tab_id='.$_GET['table'])) die ('���������� ������ ���������. ������: '.$db->error);
print ('<p>������ ��������� �������.<br><a href="javascript:close()">������� ����</a></p>');
?>