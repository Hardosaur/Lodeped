<?php
//
// Регистрация случая аллергии в досье пациента
//
if (!isset($_GET['lek_id']) || !is_numeric($_GET['lek_id'])) die ('Не передан необходимый параметр!');
require('../settings.php');
require('auth.php');
require('header.inc');
if (!isset($_SESSION['pat_id']) || !isset($_SESSION['doctor_id']) || !isset($_SESSION['date'])) die ('Нет данных в сессии! Запуск скрипта вручную?');
if (!isset($_POST['comment'])) // запросить ввод комментария (необязательного)
{
  print ('<p>Укажите дополнительную информацию о проявлениях аллергии (необязательно):</p></p><form method="post"><textarea name="comment" rows="5" cols="60"></textarea><br><input type="submit" value="Добавить"/></form></p>');
  include ('footer.inc');
  exit;
}
include('connect.inc');
$pat_id=$_SESSION['pat_id'];
$res=$db->query('select all_id from allergies where pat_id='.$pat_id.' and lek_id='.$_GET['lek_id']);
if ($res && $res->num_rows)
{
  $res->free();
  print ('<p>Указанный препарат уже содержится в списке аллергенных для данного пациента.<br><a href="javascript:close()">Закрыть окно</a></p>');
  exit;
}
if (!$db->query("insert into allergies values (NULL, $pat_id, {$_SESSION['doctor_id']}, {$_GET['lek_id']}, \"{$_SESSION['date']}\", \"{$_POST['comment']}\")"))
   die ('<p>Невозможно внести данные в базу! Ошибка: '.$db->error);
print ('<p>Данные внесены.<br><a href="javascript:close()">Закрыть окно</a></p>');
include('footer.inc');
?>