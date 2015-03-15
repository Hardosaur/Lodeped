<?php
//
//  Программа управления диагнозами (с полным названием)
//  Диагнозы хранятся в базе данных
//
require('../settings.php');
include('header.inc');
require('auth.php');
include('connect.inc');
//
// Проверка переменных сессии
//
if (!isset($_SESSION['osm_id']) || !isset($_SESSION['pat_id']) || !isset($_SESSION['doctor_id']) || !isset($_SESSION['date']))
{
  die ('Не установлены необходимые переменные в сессии! Запуск вручную?');
}
$date = $_SESSION['date'];
$osm_id = $_SESSION['osm_id'];
$pat_id = $_SESSION['pat_id'];
$doctor_id = $_SESSION['doctor_id'];
$osm_type = $_SESSION['osm_type'];
if (!isset($_GET['page']))
{
  if (!isset($_SESSION['osm_page'])) die ('Не указан номер страницы!');
  $osm_page=$_SESSION['osm_page'];
}
else
{
  $osm_page=$_GET['page'];
  $_SESSION['osm_page']=$osm_page;
}
//
// Запрос на снятие диагноза
//
if (isset($_GET['unset']))
{
  $unset=$_GET['unset'];
  if (!is_numeric($unset)) die ('<p>Ошибка передачи id диагноза для снятия!</p>');
  if (!$db->query ("update diags set unset_date=\"$date\" where set_id={$unset} and redefined=0")) print ('<p>Ошибка снятия диагноза: '.$db->error.'</p>');
}
//
// Запрос на отмену снятия диагноза
//
if (isset($_GET['ununset']))
{
  $unset=$_GET['ununset'];
  if (!is_numeric($unset)) die ('<p>Ошибка передачи id диагноза для отмены снятия!</p>');
  if (!$db->query ("update diags set unset_date=NULL where set_id={$unset} and redefined=0")) print ('<p>Ошибка снятия диагноза: '.$db->error.'</p>');
}

//
// Запрос на удаление диагноза
//
if (isset($_GET['delete']))
{
  $delete=$_GET['delete'];
  if (!is_numeric($delete)) die ('<p>Ошибка передачи id диагноза для удаления!</p>');
  $res = $db->query ('select sub_id from diags where set_id='.$delete.' and redefined=0');
  if (!$res || !$res->num_rows) die ('<p>Не найден диагноз с заданным номером!</p>');
  if ($res->num_rows>1) die ('<p>Ошибка в базе данных! Цепочка уточненных диагнозов не верна для набора '.$delete.'</p>');
  $row=$res->fetch_row();
  $sub_id=$row[0];
  $res->free();
  if (!$db->query ("delete from diags where set_id=$delete and sub_id=$sub_id")) print ('<p>Ошибка удаления диагноза: '.$db->error.'</p>');
}
//
// Добавляем новый диагноз или уточняем имеющийся
//
if (isset($_POST['diag']))
{
  if (!isset($_POST['diag_id'])) die ('<p>Не передан id диагноза!</p>');
  // Формируем полное название
  $diagnosis=$_POST['diag'];
  if (isset($_POST['set_id'])) // Запрос на уточнение диагноза
  {
      $set_id=$_POST['set_id'];
      $res = $db->query ('select sub_id, diag_id, set_date from diags where set_id='.$set_id.' and redefined=0');
      if (!$res || !$res->num_rows) die ('<p>Не найден диагноз с заданным номером!</p>');
      if ($res->num_rows>1) die ('<p style="color:red">Ошибка в базе данных! Цепочка уточненных диагнозов не верна для набора '.$set_id.'</p>');
      $row=$res->fetch_row();
      $sub_id=$row[0]+1;
      $olddate = $row[2]; // дату возьмем у предыдущего диагноза, т.к. мы его не ставим, а уточняем
      if ($row[1]!=$_POST['diag_id']) print ('<p style="color:red">Уточнение диагноза невозможно! Изменен код диагноза (т.е. указан новый диагноз).</p>');
      else
      {
          if (!$db->query('update diags set redefined=1, unset_date="'.$date.'" where set_id='.$set_id.' and redefined=0')) die ('<p>Ошибка обновления базы: '.$db->error.'</p>');
          $query="insert into diags values ($set_id, $sub_id, $pat_id, $doctor_id, {$_POST['diag_id']}, \"$diagnosis\", \"$olddate\", NULL, 0)";
          //print ($query);
          if (!$db->query($query)) die ('<p>Диагноз (уточненный) в базу не внесен! Ошибка: '.$db->error);
      }
      $res->free();
  }
  else // Запрос на внесение нового диагноза
  {
      $res = $db->query ("select sub_id from diags where pat_id=$pat_id and diag_id={$_POST['diag_id']} and unset_date is null");
      if ($res && $res->num_rows) { print ('<p style="color:red">Невозможно добавить диагноз, т.к. он уже установлен! Попробуйте уточнить имеющийся.</p>'); $res->free(); }
      else
      {
          $query = "insert into diags values (NULL, 1, $pat_id, $doctor_id, {$_POST['diag_id']}, \"$diagnosis\", \"$date\", NULL, 0)";
          //print ($query);
          if (!$db->query ($query)) die ('<p>Диагноз (новый) в базу не внесен! Ошибка: '.$db->error);
      }
  }
} // не выходим
//
// Выводим общую шапку с перечнем диагнозов
//
?>
<script language="JavaScript" type="text/javascript">
function showDiagnosis()
{
  var cnt = 1;
  var div = document.getElementById('Diagnosis');
  var text = '';
  var elem;
  while (elem = document.getElementById('data'+cnt))
  {
      if (elem.type=='select-one') // элемент типа select
         text+=' '+elem.options[elem.selectedIndex].value;
      if ((elem.type=='text' || elem.type=='hidden') && elem.value) text+=' '+elem.value;
      cnt++;
  }
  div.value=text;
}
</script>
<style>
table.list tr td { padding: 5px; }
</style>
<table border="0" cellpadding="0" cellspacing="20" width="100%"><col width="250"><col>
<tr valign="top"><td class="nav" align="left" width="260">
<a class="pages" href="osmotr.php?page=1">1. Шапка</a><br>
<?php
$res=$db->query ('select id, name, suffix, value from osm_template where osm_type='.$osm_type.' and type="page"');
if (!$res || !$res->num_rows) die ('Нет данных о разделителях страниц в шаблоне осмотра!');
while ($row = $res->fetch_object())
{
  if ($row->value == $osm_page) { print ('<span class="pages"><b>'.$row->value.'. '.$row->name.'</b></span><br>'); $page_id=$row->id; }
  else
      if (strlen($row->suffix)) print ('<a class="pages" href="'.$row->suffix.'.php?page='.$row->value.'">'.$row->value.'. '.$row->name.'</a><br>');
      else print ('<a class="pages" href="osmotr.php?page='.$row->value.'">'.$row->value.'. '.$row->name.'</a><br>');
}
$res->free();
print('</td><td align="left">');
//
// Читаем список актуальных диагнозов из базы данных
//
$res = $db->query ("select set_id, sub_id, diag_id, diag, set_date, unset_date, redefined from diags where pat_id=$pat_id and set_date <= \"$date\" and (unset_date is null or unset_date >= \"$date\") order by set_id, sub_id desc");
if ($res && $res->num_rows)
{
  if (isset($set_id)) unset ($set_id);
  print ('<table class="list" cellpadding=3><tr><th>Дата постановки</th><th>Код</th><th>Полное название</th></tr>'."\n");
  while ($row = $res->fetch_object())
  {
      if (isset($set_id) && $set_id==$row->set_id) continue; // пропускаем диагноз, т.к. ранее уже был выведен более свежий
      $set_id=$row->set_id;
      $dat = explode('-',$row->set_date);
      $set_date = $dat[2].'.'.$dat[1].'.'.$dat[0];
      if (isset($unset_date)) unset ($unset_date);
      if ($row->unset_date)
      {
          $dat=explode('-',$row->unset_date);
          $unset_date=$dat[2].'.'.$dat[1].'.'.$dat[0];
      }
      if (isset($unset_date)) print ('<tr style="color: grey">'); else print ('<tr>');
      print ('<td>'.$set_date.'</td>');
      print ('<td>'.$row->diag_id.'</td>');
      print ('<td>'.$row->diag);
      if ($row->redefined) print (' (диагноз переформулирован)</td></tr>'."\n"); // не последний в цепочке
      else
      if (isset($unset_date)) // уже был снят
      {
          if (strcmp($date,$row->unset_date)<0) print (' (диагноз снят в будущем) ');
          else print (' (диагноз снят на момент осмотра) ');
          print ("[<a href='diag.php?ununset={$row->set_id}'>Отменить снятие</a>]</td></tr>\n");
      }
      else
      {
          print ("&nbsp;[<a href='diag.php?diag_id={$row->diag_id}&set_id={$row->set_id}'>Изменить</a>]");
          print ("[<a href='diag.php?unset={$row->set_id}'>Снять</a>]");
          if ($row->sub_id == 1) print ("[<a href='diag.php?delete={$row->set_id}'>Удалить</a>]"); // удалить можно только первый диагноз в цепочке
          print ("</td></tr>\n");
      }
  }
  print ('</table></p>');
}
else print ('</p><p style="font-style: italic">Нет установленных диагнозов (на дату осмотра).</p>');
//
//
//
//print ('</ul></p><h2>Выбор диагноза</h2>');
//print ('<form method="post" action="diag.php"><p><table border="0"><tr valign="top" align="center">');

if (!isset($_GET['diag_id'])) // название диагноза не выбрано
{
  print ('<h2>Выбор нового диагноза</h2>');
  print ('<form method="post" action="diag.php"><p><table border="0"><tr valign="top" align="center">');
  if (!isset($_GET['letter'])) // не выбрана первая буква, выводим их список для выбора
  {
      print ('<td>Выберите первую букву названия диагноза:<br>');
      // выводим только первые буквы названий
      $res=$db->query('select diag_name from diag_names');
      if (!$res || !$res->num_rows) die ('База данных названий диагнозов пуста или недоступна! Ошибка: '.$db->error);
      $letters=array(); // массив первых букв
      while ($row=$res->fetch_array())
      {
            $letter = $row[0]{0};
            if (!isset($letters[$letter])) $letters[$letter]=1;
            else $letters[$letter]++;
      }
      $res->free();
      foreach ($letters as $letter => $value)
      {
           print ('<input type="button" value="'.$letter.'" onclick="javascript:document.location=\'diag.php?letter='.$letter.'\'"/> ');
      }
  }
  else // Передана первая буква, вывести список диагнозов на эту букву
  {
      print ('<td width="200">Первая буква:<br><b>'.$_GET['letter'].'</b><br>(<a href="diag.php">выбрать другую</a>)</td>');
      // получаем из базы все диагнозы на эту букву
      $res=$db->query('select * from diag_names where diag_name like "'.$_GET['letter'].'%"');
      if (!$res || !$res->num_rows) die ('База данных названий диагнозов не содержит названий на букву'.$_GET['letter'].'! Ошибка: '.$db->error);
      $size=$res->num_rows;
      if ($size<2) $size=2;
      print ('<td width="300">Диагнозы:<br><select size="'.$size.'" onchange="javascript:document.location=\'diag.php?diag_id=\'+this.options[this.selectedIndex].value">');
      while ($row = $res->fetch_object())
      {
          print ("\n<option value='{$row->diag_id}'>$row->diag_name</option>");
      }
      $res->free();
  }
  print ('</td></tr></table></p></form>');
}
else // передано название диагноза, вывести поля ввода полного названия
{
  if (isset($set_id)) unset($set_id);
  if (isset($_GET['set_id']))
  {
      $set_id=$_GET['set_id'];
      print ('<h2>Изменение формулировки диагноза</h2><p>Прежняя формулировка: ');
      $res = $db->query ('select diag from diags where set_id='.$set_id.' and redefined=0');
      if (!$res || !$res->num_rows) die ('<p>Не найден диагноз с заданным номером!</p>');
      if ($res->num_rows>1) die ('<p>Ошибка в базе данных! Цепочка уточненных диагнозов не верна для набора '.$set_id.'</p>');
      $row=$res->fetch_row();
      print ('<b>'.$row[0].'</b></p>');
      $res->free();
  }
  else
  {
      print ('<h2>Выбор нового диагноза</h2>');
  }
  print ('<form method="post" action="diag.php"><p><table border="0"><tr valign="top" align="center">');
  // получить название диагноза
  $diag_id=$_GET['diag_id'];
  if (!is_numeric($diag_id)) die ('Неверный формат номера диагноза!');
  $res=$db->query('select diag_name from diag_names where diag_id='.$diag_id);
  if (!$res || !$res->num_rows) die ('База данных названий диагнозов не содержит диагноза с номером '.$diag_id.'! Ошибка: '.$db->error);
  $row=$res->fetch_row();
  $diag_name=$row[0];
  $res->free();
  // вывести уже введенные данные
  $letter=$diag_name{0};
  print ('<td width="200">Первая буква:<br><b>'.$letter.'</b><br>(<a href="diag.php">выбрать другую</a>)</td>');
  print ('<td width="300">Название диагноза:<br><b>'.$diag_name.'</b><br>(<a href="diag.php?letter='.$letter.'">выбрать другой</a>)</td>');
  print ('<input type="hidden" name="diag_id" value="'.$diag_id.'"/>');
  if (isset($set_id)) print ('<input type="hidden" name="set_id" value="'.$set_id.'"/>');
  // чтение данных к диагнозу
  $res=$db->query('select data from diag_data where diag_id='.$diag_id);
  if (!$res || !$res->num_rows) die ('База данных описаний диагнозов не содержит данных к выбранному диагнозу! Ошибка: '.$db->error);
  $row=$res->fetch_row();
  $data=$row[0];
  $res->free();
  // разбор данных и вывод элементов ввода
  $cascades = explode ('|',$data);
  $c = 0; // счетчик каскадов
  foreach ($cascades as $cascade)
  {
      $c++;
      // рассмотрим разные частные случаи
      // 1. У диагноза нет уточнений, он состоит только из имени
      if ($cascade{0}=='=')
      {
          $value=substr($cascade,1); // убираем первый знак '='
          print ("<td><input type='hidden' id='data{$c}' value='$value'/></td>\n");
          $fullname=$value;
          break; // foreach
      }
      // делим каскад на части
      $opts = explode (';',$cascade);
      // 2. Первая строка содержит prompt, обозначенный знаком ':'
      $pos=0;
      if (isset($prompt)) unset ($prompt);
      while ($pos<strlen($opts[0]) && $opts[0]{$pos}!=':' && $opts[0]{$pos}!='=') $pos++;
      if ($opts[0][$pos]==':') // найден prompt
      {
          $prompt=substr($opts[0],0,$pos);
          $opts[0]=substr($opts[0],$pos+1);
      }
      // 3. Первая строка уазывает, что требуется пользовательский ввод - нет ничего, кроме приглашения
      $size=count($opts);
      if ($size==1 && isset($prompt) && strlen($opts[0])==0)
      {
          print ("<td width='400'>$prompt:<br><input type='text' id='data{$c}' value='' size='40' onchange='javascript:showDiagnosis()'/></td>\n");
          continue; // foreach
      }

      print ('<td width="200">');
      if (isset($prompt)) print ($prompt.':<br>'); else print ('&nbsp;<br>');
      // 4. Имеется только одна пара - список не нужен
      if ($size==1)
      {
          list ($var,$value) = explode ('=',$opts[0]);
          print $var;
          print ("<input type='hidden' id='data{$c}' value='$value'/></td>\n");
          continue; // foreach
      }
      print ("<select id='data{$c}' size='$size' onchange='javascript:showDiagnosis()'>");
      foreach ($opts as $opt)
      {
          // Общий случай: делим каждую строку на пару "вариант=значение"
          list ($var,$value) = explode ('=',$opt);
          print ("<option value='$value'>$var</option>\n");
      }
      print ('</select></td>'."\n");
  }
  // Вывод кнопки добавления
  print ('</tr></table></p><p>Полное название диагноза:&nbsp;<input type="text" name="diag" id="Diagnosis" size="120" value="');
  if (isset($fullname)) print $fullname;
  print('"/></p><p><input type="submit" value="');
  if (isset($set_id)) print ('Изменить формулировку"/>');
  else print ('Добавить диагноз"/>');
  print('&nbsp;<input type="button" value="Отменить" onclick="document.location=\'diag.php\'"/></p></form>');
}
print ('<p><input type="button" value="Далее >>" onclick="document.location=\'osmotr.php?page='.($osm_page+1).'\'"/></p>');
include('footer.inc');
?>