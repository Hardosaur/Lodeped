<?php
// checkbox
$id=$field->id;
if (isset($values[$id]))
{
  $value=$values[$id];
//  $slashval=htmlspecialchars($value);
  $checked='checked';
}
else
{
  $value='';
  $checked='';
  $slashval='';
}
$field->name=htmlspecialchars($field->name,ENT_COMPAT,'cp1251');
print <<<END
<input class="hidden" type="text" id="$id" name="$id" value="$value"/><input type="checkbox" id="chk$id" value="$field->name" $checked onclick="doCheck($id)"/>
END;
if ($value>1) print ('<span style="font-weight: bold; cursor: pointer" id="pluschk'.$id.'" onclick="doPlusCheck('.$id.')"> + </span>');
else print ('<span style="font-weight: bold; cursor: pointer" id="pluschk'.$id.'" onclick="doPlusCheck('.$id.')">&nbsp;&nbsp;&nbsp;</span>');
?>