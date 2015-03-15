<?php
// multi
unset($val);
if (isset($values[$id])) $val=$values[$id]; else $val='';
$vals=array();
if (strlen($val)) $vals=explode(', ',$val);
$opts=explode("|",$field->value); // получим пункты списка
$opts_num = count ($opts);
print ("<input type='hidden' class='hidden' name=\"$id\" id=\"$id\" value=\"$val\"/><select multiple class=\"field\" id=\"sel$id\" size=\"{$opts_num}\" onchange=\"doMulti($id)\">");
foreach ($opts as $opt)
{
    $slsh=htmlspecialchars($opt,ENT_COMPAT,'cp1251');
    if (in_array($opt, $vals)) $selected=' selected'; else $selected='';
    print ("<option value=\"$slsh\"$selected>$opt</option>\n");
}
print ('</select><br><span style="font-size: 7pt; color: gray">для выбора нескольких пунктов удерживайте кл. Ctrl</span>');
?>