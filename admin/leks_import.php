<?php
//
// Импорт/экспорт данных о лекарствах
//
require('../../settings.php');
require('../auth.php');
require ('../access.inc');
check_access_level (0);
include('../header.inc');
print ('<h1>Утилита импорта/экспорта базы данных лекарств</h1>');
if (!isset($_GET['mode']))
{ // вывод запроса на выбор базы: названия диагнозов или данные для уточнения
?>
  <p>Выберите режим:</p>
  <p>1. <a href="leks_import.php?mode=import1">Загрузка данных по названиям лекарств.</a></p>
  <p>2. <a href="leks_import.php?mode=import3">Загрузка данных по формам выпуска лекарств.</a></p>
  <p><form method="get"><input type="hidden" name="mode" value="import2">
  3. Загрузка таблиц описания формы выбора предписания. Номер таблицы: <input name="tab_id" value="" size="3"> <input type="submit" value="Загрузить">
  </form></p>

  <p>4. <a href="leks_import.php?mode=export1">Выгрузка данных по названиям лекарств.</a></p>
  <p>5. <a href="leks_import.php?mode=export3">Выгрузка данных по формам выпуска лекарств.</a></p>
  <form method="get"><input type="hidden" name="mode" value="export2">
  6. Выгрузка таблиц. Номер таблицы: <input name="tab_id" value="" size="3"> <input type="submit" value="Выгрузить"></form></p>
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
      if (!$db->query ('truncate table lek_names')) die ('Невозможно удалить старые данные! Ошибка: '.$db->error);
      // разбор строк и занесение в базу
      $count=0;
      foreach ($data as $line)
      {
          $count++;
          $line=rtrim($line);
          list ($id,$lname,$rname) = explode (';',$line);
          if (!is_numeric($id) || !strlen($lname) || !strlen($rname)) die ("Ошибка в строке №$count: $line");
          $lname=addslashes($lname);
          $rname=addslashes($rname);
          $query = "insert into lek_names values ($id, '$lname', '$rname')";
          if (!$db->query($query)) die ("Ошибка добавления данных: $id, '$lname', '$rname'");
      }
      print ("<p>Данные внесены успешно. Добавлено $count лекарств. <a href='leks_import.php'>Вернуться к первой странице</a></p>");
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
// ---------------------------------------------------------------------------------
if ($_GET['mode']=='import2')
{
  if (!isset($_GET['tab_id']) || !is_numeric($_GET['tab_id'])) die ('Не указан номер таблицы!');
  $tab_id=$_GET['tab_id'];
  if (isset($_POST['data']) && strlen($_POST['data']))
  {
      $data = explode("\n",$_POST['data']); // выделим отдельные строки
      if (!count($data)) die ('Ошибка в переданных данных! Нет строк.');
      $tab_name = array_shift($data);
      $tab_name=rtrim($tab_name);
      // удалим старые данные
      include ('../connect.inc');
      if (!$db->query ('delete from lek_data where tab_id='.$_GET['tab_id'])) die ('Невозможно удалить старые данные! Ошибка: '.$db->error);
      foreach ($data as $key => $val) $data[$key]=rtrim($val);
      $list = implode (';',$data);
      $query = "insert into lek_data values ($tab_id, '$tab_name', '$list')";
      if (!$db->query($query)) die ("Ошибка добавления данных ($tab_id, '$tab_name', '$list')! $db->error");
      print ("<p>Данные внесены успешно. <a href='leks_import.php'>Вернуться к первой странице</a></p>");
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
// ---------------------------------------------------------------------------------------
if ($_GET['mode']=='import3')
{
  if (isset($_POST['data']))
  {
      $data = explode("\n",$_POST['data']); // выделим отдельные строки
      if (!count($data)) die ('Ошибка в переданных данных! Нет строк.');
      // удалим старые данные
      include ('../connect.inc');
      if (!$db->query ('truncate table lek_forms')) die ('Невозможно удалить старые данные! Ошибка: '.$db->error);
      // разбор строк и занесение в базу
      $count=0;
      foreach ($data as $line)
      {
          $count++;
          $line=rtrim($line);
          list ($id,$lname,$rname) = explode (';',$line);
          if (!is_numeric($id) || !strlen($rname)) die ("Ошибка в строке №$count: $line");
          if (isset($lname)) $lname=addslashes($lname); else $lname='';
          $rname=addslashes($rname);
          $query = "insert into lek_forms values ($id, '$lname', '$rname')";
          if (!$db->query($query)) die ("Ошибка добавления данных: $id, '$lname', '$rname'");
      }
      print ("<p>Данные внесены успешно. Добавлено $count форм выпуска лекарств. <a href='leks_import.php'>Вернуться к первой странице</a></p>");
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
// ----------------------------------------------------------------------------------
if ($_GET['mode']=='export1')
{
  include ('../connect.inc');
  $res = $db->query ('select * from lek_names');
  if (!$res || !$res->num_rows) die ('Ошибка чтения базы: '.$db->error);
  print ('<p>Результаты чтения базы названий лекарств:<br><textarea cols="150" rows="50">');
  while ($row=$res->fetch_object())
  {
      print ("{$row->lek_id};{$row->lname};{$row->rname}\n");
  }
  print ('</textarea><br> <a href="leks_import.php">Вернуться к первой странице</a></p>');
}
// ----------------------------------------------------------------------------------
if ($_GET['mode']=='export2')
{
  if (!isset($_GET['tab_id']) || !is_numeric($_GET['tab_id'])) die ('Не указан номер таблицы!');
  include ('../connect.inc');
  $res = $db->query ('select * from lek_data where tab_id='.$_GET['tab_id']);
  if (!$res || $res->num_rows!=1) die ('Ошибка чтения или нарушение формата базы: '.$db->error);
  print ('<p>Результаты чтения базы данных по формам ввода предписаний (лекарств):<br><textarea cols="150" rows="50">');
  $row=$res->fetch_object();
  print ("$row->tab_name\n");
  $list=explode(';',$row->list);
  foreach ($list as $line)
  {
      print ("$line\n");
  }
  print ('</textarea><br> <a href="leks_import.php">Вернуться к первой странице</a></p>');
}
// ------------------------------------------------------------------------------------
if ($_GET['mode']=='export3')
{
  include ('../connect.inc');
  $res = $db->query ('select * from lek_forms');
  if (!$res || !$res->num_rows) die ('Ошибка чтения базы: '.$db->error);
  print ('<p>Результаты чтения базы форм выпуска лекарств:<br><textarea cols="150" rows="50">');
  while ($row=$res->fetch_object())
  {
      print ("{$row->form_id};{$row->lname};{$row->rname}\n");
  }
  print ('</textarea><br> <a href="leks_import.php">Вернуться к первой странице</a></p>');
}

include ('../footer.inc');
?>

