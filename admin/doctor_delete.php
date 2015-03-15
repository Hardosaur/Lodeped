<?php
//
// Удаление учетной записи доктора
//
require('../../settings.php');
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
// Проверка на наличие связей в БД
//
// Проверка таблицы Contracts
$res=$db->query ('select contract from contracts where doctor_id = "'.$_GET['doctor_id'].'" && valid>0');
if (!$res) die ('Query error: '.$db->error);
if ($res->num_rows)
{ // есть записи
  print ('<p>Удаление учетной записи доктора невозможна! Имеется '.$res->num_rows.' активных договоров с пациентами!</p>');
  $res->free();
}
else
{
  $res->free();
  // Добавить обработку других таблиц
  // Если связей нет
  if ($db->query('delete from doctors where doctor_id='.$_GET['doctor_id'])) print ('<p>Запись удалена успешно.</p>');
  else print ('<p>Ошибка удаления! '.$db->error.'</p>');
}
print ('<p><a href="doctors.php">Вернуться к списку докторов</a></p>');
include ('../footer.inc');
?>