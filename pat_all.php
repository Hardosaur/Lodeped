<?php
//
// Просмотр полного списка пациентов
// Пациенты на контракте подсвечиваются
//
require('../settings.php');
require('auth.php');
$WINDOW_TITLE = 'Полный список пациентов';
include('header.inc');
require('connect.inc');
require ('ajax_search.js');
print ('<h1>Список всех пациентов</h1>');
// Быстрый поиск на AJAX
print ('<p>Быстрый поиск: <input id="searchq" type="text" value="" size="20" maxlength="20" onkeyup="javascript:searchNameq()"/></p>');
print ('<div id="search-result">');
// Чтение данных о докторах и пациентах на контракте

$contracted = array (); // двумерный массив ID пациентов
$doctors = array (); // данные о докторах
$res = $db->query ('select doctor_id, surname, name, lastname, color from doctors');
if (!$res || !$res->num_rows) die ('База докторов пуста или недоступна! '.$db->error);
$cnt=0;
$color = array();
print ('<p>На контракте у докторов: ');
while ($doctor = $res->fetch_object())
{
  $pats = $db->query('select patients.pat_id from patients,contracts where (contracts.pat_id=patients.pat_id and contracts.doctor_id='.$doctor->doctor_id.' and contracts.valid>0)');
  if ($pats && $pats->num_rows)
  {
     while ($pat = $pats->fetch_row()) $contracted[$doctor->doctor_id][]=$pat[0];
     $pats->free();
  }
  if (isset($contracted[$doctor->doctor_id]))
  {
     $doctors[]=$doctor->doctor_id;
     $color[$doctor->doctor_id]=$doctor->color;
     print ('<span style="font-weight: bold; color: #'.$doctor->color.'">'.$doctor->surname.' '.$doctor->name{0}.'. '.$doctor->lastname{0}.'.</span>&nbsp;');
  }
  $cnt++;
}
$res->free();
print ('</p><p><table width="450" cellpadding="3" cellspacing="1" border="0" frame="void"><tr><th>Фамилия, имя, отчество</th><th>Дата рождения</th></tr>');

//Просмотр всех пациентов по первым буквам фамилии


$tr='odd';
$code = ord('А'); // код первой буквы русского алфавита в cp1251
$yacode = ord('Я'); // чтобы не вызывать функцию каждый раз
$p1 = ord ('Ь'); $p2 = ord ('Ъ'); $p3 = ord ('Ы'); // пропустим эти знаки
for (; $code<=$yacode; $code++)
{
  if ($code == $p1 || $code == $p2 || $code == $p3) continue;
  $letter = chr ($code);
  $pats = $db->query ('select * from patients where surname like "'.$letter.'%" order by surname');
  if (!$pats) continue;
  if (!$pats->num_rows) {  $pats->free(); continue; }
  print ('<tr><td colspan=3><h3>'.$letter.'</h3></td>');
  while ($row = $pats->fetch_object())
  {
      if ($tr == 'odd') $tr='even'; else $tr='odd';
      print ('<tr class="'.$tr.'"><td align="left"><span style="cursor: pointer; color: #');
      // проверка принадлежности к доктору
      $found=0;
      for ($i=0; $i<count($doctors); $i++)
          if (in_array($row->pat_id,$contracted[$doctors[$i]]))
          {
             print ($color[$doctors[$i]]);
             $found=1;
             break;
          }
      if (!$found) print ('777777');
      print ('" onclick="window.open(\'patient.php?pat_id='.$row->pat_id.'\',\'newwin\')">'.$row->surname.' '.$row->name.' '.$row->lastname.'</span></td>');
      $birth=explode('-',$row->birth);
      print ("<td align=center>$birth[2].$birth[1].$birth[0]</td></tr>");
  }
  $pats->free();
}
print ('</table></div></p><p><input class="button" type="button" value="Внести нового пациента" onClick="javascript:document.location=\'pat_edit.php\'"></p><p><a href="doctor.php">Вернуться на страницу доктора</a></p>');
include ('footer.inc');
?>