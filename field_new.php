<?php
//
// FIELD_NEW.PHP
// Создание поля формы осмотра
// Входные данные: нет
//
require('header.inc');
require('../settings.php');
require('auth.php');
require('connect.inc');
?>
<script>
function checkFields()
{
if (document.getElementById("type").value=="" || document.getElementById('name').value=="") { window.alert('Не указан тип и/или название элемента!'); return false} else return true;
}
</script>
<?php
if (isset($_POST['type'])) // постим форму
{
  if (! (isset($_POST['type']) && ($_POST['name']) && isset($_POST['suffix']))) die ('Ошибка передачи параметров!');
  $query = 'insert into osm_fields values (NULL, 0, "'.$_POST['type'].'", "'.$db->real_escape_string($_POST['name']).'", "'.$db->real_escape_string($_POST['suffix']).'", ';
  if (isset($_POST['value']) && strlen ($_POST['value']))
  {
      $value=str_replace("\r", '', trim($_POST['value']));
      $query .= '"'.$db->real_escape_string(str_replace("\n", $delim, $value)).'"';
  }
  else $query.='NULL';
  if (isset($_POST['template']) && strlen ($_POST['template'])) $query.=', "'.$_POST['template'].'"'; else $query.=', NULL';
  $query .= ')';
  print ($query);
  if (!$db->query($query)) die ('Ошибка обновления базы данных! '.$db->error);
  $res=$db->query('select LAST_INSERT_ID()');
  if (!$res || !$res->num_rows) die ('Ошибка добавления нового поля! '.$db->error);
  $row=$res->fetch_row();
  $id=$row[0];
  $res->free();
  print ('<p>Данные успешно добавлены! <a href="osmotr2.php?do=insert&id='.$id.'&after='.$_POST['after'].'">Вернуться к редактированию формы осмотра</a></p>');
  require ('footer.inc');
  return;
}
if (!isset($_GET['after']) || !is_numeric($_GET['after'])) die ('Внутренняя ошибка: id поля не указан или неверен!');
// Форма добавления
print <<<END
<form method="post" onsubmit="return checkFields()">
<input type="hidden" name="after" value="{$_GET['after']}">
<table border="0">
<tr>
<td align="right">Тип поля:</td><td><select name="type" id="type">
<option value=""> --- </option>
<option value="text">текстовое поле</option>
<option value="number">короткое поле ввода цифр</option>
<option value="dualnum">двойное поле ввода цифр</option>
<option value="area">поле ввода нескольких строк текста</option>
<option value="check">чек-бокс</option>
<option value="select">выпадающий список</option>
<option value="multi">список с возможностью выбора нескольких пунктов</option>
<option value="table">динамический список (таблица)</option>
<option value="section">заголовок секции (подраздела)</option>
<option value="header">заголовок раздела</option>
<option value="hr">горизонтальная черта</option>
<option value="module">внешний модуль</option>
</select>
</td></tr>
<td align="right">Название:</td><td><input type="text" name="name" id="name" value="" maxlength="250" size="100"/></td></tr>
<td align="right">Суффикс (необяз.):</td><td><input type="text" name="suffix" value="" maxlength="19"/></td></tr>
<td align="right">Список значений:</td><td><textarea name="value" cols="80" rows="20"></textarea>
<br>по одному значению в строке, разделитель списков - '*'</td></tr>
<td align="right">Шаблон печати (необяз.):</td><td><textarea name="template" cols="80" rows="3"></textarea><br>значение поля подставляется вместо символа $</td></tr>
</table><input type="submit" value="Сохранить"></form>
END;
require ('footer.inc');
?>