<?php
// text
$id=$field->id;
if (isset($values[$id])) $value=$values[$id]; else $value=$field->value;
$slashval=htmlspecialchars($value,ENT_COMPAT,'cp1251');
print <<<END
<input class="field" id="$id" name="$id" type="text" value="$slashval" onchange="doHighlight($id)"/>&nbsp;$field->suffix
END;
?>