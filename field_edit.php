<?php
//
// FIELD_EDIT.PHP
// �������������� ���� ����� �������
// ������� ������: id=xxx, ��� xxx - id ����
//
require('header.inc');
require('../settings.php');
require('auth.php');
require('connect.inc');
if (isset($_POST['type'])) // ������ �����
{
  if (! (isset($_POST['id']) && is_numeric($_POST['id']) && isset($_POST['name']) && isset($_POST['suffix']))) die ('������ �������� ����������!');
  $query = 'update osm_fields set';
  if ($_POST['type']!='0') $query.=' type="'.$_POST['type'].'",';
  $query.=' name="'.$db->real_escape_string($_POST['name']).'", suffix="'.$db->real_escape_string($_POST['suffix']).'", value=';
  if (isset($_POST['value']) && strlen($_POST['value']))
  {
      $value=str_replace("\r", '', trim($_POST['value']));
      $query .= '"'.$db->real_escape_string(str_replace("\n", $delim, $value)).'"';
  }
  else $query.='NULL';
  if (isset($_POST['template']) && strlen($_POST['template'])) $query.=', template="'.$_POST['template'].'"'; else $query.=', template=NULL';
  $query.=' where id='.$_POST['id'];
  print ($query);
  if (!$db->query($query)) die ('������ ���������� ���� ������! '.$db->error);
  print ('<p>������ ������� ���������! <a href="osmotr2.php">��������� � �������������� ����� �������</a></p>');
  require ('footer.inc');
  return;
}
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) die ('���������� ������: id ���� �� ������ ��� �������!');
$res = $db->query ('select * from osm_fields where id='.$_GET['id']);
if (!$res || !$res->num_rows) die ('������ ������ ���� ����� ����� �������! '.$db->error);
$field=$res->fetch_object();
$res->free();

// ����� ��������������
print <<<END
<form method="post">
<table border="0">
<tr>
<td align="right">ID ����:</td><td><input type="text" readonly name="id" value="$field->id"/></td></tr>

<td align="right">��� ����:</td><td><select name="type">
END;
if ($field->type=='text') print ('<option value="text" selected>��������� ����</option>'); else print ('<option value="text">��������� ����</option>');
if ($field->type=='number') print ('<option value="number" selected>�������� ���� ����� ����</option>'); else print ('<option value="number">�������� ���� ����� ����</option>');
if ($field->type=='dualnum') print ('<option value="dualnum" selected>������� ���� ����� ����</option>'); else print ('<option value="dualnum">������� ���� ����� ����</option>');
if ($field->type=='area') print ('<option value="area" selected>���� ����� ���������� ����� ������</option>'); else print ('<option value="area">���� ����� ���������� ����� ������</option>');
if ($field->type=='check') print ('<option value="check" selected>���-����</option>'); else print ('<option value="check">���-����</option>');
if ($field->type=='select') print ('<option value="select" selected>���������� ������</option>'); else print ('<option value="select">���������� ������</option>');
if ($field->type=='multi') print ('<option value="multi" selected>������ � ������������ ������ ���������� �������</option>'); else print ('<option value="multi">������ � ������������ ������ ���������� �������</option>');
if ($field->type=='table') print ('<option value="table" selected>������������ ������ (�������)</option>'); else print ('<option value="table">������������ ������ (�������)</option>');
if ($field->type=='section') print ('<option value="section" selected>������</option>'); else print ('<option value="section">������</option>');
if ($field->type=='header') print ('<option value="header" selected>������</option>'); else print ('<option value="header">������</option>');
if ($field->type=='hr') print ('<option value="hr" selected>�������������� �����</option>'); else print ('<option value="hr">�������������� �����</option>');
if ($field->type=='module') print ('<option value="module" selected>������� ������</option>'); else print ('<option value="module">������� ������</option>');
print <<<END1
</select></td></tr>
<td align="right">��������:</td><td><input type="text" name="name" value="$field->name" maxlength="250" size="100"/></td></tr>
<td align="right">�������:</td><td><input type="text" name="suffix" value="$field->suffix" maxlength="19"/></td></tr>
END1;
if ($field->type == 'select' || $field->type == 'table' || $field->type == 'multi' || $field->type == 'module')
{
  $values = explode ($delim, $field->value);
  print ('<td align="right">������ ��������:</td><td><textarea name="value" cols="80" rows="20">');
  foreach ($values as $value) print $value."\n";
  print ('</textarea></td></tr>');
}
print ('<td align="right">������ ������:</td><td><textarea name="template" cols="80" rows="3">'.$field->template.'</textarea><br>�������� ���� ������������� ������ ������� $</td></tr>');
print ('</table><input type="submit" value="���������"></form>');
require ('footer.inc');
?>