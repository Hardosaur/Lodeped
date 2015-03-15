<?php
//
// OSMOTR2.PHP
//
require('../settings.php');
require('auth.php');
require('connect.inc');
//
if (isset($_GET['do'])) // режим редактирования
{
  $id=$_GET['id'];
  unset ($_GET['id']);
  switch ($_GET['do'])
  {
      case 'up': // переместить запись вверх на одну позицию
      if (!is_numeric($id))
      {
          print ('Внимание: неверный id поля ('.$id.')');
          return;
      }
      $res=$db->query ('select ordr from osm_fields where id='.$id);
      if (!$res || !$res->num_rows) die ('Не найдено поле с id='.$id);
      $row=$res->fetch_row();
      $ordr=$row[0];
      $res->free();
      if (!$db->query('update osm_fields set ordr=ordr+1 where ordr='.($ordr-1))) die ('Невозможно обновить значение ordr ('.$db->error.')');
      if (!$db->query('update osm_fields set ordr=ordr-1 where id='.$id)) die ('Невозможно обновить значение ordr ('.$db->error.')');

      break;

      case 'insert': // вставить запись после 'after'
      $after=$_GET['after'];
      if (!is_numeric($id) || !is_numeric($after))
      {
          print ('Внимание: неверный id поля ('.$id.', '.$after.')');
          return;
      }
      $res=$db->query ('select ordr from osm_fields where id='.$after);
      if (!$res || !$res->num_rows) die ('Не найдено поле с id='.$after);
      $row=$res->fetch_row();
      $ordr=$row[0];
      $res->free();
      if (!$db->query('update osm_fields set ordr=ordr+1 where ordr>'.$ordr)) die ('Невозможно обновить значение ordr ('.$db->error.')');
      if (!$db->query('update osm_fields set ordr='.($ordr+1).' where id='.$id)) die ('Невозможно обновить значение ordr ('.$db->error.')');
      break;

      case 'delete':
      if (!is_numeric($id))
      {
          print ('Внимание: неверный id поля ('.$id.')');
          return;
      }
      if (!$db->query ('delete from osm_fields where id='.$id)) die ('Ошибка удаления поля: '.$db->error);
      break;

      default: die ('Неверный параметр: do='.$_GET['do']);
  }
  header ('Location: '.$_SERVER['PHP_SELF']);
}
//
require('osmotr.class.php');
// выбор режима работы
if (isset($_GET['preprint']))
{
  $Osmotr = new cOsmotr (MODE_PREPRINT);
  $Osmotr->preprint();
  exit;
}
if (isset($_GET['print']))
{
  $Osmotr = new cOsmotr (MODE_PRINT);
  $Osmotr->print_();
  exit;
}

if (isset($_GET['view']))
{
  $Osmotr = new cOsmotr (MODE_VIEW);
  $Osmotr->print_();
  exit;
}
if (isset($_GET['id']))
{
  if (isset($_GET['copy'])) $Osmotr = new cOsmotr (MODE_COPY);
  else $Osmotr = new cOsmotr (MODE_COMMON);
}
elseif (isset($_GET['type']))
{
  if (isset($_GET['pat_id'])) $Osmotr = new cOsmotr (MODE_NEW);
  else $Osmotr = new cOsmotr (MODE_TYPE);
}
else $Osmotr = new cOsmotr (MODE_EDIT);

$Osmotr->dispatch_action();
require('osm_header2.inc');
$Osmotr->print_hidden_fields();
$Osmotr->out();
include ('footer.inc');
?>