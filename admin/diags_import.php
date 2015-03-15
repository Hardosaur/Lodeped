<?php
//
// Импорт/экспорт данных о диагнозах
//
require('../../settings.php');
require('../auth.php');
require ('../access.inc');
check_access_level (0);
include('../header.inc');
print ('<h1>Утилита импорта/экспорта базы данных диагнозов</h1>');
if (!isset($_GET['mode']))
{ // вывод запроса на выбор базы: названия диагнозов или данные для уточнения
?>
  <p>Выберите режим:
  <ul><li><a href="diags_import.php?mode=import1">Загрузка данных по названиям диагнозов.</a></li>
  <li><a href="diags_import.php?mode=import2">Загрузка данных по формированию полного названия диагноза.</a></li>
  <li><a href="diags_import.php?mode=export1">Выгрузка данных по названиям диагнозов.</a></li>
  <li><a href="diags_import.php?mode=export2">Выгрузка данных по формированию полного названия диагноза.</a></li>
  </ul></p>
<?php
    include('../footer.inc');
    exit;
}
if ($_GET['mode']=='import1')
{
  if (isset($_POST['data']))
  {
      $data = explode("\n",$_POST['data']); // выделим отдельные строки
      if (!count($data)) die ('Ошибка в переданных данных! Нет строк.');
      // удалим старые данные
      include ('../connect.inc');
      if (!$db->query ('truncate table diag_names')) die ('Невозможно удалить старые данные! Ошибка: '.$db->error);
      // разбор строк и занесение в базу
      $count=0;
      foreach ($data as $line)
      {
          $count++;
          list ($num,$text) = explode (';',$line);
          rtrim($text);
          if (!is_numeric($num) || !strlen($text)) die ("Ошибка в строке №$count: $line");
          $query = "insert into diag_names values ($num, '$text')";
          if (!$db->query($query)) die ("Ошибка добавления данных: $num, '$text'");
      }
      print ("<p>Данные внесены успешно. Добавлено $count диагнозов. <a href='diags_import.php'>Вернуться к первой странице</a></p>");
  }
  else // данные не переданы, нужно вывести форму ввода
  {
?>
   <form method='post'>
   <textarea name='data' cols='150' rows='50'></textarea><br>
   <input type='submit' value='Внести данные'/>
   </form>
<?php
   }
}
if ($_GET['mode']=='import2')
{
  if (isset($_POST['data']))
  {
      $data = explode("\n",$_POST['data']); // выделим отдельные строки
      if (!count($data)) die ('Ошибка в переданных данных! Нет строк.');
      // удалим старые данные
      include ('../connect.inc');
      if (!$db->query ('truncate table diag_data')) die ('Невозможно удалить старые данные! Ошибка: '.$db->error);
      // разбор строк и занесение в базу
      $count=0;
      foreach ($data as $line)
      {
          $count++;
//          $line=$line."\n";
          list ($num,$text) = sscanf ($line,"%d;%[^\n]\n");
          if (ord($text{strlen($text)-1})==13) $text=substr($text,0,strlen($text)-1);
          if (!is_numeric($num) || !strlen($text)) die ("Ошибка в строке №$count: $line");
          $query = "insert into diag_data values ($num, '$text')";
          if (!$db->query($query)) die ("Ошибка добавления данных: $num, '$text'");
      }
      print ("<p>Данные внесены успешно. Добавлено $count диагнозов. <a href='diags_import.php'>Вернуться к первой странице</a></p>");
  }
  else // данные не переданы, нужно вывести форму ввода
  {
?>
   <form method='post'>
   <textarea name='data' cols='150' rows='50'></textarea><br>
   <input type='submit' value='Внести данные'/>
   </form>
<?php
   }
}
if ($_GET['mode']=='export1')
{
  include ('../connect.inc');
  $res = $db->query ('select * from diag_names');
  if (!$res || !$res->num_rows) die ('Ошибка чтения базы: '.$db->error);
  print ('<p>Результаты чтения базы названий диагнозов:<br><textarea cols="150" rows="50">');
  while ($row=$res->fetch_object())
  {
      print ("{$row->diag_id};{$row->diag_name}\n");
  }
  print ('</textarea><br> <a href="diags_import.php">Вернуться к первой странице</a></p>');
}
if ($_GET['mode']=='export2')
{
  include ('../connect.inc');
  $res = $db->query ('select * from diag_data');
  if (!$res || !$res->num_rows) die ('Ошибка чтения базы: '.$db->error);
  print ('<p>Результаты чтения базы данных по уточнениям для диагнозов:<br><textarea cols="150" rows="50">');
  while ($row=$res->fetch_object())
  {
      print ("{$row->diag_id};{$row->data}\n");
  }
  print ('</textarea><br> <a href="diags_import.php">Вернуться к первой странице</a></p>');
}
include ('../footer.inc');
?>

