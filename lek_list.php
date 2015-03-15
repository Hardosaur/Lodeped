<?php
//
// Просмотр истории назначений
// На входе: $_SESSION['pat_id'] или $_GET['pat_id']
//
require('../settings.php');
require('auth.php');
include('header.inc');
require('connect.inc');
// Проверка параметров вызова
if (!isset($_SESSION['pat_id']))
{
  if (!isset($_GET['pat_id']) || !is_numeric($_GET['pat_id'])) // Нет необходимого параметра
  {
      print ('<p>Не задан необходимый параметр! (Скрипт запущен вручную?)</p>');
      print ('<a href="doctor.php">Вернуться к странице доктора</a>');
      include ('footer.inc');
      exit;
  }
  $pat_id=$_GET['pat_id'];
  $_SESSION['pat_id']=$pat_id;
}
else $pat_id=$_SESSION['pat_id'];
//
// Вывод имени пациента
//
$res = $db->query ('select * from patients where pat_id = '.$pat_id);
if (!$res) die ("Не найдены данные пациента $pat_id!");
$row=$res->fetch_object();
print ('<h1>История назначений лекарственных средств</h1>');
print ("<p>Пациент: $row->surname $row->name $row->lastname</p>\n");
$res->free();
// Прочитаем назначения
$curleks = $db->query ('select l.set_date, l.unset_date, l.lek, l.ignored, d.surname from doctors as d inner join leks as l on (d.doctor_id = l.dotor_id) where l.pat_id='.$pat_id.' order by l.set_date desc');
print ($db->error);
if ($curleks && $curleks->num_rows)
{
  print ('<p><table border="0" cellpadding="6" cellspacing="1"><col width="120"><col width="120"><col width="120"><col>');
  print ('<tr><th>Дата назначения</th><th>Дата отмены</th><th>Фамилия доктора</th><th>Текст назначения</th></tr>');
  $tr='odd';
  while ($row=$curleks->fetch_object())
  {
      $d=explode('-',$row->set_date);
      $set_date=sprintf("%02d.%02d.%04d",$d[2],$d[1],$d[0]);
      if ($row->unset_date)
      {
          $d=explode('-',$row->unset_date);
          $unset_date=sprintf("%02d.%02d.%04d",$d[2],$d[1],$d[0]);
      }
      else $unset_date='';
      if ($tr == 'odd') $tr='even'; else $tr='odd';
      print ('<tr class="'.$tr.'"><td align="center">'.$set_date.'</td><td align="center">'.$unset_date.'</td><td align="center">'.$row->surname.'</td><td align="left">'.$row->lek);
      if ($row->ignored > 0) print ('&nbsp;<b>(не принимался)</b>');
      print ('</td></tr>');
  }
  print ('</table></p>');
  $curleks->free();
}
else print ('<p><i>Данных о назначениях лекарств не найдено.</i></p>');
print ('<p><a href="patient.php?pat_id='.$pat_id.'">Вернуться на страницу пациента</a></p>');
include ('footer.inc');
?>