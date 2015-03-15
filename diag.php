<?php
//
//  Программа управления диагнозами (с полным названием)
//  Диагнозы хранятся в базе данных
//  Версия 2.0
//
require ('../settings.php');
$WINDOW_TITLE = 'Диагнозы';
require ('header.inc');
require ('auth.php');
require ('connect.inc');
//
// Проверка переменных сессии - входные данные
//
if (!isset($_SESSION['osm_id']) || !isset($_SESSION['pat_id']) || !isset($_SESSION['doctor_id']) || !isset($_SESSION['date']))
{
  die ('Не установлены необходимые переменные в сессии! Запуск вручную?');
}
$date = $_SESSION['date'];
print ($date);
$osm_id = $_SESSION['osm_id'];
$pat_id = $_SESSION['pat_id'];
$doctor_id = $_SESSION['doctor_id'];
//
// Запрос на снятие диагноза
//
if (isset($_GET['unset']))
{
  $unset=$_GET['unset'];
  if (!is_numeric($unset)) die ('<p>Ошибка передачи id диагноза для снятия!</p>');
  if (!$db->query ("update diags set unset_date=\"$date\" where id=$unset")) print ('<p>Ошибка снятия диагноза: '.$db->error.'</p>');
}
//
// Запрос на отмену снятия диагноза
//
if (isset($_GET['ununset']))
{
  $unset=$_GET['ununset'];
  if (!is_numeric($unset)) die ('<p>Ошибка передачи id диагноза для отмены снятия!</p>');
  if (!$db->query ("update diags set unset_date=NULL where id=$unset")) print ('<p>Ошибка восстановления диагноза: '.$db->error.'</p>');
}

//
// Запрос на удаление диагноза
//
if (isset($_GET['delete']))
{
  $delete=$_GET['delete'];
  if (!is_numeric($delete)) die ('<p>Ошибка передачи id диагноза для удаления!</p>');
  $res = $db->query ('select set_date from diags where id='.$delete);
  if (!$res || !$res->num_rows) die ('<p>Не найден диагноз с заданным номером!</p>');
  $row=$res->fetch_row();
  $res->free();
  if ($row[0]!=$date) print ('<p>Невозможно удалить диагноз, установленный не в текущем осмотре!</p>');
  else if (!$db->query ('delete from diags where id='.$delete)) print ('<p>Ошибка удаления диагноза: '.$db->error.'</p>');
}
//
// Запрос на обновление текста диагноза
//
if (isset($_GET['edit']))
{
  $edit=$_GET['edit'];
  if (!is_numeric($edit)) print ('<p>Ошибка передачи id диагноза для редактирования!</p>');
  elseif (!isset($_POST['text']) || !strlen($_POST['text'])) print ('<p>Текст диагноза пуст или не передан!</p>');
  elseif (!$db->query ('update diags set diag = "'.$_POST['text'].'" where id='.$edit)) print ('<p>Текст диагноза обновить не удалось! Ошибка: '.$db->error);
}
//
// Добавляем новый диагноз или уточняем имеющийся
//
if (isset($_POST['diag']))
{
  if (!isset($_POST['diag_id'])) die ('<p>Не передан id диагноза!</p>');
  // Формируем полное название
  $diagnosis=$_POST['diag'];
  /* функция уточнения диагноза удалена
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
*/
          $query = "insert into diags values (NULL, $pat_id, $doctor_id, {$_POST['diag_id']}, \"$diagnosis\", \"$date\", NULL)";
          //print ($query);
          if (!$db->query ($query)) print ('<p style="color: red">Новый диагноз в базу не внесен! Ошибка: '.$db->error.'</p>');
/*
      }
  }*/
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
      if (elem.type=='select-one' && elem.selectedIndex>=0) // элемент типа select
         text+=' '+elem.options[elem.selectedIndex].value;
      if (elem.type=='select-multiple' && elem.selectedIndex>=0) // элемент типа multi-select
      {
          newline='';
          for (i=0; i<elem.options.length; i++)
          if (elem.options[i].selected)
             if (newline.length > 0) newline += ', ' + elem.options[i].value;
             else newline = elem.options[i].value;
          text+=' '+newline;
      }
      if ((elem.type=='text' || elem.type=='hidden') && elem.value) text+=' '+elem.value;
      cnt++;
  }
  if (text.charAt(0)==' ') text=text.substr(1);
  div.value=text;
}
// --------------------------------------------------------
function clickDiag (id)
{
  var diag_span=document.getElementById('diag'+id);
  var text_span=document.getElementById('text'+id);
  if (diag_span.style.display=='block')
  {
      diag_span.style.display='none';
      text_span.style.display='block';
  }
  else
  {
      diag_span.style.display='block';
      text_span.style.display='none';
  }
}
// --------------------------------------------------------
function add (token)
{
  var diag=document.getElementById('Diagnosis');
  var diagstr = new String (diag.value);
  if (diagstr.substr(diagstr.length-token.length,token.length)==token) diagstr=diagstr.slice(0,-(token.length))+'.';
  else
  {
      if (diagstr.charAt(diagstr.length-1)=='.') diagstr=diagstr.slice(0,-1)+token;
      else diagstr=diagstr+token;
  }
  diag.value=diagstr;
}

</script>
<style>
table.list tr td { padding: 5px; }
</style>
<?php
//
// Читаем список актуальных диагнозов из базы данных
//
$res = $db->query ("select id, diag_id, diag, set_date, unset_date from diags where pat_id=$pat_id and set_date <= \"$date\" and (unset_date is null or unset_date >= \"$date\") order by id desc");
if ($res && $res->num_rows)
{
  if (isset($id)) unset ($id);
  print ('<table class="list" cellpadding=3><tr><th>Дата постановки</th><th>Код</th><th>Полное название</th></tr>'."\n");
  while ($row = $res->fetch_object())
  {
      $id=$row->id;
      $set_date = join('.',array_reverse(explode ('-',$row->set_date)));
      if (isset($unset_date)) unset ($unset_date);
      if ($row->unset_date) $unset_date=join('.',array_reverse(explode ('-',$row->unset_date)));
      if (isset($unset_date)) print ('<tr style="color: gray">'); else print ('<tr>');
      print ('<td>'.$set_date.'</td><td>'.$row->diag_id.'</td>');
      if (isset($unset_date)) // уже был снят
      {
          print ('<td>'.$row->diag);
          if (strcmp($date,$row->unset_date)<0) print (' (снят в будущем осмотре) ');
          else print (' (снят, <a href="diag.php?ununset='.$row->id.'">вернуть</a>)');
      }
      else
      {
          print ('<td><span style="display: block" id="diag'.$id.'"><span onclick="javascript:clickDiag('.$id.')">'.$row->diag.'&nbsp;</span>(<a href="diag.php?unset='.$row->id.'">снять</a>');
          if ($row->set_date == $date) print (', <a href="diag.php?delete='.$row->id.'">удалить</a>'); // удалить можно только установленный в текущем сеансе
          print (')</span><span style="display: none" id="text'.$id.'"><form method="post" action="diag.php?edit='.$id.'" onsubmit="clickDiag('.$id.')"><input name="text" type="text" size="50" value="'.$row->diag.'"></form></span>');

      }
      print ("</td></tr>\n");
  }
  print ('</table></p>');
}
else print ('</p><p style="font-style: italic">Нет установленных диагнозов (на дату осмотра).</p>');
//
//
//
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
  /*
  if (isset($id)) unset($id);
  if (isset($_GET['id']))
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
  */
      print ('<h2>Выбор нового диагноза</h2>');
  //}
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
//  if (isset($set_id)) print ('<input type="hidden" name="set_id" value="'.$set_id.'"/>');
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
      if ($opts[0]{$pos}==':') // найден prompt
      {
          $prompt=substr($opts[0],0,$pos);
          $opts[0]=substr($opts[0],$pos+1);
      }
      // 3. Первая строка указывает, что требуется пользовательский ввод - нет ничего, кроме приглашения
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
      print ("<select multiple id='data{$c}' size='$size' onchange='javascript:showDiagnosis()'>");
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
  print <<<END
"/>
<input type="button" value=" ? " onclick="javascript:add('?')"/>
<input type="button" value=" реконвалесценция " onclick="javascript:add(', реконвалесценция.')"/>
<input type="button" value=" реконвалесцент " onclick="javascript:add(', реконвалесцент.')"/>
<input type="button" value=" в анамнезе " onclick="javascript:add(', в анамнезе.')"/>
</p><p><input type="submit" value="Добавить диагноз"/>
&nbsp;<input type="button" value="Отменить" onclick="document.location='diag.php'"/></p></form>
END;
}
include('footer.inc');
?>