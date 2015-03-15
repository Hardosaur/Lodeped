<?php
//
// Просмотр списка осмотров
// На входе: $_GET['pat_id'], опционально $_GET['from']
// Проверка на права доступа пока не реализована
//
require('../settings.php');
require('auth.php');
include('header.inc');
require('connect.inc');
// Проверка параметров вызова
if (!isset($_GET['pat_id']) || !is_numeric($_GET['pat_id'])) // Нет необходимого параметра
{
  print ('<p>Не задан необходимый параметр! (Скрипт запущен вручную?)</p>');
  print ('<a href="doctor.php">Вернуться к странице доктора</a>');
  include ('footer.inc');
  exit;
}
$pat_id=$_GET['pat_id'];
if (!isset($_GET['from'])) $from=0; else $from=$_GET['from'];
// Прочитаем необходимые данные
$res = $db->query ("select * from patients where pat_id = $pat_id");
if (!$res) die ("Не найдены данные пациента $pat_id!");
$row=$res->fetch_object();
print ('<h1>История осмотров</h1>');
print ("<p>Пациент: $row->surname $row->name $row->lastname</p>\n");
$res->free();
// Чтение данных об осмотрах
$osm_info = $db->query('select * from osm_info, doctors, osm_types where osm_info.pat_id='.$pat_id.' and osm_info.doctor_id = doctors.doctor_id and osm_info.osm_type = osm_types.osm_type order by osm_info.date desc limit '.$from.',30');
if ($osm_info && $osm_info->num_rows)
{
  print ('<p><table class="light"><col width=120><col width=300><col width=150><col><col>');
  print ('<tr><th>Дата осмотра<th>Тип осмотра<th>Фамилия доктора<th>&nbsp;<th>Примечание');
  $rows=$osm_info->num_rows;
  $cnt=0;
  $tr='odd';
  while ($row = $osm_info->fetch_object())
  {
      $date = explode('-',$row->date);
      $doctor=$row->surname.' '.$row->name{0}.'. '.$row->lastname{0}.'.';
      if ($tr == 'odd') $tr='even'; else $tr='odd';
      print ("\n<tr class='$tr'><td align=center>{$date[2]}.{$date[1]}.{$date[0]}</td><td align=center onclick='javascript:if(confirm(\"Желаете изменить существующий протокол осмотра?\"))document.location=\"osmotr2.php?id=$row->osm_id\"'>$row->description</td><td align=center>$doctor</td>");
      print ("<td><input type='button' class='button' value='копировать' onclick='javascript:document.location=\"osmotr2.php?copy=1&id=$row->osm_id\"'/>");
      print ('<span style="position:relative"><input type="button" class="button" value="копировать в другой тип осмотра" onclick="javascript:if(document.getElementById(\'menu'.$row->osm_id.'\').style.display==\'block\')document.getElementById(\'menu'.$row->osm_id.'\').style.display=\'none\';else document.getElementById(\'menu'.$row->osm_id.'\').style.display=\'block\'"><div id="menu'.$row->osm_id.'" class="menu" >');
      foreach ($osm_types as $osm) print ('<a style="display: block"><div onclick="javascript:document.getElementById(\'menu'.$row->osm_id.'\').style.display=\'none\';document.location=\'osmotr2.php?copy='.$osm->osm_type.'&id='.$row->osm_id.'\'">'.$osm->description.'</div></a>'."\n");
      print('</div></span>');
      print ("<input type='button' class='button' value='печать' onclick='javascript:window.open(\"osmotr2.php?print=1&id=$row->osm_id\",\"Print\")'><input type='button' class='button' value='удалить' onclick='if (confirm (\"Удалить данные осмотра?\")) document.location=\"osm_delete.php?osm_id=$row->osm_id&pat_id=$pat_id\"'/>");
      print ("<td align=left>$row->comment</td></tr>");
  }
  print ('<tr><td colspan="5" align="center">');
  if ($from>30) { $from-=30; print ("<a href='osm_list.php?pat_id=$pat_id&from=$from'>Предыдущие</a>&nbsp;|&nbsp;"); $from+=30; }
  if ($osm_info->num_rows>=30) { $from+=30; print ("<a href='osm_list.php?pat_id=$pat_id&from=$from'>Следующие</a>"); }
  print ('</td></tr></table>');
  $osm_info->free();
}
else print ('<p>Данных по осмотрам нет.</p>');
print ('<p><a href="patient.php?pat_id='.$pat_id.'">Вернуться на страницу пациента</a></p>');
include('footer.inc');
?>