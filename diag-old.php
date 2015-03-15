<?php
//
//  ��������� ���������� ���������� (� ������ ���������)
//  �������� �������� � ���� ������
//
require('../settings.php');
include('header.inc');
require('auth.php');
include('connect.inc');
//
// �������� ���������� ������
//
if (!isset($_SESSION['osm_id']) || !isset($_SESSION['pat_id']) || !isset($_SESSION['doctor_id']) || !isset($_SESSION['date']))
{
  die ('�� ����������� ����������� ���������� � ������! ������ �������?');
}
$date = $_SESSION['date'];
$osm_id = $_SESSION['osm_id'];
$pat_id = $_SESSION['pat_id'];
$doctor_id = $_SESSION['doctor_id'];
$osm_type = $_SESSION['osm_type'];
if (!isset($_GET['page']))
{
  if (!isset($_SESSION['osm_page'])) die ('�� ������ ����� ��������!');
  $osm_page=$_SESSION['osm_page'];
}
else
{
  $osm_page=$_GET['page'];
  $_SESSION['osm_page']=$osm_page;
}
//
// ������ �� ������ ��������
//
if (isset($_GET['unset']))
{
  $unset=$_GET['unset'];
  if (!is_numeric($unset)) die ('<p>������ �������� id �������� ��� ������!</p>');
  if (!$db->query ("update diags set unset_date=\"$date\" where set_id={$unset} and redefined=0")) print ('<p>������ ������ ��������: '.$db->error.'</p>');
}
//
// ������ �� ������ ������ ��������
//
if (isset($_GET['ununset']))
{
  $unset=$_GET['ununset'];
  if (!is_numeric($unset)) die ('<p>������ �������� id �������� ��� ������ ������!</p>');
  if (!$db->query ("update diags set unset_date=NULL where set_id={$unset} and redefined=0")) print ('<p>������ ������ ��������: '.$db->error.'</p>');
}

//
// ������ �� �������� ��������
//
if (isset($_GET['delete']))
{
  $delete=$_GET['delete'];
  if (!is_numeric($delete)) die ('<p>������ �������� id �������� ��� ��������!</p>');
  $res = $db->query ('select sub_id from diags where set_id='.$delete.' and redefined=0');
  if (!$res || !$res->num_rows) die ('<p>�� ������ ������� � �������� �������!</p>');
  if ($res->num_rows>1) die ('<p>������ � ���� ������! ������� ���������� ��������� �� ����� ��� ������ '.$delete.'</p>');
  $row=$res->fetch_row();
  $sub_id=$row[0];
  $res->free();
  if (!$db->query ("delete from diags where set_id=$delete and sub_id=$sub_id")) print ('<p>������ �������� ��������: '.$db->error.'</p>');
}
//
// ��������� ����� ������� ��� �������� ���������
//
if (isset($_POST['diag']))
{
  if (!isset($_POST['diag_id'])) die ('<p>�� ������� id ��������!</p>');
  // ��������� ������ ��������
  $diagnosis=$_POST['diag'];
  if (isset($_POST['set_id'])) // ������ �� ��������� ��������
  {
      $set_id=$_POST['set_id'];
      $res = $db->query ('select sub_id, diag_id, set_date from diags where set_id='.$set_id.' and redefined=0');
      if (!$res || !$res->num_rows) die ('<p>�� ������ ������� � �������� �������!</p>');
      if ($res->num_rows>1) die ('<p style="color:red">������ � ���� ������! ������� ���������� ��������� �� ����� ��� ������ '.$set_id.'</p>');
      $row=$res->fetch_row();
      $sub_id=$row[0]+1;
      $olddate = $row[2]; // ���� ������� � ����������� ��������, �.�. �� ��� �� ������, � ��������
      if ($row[1]!=$_POST['diag_id']) print ('<p style="color:red">��������� �������� ����������! ������� ��� �������� (�.�. ������ ����� �������).</p>');
      else
      {
          if (!$db->query('update diags set redefined=1, unset_date="'.$date.'" where set_id='.$set_id.' and redefined=0')) die ('<p>������ ���������� ����: '.$db->error.'</p>');
          $query="insert into diags values ($set_id, $sub_id, $pat_id, $doctor_id, {$_POST['diag_id']}, \"$diagnosis\", \"$olddate\", NULL, 0)";
          //print ($query);
          if (!$db->query($query)) die ('<p>������� (����������) � ���� �� ������! ������: '.$db->error);
      }
      $res->free();
  }
  else // ������ �� �������� ������ ��������
  {
      $res = $db->query ("select sub_id from diags where pat_id=$pat_id and diag_id={$_POST['diag_id']} and unset_date is null");
      if ($res && $res->num_rows) { print ('<p style="color:red">���������� �������� �������, �.�. �� ��� ����������! ���������� �������� ���������.</p>'); $res->free(); }
      else
      {
          $query = "insert into diags values (NULL, 1, $pat_id, $doctor_id, {$_POST['diag_id']}, \"$diagnosis\", \"$date\", NULL, 0)";
          //print ($query);
          if (!$db->query ($query)) die ('<p>������� (�����) � ���� �� ������! ������: '.$db->error);
      }
  }
} // �� �������
//
// ������� ����� ����� � �������� ���������
//
?>
<script language="JavaScript" type="text/javascript">
function showDiagnosis()
{
  var cnt = 1;
  var div = document.getElementById('Diagnosis');
  var text = '';
  var elem;
  while (elem = document.getElementById('data'+cnt))
  {
      if (elem.type=='select-one') // ������� ���� select
         text+=' '+elem.options[elem.selectedIndex].value;
      if ((elem.type=='text' || elem.type=='hidden') && elem.value) text+=' '+elem.value;
      cnt++;
  }
  div.value=text;
}
</script>
<style>
table.list tr td { padding: 5px; }
</style>
<table border="0" cellpadding="0" cellspacing="20" width="100%"><col width="250"><col>
<tr valign="top"><td class="nav" align="left" width="260">
<a class="pages" href="osmotr.php?page=1">1. �����</a><br>
<?php
$res=$db->query ('select id, name, suffix, value from osm_template where osm_type='.$osm_type.' and type="page"');
if (!$res || !$res->num_rows) die ('��� ������ � ������������ ������� � ������� �������!');
while ($row = $res->fetch_object())
{
  if ($row->value == $osm_page) { print ('<span class="pages"><b>'.$row->value.'. '.$row->name.'</b></span><br>'); $page_id=$row->id; }
  else
      if (strlen($row->suffix)) print ('<a class="pages" href="'.$row->suffix.'.php?page='.$row->value.'">'.$row->value.'. '.$row->name.'</a><br>');
      else print ('<a class="pages" href="osmotr.php?page='.$row->value.'">'.$row->value.'. '.$row->name.'</a><br>');
}
$res->free();
print('</td><td align="left">');
//
// ������ ������ ���������� ��������� �� ���� ������
//
$res = $db->query ("select set_id, sub_id, diag_id, diag, set_date, unset_date, redefined from diags where pat_id=$pat_id and set_date <= \"$date\" and (unset_date is null or unset_date >= \"$date\") order by set_id, sub_id desc");
if ($res && $res->num_rows)
{
  if (isset($set_id)) unset ($set_id);
  print ('<table class="list" cellpadding=3><tr><th>���� ����������</th><th>���</th><th>������ ��������</th></tr>'."\n");
  while ($row = $res->fetch_object())
  {
      if (isset($set_id) && $set_id==$row->set_id) continue; // ���������� �������, �.�. ����� ��� ��� ������� ����� ������
      $set_id=$row->set_id;
      $dat = explode('-',$row->set_date);
      $set_date = $dat[2].'.'.$dat[1].'.'.$dat[0];
      if (isset($unset_date)) unset ($unset_date);
      if ($row->unset_date)
      {
          $dat=explode('-',$row->unset_date);
          $unset_date=$dat[2].'.'.$dat[1].'.'.$dat[0];
      }
      if (isset($unset_date)) print ('<tr style="color: grey">'); else print ('<tr>');
      print ('<td>'.$set_date.'</td>');
      print ('<td>'.$row->diag_id.'</td>');
      print ('<td>'.$row->diag);
      if ($row->redefined) print (' (������� ����������������)</td></tr>'."\n"); // �� ��������� � �������
      else
      if (isset($unset_date)) // ��� ��� ����
      {
          if (strcmp($date,$row->unset_date)<0) print (' (������� ���� � �������) ');
          else print (' (������� ���� �� ������ �������) ');
          print ("[<a href='diag.php?ununset={$row->set_id}'>�������� ������</a>]</td></tr>\n");
      }
      else
      {
          print ("&nbsp;[<a href='diag.php?diag_id={$row->diag_id}&set_id={$row->set_id}'>��������</a>]");
          print ("[<a href='diag.php?unset={$row->set_id}'>�����</a>]");
          if ($row->sub_id == 1) print ("[<a href='diag.php?delete={$row->set_id}'>�������</a>]"); // ������� ����� ������ ������ ������� � �������
          print ("</td></tr>\n");
      }
  }
  print ('</table></p>');
}
else print ('</p><p style="font-style: italic">��� ������������� ��������� (�� ���� �������).</p>');
//
//
//
//print ('</ul></p><h2>����� ��������</h2>');
//print ('<form method="post" action="diag.php"><p><table border="0"><tr valign="top" align="center">');

if (!isset($_GET['diag_id'])) // �������� �������� �� �������
{
  print ('<h2>����� ������ ��������</h2>');
  print ('<form method="post" action="diag.php"><p><table border="0"><tr valign="top" align="center">');
  if (!isset($_GET['letter'])) // �� ������� ������ �����, ������� �� ������ ��� ������
  {
      print ('<td>�������� ������ ����� �������� ��������:<br>');
      // ������� ������ ������ ����� ��������
      $res=$db->query('select diag_name from diag_names');
      if (!$res || !$res->num_rows) die ('���� ������ �������� ��������� ����� ��� ����������! ������: '.$db->error);
      $letters=array(); // ������ ������ ����
      while ($row=$res->fetch_array())
      {
            $letter = $row[0]{0};
            if (!isset($letters[$letter])) $letters[$letter]=1;
            else $letters[$letter]++;
      }
      $res->free();
      foreach ($letters as $letter => $value)
      {
           print ('<input type="button" value="'.$letter.'" onclick="javascript:document.location=\'diag.php?letter='.$letter.'\'"/> ');
      }
  }
  else // �������� ������ �����, ������� ������ ��������� �� ��� �����
  {
      print ('<td width="200">������ �����:<br><b>'.$_GET['letter'].'</b><br>(<a href="diag.php">������� ������</a>)</td>');
      // �������� �� ���� ��� �������� �� ��� �����
      $res=$db->query('select * from diag_names where diag_name like "'.$_GET['letter'].'%"');
      if (!$res || !$res->num_rows) die ('���� ������ �������� ��������� �� �������� �������� �� �����'.$_GET['letter'].'! ������: '.$db->error);
      $size=$res->num_rows;
      if ($size<2) $size=2;
      print ('<td width="300">��������:<br><select size="'.$size.'" onchange="javascript:document.location=\'diag.php?diag_id=\'+this.options[this.selectedIndex].value">');
      while ($row = $res->fetch_object())
      {
          print ("\n<option value='{$row->diag_id}'>$row->diag_name</option>");
      }
      $res->free();
  }
  print ('</td></tr></table></p></form>');
}
else // �������� �������� ��������, ������� ���� ����� ������� ��������
{
  if (isset($set_id)) unset($set_id);
  if (isset($_GET['set_id']))
  {
      $set_id=$_GET['set_id'];
      print ('<h2>��������� ������������ ��������</h2><p>������� ������������: ');
      $res = $db->query ('select diag from diags where set_id='.$set_id.' and redefined=0');
      if (!$res || !$res->num_rows) die ('<p>�� ������ ������� � �������� �������!</p>');
      if ($res->num_rows>1) die ('<p>������ � ���� ������! ������� ���������� ��������� �� ����� ��� ������ '.$set_id.'</p>');
      $row=$res->fetch_row();
      print ('<b>'.$row[0].'</b></p>');
      $res->free();
  }
  else
  {
      print ('<h2>����� ������ ��������</h2>');
  }
  print ('<form method="post" action="diag.php"><p><table border="0"><tr valign="top" align="center">');
  // �������� �������� ��������
  $diag_id=$_GET['diag_id'];
  if (!is_numeric($diag_id)) die ('�������� ������ ������ ��������!');
  $res=$db->query('select diag_name from diag_names where diag_id='.$diag_id);
  if (!$res || !$res->num_rows) die ('���� ������ �������� ��������� �� �������� �������� � ������� '.$diag_id.'! ������: '.$db->error);
  $row=$res->fetch_row();
  $diag_name=$row[0];
  $res->free();
  // ������� ��� ��������� ������
  $letter=$diag_name{0};
  print ('<td width="200">������ �����:<br><b>'.$letter.'</b><br>(<a href="diag.php">������� ������</a>)</td>');
  print ('<td width="300">�������� ��������:<br><b>'.$diag_name.'</b><br>(<a href="diag.php?letter='.$letter.'">������� ������</a>)</td>');
  print ('<input type="hidden" name="diag_id" value="'.$diag_id.'"/>');
  if (isset($set_id)) print ('<input type="hidden" name="set_id" value="'.$set_id.'"/>');
  // ������ ������ � ��������
  $res=$db->query('select data from diag_data where diag_id='.$diag_id);
  if (!$res || !$res->num_rows) die ('���� ������ �������� ��������� �� �������� ������ � ���������� ��������! ������: '.$db->error);
  $row=$res->fetch_row();
  $data=$row[0];
  $res->free();
  // ������ ������ � ����� ��������� �����
  $cascades = explode ('|',$data);
  $c = 0; // ������� ��������
  foreach ($cascades as $cascade)
  {
      $c++;
      // ���������� ������ ������� ������
      // 1. � �������� ��� ���������, �� ������� ������ �� �����
      if ($cascade{0}=='=')
      {
          $value=substr($cascade,1); // ������� ������ ���� '='
          print ("<td><input type='hidden' id='data{$c}' value='$value'/></td>\n");
          $fullname=$value;
          break; // foreach
      }
      // ����� ������ �� �����
      $opts = explode (';',$cascade);
      // 2. ������ ������ �������� prompt, ������������ ������ ':'
      $pos=0;
      if (isset($prompt)) unset ($prompt);
      while ($pos<strlen($opts[0]) && $opts[0]{$pos}!=':' && $opts[0]{$pos}!='=') $pos++;
      if ($opts[0][$pos]==':') // ������ prompt
      {
          $prompt=substr($opts[0],0,$pos);
          $opts[0]=substr($opts[0],$pos+1);
      }
      // 3. ������ ������ ��������, ��� ��������� ���������������� ���� - ��� ������, ����� �����������
      $size=count($opts);
      if ($size==1 && isset($prompt) && strlen($opts[0])==0)
      {
          print ("<td width='400'>$prompt:<br><input type='text' id='data{$c}' value='' size='40' onchange='javascript:showDiagnosis()'/></td>\n");
          continue; // foreach
      }

      print ('<td width="200">');
      if (isset($prompt)) print ($prompt.':<br>'); else print ('&nbsp;<br>');
      // 4. ������� ������ ���� ���� - ������ �� �����
      if ($size==1)
      {
          list ($var,$value) = explode ('=',$opts[0]);
          print $var;
          print ("<input type='hidden' id='data{$c}' value='$value'/></td>\n");
          continue; // foreach
      }
      print ("<select id='data{$c}' size='$size' onchange='javascript:showDiagnosis()'>");
      foreach ($opts as $opt)
      {
          // ����� ������: ����� ������ ������ �� ���� "�������=��������"
          list ($var,$value) = explode ('=',$opt);
          print ("<option value='$value'>$var</option>\n");
      }
      print ('</select></td>'."\n");
  }
  // ����� ������ ����������
  print ('</tr></table></p><p>������ �������� ��������:&nbsp;<input type="text" name="diag" id="Diagnosis" size="120" value="');
  if (isset($fullname)) print $fullname;
  print('"/></p><p><input type="submit" value="');
  if (isset($set_id)) print ('�������� ������������"/>');
  else print ('�������� �������"/>');
  print('&nbsp;<input type="button" value="��������" onclick="document.location=\'diag.php\'"/></p></form>');
}
print ('<p><input type="button" value="����� >>" onclick="document.location=\'osmotr.php?page='.($osm_page+1).'\'"/></p>');
include('footer.inc');
?>