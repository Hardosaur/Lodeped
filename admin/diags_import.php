<?php
//
// ������/������� ������ � ���������
//
require('../../settings.php');
require('../auth.php');
require ('../access.inc');
check_access_level (0);
include('../header.inc');
print ('<h1>������� �������/�������� ���� ������ ���������</h1>');
if (!isset($_GET['mode']))
{ // ����� ������� �� ����� ����: �������� ��������� ��� ������ ��� ���������
?>
  <p>�������� �����:
  <ul><li><a href="diags_import.php?mode=import1">�������� ������ �� ��������� ���������.</a></li>
  <li><a href="diags_import.php?mode=import2">�������� ������ �� ������������ ������� �������� ��������.</a></li>
  <li><a href="diags_import.php?mode=export1">�������� ������ �� ��������� ���������.</a></li>
  <li><a href="diags_import.php?mode=export2">�������� ������ �� ������������ ������� �������� ��������.</a></li>
  </ul></p>
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
      if (!$db->query ('truncate table diag_names')) die ('���������� ������� ������ ������! ������: '.$db->error);
      // ������ ����� � ��������� � ����
      $count=0;
      foreach ($data as $line)
      {
          $count++;
          list ($num,$text) = explode (';',$line);
          rtrim($text);
          if (!is_numeric($num) || !strlen($text)) die ("������ � ������ �$count: $line");
          $query = "insert into diag_names values ($num, '$text')";
          if (!$db->query($query)) die ("������ ���������� ������: $num, '$text'");
      }
      print ("<p>������ ������� �������. ��������� $count ���������. <a href='diags_import.php'>��������� � ������ ��������</a></p>");
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
if ($_GET['mode']=='import2')
{
  if (isset($_POST['data']))
  {
      $data = explode("\n",$_POST['data']); // ������� ��������� ������
      if (!count($data)) die ('������ � ���������� ������! ��� �����.');
      // ������ ������ ������
      include ('../connect.inc');
      if (!$db->query ('truncate table diag_data')) die ('���������� ������� ������ ������! ������: '.$db->error);
      // ������ ����� � ��������� � ����
      $count=0;
      foreach ($data as $line)
      {
          $count++;
//          $line=$line."\n";
          list ($num,$text) = sscanf ($line,"%d;%[^\n]\n");
          if (ord($text{strlen($text)-1})==13) $text=substr($text,0,strlen($text)-1);
          if (!is_numeric($num) || !strlen($text)) die ("������ � ������ �$count: $line");
          $query = "insert into diag_data values ($num, '$text')";
          if (!$db->query($query)) die ("������ ���������� ������: $num, '$text'");
      }
      print ("<p>������ ������� �������. ��������� $count ���������. <a href='diags_import.php'>��������� � ������ ��������</a></p>");
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
if ($_GET['mode']=='export1')
{
  include ('../connect.inc');
  $res = $db->query ('select * from diag_names');
  if (!$res || !$res->num_rows) die ('������ ������ ����: '.$db->error);
  print ('<p>���������� ������ ���� �������� ���������:<br><textarea cols="150" rows="50">');
  while ($row=$res->fetch_object())
  {
      print ("{$row->diag_id};{$row->diag_name}\n");
  }
  print ('</textarea><br> <a href="diags_import.php">��������� � ������ ��������</a></p>');
}
if ($_GET['mode']=='export2')
{
  include ('../connect.inc');
  $res = $db->query ('select * from diag_data');
  if (!$res || !$res->num_rows) die ('������ ������ ����: '.$db->error);
  print ('<p>���������� ������ ���� ������ �� ���������� ��� ���������:<br><textarea cols="150" rows="50">');
  while ($row=$res->fetch_object())
  {
      print ("{$row->diag_id};{$row->data}\n");
  }
  print ('</textarea><br> <a href="diags_import.php">��������� � ������ ��������</a></p>');
}
include ('../footer.inc');
?>

