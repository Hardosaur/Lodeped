<?php
//
// OSM_PAGES.INC
// Перечень страниц протокола осмотра
// Входные данные: $osm_page - номер страницы
// Должно быть открыто соединение с БД
//
if ($osm_page==1) print ('<span class="pages"><b>1. Шапка</b></span><br>');
else print ('<a class="pages" href="javascript:document.getElementById(\'next\').value=\'1\';document.getElementById(\'script\').value=\'\';document.forms[0].submit()">1. Шапка</a><br>');
//
// Получим информацию о страницах, оформим навигационную панель
//
$nxid = 0; // id элемента описания следующей страницы
$nxpage=0; // номер след. страницы
$nxscript=''; // имя след. скрипта (для некоторых случаев)
$res=$db->query ('select id, name, suffix, value from osm_template where osm_type='.$osm_type.' and type="page"');
if (!$res || !$res->num_rows) die ('Нет данных о разделителях страниц в шаблоне осмотра!');
while ($row = $res->fetch_object())
{
  if ($row->value == $osm_page) { print ('<span class="pages"><b>'.$row->value.'. '.$row->name.'</b></span><br>'); $page_id=$row->id; }
  else print ('<a class="pages" href=\'javascript:document.getElementById("next").value="'.$row->value.'";document.getElementById("script").value="'.$row->suffix.'";document.forms[0].submit()\'>'.$row->value.'. '.$row->name.'</a><br>');
  if (!$nxid && $row->value > $osm_page) { $nxid=$row->id; $nxpage=$row->value; $nxscript = $row->suffix; }
}
if (!isset($page_id))
   if ($osm_page>1) die ('Не найдена страница номер '.$osm_page.' в описании шаблона!');
   else $page_id=0;
$res->free();
?>