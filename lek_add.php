<?php
//
// Пополнение выпадающего списка в шаблоне лекарства
//
if (!isset($_GET['table']) || !is_numeric($_GET['table']) || !isset($_GET['value'])) die ('Недостаточно параметров!');
$value=$_GET['value'];
require('../settings.php');
require('auth.php');
include('connect.inc');
// читаем параметр, который нужно подправить
$res=$db->query ('select list from lek_data where tab_id='.$_GET['table']);
if (!$res || !$res->num_rows) die ('Невозможно найти запись в базе для пополнения!');
$row=$res->fetch_row();
// проверяем, не пытается ли пользователь добавить уже существующее поле
$lines=explode(';',$row[0]);
foreach ($lines as $line)
        if (!strcasecmp($value,$line))
        {
            $res->free();
            print ('<p>Указанное значение уже содержится в базе данных.<br><a href="javascript:close()">Закрыть окно</a></p>');
            exit;
        }
$value=$row[0].';'.$value;
$value=$db->real_escape_string($value);
// сохраняем
$res->free();
if (!$db->query ('update lek_data set list="'.$value.'" where tab_id='.$_GET['table'])) die ('Невозможно внести изменения. Ошибка: '.$db->error);
print ('<p>Данные пополнены успешно.<br><a href="javascript:close()">Закрыть окно</a></p>');
?>