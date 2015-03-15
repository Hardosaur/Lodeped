<?php
require('../settings.php');
require('auth.php');
include('connect.inc');
// проверим пациента
if (!isset($_POST['pat_id']) || !is_numeric($_POST['pat_id'])) die ('Нет требуемого параметра! (Запуск скрипта вручную?)');
$res=$db->query('select name from patients where pat_id='.$_POST['pat_id']);
if (!$res || !$res->num_rows) die ('Неверный ID пациента! Нет такого в базе!');
$res->free();
// Сформируем значение даты
$date = sprintf("%4d-%02d-%02d",$_POST['Date3'],$_POST['Date2'],$_POST['Date1']);
//
// Сохраняем переменные в сессии
//
$_SESSION['date']=$date;
$_SESSION['pat_id']=$_POST['pat_id'];
//сформируем текстовую строку с полями данных
$data='pat_id='.$_POST['pat_id'];
reset($_POST);
while (list($key,$value) = each($_POST))
{
  if (is_numeric($key) && strlen($value)) $data.=';'.$key.'='.$value;
}
$data=$db->real_escape_string ($data); // для случаев с кавычками, точками и т.п.
//
// Обновление информации об осмотре
//
if (isset($_POST['update']))
{
  if (!isset($_POST['osm_id'])) die ('Не передан параметр osm_id!');
  $osm_id=$_POST['osm_id'];
  $query = 'update osm_data set data="'.$data.'" where osm_id='.$_POST['osm_id'];
  if (!$db->query($query))
  {
      print ('Обновление данных в базе не прошло! Ошибка: '.$db->error);
      exit;
  }
  else print ('<p>Данные обновлены успешно!</p>');
}
else // нет признака обновления, значит, добавляем новую запись
{
  $query = 'insert into osm_info values (NULL, '.$_POST['osm_type'].', '.$_POST['pat_id'].', '.$_SESSION['doctor_id'].', "'.$date.'", "")';
  //print ($query);
  if (!$db->query($query)) die ('Добавление в базу не прошло! Ошибка: '.$db->error);
  // выясним номер записи
  $res=$db->query('select LAST_INSERT_ID() from osm_info');
  $row=$res->fetch_array();
  $osm_id=$row[0];
  $res->free();
  // запись в базу протокола осмотра
  $query = 'insert into osm_data values ('.$osm_id.',"'.$data.'")';
  if (!$db->query($query))
  {
      print ('Добавление в базу не прошло! Ошибка: '.$db->error);
      $db->query('delete from osm_info where osm_id = '.$osm_id);
      exit;
  }
  else print ('<p>Данные внесены в базу успешно!</p>');
}
$_SESSION['osm_id']=$osm_id;
header ('Location: diag.php'); // переход к диагнозам
//print ('<p><a href="osm_print.php?osm_id='.$osm_id.'" target="_blank">Перейти к печати протокола осмотра &gt;&gt;</a></p>');
//print ('<p><a href="patient.php?pat_id='.$_POST['pat_id'].'">&lt;&lt; Вернуться на страницу пациента</a></p>');
//include('footer.inc');
?>