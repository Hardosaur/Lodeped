<?php
//
// Редактор базы данных диагнозов
// Позволяет изменять, удалять, добавлять диагнозы в базу данных
//
require('../../settings.php');
require('../auth.php');
$WINDOW_TITLE = 'Редактор диагнозов';
require ('../access.inc');
check_access_level (1);
include('../header.inc');
require('../connect.inc');
?>
<script language="JavaScript" type="text/javascript">
function getFocus(input)
{
  if (input.value[0]!= '\0'&& input.value[0]=='-' && input.value[1]=='-') input.value="";
}
</script>
<style>
table.edit { border: 0px;  }
tr.edit td { background-color: #f7f7f7; padding: 5px;}
tr.coledit td { background-color: #f0f0f0; padding: 5px;}
tr.newcol td { background-color: white; padding: 5px;}
input {background-color: white; border: solid 1px gray; padding: 4px;}
input.button {background-color: #dddddd; }
</style>
<h1>Редактор описаний диагнозов</h1>
<?php
//
// Сохраняем данные в базе
//
if (isset($_POST['diag_id']))
{
  $c=1;
  $data='';
  while (isset($_POST['value'.$c.'-1']) || isset($_POST['prompt'.$c]))
  {
      if (isset($_POST['prompt'.$c]))
      {
           $prompt=trim($_POST['prompt'.$c]);
           $prompt=rtrim($prompt,':'); // если случайно введено двоеточие
           if (strlen($prompt)) $cascade=$prompt.':'; else $cascade='';
      }
      else $cascade='';
      $c2=1;
      while (isset($_POST['value'.$c.'-'.$c2]))
      {
          if ($c2>1) $cascade.=';';
          @$cascade.=$_POST['var'.$c.'-'.$c2].'='.$_POST['value'.$c.'-'.$c2];
          $c2++;
      }
      if ($c>1 && strlen($cascade)) $data.='|';
      $data.=$cascade;
      $c++;
  }
  if ($data)
     if (!$db->query('update diag_data set data="'.$data.'" where diag_id='.$_POST['diag_id'])) die ('Ошибка обновления базы данных: '.$db->error);
  $_GET['diag_id']=$_POST['diag_id'];
}
//
// Создаем пустую форму нового диагноза
//
if (isset($_POST['new']))
{
  $new = $_POST['new'];
  if (!strlen ($new)) die ('<p>Не задано короткое название диагноза! <a href="diags_edit.php">Назад.</a></p>');
  // проверим, нет ли такого в базе
  $res = $db->query ('select diag_id from diag_names where diag_name = "'.$new.'"');
  if ($res && $res->num_rows) die ('<p>Такой диагноз уже есть в базе! <a href="diags_edit.php">Назад.</a></p>');
  // добавим
  if (!$db->query('insert into diag_names values (NULL, "'.$new.'")')) die ('<p>Невозможно добавить диагноз! Ошибка: '.$db->error.' <a href="diags_edit.php">Назад.</a></p>');
  $res=$db->query('select LAST_INSERT_ID() from diag_names');
  $row=$res->fetch_array();
  $diag_id=$row[0];
  $res->free();
  if (!$db->query('insert into diag_data values ('.$diag_id.', "")')) die ('<p>Невозможно добавить диагноз! Ошибка: '.$db->error.' <a href="diags_edit.php">Назад.</a></p>');
  $_GET['diag_id']=$diag_id; // перейдем к редактированию нового диагноза
}
//
// Удаляем диагноз
//
if (isset($_POST['delete']))
{
  if (!isset($_POST['diag_id']) || !is_numeric($_POST['diag_id'])) die ('<p>Не задан id диагноза! <a href="diags_edit.php">Назад.</a></p>');
  if (!$db->query('delete from diag_names where diag_id='.$_POST['diag_id'])) die ('<p>Невозможно удалить диагноз [1]! Ошибка: '.$db->error.' <a href="diags_edit.php">Назад.</a></p>');
  if (!$db->query('delete from diag_data where diag_id='.$_POST['diag_id'])) die ('<p>Невозможно удалить диагноз [2]! Ошибка: '.$db->error.' <a href="diags_edit.php">Назад.</a></p>');
  print ('<p>Диагноз успешно удалён! <a href="diags_edit.php">Назад.</a></p>');
  exit;
}
//
// Выводим формы
//
if (!isset($_GET['diag_id']))
{
if (!isset($_GET['letter'])) // не выбрана первая буква, выводим их список для выбора
  {
      print ('<h2>Выбор диагноза</h2><p><table border="0"><tr valign="top" align="center">');
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
           print ("<input class='button' type='button' value='$letter' onclick='javascript:document.location=\"diags_edit.php?letter=$letter\"'/>");
      }
  }
  else // Передана первая буква, вывести список диагнозов на эту букву
  {
      print ('<h2>Выбор диагноза</h2><p><table border="0"><tr valign="top" align="center">');
      print ('<td width="200">Первая буква:<br><b>'.$_GET['letter'].'</b><br>(<a href="diags_edit.php">выбрать другую</a>)</td>');
      // получаем из базы все диагнозы на эту букву
      $res=$db->query('select * from diag_names where diag_name like "'.$_GET['letter'].'%"');
      if (!$res || !$res->num_rows) die ('База данных названий диагнозов не содержит названий на букву'.$_GET['letter'].'! Ошибка: '.$db->error);
      $size=$res->num_rows;
      if ($size<2) $size=2;
      print ('<td width="300">Диагнозы:<br><select size="'.$size.'" onchange="javascript:document.location=\'diags_edit.php?diag_id=\'+this.options[this.selectedIndex].value">');
      while ($row = $res->fetch_object())
      {
          print ("\n<option value='{$row->diag_id}'>$row->diag_name</option>");
      }
      $res->free();
  }
  print ('</td></tr></table></p>');
  print ('<h2>Добавить новый диагноз</h2><form method="post" action="diags_edit.php"><p>Название диагоноза:&nbsp;<input type="text" name="new" size="40" maxlength="99"/><input class="button" type="submit" value="Добавить"/><br>');
  print ('(название должно начинаться с существительного и быть коротким, но понятным)</p>');
}
else // передано название диагноза, вывести форму редактирования полей ввода
{
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
  print ('<h2>Выбор диагноза</h2><p><table border="0"><tr valign="top" align="center">');
  print ('<td width="200">Первая буква:<br><b>'.$letter.'</b><br>(<a href="diags_edit.php">выбрать другую</a>)</td>');
  print ('<td width="300">Название диагноза:<br><b>'.$diag_name.'</b><br>(<a href="diags_edit.php?letter='.$letter.'">выбрать другой</a>)</td></tr></table></p>');
  print ('<form action="diags_edit.php" method="post"><input type="hidden" name="diag_id" value="'.$diag_id.'"/>');
  print ('<h2>Редактировать диагноз</h2><p><table class="edit" border="0"><tr class="edit" valign="top" align="center">');
  // чтение данных к диагнозу
  $res=$db->query('select data from diag_data where diag_id='.$diag_id);
  if (!$res || !$res->num_rows) die ('База данных описаний диагнозов не содержит данных к выбранному диагнозу! Ошибка: '.$db->error);
  $row=$res->fetch_row();
  $data=$row[0];
  $res->free();
  //
  // разбор данных и вывод элементов ввода
  //
  $c = 0; // счетчик каскадов
  $types=array(); // массив типов столбцов
  if (strlen($data)) $cascades = explode ('|',$data);
  if (strlen($data))
  foreach ($cascades as $cascade)
  {
      $c++;
      // рассматриваем опции редактирования столбцов
      if (isset($_GET['delete']) && $_GET['delete']==$c && !isset($deleted)) { $c--; $deleted=1; continue; } // пропускаем каскад
      if (isset($_GET['insert']) && $_GET['insert']==$c)
      {
          if (!isset($_GET['type'])) die ('Не указан тип вставляемого столбца!');
          $type=$_GET['type'];
          $type+=0; // переводим в числовую форму
          $types[$c]=$type;
          switch ($type)
          {
              case 1: // одно значение
                   print ("<td align='center' width='350' nowrap><input type='hidden' name='var{$c}-1' value='' size='20' disabled/><input name='value{$c}-1' value='-- Название диагноза --' size='40' onfocus='javascript:getFocus(this)'/></td>\n");
                   break;
              case 2: // пользовательский ввод
                   print ("<td align='center' width='350' nowrap><input name='prompt{$c}' value='-- Приглашение к вводу --' size='40' onfocus='javascript:getFocus(this)'/></td>\n");
                   break;
              case 3: // общий случай - список значений
                   print ("<td align='center' width='550' nowrap><input name='prompt{$c}' value='' size='40' onfocus='javascript:getFocus(this)'/><br><input name='var{$c}-1' value='-- Пункт списка 1 --' size='20'/>=<input name='value{$c}-1' value='-- Вариант строки 1 --' size='40' onfocus='javascript:getFocus(this)'/>");
                   print ("<a href='diags_edit.php?diag_id=$diag_id&push=$c'>Добавить новый пункт в список (пустой)</a><br><a href='diags_edit.php?diag_id=$diag_id&pop=$c'>Удалить последний пункт списка</a></td>\n");
                   break;
              default: die ('Ошибка выбора формата столбца! Значение "'.$type.'" не определено.');
          }
          $c++;
      }
      // 1. У диагноза нет уточнений, он состоит только из имени
      if ($cascade{0}=='=')
      {
          $value=substr($cascade,1); // убираем первый знак '='
          print ("<td align='center' width='350' nowrap><input type='hidden' name='var$c-1' value='' size='20' disabled/><input name='value{$c}-1' value='$value' size='40' onfocus='javascript:getFocus(this)'/></td>\n");
          $types[$c]=1;
          break; // foreach
      }
      // делим каскад на части
      $opts = explode (';',$cascade);
      // 2. Первая строка содержит prompt, обозначенный знаком ':'
      $pos=0;
      $prompt='';
      while ($pos<strlen($opts[0]) && $opts[0]{$pos}!=':' && $opts[0]{$pos}!='=') $pos++; // ищем знак ':'
      if ($opts[0][$pos]==':') // найден prompt
      {
          $prompt=substr($opts[0],0,$pos);
          $opts[0]=substr($opts[0],$pos+1);
      }
      // 3. Первая строка уазывает, что требуется пользовательский ввод - нет ничего, кроме приглашения
      $size=count($opts);
      if ($size==1 && strlen($prompt) && strlen($opts[0])==0)
      {
          print ("<td align='center' width='350' nowrap><input name='prompt$c' value='$prompt' size='40' onfocus='javascript:getFocus(this)'/></td>\n");
          $types[$c]=2;
          continue; // foreach
      }
      // 4. Список (общий случай)
      $types[$c]=3;
      print ("<td align='center' width='550' nowrap><input name='prompt$c' value='$prompt' size='40' onfocus='javascript:getFocus(this)'/><br>\n");
      $cnt=0;
      if (isset ($_GET['pop']) && $_GET['pop'] == $c) $size--; // команда удалить последнюю строку
      if ($size<1) die ('Попытка удалить единственную строку!');
      foreach ($opts as $opt)
      {
          $cnt++;
          if ($cnt>$size) continue; // пропустить последнюю строку
          list ($var,$value) = explode ('=',$opt);
          print ("<input name='var{$c}-{$cnt}' value='$var' size='20' onfocus='javascript:getFocus(this)'/>=<input name='value{$c}-{$cnt}' value='$value' size='40' onfocus='javascript:getFocus(this)'/><br>\n");
      }
      $cnt++;
      if (isset($_GET['push']) && $_GET['push'] == $c) print ("<input name='var{$c}-{$cnt}' value='-- Пункт списка $cnt --' size='20' onfocus='javascript:getFocus(this)'/>=<input name='value{$c}-{$cnt}' value='-- Вариант строки $cnt --' size='40' onfocus='javascript:getFocus(this)'/><br>\n");
      print ("<a href='diags_edit.php?diag_id=$diag_id&push=$c'>Добавить</a>&nbsp;|&nbsp;<a href='diags_edit.php?diag_id=$diag_id&pop=$c'>Удалить</a>&nbsp;последний пункт</td>\n");
  } // конец вывода форм
  //
  // Вставляем новый столбец (если нужно)
  //
  if (isset($_GET['insert']) && $_GET['insert']==$c+1)
  {
          $c++;
          if (!isset($_GET['type'])) die ('Не указан тип вставляемого столбца!');
          $type=$_GET['type'];
          $type+=0; // переводим в числовую форму
          $types[$c]=$type;
          switch ($type)
          {
              case 1: // одно значение
                   print ("<td align='center' width='350' nowrap><input type='hidden' name='var{$c}-1' value='' size='20' disabled/>&nbsp;<input name='value{$c}-1' value='-- Название диагноза --' size='40' onfocus='javascript:getFocus(this)'/></td>\n");
                   break;
              case 2: // пользовательский ввод
                   print ("<td align='center' width='550' nowrap><input name='prompt{$c}' value='-- Приглашение к вводу --' size='40' onfocus='javascript:getFocus(this)'/></td>\n");
                   break;
              case 3: // общий случай - список значений
                   print ("<td align='center' width='550' nowrap><input name='prompt{$c}' value='-- Приглашение --' size='40' onfocus='javascript:getFocus(this)'/><br><input name='var{$c}-1' value='-- Пункт списка 1 --' size='20' onfocus='javascript:getFocus(this)'/>&nbsp;=&nbsp;<input name='value{$c}-1' value='-- Вариант строки 1 --' size='40' onfocus='javascript:getFocus(this)'/><br>\n");
                   print ("<a href='diags_edit.php?diag_id=$diag_id&push=$c'>Добавить новый пункт в список (пустой)</a><br><a href='diags_edit.php?diag_id=$diag_id&pop=$c'>Удалить последний пункт списка</a></td>\n");
                   break;
              default: die ('Ошибка выбора формата столбца! Значение "'.$type.'" не определено.');
          }
  }
  print ('</tr>');
  //
  // Обновляем данные, если были подана команда редактирования
  //
  if (isset($_GET['delete']) || isset($_GET['insert']) || isset($_GET['push']) || isset($_GET['pop']))
  {
      if (isset($_GET['delete']) && isset($cascades[$_GET['delete']-1]))
      {
          array_splice($cascades, $_GET['delete']-1, 1); // удаляем каскад
      }
      if (isset($_GET['insert']))
      {
          if (!isset($_GET['type'])) die ('Не указан тип вставляемого столбца!');
          $type=$_GET['type'];
          $type+=0; // переводим в числовую форму
          $pos=$_GET['insert']-1;
          switch ($type)
          {
              case 1: // одно значение
                   if (!isset($cascades)) $cascades[0]='=-- Название диагноза --';
                   else array_splice($cascades,$pos,0,'=-- Название диагноза --');
                   break;
              case 2: // пользовательский ввод
                   if (!isset($cascades)) $cascades[0]='-- Приглашение к вводу --:';
                   else array_splice($cascades,$pos,0,'-- Приглашение к вводу --:');
                   break;
              case 3: // общий случай - список значений
                   if (!isset($cascades)) $cascades[0]='-- Пункт списка 1 --=-- Вариант строки 1 --';
                   else array_splice($cascades,$pos,0,'-- Пункт списка 1 --=-- Вариант строки 1 --');
                   break;
              default: die ('Ошибка выбора формата столбца! Значение "'.$type.'" не определено.');
          }
      }
      if (isset($_GET['pop']))
      {
          $opts = explode (';',$cascades[$_GET['pop']-1]);
          array_pop ($opts);
          $cascades[$_GET['pop']-1]=implode(';',$opts);
      }
      if (isset($_GET['push'])) $cascades[$_GET['push']-1].=';-- Новый пункт списка --=-- Новый вариант строки --';
      if (count($cascades)) $data = implode ('|',$cascades);
      else $data='';
//      print ('Update: '.$data); // для отладки
      if (!$db->query('update diag_data set data="'.$data.'" where diag_id='.$diag_id)) die ('Ошибка обновления формата диагноза: '.$db->error);
  }
  //
  // Выводим строку с командами редактирования формата
  //
  print ('<tr class="coledit">');
  for ($cnt=1; $cnt<=$c; $cnt++)
  {
      if (!isset($types[$cnt])) continue; // столбец мог быть удален
      print ("<td align='center' nowrap>Столбец:&nbsp;<a href='diags_edit.php?diag_id=$diag_id&delete=$cnt'>удалить</a>");
      if ($types[$cnt]==1) print ('</td>');
      else print ("&nbsp;|&nbsp;вставить:&nbsp;<a href='diags_edit.php?diag_id=$diag_id&insert=$cnt&type=2'>текстовый ввод</a>&nbsp;|&nbsp;<a href='diags_edit.php?diag_id=$diag_id&insert=$cnt&type=3'>список</a></td>");
  }
  //
  // Выводим строку с запросом на добавление нового столбца
  //
  $c++;
  print ('</tr><tr class="newcol"><td>Добавить новый столбец:<br>');
  if ($c==1) print ("<a href='diags_edit.php?diag_id=$diag_id&insert=$c&type=1'>-&nbsp;полное название диагноза (только первый столбец)</a><br>"); // если столбцов еще нет
  print ("<a href='diags_edit.php?diag_id=$diag_id&insert=$c&type=2'>-&nbsp;приглашение к вводу данных</a><br><a href='diags_edit.php?diag_id=$diag_id&insert=$c&type=3'>-&nbsp;список значений</a></tr>");
  //
  print ('</table></p><p><input class="button" type="submit" value="Сохранить новый формат диагноза"/></p></form>'."\n");
  print ('<form method="post" action="diags_edit.php"><p><input type="hidden" name="delete" value="1"/><input type="hidden" name="diag_id" value="'.$diag_id.'"/><input type="submit" value="Удалить диагноз" onClick="return confirm(\'Удалить диагноз <<'.$diag_name.'>> (данные будут потеряны)?\')"/></p></form>');
}
include('../footer.inc');
?>

