<?php
require('../settings.php');
require('auth.php');
include('header.inc');
include('connect.inc');
// проверим пациента
if (!isset($_GET['osm_id']) || !is_numeric($_GET['osm_id'])) die ('Нет требуемого параметра! (Запуск скрипта вручную?)');
if (!isset($_GET['confirm'])) // запрос подтверждения
{
  print ('<p align=center>Вы действительно хотите удалить протокол осмотра?<br><a href="osm_delete.php?osm_id='.$_GET['osm_id'].'&pat_id='.$_GET['pat_id'].'&confirm=1">Да</a>&nbsp;|&nbsp;<a href="patient.php?pat_id='.$_GET['pat_id'].'">Нет</a>');
}
else
{
  if (!$db->query('delete from osm_info where osm_id = '.$_GET['osm_id'])) die ('Ошибка удаления данных! '.$db->error);
  if (!$db->query('delete from osm_data where osm_id = '.$_GET['osm_id'])) die ('Ошибка удаления данных! '.$db->error);
  print ('<p>Данные об осмотре удалены успешно!');
}
print ('<br><a href="patient.php?pat_id='.$_GET['pat_id'].'">&lt;&lt; Вернуться на страницу пациента</a></p>');
include('footer.inc');
?>