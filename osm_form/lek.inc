<?php
//global $db;
$checked=''; $checked2=''; $value='';
if (isset($field))
{
  $id=$field->id;
  if (isset($values[$id]))
  {
      $value=$values[$id];
      if ((int) $value & 1) $checked='checked'; else $checked='';
      if ((int) $value & 2) $checked2='checked'; else $checked2='';
  }
}
// чтение данных о лекарственных назначениях
if (($this->editmode!=MODE_PRINT && $this->editmode!=MODE_VIEW) || ($checked=='' && $checked2==''))
{
  $pat_id=$this->uPatID;
  $date=$this->sDate;
// все предписания
  $res=$db->query('select * from leks where pat_id='.$pat_id.' and unset_date is null and set_date <= "'.$date.'" order by set_date desc');
  if ($res && $res->num_rows)
  {
     while ($row = $res->fetch_object())
     {
      if ($this->editmode==MODE_PREPRINT) print ('<input name="lek'.$row->id.'" type="checkbox" checked value="1">&nbsp;');
      if ($this->editmode==MODE_PRINT && !isset($_POST['lek'.$row->id])) continue;
      print ($row->lek);
      if ($row->ignored > 0) print ('&nbsp;<b>(не принимался)</b>');
      print ('<br>'."\n");
     }
     $res->free();
  }
  else print ('Новых назначений нет.<br>');
  if ($this->editmode!=MODE_PRINT && $this->editmode!=MODE_PREPRINT &&$this->editmode!=MODE_VIEW)
  {
     $res = $db->query('select allergies.all_id, lek_names.rname from allergies, lek_names where allergies.pat_id='.$pat_id.' and allergies.lek_id=lek_names.lek_id');
     if ($res && $res->num_rows)
     {
         print ('<b>Имеются сведения об аллергических реакциях на следующие препараты:</b><br><ul>');
         while ($row = $res->fetch_row()) print ("<li>{$row[1]}</li>\n");
         $res->free();
         print ('</ul><a href="allergy.php?pat_id='.$pat_id.'" target="_blank">Подробнее...</a><br>');
     }
     print ('<input class="hidden" type="text" id="'.$id.'" name="'.$id.'" value="'.$value.'"/>');
     print ('<p><input type="checkbox" id="chk1'.$id.'" value="1" '.$checked.' onclick="doCheck1('.$id.')"/>&nbsp;Продолжать применение назначенных препаратов</p>');
     print ('<p><input type="checkbox" id="chk2'.$id.'" value="2" '.$checked2.' onclick="doCheck2('.$id.')"/>&nbsp;Не выводить строчку о назначениях</p>');
     if (isset($_SESSION['pat_id'])) print ('<a href="lek.php" target="_blank">Работать с лекарственными назначениями...</a>');
     else print ('<span style="color: grey">Работа с назначениями в режиме редактирования формы невозможна!</span>');
  }
}
else
if ($checked2=='') print ('Продолжать применение назначенных препаратов.');
?>