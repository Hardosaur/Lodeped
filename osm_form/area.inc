<?php
// text
$id=$field->id;
if (isset($values[$id])) $value=$values[$id]; else $value=$field->value;
$slashval=htmlspecialchars($value,ENT_COMPAT,'cp1251');
print <<<END
<textarea id="$id" name="$id" rows="5" cols="50" onchange="doHighlight($id)"/>$slashval</textarea>
END;
?>