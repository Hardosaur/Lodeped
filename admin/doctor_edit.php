<?php
//
// Изменение данных о докторах, включая пароли
//
require('../../settings.php');
$WINDOW_TITLE = 'Данные доктора';
include('../header.inc');
if (!isset($_GET['doctor_id']) || !is_numeric($_GET['doctor_id'])) // Нет необходимого параметра
{
  print ('<p>Не задан необходимый параметр! (Скрипт запущен вручную?)</p>');
  print ('<a href="doctors.php">Вернуться к списку докторов</a>');
  include ('../footer.inc');
  exit;
}
require('../auth.php');
require ('../access.inc');
check_access_level (0);
$db = new mysqli($dbhost,$dbuser,$dbpass,$dbname);
if (mysqli_connect_errno()) die ('Connect error: '.mysqli_connect_error());
$db->query ('set names cp1251');
//
if (isset($_POST['name'])) // Переданы данные для обновления
{
  if (!(isset($_POST['surname']) && isset($_POST['lastname']) && isset($_POST['category']) && isset($_POST['dep_id']) && isset($_POST['doctor_pass'])&& isset($_POST['access_level'])))
     die ('Not enough data!');
  $q = 'update doctors set doctor_pass="'.$_POST['doctor_pass'].'", surname="'.$_POST['surname'].'", name="'.$_POST['name'].
                  '", lastname="'.$_POST['lastname'].'", category='.$_POST['category'].', speciality="'.$_POST['speciality'].'", dep_id='.$_POST['dep_id'].', access_level='.$_POST['access_level'].
                  ', color = "'.$_POST['color'].'" where doctor_id='.$_GET['doctor_id'];
//  print ($q);
  if (!$db->query($q))
                  print ('<p><font color="red">Обновление данных не произошло!</font></p>');
  print ('<p><a href="doctors.php">Вернуться к списку докторов</a></p>');
  include ('../footer.inc');
  exit;
}
//
// Данных нет, требуется вывести форму
$res=$db->query ('select * from doctors where doctor_id = "'.$_GET['doctor_id'].'"');
if (!$res) die ('Query error: '.$db->error);
if (!($row = $res->fetch_object())) die ('No doctors with specified ID!');
print <<<END
<h1>Изменить данные доктора</h1>
<p><form method="post">
<table class="left"><col align=right><col align=left>
<tr><td>Фамилия:<td><input type='text' name='surname' size='30' maxlength='30' value='$row->surname'>
<tr><td>Имя:<td><input type='text' name='name' size='30' maxlength='30' value='$row->name'>
<tr><td>Отчество:<td><input type='text' name='lastname' size='30' maxlength='30' value='$row->lastname'>
<tr><td>Пароль:<td><input type='text' name='doctor_pass' size='30' maxlength='30' value='$row->doctor_pass'>
<tr><td>Уровень доступа:<td><input type='text' name='access_level' size='3' maxlength='1' value='$row->access_level'></tr>
<tr><td colspan="2">0 - администратор, 1 - супер-пользователь, 2 - ревизор, 3 - пользователь</td></tr>
<tr><td>Категория:<td><input type='text' name='category' size='3' maxlength='3' value='$row->category'>
<tr><td>Учёная степень:<td><input type='text' name='speciality' size='30' maxlength='30' value='$row->speciality'>
END;
$dep = $row->dep_id; // сохраним для выпадающего списка
$dcolor = $row->color;
$res->free();
$res=$db->query('select * from departments');
if (!$res) die ('Query error: '.$db->error);
print ('<tr><td>Отделение:');
print ('<td><select name="dep_id" size="1">');
while ($row = $res->fetch_object())
  if ($row->dep_id == $dep) print ("<option selected value='$row->dep_id'>$row->title</option>\n");
  else print ("<option value='$row->dep_id'>$row->title</option>\n");
$res->free();
print ('</select>');
print ('<tr><td>Цвет (в списке пациентов):</td><td><select name="color" size="1">');
$colorlist = array (array ('000000','черный'), array ('0090D0','голубой'),array ('0000B0','синий'),array('009000','зеленый'),array('A00000','красный'),array('A0A000','желтый'),array('C07000','оранжевый'),array('A000CC','фиолетовый'));
foreach ($colorlist as $color)
{
  if ($dcolor == $color[0]) print ('<option selected ');
  else print ('<option ');
  print ('value="'.$color[0].'" style="color: #'.$color[0].'">'.$color[1].'</option>');
}
print ('</select></table>');
print ('<input class="button" type="submit" value="Изменить"></form>&nbsp;<input type="button" value="Отмена" onClick="javascript:history.go(-1)">');
include ('../footer.inc');
?>