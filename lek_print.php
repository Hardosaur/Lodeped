<?php
//
// LEK_PRINT.PHP
// Распечатка лекарственных назначений на руки
//
require('../settings.php');
require('auth.php');
require('connect.inc');
date_default_timezone_set ("Europe/Minsk"); // чтобы избежать сообщения о проблемах с получением неправильной даты
if (!isset($_SESSION['pat_id'])) die ('Неверные параметры запуска! [1]');
$pat_id=$_SESSION['pat_id'];
if (!is_numeric($pat_id)) die ('Неверные параметры запуска! [2]');
if (!isset($_SESSION['doctor_id'])) die ('Неверные параметры запуска! [3]');
$doctor_id=$_SESSION['doctor_id'];
if (!is_numeric($doctor_id)) die ('Неверные параметры запуска! [4]');
if (isset($_GET['preprint']))
{
  $preprint=1;
  print ('<form method="post" action="lek_print.php">');
  print ('<input type="checkbox" name="head_out" value="1" checked/>&nbsp;Печатать шапку<br><input type="checkbox" name="name_out" value="1" checked/>&nbsp;Печатать ФИО<br>Назначения:<br>');
}
else
{
  print ('<body style="width: 100mm; text-align: justify; font: italic 12px "Book Antiqua"; margin-top: 5px; margin-bottom: 8px">');
  if (isset($_POST['head_out'])) print ('<img src="narrowhead.png" style="width:100mm"><br>');
  if (isset($_POST['name_out']))
  {
          $res = $db->query ('select surname, name, lastname, birth, address from patients where pat_id = '.$pat_id);
          if ($res && $res->num_rows)
          {
              $pat = $res->fetch_object();
              $birth=explode('-',$pat->birth);
              print ('<p>Пациент: '.$pat->surname.' '.$pat->name.' '.$pat->lastname.', '.$birth[2].'.'.$birth[1].'.'.$birth[0].'<br>');
              print ('Адрес:   '.$pat->address.'</p>'."\n");
              $res->free();
          }    
  }
  print ('<ol>');
}
// -----------------------------------------------------------------------------
$date = date('Y-m-d');
$res=$db->query('select * from leks where pat_id='.$pat_id.' and unset_date is null and set_date <= "'.$date.'" order by set_date desc');
if ($res && $res->num_rows)
{
  while ($row = $res->fetch_object())
  {
      if (isset($preprint)) print ('<input name="lek'.$row->id.'" type="checkbox" checked value="1">&nbsp;');
      else if (!isset($_POST['lek'.$row->id])) continue; else print ('<li>');
      print ($row->lek);
      if (!isset($preprint)) print ('</li>'); else print ('<br>');
  }
     $res->free();
}
if (isset($preprint)) print ('<br><input type="submit" value="Печатать"></form>');
else
{
  print ('</ol>');
  print ('<br>Врач ');
  $res=$db->query ('select * from doctors where doctor_id='.$doctor_id);
  if ($res && $res->num_rows==1)
      {
          $row=$res->fetch_object();
          if ($row->category==0) print ('высшей категории');
          elseif ($row->category<3) print ($row->category.'-ой категории');
          if ($row->speciality) print(', '.$row->speciality.' ');
          print ('_________ ('.$row->surname.' '.$row->name{0}.'.'.$row->lastname{0}.'.)</div>');
          $res->free();
      }
}
include ('footer.inc');
?>