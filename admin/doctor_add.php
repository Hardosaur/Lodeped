<?php
//
// Добавление учетной записи доктора
//
require('../../settings.php');
$WINDOW_TITLE = 'Новый доктор';
include('../header.inc');
require('../auth.php');
require ('../access.inc');
check_access_level (0);
$db = new mysqli($dbhost,$dbuser,$dbpass,$dbname);
if (mysqli_connect_errno()) die ('Connect error: '.mysqli_connect_error());
$db->query ('set names cp1251');
//
if (isset($_POST['name'])) // Переданы данные для добавления
{
  if (!(isset($_POST['surname']) && isset($_POST['lastname']) && isset($_POST['category']) && isset($_POST['dep_id']) && isset($_POST['doctor_pass'])&& isset($_POST['access_level'])))
     die ('Not enough data!');
  if (!(isset($_POST['doctor_id']))) $doctor_id='NULL';
  else
  {
      if (!is_numeric($_POST['doctor_id'])) $doctor_id='NULL';
      else $doctor_id=$_POST['doctor_id'];
  }
  $q = 'insert into doctors values ('.$doctor_id.', "'.$_POST['doctor_pass'].'", '.$_POST['dep_id'].', "'.$_POST['surname'].'", "'.$_POST['name'].'", "'.$_POST['lastname'].'", '.
       $_POST['category'].', "'.$_POST['speciality'].'", '.$_POST['access_level'].', "'.$_POST['color'].'")';
//  print ($q);
  if (!$db->query($q))
                  print ('<p><font color="red">Добавление данных не произошло!</font></p>');
  else print ('<p>Данные добавлены успешно.</p>');
  print ('<p><a href="doctors.php">Вернуться к списку докторов</a></p>');
  include ('../footer.inc');
  exit;
}
//
// Данных нет, требуется вывести форму
?>
<h1>Добавить новую учетную запись доктора</h1>
<p><form method=post>
<table class="light" border=0><col align=right><col align=left>
<tr><td class="left">Номер (можно оставить пустым):
<td><input type='text' name='doctor_id' size='30' maxlength='30' value='NULL'>
<tr><td class="left">Фамилия:
<td><input type='text' name='surname' size='30' maxlength='30' value=''>
<tr><td class="left">Имя:
<td><input type='text' name='name' size='30' maxlength='30' value=''>
<tr><td class="left">Отчество:
<td><input type='text' name='lastname' size='30' maxlength='30' value=''>
<tr><td class="left">Пароль:
<td><input type='text' name='doctor_pass' size='30' maxlength='30' value=''>
<tr><td class="left">Уровень доступа:
<td><input type='text' name='access_level' size='3' maxlength='1' value='3'></tr>
<tr><td colspan="2">0 - администратор, 1 - супер-пользователь, 2 - ревизор, 3 - пользователь</td></tr>
<tr><td class="left">Категория:
<td><input type='text' name='category' size='3' maxlength='3' value=''> 0 - высшая, 3 - без категории
<tr><td class="left">Учёная степень:
<td><input type='text' name='speciality' size='30' maxlength='30' value=''>
<?php
$res=$db->query('select * from departments');
if (!$res) die ('Query error: '.$db->error);
print ('<tr><td class="left">Отделение:');
print ('<td><select name="dep_id" size="1">');
while ($row = $res->fetch_object())
  print ("<option value='$row->dep_id'>$row->title</option>\n");
$res->free();
print ('</select>');
print ('<tr><td class="left">Цвет (в списке пациентов):</td><td><select name="color" size="1">');
$colorlist = array (array ('000000','черный'), array ('0090D0','голубой'),array ('0000B0','синий'),array('009000','зеленый'),array('A00000','красный'),array('A0A000','желтый'),array('C07000','оранжевый'),array('A000CC','фиолетовый'));
foreach ($colorlist as $color) print ('<option value="'.$color[0].'" style="color: #'.$color[0].'">'.$color[1].'</option>');
print ('</select></table>');
print ('<input class="button" type="submit" value="Добавить"></form>&nbsp;<input type="button" value="Отмена" onClick="javascript:history.go(-1)">');
include ('../footer.inc');
?>