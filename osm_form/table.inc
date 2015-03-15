<?php
// table
if (isset($values[$id])) $val=$values[$id]; else $val='';
print ('<input type="hidden" class="hidden" name="'.$id.'" id="'.$id.'" value="'.$val."\"/>\n");
print ('<div class="table" id="tab'.$id.'">');
if (strlen($val))
{
  //-debug
//  print ('['.$val.']<br>');
  //-debug
  $lines = explode('<BR>',$val);
  for ($i=0; $i<count($lines); $i++) if (strlen($lines[$i])) print ($lines[$i].'&nbsp;<a href="javascript:tableRemove('.$id.', '.$i.')">&otimes;</a><BR>');
  unset($lines);
}
// else print ('='.$field->value.'=');
print ('</div><span id="but'.$id.'"><a href="javascript:tableShow('.$id.')">добавить</a></span><div style="display:none" id="sel'.$id.'">');
$selects = explode ($delim.'*'.$delim,$field->value);
$columns = count ($selects);
for ($i=0; $i<count($selects); $i++)
{
  print("<select class=\"table\" id=\"tab{$id}col$i\">");
  $opts=explode($delim,$selects[$i]); // получим пункты списка
  print ("<option value=\"\"></option>\n");
  foreach ($opts as $opt)
  {
    $slsh=htmlspecialchars($opt,ENT_COMPAT,'cp1251');
    print ("<option value=\"$slsh\">$opt</option>\n");
  }
  print ("</select>&nbsp;\n");
}
print ("<a href='javascript:tableAdd($id,$columns)'>добавить</a></div>");
?>