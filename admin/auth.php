<?php
//
// �������� ������� � ������ (��� �� ������� ������) ������ � ������ ��������������,
// � ����� ����������� � ��������� ������ � ��
//
if (isset($_POST['admin_login']) && isset($_POST['admin_pass']))
{ // �����������, �.�. �����/������ �������� ����� POST
  $db=new mysqli($dbhost,$dbuser,$dbpass,$dbname);
  if (mysqli_connect_errno()) die ('Connect error: '.mysqli_connect_error());
  $login=$db->real_escape_string($_POST['admin_login']);
  $pass=$db->real_escape_string($_POST['admin_pass']);
  $result=$db->query ("select  * from admins where admin_login='$login' and admin_pass='$pass'");
  if (!$result) die ('Query error:'.$db->error);
  if (!$row=$result->fetch_object()) print ('<color=red>����� ��� ������ �������!</color>');
  else
  {
      session_start();
      $_SESSION['admin_login']=$row->admin_login;
      $_SESSION['admin_pass']=$row->admin_pass;
      $result->free();
      $db->close();
      return;
  }
}
// �������� ������ � ������
session_start();
if (!isset($_SESSION['admin_login']))
{ // ������� ����� �����������
  print ("<p align='center'><form method='post'>".
         "����� ��������������:&nbsp;<input type='input' name='admin_login' size='20' maxlength='20'><br>".
         "������ �������:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='input' name='admin_pass' size='20' maxlength='20'<br>".
         "<input type='submit' value='����� � �������'></form>");
  exit;
}
else return;
?>

