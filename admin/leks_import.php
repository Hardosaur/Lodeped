<?php
//
// ������/������� ������ � ����������
//
require('../../settings.php');
require('../auth.php');
require ('../access.inc');
check_access_level (0);
include('../header.inc');
print ('<h1>������� �������/�������� ���� ������ ��������</h1>');
if (!isset($_GET['mode']))
{ // ����� ������� �� ����� ����: �������� ��������� ��� ������ ��� ���������
?>
  <p>�������� �����:</p>
  <p>1. <a href="leks_import.php?mode=import1">�������� ������ �� ��������� ��������.</a></p>
  <p>2. <a href="leks_import.php?mode=import3">�������� ������ �� ������ ������� ��������.</a></p>
  <p><form method="get"><input type="hidden" name="mode" value="import2">
  3. �������� ������ �������� ����� ������ �����������. ����� �������: <input name="tab_id" value="" size="3"> <input type="submit" value="���������">
  </form></p>

  <p>4. <a href="leks_import.php?mode=export1">�������� ������ �� ��������� ��������.</a></p>
  <p>5. <a href="leks_import.php?mode=export3">�������� ������ �� ������ ������� ��������.</a></p>
  <form method="get"><input type="hidden" name="mode" value="export2">
  6. �������� ������. ����� �������: <input name="tab_id" value="" size="3"> <input type="submit" value="���������"></form></p>
<?php
    include('../footer.inc');
    exit;
}
if ($_GET['mode']=='import1')
{
  if (isset($_POST['data']))
  {
      $data = explode("\n",$_POST['data']); // ������� ��������� ������
      if (!count($data)) die ('������ � ���������� ������! ��� �����.');
      // ������ ������ ������
      include ('../connect.inc');
      if (!$db->query ('truncate table lek_names')) die ('���������� ������� ������ ������! ������: '.$db->error);
      // ������ ����� � ��������� � ����
      $count=0;
      foreach ($data as $line)
      {
          $count++;
          $line=rtrim($line);
          list ($id,$lname,$rname) = explode (';',$line);
          if (!is_numeric($id) || !strlen($lname) || !strlen($rname)) die ("������ � ������ �$count: $line");
          $lname=addslashes($lname);
          $rname=addslashes($rname);
          $query = "insert into lek_names values ($id, '$lname', '$rname')";
          if (!$db->query($query)) die ("������ ���������� ������: $id, '$lname', '$rname'");
      }
      print ("<p>������ ������� �������. ��������� $count ��������. <a href='leks_import.php'>��������� � ������ ��������</a></p>");
  }
  else // ������ �� ��������, ����� ������� ����� �����
  {
?>
   <form method='post'>
   <textarea name='data' cols='150' rows='50'></textarea><br>
   <input type='submit' value='������ ������'/>
   </form>
<?php
   }
}
// ---------------------------------------------------------------------------------
if ($_GET['mode']=='import2')
{
  if (!isset($_GET['tab_id']) || !is_numeric($_GET['tab_id'])) die ('�� ������ ����� �������!');
  $tab_id=$_GET['tab_id'];
  if (isset($_POST['data']) && strlen($_POST['data']))
  {
      $data = explode("\n",$_POST['data']); // ������� ��������� ������
      if (!count($data)) die ('������ � ���������� ������! ��� �����.');
      $tab_name = array_shift($data);
      $tab_name=rtrim($tab_name);
      // ������ ������ ������
      include ('../connect.inc');
      if (!$db->query ('delete from lek_data where tab_id='.$_GET['tab_id'])) die ('���������� ������� ������ ������! ������: '.$db->error);
      foreach ($data as $key => $val) $data[$key]=rtrim($val);
      $list = implode (';',$data);
      $query = "insert into lek_data values ($tab_id, '$tab_name', '$list')";
      if (!$db->query($query)) die ("������ ���������� ������ ($tab_id, '$tab_name', '$list')! $db->error");
      print ("<p>������ ������� �������. <a href='leks_import.php'>��������� � ������ ��������</a></p>");
  }
  else // ������ �� ��������, ����� ������� ����� �����
  {
?>
   <form method='post'>
   <textarea name='data' cols='150' rows='50'></textarea><br>
   <input type='submit' value='������ ������'/>
   </form>
<?php
   }
}
// ---------------------------------------------------------------------------------------
if ($_GET['mode']=='import3')
{
  if (isset($_POST['data']))
  {
      $data = explode("\n",$_POST['data']); // ������� ��������� ������
      if (!count($data)) die ('������ � ���������� ������! ��� �����.');
      // ������ ������ ������
      include ('../connect.inc');
      if (!$db->query ('truncate table lek_forms')) die ('���������� ������� ������ ������! ������: '.$db->error);
      // ������ ����� � ��������� � ����
      $count=0;
      foreach ($data as $line)
      {
          $count++;
          $line=rtrim($line);
          list ($id,$lname,$rname) = explode (';',$line);
          if (!is_numeric($id) || !strlen($rname)) die ("������ � ������ �$count: $line");
          if (isset($lname)) $lname=addslashes($lname); else $lname='';
          $rname=addslashes($rname);
          $query = "insert into lek_forms values ($id, '$lname', '$rname')";
          if (!$db->query($query)) die ("������ ���������� ������: $id, '$lname', '$rname'");
      }
      print ("<p>������ ������� �������. ��������� $count ���� ������� ��������. <a href='leks_import.php'>��������� � ������ ��������</a></p>");
  }
  else // ������ �� ��������, ����� ������� ����� �����
  {
?>
   <form method='post'>
   <textarea name='data' cols='150' rows='50'></textarea><br>
   <input type='submit' value='������ ������'/>
   </form>
<?php
   }
}
// ----------------------------------------------------------------------------------
if ($_GET['mode']=='export1')
{
  include ('../connect.inc');
  $res = $db->query ('select * from lek_names');
  if (!$res || !$res->num_rows) die ('������ ������ ����: '.$db->error);
  print ('<p>���������� ������ ���� �������� ��������:<br><textarea cols="150" rows="50">');
  while ($row=$res->fetch_object())
  {
      print ("{$row->lek_id};{$row->lname};{$row->rname}\n");
  }
  print ('</textarea><br> <a href="leks_import.php">��������� � ������ ��������</a></p>');
}
// ----------------------------------------------------------------------------------
if ($_GET['mode']=='export2')
{
  if (!isset($_GET['tab_id']) || !is_numeric($_GET['tab_id'])) die ('�� ������ ����� �������!');
  include ('../connect.inc');
  $res = $db->query ('select * from lek_data where tab_id='.$_GET['tab_id']);
  if (!$res || $res->num_rows!=1) die ('������ ������ ��� ��������� ������� ����: '.$db->error);
  print ('<p>���������� ������ ���� ������ �� ������ ����� ����������� (��������):<br><textarea cols="150" rows="50">');
  $row=$res->fetch_object();
  print ("$row->tab_name\n");
  $list=explode(';',$row->list);
  foreach ($list as $line)
  {
      print ("$line\n");
  }
  print ('</textarea><br> <a href="leks_import.php">��������� � ������ ��������</a></p>');
}
// ------------------------------------------------------------------------------------
if ($_GET['mode']=='export3')
{
  include ('../connect.inc');
  $res = $db->query ('select * from lek_forms');
  if (!$res || !$res->num_rows) die ('������ ������ ����: '.$db->error);
  print ('<p>���������� ������ ���� ���� ������� ��������:<br><textarea cols="150" rows="50">');
  while ($row=$res->fetch_object())
  {
      print ("{$row->form_id};{$row->lname};{$row->rname}\n");
  }
  print ('</textarea><br> <a href="leks_import.php">��������� � ������ ��������</a></p>');
}

include ('../footer.inc');
?>

