<?php
//
// DEP_EDIT.PHP
// �������������� ������ ������������� (���������)
//
require('../../settings.php');
require('../auth.php');
require ('../access.inc');
check_access_level (0);
require('../connect.inc');
$WINDOW_TITLE = '���������';
require('../header.inc');
// ------------------------------------------------------
print ('<h1>���������</h1>');
if (isset($_POST['edit'])) // ��������� ����� �������� ���������
{
  if (is_numeric($_POST['edit']) && isset($_POST['title']) && strlen ($_POST['title']) && strlen ($_POST['title'])<=20)
  {
      $query = 'update departments set title="'.$db->real_escape_string($_POST['title']).'" where dep_id='.$_POST['edit'];
      if (!$db->query($query)) print ('<p style="color: red">��������! ��������� ����� �������� ��������� �� �������! '.$db->error.'</p>');
  }
  else print ('<p style="color: red">��������! ������ �������� �������� ���������! ������� �������? (����� 20 ��������)</p>');
}
if (isset($_POST['add'])) // ��������� ����� �������� �������
{
  if (isset($_POST['title']) && strlen ($_POST['title']) && strlen ($_POST['title'])<=20 )
  {
      $query = 'insert into departments values (NULL, "'.$db->real_escape_string($_POST['title']).'")';
      if (!$db->query($query)) print ('<p style="color: red">��������! �������� ����� �������� ��������� �� �������! '.$db->error.'</p>');
  }
  else print ('<p style="color: red">��������! ������ �������� �������� ������ ���������! ������� �������? (����� 20 ��������)</p>');
}
if (isset($_GET['delete']))
{
  if (is_numeric($_GET['delete']))
  {
      $res = $db->query('select doctor_id from doctors where dep_id='.$_GET['delete']);
      if ($res && $res->num_rows)
      {
          print ('<p style="color: red">��������! ������� ��������� ������, ��� ��� � ���� ��������� '.$res->num_rows.' ��������!</p>');
          $res->free();
      }
      elseif (!$db->query('delete from departments where dep_id='.$_GET['delete'])) print ('<p style="color: red">��������! ������� ��������� �� �������! '.$db->error.'</p>');
  }
  else print ('<p style="color: red">��������! ������ �������� ������ ���������!</p>');
}
// --------------------
$res=$db->query ('select * from departments');
if ($res && $res->num_rows)
{
  while ($row = $res->fetch_object())
  {
      print ('<form method="post" action="dep_edit.php"><input type="hidden" name="edit" value="'.$row->dep_id.'"/><input type="text" style="border: 1" size="20" name="title" maxlength="20" value="'.$row->title.'"/>');
      print ('<img class="button" src="../del.png" onclick="if (confirm(\'�� ������������� ������ ������� ��������� '.$row->title.'?\')) document.location=\'dep_edit.php?delete='.$row->dep_id.'\'"/></form><br>'."\n");
  }
  $res->free();
}
print ('<p>�������� ����� ���������: <form method="post"><input type="hidden" name="add" value="1"/><input type="text" size="20" maxlength="20" name="title" value=""/>&nbsp;<input class="button" type="submit" value="��������"></form></p>');
require('../footer.inc');
?>

