<?php
//
// Страница с данными пациента
// На входе: $_GET['pat_id']
// Проверка на права доступа пока не реализована
//
define ('PAGE_ONE',15);
require('../settings.php');
require('auth.php');
require('access.inc');
$WINDOW_TITLE = 'Страница пациента';
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
// Прочитаем необходимые данные
$doctor_id = $_SESSION['doctor_id'];
$pat_id = $_GET['pat_id'];
if (!isset($_SESSION['pat_id']) || $_SESSION['pat_id']!=$pat_id) $_SESSION['pat_id']=$pat_id;
$pat_info = $db->query ('select * from patients where pat_id = '.$pat_id);
if (!$pat_info || !$pat_info->num_rows) die ('Не найдены данные пациента '.$pat_id);
$phones = $db->query ('select * from phones where pat_id = '.$pat_id);
//
// Личные данные пациента
//
$row=$pat_info->fetch_object();
print ('<a class="small" href="doctor.php">Вернуться на страницу доктора</a>');
print ("<h1>$row->surname $row->name $row->lastname");
if ($row->dispancer) print(' (Д)');
print ('</h1><table><tr><td><h2>Паспортные данные</h2><table class="light"><tr class="even"><td class="left">Пол<td>');
strcmp($row->sex,'male')?print('Женский'):print('Мужской');
print ('</td></tr><tr class="odd"><td class="left">Дата рождения<td>');
list ($year, $month, $day) = explode ('-',$row->birth); // предполагаем, что дата хранится в MySQL в виде YYYY-MM-DD
print ("$day.$month.$year".'</td></tr>');
print ('<tr class="even"><td class="left">Место жительства<td>'.$row->address);
if ($row->entrance) print (", подъезд $row->entrance");
if ($row->floor) print (", этаж $row->floor");
if ($row->domophone) print (", код $row->domophone");
print ('</td></tr><tr class="odd"><td class="left">Телефоны:<td>');
if ($phones && $phones->num_rows)
{
  while ($pr = $phones->fetch_object())
  {
//      print_r($pr);
      if ($pr->owner_name && strlen ($pr->owner_name)) print ($pr->owner_name);
      if ($pr->owner && strlen ($pr->owner)) print (' ('.$pr->owner.')');
      print (' : '.$pr->number.' ('.$pr->operator.') <br>');
  }
  $phones->free();
}
if ($row->comment && strlen($row->comment)) print ('<tr class="odd"><td class="left">Доп. информация</td><td>'.$row->comment);
$pat_info->free();
print ('</table>');
if (access_level()!=2) print ('<a class="small" href="pat_edit.php?pat_id='.$pat_id.'">Редактировать данные пациента</a>');
print ('</td>');
//
// Информация о контрактах
//
print ('<td style="vertical-align: top; padding-left: 20px"><h2>Контракты в ЛОДЭ</h2>');
$cont_info = $db->query ('select * from contracts, doctors where contracts.pat_id = '.$pat_id.' and contracts.doctor_id = doctors.doctor_id order by contracts.valid desc');
if ($cont_info && $cont_info->num_rows)
{
  print ('<table class="light"><col width=140><col width=150>');
  print ('<tr><th>Действителен до<th>Доктор<th>&nbsp;');
  $tr = 'odd';
  while ($row=$cont_info->fetch_object())
  {
      $row->valid?$color='black':$color='#B0B0B0';
      $doctor=$row->surname.' '.$row->name{0}.'. '.$row->lastname{0}.'.';
      $ed = explode('-',$row->expired);
      $expired=$ed[2].'.'.$ed[1].'.'.$ed[0];
      if ($tr == 'odd') $tr='even'; else $tr='odd';
      print ("<tr class='$tr' style='color: $color'><td align=center>$expired<td align=center>$doctor<td align=center><a style='color: $color' href='contract.php?contract=$row->contract'>Информация</a>");
  }
  print ('</table><a class="small" href="contract.php?pat_id='.$pat_id.'">Заключить новый договор</a></td></tr></table>');
  $cont_info->free();
}
else // контрактов еще нет
{
  print ('<p>Действующих или истекших договоров нет. <a class="small" href="contract.php?pat_id='.$pat_id.'">Заключить договор.</a></p></td></tr></table>');
}
//
// Информация об осмотрах
//
$dep_id=$_SESSION['dep_id'];
// фильтр осмотров по отделениям
if (isset($_GET['view']) && is_numeric($_GET['view']))
{
  $view=$_GET['view'];
  print ('<h2>Осмотры специалистов своего отделения</h2><a class="small" href="patient.php?pat_id='.$pat_id.'">Вывести осмотры всех специалистов</a>');
}
else print ('<h2>Осмотры всех специалистов</h2><a class="small" href="patient.php?pat_id='.$pat_id.'&view='.$dep_id.'">Вывести осмотры специалистов своего отделения</a></h2>');
// Список доступных для данного доктора типов осмотра
$res=$db->query('select osm_types.osm_type, osm_types.description from osm_types, osm_access where osm_access.dep_id='.$dep_id.' and osm_types.osm_type=osm_access.osm_type order by description'); // order by osm_type
$osm_types=array();
if ($res && $res->num_rows)
{
  while ($row = $res->fetch_object()) $osm_types[]=$row;
}
$res->free();
// Выводим список
if (isset($view))
   $osm_info = $db->query('select * from osm_info, doctors, osm_types, osm_access where osm_info.pat_id='.$pat_id.' and osm_info.doctor_id = doctors.doctor_id and osm_info.osm_type = osm_types.osm_type and osm_types.osm_type=osm_access.osm_type and osm_access.dep_id='.$view.' order by osm_info.date desc');
else
   $osm_info = $db->query('select * from osm_info, doctors, osm_types where osm_info.pat_id='.$pat_id.' and osm_info.doctor_id = doctors.doctor_id and osm_info.osm_type = osm_types.osm_type order by osm_info.date desc');
$access = access_level();
if ($osm_info && $osm_info->num_rows)
{
  print ('<p><table class="light" style="margin-bottom:0px"><col width=120><col width=300><col width=150><col><col>');
  print ('<tr><th>Дата осмотра<th>Тип осмотра<th>Фамилия доктора<th colspan="2"></th></tr>');
  $rows=$osm_info->num_rows;
  $cnt=0;
  $tr='odd';
  while ($row = $osm_info->fetch_object())
  {
      if ($cnt == PAGE_ONE) print ('<tr id="all_row"><td colspan="5" align="center"><span style="color: #dd2020; cursor: pointer" onclick="document.getElementById(\'all_osm\').style.display=\'block\';document.getElementById(\'all_row\').style.display=\'none\'">Открыть полный список осмотров ('.$rows.')</span></td></tr></table><table class="light" id="all_osm" style="display:none; margin-top:0px"><col width=120><col width=300><col width=150><col><col>');

      $date = explode('-',$row->date);
      $doctor=$row->surname.' '.$row->name{0}.'. '.$row->lastname{0}.'.';
      if ($tr == 'odd') $tr='even'; else $tr='odd';
      print ("\n<tr class='$tr'><td align=center>{$date[2]}.{$date[1]}.{$date[0]}</td>");
      if ((($row->doctor_id == $doctor_id) && $access!=2) || $access<2) // полный доступ к осмотру
         print ('<td class="osmotr" onclick="javascript:if(confirm(\'Желаете изменить существующий протокол осмотра?\'))document.location=\'osmotr2.php?id='.$row->osm_id.'\'">');
      else
         print ('<td class="osmotr" onclick="window.open(\'osmotr2.php?id='.$row->osm_id.'&view=1\',\'osm_view\')">');
      print ($row->description.'</td><td align=center>'.$doctor.'</td>');
      /* - отключено
      if ($row->dep_id == $dep_id || $access<2) // доступ к осмотрам своего отделения
      {
      */
      if ($access!=2) //  ревизору недоступны основные операции
      {
         print ('<td><img class="button" src="copy.png" alt="копировать" onclick="javascript:document.location=\'osmotr2.php?copy='.$row->osm_type.'&id='.$row->osm_id.'\'"/>');
         print ('<span style="position:relative"><img class="button" src="copyto.png" onclick="javascript:if(document.getElementById(\'menu'.$row->osm_id.'\').style.display==\'block\')document.getElementById(\'menu'.$row->osm_id.'\').style.display=\'none\';else document.getElementById(\'menu'.$row->osm_id.'\').style.display=\'block\'"/><div id="menu'.$row->osm_id.'" class="menu" >');
         foreach ($osm_types as $osm) print ('<a style="display: block"><div onclick="javascript:document.getElementById(\'menu'.$row->osm_id.'\').style.display=\'none\';document.location=\'osmotr2.php?copy='.$osm->osm_type.'&id='.$row->osm_id.'\'">'.$osm->description.'</div></a>'."\n");
         print('</div></span>');
      /*
      }
      else print ('<td>');
      */
      print ('<img class="button" src="print.png" alt="печать" onclick="javascript:window.open(\'osmotr2.php?preprint=1&id='.$row->osm_id.'\',\'Print\')">');
      if ($row->doctor_id == $doctor_id || $access<2)
         print ('<img class="button" src="delete.png" value="удалить" onclick="if (confirm (\'Удалить данные осмотра?\')) document.location=\'osm_delete.php?osm_id='.$row->osm_id.'&pat_id='.$pat_id.'\'"/>');
      }
      print ("</td><td align=left>$row->comment</td></tr>");
      $cnt++;
  }
  $osm_info->free();
//  if ($rows>PAGE_ONE) print ('<tr class="'.$tr.'"><td colspan="6" align="center"><a href="osm_list.php?pat_id='.$pat_id.'">Просмотреть весь архив осмотров</a></td></tr>');
  print ('</table>');
}
else print ('<p><i>Данных по осмотрам нет.</i></p>');
//
// Выводим список доступных типов осмотров
//
if ($access!=2)
{
  print ('<p>Новый осмотр:'."\n".'<ul>');
  foreach ($osm_types as $row) print ("<li><a href='osmotr2.php?pat_id=$pat_id&type=$row->osm_type'>$row->description</a></li>\n");
  print ('</ul></p>');
}
//
// Информация об актуальных диагнозах
//
print ('<h2>Диагнозы</h2>');
$res=$db->query('select diag, date_format(set_date,"%d.%m.%Y") from diags where pat_id='.$pat_id.' and unset_date is null order by set_date desc');
if ($res && $res->num_rows)
{
  print ('<p><table class="light"><col width="120"/><col/>'."\n");
  print ('<tr><th>Дата постановки</th><th>Диагноз</th></tr>'."\n");
  $tr='odd';
  while ($row=$res->fetch_row())
  {
      if ($tr == 'odd') $tr='even'; else $tr='odd';
      print ("<tr class='$tr'><td align='center'>{$row[1]}</td><td>{$row[0]}</td></tr>\n");
  }
  $res->free();
  print ('</table></p>');
}
else print ('<p><i>Актуальных диагнозов нет.</i></p>');
print ('<p><a class="small" href="diag_list.php?pat_id='.$pat_id.'">Просмотреть историю диагнозов</a></p>');
//
// Информация о назначениях лекарств
//
print ('<h2>Текущие назначения&nbsp;&nbsp;<input type="button" onclick="window.open(\'lek_print.php?preprint=1\',\'preprint\')"/ value="Распечатать">&nbsp;<input type="button" onclick="window.open(\'recipe.php?preprint=1\',\'preprint\')"/ value="Выписать рецепт"></h2>');
$res=$db->query('select lek, date_format(set_date,"%d.%m.%Y") from leks where pat_id='.$pat_id.' and unset_date is null order by set_date desc');
if ($res && $res->num_rows)
{
  print ('<p><table class="light"><col width="120"/><col/>'."\n");
  print ('<tr><th>Дата назначения</th><th>Назначение</th></tr>'."\n");
  $tr='odd';
  while ($row=$res->fetch_row())
  {
      if ($tr == 'odd') $tr='even'; else $tr='odd';
      print ("<tr class='$tr'><td align='center'>{$row[1]}</td><td>{$row[0]}</td></tr>\n");
  }
  $res->free();
  print ('</table></p>');
}
else print ('<p><i>Текущих назначений нет.</i></p>');
print ('<p><a class="small" href="lek_list.php?pat_id='.$pat_id.'">Посмотреть историю назначений</a></p>');
//
// Информация об аллергиях
//
$res = $db->query('select lek_names.rname from allergies, lek_names where allergies.pat_id='.$pat_id.' and allergies.lek_id=lek_names.lek_id');
if ($res && $res->num_rows)
{
  print ('<h2>Аллергические реакции</h2><p>Отмечены проявления аллергии на следующие препараты:<ul>');
  while ($row = $res->fetch_row()) print ("<li>{$row[0]}</li>");
  $res->free();
  if ($access!=2) print ('</ul><p><a class="small" href="allergy.php?pat_id='.$pat_id.'">Подробнее...</a></p>');
}

//
// Эпикриз
//
if ($access!=2) // нет доступа ревизору
{
print ('<h2>Отчёты</h2>');
$reports = $db->query('select * from reports, doctors, report_types where patient_id='.$pat_id.' and doctors.doctor_id = reports.doctor_id and report_types.report_type = reports.report_type');
if ($reports && $reports->num_rows)
{
  print ('<p><table class="light" style="margin-bottom:0px"><col width=120><col width=300><col width=150><col>');
  print ('<tr><th>Дата<th>Тип отчёта<th>Фамилия доктора<th colspan="2"></th></tr>');
  while ($row = $reports->fetch_object())
  {
      $doctor=$row->surname.' '.$row->name{0}.'. '.$row->lastname{0}.'.';
      $tr='odd';
      if ($tr == 'odd') $tr='even'; else $tr='odd';
      print ("\n".'<tr class="'.$tr.'"><td align=center>'.join('.',array_reverse(explode('-',$row->created))).'</td>');
      if ($row->doctor_id == $doctor_id || $access<2) // полный доступ к осмотру
         print ('<td class="osmotr" onclick="javascript:if(confirm(\'Желаете изменить существующий отчёт?\'))document.location=\'report.php?mode=edit&id='.$row->report_id.'\'">');
      else
         print ('<td class="osmotr" onclick="window.open(\'report.php?mode=view&id='.$row->report_id.'\',\'report_view\')">');
      print ($row->title.'</td><td align=center>'.$doctor.'</td>');
      print ('<td><img class="button" src="print.png" alt="печать" onclick="javascript:window.open(\'report.php?mode=print&id='.$row->report_id.'\',\'Print\')">');
      if ($row->doctor_id == $doctor_id || $access<2)
         print ('<img class="button" src="delete.png" value="удалить" onclick="if (confirm (\'Удалить отчёт?\')) document.location=\'report.php?delete='.$row->report_id.'\'"/>');
      print ("</td></tr>");
  }
  $reports->free();
  print ('</table></p>'."\n");
}
else print ('<p><i>Отчётов нет.</i></p>');
// Выводим список доступных типов отчётов
$res = $db->query ('select report_type, title from report_types');
if ($res && $res->num_rows)
{
  print ('<p>Создать отчёт:<ul>'."\n");
  while ($row = $res->fetch_object())
  {
      print ('<li><a href="report.php?report='.$row->report_type.'">'.$row->title.'</a></li>'."\n");
  }
  $res->free();
  print ('</ul></p>');
}
} // нет доступа ревизору
//
include ('footer.inc');
?>