<?php
//
// Обработка начальной и конечной даты
//
if (!isset($date2_mysql) && !isset($date1_mysql))
{
  if (!isset($vars['1']) || !isset($vars['2']) || !isset($vars['3']) || !isset($vars['10']) || !isset($vars['11']) || !isset($vars['12']))
   return 1;
  if (!is_numeric($vars['1']) || !is_numeric($vars['2']) || !is_numeric($vars['3']) || !is_numeric($vars['10']) || !is_numeric($vars['11']) || !is_numeric($vars['12']))
   return 2;
  if (!checkdate($vars['2'],$vars['1'],$vars['3'])) return 3;
  if (!checkdate($vars['11'],$vars['10'],$vars['12'])) return 4;
  $date1=$vars['1'].'.'.$vars['2'].'.'.$vars['3'];
  $date2=$vars['10'].'.'.$vars['11'].'.'.$vars['12'];
  $date1_mysql=$vars['3'].'-'.$vars['2'].'-'.$vars['1'];
  $date2_mysql=$vars['12'].'-'.$vars['11'].'-'.$vars['10'];
}
return 0;
?>