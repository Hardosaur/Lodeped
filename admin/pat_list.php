<?php
//
// ������� ������� �� ���������� ���������
//
require('../../settings.php');
include('../header.inc');
require('../auth.php');
require ('../access.inc');
check_access_level (2);
$db = new mysqli($dbhost,$dbuser,$dbpass,$dbname);
if (mysqli_connect_errno()) die ('Connect error: '.mysqli_connect_error());
$db->query ('set names cp1251');
print ('<table class="light">');
print ('<tr><th>�</th><th>��� ��������</th><th>���</th><th>���� ��������</th><th>�����</th><th>��������</th><th>���� ����������</th></tr>');
$doctors = $db->query ('select doctor_id, surname from doctors where dep_id=1');
if (!$doctors || !$doctors->num_rows) die ('�� ������� �������� � ��������!');
while ($doc = $doctors->fetch_row())
{
  print ('<tr><td colspan="8" class="separator">������ '.$doc[1].'</td></tr>'."\n");
  $res = $db->query ('select * from contracts as c, patients as p where c.valid>0 and c.doctor_id='.$doc[0].' and p.pat_id = c.pat_id order by p.surname');
  if (!$res || !$res->num_rows) continue;
  while ($row = $res->fetch_object())
  {
      print ('<tr><td>');
      $row->dispancer?print('�'):print(' '); print ('</td>'."\n");
      print ("<td>$row->surname $row->name $row->lastname</td>");
      $row->sex=='male'?print('<td>�</td>'):print('<td>�</td>');
      $d=explode('-',$row->birth);
      print ("<td>$d[2].$d[1].$d[0]</td>");
      print ('<td>'.$row->address);
      if ($row->entrance) print (', ������� '.$row->entrance);
      if ($row->floor) print (', ���� '.$row->floor);
      print ('</td><td>');
      $phones = $db->query ('select * from phones where pat_id='.$row->pat_id);
      if ($phones && $phones->num_rows)
      {
          while ($p = $phones->fetch_object()) print ($p->number." ($p->operator) ");
          $phones->free();
      }
      //print ('</td><td>'.$row->number);
      $d=explode('-',$row->signed);
      print ("</td><td>$d[2].$d[1].$d[0]</td></tr>");
  }
  $res->free();
}
$doctors->free();
include ('../footer.inc');
?>