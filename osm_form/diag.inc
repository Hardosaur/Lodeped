<?php
//global $db;
//$checked='';
if (isset($field))
{
  $id=$field->id;
/*  if (isset($values[$id]))
  {
      $checked='checked';
  }
  */
}
$pat_id=$this->uPatID;
$date=$this->sDate;
if (($this->editmode!=MODE_PRINT && $this->editmode!=MODE_VIEW) /*|| $checked==''*/)
{
$res = $db->query ("select id, diag, set_date, unset_date from diags where pat_id=$pat_id and set_date <= \"$date\" and (unset_date is null or unset_date > \"$date\") order by set_date desc");
if ($res && $res->num_rows)
{
  $p_diags = array();
  $diags = array();
  while ($row = $res->fetch_object())
  {
      if (strpos($row->diag,'ќстр')) $p_diags[]=array($row->diag,join('.',array_reverse(explode('-',$row->set_date)))); // приоритет дл€ острых диагнозов
      else $diags[]=array($row->diag,join('.',array_reverse(explode('-',$row->set_date))),$row->id);
  }
  $res->free();
  if (count($p_diags)) foreach ($p_diags as $d)
  {
      if ($this->editmode==MODE_PREPRINT) print ('<input name="diag'.$d[2].'" type="checkbox" checked value="1">&nbsp;');
      if ($this->editmode!=MODE_PRINT || isset($_POST['diag'.$d[2]]))
      {
            print ($d[0]);
            if ($this->editmode!=MODE_PRINT && $this->editmode!=MODE_VIEW) print (' ('.$d[1].')');
            print ('<br>'."\n");
      }
  }
  if (count($diags)) foreach ($diags as $d)
  {
      if ($this->editmode==MODE_PREPRINT) print ('<input name="diag'.$d[2].'" type="checkbox" checked value="1">&nbsp;');
      if ($this->editmode!=MODE_PRINT || isset($_POST['diag'.$d[2]]))
      {
            print ($d[0]);
            if ($this->editmode!=MODE_PRINT && $this->editmode!=MODE_VIEW) print (' ('.$d[1].')');
            print ('<br>'."\n");
      }
  }
}
else print ('Ќет установленных диагнозов (на дату осмотра).<br>');
}
if ($this->editmode!=MODE_PRINT && $this->editmode!=MODE_PREPRINT && $this->editmode!=MODE_VIEW)
{
  print ('<input type="hidden" class="hidden" type="text" id="'.$id.'" name="'.$id.'" value="'.$value.'"/>');
//  print ('<p><input type="checkbox" id="chk'.$id.'" value="1" '.$checked.' onclick="doCheck('.$id.')"/>&nbsp;Ќе выводить строчку о диагнозах</p>');
if (isset($_SESSION['pat_id'])) print ('<a href="diag.php" target="_blank">–аботать с диагнозами</a>');
else print ('<span style="color: grey">–абота с диагнозами в режиме редактировани€ формы невозможна!</span>');
}
?>