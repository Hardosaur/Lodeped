<?php
//
// OTC.PHP
// Редактирование перечня безрецептурного отпуска лекарст
//
require('../../settings.php');
require('../auth.php');
require('../connect.inc');
$WINDOW_TITLE = 'Список безрецептурных препаратов';
include('../header.inc');
// получим список id в ассоциативный массив
$otc = array();
$res = $db->query ('select lek_id from otc');
if ($res && $res->num_rows)
{
  while ($row=$res->fetch_row())
  {
      $otc[$row[0]]=1;
  }
  $res->free();
} else print ('<p>Список пуст!</p>');
// прочитаем список всех лекарств
$leks = array();
$res = $db->query ('select lek_id, rname from lek_names order by rname');
if ($res && $res->num_rows)
{
  while ($row = $res->fetch_object())
  {
      $leks[$row->lek_id]=$row->rname;
  }
  $res->free();
} else die ('Нет доступа к списку лекарств! '.$db->error);
// анализ списка лекарств, если передан
if (isset($_POST['list']))
{
  $list = explode ("\n",$_POST['list']);
  if (!count($list)) print ('<p>Загруженный перечень пуст!</p>');
  foreach ($list as $k => &$l)
  {
      $l=trim($l);
      if (!strlen($l)) unset ($list[$k]);
  }
//  print_r($list);
  $analysis=count($list);
  $flist=array();
  $found=0;
  foreach ($leks as $lek_id=>$rname)
  {
      if (($key = array_search($rname,$list))!== FALSE)
      {
//          print ('<br>*'.$rname);
          if (!isset($otc[$lek_id])) { $flist[]='('.$lek_id.')'; $otc[$lek_id]=1; }
          $found++;
          unset($list[$key]);
      }
  }
  $done = count ($flist);
  if ($done) // добавляем препараты
  {
      if (!$db->query('insert into otc values '.join(',',$flist))) die ('Ошибка внесения данных в базу! '.$db->error);
  }
}
// ручное изменение безрецептурного списка
if (isset($_POST['manual']))
{
  unset($otc);
  $otc=array(); $flist=array();

  foreach ($_POST as $key=>$post)
    if (is_numeric($key)) { $otc[$key]=1; $flist[]='('.$key.')'; }
  if (!$db->query('truncate table otc')) print ('<p>Ошибка обнуления базы! '.$db->error.'</p>');
  if (count($flist) && !$db->query('insert into otc values '.join(',',$flist))) die ('Ошибка внесения данных в базу! '.$db->error);
}
// Выведем список всех лекарств
print ('<p>В базе '.count($otc).' препаратов, не требующих рецепта.</p>');
print ('<table border="0"><tr><td>'."\n".'<form method="post"><input type="hidden" name="manual" value="1"/>'."\n");
foreach ($leks as $lek_id => $rname)
{
  print ('<input name="'.$lek_id.'" type="checkbox"');
  if (isset($otc[$lek_id])) print (' checked');
  print ('/>&nbsp;'.$rname.'</br>'."\n");
}
print ('<input type="submit" value="Сохранить"/></form></td>'."\n");
// форма внесения списка лекарств из документа
print ('<td valign="top">'."\n");
if (isset($analysis)) // список был внесен ранее
{
  print ('<p>Проанализировано: '.$analysis.' названий препаратов. Из них сопоставлено: '.$found.'. Из них добавлено в базу: '.$done.'.</p>');
  if (count($list))
  {
      print ('<p>Не сопоставлены следующие элементы из перечня:</p><ul>');
      foreach ($list as $l) print ('<li>'.$l.'</li>'."\n");
      print ('</ul>');
  }
}
print ('<h2>Список препаратов:</h2><form method="post"><textarea name="list" cols="50" rows="50"/></textarea><br><input type="submit" value="Загрузить"/></form></td>'."\n");
include ('../footer.inc');

?>