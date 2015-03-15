<?php
//
// Просмотр полного списка пациентов
// Возможны несколько режимов: вывод всего списка по буквам (первые 10), списка по первой букве, поиск по фамилии
//
require('../settings.php');
require('auth.php');
$WINDOW_TITLE = 'Список пациентов';
include('header.inc');
require('connect.inc');
print '<h1>Просмотр списка пациентов</h1>';
// Получение информации по количеству пациентов
$res = $db->query ('select count(*) from patients');
if (!$res || !$row=$res->fetch_row()) die ('База пациентов пуста!');
print '<p>Всего пациентов в базе: '.$row[0].'</p>';
$res->free();
?>
<p><form method='post'>
Поиск по первым буквам: <input type='text' name='search' size='30' maxlength='30'>&nbsp;<input class="button" type='submit' value='Искать'>
</form></p>
<p>Пациенты на контракте выделены <span style="color: #307030; font-weight: bold">цветом</span></p>
<p><table width=450 cellpadding=3 cellspacing=1 border=0 frame='void'>
<tr><th>Фамилия, имя, отчество</th><th>Дата рождения</th></tr>
<?php
//
// Поиск по фамилии
//
$tr='odd';
if (isset($_POST['search']))
{
  $pats = $db->query ('select * from patients where surname like "'.$_POST['search'].'%" order by surname');
  if (!$pats) die ('Error in query! '.$db->error);
  if (!$pats->num_rows)
  {
      $pats->free();
      print ('</table><p>Пациентов с фамилией, начинающейся на "'.$_POST['search'].'", не найдено.');
  }
  else
  {
      $res = $db->query ('select patients.pat_id from patients,contracts where patients.surname like "'.$_POST['search'].'%" and (contracts.pat_id=patients.pat_id and contracts.doctor_id='.$_SESSION['doctor_id'].' and contracts.valid>0) order by patients.surname');
      if (isset ($own_pats)) unset ($own_pats);
      $own_pats=array();
      if ($res && $res->num_rows)
      {
          while ($row=$res->fetch_row()) $own_pats[]=$row[0];
          $res->free();
      }
      while ($row = $pats->fetch_object())
      {
          if ($tr == 'odd') $tr='even'; else $tr='odd';
          print ("<tr class='$tr'><td align='left'><a ");
          if (in_array($row->pat_id,$own_pats)) print ('class="special" ');
          print ("href='patient.php?pat_id=$row->pat_id'>$row->surname $row->name $row->lastname</a></td>");
          $birth=explode('-',$row->birth);
          print ("<td align=center>$birth[2].$birth[1].$birth[0]</td></tr>");
      }
  }
  print ('</table></p><p><a href="pat_all.php">Вернуться к списку пациентов</a></p>');
  include ('footer.inc');
  exit;
}
//
// Все пациенты с фамилией на заданную букву
//
if (isset($_GET['letter']))
{
  $pats = $db->query ('select * from patients where surname like "'.$_GET['letter'].'%" order by surname');
  if (!$pats) die ('Error in query!');
  if (!$pats->num_rows)
  {
      $pats->free();
      print ('</table><p>Пациентов с фамилией, начинающейся на "'.$_GET['letter'].'", не найдено.');
  }
  else
  {
      $res = $db->query ('select patients.pat_id from patients,contracts where patients.surname like "'.$_GET['letter'].'%" and (contracts.pat_id=patients.pat_id and contracts.doctor_id='.$_SESSION['doctor_id'].' and contracts.valid>0) order by patients.surname');
      if (isset ($own_pats)) unset ($own_pats);
      $own_pats=array();
      if ($res && $res->num_rows)
      {
          while ($row=$res->fetch_row()) $own_pats[]=$row[0];
          $res->free();
      }
      while ($row = $pats->fetch_object())
      {
          if ($tr == 'odd') $tr='even'; else $tr='odd';
          print ("<tr class='$tr'><td align='left'><a ");
          if (in_array($row->pat_id,$own_pats)) print ('class="special" ');
          print ("href='patient.php?pat_id=$row->pat_id'>$row->surname $row->name $row->lastname</a></td>");
          $birth=explode('-',$row->birth);
          print ("<td align=center>$birth[2].$birth[1].$birth[0]</td></tr>");
      }
  }
  print ('</table></p><p><a href="pat_all.php">Вернуться к списку пациентов</a></p>');
  include ('footer.inc');
  exit;
}
//
//Просмотр всех пациентов по первым буквам фамилии
//
// Цикл чтения по первым буквам алфавита
//
$code = ord('А'); // код первой буквы русского алфавита в cp1251
$yacode = ord('Я'); // чтобы не вызывать функцию каждый раз
$p1 = ord ('Ь'); $p2 = ord ('Ъ'); $p3 = ord ('Ы'); // пропустим эти знаки
for (; $code<$yacode; $code++)
{
  if ($code == $p1 || $code == $p2 || $code == $p3) continue;
  $letter = chr ($code);
  $pats = $db->query ('select * from patients where surname like "'.$letter.'%" order by surname');
  if (!$pats) continue;
  if (!$pats->num_rows) {  $pats->free(); continue; }
  $res = $db->query ('select patients.pat_id from patients,contracts where patients.surname like "'.$letter.'%" and (contracts.pat_id=patients.pat_id and contracts.doctor_id='.$_SESSION['doctor_id'].' and contracts.valid>0) order by patients.surname');
  if (isset ($own_pats)) unset ($own_pats);
  $own_pats=array();
  if ($res && $res->num_rows)
  {
     while ($row=$res->fetch_row()) $own_pats[]=$row[0];
     $res->free();
  }
  print ('<tr><td colspan=3><h3>'.$letter.'</h3></td>');
  $count=0;
  while ($count++<7 && $row = $pats->fetch_object())
  {
      if ($tr == 'odd') $tr='even'; else $tr='odd';
      print ('<tr class="'.$tr.'"><td align="left"><a ');
      if (in_array($row->pat_id,$own_pats)) print ('class="special" ');
      print ("href='patient.php?pat_id=$row->pat_id'>$row->surname $row->name $row->lastname</a></td>");
      $birth=explode('-',$row->birth);
      print ("<td align=center>$birth[2].$birth[1].$birth[0]</td></tr>");
  }
  if ($pats->num_rows>7) print ("<tr><td colspan='2' style='text-align: center; font-size: smaller; font-style: bold'><a href='pat_all.php?letter=$letter'>Все пациенты ($pats->num_rows) на букву '$letter'</a></td>\n");
  $pats->free();
}
print ('</table></p><p><input class="button" type="button" value="Внести нового пациента" onClick="javascript:document.location=\'pat_add.php\'"></p><p><a href="doctor.php">Вернуться на страницу доктора</a></p>');
include ('footer.inc');
?>