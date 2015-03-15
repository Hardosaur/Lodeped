<?php
//
// �������� ������ ���������� ��������� (������ �������)
// �� �����: $_GET['pat_id']
// �������� �� ����� ������� ���� �� �����������
//
require('../settings.php');
require('auth.php');
$WINDOW_TITLE = '���� ���������� ���������';
include('header.inc');
require('connect.inc');
// �������� ���������� ������
if (!isset($_GET['pat_id']) || !is_numeric($_GET['pat_id'])) // ��� ������������ ���������
{
  print ('<p>�� ����� ����������� ��������! (������ ������� �������?)</p>');
  print ('<a href="doctor.php">��������� � �������� �������</a>');
  include ('footer.inc');
  exit;
}
$pat_id=$_GET['pat_id'];
// ����� ����� ��������
$res = $db->query ('select * from patients where pat_id = '.$pat_id);
if (!$res) die ("�� ������� ������ �������� $pat_id!");
$row=$res->fetch_object();
print ('<h1>���� ���������� ���������</h1>');
print ("<p>�������: $row->surname $row->name $row->lastname</p>\n");
$res->free();
// ��������� ��������
$diags = $db->query ('select * from diags, doctors where diags.pat_id='.$pat_id.' and diags.doctor_id = doctors.doctor_id order by diags.set_date desc');
if ($diags)
{
  print ('<p><table border="0" cellpadding="6" cellspacing="1"><col width="80"><col width="80"><col width="120"><col width="60"><col><col>');
  print ('<tr><th>���� ����������</th><th>���� ������</th><th>������� �������</th><th>������</th><th>�������</th></tr>');
  $color = 'black'; // ���� ������� ������
  $status='';
  $tr='even';
  while ($diag=$diags->fetch_object())
  {
      // ���������� ����
      $doctor=$diag->surname.' '.$diag->name{0}.'. '.$diag->lastname{0}.'.';
      $status='��������';
      if ($diag->unset_date)
      {
          $unset_date=join('.',array_reverse(explode('-',$diag->unset_date)));
          $status='����';
          $color='#777777';
      }
      else
      {
          $unset_date='';
          $color='black';
      }
      $set_date=join('.',array_reverse(explode('-',$diag->set_date)));
      if ($tr == 'odd') $tr='even'; else $tr='odd';
      print ("\n<tr class='$tr' style='color: $color'><td>$set_date</td><td>$unset_date</td><td>$doctor</td><td>$status</td><td>$diag->diag</td></tr>");
  }
  print ('</table></p>');
  $diags->free();
}
else print ('<p style="font-style: italic">������������� ��������� �� �������.</p>');
print ('<p><a href="patient.php?pat_id='.$pat_id.'">��������� �� �������� ��������</a></p>');
include ('footer.inc');
?>


