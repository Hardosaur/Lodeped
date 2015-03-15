<?php
//
// ������/������� ������� ����� �������
//
require('../../settings.php');
require('../auth.php');
require ('../access.inc');
check_access_level (0);
include('../header.inc');
print ('<h1>������� �������/�������� ������� ����� �������</h1>');
if (!isset($_GET['mode']))
{
?>
  <p>�������� �����:
  <p>1. <a href="osm_import.php?mode=import1">�������� ���� ����� ����� �������.</a></p>
  <p>2. <a href="osm_import.php?mode=export1">�������� ���� ����� ����� �������.</a></p>
  <!--
  <p><form method="get"><input type="hidden" name="mode" value="export1">
  2. ��������. ����� ���� �������: <input name="osm_type" value="" size="3"> <input type="submit" value="���������">
  </form></p>
  <p><form method="get"><input type="hidden" name="mode" value="import2">
  -->
 <?php
    include('../footer.inc');
    exit;
}
if ($_GET['mode']=='import1')
{
  if (isset($_POST['data']))
  {
/*
      if (!isset($_POST['osm_type']) || !is_numeric($_POST['osm_type']) || !isset($_POST['description']) || !strlen($_POST['description'])) die ('������������ ����������!');
      $osm_type = $_POST['osm_type'];
*/
      // ������� ��������� ������ � ������
      $data = explode("\n",$_POST['data']);
      if (!count($data)) die ('������ � ���������� ������! ��� �����.');
      // ������ ������ ������
      include ('../connect.inc');
/*
      if (!$db->query ('delete from osm_template where osm_type='.$osm_type)) die ('���������� ������� ������ ������! ������: '.$db->error);
      // ������� �������� �������
      $res=$db->query('select * from osm_types where osm_type = '.$osm_type);
      if ($res && $res->num_rows)
      {
          if (!$db->query('update osm_types set description="'.$_POST['description'].'" where osm_type='.$osm_type)) die ('���������� �������� �������� �������! ������: '.$db->error);
          $res->free();
      }
      else // ��������� ����� ��� �������
      {
          if (!$db->query('insert into osm_types values ('.$osm_type.', "'.$_POST['description'].'")')) die ('���������� �������� �������� �������! ������: '.$db->error);
      }
*/
      if (!$db->query ('truncate table osm_fields')) die ('������ ������� ������� ����� �������! '.$db->error);
      // ������ ����� � ��������� � ����
      $count=0; // c������ �����
      $count2=0; // ������� �����
      $select=$table=0; // ����� ��������� ������ � �������
      $sel_value='';
      $sel_line=0;
      $f=array();
      $query = 'insert into osm_fields values ';
      foreach ($data as $line)
      {
          $count++;
          if ($line{0}==';') continue; // ��������� �����������
          $line=trim($line); // �������� ��������� \r
          if ($select) // ������������ ������
          {
              if ($line{0}=='*') // ������ ����� ������
              {
                  $query.='"'.$sel_value.'")';
                  $select=0;
                  $sel_value='';
                  continue; // foreach
              }
              else
              {
                  if (strlen($sel_value)) $sel_value.='|';
                  $sel_value.=$line;
                  continue; // foreach
              }
          }
          elseif ($table) // ������������ ������
          {
              if ($line{0}=='*' && (strlen($line)>1 && $line{1}=='*') ) // ������ ����� ������
              {
                  $query.='"'.$sel_value.'")';
                  $table=0;
                  $sel_value='';
                  continue; // foreach
              }
              else
              {
                  if (strlen($sel_value)) $sel_value.='|';
                  $sel_value.=$line;
                  continue; // foreach
              }
          }
          if (count($f)) $query.=', '; // �� ������ �������
          unset($f);
          $f = explode (';', $line);
//          print_r($f); print ('<br>');
          $query.='(NULL, '.++$count2.', ';
          // type
          if (isset($f[0]) && strlen ($f[0])) $query.='"'.$f[0].'",'; else die ("������ ������� 1 (�� ������ ��� ����)! ������ $count: $line.");
          if ($f[0] == 'select' || $f[0] == 'multi') { $select=1; $sel_value=''; $sel_line=$count;}
          if ($f[0] == 'table') { $table=1; $sel_value='';  $sel_line=$count;}
          // name
          if (isset($f[1]) && strlen ($f[1])) $query.='"'.$f[1].'", '; else die ("������ ������� 2 (�� ������� �������� ����)! ������ $count: $line.");
          // suffix, ��� ��������������
          if (isset($f[2]) && strlen($f[2])) $query.='"'.$f[2].'", '; else $query.='NULL, ';
          if (!$select && !$table) $query.=' NULL)';
      }
      if ($select || $table) die ('������ ��������� ������! �� �������� ������ � ������ '.$sel_line);
      print ($query);
      if (!$db->query($query)) die ('<p style="color:red">������ ���������� ������: '.$db->error.'</p>');
      print ("<p>������ ������� �������. ���������� $count �����. <a href='osm_import.php'>��������� � ���������� ��������</a></p>");
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
  if (isset($_POST['data']) && strlen($_POST['data']))
  {
      if (!isset($_GET['osm_type']) || !is_numeric($_GET['osm_type'])) die ('�� ������� ����� ���� �������!');
      $osm_type = $_GET['osm_type'];
      include('../connect.inc');
      $data = $db->real_escape_string($_POST['data']);
      $res=$db->query('select * from osm_print where osm_type = '.$osm_type);
      // ��������, ���� �� ����� ������ � ����
      if ($res && $res->num_rows)
      {
          $res->free();
          if (!$db->query('update osm_print set template="'.$data.'" where osm_type='.$osm_type)) die ('���������� �������� ������! ������: '.$db->error);
      }
      else // ��������� ����� ��� �������
      {
          $query='insert into osm_print values ('.$osm_type.', "'.$data.'")';
          if (!$db->query($query)) die ('���������� �������� ������! ������: '.$db->error);
      }
      print ('<p>������ ������� �������.</p> <a href="osm_import.php">��������� � ���������� ��������</a>');
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
  if (!isset($_GET['osm_type']))
  {
?>
   <form method='get'>
   ����� (���) �������: <input type='text' name='osm_type' size='5'/><br>
   <input type='submit' value='��������� ����'/>
   </form>
<?php
  }
  else
  {
      if (!is_numeric($_GET['osm_type'])) die ('������������ ����������!');
      $osm_type=$_GET['osm_type'];
      include ('../connect.inc');
      $res = $db->query ('select template from osm_template where osm_type='.$osm_type);
      if (!$res || !$res->num_rows) die ('������ ������ ����: '.$db->error);
      print ('<p>���������� ������ ������� ������� (��� '.$osm_type.'):<br><textarea cols="150" rows="50">');
      while ($row=$res->fetch_object())
      {
          $line = "\n"; // ��������� � �������� �������
          $select='';
          if ($row->type=='select') $select=$row->value;
          else if ($row->value) $line=';'.$row->value.$line;
          if ($row->size) $line=';'.$row->size.$line;
          if ($row->suffix) $line=';'.$row->suffix.$line;
          if ($row->name) $line=';'.$row->name.$line; else die ('������ � ����! ������� '.$row->id);
          if ($row->type) $line=';'.$row->type.$line; else die ('������ � ����! ������� '.$row->id);
          if ($row->parent_id) $line=';'.$row->parent_id.$line; else $line=';'.$line;
          if ($row->id) $line=$row->id.$line; else die ('������ � ����! ������� '.$row->id);
          print $line;
          if ($row->type=='select' || $row->type=='multi')
          {
              $lines=explode(';',$row->value);
              foreach ($lines as $line) print $line."\n";
          }
      }
      print ('</textarea><br> <a href="osm_import.php">��������� � ���������� ��������</a></p>');
  }
}
if ($_GET['mode']=='export2')
{
  if (!isset($_GET['osm_type']))
  {
?>
   <form method='get'>
   ����� (���) �������: <input type='text' name='osm_type' size='5'/><br>
   <input type='submit' value='��������� ����'/>
   </form>
<?php
  }
  else
  {
      if (!is_numeric($_GET['osm_type'])) die ('������������ ����������!');
      $osm_type=$_GET['osm_type'];
      include ('../connect.inc');
      $res = $db->query ('select template from osm_print where osm_type='.$osm_type);
      if (!$res || !$res->num_rows) die ('������ ������ ����: '.$db->error);
      $row=$res->fetch_row();
      print ('<p>���������� ������ ������� ������� (��� '.$osm_type.'):<br><textarea cols="150" rows="50">'.$row[0].'</textarea><br> <a href="osm_import.php">��������� � ���������� ��������</a></p>');
  }
}
include ('../footer.inc');
?>

