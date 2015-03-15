<?php
//
// REPORT.PHP
// Ввод данных для подготовки отчета по шаблону
// Тип отчета передается в $_GET['report']
//
require('../settings.php');
require('auth.php');
require('connect.inc');
//
if (!isset($_SESSION['doctor_id']) || !isset ($_SESSION['pat_id'])) die ('Неверный способ запуска! (Нет данных о докторе и пациенте)');
// Удаление отчета
if (isset($_GET['delete']) && is_numeric ($_GET['delete']))
{
  if ($db->query ('delete from report_data where report_id='.$_GET['delete']) && $db->query ('delete from reports where report_id='.$_GET['delete'])) die ('Отчёт удален успешно! <a class="small" href="patient.php?pat_id='.$_SESSION['pat_id'].'">Вернуться на страницу пациента</a>');
  else die ('Удалить отчёт не удалось! '.$db->error);
}
// 1: создание нового отчета
if (isset ($_GET['report']) && is_numeric($_GET['report']))
{
  $report_type = $_GET['report'];
  $report_id=0;
}
// 2: редактирование или просмотр отчета
if (isset($_GET['mode'])) $mode=$_GET['mode'];
//
$vars = array();
date_default_timezone_set ("Europe/Minsk"); // чтобы избежать сообщения о проблемах с получением неправильной даты
// ID отчета
if (isset($_POST['report_id'])) // данные отчета поступили на вход
{
  $mode='edit';
  $report_id = $_POST['report_id'];
  if (isset($_POST['report_type']) && is_numeric($_POST['report_type'])) $report_type=$_POST['report_type'];
  if (!isset($report_type)) die ('Ошибка: потерян тип отчета!');
  if (!$report_id) // данные еще не сохранялись, отчет не создавался
  {
      if (!isset($_SESSION['doctor_id'])) print ('<p style="color: red">Нет данных о докторе!</p>');
      else
      {
          if (!$db->query('insert into reports values (NULL,'.$report_type.',"'.date('Y-m-d').'",'.$_SESSION['doctor_id'].','.$_SESSION['pat_id'].')')) die ('<p style="color: red">Ошибка сохранения данных! '.$db->error.'</p>');
          $res=$db->query('select LAST_INSERT_ID() from reports'); // выясним номер записи
          if (!$res || !$res->num_rows) die ('<p style="color: red">Ошибка: '.$db->error.'</p>');
          $row=$res->fetch_row();
          $report_id=$row[0];
          $res->free();
      }
  }
  else // удалим все предыдущие данные
  {
      if (!$db->query('delete from report_data where report_id='.$report_id)) die ('<p style="color: red">Ошибка удаления предыдущих данных! '.$db->error.'</p>');
  }
  // формируем запрос на сохранение всех данных
  $query = 'insert into report_data values ';
  // добавляем переменные, которые сохранены под порядковыми именами
  reset($_POST);
  foreach ($_POST as $key=>$value)
  {
      $value=trim($value);
      if (is_numeric($key) && strlen ($value)) $query .= '('.$report_id.','.$key.',"'.addslashes($value).'"), ';
      $vars[$key]=$value;
  }
  $query = substr ($query,0,-2); // отбросим два последних символа
//  print ('<p>'.$query.'</p>');
  if (!$db->query($query)) die ('<p style="color: red">Ошибка сохранения данных! '.$db->error.'</p>');
}
elseif (isset($_GET['id']) && is_numeric ($_GET['id']))
{
  $report_id=$_GET['id'];
  $res=$db->query ('select report_type from reports where report_id='.$_GET['id']);
  if (!$res || !$res->num_rows) die ('Неверный номер отчета! Не найден в базе. '.$db->error);
  $row=$res->fetch_object();
  $report_type=$row->report_type;
  $res->free();
}
// читаем данные, если они не поступали через POST
if (!isset($_POST['report_id']) && $report_id)
{
  $res = $db->query ('select var_id, value from report_data where report_id='.$report_id);
  if (!$res || !$res->num_rows) die ('<p style="color: red">Данные отчета №'.$report_id.' не читаются! Ошибка: '.$db->error.'</p>');
  while ($row = $res->fetch_object())
  {
       $vars[$row->var_id]=$row->value;
  }
  $res->free();
}
// читаем форму отчета
$res = $db->query ('select * from report_types where report_type = '.$report_type);
if (!$res || !$res->num_rows) die ('<p style="color: red">Шаблон отчета не читается! Ошибка: '.$db->error.'</p>');
$row=$res->fetch_object();
$report_template = explode("\n",$row->body);
$report_title=$WINDOW_TITLE=$row->title;
$res->free();
// выводим заголовок
if (isset($mode))
{
  if ($mode=='print')
  {
      print ('<html><head><link rel="stylesheet" type="text/css" href="main.css">'."\n");
      require ('print.inc');
  }
  elseif ($mode=='view') print ('<html><head><title>Просмотр отчета</title><link rel="stylesheet" type="text/css" href="main.css">');
  else
  {
       include('header.inc');
       print ('<form action="report.php" method="post"><input name="report_id" type="hidden" value="'.$report_id.'"/><input name="report_type" type="hidden" value="'.$report_type.'"/>'."\n");
  }
}
else
{
  include('header.inc');
  print ('<form action="report.php" method="post"><input name="report_id" type="hidden" value="'.$report_id.'"/><input name="report_type" type="hidden" value="'.$report_type.'"/>'."\n");
}
// парсим форму: сочетание #xxx заменяем на поле ввода переменной xxx
// print_r ($vars);
foreach ($vars as $key=>$value) $vars[$key]=nl2br(htmlspecialchars($value,ENT_COMPAT,'cp1251'));
//$srch = array ('/#(\d+):(\d+)/e','/@(\d+):(\d+):(\d+)/e');
$srch = array ('/#(\d+):(\d+)/','/@(\d+):(\d+):(\d+)/');
if (isset($mode) && ($mode=='view' || $mode=='print'))
   $rplc = function ($matches) use ($vars) {if (isset ($vars[$matches[1]])) return $vars[$matches[1]];else return '';};
else
$rplc = function ($matches) use ($vars) {if (!isset ($vars[$matches[1]])) $vars[$matches[1]]=''; if ($matches[0][0]=='#') return '<input name="'.$matches[1].'" type="text" size="'.$matches[2].'" value="'.$vars[$matches[1]].'"/>';
else return '<textarea style="vertical-align:top" name="'.$matches[1].'" cols="'.$matches[2].'" rows="'.$matches[3].'">'.$vars[$matches[1]].'</textarea>';};
foreach ($report_template as $line)
{
  // ищем подключаемые модули
  if (preg_match('/module\((\w+)\)/i',$line,$matches)) include('report/'.$matches[1].'.inc');
  else
  {
      $line=preg_replace_callback ($srch,$rplc,$line);
      print ('<p>'.$line.'</p>');
  }
}
if (!isset($mode) || $mode=='edit') print ('<br><input type="submit" value="Сохранить"/>&nbsp;<a class="small" href="patient.php?pat_id='.$_SESSION['pat_id'].'">Вернуться на страницу пациента</a></form>');
include('footer.inc');
?>