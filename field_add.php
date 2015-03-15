<?php
//
// FIELD_ADD.PHP
// Добавление в поле типа "select" нового значения выпадающего списка
// Входные данные: id=xxx, где xxx - id поля
//                 text=
//
require('header.inc');
require('../settings.php');
require('auth.php');
require('connect.inc');
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) die ('Внутренняя ошибка: id поля не указан или неверен!');
if (!isset($_GET['text']) || !strlen($_GET['text'])) die ('Добавляемый текст пуст или не указан!');
$res = $db->query ('select * from osm_fields where id='.$_GET['id']);
if (!$res || !$res->num_rows) die ('Ошибка чтения базы полей формы осмотра! '.$db->error);
$field=$res->fetch_object();
$res->free();
if ($field->type != 'select') die ('Нельзя пополнять поле типа '.$field->type.'!');
$field->value.=$delim.$db->real_escape_string($_GET['text']);
$query = 'update osm_fields set value="'.$field->value.'" where id='.$_GET['id'];
print ($query);
if (!$db->query($query)) die ('Ошибка обновления базы данных! '.$db->error);
print ('<p>Данные успешно обновлены! <a href="javascript:window.close()">Закрыть</a></p>');
require ('footer.inc');
?>