<?php
//
// FIELD_NEW.PHP
// �������� ���� ����� �������
// ������� ������: ���
//
require('header.inc');
require('../settings.php');
require('auth.php');
require('connect.inc');
?>
<script>
function checkFields()
{
if (document.getElementById("type").value=="" || document.getElementById('name').value=="") { window.alert('�� ������ ��� �/��� �������� ��������!'); return false} else return true;
}
</script>
<?php
if (isset($_POST['type'])) // ������ �����
{
  if (! (isset($_POST['type']) && ($_POST['name']) && isset($_POST['suffix']))) die ('������ �������� ����������!');
  $query = 'insert into osm_fields values (NULL, 0, "'.$_POST['type'].'", "'.$db->real_escape_string($_POST['name']).'", "'.$db->real_escape_string($_POST['suffix']).'", ';
  if (isset($_POST['value']) && strlen ($_POST['value']))
  {
      $value=str_replace("\r", '', trim($_POST['value']));
      $query .= '"'.$db->real_escape_string(str_replace("\n", $delim, $value)).'"';
  }
  else $query.='NULL';
  if (isset($_POST['template']) && strlen ($_POST['template'])) $query.=', "'.$_POST['template'].'"'; else $query.=', NULL';
  $query .= ')';
  print ($query);
  if (!$db->query($query)) die ('������ ���������� ���� ������! '.$db->error);
  $res=$db->query('select LAST_INSERT_ID()');
  if (!$res || !$res->num_rows) die ('������ ���������� ������ ����! '.$db->error);
  $row=$res->fetch_row();
  $id=$row[0];
  $res->free();
  print ('<p>������ ������� ���������! <a href="osmotr2.php?do=insert&id='.$id.'&after='.$_POST['after'].'">��������� � �������������� ����� �������</a></p>');
  require ('footer.inc');
  return;
}
if (!isset($_GET['after']) || !is_numeric($_GET['after'])) die ('���������� ������: id ���� �� ������ ��� �������!');
// ����� ����������
print <<<END
<form method="post" onsubmit="return checkFields()">
<input type="hidden" name="after" value="{$_GET['after']}">
<table border="0">
<tr>
<td align="right">��� ����:</td><td><select name="type" id="type">
<option value=""> --- </option>
<option value="text">��������� ����</option>
<option value="number">�������� ���� ����� ����</option>
<option value="dualnum">������� ���� ����� ����</option>
<option value="area">���� ����� ���������� ����� ������</option>
<option value="check">���-����</option>
<option value="select">���������� ������</option>
<option value="multi">������ � ������������ ������ ���������� �������</option>
<option value="table">������������ ������ (�������)</option>
<option value="section">��������� ������ (����������)</option>
<option value="header">��������� �������</option>
<option value="hr">�������������� �����</option>
<option value="module">������� ������</option>
</select>
</td></tr>
<td align="right">��������:</td><td><input type="text" name="name" id="name" value="" maxlength="250" size="100"/></td></tr>
<td align="right">������� (������.):</td><td><input type="text" name="suffix" value="" maxlength="19"/></td></tr>
<td align="right">������ ��������:</td><td><textarea name="value" cols="80" rows="20"></textarea>
<br>�� ������ �������� � ������, ����������� ������� - '*'</td></tr>
<td align="right">������ ������ (������.):</td><td><textarea name="template" cols="80" rows="3"></textarea><br>�������� ���� ������������� ������ ������� $</td></tr>
</table><input type="submit" value="���������"></form>
END;
require ('footer.inc');
?>