<?php
//
// ����� ������ � ������������ ������������
// ������� ������: $pat_id - ID ��������
//                 $vars[1..3, 10..12] - ��������� � �������� ����
//
if (include 'report/date2.inc') print ('<p>[ ��������� � �������� ���� �� ������� ��� ������� �������! ]</p>');
else
{
  $res=$db->query ("select diag, set_date, unset_date from diags where pat_id=$pat_id and set_date <= '$date2_mysql' and set_date >= '$date1_mysql' order by set_date");
  if ($res && $res->num_rows)
  {
      print ('<ul>');
      while ($row = $res->fetch_object())
      {
          print ('<li>'.join('.',array_reverse(explode('-',$row->set_date))).': '.$row->diag.'</li>');
//      if ($row->unset_date) print (', '.join('.',array_reverse(explode('-',$row->unset_date))));
//      print (')</li>');
      }
      $res->free();
      print ('</ul>');
  }
}
?>