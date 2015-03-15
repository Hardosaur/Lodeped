<?php
//
// ������ � �����������: ��������, ���������, ����������, �����������
// �������� ��������� �������� ����� ��������, �������� ���������� �� ID ������� � ������
// ������� ��������� ���������, ������� �� ���� ��������� ��������
//
require('../settings.php');
$WINDOW_TITLE = '��������';
include('header.inc');
require('auth.php'); // �������� ������ �������
require('connect.inc');
//
// �������� ��������� � ��������
//
if (isset($_POST['contract']) && is_numeric($_POST['contract'])) // �������� ������ ��� ���������
{
  if (!(
  //strlen($_POST['number']) &&
  strlen($_POST['year1']) && strlen($_POST['month1']) && strlen($_POST['day1'])
        && strlen($_POST['year2']) && strlen($_POST['month2']) && strlen($_POST['day2'])) && strlen ($_POST['age_group']))
     die ('�� ��������� ��� ����������� ����! <a href="javascript:history.go(-1)">��������� �����</a>');
  if (!isset($_POST['number'])) $_POST['number']=1;
  // ����������� ����
  if (!(is_numeric($_POST['year1']) && $_POST['year1']>1900 && is_numeric($_POST['month1']) && $_POST['month1']>0 && $_POST['month1']<13
        && is_numeric($_POST['day1']) && $_POST['day1']>0 && $_POST['day1']<32))
  {
     print ('<p>��������, ���� ���������� ��������� ������� �������!</p><p><a href="javascript:history.go(-1)">��������� �����</a></p>');
     include ('footer.inc');
     exit;
  }
  $signed = sprintf("%4d-%02d-%02d",$_POST['year1'],$_POST['month1'],$_POST['day1']);
  if (!(is_numeric($_POST['year2']) && $_POST['year2']>1900 && is_numeric($_POST['month2']) && $_POST['month2']>0 && $_POST['month2']<13
        && is_numeric($_POST['day2']) && $_POST['day2']>0 && $_POST['day2']<32))
  {
     print ('<p>��������, ���� �������� ��������� ������ �������!</p><p><a href="javascript:history.go(-1)">��������� �����</a></p>');
     include ('footer.inc');
     exit;
  }
  $expired = sprintf("%4d-%02d-%02d",$_POST['year2'],$_POST['month2'],$_POST['day2']);
  // �������� ������
  $q = 'update contracts set number="'.$_POST['number'] . '", signed="' . $signed. '", expired="' . $expired . '", age_group='.$_POST['age_group'].' where contract='.$_POST['contract'];
  if (!$db->query($q)) die ('<p style="color:red">���������� ������ �� ���������! '. $db->error. '</p>');
  print ('<p>������ ������� �������.</p><p><a href="contract.php?contract='.$_POST['contract'].'">��������� �� �������� ���������</a></p>');
  include ('footer.inc');
  exit;
}
//
// �������� ������ � ����� ��������� � ��
//
if (isset($_POST['pat_id']) && is_numeric($_POST['pat_id'])) // �������� ������ ��� ����������
{
  if (!(
  //isset($_POST['number']) &&
  isset($_POST['year1']) && isset($_POST['month1']) && isset($_POST['day1'])
        && isset($_POST['year2']) && isset($_POST['month2']) && isset($_POST['day2'])))
     die ('Not enough data!');
  if (!isset($_POST['number'])) $_POST['number']=1;
  // ����������� ����
  if (!(is_numeric($_POST['year1']) && $_POST['year1']>1900 && is_numeric($_POST['month1']) && $_POST['month1']>0 && $_POST['month1']<13
        && is_numeric($_POST['day1']) && $_POST['day1']>0 && $_POST['day1']<32))
  {
     print ('<p>��������, ���� ���������� ��������� ������� �������!</p><p><a href="javascript:history.go(-1)">��������� �����</a></p>');
     include ('footer.inc');
     exit;
  }
  $signed = sprintf("%4d-%02d-%02d",$_POST['year1'],$_POST['month1'],$_POST['day1']);
  if (!(is_numeric($_POST['year2']) && $_POST['year2']>1900 && is_numeric($_POST['month2']) && $_POST['month2']>0 && $_POST['month2']<13
        && is_numeric($_POST['day2']) && $_POST['day2']>0 && $_POST['day2']<32))
  {
     print ('<p>��������, ���� �������� ��������� ������ �������!</p><p><a href="javascript:history.go(-1)">��������� �����</a></p>');
     include ('footer.inc');
     exit;
  }
  $expired = sprintf("%4d-%02d-%02d",$_POST['year2'],$_POST['month2'],$_POST['day2']);
  // ��������� ������
  $q = 'insert into contracts values (NULL, ' . $_SESSION['doctor_id'] . ', ' . $_POST['pat_id'] . ', "' . $_POST['number'] . '", "' . $signed. '", "' . $expired . '", 1, '.$_POST['age_group'].')';
//  print ($q); // ��� �������
  if (!$db->query($q)) die ('<p><font color="red">���������� ������ �� ���������!</font></p>');
  print ('<p>������ ������� �������.</p><p><a href="doctor.php">��������� �� �������� �������</a></p><p><a href="patient.php?pat_id='.$_POST['pat_id'].'">��������� �� �������� ��������</a></p>');
  include ('footer.inc');
  exit;
}
//
// ����� �������� ������ ���������
//
if (isset($_GET['pat_id']) && is_numeric($_GET['pat_id']) && !isset($_GET['valid']) && !isset($_GET['delete']))
{
  $pat_id = $_GET['pat_id'];
  // ������ ������ � ����������� ��������
  $res = $db->query ('select surname, name, lastname from patients where pat_id='.$pat_id);
  if (!$res || !$res->num_rows) die ('������ �������� �� �������! ������������ ������ ������ �������.');
  $row = $res->fetch_row();
  print ('<h1>����������� ���������</h1>');
  print ("<p>�������: $row[0] $row[1] $row[2] (<a href='pat_all.php'>������� �������</a>)</p>");
  $res->free();
  // ������ ������ �������
  $res = $db->query ('select surname, name, lastname from doctors where doctor_id='.$_SESSION['doctor_id']);
  if (!$res || !$res->num_rows) die ('������ ������� �� �������! ������ � ������ �������.');
  $row = $res->fetch_row();
  print ("<p>������: $row[0] $row[1] $row[2] (<a href='doctor.php?logout=1'>����� ��� ������ ������</a>)</p>");
  $res->free();
  // ��������� ������� ����
  date_default_timezone_set ("Europe/Minsk"); // ����� �������� ��������� � ��������� � ���������� ������������ ����
  $curdate = getdate(); // ���. ������ �������� ������� ����
  $day=$curdate['mday'];
  $mon=$curdate['mon'];
  $year=$curdate['year'];
  $nyear=$year+1;
  // ����� �����
  print <<<END
<p><form method=post>
<input type='hidden' name='pat_id' value='$pat_id'>
<table class="light">
<!--
<tr><td class="left">����� ���������:<td><input type='text' name='number' size='10' maxlength='30' value=''>
-->
<tr><td class="left">���� ����������:<td><input type='text' name='day1' size='1' maxlength='2' value='$day'>.<input type='text' name='month1' size='1' maxlength='2' value='$mon'>.<input type='text' name='year1' size='4' maxlength='4' value='$year'>
<tr><td class="left">������������ ��:<td><input type='text' name='day2' size='1' maxlength='2' value='$day'>.<input type='text' name='month2' size='1' maxlength='2' value='$mon'>.<input type='text' name='year2' size='4' maxlength='4' value='$nyear'>
<tr><td class="left">���������� ������:</td><td><select name="age_group">
<option value="0">�� 1 ����</option>
<option value="1">1-3 ����</option>
<option value="2">3-6 ���</option>
<option value="3">������ 6 ���</option>
</select>
</table>
<input type="submit" class="button"value="������ ������"></form>&nbsp;<input type="button" value="������" onClick="javascript:history.go(-1)">
END;
include('footer.inc');
exit;
}
//
// ���������/����������� ���������
//
if (isset($_GET['valid']) && is_numeric($_GET['valid']))
{
  if (!isset($_GET['contract']) || !isset($_GET['pat_id'])) die ('�� ������ ����� ��������� �/��� �������� (������ ������ �������?).');
  if (!$db->query ('update contracts set valid='.$_GET['valid'].' where contract='.$_GET['contract'])) die ('������ ��������� ������ � ���������!');
  /* obsolete - deleted
  if ($_GET['valid']=='0')
  { // ������� ������ � ��������� � ��������
      if (!$db->query ('update patients set contract=0 where pat_id='.$_GET['pat_id'])) die ('������ ��������� ������ � ��������� � ��������!');
  }
  else
  {
      if (!$db->query ('update patients set contract='.$_GET['contract'].' where pat_id='.$_GET['pat_id'])) die ('������ ��������� ������ � ��������� � ��������!');
  }
  */
  print ('<p>������ � ��������� �������� �������.</p><p><a href="contract.php?contract='.$_GET['contract'].'">��������� �� �������� ���������</a></p>');
  include('footer.inc');
  exit;
}
//
// �������� ���������
//
if (isset($_GET['delete']))
{
  if (!isset($_GET['contract']) || !isset($_GET['pat_id'])) die ('�� ������ ����� ��������� �/��� �������� (������ ������ �������?).');
  if (!$db->query ('delete from contracts where contract='.$_GET['contract'])) die ('������ �������� ������ � ���������!');
  /* obsolete
  // ������� ������ � ��������� � ��������
  if (!$db->query ('update patients set contract=0 where pat_id='.$_GET['pat_id'])) die ('������ ��������� ������ � ��������� � ��������!');
  */
  print ('<p>������ � ��������� ������� �������.</p><p><a href="doctor.php">��������� �� �������� �������</a></p>');
  include('footer.inc');
  exit;
}
//
// ����� ���������� � ���������
//
if (isset($_GET['contract']) && is_numeric($_GET['contract']) && !isset($_GET['update']) && !isset($_GET['delete']))
{
  $contract=$_GET['contract'];
  $res=$db->query('select * from contracts where contract = '.$contract);
  if (!$res || !$res->num_rows) die ('����� ��������� ������� (������ ������� �������?).');
  if (!$row=$res->fetch_object()) die ('������ ��������� �����������.');
  $row->valid?$valid='��':$valid='���';
  $row->signed=join('.',array_reverse(explode ('-',$row->signed)));
  $row->expired=join('.',array_reverse(explode ('-',$row->expired)));
  if (!isset($row->age_group)) $row->age_group=0;
  $res2=$db->query('select * from doctors where doctor_id='.$row->doctor_id);
  if (!$res2 || !$res2->num_rows) die ('������ � ������� � ��������� �������.');
  $dr = $res2->fetch_object();
  $res3=$db->query('select * from patients where pat_id='.$row->pat_id);
  if (!$res3 || !$res3->num_rows) die ('������ � �������� � ��������� �������.');
  $pr = $res3->fetch_object();
  $pr->birth=join('.',array_reverse(explode ('-',$pr->birth)));
  $agegrp = array ('�� 1 ����','1-3 ����','3-6 ���','������ 6 ���');
  print <<<END
<h1>���������� � ����������� �������� (���������)</h1>
<p><table class="light">
<!--<tr><td class="left">����� ��������:<td>$row->number</td></tr>-->
<tr><td class="left">���� ����������:<td>$row->signed</td></tr>
<tr><td class="left">������������ ��:<td>$row->expired</td></tr>
<tr><td class="left">�������:<td>$pr->surname $pr->name $pr->lastname, ���� ����. $pr->birth (<a href='patient.php?pat_id=$pr->pat_id'>������� �������� ��������</a>)</td></tr>
<tr><td class="left">���������� ������:</td><td>{$agegrp[$row->age_group]}</td></tr>
<tr><td class="left">������:<td>$dr->surname $dr->name $dr->lastname</td></tr>
<tr><td class="left">�������� �������:<td>$valid (<a href='contract.php?contract=$contract&pat_id=$row->pat_id&valid=0'>��������������</a>&nbsp;|&nbsp;<a href='contract.php?contract=$contract&pat_id=$row->pat_id&valid=1'>������������</a>)</td></tr>
</table></p>
<p><input type='button' class="button" value='������������� ��������' onclick="javascript:document.location='contract.php?contract=$contract&update=1'"></p>
<p><input type='button' class="button" value='������� ������ � ��������� (�� �������������)' onclick="javascript:document.location='contract.php?contract=$contract&pat_id=$row->pat_id&delete=1'"></p>
<p><a href='doctor.php'>��������� �� �������� �������</a></p>
<p><a href="patient.php?pat_id=$row->pat_id">��������� �� �������� ��������</a></p>
END;
  include('footer.inc');
  exit;
}
//
// ����� ����� ��������� ���������
//
if (isset($_GET['contract']) && is_numeric($_GET['contract']) && isset($_GET['update']))
{
  $contract=$_GET['contract'];
  $res=$db->query('select * from contracts where contract = '.$contract);
  if (!$res || !$res->num_rows) die ('����� ��������� ������� (������ ������� �������?).');
  if (!$row=$res->fetch_object()) die ('������ ��������� �����������.');
  $row->valid?$valid='��':$valid='���';
  $agegrp=array ('','','','');
  if (!isset($row->age_group)) $row->age_group=0;
  $agegrp[$row->age_group]=' selected';
  list ($year1, $month1, $day1) = explode ('-',$row->signed);
  list ($year2, $month2, $day2) = explode ('-',$row->expired);
  $res2=$db->query('select * from doctors where doctor_id='.$row->doctor_id);
  if (!$res2 || !$res2->num_rows) die ('������ � ������� � ��������� �������.');
  $dr = $res2->fetch_object();
  $res3=$db->query('select * from patients where pat_id='.$row->pat_id);
  if (!$res3 || !$res3->num_rows) die ('������ � �������� � ��������� �������.');
  $pr = $res3->fetch_object();
  $pr->birth=join('.',array_reverse(explode ('-',$pr->birth)));
  print <<<END
<h1>��������� ������ � ����������� �������� (���������)</h1>
<p><form method='post'>
<input type='hidden' name='contract' value='$row->contract'>
<input type='hidden' name='pat_id' value='$row->pat_id'>
<input type='hidden' name='doctor_id' value='$row->doctor_id'>
<table class="light">
<!--<tr><td class="left">����� ���������:<td><input type='text' name='number' size='10' maxlength='30' value='$row->number'>-->
<tr><td class="left">���� ����������:<td><input type='text' name='day1' size='1' maxlength='2' value='$day2'>.<input type='text' name='month1' size='1' maxlength='2' value='$month1'>.<input type='text' name='year1' size='4' maxlength='4' value='$year1'>
<tr><td class="left">������������ ��:<td><input type='text' name='day2' size='1' maxlength='2' value='$day2'>.<input type='text' name='month2' size='1' maxlength='2' value='$month2'>.<input type='text' name='year2' size='4' maxlength='4' value='$year2'>
<tr><td class="left">�������:<td>$pr->surname $pr->name $pr->lastname, ���� ����. $pr->birth (<a href='patient.php?pat_id=$pr->pat_id'>������� �������� ��������</a>)
<tr><td class="left">���������� ������:</td><td><select name="age_group">
<option value="0" {$agegrp[0]}>�� 1 ����</option>
<option value="1" {$agegrp[1]}>1-3 ����</option>
<option value="2" {$agegrp[2]}>3-6 ���</option>
<option value="3" {$agegrp[3]}>������ 6 ���</option>
</select>
<tr><td class="left">������:<td>$dr->surname $dr->name $dr->lastname
</table></p>
<p><input class="button" type='submit' value='�������� ������ ���������'></p>
</form>
END;
  include('footer.inc');
  exit;
}
?>