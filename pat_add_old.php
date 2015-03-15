<?php
//
// Добавление данных о новом пациенте
//
require('../settings.php');
include('header.inc');
require('auth.php'); // доступно только доктору
require('connect.inc');
//
if (isset($_POST['name'])) // Переданы данные для обновления
{
  if (!(strlen($_POST['name']) && strlen($_POST['surname']) && strlen($_POST['lastname']) && strlen($_POST['month']) && strlen($_POST['day'])
        && strlen($_POST['year']) && strlen($_POST['sex']) && strlen($_POST['address'])))
     die ('Not enough data!');
  // Проверим, есть ли такой человек в БД
  $chk = $db->query ('select * from patients where surname="'.$_POST['surname'].'" and name="'.$_POST['name'].'" and lastname="'.$_POST['lastname'].'"');
  if ($chk && $chk->num_rows)
  {
      print ('<p>В базе уже есть такой пациент! Добавление не имеет смысла.</p><p><a href="javascript:history.go(-1)">Вернуться назад</a></p>');
      include ('footer.inc');
      exit;
  }
  // Преобразуем дату
  if ((!(is_numeric($_POST['year']) && $_POST['year']>1900 && is_numeric($_POST['month']) && $_POST['month']>0 && $_POST['month']<13
        && is_numeric($_POST['day']) && $_POST['day']>0 && $_POST['day']<32))
        || strtotime($_POST['day'].'.'.$_POST['month'].'.'.$_POST['year']) === -1)
  {
     print ('<p>Извините, дата рождения введена неверно!</p><p><a href="javascript:history.go(-1)">Вернуться назад</a></p>');
     include ('footer.inc');
     exit;
  }
  $birth = sprintf("%4d-%02d-%02d",$_POST['year'],$_POST['month'],$_POST['day']);
  // Добавляем запись о пациенте
  $q = 'insert into patients values (NULL, '. $_POST['dispancer'] . ', "' . $_POST['surname'] . '", "' . $_POST['name']. '", "' . $_POST['lastname'] .
       '", "' . $birth . '", "' . $_POST['address'] . '", '. $_POST['floor'] . ', ' . $_POST['entrance'] . ', "' . $_POST['sex'] . '", 0, "'. $_POST['comment'] . '")';
//  print ($q); // Для отладки
  if (!$db->query($q)) die ('<p><font color="red">Добавление данных не произошло! Ошибка: '.$db->error.'</font></p>');
  //
  // Добавляем телефонные номера, узнав предварительно новый pat_id
  //
  $p = $db->query ('select pat_id from patients where surname="'.$_POST['surname'].'" and name="'.$_POST['name'].'"');
  if (!($p && $pr=$p->fetch_object())) die ('<p><font color="red">Данные внесены, но не найдены!</font></p>');
  $pat_id = $pr->pat_id;
  $p->free();
  if (isset($_POST['phone1']))
  {
      $c=1;
      $q = 'insert into phones values ';
      while (isset($_POST['phone'.$c]) && isset($_POST['operator'.$c]) && strlen($_POST['phone'.$c])>0)
      {
          $q=$q . '(' . $pat_id . ', "' . $_POST['phone'.$c] . '", "' . $_POST['operator'.$c] . '"),';
          $c++;
      }
      $q = rtrim ($q,','); // удаляем последнюю запятую
//      echo $q; // Для отладки
      if (!$db->query($q))
      {
          die ('<p>Ошибка добавления номеров телефонов! ('.$db->error.')</p><p><a href="doctor.php">Вернуться на страницу доктора</a></p>');
      }
  }
  // Готово!
  print ('<p>Данные внесены успешно.</p><p>');
  if (isset($_POST['contract']) && strlen($_POST['contract'])) print ('<a href="contract.php?pat_id='.$pat_id.'">Перейти к регистрации договора (контракта)</a></p>');
  else print ('<a href="doctor.php">Вернуться на страницу доктора</a></p>');
  include ('footer.inc');
  exit;
}
//
// Данных нет, требуется вывести форму
//
?>
<h1>Добавить данные о новом пациенте</h1>
<p><form method=post>
<table border=0>
<tr><td>Фамилия:<td><input type='text' name='surname' size='30' maxlength='30' value=''>
<tr><td>Имя:<td><input type='text' name='name' size='30' maxlength='30' value=''>
<tr><td>Отчество:<td><input type='text' name='lastname' size='30' maxlength='30' value=''>
<tr><td>Диспансерный больной:<td><select name='dispancer' size='2'><option value="0" selected>Нет</option><option value="1">Да</option></select>
<tr><td>Пол:<td><select name='sex' size='1'><option value='male'>мужской</option><option value='female'>женский</option></select>
<tr><td>Дата рождения (ДД.ММ.ГГГГ):<td><input type='text' name='day' size='1' maxlength='2' value=''>.<input type='text' name='month' size='1' maxlength='2' value=''>.<input type='text' name='year' size='2' maxlength='4' value=''>
<tr><td>Адрес места жительства:<td><input type='text' name='address' size='30' maxlength='99' value=''>
<tr><td>Подъезд:<td><input type='text' name='entrance' size='2' maxlength='5' value=''>
<tr><td>Этаж:<td><input type='text' name='floor' size='2' maxlength='5' value=''>
<tr><td valign='top'>Номера телефонов:<td><input type='text' name='phone1' size='10' maxlength='30' value=''>&nbsp;оператор&nbsp;<input type='text' name='operator1' size='10' maxlength='12' value=''><br>
                             <input type='text' name='phone2' size='10' maxlength='30' value=''>&nbsp;оператор&nbsp;<input type='text' name='operator2' size='10' maxlength='12' value=''><br>
                             <input type='text' name='phone3' size='10' maxlength='30' value=''>&nbsp;оператор&nbsp;<input type='text' name='operator3' size='10' maxlength='12' value=''><br>
<tr><td valign='top'>Доп. информация:<td><textarea name='comment' cols='50' rows='4'></textarea>
</table></p>
<p><input type="checkbox" name="contract" value="1">&nbsp;После добавления данных перейти к оформлению договора</p>
<p><input type="submit" value="Внести данные">&nbsp;<input type="button" value="Отмена" onClick="javascript:history.go(-1)"></p></form>
<?php
include ('footer.inc');
?>