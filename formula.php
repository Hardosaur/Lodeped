<?php
//
// Расчет формулы для финансовой отчетности
//
require('../settings.php');
require('auth.php');
$WINDOW_TITLE = 'Отчет';
include('header.inc');
require('connect.inc');
date_default_timezone_set ("Europe/Minsk"); // чтобы избежать сообщения о проблемах с получением неправильной даты
$doctor_id = $_SESSION['doctor_id'];
$patients = $db->query ('select * from contracts, patients where contracts.valid>0 and contracts.doctor_id = '.$doctor_id.' and patients.pat_id = contracts.pat_id');
$ages0_1 = array(0,0);
$ages1_3 = array(0,0);
$ages3_6 = array(0,0);
$ages6_ = array(0,0);
if (isset($_POST['day']) && isset($_POST['month']) && isset($_POST['year'])) // передана точка отсчета
{
   $time = (int) mktime (0,0,0, $_POST['month'], $_POST['day'], $_POST['year']);
   print ('Дата: '.$_POST['month'].'.'.$_POST['day'].'.'.$_POST['year']);
}
else
{
  $time = (int) time();
  print ('Дата: сегодня');
}
print ('<p>Рассчет произведен ');
//.date('d.m.Y',$time));
if (isset($_GET['signed'])) print ('по группам учёта (<a href="formula.php">рассчитать по дате рождения</a>)</p>');
else print ('по дате рождения (<a href="formula.php?signed=1">рассчитать по группам учёта</a>)</p>');
while ($row = $patients->fetch_object())
{
  $birth = explode ('-',$row->birth);
//  $signed = explode ('-',$row->signed);
//  $corr = 24*3600*92; // отнимаем три месяца
  $corr = 0;
  if (isset($_GET['signed'])) // возраст определяется группой наблюдения, заданной при заключении контракта
  {
   $age = $row->age_group;
   if ($age<0) $age=0;
   elseif ($age == 2) $age=4;
   elseif ($age == 3) $age=7;
  }
  else $age = (int)(($time-mktime(0,0,0,$birth[1],$birth[2],$birth[0])-$corr)/31536000);// 365*24*3600 - кол-во секунд в году, вычисляем число полных лет
  //
  //print ($row->surname.' '.$row->name{0}.'.'.$row->lastname{0}.'. = '.$age.'<br>');
  //
  if ($age<0) $age=0;
  if ($age<1) $ages0_1[0]++;
  else if ($age<3) $ages1_3[0]++;
  else if ($age<6) $ages3_6[0]++;
  else $ages6_[0]++;
  if ($row->dispancer)
  {
    if ($age<1) $ages0_1[1]++;
    else if ($age<3) $ages1_3[1]++;
    else if ($age<6) $ages3_6[1]++;
    else $ages6_[1]++;
  }
}

$total = $ages0_1[0]+$ages1_3[0]+$ages3_6[0]+$ages6_[0];
$totald = $ages0_1[1]+$ages1_3[1]+$ages3_6[1]+$ages6_[1];
print <<<END
<p><table class="report" width="600">
<tr><th></th><th>Всего пациентов</th><th>До 1 года</th><th>От 1 до 3 лет</th><th>От 3 до 6 лет</th><th>Старше 6 лет</th></tr>
<tr><td>Всего</td><td>$total</td><td>{$ages0_1[0]}</td><td>{$ages1_3[0]}</td><td>{$ages3_6[0]}</td><td>{$ages6_[0]}</td></tr>
<tr><td>Из них Д</td><td>$totald</td><td>{$ages0_1[1]}</td><td>{$ages1_3[1]}</td><td>{$ages3_6[1]}</td><td>{$ages6_[1]}</td></tr>
</table></p>
<p><a class="small" href="doctor.php">Вернуться на страницу доктора</a></p>
END;
/*
print ('<p><table border="0" cellspacing="0" width="500"><tr>');
print ('<td style="border-bottom: solid 1px black">Всего: '.$total.'</td>');
print ('<td rowspan="2">=</td>');
print ('<td style="border-bottom: solid 1px black">0 - 1: '.$ages0_1[0].'</td>');
print ('<td rowspan="2">=</td>');
print ('<td style="border-bottom: solid 1px black">1 - 3: '.$ages1_3[0].'</td>');
print ('<td rowspan="2">=</td>');
print ('<td style="border-bottom: solid 1px black">3 - 6: '.$ages3_6[0].'</td>');
print ('<td rowspan="2">=</td>');
print ('<td style="border-bottom: solid 1px black">>6: '.$ages6_[0].'</td>');
print ('</tr><tr>');
print ('<td>Д: '.$totald.'</td>');
print ('<td>Д: '.$ages0_1[1].'</td>');
print ('<td>Д: '.$ages1_3[1].'</td>');
print ('<td>Д: '.$ages3_6[1].'</td>');
print ('<td>Д: '.$ages6_[1].'</td></tr></table></p>');

print ('<p><a class="small" href="doctor.php">Вернуться на страницу доктора</a></p>');
*/
include ('footer.inc');
?>