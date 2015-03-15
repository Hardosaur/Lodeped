<?php
//
// EDIT_REPORT_BODY.PHP
// Редактирование шаблона отчета
//
require('../../settings.php');
require('../auth.php');
require ('../access.inc');
check_access_level (0);
require('../connect.inc');
if (!isset($_GET['report']) || !is_numeric($_GET['report'])) die ('Неверный параметр запуска!');

// ------------------------------------------------------
if (isset($_POST['body'])) // сохраняем шаблон отчета
{
  if (strlen ($_POST['body']))
  {
      $query = 'update report_types set body="'.$db->real_escape_string($_POST['body']).'" where report_type='.$_GET['report'];
      if (!$db->query($query)) print ('<p style="color: red">Внимание! Сохранить шаблон отчета не удалось! Ошибка: '.$db->error.'</p>');
  }
  else print ('<p style="color: red">Внимание! Ошибка в параметрах! Шаблон отчета пуст?</p>');
}
// ------------------------------------------------------
$res = $db->query ('select title, body from report_types where report_type='.$_GET['report']);
if (!$res || !$res->num_rows) die ('Не найден требуемый отчет!');
$row=$res->fetch_object();
$WINDOW_TITLE = $row->title;
require('../header.inc');
print ('<form method="post"><p><textarea name="body" cols="200" rows="30">'.$row->body.'</textarea></p><p><input type="submit" value="Сохранить"/></p></form>');
require('../footer.inc');
?>