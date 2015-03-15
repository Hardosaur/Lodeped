<?php
//
// FIELD_EDIT.PHP
// Редактирование поля формы осмотра
// Входные данные: id=xxx, где xxx - id поля
//
require('header.inc');
require('../settings.php');
require('auth.php');
require('connect.inc');
if (isset($_POST['type'])) // постим форму
{
  if (! (isset($_POST['id']) && is_numeric($_POST['id']) && isset($_POST['name']) && isset($_POST['suffix']))) die ('Ошибка передачи параметров!');
  $query = 'update osm_fields set';
  if ($_POST['type']!='0') $query.=' type="'.$_POST['type'].'",';
  $query.=' name="'.$db->real_escape_string($_POST['name']).'", suffix="'.$db->real_escape_string($_POST['suffix']).'", value=';
  if (isset($_POST['value']) && strlen($_POST['value']))
  {
      $value=str_replace("\r", '', trim($_POST['value']));
      $query .= '"'.$db->real_escape_string(str_replace("\n", $delim, $value)).'"';
  }
  else $query.='NULL';
  if (isset($_POST['template']) && strlen($_POST['template'])) $query.=', template="'.$_POST['template'].'"'; else $query.=', template=NULL';
  $query.=' where id='.$_POST['id'];
  print ($query);
  if (!$db->query($query)) die ('Ошибка обновления базы данных! '.$db->error);
  print ('<p>Данные успешно обновлены! <a href="osmotr2.php">Вернуться к редактированию формы осмотра</a></p>');
  require ('footer.inc');
  return;
}
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) die ('Внутренняя ошибка: id поля не указан или неверен!');
$res = $db->query ('select * from osm_fields where id='.$_GET['id']);
if (!$res || !$res->num_rows) die ('Ошибка чтения базы полей формы осмотра! '.$db->error);
$field=$res->fetch_object();
$res->free();

// Форма редактирования
print <<<END
<form method="post">
<table border="0">
<tr>
<td align="right">ID поля:</td><td><input type="text" readonly name="id" value="$field->id"/></td></tr>

<td align="right">Тип поля:</td><td><select name="type">
END;
if ($field->type=='text') print ('<option value="text" selected>текстовое поле</option>'); else print ('<option value="text">текстовое поле</option>');
if ($field->type=='number') print ('<option value="number" selected>короткое поле ввода цифр</option>'); else print ('<option value="number">короткое поле ввода цифр</option>');
if ($field->type=='dualnum') print ('<option value="dualnum" selected>двойное поле ввода цифр</option>'); else print ('<option value="dualnum">двойное поле ввода цифр</option>');
if ($field->type=='area') print ('<option value="area" selected>поле ввода нескольких строк текста</option>'); else print ('<option value="area">поле ввода нескольких строк текста</option>');
if ($field->type=='check') print ('<option value="check" selected>чек-бокс</option>'); else print ('<option value="check">чек-бокс</option>');
if ($field->type=='select') print ('<option value="select" selected>выпадающий список</option>'); else print ('<option value="select">выпадающий список</option>');
if ($field->type=='multi') print ('<option value="multi" selected>список с возможностью выбора нескольких пунктов</option>'); else print ('<option value="multi">список с возможностью выбора нескольких пунктов</option>');
if ($field->type=='table') print ('<option value="table" selected>динамический список (таблица)</option>'); else print ('<option value="table">динамический список (таблица)</option>');
if ($field->type=='section') print ('<option value="section" selected>секция</option>'); else print ('<option value="section">секция</option>');
if ($field->type=='header') print ('<option value="header" selected>раздел</option>'); else print ('<option value="header">раздел</option>');
if ($field->type=='hr') print ('<option value="hr" selected>горизонтальная черта</option>'); else print ('<option value="hr">горизонтальная черта</option>');
if ($field->type=='module') print ('<option value="module" selected>внешний модуль</option>'); else print ('<option value="module">внешний модуль</option>');
print <<<END1
</select></td></tr>
<td align="right">Название:</td><td><input type="text" name="name" value="$field->name" maxlength="250" size="100"/></td></tr>
<td align="right">Суффикс:</td><td><input type="text" name="suffix" value="$field->suffix" maxlength="19"/></td></tr>
END1;
if ($field->type == 'select' || $field->type == 'table' || $field->type == 'multi' || $field->type == 'module')
{
  $values = explode ($delim, $field->value);
  print ('<td align="right">Список значений:</td><td><textarea name="value" cols="80" rows="20">');
  foreach ($values as $value) print $value."\n";
  print ('</textarea></td></tr>');
}
print ('<td align="right">Шаблон печати:</td><td><textarea name="template" cols="80" rows="3">'.$field->template.'</textarea><br>значение поля подставляется вместо символа $</td></tr>');
print ('</table><input type="submit" value="Сохранить"></form>');
require ('footer.inc');
?>