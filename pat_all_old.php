<?php
//
// �������� ������� ������ ���������
// �������� ��������� �������: ����� ����� ������ �� ������ (������ 10), ������ �� ������ �����, ����� �� �������
//
require('../settings.php');
require('auth.php');
$WINDOW_TITLE = '������ ���������';
include('header.inc');
require('connect.inc');
print '<h1>�������� ������ ���������</h1>';
// ��������� ���������� �� ���������� ���������
$res = $db->query ('select count(*) from patients');
if (!$res || !$row=$res->fetch_row()) die ('���� ��������� �����!');
print '<p>����� ��������� � ����: '.$row[0].'</p>';
$res->free();
?>
<p><form method='post'>
����� �� ������ ������: <input type='text' name='search' size='30' maxlength='30'>&nbsp;<input class="button" type='submit' value='������'>
</form></p>
<p>�������� �� ��������� �������� <span style="color: #307030; font-weight: bold">������</span></p>
<p><table width=450 cellpadding=3 cellspacing=1 border=0 frame='void'>
<tr><th>�������, ���, ��������</th><th>���� ��������</th></tr>
<?php
//
// ����� �� �������
//
$tr='odd';
if (isset($_POST['search']))
{
  $pats = $db->query ('select * from patients where surname like "'.$_POST['search'].'%" order by surname');
  if (!$pats) die ('Error in query! '.$db->error);
  if (!$pats->num_rows)
  {
      $pats->free();
      print ('</table><p>��������� � ��������, ������������ �� "'.$_POST['search'].'", �� �������.');
  }
  else
  {
      $res = $db->query ('select patients.pat_id from patients,contracts where patients.surname like "'.$_POST['search'].'%" and (contracts.pat_id=patients.pat_id and contracts.doctor_id='.$_SESSION['doctor_id'].' and contracts.valid>0) order by patients.surname');
      if (isset ($own_pats)) unset ($own_pats);
      $own_pats=array();
      if ($res && $res->num_rows)
      {
          while ($row=$res->fetch_row()) $own_pats[]=$row[0];
          $res->free();
      }
      while ($row = $pats->fetch_object())
      {
          if ($tr == 'odd') $tr='even'; else $tr='odd';
          print ("<tr class='$tr'><td align='left'><a ");
          if (in_array($row->pat_id,$own_pats)) print ('class="special" ');
          print ("href='patient.php?pat_id=$row->pat_id'>$row->surname $row->name $row->lastname</a></td>");
          $birth=explode('-',$row->birth);
          print ("<td align=center>$birth[2].$birth[1].$birth[0]</td></tr>");
      }
  }
  print ('</table></p><p><a href="pat_all.php">��������� � ������ ���������</a></p>');
  include ('footer.inc');
  exit;
}
//
// ��� �������� � �������� �� �������� �����
//
if (isset($_GET['letter']))
{
  $pats = $db->query ('select * from patients where surname like "'.$_GET['letter'].'%" order by surname');
  if (!$pats) die ('Error in query!');
  if (!$pats->num_rows)
  {
      $pats->free();
      print ('</table><p>��������� � ��������, ������������ �� "'.$_GET['letter'].'", �� �������.');
  }
  else
  {
      $res = $db->query ('select patients.pat_id from patients,contracts where patients.surname like "'.$_GET['letter'].'%" and (contracts.pat_id=patients.pat_id and contracts.doctor_id='.$_SESSION['doctor_id'].' and contracts.valid>0) order by patients.surname');
      if (isset ($own_pats)) unset ($own_pats);
      $own_pats=array();
      if ($res && $res->num_rows)
      {
          while ($row=$res->fetch_row()) $own_pats[]=$row[0];
          $res->free();
      }
      while ($row = $pats->fetch_object())
      {
          if ($tr == 'odd') $tr='even'; else $tr='odd';
          print ("<tr class='$tr'><td align='left'><a ");
          if (in_array($row->pat_id,$own_pats)) print ('class="special" ');
          print ("href='patient.php?pat_id=$row->pat_id'>$row->surname $row->name $row->lastname</a></td>");
          $birth=explode('-',$row->birth);
          print ("<td align=center>$birth[2].$birth[1].$birth[0]</td></tr>");
      }
  }
  print ('</table></p><p><a href="pat_all.php">��������� � ������ ���������</a></p>');
  include ('footer.inc');
  exit;
}
//
//�������� ���� ��������� �� ������ ������ �������
//
// ���� ������ �� ������ ������ ��������
//
$code = ord('�'); // ��� ������ ����� �������� �������� � cp1251
$yacode = ord('�'); // ����� �� �������� ������� ������ ���
$p1 = ord ('�'); $p2 = ord ('�'); $p3 = ord ('�'); // ��������� ��� �����
for (; $code<$yacode; $code++)
{
  if ($code == $p1 || $code == $p2 || $code == $p3) continue;
  $letter = chr ($code);
  $pats = $db->query ('select * from patients where surname like "'.$letter.'%" order by surname');
  if (!$pats) continue;
  if (!$pats->num_rows) {  $pats->free(); continue; }
  $res = $db->query ('select patients.pat_id from patients,contracts where patients.surname like "'.$letter.'%" and (contracts.pat_id=patients.pat_id and contracts.doctor_id='.$_SESSION['doctor_id'].' and contracts.valid>0) order by patients.surname');
  if (isset ($own_pats)) unset ($own_pats);
  $own_pats=array();
  if ($res && $res->num_rows)
  {
     while ($row=$res->fetch_row()) $own_pats[]=$row[0];
     $res->free();
  }
  print ('<tr><td colspan=3><h3>'.$letter.'</h3></td>');
  $count=0;
  while ($count++<7 && $row = $pats->fetch_object())
  {
      if ($tr == 'odd') $tr='even'; else $tr='odd';
      print ('<tr class="'.$tr.'"><td align="left"><a ');
      if (in_array($row->pat_id,$own_pats)) print ('class="special" ');
      print ("href='patient.php?pat_id=$row->pat_id'>$row->surname $row->name $row->lastname</a></td>");
      $birth=explode('-',$row->birth);
      print ("<td align=center>$birth[2].$birth[1].$birth[0]</td></tr>");
  }
  if ($pats->num_rows>7) print ("<tr><td colspan='2' style='text-align: center; font-size: smaller; font-style: bold'><a href='pat_all.php?letter=$letter'>��� �������� ($pats->num_rows) �� ����� '$letter'</a></td>\n");
  $pats->free();
}
print ('</table></p><p><input class="button" type="button" value="������ ������ ��������" onClick="javascript:document.location=\'pat_add.php\'"></p><p><a href="doctor.php">��������� �� �������� �������</a></p>');
include ('footer.inc');
?>