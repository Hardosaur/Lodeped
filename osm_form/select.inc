<?php
// select
if (isset($values[$id])) $val=$values[$id]; else $val='';
print ("<input class=\"hidden\" name=\"$id\" id=\"$id\" value=\"$val\"/>");
print ('<select style="display:inline" class="field" id="sel'.$id.'" onchange="doSelect('.$id.')">');
$opts=explode("|",$field->value); // получим пункты списка
print ("<option value=\"\">Ч</option>\n");
foreach ($opts as $opt)
{
    $slsh=htmlspecialchars($opt,ENT_COMPAT,'cp1251');
    if ($opt == $val) $selected=' selected'; else $selected='';
    print ("<option value=\"$slsh\" $selected>$opt</option>\n");
}
print ('</select>&nbsp;'.$field->suffix.'&nbsp;<a href="javascript:selectShow('.$id.')">&oplus;</a><span id="span'.$id.'" style="display:none"><input class="field" id="add'.$id.'" type="text" size="40" maxlength="100" />&nbsp;<a href="javascript:selectSave('.$id.')">сохранить</a></span>');
?>