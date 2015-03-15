<?php
$teeth=array();
for ($i=11; $i<=88; $i++) if ($i%10) $teeth[$i]=''; // инициализируем массив значений
if (isset($values[$id]) && strlen(trim($values[$id])))
{
       $th = explode (' ',trim($values[$id]));
       foreach ($th as $val)
       {
           if ($val>50) $key=$val-40;
           else $key=$val;
           $teeth[$key]=$val;
       }
       print ('<input class="hidden" type="hidden" name="'.$id.'" id="'.$id.'" value="'.$values[$id].'">');
}
else print ('<input class="hidden" type="hidden" name="'.$id.'" id="'.$id.'" value="">');
if ($this->editmode!=MODE_PRINT && $this->editmode!=MODE_VIEW)
{
    // тестовый вывод
    print ('проверка: ['.$values[$id].']<br>');
    print ('<table align="center" border="0" cellspacing="0"><tr><td style="border-bottom: solid 1px black; padding: 5px">&nbsp;');
    for ($i=8; $i>0; $i--) print ('<input style="width: 28px; height: 18px" type="button" id="tooth1'.$i.'" value="'.$teeth[10+$i].'" onclick="teethButton('.$id.',1'.$i.')">&nbsp;');
    print ('</td><td style="border-bottom: solid 1px black; border-left: solid 1px black; padding: 5px">&nbsp;');
    for ($i=1; $i<9; $i++) print ('<input style="width: 28px; height: 18px" type="button" id="tooth2'.$i.'" value="'.$teeth[20+$i].'" onclick="teethButton('.$id.',2'.$i.')">&nbsp;');
    print ('</td></tr><td style="padding: 5px">&nbsp;');
    for ($i=8; $i>0; $i--) print ('<input style="width: 28px; height: 18px" type="button" id="tooth4'.$i.'" value="'.$teeth[40+$i].'" onclick="teethButton('.$id.',4'.$i.')">&nbsp;');
    print ('</td><td style="border-left: solid 1px black; padding: 5px">&nbsp;');
    for ($i=1; $i<9; $i++) print ('<input style="width: 28px; height: 18px" type="button" id="tooth3'.$i.'" value="'.$teeth[30+$i].'" onclick="teethButton('.$id.',3'.$i.')">&nbsp;');
    print ('</td></tr></table>');
}
elseif (isset($values[$id]))
{
    print ('<br><span style="font-family: \'Courier new\'; font-style: normal">');
    for ($i=8; $i>0; $i--) if ($teeth[10+$i]) print ($teeth[10+$i].' '); else print ('() ');
    print (' | ');
    for ($i=1; $i<9; $i++) if ($teeth[20+$i]) print ($teeth[20+$i].' '); else print ('() ');
    print ('<br>'); for ($i=0; $i<17; $i++) print ('ЧЧЧ'); print ('<br>');
    for ($i=8; $i>0; $i--) if ($teeth[40+$i]) print ($teeth[40+$i].' '); else print ('() ');
    print (' | ');
    for ($i=1; $i<9; $i++) if ($teeth[30+$i]) print ($teeth[30+$i].' '); else print ('() ');
    print ('</span>');
}
?>