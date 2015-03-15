<?php
//
// ADMIN.PHP
// ������ �����������������
//
require('../../settings.php');
require('../auth.php');
require('../connect.inc');
$WINDOW_TITLE = '�����������������';
require ('../access.inc');
check_access_level (1);
require('../header.inc');
// ------------------------------------------------------
if (isset($_GET['delete_type'])) // ������� ��� �������
{
  if (is_numeric($_GET['delete_type']))
  {
      $res = $db->query ('select osm_id from osm_info where osm_type = '.$_GET['delete_type']);
      if ($res && ($num_rows=$res->num_rows)) print ('<p style="color: red">��������! ���������� ������� ��������� ��� �������, ��������� �� ��� ������ ������� '.$num_rows.' ���������� ������� ���������!');
      else
      {
         if (!$db->query ('delete from osm_types where osm_type = '.$_GET['delete_type'])) print ('<p style="color: red">��������! ������� ��������� ��� ������� �� �������! '.$db->error.'</p>');
      }
  }
  else print ('<p style="color: red">��������! �������� ��� ���������!</p>');
}
// ------------------------------------------------------
if (isset($_POST['edit_type'])) // ��������� ����� �������� �������
{
  if (is_numeric($_POST['edit_type']) && isset($_POST['description']) && strlen ($_POST['description']))
  {
      $query = 'update osm_types set description="'.$db->real_escape_string($_POST['description']).'" where osm_type='.$_POST['edit_type'];
      if (!$db->query($query)) print ('<p style="color: red">��������! ��������� ����� �������� ����� ������� �� �������! '.$db->error.'</p>');
  }
  else print ('<p style="color: red">��������! ������ �������� ������ ��������!</p>');
}
// ------------------------------------------------------
if (isset($_POST['add_type'])) // ��������� ����� �������� �������
{
  if (isset($_POST['description']) && strlen ($_POST['description']))
  {
      $query = 'insert into osm_types values (NULL, "'.$db->real_escape_string($_POST['description']).'", NULL, "")';
      if (!$db->query($query)) print ('<p style="color: red">��������! �������� ����� �������� ����� ������� �� �������! '.$db->error.'</p>');
  }
  else print ('<p style="color: red">��������! ������ �������� �������� ������ �������!</p>');
}
// ------------------------------------------------------
if (isset($_POST['edit_access'])) // ��������� ������ ������� � ��������
{
  if (is_numeric($_POST['edit_access']))
  {
     if (!$db->query ('delete from osm_access where osm_type='.$_POST['edit_access'])) print ('<p style="color: yellow">������ �������� ������ �������! '.$db->error.'</p>');
     $query = 'insert into osm_access values ';
     $count=0;
     foreach ($_POST as $key=>$value)
        if (is_numeric($key))
        {
           if ($count) $query.=',';
           $query.='('.$_POST['edit_access'].','.$key.')';
           $count++;
        }
     if ($count) if (!$db->query ($query)) print ('<p style="color: red">��������! �������� ������� ������� � ������� �� �������! '.$db->error.'</p>');
  }
  else print ('<p style="color: red">��������! ������ �������� ����� ������� � ��������!</p>');
}
// ------------------------------------------------------
if (isset($_GET['delete_report'])) // ������� ����� ������
{
  if (is_numeric($_GET['delete_report']))
  {
      $res = $db->query ('select report_id from reports where report_type = '.$_GET['delete_report']);
      if ($res && ($num_rows=$res->num_rows)) print ('<p style="color: red">��������! ���������� ������� ��������� ��� ������, ��������� �� ��� ������ ������� '.$num_rows.' �������!');
      else
      {
         if (!$db->query ('delete from report_types where report_type = '.$_GET['delete_report'])) print ('<p style="color: red">��������! ������� ��������� ��� ������ �� �������! '.$db->error.'</p>');
      }
  }
  else print ('<p style="color: red">��������! �������� ����� ������!</p>');
}
// ------------------------------------------------------
if (isset($_POST['edit_report'])) // ��������� ����� �������� ������
{
  if (is_numeric($_POST['edit_report']) && isset($_POST['title']) && strlen ($_POST['title']))
  {
      $query = 'update report_types set title="'.$db->real_escape_string($_POST['title']).'" where report_type='.$_POST['edit_report'];
      if (!$db->query($query)) print ('<p style="color: red">��������! ��������� ����� �������� ������ �� �������! '.$db->error.'</p>');
  }
  else print ('<p style="color: red">��������! �������� ���������: �� ������� ��������?</p>');
}
// -------------------------------------------------------------
if (isset($_POST['add_report'])) // ��������� ����� �����
{
  if (isset($_POST['title']) && strlen ($_POST['title']))
  {
      $query = 'insert into report_types values (NULL, "'.$db->real_escape_string($_POST['title']).'", "")';
      if (!$db->query($query)) print ('<p style="color: red">��������! �������� ����� ����� �� �������! '.$db->error.'</p>');
  }
  else print ('<p style="color: red">��������! ������ �������� �������� ������ ������!</p>');
}
// -----------------------------------------------------------
// ������ ������ ���������
$res=$db->query ('select * from departments');
if (!$res && !$res->num_rows) die ('������ ��������� ����! ������ ����������.');
while ($row = $res->fetch_object()) $departments[]=$row;
$res->free();
//
// ������ ������� �������
$access=array();
$res=$db->query ('select * from osm_access');
if ($res && $res->num_rows)
{
  while ($row = $res->fetch_object())
        $access[$row->osm_type][$row->dep_id]=1;
}
$res->free();
//
// ������� ������ ��������
print ('<h2>����� ��������</h2>');
print ('<table style="margin-top: -5px"><tr><td colspan="2" width="300">');
foreach ($departments as $dep) print ('<td class="dot">'.$dep->title.'</td>');
print ("<td></td></tr>\n");
$res=$db->query ('select * from osm_types order by description');
if ($res && $res->num_rows)
{
  while ($row = $res->fetch_object())
  {
      print ('<tr><form method="post" action="admin.php"><td style="padding: 1px 3px"><input type="hidden" name="edit_type" value="'.$row->osm_type.'"/><input type="text" style="border: 1" size="60" name="description" value="'.$row->description.'"/>');
      print ('&nbsp;<img src="../del.png" onclick="javascript:if(confirm(\'������� ������� ��������� ��� �������?\'))document.location=\'admin.php?delete_type='.$row->osm_type.'\'"/>&nbsp;</td>');
      print ('<td class="dot" style="padding: 1px 5px"><a href="../osmotr2.php?type='.$row->osm_type.'" target="_blank">������&nbsp;�����</a></td></form>');
      print ('<form method="post" action="admin.php"><input type="hidden" name="edit_access" value="'.$row->osm_type.'">');
      foreach ($departments as $dep)
      {
         print ('<td class="dot"><input class="check" type="checkbox" name="'.$dep->dep_id.'" value="1"');
         if (isset ($access[$row->osm_type][$dep->dep_id])) print ('checked');
         print (' onchange="document.getElementById(\'button'.$row->osm_type.'\').style.display=\'block\'"/></td>');
      }
      print ('<td><input class="button" id="button'.$row->osm_type.'" type="submit" value="���������" style="display:none"/></td></form></tr>'."\n");
  }
  $res->free();
  print ('</table>');
}
?>
<p>�������� ����� ��� �������: <form method="post" action="admin.php"><input type="hidden" name="add_type" value="1"/><input type="text" size="60" name="description" value="(��������)" onfocus="javascript:this.value=''"/>&nbsp;<input class="button" type="submit" value="��������"></form></p>
<input class="button" type="button" value="�������� ����� ����� �������" onclick="window.open('../osmotr2.php','newwin')"/><br>
<?php
if (access_level() == 0) print ('<input class="button" type="button" value="������/������� ����� ����� �������" onclick="window.open(\'osm_import.php\',\'newwin\')"/>');
// -----------------------------------------------
// ������� ������ �������
print ('<h2>������</h2>');
print ('<table style="margin-top: -5px">');
$res=$db->query ('select * from report_types order by title');
if ($res && $res->num_rows)
{
  while ($row = $res->fetch_object())
  {
      print ('<tr><form method="post" action="admin.php"><td style="padding: 1px 3px"><input type="hidden" name="edit_report" value="'.$row->report_type.'"/><input type="text" style="border: 1" size="60" name="title" value="'.$row->title.'"/>');
      print ('&nbsp;<img src="../del.png" onclick="javascript:if(confirm(\'������� ������� ��������� �����?\'))document.location=\'admin.php?delete_report='.$row->report_type.'\'"/>&nbsp;</td>');
      print ('<td class="dot" style="padding: 1px 5px"><a href="edit_report_body.php?report='.$row->report_type.'" target="_blank">������</a></td></form></tr>'."\n");
  }
  $res->free();
  print ('</table>');
}
?>
<p>�������� ����� �����: <form method="post" action="admin.php"><input type="hidden" name="add_report" value="1"/><input type="text" size="60" name="title" value="(��������)" onfocus="javascript:this.value=''"/>&nbsp;<input class="button" type="submit" value="��������"></form></p>
<!-- ��������� ���� -->
<h2>������ � ����������</h2>
<input class="button" type="button" value="��������" onclick="window.open('diags_edit.php','newwin')"/>

<h2>������ � �������������� �����������</h2>
<input class="button" type="button" value="������������� ����������" onclick="window.open('lek_editor.php','newwin')"/>
<input class="button" type="button" value="�������������� ���������" onclick="window.open('otc.php','newwin')"/>
<h2>������ ���������</h2>
<input class="button" type="button" value="������ ������ �������� �� ���������" onclick="window.open('pat_list.php','newwin')"/>
<?php
if (access_level() == 0) print <<<END
<h2>������� � ���������</h2>
<input class="button" type="button" value="�������" onclick="window.open('doctors.php','newwin')"/>
&nbsp;<input class="button" type="button" value="���������" onclick="window.open('dep_edit.php','newwin')"/>
END;
print ('<p><br><a class="small" href="/doctor.php">��������� �� �������� �������</a></p>');
require('../footer.inc');
?>