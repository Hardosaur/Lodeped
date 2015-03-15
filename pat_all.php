<?php
//
// �������� ������� ������ ���������
// �������� �� ��������� ��������������
//
require('../settings.php');
require('auth.php');
$WINDOW_TITLE = '������ ������ ���������';
include('header.inc');
require('connect.inc');
require ('ajax_search.js');
print ('<h1>������ ���� ���������</h1>');
// ������� ����� �� AJAX
print ('<p>������� �����: <input id="searchq" type="text" value="" size="20" maxlength="20" onkeyup="javascript:searchNameq()"/></p>');
print ('<div id="search-result">');
// ������ ������ � �������� � ��������� �� ���������

$contracted = array (); // ��������� ������ ID ���������
$doctors = array (); // ������ � ��������
$res = $db->query ('select doctor_id, surname, name, lastname, color from doctors');
if (!$res || !$res->num_rows) die ('���� �������� ����� ��� ����������! '.$db->error);
$cnt=0;
$color = array();
print ('<p>�� ��������� � ��������: ');
while ($doctor = $res->fetch_object())
{
  $pats = $db->query('select patients.pat_id from patients,contracts where (contracts.pat_id=patients.pat_id and contracts.doctor_id='.$doctor->doctor_id.' and contracts.valid>0)');
  if ($pats && $pats->num_rows)
  {
     while ($pat = $pats->fetch_row()) $contracted[$doctor->doctor_id][]=$pat[0];
     $pats->free();
  }
  if (isset($contracted[$doctor->doctor_id]))
  {
     $doctors[]=$doctor->doctor_id;
     $color[$doctor->doctor_id]=$doctor->color;
     print ('<span style="font-weight: bold; color: #'.$doctor->color.'">'.$doctor->surname.' '.$doctor->name{0}.'. '.$doctor->lastname{0}.'.</span>&nbsp;');
  }
  $cnt++;
}
$res->free();
print ('</p><p><table width="450" cellpadding="3" cellspacing="1" border="0" frame="void"><tr><th>�������, ���, ��������</th><th>���� ��������</th></tr>');

//�������� ���� ��������� �� ������ ������ �������


$tr='odd';
$code = ord('�'); // ��� ������ ����� �������� �������� � cp1251
$yacode = ord('�'); // ����� �� �������� ������� ������ ���
$p1 = ord ('�'); $p2 = ord ('�'); $p3 = ord ('�'); // ��������� ��� �����
for (; $code<=$yacode; $code++)
{
  if ($code == $p1 || $code == $p2 || $code == $p3) continue;
  $letter = chr ($code);
  $pats = $db->query ('select * from patients where surname like "'.$letter.'%" order by surname');
  if (!$pats) continue;
  if (!$pats->num_rows) {  $pats->free(); continue; }
  print ('<tr><td colspan=3><h3>'.$letter.'</h3></td>');
  while ($row = $pats->fetch_object())
  {
      if ($tr == 'odd') $tr='even'; else $tr='odd';
      print ('<tr class="'.$tr.'"><td align="left"><span style="cursor: pointer; color: #');
      // �������� �������������� � �������
      $found=0;
      for ($i=0; $i<count($doctors); $i++)
          if (in_array($row->pat_id,$contracted[$doctors[$i]]))
          {
             print ($color[$doctors[$i]]);
             $found=1;
             break;
          }
      if (!$found) print ('777777');
      print ('" onclick="window.open(\'patient.php?pat_id='.$row->pat_id.'\',\'newwin\')">'.$row->surname.' '.$row->name.' '.$row->lastname.'</span></td>');
      $birth=explode('-',$row->birth);
      print ("<td align=center>$birth[2].$birth[1].$birth[0]</td></tr>");
  }
  $pats->free();
}
print ('</table></div></p><p><input class="button" type="button" value="������ ������ ��������" onClick="javascript:document.location=\'pat_edit.php\'"></p><p><a href="doctor.php">��������� �� �������� �������</a></p>');
include ('footer.inc');
?>