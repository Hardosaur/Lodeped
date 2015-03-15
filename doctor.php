<?php
//
// Начальная страница доктора
//
require('../settings.php');
require('auth.php');
require ('access.inc');
$WINDOW_TITLE = 'Страница доктора';
include('header.inc');
require('connect.inc');
// Прочитаем необходимые данные
date_default_timezone_set ("Europe/Minsk"); // чтобы избежать сообщения о проблемах с получением неправильной даты
// Выбор доктора (режим ревизора)
if (isset($_GET['doctor']))
{
  if (!is_numeric($_GET['doctor'])) print ('<p style="color:red">Неверный ID доктора!</p>');
  elseif (!isset($_SESSION['revisor_id'])) print ('<p style="color:red">Неверный формат вызова!</p>');
  else $_SESSION['doctor_id']=$_GET['doctor'];
}
// Список докторов для ревизора
if (isset($_SESSION['revisor_id']) && (!isset($_SESSION['doctor_id']) || isset($_GET['change'])))
{
  print ('<h1>Выберите доктора</h1>'."\n");
  $docs = $db->query ('select doctor_id, surname, name, lastname from doctors order by surname');
  if (!$docs || !$docs->num_rows) die ('Не найдена информация о докторах!');
  print ('<ul>');
  while ($row=$docs->fetch_object())
  {
      print ("\n".'<li><a href="doctor.php?doctor='.$row->doctor_id.'">'.$row->surname.' '.$row->name{0}.'. '.$row->lastname{0}.'.</a></li>');
  }
  $docs->free();
  include ('footer.inc');
  return;
}
// Режим работы доктора
$doctor_id = $_SESSION['doctor_id'];
if (!isset($_GET['sort'])) $_GET['sort']='birth';
//$sort='patients.birth desc';
if ($_GET['sort']== 'birth') $sort='patients.birth desc';
elseif ($_GET['sort']== 'surname') $sort='patients.surname asc';
elseif ($_GET['sort']== 'age_group') $sort='contracts.age_group, patients.surname asc';
elseif ($_GET['sort']== 'signed') $sort='contracts.signed, patients.surname asc';
if (isset($_GET['full']) && $_GET['full']) $full=1; else $full=0; // полная информация о пациентах
$doc_info = $db->query ("select * from doctors, departments where doctors.doctor_id=$doctor_id and departments.dep_id=doctors.dep_id");
$patients = $db->query ("select * from contracts, patients where contracts.valid>0 and contracts.doctor_id = $doctor_id and patients.pat_id = contracts.pat_id order by $sort");
if (!$row=$doc_info->fetch_object()) die ('Не найдена информация о докторе!');
print ('<h1>'.$row->surname.' '.$row->name.' '.$row->lastname.'</h1><p>Врач ');
switch ($row->category)
{
  case 0: print ('высшей категории'); break;
  case 1: print ('первой категории'); break;
  case 2: print ('второй категории'); break;
  default: print ('без категории'); break;
}
print (', '.$row->speciality.', отделение "'.$row->title.'" (<a class="small" href="doctor.php?logout=1">Выйти</a>)');
$doc_info->free();
if (access_level()<=1) print ('<p><a class="small" href="/admin/admin.php" target="_blank">Администрирование</a></p>');
if (access_level()==2) print ('<p><a class="small" href="doctor.php?change=1">Выбрать другого доктора</a></p>');
$today=getdate();
//
// Пациенты
//
print <<<END2
<h2>Пациенты</h2>
<p>
<input class="button" type='button' value='Список всех пациентов' onclick='javascript:document.location="pat_all.php"'>&nbsp;
<input class="button" type='button' value='Внести нового пациента' onclick='javascript:document.location="pat_edit.php"'>
</p>
END2;
//if ($_SESSION['dep_id']==1)
if ($patients->num_rows)
{
print <<<END3
<h2>Пациенты на контракте</h2>
<form method="post" action="formula.php"><p>Произвести рассчет количества пациентов на дату
<input type="text" name="day" size="2" value="{$today['mday']}"/>.<input type="text" name="month" size="2" value="{$today['mon']}"/>.<input type="text" name="year" size="4" value="{$today['year']}"/>
<input type="submit" value="Рассчитать"></p></form>
<p><table cellpadding=3 cellspacing=0 border=0 bordercolor='gray' frame='void'>
<col width="250">
<col width="80">
<col width="120">
<col width="120">
<col width="120">
<col width="30">
<col width="5">
<col width="200">
<col width="400">
<tr><th><a href="doctor.php?sort=surname&full=$full">Фамилия, имя, отчество</a><th><a href="doctor.php?sort=birth&full=$full">Возраст</a><th>Дата рождения</th><th><a href="doctor.php?sort=signed&full=$full">Дата заключения контракта</th><th><a href="doctor.php?sort=age_group&full=$full">Группа учёта</a><th>Д
END3;
if ($full) print ('<th><a href="doctor.php?sort='.$_GET['sort'].'&full=0">&laquo;</a></th><th>Адрес</th><th>Телефоны родственников</th></tr>');
else print ('<th><a href="doctor.php?sort='.$_GET['sort'].'&full=1">&raquo;</a></th></tr>');

$ages0_1 = array(0,0,0,0);
$ages1_3 = array(0,0,0,0);
$ages3_6 = array(0,0,0,0);
$ages6_ = array(0,0,0,0);
// Вывод таблицы с информацией по пациентам
$tr='odd';
while ($row = $patients->fetch_object())
{
  $birth = explode ('-',$row->birth);
  $age = (int)((time()-mktime(0,0,0,$birth[1],$birth[2],$birth[0]))/31558433);// 365.25*24*3600 - кол-во секунд в году, вычисляем число полных лет
  $signed = explode ('-',$row->signed);
  $age2 = (int)((mktime(0,0,0,$signed[1],$signed[2],$signed[0])-mktime(0,0,0,$birth[1],$birth[2],$birth[0]))/31558433); // на дату заключения договора
  // готовим данные о возрасте для вывода в таблице
  if ($age==0)
  {
      $agestr='до 1 года';
      if (!isset($prevage) && $sort == 'patients.birth desc') print ('<tr><td colspan="4" class="separator">Возраст до года</td></tr>');

  }
  else
  {
      switch ($age)
      {
      case 1: $agestr = $age.' год'; break;
      case 2: case 3: case 4: $agestr = $age . ' года'; break;
      default: $agestr = $age . ' лет';
      }
      if ($sort == 'patients.birth desc') switch ($age)
      {
      case 1: case 2:
           if (!isset($prevage) || $prevage==0) print ('<tr><td colspan="5" class="separator">Возраст от 1 до 3 лет</td></tr>');
           break;
      case 3: case 4: case 5:
           if (!isset($prevage) || $prevage<3) print ('<tr><td colspan="5" class="separator">Возраст от 3 до 6 лет</td></tr>');
           break;
      default:
           if (!isset($prevage) || $prevage<6) print ('<tr><td colspan="5" class="separator">Возраст от 6 лет и старше</td></tr>');
           break;
      }
  }
  $prevage=$age;
  if ($tr == 'odd') $tr='even'; else $tr='odd';
  print ("<tr class='$tr'><td align=left><a href='patient.php?pat_id=$row->pat_id'>$row->surname $row->name $row->lastname</a></td>");
  print ("<td align='center'>$agestr</td>");
  print ("<td align=center>$birth[2].$birth[1].$birth[0]</td>");
  print ('<td align=center>'.join('.',array_reverse(explode('-',$row->signed))).'</td>');
  print ('<td align="center">');
  switch ($row->age_group)
  {
      case 0: print ('до 1 года'); break;
      case 1: print ('1-3 года'); break;
      case 2: print ('3-6 лет'); break;
      case 3: print ('старше 6 лет'); break;
  }
  print ('</td><td align="center">');
  $row->dispancer?print('Д</td><td></td>'):print('</td><td></td>');
  if ($full)
  {
     print ('<td>'.$row->address.'</td><td>');
     $phones = $db->query ('select * from phones where pat_id = '.$row->pat_id);
     if ($phones && $phones->num_rows)
     {
        while ($pr = $phones->fetch_object())
        {
           if (!strlen(trim($pr->number)) || $pr->number == 'нет') continue;
           if ($pr->owner_name && strlen (trim($pr->owner_name))) print ($pr->owner_name);
           if ($pr->owner && strlen (trim($pr->owner))) print (' ('.$pr->owner.')');
           print (' : '.$pr->number.' ('.$pr->operator.')<br>');
        }
        $phones->free();
     }
     print ('</td></tr>');
  }
}
print ('</table></p>');
}
include ('footer.inc');

?>