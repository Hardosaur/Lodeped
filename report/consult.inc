<?php
//
// ����� ������ � ����������� �������� � �������������
// ������� ������: $pat_id - ID ��������
//                 $vars[1..3, 10..12] - ��������� � �������� ����
//
if (include 'report/date2.inc') print ('<p>[ ��������� � �������� ���� �� ������� ��� ������� �������! ]</p>');
else
{
$res=$db->query ('select osm_info.date, osm_types.title from osm_info, osm_types where osm_info.pat_id='.$pat_id.' and osm_info.date <= "'.$date2_mysql.'" and osm_info.date >= "'.$date1_mysql.'" and osm_types.osm_type=osm_info.osm_type order by osm_info.date');
if ($res && $res->num_rows)
{
  print ('<ul>');
  while ($row = $res->fetch_object())
  {
      if (stripos($row->title,'��������') === false && stripos($row->title,'�������') === false && stripos($row->title,'������������') === false)
      print ('<li>'.join('.',array_reverse(explode('-',$row->date))).': '.strtolower($row->title).'</li>');
  }
  $res->free();
  print ('</ul>');
}
}
?>