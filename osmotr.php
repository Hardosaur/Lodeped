<?php
//
// OSMOTR.PHP
// Вывод заданной страницы формы осмотра.
// Номер страницы хранится в сессии или берётся по умолчанию (1).
// Номер формы (тип осмотра) передается явно или подчитывается из базы, если передан id.
// Поля шаблона хранятся в базе данных. Поля-списки могут быть пополнены из интерфейса.
// При необходимости создается пустой протокол осмотра или создается копия предыдущего (если id не передан явно).
//
require('../settings.php');
require('auth.php');
include('connect.inc');
//------------------------------------------------------------------------------
// Выполняем сохранение данных и переход на другую страницу, если переданы данные из формы
//
if (isset($_POST['next']))
{
  if (!isset($_SESSION['pat_id']) || !isset($_SESSION['doctor_id']) || !isset($_SESSION['osm_id']) || !isset($_SESSION['osm_type']) || !isset($_SESSION['date']))
     die ('Не найдены параметры осмотра! (Ошибка работы механизма сессий?)');
  $pat_id=$_SESSION['pat_id'];
  $osm_id=$_SESSION['osm_id'];
  $osm_type=$_SESSION['osm_type'];
  $date=$_SESSION['date'];
  $values=array(); // ассоциативный массив значений переменных
  //
  // Прочитаем данные, уже имеющиеся в базе данных (даже пустые)
  //
  $res=$db->query('select data from osm_data where osm_id='.$osm_id);
  if (!$res || !$res->num_rows) die ('Нет данных осмотра в базе!');
  $row=$res->fetch_row();
  if (strlen($row[0]))
  {
      $pairs = explode ($delim,$row[0]); // получаем пары "имя = значение"
//      print_r($pairs);
      foreach ($pairs as $pair)
      {
          list ($id, $value) = explode ('=',$pair);
          $values[$id]=$value;
      }
  }
  $res->free();
  // Сформируем значение даты
  if (isset($_POST['Date3'])) $newdate = sprintf("%4d-%02d-%02d",$_POST['Date3'],$_POST['Date2'],$_POST['Date1']);
  if (isset($newdate) && $newdate != $date)
  {   //print ($newdate);
      if (!$db->query('update osm_info set date="'.$newdate.'" where osm_id='.$osm_id)) print ($db->error);
      $_SESSION['date']=$newdate;
  }
  // просканируем входные данные, обновим $values
  reset($_POST);
  while (list($key,$value) = each($_POST))
  {
      if (is_numeric($key)) $values[$key]=$value;
  }
  $data='';
  foreach ($values as $key=>$value)
  {
       if (!strlen($value)) continue;
       if (strlen($data)) $data.=$delim.$key.'='.$value;
       else $data.=$key.'='.$value;
  }
  $data=$db->real_escape_string ($data); // для случаев с кавычками, точками и т.п.
//  print ('"'.$data.'"');
  //
  // Обновление информации об осмотре
  //
  $query = 'update osm_data set data="'.$data.'" where osm_id='.$osm_id;
  if (!$db->query($query)) die ('Обновление данных в базе не прошло! Ошибка: '.$db->error);

  if (strlen($_POST['script'])) header ('Location: '.$_POST['script'].'.php?page='.$_POST['next']);
  else header ('Location: osmotr.php?page='.$_POST['next']);
/*
  if (strlen($_POST['script'])) print ('<a href="'.$_POST['script'].'.php">Линк</a>');
  else print ('<a href="osmotr.php?page='.$_POST['next'].'">Линк</a>');
*/
  exit;
}
// -----------------------------------------------------------------------------
// Проверяем параметры для основной функциональности
//
$doctor_id=$_SESSION['doctor_id'];
if (!isset($_GET['page'])) // осмотр открывается первый раз, номер страницы не передан
{
  $osm_page=1;
  if (!isset($_GET['pat_id']) || !is_numeric($_GET['pat_id'])) die ('Нет требуемого параметра! (Запуск скрипта вручную?)');
  $pat_id=$_GET['pat_id'];
  $_SESSION['pat_id']=$pat_id;
  date_default_timezone_set ("Europe/Minsk"); // чтобы избежать сообщения о проблемах с получением неправильной даты
  $d=getdate(); // получение текущей даты
  $today=$d["mday"].'.'.$d["mon"].'.'.$d["year"];
  $date=$d["year"].'-'.$d["mon"].'-'.$d["mday"];
  //
  // Проверяем режим запуска: редактировать осмотр или создать новый
  //
  if (!isset($_GET['osm_id'])) // нужно создать новый осмотр
  {
      if (!isset($_GET['osm_type'])) die ('Не задан тип осмотра! (Должен быть передан в явном виде)');
      $osm_type=$_GET['osm_type'];
      $_SESSION['osm_type']=$osm_type;
      $osm_new=1; // флаг нового осмотра
      // поищем предыдущий осмотр, скопируем его в новый
      $data='';
      $res=$db->query('select osm_id, date from osm_info where pat_id='.$pat_id.' and osm_type='.$osm_type.' order by date desc limit 0,1'); // получаем последний подходящий по типу осмотр
      if (!$res || !$res->num_rows) $res=$db->query('select osm_id, date from osm_info where pat_id='.$pat_id.' order by date desc limit 0,1'); // получаем последний осмотр другого типа
      if ($res && $res->num_rows)
      {
          $row=$res->fetch_row();
          $osm_id=$row[0];
          $old_date=$row[1];
          $res->free();
          unset($row);
          $res=$db->query('select * from osm_data where osm_id='.$osm_id);
          if ($res && $res->num_rows==1)
          {
              $row=$res->fetch_row();
              $data=$row[1];
              unset($row);
              $res->free();
          }
          else die ('Ошибка в базе: не найдены данные осмотра #'.$osm_id);
       }
       $_SESSION['date']=$date;
       $query = "insert into osm_info values (NULL, $osm_type, $pat_id, $doctor_id, \"$date\", \"\")";
       if (!$db->query($query)) die ('Создание нового осмотра не прошло! Ошибка: '.$db->error);
       $res=$db->query('select LAST_INSERT_ID() from osm_info'); // выясним номер записи
       if (!$res || !$res->num_rows) die ('Ошибка: '.$db->error);
       $row=$res->fetch_row();
       $osm_id=$row[0];
       $_SESSION['osm_id']=$osm_id;
       $res->free();
       // запись в базу нового протокола осмотра (возможно, копии предыдущего)
       $query = "insert into osm_data values ($osm_id,\"$data\")";
       if (!$db->query($query))
       {
           print ('Добавление в базу не прошло! Ошибка: '.$db->error);
           $db->query('delete from osm_info where osm_id = '.$osm_id);
           exit;
       }
  }
  else // передан номер существующего осмотра, нужно получить о нем все данные
  {
      $osm_id=$_GET['osm_id'];
      $_SESSION['osm_id']=$osm_id;
      $res=$db->query('select * from osm_info where osm_id='.$osm_id);
      if (!$res || !$res->num_rows) die ('Осмотр с ID '.$osm_id.' не существует!');
      $row=$res->fetch_object();
      if ($pat_id != $row->pat_id) die ('Ошибка: переданный ID пацента не соответствует ID в базе!');
      $date=$row->date;
      $_SESSION['date']=$date;
      $osm_type=$row->osm_type;
      $_SESSION['osm_type']=$osm_type;
      $res->free();
  }
}
else // осмотр уже существует, так как передан номер страницы
{
  if (!isset($_SESSION['pat_id']) || !isset($_SESSION['doctor_id']) || !isset($_SESSION['osm_id']) || !isset($_SESSION['osm_type']) || !isset($_SESSION['date']))
     die ('Не найдены параметры осмотра! (Ошибка работы механизма сессий?)');
  $pat_id=$_SESSION['pat_id'];
  $osm_id=$_SESSION['osm_id'];
  $osm_type=$_SESSION['osm_type'];
  $date=$_SESSION['date'];
  $osm_page=$_GET['page'];
}
//
// Прочитаем уже имеющиеся данные осмотра
//
if (!isset($data)) // возможно, данные уже были получены, если был создан новый осмотр
{
  $res=$db->query('select data from osm_data where osm_id='.$osm_id);
  if (!$res || !$res->num_rows) die ('Неверный ID протокола осмотра ('.$osm_id.')! Нет такого в базе!');
  $row = $res->fetch_row();
  $data = $row[0];
  $res->free();
}
//
// Сформируем ассоциативный массив значений полей
//
$values=array(); // ассоциативный массив значений полей
if (strlen($data)) // данных может и не быть
{
//  print_r ($data);
  $vals = explode ($delim,$data); // получаем пары "имя = значение"
  foreach ($vals as $pair)
  {
    list ($id, $value) = explode ('=',$pair);
//    print ("<br>$id=$value");
    $values[$id]=$value;
  }
  // автовычисляемые поля - прирост веса и роста
  if (isset($osm_new) && isset($old_date)) // копируем старый вес и рост
  {
     $values[6]=$old_date;
     if (isset($values[7])) $values[9]=$values[7]; else $values[9]=0;
     if (isset($values[10])) $values[12]=$values[10]; else $values[12]=0;
     $values[8]=$values[11]=0;
  }
}
//
// Прочитаем название осмотра
//
$res=$db->query('select description from osm_types where osm_type='.$osm_type);
if (!$res || !$res->num_rows) die ('Не найдено название осмотра (тип '.$osm_type.')!');
$row = $res->fetch_row();
$page_title = $row[0].' (Стр. '.$osm_page.')';
$res->free();
include ('osm_header.inc');
//
// Узнаем ФИО пациента (для наглядности)
//
$res=$db->query('select surname, name, lastname from patients where pat_id='.$pat_id);
if (!$res || !$res->num_rows) die ('Не найдены личные данные пациента! (Неверный код пациента?');
$row=$res->fetch_row();
$patient=$row[0].' '.$row[1].' '.$row[2];
$res->free();
//
// Страница отформатирована табличным методом, что гарантирует максимальную совместимость и контроль за расположением элементов
//
print ('<table border="0" cellpadding="0" cellspacing="20" width="100%"><tr valign="top">'); // деление страницы на две части - навигационную и основную
// навигационная часть
print ('<td class="nav" align="left" width="260">');
include('osm_pages.inc');
//
// Оформим основную часть страницы
//
print ('</td><td align="left">');
print ('<form method="post"><input type="hidden" name="next" value="'.$nxpage.'"/><input type="hidden" name="script" value="'.$nxscript.'"/>'."\n");
print ("<p>Пациент: $patient</p>\n");
if ($osm_page==1) // особый случай, нужно вывести дату
{
  $dat=explode('-',$date);
  print ("<p>Дата осмотра : <input name='Date1' id='Date1' size='2' maxlength='2' value='{$dat[2]}'>&nbsp;/&nbsp;\n".
        "<input name='Date2' id='Date2' size='2' maxlength='2' value='{$dat[1]}'>&nbsp;/&nbsp;\n".
        "<input name='Date3' id='Date3' size='4' maxlength='4' value='{$dat[0]}'>\n");
  if (isset($today)) print ('(Сегодня: '.$today.')');
}
//
// Читаем набор полей для шаблона и выводим в соответствии с форматом
//
if (!$nxid) // текущая страница последняя
   $query = 'select * from osm_template where osm_type='.$osm_type.' and id > '.$page_id;
else $query = 'select * from osm_template where osm_type='.$osm_type.' and (id > '.$page_id.' and id < '.$nxid.')';
$res=$db->query ($query);
if (!$res || !$res->num_rows) die ('Не найдено описание страницы '.$page_id.' в шаблоне типа '.$osm_type.' в базе данных!');
$fields = array(); // массив объектов-описаний полей
$sections = array(); // массив описаний секций (перечень входящих элементов)
while ($row = $res->fetch_object()) // формируем массив объектов
{
  $fields[$row->id] = $row; // сохраним
  $id=$row->id;
  $parent = $row->parent_id;
  if ($parent && $parent>$id) die ('Ошибка в описании шаблона! Элемент '.$row->id.': parent > id!');
  while ($parent)
  {
      $sections[$parent][]=$id; // сохраним обратную связь (по id) секции со вложенными элементами
      $fields[$parent]->value=1;
      $parent=$fields[$parent]->parent_id; // проверим, входит ли секция в другую секцию, и вызовем добавление id итеративно
  }
}
$res->free();
// Анализ типов и вывод элементов интерфейса
$stack = array (); // стек вложенности секций
$div_opened = 0; // признак открытия блока, необходим для объединения элементов, не входящих ни в одну секцию
$indent=-20; // отступ для текущей секции
foreach ($fields as $field)
{
  $id=$field->id;
  while (count($stack) && !in_array($id, $sections[end($stack)])) // закрываем секцию, т.к. новый элемент в нее уже не входит
  {
      if ($div_opened)
      {
          print ("</tr></table>\n<!----------------------------->\n");
          $div_opened=0;
      }
      print ('</div>');
      $indent-=20;
      array_pop ($stack);
  }
  if (!$div_opened && $field->type!='section')
  {
      print ('<table border="0" cellspacing="10">'."\n");
      $div_opened=1;
  }
  // ----------------------------------------------
  // Специальные поля, идентифицируются по ID
  if ($id == 7) // "Вес"
  {
     print ('<tr valign="top"><td align="right">');
     if (isset($values[6])) print ('<input type="hidden" name="6" value="'.$values[6].'">'); // дата предыдущего осмотра в формате MySQL
     if (isset($values[7])) $value=$values[7]; else $value=$field->value;
     if ($field->size) $size=$field->size; else $size=6;
     print ("$field->name:</td><td><input class='input' type='text' value='$value' size='$size' maxlength='$size' name='$id' id='$id' onblur='CalcDelta1()'/>&nbsp;");
     if ($field->suffix) print ($field->suffix);
     print ('&nbsp;<input style="border:0" type="text" name="8" id="8" size="20" readonly value="');
     if (isset($values[8]) && $values[8]) print ($values[8]);
     print ('">');
     if (isset($values[9]) && $values[9]) print ('<input type="hidden" name="9" value="'.$values[9].'">');
     print ('</td></tr>');
     continue;
  }
  if ($id == 10) // "Рост"
  {
     print ('<tr valign="top"><td align="right">');
     if (isset($values[10])) $value=$values[10]; else $value=$field->value;
     if ($field->size) $size=$field->size; else $size=6;
     print ("$field->name:</td><td><input class='input' type='text' value='$value' size='$size' maxlength='$size' name='$id' id='$id' onblur='CalcDelta2()'/>&nbsp;");
     if ($field->suffix) print ($field->suffix);
     print ('&nbsp;<input style="border:0" type="text" name="11" id="11" size="20" readonly value="');
     if (isset($values[11]) && $values[11]) print ($values[11]);
     print ('">');
     if (isset($values[12]) && $values[12]) print ('<input type="hidden" name="12" value="'.$values[12].'">');
     print ('</td></tr>');
     continue;
  }
  if ($id == 25 ) // "Зубная формула"
  {
    $teeth=array();
    for ($i=11; $i<=88; $i++) if ($i%10) $teeth[$i]=''; // инициализируем массив значений
    for ($i=25; $i<=28; $i++)
    {
        if (isset($values[$i]))
        {
            $th = explode (' ',$values[$i]);
            foreach ($th as $val)
            {
                if ($val>50) $key=$val-40;
                else $key=$val;
                $teeth[$key]=$val;
            }
        }
        else $values[$i]='';
        print ('<input type="hidden" name="'.$i.'" value="'.$values[$i].'">');
    }
    print ('<table align="center" border="0" cellspacing="0"><tr><td style="border-bottom: solid 1px black; padding: 5px">&nbsp;');
    for ($i=8; $i>0; $i--) print ('<input style="width: 28px" type="button" id="tooth1'.$i.'" value="'.$teeth[10+$i].'" size="5" onclick="teethButton(1'.$i.',1,0)">&nbsp;');
    print ('</td><td style="border-bottom: solid 1px black; border-left: solid 1px black; padding: 5px">&nbsp;');
    for ($i=1; $i<9; $i++) print ('<input style="width: 28px" type="button" id="tooth2'.$i.'" value="'.$teeth[20+$i].'" size="5" onclick="teethButton(2'.$i.',2,1)">&nbsp;');
    print ('</td></tr><td style="padding: 5px">&nbsp;');
    for ($i=8; $i>0; $i--) print ('<input style="width: 28px" type="button" id="tooth3'.$i.'" value="'.$teeth[30+$i].'" size="5" onclick="teethButton(3'.$i.',3,0)">&nbsp;');
    print ('</td><td style="border-left: solid 1px black; padding: 5px">&nbsp;');
    for ($i=1; $i<9; $i++) print ('<input style="width: 28px" type="button" id="tooth4'.$i.'" value="'.$teeth[40+$i].'" size="5" onclick="teethButton(4'.$i.',4,1)">&nbsp;');
    print ('</td></tr></table>');
    continue;
  }
  // ----------------------------------------------
  switch ($field->type)
  {
      case 'section' : // секция, содержит вложенные элементы
           if ($div_opened)
           {
              print ("</tr></table>\n");
              $div_opened=0;
           }
           array_push($stack,$id);
           $indent+=20;
           $display='none';
           $secval='';
           foreach ($sections[$id] as $child)
           {
               if (isset($values[$child]))
               {
                   $display='block';
                   $secval=' ';
                   break;
               }
           }
           print ("<div class='section' style='margin-left: {$indent}px' onclick='javascript:showSection(\"$id\")'><input type='hidden' name='$id' value='$secval'/>$field->name&nbsp;");
           if ($display=='none') print ("<img id='img$id' src='img/down.png' class='arrow'/>"); else print ("<img id='img$id' src='img/up.png' class='arrow'/>");
           print ("</div>\n<div style='display: $display; margin-left: {$indent}px; margin-bottom: 5px; border: solid 1px #c3e6cd' id='id{$id}'>");
           break;
      case 'header': // заголовок секции
           print ("<div class='header'><h2>$field->name</h2></div>\n");
           break;
      case 'br': // перевод строки
           print ('</tr><tr>');
           break;
      case 'text': // текстовое поле ввода
           if (isset($values[$id])) $value=$values[$id]; else $value=$field->value;
           $slashval=addslashes($value);
           if ($field->size) $size=$field->size; else $size=40;
           print ("<tr valign='top'><td align='right'>$field->name:</td><td><input type='text' value='$slashval' size='$size' maxlength='100' name='$id' id='$id'/>&nbsp;");
           if ($field->suffix) print ($field->suffix);
           print("</td></tr>\n");
           break;
      case 'number': // текстовое поле ввода
           if (isset($values[$id])) $value=$values[$id]; else $value=$field->value;
           if ($field->size) $size=$field->size; else $size=6;
           print ("<tr valign='top'><td align='right'>$field->name:</td><td><input class='input' type='text' value='$value' size='$size' maxlength='$size' name='$id' id='$id'/>&nbsp;");
           if ($field->suffix) print ($field->suffix);
           print("</td></tr>\n");
           break;
      case 'check': // чек-бокс
           if (isset($values[$id])) {$value=$values[$id]; $checked='checked'; } else { $checked=''; $value=''; }
           $slashval=addslashes($value);
           print ("<tr valign='top'><td align='right'><input type='hidden' name='$id' id='in$id' value='$slashval'><input type='checkbox' style='border:none' $checked value='$field->name' id='chk$id' onclick='check($id)'/></td><td><label for='chk$id'>$field->name</label></td></tr>\n");
           break;
      case 'select': // выпадающий список, оформленный с помощью javascript
           if (isset($values[$id])) $val=$values[$id]; else $val='';
           if ($field->size) $size=$field->size; else $size=30;
           print ("<tr valign='top'><td align='right'>$field->name:</td><td><input class='input' type='text' value=\"$val\" size='$size' maxlength='100' id='in{$id}' name='$id'/>");
           if ($field->suffix) print ('&nbsp;'.$field->suffix);
           print ('<img id="img'.$id.'" src="img/down.png" class="button" align="middle" alt="Список вариантов" onclick=\'javascript:showMenu("'.$id.'")\' onmouseover="hoverArrow(\''.$id.'\')" onmouseout="unhoverArrow(\''.$id.'\')"/>');
           print ('<img src="img/plus.png" class="button" align="middle" alt="Сохранить в базе данных" onclick=\'javascript:Add('.$osm_type.','.$id.')\' onmouseover="this.src=\'img/plus2.png\'" onmouseout="this.src=\'img/plus.png\'"/>');
           print ('<img src="img/cross.png" class="button" align="middle" alt="Очистить поле" onclick="document.getElementById(\'in'.$id.'\').value=\'\'" onmouseover="this.src=\'img/cross2.png\'" onmouseout="this.src=\'img/cross.png\'"/>');
           $vals=explode(";",$field->value); // получим ряд значений
           if (count($vals)>15) $size=15; else $size=count($vals);
           print ("<br><span style='position:relative;'><select class='dropdown' style='display: none' size='$size' id='m{$id}' onchange='select($id)'>");
           foreach ($vals as $val)
           {
               $slashval=addslashes($val); // на всякий случай
               print ("<option value=\"$slashval\">$val</option>\n");
           }
           print ('</select></span></td></tr>');
           break;
      case 'multi': // список элементов с возможностью выбора нескольких из них
           if (isset($values[$id])) $val=$values[$id]; else $val='';
           print ("<tr valign='top'><td align='right'>$field->name:</td><td><input type='hidden' value='$val' id='in{$id}' name='$id'/>\n<ul class='multi'>");
           $lines = explode(';',$field->value);
           if (strlen($val))
           {
               $vals = explode(', ',$val);
               foreach ($lines as $line)
               {
                   if (in_array($line,$vals)) print ("<li style=\"list-style-image: url('checked.png')\" onclick=\"multi(this, 'in{$id}')\">$line</li>\n");
                   else print ("<li style=\"list-style-image: url('unchecked.png')\" onclick=\"multi(this, 'in{$id}')\">$line</li>\n");
               }
           }
           else
               foreach ($lines as $line)
               {
                   print ("<li style=\"list-style-image: url('unchecked.png')\" onclick=\"multi(this, 'in{$id}')\">$line</li>\n");
               }
           print ('</ul></td></tr>');
           break;
      case 'list': // обычный список типа select, небольшого размера и без возможности пополнения из интерфейса
           if (isset($values[$id])) { $val=$values[$id]; $slashval=addslashes($val); } else $val=$slashval='';
           $vals=explode(';',$field->value); // получим элементы списка
           $size=count($vals);
           print ("<tr valign='top'><td align='right'>$field->name:</td><td><input type='hidden' name='$id' id='$id' value='$slashval'><select class='input' size='$size' id='list$id' onChange='listSelect($id)'/>\n");
           foreach ($vals as $value)
           {
               $slashval=addslashes($value); // на всякий случай
//               print ('"'.$val.'"="'.$field->value.'"');
               if ($value == $val) $selected='selected'; else $selected='';
               print ("<option value=\"$slashval\" $selected>$value</option>\n");
           }
           print ('</select><img src="img/cross.png" class="button" align="top" alt="Очистить список" onclick="listClear('.$id.')" onmouseover="this.src=\'img/cross2.png\'" onmouseout="this.src=\'img/cross.png\'"/></td></tr>');
           break;
      case 'table': // таблица описания набора элементов
           print ("<tr valign='top'><td align='left'>$field->name:</td><td><input name='$id' id='in$id' size='140' type='text' value='");
           if (isset($values[$id])) print ($values[$id]);
           print ("'/><table border='0' cellspacing='0' cellpadding='2'>\n");
           $vals = explode (';*;',$field->value); // получим набор столбцов
           if (count($vals)<2) die ('Ошибка в поле '.$id.': недостаточно столбцов в таблице!');
           $pars = explode (';',array_shift($vals)); // получим строки
           if (count($pars)<2) die ('Ошибка в поле '.$id.': недостаточно строк в таблице!');
           $opts = array();
           foreach ($vals as $val) $opts[]=explode(';',$val); // получим набор списков для каждого столбца
           if (isset ($values[$id])) // получаем данные для отрисовки уже заполненной таблицы
           {
               $sels = explode ('; ', $values[$id]);
               $heads = array();
               foreach ($sels as  $key=>$value)
               {
                   list ($rh, $row) = explode (': ', $value);
                   $rows = explode (', ', $row);
//                   foreach ($rows as $k=>$v) $rows[$k]=rtrim($v, ', '); // лишняя запятая может встретиться
                   $sels[$key]=$rows; // $sels содержит строку таблицы в виде массива
                   $heads[$key]=$rh;
               }
           }
//           print_r ($sels);
           $c1=1;
           $marked = 0; // признак того, что строка описана в исходных данных
           foreach ($pars as $par) // выводим строки в таблице
           {
               print ("<tr><td id='$id-$c1' onclick='tableClear($id,$c1)'>$par</td>");
               reset($opts);
               $marked=0;
               if (isset ($values[$id])) for ($i=0; $i<count($heads); $i++)
                   if ($heads[$i] == $par)
                   {
                       $marked=1;
                       break;
                   }
               $c2=1;
               foreach ($opts as $opt)
               {
                   print ("<td><select id='$id-$c1-$c2' onchange='tableChange($id,$c1)' size='".count($opt)."'>\n");
                   foreach ($opt as $o)
                   {
                       if ($marked && (array_search ($o, $sels[$i])!==FALSE) ) print ("<option value='$o' selected>$o</option>\n");
                       else print ("<option value='$o'>$o</option>\n");
                   }
                   $c2++;
                   print ("</select>\n");
               }
               print ("</tr>\n");
               $c1++;
           }
           print ('</table></td></tr>');
           break;
      default: die ("Ошибка в полях шаблона! Поле $id:"); print_r ($field); break;
  }
}
if ($div_opened) print ('</table>');
while (count($stack)) // закрываем все открытые секции
{
  print ("</div>\n");
  array_pop ($stack);
}
print ('<p><input type="submit" value="Далее >>"></p></form>');
include ('footer.inc');
?>