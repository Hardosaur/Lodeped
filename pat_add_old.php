<?php
//
// ���������� ������ � ����� ��������
//
require('../settings.php');
include('header.inc');
require('auth.php'); // �������� ������ �������
require('connect.inc');
//
if (isset($_POST['name'])) // �������� ������ ��� ����������
{
  if (!(strlen($_POST['name']) && strlen($_POST['surname']) && strlen($_POST['lastname']) && strlen($_POST['month']) && strlen($_POST['day'])
        && strlen($_POST['year']) && strlen($_POST['sex']) && strlen($_POST['address'])))
     die ('Not enough data!');
  // ��������, ���� �� ����� ������� � ��
  $chk = $db->query ('select * from patients where surname="'.$_POST['surname'].'" and name="'.$_POST['name'].'" and lastname="'.$_POST['lastname'].'"');
  if ($chk && $chk->num_rows)
  {
      print ('<p>� ���� ��� ���� ����� �������! ���������� �� ����� ������.</p><p><a href="javascript:history.go(-1)">��������� �����</a></p>');
      include ('footer.inc');
      exit;
  }
  // ����������� ����
  if ((!(is_numeric($_POST['year']) && $_POST['year']>1900 && is_numeric($_POST['month']) && $_POST['month']>0 && $_POST['month']<13
        && is_numeric($_POST['day']) && $_POST['day']>0 && $_POST['day']<32))
        || strtotime($_POST['day'].'.'.$_POST['month'].'.'.$_POST['year']) === -1)
  {
     print ('<p>��������, ���� �������� ������� �������!</p><p><a href="javascript:history.go(-1)">��������� �����</a></p>');
     include ('footer.inc');
     exit;
  }
  $birth = sprintf("%4d-%02d-%02d",$_POST['year'],$_POST['month'],$_POST['day']);
  // ��������� ������ � ��������
  $q = 'insert into patients values (NULL, '. $_POST['dispancer'] . ', "' . $_POST['surname'] . '", "' . $_POST['name']. '", "' . $_POST['lastname'] .
       '", "' . $birth . '", "' . $_POST['address'] . '", '. $_POST['floor'] . ', ' . $_POST['entrance'] . ', "' . $_POST['sex'] . '", 0, "'. $_POST['comment'] . '")';
//  print ($q); // ��� �������
  if (!$db->query($q)) die ('<p><font color="red">���������� ������ �� ���������! ������: '.$db->error.'</font></p>');
  //
  // ��������� ���������� ������, ����� �������������� ����� pat_id
  //
  $p = $db->query ('select pat_id from patients where surname="'.$_POST['surname'].'" and name="'.$_POST['name'].'"');
  if (!($p && $pr=$p->fetch_object())) die ('<p><font color="red">������ �������, �� �� �������!</font></p>');
  $pat_id = $pr->pat_id;
  $p->free();
  if (isset($_POST['phone1']))
  {
      $c=1;
      $q = 'insert into phones values ';
      while (isset($_POST['phone'.$c]) && isset($_POST['operator'.$c]) && strlen($_POST['phone'.$c])>0)
      {
          $q=$q . '(' . $pat_id . ', "' . $_POST['phone'.$c] . '", "' . $_POST['operator'.$c] . '"),';
          $c++;
      }
      $q = rtrim ($q,','); // ������� ��������� �������
//      echo $q; // ��� �������
      if (!$db->query($q))
      {
          die ('<p>������ ���������� ������� ���������! ('.$db->error.')</p><p><a href="doctor.php">��������� �� �������� �������</a></p>');
      }
  }
  // ������!
  print ('<p>������ ������� �������.</p><p>');
  if (isset($_POST['contract']) && strlen($_POST['contract'])) print ('<a href="contract.php?pat_id='.$pat_id.'">������� � ����������� �������� (���������)</a></p>');
  else print ('<a href="doctor.php">��������� �� �������� �������</a></p>');
  include ('footer.inc');
  exit;
}
//
// ������ ���, ��������� ������� �����
//
?>
<h1>�������� ������ � ����� ��������</h1>
<p><form method=post>
<table border=0>
<tr><td>�������:<td><input type='text' name='surname' size='30' maxlength='30' value=''>
<tr><td>���:<td><input type='text' name='name' size='30' maxlength='30' value=''>
<tr><td>��������:<td><input type='text' name='lastname' size='30' maxlength='30' value=''>
<tr><td>������������ �������:<td><select name='dispancer' size='2'><option value="0" selected>���</option><option value="1">��</option></select>
<tr><td>���:<td><select name='sex' size='1'><option value='male'>�������</option><option value='female'>�������</option></select>
<tr><td>���� �������� (��.��.����):<td><input type='text' name='day' size='1' maxlength='2' value=''>.<input type='text' name='month' size='1' maxlength='2' value=''>.<input type='text' name='year' size='2' maxlength='4' value=''>
<tr><td>����� ����� ����������:<td><input type='text' name='address' size='30' maxlength='99' value=''>
<tr><td>�������:<td><input type='text' name='entrance' size='2' maxlength='5' value=''>
<tr><td>����:<td><input type='text' name='floor' size='2' maxlength='5' value=''>
<tr><td valign='top'>������ ���������:<td><input type='text' name='phone1' size='10' maxlength='30' value=''>&nbsp;��������&nbsp;<input type='text' name='operator1' size='10' maxlength='12' value=''><br>
                             <input type='text' name='phone2' size='10' maxlength='30' value=''>&nbsp;��������&nbsp;<input type='text' name='operator2' size='10' maxlength='12' value=''><br>
                             <input type='text' name='phone3' size='10' maxlength='30' value=''>&nbsp;��������&nbsp;<input type='text' name='operator3' size='10' maxlength='12' value=''><br>
<tr><td valign='top'>���. ����������:<td><textarea name='comment' cols='50' rows='4'></textarea>
</table></p>
<p><input type="checkbox" name="contract" value="1">&nbsp;����� ���������� ������ ������� � ���������� ��������</p>
<p><input type="submit" value="������ ������">&nbsp;<input type="button" value="������" onClick="javascript:history.go(-1)"></p></form>
<?php
include ('footer.inc');
?>