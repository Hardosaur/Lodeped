<?php
// dualnum
if (isset($values[$id])) $value=$values[$id]; else $value=$field->value;
if (isset ($value) && strlen($value)) list ($val1, $val2) = explode('/',$value); else $val1=$val2='';
print <<<END
<input id="$id" name="$id" type="hidden" value="$value"/><input class="numfield" id="f$id" type="text" value="$val1" onChange="doDualnum($id)"/>&nbsp;/&nbsp;<input class="numfield" id="s$id" type="text" value="$val2" onChange="doDualnum($id)"/>&nbsp;$field->suffix
END;
?>