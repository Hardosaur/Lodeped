<html>
<head><title>���������� ������ ������������ ���������</title>
<link rel="stylesheet" type="text/css" href="main.css">
<script>
function addCommas()
{
  if (document.forms[0].elements[0].value.charAt(0)!="'")
     document.forms[0].elements[0].value="'"+document.forms[0].elements[0].value+"'"
}
function checkForm()
{
  if (document.forms[0].elements[0].value.length==0 || document.forms[0].elements[1].value.length==0)
  {
      alert ('�� ������� ��� �������� ���������!');
      return false;
  }
  return true;
}
</script>
</head>
<body>
<?php
//
// ���������� ������ ��������� � ������
//
if (!isset($_POST['lname']))
{
?>
<form method="post">
<table align="center" cellpadding="6" cellspacing="0" frame="border" rules="none" width="600" border="1" bordercolor="black">
<col align="right">
<col align="left">
<tr><td>���������&nbsp;��������&nbsp;���������:</td>
<td><input type="text" name="lname" size="30" value="" maxlength="49"/></td>
<tr><td colspan="2" align="left" style="font-size: 9pt">�������� ��������� �� ������ ����������� � ����������� ������ (����. Ibuprofeni);<br>� ������ ���������� �������� - � ������������ ������ � <a href="javascript:addCommas()">��������� ��������</a> (����. 'Bioparox')</td></tr>
<tr><td>�������&nbsp;��������&nbsp;���������:</td>
<td><input type="text" name="rname" size="30" value="" maxlength="49"/></td></tr></table>
<p align="center"><input type="submit" value="��������" onclick="return checkForm()"/></p>
</form>
</body>
</html>
<?php
exit;
}
if (!isset($_POST['rname'])) die ('<p>�� ������� ������� �������� ���������! <a href="lek_new.php">���������</a></p>');
require('../settings.php');
require('auth.php');
include('connect.inc');
$lname=$db->real_escape_string(trim($_POST['lname']));
$rname=$db->real_escape_string(trim($_POST['rname']));
if (!strlen($lname) || !strlen($rname)) die ('<p>�� ������� ������� ��� ��������� �������� ���������! <a href="lek_new.php">���������</a></p>');
// ��������, ��� �� ������ ��������� � ����
$res=$db->query ('select * from lek_names where lname="'.$lname.'" or rname="'.$rname.'"');
if ($res && $res->num_rows) die ('<p>��������� �������� ��� ���� � ����! <a href="lek_new.php">���������</a></p>');
if (!$db->query('insert into lek_names values (NULL, "'.$lname.'", "'.$rname.'")')) die ('<p>������ ���������� �������� ���������: '.$db->error.' <a href="lek_new.php">���������</a></p>');
print ('<p>�������� �������� �������.<br><a href="javascript:close()">������� ����</a></p></body></html>');
?>