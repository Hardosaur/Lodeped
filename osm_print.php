<?php
require('../settings.php');
require('auth.php');
// -----------------------------------------------------------------------------
// ѕечать готового отчЄта (после внесенных ручных правок)
//
if (isset($_POST['print']))
{
?>
<html><head><title>Ќа печать</title>
<style>
body { font: italic 11pt "Book Antiqua" }
</style>
<?php
  foreach (explode ("\n",$_POST['print']) as $line) print (rtrim($line).'<br>');
  exit;
}
// -----------------------------------------------------------------------------
// ѕросмотр отчЄта перед печатью
//
include('header.inc');
include('connect.inc');
if (!isset($_GET['id'])) die ('Ќет требуемых параметров в сессии! («апуск вручную?)');
$osm_id=$_SESSION['osm_id'];
$osm_type=$_SESSION['osm_type'];
$pat_id=$_SESSION['pat_id'];
$date=$_SESSION['date'];
$res=$db->query('select data from osm_data where osm_id='.$osm_id);
if (!$res || !$res->num_rows) die ('Ќеверный ID протокола осмотра! Ќет такого в базе!');
$row = $res->fetch_row();
$vals = explode ($delim,$row[0]); // получаем пары "им€ = значение"
$res->free();
foreach ($vals as $pair)
{
  list ($id, $value) = explode ('=',$pair);
//  print ($id.':'.$value.'.<br>');
  $values[$id]=stripslashes($value);
}
// читаем диагнозы
$res=$db->query('select diag from diags where pat_id='.$pat_id.' and redefined=0 and unset_date is null order by set_date desc');
if ($res && $res->num_rows)
{
  $diags='';
  while ($row=$res->fetch_row()) $diags.=' '.$row[0];
  $res->free();
  $values['700']=$diags;
}
// читаем лекарственные назначени€
$res=$db->query('select lek from leks where pat_id='.$pat_id.' and unset_date is null order by set_date desc');
if ($res && $res->num_rows)
{
  $leks='';
  while ($row=$res->fetch_row()) $leks.=' '.$row[0];
  $res->free();
  $values['1000']=$leks;
}
?>
<table border="0" cellpadding="0" cellspacing="20" width="100%"><col width="250"><col>
<tr valign="top"><td align="left">
<a class="pages" href="osmotr.php?page=1">1. Ўапка</a><br>
<?php
$res=$db->query ('select id, name, suffix, value from osm_template where osm_type='.$osm_type.' and type="page"');
if (!$res || !$res->num_rows) die ('Ќет данных о разделител€х страниц в шаблоне осмотра!');
while ($row = $res->fetch_object())
{
//  if ($row->value == $osm_page) { print ('<span class="pages"><b>'.$row->value.'. '.$row->name.'</b></span><br>'); $page_id=$row->id; }
//  else
      if (strlen($row->suffix)) print ('<a class="pages" href="'.$row->suffix.'.php?page='.$row->value.'">'.$row->value.'. '.$row->name.'</a><br>');
      else print ('<a class="pages" href="osmotr.php?page='.$row->value.'">'.$row->value.'. '.$row->name.'</a><br>');
}
$res->free();
print('</td><td align="left">');
print ('<h2>ѕросмотр перед печатью</h2>');
print ('<form method="post" target="_blank"><textarea name="print" cols="80" rows="40">');
$d=explode('-',$date);
print ($d[2].'.'.$d[1].'.'.$d[0].' ');
//
// „итаем шаблон
//
$res=$db->query('select template from osm_print where osm_type='.$osm_type);
if (!$res || !$res->num_rows) die ('Ќе найден шаблон осмотра є'.$osm_type);
$row = $res->fetch_row();
$tmpl=explode("\n",$row[0]);
$res->free();
$count=0;
foreach ($tmpl as $line)
{
  $line = rtrim($line); // удалим лишние символы в конце строки, напр, \r
  $count++;
  if (!strlen($line)) continue;
  if ($line{0} == ';') continue; // комментарии
  $out = '';
  $found = 0; // флаг обнаружени€ переменных
  $vars = 0; // число найденных переменных
  $p1 = 0; // начало подстроки
  while (1) // начинаем разбор шаблона, в котором переменные обозначены как %переменна€ или %[список]
  {
      $len = strlen($line);
      if ($p1>=$len) break; // строка закончена
      while ($p1<$len && $line{$p1}!='%') { $out.=$line{$p1}; $p1++; }
      if ($p1==$len) break; // строка закончена
      if ($line{$p1+1}=='%') { $p1++; continue; } // пропустим знак %%
      $p2=$p1+1;
      if ($line{$p2}=='[') // обработка списка переменных вида %[a,b,c,...n]
      {
          $p2++;
          $id='';
          while ($p2<$len && (($line{$p2}>='0' && $line{$p2}<='9') || $line{$p2}==',' || $line{$p2}==' ')) {$id.=$line{$p2}; $p2++; }
          if ($line{$p2}!=']') die ('<p>ќшибка в строке '.$count.'! Ќет закрывающей скобки дл€ списка переменных подстановки!</p>');
          $p2++;
          $vars++;
          $ids = explode (',',$id);
          if (!count($ids)) die ('<p>ќшибка в строке '.$count.'! —писок переменных пуст!</p>');
          $list=array();
          foreach ($ids as $id)
          {
              $id=trim($id);
              if (isset($values[$id])) $list[] = $values[$id]; // массив значений переменных, указанных в списке
          }
          if (count($list))
          {
              $out .= join(', ', $list);
              $found++;
          }
      }
      else // обработка одной переменной вида %a
      {
          $id='';
          while ($p2<$len && $line{$p2}>='0' && $line{$p2}<='9') {$id.=$line{$p2}; $p2++; }
          //      $id+=0; // переводим в числовой контекст
          if ($id<=0) die ('<p>ќшибка в строке '.$count.'! Ќеверно указана переменна€ дл€ подстановки!</p>');
          $vars++;
          if (isset($values[$id]))
          {
              $out.=$values[$id];
              $found++;
          }
       }
       $p1=$p2; // пропустим переменную
  }
  if ($found || !$vars) print (str_replace ('<br>',"\n",$out));
}
/*
      $len = strlen($line); // корректируем длину строки
      while ($pos<$len && $line{$pos}!='[') $pos++;
      if ($pos==$len) break;
      $p1=$pos;
      while ($pos<$len && $line{$pos}!=']') $pos++;
      if ($pos==$len) break; // но это ошибка в строке!
      $found--; // найдена переменна€
      $var=substr ($line, $p1+1, $pos-$p1-1);

//      print ('Ќайдена переменна€ '.$var.' в позиции '.$p1.'<br>'); // дл€ отладки

      if (isset($values[$var])) // найдена переменна€, требуетс€ ее вставить
      {
          $found++; // $found будет оставатьс€ в 0, если переменные найдены и вставлены
          $line = substr_replace($line,$values[$var],$p1,$pos-$p1+1);
          $pos=$p1; // начнем сначала, т.к. длина строки могла изменитьс€
      }
  }
  if (!$found) print ($line); // найдены и вставлены все переменные, либо переменных нет
                              // а иначе строка не выводитс€
}
*/
print('</textarea><br><input type="submit" value="Ќа печать"/></form><br><input type="button" value="«акрыть форму осмотра" onclick="location=\'doctor.php\'"/></table>');
include('footer.inc');
?>