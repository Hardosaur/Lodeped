<?php
//
// Импорт/экспорт шаблона формы осмотра
//
require('../../settings.php');
require('../auth.php');
require ('../access.inc');
check_access_level (0);
include('../header.inc');
print ('<h1>Утилита импорта/экспорта шаблона формы осмотра</h1>');
if (!isset($_GET['mode']))
{
?>
  <p>Выберите режим:
  <p>1. <a href="osm_import.php?mode=import1">Загрузка всех полей формы осмотра.</a></p>
  <p>2. <a href="osm_import.php?mode=export1">Выгрузка всех полей формы осмотра.</a></p>
  <!--
  <p><form method="get"><input type="hidden" name="mode" value="export1">
  2. Выгрузка. Номер типа осмотра: <input name="osm_type" value="" size="3"> <input type="submit" value="Выгрузить">
  </form></p>
  <p><form method="get"><input type="hidden" name="mode" value="import2">
  -->
 <?php
    include('../footer.inc');
    exit;
}
if ($_GET['mode']=='import1')
{
  if (isset($_POST['data']))
  {
/*
      if (!isset($_POST['osm_type']) || !is_numeric($_POST['osm_type']) || !isset($_POST['description']) || !strlen($_POST['description'])) die ('Недостаточно параметров!');
      $osm_type = $_POST['osm_type'];
*/
      // выделим отдельные строки в данных
      $data = explode("\n",$_POST['data']);
      if (!count($data)) die ('Ошибка в переданных данных! Нет строк.');
      // удалим старые данные
      include ('../connect.inc');
/*
      if (!$db->query ('delete from osm_template where osm_type='.$osm_type)) die ('Невозможно удалить старые данные! Ошибка: '.$db->error);
      // Обновим название осмотра
      $res=$db->query('select * from osm_types where osm_type = '.$osm_type);
      if ($res && $res->num_rows)
      {
          if (!$db->query('update osm_types set description="'.$_POST['description'].'" where osm_type='.$osm_type)) die ('Невозможно обновить название осмотра! Ошибка: '.$db->error);
          $res->free();
      }
      else // добавляем новый тип осмотра
      {
          if (!$db->query('insert into osm_types values ('.$osm_type.', "'.$_POST['description'].'")')) die ('Невозможно добавить название осмотра! Ошибка: '.$db->error);
      }
*/
      if (!$db->query ('truncate table osm_fields')) die ('Ошибка очистки таблицы полей осмотра! '.$db->error);
      // разбор строк и занесение в базу
      $count=0; // cчетчик строк
      $count2=0; // счетчик полей
      $select=$table=0; // флаги обработки списка и таблицы
      $sel_value='';
      $sel_line=0;
      $f=array();
      $query = 'insert into osm_fields values ';
      foreach ($data as $line)
      {
          $count++;
          if ($line{0}==';') continue; // пропустим комментарии
          $line=trim($line); // обрезаем последний \r
          if ($select) // обрабатываем список
          {
              if ($line{0}=='*') // найден конец списка
              {
                  $query.='"'.$sel_value.'")';
                  $select=0;
                  $sel_value='';
                  continue; // foreach
              }
              else
              {
                  if (strlen($sel_value)) $sel_value.='|';
                  $sel_value.=$line;
                  continue; // foreach
              }
          }
          elseif ($table) // обрабатываем список
          {
              if ($line{0}=='*' && (strlen($line)>1 && $line{1}=='*') ) // найден конец списка
              {
                  $query.='"'.$sel_value.'")';
                  $table=0;
                  $sel_value='';
                  continue; // foreach
              }
              else
              {
                  if (strlen($sel_value)) $sel_value.='|';
                  $sel_value.=$line;
                  continue; // foreach
              }
          }
          if (count($f)) $query.=', '; // не первый элемент
          unset($f);
          $f = explode (';', $line);
//          print_r($f); print ('<br>');
          $query.='(NULL, '.++$count2.', ';
          // type
          if (isset($f[0]) && strlen ($f[0])) $query.='"'.$f[0].'",'; else die ("Ошибка формата 1 (не указан тип поля)! Строка $count: $line.");
          if ($f[0] == 'select' || $f[0] == 'multi') { $select=1; $sel_value=''; $sel_line=$count;}
          if ($f[0] == 'table') { $table=1; $sel_value='';  $sel_line=$count;}
          // name
          if (isset($f[1]) && strlen ($f[1])) $query.='"'.$f[1].'", '; else die ("Ошибка формата 2 (не указано название поля)! Строка $count: $line.");
          // suffix, уже необязательный
          if (isset($f[2]) && strlen($f[2])) $query.='"'.$f[2].'", '; else $query.='NULL, ';
          if (!$select && !$table) $query.=' NULL)';
      }
      if ($select || $table) die ('Ошибка обработка списка! Не закрытый список в строке '.$sel_line);
      print ($query);
      if (!$db->query($query)) die ('<p style="color:red">Ошибка добавления данных: '.$db->error.'</p>');
      print ("<p>Данные внесены успешно. Обработано $count строк. <a href='osm_import.php'>Вернуться к предыдущей странице</a></p>");
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
  if (isset($_POST['data']) && strlen($_POST['data']))
  {
      if (!isset($_GET['osm_type']) || !is_numeric($_GET['osm_type'])) die ('Не передан номер типа осмотра!');
      $osm_type = $_GET['osm_type'];
      include('../connect.inc');
      $data = $db->real_escape_string($_POST['data']);
      $res=$db->query('select * from osm_print where osm_type = '.$osm_type);
      // проверим, есть ли такой шаблон в базе
      if ($res && $res->num_rows)
      {
          $res->free();
          if (!$db->query('update osm_print set template="'.$data.'" where osm_type='.$osm_type)) die ('Невозможно обновить шаблон! Ошибка: '.$db->error);
      }
      else // добавляем новый тип осмотра
      {
          $query='insert into osm_print values ('.$osm_type.', "'.$data.'")';
          if (!$db->query($query)) die ('Невозможно добавить шаблон! Ошибка: '.$db->error);
      }
      print ('<p>Данные внесены успешно.</p> <a href="osm_import.php">Вернуться к предыдущей странице</a>');
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
  if (!isset($_GET['osm_type']))
  {
?>
   <form method='get'>
   Номер (тип) осмотра: <input type='text' name='osm_type' size='5'/><br>
   <input type='submit' value='Прочитать базу'/>
   </form>
<?php
  }
  else
  {
      if (!is_numeric($_GET['osm_type'])) die ('Недостаточно параметров!');
      $osm_type=$_GET['osm_type'];
      include ('../connect.inc');
      $res = $db->query ('select template from osm_template where osm_type='.$osm_type);
      if (!$res || !$res->num_rows) die ('Ошибка чтения базы: '.$db->error);
      print ('<p>Результаты чтения шаблона осмотра (тип '.$osm_type.'):<br><textarea cols="150" rows="50">');
      while ($row=$res->fetch_object())
      {
          $line = "\n"; // формируем с обратной стороны
          $select='';
          if ($row->type=='select') $select=$row->value;
          else if ($row->value) $line=';'.$row->value.$line;
          if ($row->size) $line=';'.$row->size.$line;
          if ($row->suffix) $line=';'.$row->suffix.$line;
          if ($row->name) $line=';'.$row->name.$line; else die ('Ошибка в базе! Элемент '.$row->id);
          if ($row->type) $line=';'.$row->type.$line; else die ('Ошибка в базе! Элемент '.$row->id);
          if ($row->parent_id) $line=';'.$row->parent_id.$line; else $line=';'.$line;
          if ($row->id) $line=$row->id.$line; else die ('Ошибка в базе! Элемент '.$row->id);
          print $line;
          if ($row->type=='select' || $row->type=='multi')
          {
              $lines=explode(';',$row->value);
              foreach ($lines as $line) print $line."\n";
          }
      }
      print ('</textarea><br> <a href="osm_import.php">Вернуться к предыдущей странице</a></p>');
  }
}
if ($_GET['mode']=='export2')
{
  if (!isset($_GET['osm_type']))
  {
?>
   <form method='get'>
   Номер (тип) осмотра: <input type='text' name='osm_type' size='5'/><br>
   <input type='submit' value='Прочитать базу'/>
   </form>
<?php
  }
  else
  {
      if (!is_numeric($_GET['osm_type'])) die ('Недостаточно параметров!');
      $osm_type=$_GET['osm_type'];
      include ('../connect.inc');
      $res = $db->query ('select template from osm_print where osm_type='.$osm_type);
      if (!$res || !$res->num_rows) die ('Ошибка чтения базы: '.$db->error);
      $row=$res->fetch_row();
      print ('<p>Результаты чтения шаблона осмотра (тип '.$osm_type.'):<br><textarea cols="150" rows="50">'.$row[0].'</textarea><br> <a href="osm_import.php">Вернуться к предыдущей странице</a></p>');
  }
}
include ('../footer.inc');
?>

