<?php
//
// OSM_PAGES.INC
// �������� ������� ��������� �������
// ������� ������: $osm_page - ����� ��������
// ������ ���� ������� ���������� � ��
//
if ($osm_page==1) print ('<span class="pages"><b>1. �����</b></span><br>');
else print ('<a class="pages" href="javascript:document.getElementById(\'next\').value=\'1\';document.getElementById(\'script\').value=\'\';document.forms[0].submit()">1. �����</a><br>');
//
// ������� ���������� � ���������, ������� ������������� ������
//
$nxid = 0; // id �������� �������� ��������� ��������
$nxpage=0; // ����� ����. ��������
$nxscript=''; // ��� ����. ������� (��� ��������� �������)
$res=$db->query ('select id, name, suffix, value from osm_template where osm_type='.$osm_type.' and type="page"');
if (!$res || !$res->num_rows) die ('��� ������ � ������������ ������� � ������� �������!');
while ($row = $res->fetch_object())
{
  if ($row->value == $osm_page) { print ('<span class="pages"><b>'.$row->value.'. '.$row->name.'</b></span><br>'); $page_id=$row->id; }
  else print ('<a class="pages" href=\'javascript:document.getElementById("next").value="'.$row->value.'";document.getElementById("script").value="'.$row->suffix.'";document.forms[0].submit()\'>'.$row->value.'. '.$row->name.'</a><br>');
  if (!$nxid && $row->value > $osm_page) { $nxid=$row->id; $nxpage=$row->value; $nxscript = $row->suffix; }
}
if (!isset($page_id))
   if ($osm_page>1) die ('�� ������� �������� ����� '.$osm_page.' � �������� �������!');
   else $page_id=0;
$res->free();
?>