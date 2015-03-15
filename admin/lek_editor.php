<?php
//
// Редактор таблиц, описывающих форму ввода лекарственных назначений
//
require('../../settings.php');
require('../auth.php');
require ('../access.inc');
check_access_level (1);
include('../header.inc');
include ('../connect.inc');
print ('<h1>Редактор формы ввода лекарственного назначения</h1>');

if (isset ($_POST['lek_names']))
{
  if (isset($_POST['data']))
  {
      $data = explode("\n",$_POST['data']); // выделим отдельные строки
      if (!count($data)) die ('Ошибка в переданных данных! Нет строк.');
      // удалим старые данные
      if (!$db->query ('truncate table lek_names')) die ('Невозможно удалить старые данные! Ошибка: '.$db->error);
      // разбор строк и занесение в базу
      $count=0;
      foreach ($data as $line)
      {
          $line=rtrim($line);
          if (!strlen($line)) continue;
          $count++;
          list ($id,$lname,$rname) = explode (';',$line);
          if (!is_numeric($id) || !strlen($lname) || !strlen($rname)) die ("Ошибка в строке №$count: $line");
          $lname=addslashes($lname);
          $rname=addslashes($rname);
          $query = "insert into lek_names values ($id, '$lname', '$rname')";
          if (!$db->query($query)) die ("Ошибка добавления данных: $id, '$lname', '$rname'");
      }
  }
}
if (isset($_POST['lek_forms'])) // сохранение форм лекарственных препаратов
{
  if (isset($_POST['list']) && strlen ($_POST['list']))
  {
     $data = explode("\n",$_POST['list']); // выделим отдельные строки
      if (!count($data)) die ('Ошибка в переданных данных! Нет строк.');
      // удалим старые данные
      if (!$db->query ('truncate table lek_forms')) die ('Невозможно удалить старые данные! Ошибка: '.$db->error);
      // разбор строк и занесение в базу
      $count=0;
      foreach ($data as $line)
      {
          $line=rtrim($line);
          if (!strlen($line)) continue;
          $count++;
          list ($id,$lname,$rname) = explode (';',$line);
          if (!is_numeric($id) || !strlen($rname)) die ("Ошибка в строке №$count: $line");
          if (isset($lname)) $lname=addslashes($lname); else $lname='';
          $rname=addslashes($rname);
          $query = "insert into lek_forms values ($id, '$lname', '$rname')";
          if (!$db->query($query)) die ("Ошибка добавления данных: $id, '$lname', '$rname'");
      }
   }
}
if (isset($_POST['tab_id'])) // сохранение нового содержимого таблицы
{
  if (isset($_POST['list']) && strlen ($_POST['list']))
  {
     $data = explode ("\n", $_POST['list']);
     foreach ($data as $key => $val)
     {
        $val=trim($val);
        if (strlen($val)) $data[$key]=$val;
        else unset ($data[$key]);
     }
     if (count ($data))
     {
        $list = implode (';', $data);
        //print ('<p>'.$list.'</p>');
        if (!$db->query ('update lek_data set list = "'.$list.'" where tab_id='.$_POST['tab_id'])) print ('<p>Ошибка обновления базы данных! '.$db->error.'</p>');
     }
     else print ('<p>Список пуст!</p>');
  }
  else print ('<p>Внутренняя ошибка 1!</p>');
}

// Названия лекарственных средств


print ('<table><tr valign="top"><td align="center"><form method="post"><textarea name="data" cols="50" rows="100">');
$res = $db->query ('select * from lek_names');
if (!$res || !$res->num_rows) break;
while ($row=$res->fetch_object())
{
  print ($row->lek_id.';'.$row->lname.';'.$row->rname."\n");
}
$res->free();
print ('</textarea><input type="submit" value="Сохранить"/><input type="hidden" name="lek_names" value="1"/></form></td>');


// Формы лекарственных средств


print ('<td align="center"><form method="post"><textarea name="list" cols="36" rows="');
$res = $db->query ('select * from lek_forms');
if (!$res || !$res->num_rows) break;
print ($res->num_rows.'">');
while ($row=$res->fetch_object())
{
  print ($row->form_id.';'.$row->lname.';'.$row->rname."\n");
}
$res->free();
print ('</textarea><input type="submit" value="Сохранить"/><input type="hidden" name="lek_forms" value="1"/></form></td>');

// Таблицы интерфейса

$tab_id=1;
do
{
  print ('<td align="center"><form method="post">');
  $res = $db->query ('select * from lek_data where tab_id='.$tab_id);
  if (!$res || !$res->num_rows) break;
  $row=$res->fetch_object();
  print ('<b>'.$row->tab_name.'</b><br>');
  $list = explode (';',$row->list);
  $size=count($list)+1;
  if ($size>50) $size=50;
  print ('<textarea name="list" rows="'.$size.'">');
  foreach ($list as $value) print ($value."\n");
  $res->free();
  print ('</textarea><input type="submit" value="Сохранить"/><input type="hidden" name="tab_id" value="'.$tab_id.'"/></form></td>');
  $tab_id++;
} while (1);
print ('</table>');
include('../footer.inc');
?>