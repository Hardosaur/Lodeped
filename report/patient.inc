<?php
if (isset($_SESSION['pat_id']))
{
  $pat_id=$_SESSION['pat_id'];
  $pat = $db->query ('select * from patients where pat_id='.$_SESSION['pat_id']);
  if (!$pat || !$pat->num_rows) print ('������ ������ ������ ��������! '.$db->error);
  else
  {
      $row=$pat->fetch_object();
      print ('<p>�������, ���, ��������: '.$row->surname.' '.$row->name.' '.$row->lastname.'</p>');
      print ('<p>���� ��������: '.join('.',array_reverse(explode('-',$row->birth))).'</p>');
      print ('<p>�����: '.$row->address.'</p>');
      $pat->free();
  }
}
else print ('��� ������ � ��������! (�������� ������� �������?)');
?>