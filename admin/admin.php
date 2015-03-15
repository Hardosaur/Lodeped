<?php
//
// ADMIN.PHP
// Панель администрирования
//
require('../../settings.php');
require('../auth.php');
require('../connect.inc');
$WINDOW_TITLE = 'Администрирование';
require ('../access.inc');
check_access_level (1);
require('../header.inc');
// ------------------------------------------------------
if (isset($_GET['delete_type'])) // удаляем тип осмотра
{
  if (is_numeric($_GET['delete_type']))
  {
      $res = $db->query ('select osm_id from osm_info where osm_type = '.$_GET['delete_type']);
      if ($res && ($num_rows=$res->num_rows)) print ('<p style="color: red">Внимание! Невозможно удалить выбранный тип осмотра, поскольку на его основе сделано '.$num_rows.' протоколов осмотра пациентов!');
      else
      {
         if (!$db->query ('delete from osm_types where osm_type = '.$_GET['delete_type'])) print ('<p style="color: red">Внимание! Удалить выбранный тип осмотра не удалось! '.$db->error.'</p>');
      }
  }
  else print ('<p style="color: red">Внимание! Неверный тип аргумента!</p>');
}
// ------------------------------------------------------
if (isset($_POST['edit_type'])) // сохраняем новое название осмотра
{
  if (is_numeric($_POST['edit_type']) && isset($_POST['description']) && strlen ($_POST['description']))
  {
      $query = 'update osm_types set description="'.$db->real_escape_string($_POST['description']).'" where osm_type='.$_POST['edit_type'];
      if (!$db->query($query)) print ('<p style="color: red">Внимание! Сохранить новое название формы осмотра не удалось! '.$db->error.'</p>');
  }
  else print ('<p style="color: red">Внимание! Ошибка передачи нового названия!</p>');
}
// ------------------------------------------------------
if (isset($_POST['add_type'])) // добавляем новое название осмотра
{
  if (isset($_POST['description']) && strlen ($_POST['description']))
  {
      $query = 'insert into osm_types values (NULL, "'.$db->real_escape_string($_POST['description']).'", NULL, "")';
      if (!$db->query($query)) print ('<p style="color: red">Внимание! Добавить новое название формы осмотра не удалось! '.$db->error.'</p>');
  }
  else print ('<p style="color: red">Внимание! Ошибка передачи названия нового осмотра!</p>');
}
// ------------------------------------------------------
if (isset($_POST['edit_access'])) // сохраняем список доступа к осмотрам
{
  if (is_numeric($_POST['edit_access']))
  {
     if (!$db->query ('delete from osm_access where osm_type='.$_POST['edit_access'])) print ('<p style="color: yellow">Ошибка удаления таблиц доступа! '.$db->error.'</p>');
     $query = 'insert into osm_access values ';
     $count=0;
     foreach ($_POST as $key=>$value)
        if (is_numeric($key))
        {
           if ($count) $query.=',';
           $query.='('.$_POST['edit_access'].','.$key.')';
           $count++;
        }
     if ($count) if (!$db->query ($query)) print ('<p style="color: red">Внимание! Добавить таблицу доступа к осмотру не удалось! '.$db->error.'</p>');
  }
  else print ('<p style="color: red">Внимание! Ошибка передачи кодов доступа к осмотрам!</p>');
}
// ------------------------------------------------------
if (isset($_GET['delete_report'])) // удаляем форму отчета
{
  if (is_numeric($_GET['delete_report']))
  {
      $res = $db->query ('select report_id from reports where report_type = '.$_GET['delete_report']);
      if ($res && ($num_rows=$res->num_rows)) print ('<p style="color: red">Внимание! Невозможно удалить выбранный тип отчета, поскольку на его основе сделано '.$num_rows.' отчетов!');
      else
      {
         if (!$db->query ('delete from report_types where report_type = '.$_GET['delete_report'])) print ('<p style="color: red">Внимание! Удалить выбранный тип отчета не удалось! '.$db->error.'</p>');
      }
  }
  else print ('<p style="color: red">Внимание! Неверный номер отчета!</p>');
}
// ------------------------------------------------------
if (isset($_POST['edit_report'])) // сохраняем новое название отчета
{
  if (is_numeric($_POST['edit_report']) && isset($_POST['title']) && strlen ($_POST['title']))
  {
      $query = 'update report_types set title="'.$db->real_escape_string($_POST['title']).'" where report_type='.$_POST['edit_report'];
      if (!$db->query($query)) print ('<p style="color: red">Внимание! Сохранить новое название отчета не удалось! '.$db->error.'</p>');
  }
  else print ('<p style="color: red">Внимание! Неверные аргументы: не указано название?</p>');
}
// -------------------------------------------------------------
if (isset($_POST['add_report'])) // добавляем новый отчет
{
  if (isset($_POST['title']) && strlen ($_POST['title']))
  {
      $query = 'insert into report_types values (NULL, "'.$db->real_escape_string($_POST['title']).'", "")';
      if (!$db->query($query)) print ('<p style="color: red">Внимание! Добавить новый отчет не удалось! '.$db->error.'</p>');
  }
  else print ('<p style="color: red">Внимание! Ошибка передачи названия нового отчета!</p>');
}
// -----------------------------------------------------------
// Читаем список отделений
$res=$db->query ('select * from departments');
if (!$res && !$res->num_rows) die ('Список отделений пуст! Работа невозможна.');
while ($row = $res->fetch_object()) $departments[]=$row;
$res->free();
//
// Читаем таблицу доступа
$access=array();
$res=$db->query ('select * from osm_access');
if ($res && $res->num_rows)
{
  while ($row = $res->fetch_object())
        $access[$row->osm_type][$row->dep_id]=1;
}
$res->free();
//
// Выводим список осмотров
print ('<h2>Формы осмотров</h2>');
print ('<table style="margin-top: -5px"><tr><td colspan="2" width="300">');
foreach ($departments as $dep) print ('<td class="dot">'.$dep->title.'</td>');
print ("<td></td></tr>\n");
$res=$db->query ('select * from osm_types order by description');
if ($res && $res->num_rows)
{
  while ($row = $res->fetch_object())
  {
      print ('<tr><form method="post" action="admin.php"><td style="padding: 1px 3px"><input type="hidden" name="edit_type" value="'.$row->osm_type.'"/><input type="text" style="border: 1" size="60" name="description" value="'.$row->description.'"/>');
      print ('&nbsp;<img src="../del.png" onclick="javascript:if(confirm(\'Желаете удалить выбранный тип осмотра?\'))document.location=\'admin.php?delete_type='.$row->osm_type.'\'"/>&nbsp;</td>');
      print ('<td class="dot" style="padding: 1px 5px"><a href="../osmotr2.php?type='.$row->osm_type.'" target="_blank">Список&nbsp;полей</a></td></form>');
      print ('<form method="post" action="admin.php"><input type="hidden" name="edit_access" value="'.$row->osm_type.'">');
      foreach ($departments as $dep)
      {
         print ('<td class="dot"><input class="check" type="checkbox" name="'.$dep->dep_id.'" value="1"');
         if (isset ($access[$row->osm_type][$dep->dep_id])) print ('checked');
         print (' onchange="document.getElementById(\'button'.$row->osm_type.'\').style.display=\'block\'"/></td>');
      }
      print ('<td><input class="button" id="button'.$row->osm_type.'" type="submit" value="сохранить" style="display:none"/></td></form></tr>'."\n");
  }
  $res->free();
  print ('</table>');
}
?>
<p>Добавить новый тип осмотра: <form method="post" action="admin.php"><input type="hidden" name="add_type" value="1"/><input type="text" size="60" name="description" value="(название)" onfocus="javascript:this.value=''"/>&nbsp;<input class="button" type="submit" value="Добавить"></form></p>
<input class="button" type="button" value="Редактор полей формы осмотра" onclick="window.open('../osmotr2.php','newwin')"/><br>
<?php
if (access_level() == 0) print ('<input class="button" type="button" value="Импорт/экспорт полей формы осмотра" onclick="window.open(\'osm_import.php\',\'newwin\')"/>');
// -----------------------------------------------
// Выводим список отчетов
print ('<h2>Отчеты</h2>');
print ('<table style="margin-top: -5px">');
$res=$db->query ('select * from report_types order by title');
if ($res && $res->num_rows)
{
  while ($row = $res->fetch_object())
  {
      print ('<tr><form method="post" action="admin.php"><td style="padding: 1px 3px"><input type="hidden" name="edit_report" value="'.$row->report_type.'"/><input type="text" style="border: 1" size="60" name="title" value="'.$row->title.'"/>');
      print ('&nbsp;<img src="../del.png" onclick="javascript:if(confirm(\'Желаете удалить выбранный отчет?\'))document.location=\'admin.php?delete_report='.$row->report_type.'\'"/>&nbsp;</td>');
      print ('<td class="dot" style="padding: 1px 5px"><a href="edit_report_body.php?report='.$row->report_type.'" target="_blank">Шаблон</a></td></form></tr>'."\n");
  }
  $res->free();
  print ('</table>');
}
?>
<p>Добавить новый отчет: <form method="post" action="admin.php"><input type="hidden" name="add_report" value="1"/><input type="text" size="60" name="title" value="(название)" onfocus="javascript:this.value=''"/>&nbsp;<input class="button" type="submit" value="Добавить"></form></p>
<!-- Остальные поля -->
<h2>Работа с диагнозами</h2>
<input class="button" type="button" value="Диагнозы" onclick="window.open('diags_edit.php','newwin')"/>

<h2>Работа с лекарственными препаратами</h2>
<input class="button" type="button" value="Лекарственные назначения" onclick="window.open('lek_editor.php','newwin')"/>
<input class="button" type="button" value="Безрецептурные препараты" onclick="window.open('otc.php','newwin')"/>
<h2>Список пациентов</h2>
<input class="button" type="button" value="Полный список пацентов на контракте" onclick="window.open('pat_list.php','newwin')"/>
<?php
if (access_level() == 0) print <<<END
<h2>Доктора и отделения</h2>
<input class="button" type="button" value="Доктора" onclick="window.open('doctors.php','newwin')"/>
&nbsp;<input class="button" type="button" value="Отделения" onclick="window.open('dep_edit.php','newwin')"/>
END;
print ('<p><br><a class="small" href="/doctor.php">Вернуться на страницу доктора</a></p>');
require('../footer.inc');
?>