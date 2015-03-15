<?php
//
// Просмотр списка уточненных диагнозов (полной истории)
// На входе: $_GET['pat_id']
// Проверка на права доступа пока не реализована
//
require('../settings.php');
require('auth.php');
$WINDOW_TITLE = 'Лист уточненных диагнозов';
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
// Вывод имени пациента
$res = $db->query ('select * from patients where pat_id = '.$pat_id);
if (!$res) die ("Не найдены данные пациента $pat_id!");
$row=$res->fetch_object();
print ('<h1>Лист уточненных диагнозов</h1>');
print ("<p>Пациент: $row->surname $row->name $row->lastname</p>\n");
$res->free();
// Прочитаем диагнозы
$diags = $db->query ('select * from diags, doctors where diags.pat_id='.$pat_id.' and diags.doctor_id = doctors.doctor_id order by diags.set_date desc');
if ($diags)
{
  print ('<p><table border="0" cellpadding="6" cellspacing="1"><col width="80"><col width="80"><col width="120"><col width="60"><col><col>');
  print ('<tr><th>Дата постановки</th><th>Дата снятия</th><th>Фамилия доктора</th><th>Статус</th><th>Диагноз</th></tr>');
  $color = 'black'; // цвет текущей строки
  $status='';
  $tr='even';
  while ($diag=$diags->fetch_object())
  {
      // подготовим поля
      $doctor=$diag->surname.' '.$diag->name{0}.'. '.$diag->lastname{0}.'.';
      $status='актуален';
      if ($diag->unset_date)
      {
          $unset_date=join('.',array_reverse(explode('-',$diag->unset_date)));
          $status='снят';
          $color='#777777';
      }
      else
      {
          $unset_date='';
          $color='black';
      }
      $set_date=join('.',array_reverse(explode('-',$diag->set_date)));
      if ($tr == 'odd') $tr='even'; else $tr='odd';
      print ("\n<tr class='$tr' style='color: $color'><td>$set_date</td><td>$unset_date</td><td>$doctor</td><td>$status</td><td>$diag->diag</td></tr>");
  }
  print ('</table></p>');
  $diags->free();
}
else print ('<p style="font-style: italic">Установленных диагнозов не найдено.</p>');
print ('<p><a href="patient.php?pat_id='.$pat_id.'">Вернуться на страницу пациента</a></p>');
include ('footer.inc');
?>


