<?php
//
//  Программа управления назначением лекарств
//  Назначения сохраняются в базе данных с привязкой к пациентам
//
require('../settings.php');
include('header.inc');
require('auth.php');
include('connect.inc');
//
// Проверка переменных сессии
//
if (
//!isset($_SESSION['osm_id']) ||
!isset($_SESSION['pat_id']) || !isset($_SESSION['doctor_id']) || !isset($_SESSION['date']) || !isset($_SESSION['osm_id']) || !isset($_SESSION['osm_type']))
{
  die ('Не установлены необходимые переменные в сессии! Запуск вручную?');
}
$date = $_SESSION['date'];
$osm_id = $_SESSION['osm_id'];
$osm_type = $_SESSION['osm_type'];
$pat_id = $_SESSION['pat_id'];
$doctor_id = $_SESSION['doctor_id'];
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
// Добавляем новое предписание (если передан параметр)
//
if (isset($_POST['Lek']))
{
  if (!isset($_GET['lek_id'])) die ('<p style="color: red">Не передан id препарата!</p>');
  $lek_id=$_GET['lek_id'];
  unset($_GET['lek_id']); // возврат к выбору препарата
  // Формируем полное название
  $lek=$_POST['form'].' '.$_POST['lek_name'].' '.trim($_POST['Lek']);
  if ($lek{strlen($lek)-1}!='.') $lek.='.';
  if (strlen($_POST['Course'])) $lek.=' '.$_POST['Course'].'.';
  $lek=$db->real_escape_string($lek);
  $res = $db->query ("select id from leks where pat_id=$pat_id and lek_id=$lek_id and unset_date is null");
  if ($res && $res->num_rows)
  {
      $row = $res->fetch_row();
      if (!$db->query("update leks set set_date='$date', doctor_id=$doctor_id, lek='$lek' where id={$row[0]}")) die ('Ошибка обновления базы данных: '.$db->error);
      $res->free();
  }
  else
  {
      $query = "insert into leks values (NULL, $lek_id, $pat_id, $doctor_id, \"$date\", NULL, \"$lek\")";
      //print ($query);
      if (!$db->query ($query)) die ('<p>Предписание в базу не внесено! Ошибка: '.$db->error);
  }
} // не выходим
//
//  Удаление предписания
//
if (isset($_GET['delete']))
{
  $id=$_GET['delete'];
  if (!is_numeric($id)) die ('Ошибка передачи параметра!');
  if (!$db->query('update leks set unset_date = "'.$date.'" where id='.$id)) die ('Ошибка отмены назначения ('.$db->error.')! Неверный параметр?');
}
//
// Скрипты
//
?>
<script language="JavaScript" type="text/javascript">
function On (id)
{
  var el = document.getElementById(id);
  if (el.style.display == '' || el.style.display == 'none') el.style.display='inline';
}
function Off (id)
{
  var el = document.getElementById(id);
  if (el && el.style.display == 'inline') el.style.display="none";
}
function Set (id, value)
{
  var input = document.getElementById('data'+id);
  input.value=value;
  Off ('i'+id); // ввод извне, поэтому поле нужно скрыть
}
function Clear (id)
{
  var input = document.getElementById('data'+id);
  input.value='';
}
function showLek ()
{
  var c = 1;
  var text = '';
  while ((c<13) && (input = document.getElementById("data"+c)))
  {
      if (input.value != '')
      {
          if (c==5) text+=' по';
          text+=' '+input.value;
      }
      c++;
  }
  document.getElementById('Lek').value=text;
}
function showCourse ()
{
  var c = 13;
  var text = '';
  while (input = document.getElementById("data"+c))
  {
      if (input.value != '') text+=input.value;
      c++;
  }
  document.getElementById('Course').value=text;
}
// ----------------------------------------------------
function Add (table, id)
{
  var value = document.getElementById('data'+id).value;
  if (value.length==0) return;
  /*
  var sel = document.getElementById('T'+id);
  sel.options[sel.length]=new Option (value,value);
  sel.style.display="none";
  sel.style.display="inline";
  */
  window.open("lek_add.php?table="+table+"&value="+value,"","");
}
// ----------------------------------------------------
function selectT0 (sel) // Шаг 3 - выбор дозировки
{
  if (sel.selectedIndex == 0) { On('S1'); On('S2'); return; }
  Off('S1');
  Off('S2');
  Clear(1);
  Clear(2);
  showLek();
}
// ----------------------------------------------------
function selectT1 (sel) // концентрация раствора
{
  var value = sel.options[sel.selectedIndex].value;
  if (value == '') On ('i1');
  else Set(1,value);
  showLek();
}
// ----------------------------------------------------
function selectT2 (sel) // дозировка препарата
{
  var value = sel.options[sel.selectedIndex].value;
  if (value == '') On ('i2');
  else Set(2,value);
  showLek();
}
// ----------------------------------------------------
function selectTa (sel) // указание на ампулы
{
  if (sel.selectedIndex == 1) Clear (3);
  else Set(3,'in ampullis');
  showLek();
}
// ----------------------------------------------------
function selectT4 (sel) // размер однократной дозы
{
  var value = sel.options[sel.selectedIndex].value;
  if (value == '') On ('i5');
  else Set(5,value);
  selectTs(document.getElementById('Ts')); // корректировка суффикса
  showLek();
}
// ----------------------------------------------------
function selectTs (sel) // суффикс дозы
{
  if (sel.selectedIndex==-1) return;
  if (sel.options[sel.selectedIndex].value == '') { On ('i6'); return; }
  var suffix = parseInt(sel.options[sel.selectedIndex].value);
  var doze = parseInt(document.getElementById('data5').value);
  // проверим, не является ли value дробным числом
  var str = new String (document.getElementById('data5').value);
  if (str.indexOf('/')>0 || str.indexOf(',')>0) doze=2;
  if (isNaN(doze) || doze<=0) return; // не обрабатываем, пока не будет введено число
  var value=sel.options[sel.selectedIndex].text;
  switch (suffix)
  {
      case 0: // капель
           if (doze==1) value="капле";
           else if (doze<5) value="капли";
           else value="капель";
           break;
      case 3: // порошков
           if (doze==1) value="порошку";
           else if (doze<5) value="порошка";
           else value="порошков";
           break;
      case 5: // капсулы
           if (doze==1) value="капсуле";
           else if (doze<5) value="капсулы";
           else value="капсул";
           break;
      case 7: // свечей
           if (doze==1) value="свече";
           else if (doze<5) value="свечи";
           else value="свечей";
           break;
      case 8: // доз
           if (doze==1) value="дозе";
           else if (doze<5) value="дозы";
           else value="доз";
           break;
  }
  Set(6,value);
  showLek();
}
// ----------------------------------------------------
function selectT5 (sel) // метод применения
{
  Clear(7);
  // получим индекс и значение метода применения
  var text = sel.options[sel.selectedIndex].text;
  var value = parseInt(sel.options[sel.selectedIndex].value,10);
  if (value==14) // не указывать
  {
      Clear(4);
      Clear(6);
      Clear(7);
      Off('S6');
      Off('S7');
      showLek();
      return;
  }
  Set(4,text);
  switch (value)
  {
      case 1: case 2: case 3: case 4: case 11: case 13: // парентерально или внутрь
           On('S4');
           On('S5');
           Off('S6');
           Off('S7');
           Clear(7);
           break;
      case 5: case 6: case 7: // местно
           Off('S7');
           Off('S4');
           Off('S5');
           On('S6');
           Clear(5);
           Clear(6);
           break;
      case 9: case 10: case 12: // капли (в нос и пр.)
           On('S4');
           On('S5');
           Off('S6');
           On('S7');
           Clear(5);
           Clear(6);
           break;
      case 8: // ингаляторно
           Off('S4');
           Off('S5');
           Off('S6');
           Off('S7');
           Clear(5);
           Clear(6);
           Clear(7);
           break;
  }
  if (value==11)
  {
      Set(7,'под язык');
      Clear(4);
  }
  showLek();
}
// ----------------------------------------------------
function selectT7 (sel) // место применения местных препаратов
{
  var value = sel.options[sel.selectedIndex].value;
  if (value == '') On ('i7');
  else Set(7,value);
  showLek();
}
// ----------------------------------------------------
function selectTlr (sel) // место закапывания
{
  var index = sel.selectedIndex;
  var T5 = document.getElementById('T5');
  var index2 = T5.options[T5.selectedIndex].value;
  var text = 'error';
  switch (index2)
  {
      case '9': // в нос
           if (index == 0) text="в левую ноздрю";
           else if (index == 1) text="в правую ноздрю";
           else text="в ноздри";
           break;
      case '10': // в глаза
           if (index == 0) text="в левый глаз";
           else if (index == 1) text="в правый глаз";
           else text="в оба глаза";
           break;
      case '12': // в ухо
           if (index == 0) text="в левое ухо";
           else if (index == 1) text="в правое ухо";
           else text="в оба уха";
           break;
  }
  Set(7,text);
  showLek();
}
// ----------------------------------------------------
function selectTk (sel) // кратность приёма
{
  var index = sel.selectedIndex;
  Set(10,sel.options[index].value);
  if (index < 2) // нужно указать, сколько раз
  {
      On('S9');
  }
  else
  {
      Off('S9');
      Clear(8);
      Clear(9);
  }
  showLek();
}
// ----------------------------------------------------
function selectTv (sel) // кратность приёма
{
  var value = sel.options[sel.selectedIndex].value;
  if (value == '') On ('i8');
  else
  {
      Set(8,value);
      changeIv();
  }
  showLek();
}
// ----------------------------------------------------
function changeIv () // корректировка "раз"
{
  var value = parseInt(document.getElementById('data8').value);
  if (isNaN(value)) return;
  if (value==1) Set(9,'раз');
  else if (value<5) Set(9,'раза');
  else Set(9,'раз');
  showLek();
}
// ----------------------------------------------------
function selectT9 (sel) // как принимать
{
  var index = sel.selectedIndex;
  var value = sel.options[index].value;
  if (value == '') { On ('i11'); return; }
  Set(11,value);
  if (index==0) // спец. схема
  {
     Off('S11');
     Clear(12);
  }
  else On('S11');
  showLek();
}
// ----------------------------------------------------
function selectT10 (sel) // когда принимать
{
  var value = sel.options[sel.selectedIndex].value;
  if (value == '') On ('i12');
  else Set(12,value);
  showLek();
}
// ----------------------------------------------------
function selectT11 (sel) // ввод данных о курсе лечения
{
  if (sel.selectedIndex == 0)
  {
      On('S12');
  }
  else
  {
    Off('S12');
    Clear(13);
    Clear(14);
    Clear(15);
    Clear(16);
    Clear(17);
    Clear(18);
    Clear(19);
    Clear(20);
  }
  showCourse();
}
function selectT12 (sel)
{
  var value = sel.value;
  if (value=='')
  {
      document.getElementById('data13').value='';
      document.getElementById('data15').value='';
      showLek();
      return;
  }
  var val=parseInt(value);
  document.getElementById('data13').value='Курс лечения: ';
  var text=' дней';
  if (val==1) text=' день';
  else if (val<5) text=' дня';
  document.getElementById('data15').value=text;
  showCourse();
}
function selectT13 (sel)
{
  var value = sel.value;
  if (value=='')
  {
      document.getElementById('data16').value='';
      showLek();
      return;
  }
  document.getElementById('data16').value=', количество курсов: ';
  showCourse();
}
function selectT14 (sel)
{
  var value = sel.value;
  if (value='')
  {
      document.getElementById('data18').value='';
      document.getElementById('data20').value='';
      showLek();
      return;
  }
  document.getElementById('data18').value=', интервал между курсами: ';
  var val=parseInt(value);
  var text=' дней';
  if (val==1) text=' день';
  else if (val<5) text=' дня';
  document.getElementById('data20').value=text;
  showCourse();
}
// ----------------------------------------------------
</script>
<style>
table.list tr td { padding: 5px; }
</style>
<table border="0" cellpadding="0" cellspacing="20" width="100%"><col width="250"><col>
<tr valign="top"><td align="left" class="nav" width="260">
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
// Информация по аллергическим реакциям
//
$res = $db->query('select allergies.all_id, lek_names.rname from allergies, lek_names where allergies.pat_id='.$pat_id.' and allergies.lek_id=lek_names.lek_id');
if ($res && $res->num_rows)
{
  print ('<p><b>Внимание!</b> Имеются сведения об аллергических реакциях на следующие препараты:<ul>');
  while ($row = $res->fetch_row()) print ("<li>{$row[1]}</li>\n");
  $res->free();
  print ('</ul><a href="allergy.php?pat_id='.$pat_id.'" target="_blank">Подробнее...</a></p>');
}
print ('<p><table class="list" cellpadding="3"><tr><th>Дата</th><th>Код</th><th>Назначение</th></tr>');
//
// Читаем список назначений из базы данных
//
$res=$db->query('select * from leks where pat_id='.$pat_id.' and unset_date is null order by set_date desc');
if ($res && $res->num_rows)
{
  while ($row = $res->fetch_object())
  {
      $dat=explode('-',$row->set_date);
      print ('<tr><td>'.$dat[2].'.'.$dat[1].'.'.$dat[0].'</td>');
      print ('<td>'.$row->lek_id.'</td>');
      print ('<td>'.$row->lek.'&nbsp;[<a href="lek.php?delete='.$row->id.'" onclick="return confirm(\'Хотите отменить назначение?\')">Отменить</a>]');
      print ('&nbsp;[<a href="allergy_add.php?lek_id='.$row->lek_id.'" target="_blank" onclick="return confirm(\'Хотите отметить проявление аллергии?\')">Аллергия!</a>]</td></tr>'."\n");
  }
  print ('</table></p>');
  $res->free();
}
else print ('</table></p><p>Текущих назначений нет.</p>');

//
// 1. Выбор препарата из списка
//
if (!isset($_GET['lek_id'])) // название препарата не выбрано
{
  print ('<h2>Выбор названия препарата <span style="font-size: 10pt">(<a href="lek_new.php" target="_blank">добавить новое наименование</a>)</span></h2>');
  print ('<p><table border="0"><tr valign="top" align="center">');
  if (!isset($_GET['letter'])) // не выбрана первая буква, выводим их список для выбора
  {
      print ('<td>Выберите первую букву русского названия препарата:<br>');
      // выводим только первые буквы названий
      $res=$db->query('select rname from lek_names order by rname');
      if (!$res || !$res->num_rows) die ('База данных названий лекарств пуста или недоступна! Ошибка: '.$db->error);
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
           print ('<input type="button" value="'.$letter.'" onclick="javascript:document.location=\'lek.php?letter='.$letter.'\'"/> ');
      }
  }
  else // Передана первая буква, вывести список лекарств на эту букву
  {
      print ('<td width="200">Первая буква:<br><b>'.$_GET['letter'].'</b><br>(<a href="lek.php">выбрать другую</a>)</td>');
      // получаем из базы все лекарства на эту букву
      $res=$db->query('select * from lek_names where rname like "'.$_GET['letter'].'%" order by rname');
      if (!$res || !$res->num_rows) die ('База данных названий лекарств не содержит названий на букву'.$_GET['letter'].'! Ошибка: '.$db->error);
      $size=$res->num_rows;
      if ($size<2) $size=2;
      print ('<td width="300">Препараты:<br><select size="'.$size.'" onchange="javascript:document.location=\'lek.php?lek_id=\'+this.options[this.selectedIndex].value">');
      while ($row = $res->fetch_object())
      {
          print ("\n<option value='{$row->lek_id}'>$row->rname</option>");
      }
      $res->free();
  }
  print ('</td></tr></table></p>');
  print ('<p><input type="button" value="Далее >>" onclick="document.location=\'osmotr.php?page='.($osm_page+1).'\'"/></p>');
  include('footer.inc');
  exit;
}
//
// Препарат выбран, вывод формы
//
$lek_id = $_GET['lek_id'];
print ('<h2>Ввод полного предписания</h2>');
// читаем название препарата
$res = $db->query ('select * from lek_names where lek_id='.$lek_id);
if (!$res) die ('<p>Название препарата не найдено! Ошибка: '.$db->error.'</p>');
$row=$res->fetch_object();
$lek_name=$row->rname;
$res->free();
//
  print ('<p><table border="0" cellspacing="10"><tr valign="top" align="center">');
  print ('<td width="200">Первая буква:<br><b>'.$lek_name{0}.'</b><br>(<a href="lek.php">выбрать другую</a>)</td>');
  print ('<td width="300">Препарат:<br><b>'.$lek_name.'</b><br>(<a href="lek.php?letter='.$lek_name{0}.'">выбрать другой на ту же букву</a>)</td>');
//
// 2. Выбор формы выпуска
//
if (!isset($_GET['form']))
{
  $res = $db->query ('select * from lek_forms');
  if (!$res) die ('<p>Не найдены данные о формах выпуска! Ошибка: '.$db->error.'</p>');
  $size=$res->num_rows;
  if ($size<2) $size=2;
  print ('<td width="300">Формы выпуска:<br><select size="'.$size.'" onchange="javascript:document.location=\'lek.php?lek_id='.$lek_id.'&form=\'+this.options[this.selectedIndex].value">');
  while ($row = $res->fetch_object()) print ("\n<option value='{$row->form_id}'>$row->rname</option>");
  $res->free();
  print ('</td></tr></table></p>');
  print ('<p><input type="button" value="Далее >>" onclick="document.location=\'osmotr.php?page='.($osm_page+1).'\'"/></p>');
  include('footer.inc');
  exit;
}
// получим название формы выпуска
$form_id=$_GET['form'];
if ($form_id==19) // прочие
{
  $form_name='';
}
else
{
  $res = $db->query('select * from lek_forms where form_id='.$form_id);
  if (!$res) die ('<p>Не найдены данные о форме выпуска! Ошибка: '.$db->error.'</p>');
  $row=$res->fetch_object();
  $form_name=$row->rname;
  $res->free();
}
print ('<td width="200">Форма выпуска:<br><b>'.$form_name.'</b><br>(<a href="lek.php?lek_id='.$lek_id.'">выбрать другую</a>)</td>');
?>
<td>Препарат в ампулах?<br>
<select id="Ta" size="2" onchange="javascript:selectTa(this)">
<option value="in ampullis">Да</option>
<option value="" selected>Нет</option>
</select>
<input type="hidden" id="data3" value=""/>
</td>
<?php
if ($form_id == 16 || $form_id == 4 || $form_id == 3 || $form_id == 11) // растворы, таблетки, капсулы или драже
{
?>
<td>Указать дозировку?<br>
<select id="T0" size="2" onchange="javascript:selectT0(this)">
<option value="1">Да</option>
<option value="2" selected>Нет</option>
</select>
</td>
</tr>
</table>
</p>
<?php
}
// ----------------------------------------
// Вывод полной формы ввода предписания
// ----------------------------------------
print ('<p><table cellpadding="0" cellspacing="10"><tr valign="top" align="center">');
//
// 3. Ввод дозировки (если нужно)
//
// для раствора нужна концентрация
print ('<td id="S1" style="display: none">');
if ($form_id==11)
{
  $res = $db->query('select * from lek_data where tab_id=1'); // таблица 1
  if (!$res || $res->num_rows!=1) die ('<p>Не найдена таблица 1! Ошибка: '.$db->error.'</p>');
  $row=$res->fetch_object();
  $opts = explode (';',$row->list);
  print ($row->tab_name.':<br><select size="10" id="T1" onchange="javascript:selectT1(this)">');
  foreach ($opts as $opt)
  {
      print ("\n<option value='$opt'>$opt</option>");
  }
  $res->free();
  print ('<option value="">(другая)</option></select>');
}
print ('<br><span id="i1" style="display: none"><input type="text" id="data1" size="6" value="" onChange="javascript:showLek()"/>');
print ('<img src="img/plus.png" onClick="Add(1,1)"></span></td>'."\n");
// дозировка для некоторых препаратов
print ('<td id="S2" style="display: none">');
$res = $db->query('select * from lek_data where tab_id=2'); // таблица 2 - дозировка
if (!$res || $res->num_rows!=1) die ('<p>Не найдена таблица 2! Ошибка: '.$db->error.'</p>');
$row=$res->fetch_object();
$opts = explode (';',$row->list);
print ($row->tab_name.':<br><select size="10" id="T2" onchange="javascript:selectT2(this)">');
foreach ($opts as $opt)
{
    print ("\n<option value='$opt'>$opt</option>");
}
$res->free();
print ('<option value="">(другая)</option></select>');
print ('<br><span id="i2"  style="display: none"><input type="text" id="data2" size="11" value="" onChange="javascript:showLek()"/>');
print ('<img src="img/plus.png" onClick="javascript:Add(2,2)"></span></td>'."\n");
//
// 7. Метод применения
//
?>
<td id="S3" style="display: inline">
Метод применения:<br>
<select id="T5" size="10" onchange="javascript:selectT5(this)">
<?php
if ($form_id == 11 || $form_id == 15 || $form_id == 17 || $form_id == 19) // пригодные для инъекций
   print ('<option value="1">в/венно</option><option value="2">в/мышечно</option><option value="3">п/кожно</option><option value="4">в/кожно</option>');
if ($form_id == 8 || $form_id == 11 || $form_id == 10 || $form_id == 14 || $form_id == 15 || $form_id == 17 || $form_id == 18 || $form_id == 19) // жидкие препараты
   print ('<option value="5">электрофорез на область</option><option value="6">фонофорез на область</option><option value="7">местно на область</option><option value="8">ингаляторно</option><option value="9">интраназально</option><option value="10">в глаза</option><option value="12">интрамеатально</option><option value="13">внутрь</option>');
if ($form_id == 1 || $form_id == 2 || $form_id == 5 || $form_id == 6 || $form_id == 7 || $form_id == 9) // мази и т.п.
   print ('<option value="7">местно на область</option>');
if ($form_id == 3 || $form_id == 4 || $form_id == 16) // таблетки и т.п.
   print ('<option value="11">сублингвально</option><option value="13">внутрь</option>');
print ('<option value="14">(не указывать)</option></select>');
print ('<br><span id="i4" style="display: none"><input type="text" id="data4" value="" onChange="javascript:showLek()"/></span></td>');
//
// 5.1. Выбор общей дозы
//
print ('<td id="S4" style="display: inline">');
$res = $db->query ('select * from lek_data where tab_id=4'); // однократная доза
if (!$res || $res->num_rows!=1) die ('<p>Не найдена таблица 4! Ошибка: '.$db->error.'</p>');
$row=$res->fetch_object();
$opts = explode (';',$row->list);
print ($row->tab_name.':<br><select size="10" id="T4" onchange="javascript:selectT4(this)">');
foreach ($opts as $opt)
{
    print ("\n<option value='$opt'>$opt</option>");
}
$res->free();
print ('<option value="">(другая)</option></select>');
print ('<br><span id="i5" style="display: none"><input type="text" id="data5" size="7" value="" onChange="javascript:selectTs(document.getElementById(\'Ts\'));showLek()"/>');
print ('<img src="img/plus.png" onClick="javascript:Add(4,5)"></span></td>'."\n");
// суффиксы дозировок для однократных доз
print ('<td id="S5" style="display: inline">Единицы:<br><select size="10" id="Ts" onchange="javascript:selectTs(this)">');
if ($form_id == 8 || $form_id == 11 || $form_id == 14 || $form_id == 15 || $form_id == 17 || $form_id == 18 || $form_id == 19) // жидкие препараты
   print ('<option value="0">капель</option><option value="1">мл</option><option value="9">ЕД</option><option value="2">чайн. ложк.</option>');
if ($form_id == 10) // порошки
   print ('<option value="3">порошков</option>');
if ($form_id == 16) // таблетки
   print ('<option value="4">таблеток</option>');
if ($form_id == 4) // капсулы
   print ('<option value="5">капсул</option>');
if ($form_id == 3) // драже
   print ('<option value="6">драже</option>');
if ($form_id == 12 || $form_id == 13) // свечи
   print ('<option value="7">свечей</option>');
print ('<option value="8">доз</option>');
print ('<option value="">(другое)</option></select><br><span id="i6" style="display: none"><input type="text" id="data6" size="9" value="" onChange="javascript:showLek()"></span></td>');
//
// 5.2. Выбор места применения (для местных и -форезов)
//
print ('<td id="S6" style="display: none">');
$res = $db->query ('select * from lek_data where tab_id=7'); // место применения
if (!$res || $res->num_rows!=1) die ('<p>Не найдена таблица 7! Ошибка: '.$db->error.'</p>');
$row=$res->fetch_object();
$opts = explode (';',$row->list);
print ($row->tab_name.':<br><select size="10" id="T7" onchange="javascript:selectT7(this)">');
foreach ($opts as $opt)
{
    print ("\n<option value='$opt'>$opt</option>");
}
$res->free();
print ('<option value="">(другое)</option></select>');
print('<br><span id="i7" style="display: none"><input type="text" id="data7" size="9" value="" onChange="javascript:showLek()">');
print ('<img src="img/plus.png" onClick="Add(7,7)"></span></td>'."\n");
//
// 5.4. Выбор правой/левой ноздри (уха, глаза)
//
?>
<td id="S7" style="display: none">
Слева или справа:<br>
<select size="3" id="Tlr" onchange="javascript:selectTlr(this)">
<option value="">Левый (-ая, -ое)</option>
<option value="">Правый (-ая, -ое)</option>
<option value="">Оба</option>
</select></td>
<!--
//
// 6. Кратность приёма
//
-->
<td id="S9" style="display: inline">
Сколько раз:<br>
<select size="10" id="Tv" onchange="javascript:selectTv(this)">
<option value="1">1</option>
<option value="2">2</option>
<option value="3">3</option>
<option value="4">4</option>
<option value="5">5</option>
<option value="6">6</option>
<option value="7">7</option>
<option value="">(другое)</option>
</select>
<br>
<span id="i8" style="display: none"><input type="text" id="data8" size="7" value="" onChange="javascript:changeIv();showLek()"/></span>
</td>
<td id="S8" style="display: inline">
Кратность приёма:<br>
<select size="10" id="Tk" onchange="javascript:selectTk(this)">
<option value="в сутки">...раз в сутки</option>
<option value="в неделю">...раз в неделю</option>
<option value="через день">Через день</option>
<option value="однократно">Однократно</option>
</select><br>
<span id="i10" style="display: none"><input type="text" id="data10" value="" onChange="javascript:showLek()"/></span>
<input type="hidden" id="data9" value=""/>
</td>
<?php
//
// Схема приёма
//
print ('<td id="S10" style="display: inline">');
// как принимать
$res = $db->query ('select * from lek_data where tab_id=9');
if (!$res || $res->num_rows!=1) die ('<p>Не найдена таблица 9! Ошибка: '.$db->error.'</p>');
$row=$res->fetch_object();
$opts = explode (';',$row->list);
print ($row->tab_name.':<br><select size="10" id="T9" onchange="javascript:selectT9(this)"><option value="по специальной схеме">(по специальной схеме)</option>');
foreach ($opts as $opt)
{
    print ("\n<option value='$opt'>$opt</option>");
}
$res->free();
print ('<option value="">(другая)</option></select>');
print ('<br><span id="i11" style="display: none"><input type="text" id="data11" size="33" value="" onChange="javascript:showLek()"/>');
print ('<img src="img/plus.png" onClick="Add(9,11)"></span></td>'."\n");
// когда принимать
print ('<td id="S11" style="display: inline">');
$res = $db->query ('select * from lek_data where tab_id=10');
if (!$res || $res->num_rows!=1) die ('<p>Не найдена таблица 10! Ошибка: '.$db->error.'</p>');
$row=$res->fetch_object();
$opts = explode (';',$row->list);
print ($row->tab_name.':<br><select size="10" id="T10" onchange="javascript:selectT10(this)">');
foreach ($opts as $opt)
{
    print ("\n<option value='$opt'>$opt</option>");
}
$res->free();
print ('<option value="">(другое)</option></select>');
print ('<br><span id="i12" style="display: none"><input type="text" id="data12" size="28" value="" onChange="javascript:showLek()"/>');
print ('<img src="img/plus.png" onClick="Add(10,12)"></span></td>'."\n");
//
// Информация о курсе приёма
//
?>
</tr></table>
<table cellspacing="10" cellpadding="0" border="0">
<tr valign="top" align="center">
<td>Указать данные о курсе приёма?<br>
<select id="T11" size="2" onchange="javascript:selectT11(this)">
<option value="1">Да</option>
<option value="2" selected>Нет</option>
</select>
</td>
<td id="S12" style="display: none">
<!--
<select size="10" id="Tk" onchange="javascript:selectTk(this)">
<option value="в сутки">Несколько раз в сутки</option>
<option value="в неделю">Несколько раз в неделю</option>
<option value="через день">Через день</option>
<option value="однократно">Однократно</option>
</select><br>

<span id="i10" style="display: none">
-->
<input type="hidden" id="data13" value=""/><!-- "Курс лечения" -->
Курс лечения:&nbsp;<input type="text" id="data14" size="2" value="" onChange="javascript:selectT12(this)"/>&nbsp;дней.
<input type="hidden" id="data15" value=""/><!-- слово "дней" -->

<!--
<select size="10" id="Tk" onchange="javascript:selectTk(this)">
<option value="в сутки">Несколько раз в сутки</option>
<option value="в неделю">Несколько раз в неделю</option>
<option value="через день">Через день</option>
<option value="однократно">Однократно</option>
</select><br>

<span id="i10" style="display: none">
-->
<input type="hidden" id="data16" value=""/><!-- "Количество курсов" -->
Количество курсов:&nbsp;<input type="text" id="data17" size="2" value="" onChange="javascript:selectT13(this)"/>.

<!--
<select size="10" id="Tk" onchange="javascript:selectTk(this)">
<option value="в сутки">Несколько раз в сутки</option>
<option value="в неделю">Несколько раз в неделю</option>
<option value="через день">Через день</option>
<option value="однократно">Однократно</option>
</select><br>

<span id="i10" style="display: none">
-->
<input type="hidden" id="data18" value=""/><!-- "интервал между курсами" -->
Интервал между курсами:&nbsp;<input type="text" id="data19" size="2" value="" onChange="javascript:selectT14(this)"/>&nbsp;дней.
<input type="hidden" id="data20" value=""/><!-- слово "дней" -->
</td>
</tr></table></p>
<p>Текст предписания: <b>
<?php
//
// Полное предписание
//
print ('<form method="post"><input type="hidden" name="lek_name" value="'.$lek_name.'"><input type="hidden" name="form" value="'.$form_name.'">');
print ($form_name);
print (' '.$lek_name.' ');
print ('<input type="text" id="Lek" name="Lek" size="100" value=""/></p>');
print ('<p>Курс приёма: <input type="text" id="Course" name="Course" size="80" value=""/></p>');
print ('<p><input type="submit" value="Добавить"/></p></form>');
print ('<p><input type="button" value="Далее >>" onclick="document.location=\'osmotr.php?page='.($osm_page+1).'\'"/></p>');
include ('footer.inc');
?>