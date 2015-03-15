<?php
//
// Пополнение выпадающего списка в шаблоне осмотра
//
if (!isset($_GET['osm_type']) || !isset($_GET['id']) || !isset($_GET['add'])) die ('Недостаточно параметров!');
$osm_type=$_GET['osm_type'];
$id=$_GET['id'];
$add=trim($_GET['add']);
if (!is_numeric($osm_type) || !is_numeric($id) || !strlen($add)) die ('Неверные параметры!');
require('../settings.php');
require('auth.php');
include('connect.inc');
// читаем параметр, который нужно подправить
$res=$db->query ('select type, value from osm_template where osm_type='.$osm_type.' and id='.$id);
if (!$res || !$res->num_rows) die ('Невозможно найти запись в базе для пополнения!');
$row=$res->fetch_row();
if ($row[0]!='select') die ('Указанный параметр не имеет тип select! Изменение невозможно.');
$value = $row[1];
// проверяем, не пытается ли пользователь добавить уже существующее поле
$vars=explode(';',$value);
foreach ($vars as $var) if (!strcmp($add,$var)) die ('Указанное значение уже имеется в списке! Пополнение не имеет смысла.');
$value.=';'.$add;
$value=$db->real_escape_string($value);
// сохраняем
if (!$db->query ('update osm_template set value="'.$value.'" where id='.$id)) die ('Невозможно внести изменения. Ошибка: '.$db->error);
print ('<p>Данные пополнены успешно.<br><a href="javascript:close()">Закрыть окно</a></p>');
?>