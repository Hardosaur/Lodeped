<?php
//
// PRINTER_SETTINGS.PHP
// Настройка отступов при печати и др. параметров
//
require('../settings.php');
require('auth.php');
require('connect.inc');
if (isset($_POST['printer_id']))
{
  $printer = $_POST['printer_id'];
  if (!is_numeric($printer)) die ('Ошибка передачи данных [3]');
  $cnt=1;
  while (isset($_POST['p'.$cnt.'x']))
  {
      $padding_x=$_POST['p'.$cnt.'x'];
      if (!is_numeric($padding_x)) $padding_x=0;
      $padding_y=$_POST['p'.$cnt.'y'];
      if (!is_numeric($padding_y)) $padding_y=0;
      $query="insert into printpaddings values ($printer,$cnt,$padding_x,$padding_y) ON DUPLICATE KEY UPDATE padding_x=$padding_x, padding_y=$padding_y";
      if (!$db->query ($query)) print ('Не удалось добавить/обновить атрибут '.$cnt.' ('.$db->error.')!<br>');
      $cnt++;
  }
}
if (isset($_POST['model']))
{
  $model=addslashes($_POST['model']);
  if (!strlen($model) || strlen($model)>200) die ('Ошибка передачи параметров! [2]');
  if (!$db->query('insert into printer values (NULL, "'.$model.'")')) die ('Ошибка сохранения принтера: '.$db->error);
  $res = $db->query ('select LAST_INSERT_ID()');
  if ($res && $row=$res->fetch_row()) {$printer=$row[0]; $res->free();}
  else die ('Ошибка сохранения принтера: '.$db->error);
  $_SESSION['printer']=$printer;
}
if (isset($_GET['new']) || !isset($_SESSION['printer']))
{
  print ('<h2>Создание нового набора настроек</h2>');
  print ('<form method="post" action="printer_settings.php">Название набора настроек (принтера):&nbsp;<input type="text" name="model" value="" size="30" maxlength="200"/>&nbsp;<input type="submit" value="Добавить"></form>');
}
else // принтер выбран
{
  if (!isset($printer)) $printer=$_SESSION['printer'];
  if (!is_numeric($printer)) die ('Ошибка передачи параметров! [1]');
  $res = $db->query ('select model from printer where id='.$printer);
  if ($res && $row=$res->fetch_row()) {$model=$row[0]; $res->free();}
  else die ('Ошибка чтения данных: '.$db->error);
  print ('<h1>Настройки принтера "'.$model.'"</h1>');
  print ('<h2>Отступы для бланка рецепта</h2>');
  $paddings=array();
  $res=$db->query('select * from printpaddings where printer_id='.$printer);
  if ($res && $res->num_rows)
  {
      while ($row = $res->fetch_object())
      {
          $paddings[$row->id][0]=$row->padding_x;
          $paddings[$row->id][1]=$row->padding_y;
      }
      $res->free();
  }
print <<<END
<form action="printer_settings.php" method="post">
<input type="hidden" name="printer_id" value="$printer"/>
<table>
<tr>
<td>Дата:
<td><input type="text" name="p1x" value="{$paddings[1][0]}" size="2"/>
<td><input type="text" name="p1y" value="{$paddings[1][1]}" size="2"/>
<tr>
<td>ФИО пациента:
<td><input type="text" name="p2x" value="{$paddings[2][0]}" size="2"/>
<td><input type="text" name="p2y" value="{$paddings[2][1]}" size="2"/>
<tr>
<td>Возраст:
<td><input type="text" name="p3x" value="{$paddings[3][0]}" size="2"/>
<td><input type="text" name="p3y" value="{$paddings[3][1]}" size="2"/>
<tr>
<td>ФИО врача:
<td><input type="text" name="p4x" value="{$paddings[4][0]}" size="2"/>
<td><input type="text" name="p4y" value="{$paddings[4][1]}" size="2"/>
<tr>
<td>Назначение 1, верхний левый угол:
<td><input type="text" name="p5x" value="{$paddings[5][0]}" size="2"/>
<td><input type="text" name="p5y" value="{$paddings[5][1]}" size="2"/>
<tr>
<td>Назначение 1, нижний правый угол:
<td><input type="text" name="p6x" value="{$paddings[6][0]}" size="2"/>
<td><input type="text" name="p6y" value="{$paddings[6][1]}" size="2"/>
<tr>
<td>Назначение 2, верхний левый угол:
<td><input type="text" name="p7x" value="{$paddings[7][0]}" size="2"/>
<td><input type="text" name="p7y" value="{$paddings[7][1]}" size="2"/>
<tr>
<td>Назначение 2, нижний правый угол:
<td><input type="text" name="p8x" value="{$paddings[8][0]}" size="2"/>
<td><input type="text" name="p8y" value="{$paddings[8][1]}" size="2"/>
</table>
<input type="submit" value="Сохранить"/>
</form>
END;

}