<?php
//
// Просмотр списка аллергий
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
// Разберемся с датами
//
if (!isset($_SESSION['date']))
{
  date_default_timezone_set ("Europe/Minsk"); // чтобы избежать сообщения о проблемах с получением неправильной даты
  $today=getdate(); // получение текущей даты
  $_SESSION['date']=$today["year"].'-'.$today["mon"].'-'.$today["mday"];
}
//
// Запрос на удаление
//
if (isset($_GET['delete']) && is_numeric($_GET['delete']))
{
  if (!$db->query('delete from allergies where all_id='.$_GET['delete'])) print ('<p>Ошибка удаление записи об аллергии! '.$db->error.'</p>');
}
// Вывод имени пациента
$res = $db->query ('select * from patients where pat_id = '.$pat_id);
if (!$res) die ("Не найдены данные пациента $pat_id!");
$row=$res->fetch_object();
print ('<h1>Лист аллергических реакций</h1>');
print ("<p>Пациент: $row->surname $row->name $row->lastname</p>\n");
$res->free();
// Прочитаем диагнозы
$allergies = $db->query ('select * from allergies, lek_names, doctors where allergies.pat_id='.$pat_id.' and lek_names.lek_id=allergies.lek_id and doctors.doctor_id = allergies.doctor_id order by allergies.set_date desc');
if ($allergies && $allergies->num_rows)
{
  print ('<p><table border="0" cellpadding="6" cellspacing="1"><col><col width="120"><col width="120"><col width="150"><col>');
  print ('<tr><th>&nbsp;</th><th>Дата выявления</th><th>Фамилия доктора</th><th>На какой препарат</th><th>Пояснения</th></tr>');
  while ($row=$allergies->fetch_object())
  {
      // подготовим поля
      $doctor=$row->surname.' '.$row->name{0}.'. '.$row->lastname{0}.'.';
      $d=explode('-',$row->set_date);
      $set_date=sprintf("%02d.%02d.%04d",$d[2],$d[1],$d[0]);
      print ("\n<tr><td>[<a href='allergy.php?delete=$row->all_id' onclick='javascript:return Confirm(\"Удалить данные об аллергии?\")'>удалить</a>]<td>$set_date</td><td>$doctor</td><td>$row->rname</td><td>$row->comment</td><td>&nbsp;</td></tr>");
  }
  print ('</table></p>');
  $allergies->free();
}
else print ('<p><i>Данных об аллергиях не найдено.</i></p>');
print ('<h2>Добавить новую информацию об аллергии</h2>');
//
// Форма выбора препарата для пометки аллергии
//
print ('<p><table border="0"><tr valign="top" align="center">');
if (!isset($_GET['letter'])) // не выбрана первая буква, выводим их список для выбора
{
    print ('<td>Выберите первую букву русского названия препарата:<br>');
    // выводим только первые буквы названий
    $res=$db->query('select rname from lek_names order by rname');
    if (!$res || !$res->num_rows) die ('База данных названий лекарств пуста или недоступна! Ошибка: '.$db->error);
    $letters=array(); // массив первых букв
    while ($row=$res->fetch_array())
    {
          $letter = $row[0]{0};
          if (!isset($letters[$letter])) $letters[$letter]=1;
          else $letters[$letter]++;
    }
    $res->free();
    foreach ($letters as $letter => $value)
    {
         print ('<input type="button" value="'.$letter.'" onclick="javascript:document.location=\'allergy.php?letter='.$letter.'\'"/> ');
    }
}
else // Передана первая буква, вывести список лекарств на эту букву
{
    print ('<td width="200">Первая буква:<br><b>'.$_GET['letter'].'</b><br>(<a href="allergy.php">выбрать другую</a>)</td>');
    // получаем из базы все лекарства на эту букву
    $res=$db->query('select * from lek_names where rname like "'.$_GET['letter'].'%"');
    if (!$res || !$res->num_rows) die ('База данных названий лекарств не содержит названий на букву'.$_GET['letter'].'! Ошибка: '.$db->error);
    $size=$res->num_rows;
    if ($size<2) $size=2;
    print ('<td width="300">Препараты:<br><select size="'.$size.'" onchange="javascript:window.open(\'allergy_add.php?lek_id=\'+this.options[this.selectedIndex].value,\'\',\'\')">');
    while ($row = $res->fetch_object())
    {
        print ("\n<option value='{$row->lek_id}'>$row->rname</option>");
    }
    $res->free();
}
print ('</td></tr></table></p>');
print ('<p><a href="patient.php?pat_id='.$pat_id.'">Вернуться на страницу пациента</a></p>');
include ('footer.inc');
?>


