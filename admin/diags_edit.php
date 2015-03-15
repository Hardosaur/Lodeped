<?php
//
// �������� ���� ������ ���������
// ��������� ��������, �������, ��������� �������� � ���� ������
//
require('../../settings.php');
require('../auth.php');
$WINDOW_TITLE = '�������� ���������';
require ('../access.inc');
check_access_level (1);
include('../header.inc');
require('../connect.inc');
?>
<script language="JavaScript" type="text/javascript">
function getFocus(input)
{
  if (input.value[0]!= '\0'&& input.value[0]=='-' && input.value[1]=='-') input.value="";
}
</script>
<style>
table.edit { border: 0px;  }
tr.edit td { background-color: #f7f7f7; padding: 5px;}
tr.coledit td { background-color: #f0f0f0; padding: 5px;}
tr.newcol td { background-color: white; padding: 5px;}
input {background-color: white; border: solid 1px gray; padding: 4px;}
input.button {background-color: #dddddd; }
</style>
<h1>�������� �������� ���������</h1>
<?php
//
// ��������� ������ � ����
//
if (isset($_POST['diag_id']))
{
  $c=1;
  $data='';
  while (isset($_POST['value'.$c.'-1']) || isset($_POST['prompt'.$c]))
  {
      if (isset($_POST['prompt'.$c]))
      {
           $prompt=trim($_POST['prompt'.$c]);
           $prompt=rtrim($prompt,':'); // ���� �������� ������� ���������
           if (strlen($prompt)) $cascade=$prompt.':'; else $cascade='';
      }
      else $cascade='';
      $c2=1;
      while (isset($_POST['value'.$c.'-'.$c2]))
      {
          if ($c2>1) $cascade.=';';
          @$cascade.=$_POST['var'.$c.'-'.$c2].'='.$_POST['value'.$c.'-'.$c2];
          $c2++;
      }
      if ($c>1 && strlen($cascade)) $data.='|';
      $data.=$cascade;
      $c++;
  }
  if ($data)
     if (!$db->query('update diag_data set data="'.$data.'" where diag_id='.$_POST['diag_id'])) die ('������ ���������� ���� ������: '.$db->error);
  $_GET['diag_id']=$_POST['diag_id'];
}
//
// ������� ������ ����� ������ ��������
//
if (isset($_POST['new']))
{
  $new = $_POST['new'];
  if (!strlen ($new)) die ('<p>�� ������ �������� �������� ��������! <a href="diags_edit.php">�����.</a></p>');
  // ��������, ��� �� ������ � ����
  $res = $db->query ('select diag_id from diag_names where diag_name = "'.$new.'"');
  if ($res && $res->num_rows) die ('<p>����� ������� ��� ���� � ����! <a href="diags_edit.php">�����.</a></p>');
  // �������
  if (!$db->query('insert into diag_names values (NULL, "'.$new.'")')) die ('<p>���������� �������� �������! ������: '.$db->error.' <a href="diags_edit.php">�����.</a></p>');
  $res=$db->query('select LAST_INSERT_ID() from diag_names');
  $row=$res->fetch_array();
  $diag_id=$row[0];
  $res->free();
  if (!$db->query('insert into diag_data values ('.$diag_id.', "")')) die ('<p>���������� �������� �������! ������: '.$db->error.' <a href="diags_edit.php">�����.</a></p>');
  $_GET['diag_id']=$diag_id; // �������� � �������������� ������ ��������
}
//
// ������� �������
//
if (isset($_POST['delete']))
{
  if (!isset($_POST['diag_id']) || !is_numeric($_POST['diag_id'])) die ('<p>�� ����� id ��������! <a href="diags_edit.php">�����.</a></p>');
  if (!$db->query('delete from diag_names where diag_id='.$_POST['diag_id'])) die ('<p>���������� ������� ������� [1]! ������: '.$db->error.' <a href="diags_edit.php">�����.</a></p>');
  if (!$db->query('delete from diag_data where diag_id='.$_POST['diag_id'])) die ('<p>���������� ������� ������� [2]! ������: '.$db->error.' <a href="diags_edit.php">�����.</a></p>');
  print ('<p>������� ������� �����! <a href="diags_edit.php">�����.</a></p>');
  exit;
}
//
// ������� �����
//
if (!isset($_GET['diag_id']))
{
if (!isset($_GET['letter'])) // �� ������� ������ �����, ������� �� ������ ��� ������
  {
      print ('<h2>����� ��������</h2><p><table border="0"><tr valign="top" align="center">');
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
           print ("<input class='button' type='button' value='$letter' onclick='javascript:document.location=\"diags_edit.php?letter=$letter\"'/>");
      }
  }
  else // �������� ������ �����, ������� ������ ��������� �� ��� �����
  {
      print ('<h2>����� ��������</h2><p><table border="0"><tr valign="top" align="center">');
      print ('<td width="200">������ �����:<br><b>'.$_GET['letter'].'</b><br>(<a href="diags_edit.php">������� ������</a>)</td>');
      // �������� �� ���� ��� �������� �� ��� �����
      $res=$db->query('select * from diag_names where diag_name like "'.$_GET['letter'].'%"');
      if (!$res || !$res->num_rows) die ('���� ������ �������� ��������� �� �������� �������� �� �����'.$_GET['letter'].'! ������: '.$db->error);
      $size=$res->num_rows;
      if ($size<2) $size=2;
      print ('<td width="300">��������:<br><select size="'.$size.'" onchange="javascript:document.location=\'diags_edit.php?diag_id=\'+this.options[this.selectedIndex].value">');
      while ($row = $res->fetch_object())
      {
          print ("\n<option value='{$row->diag_id}'>$row->diag_name</option>");
      }
      $res->free();
  }
  print ('</td></tr></table></p>');
  print ('<h2>�������� ����� �������</h2><form method="post" action="diags_edit.php"><p>�������� ���������:&nbsp;<input type="text" name="new" size="40" maxlength="99"/><input class="button" type="submit" value="��������"/><br>');
  print ('(�������� ������ ���������� � ���������������� � ���� ��������, �� ��������)</p>');
}
else // �������� �������� ��������, ������� ����� �������������� ����� �����
{
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
  print ('<h2>����� ��������</h2><p><table border="0"><tr valign="top" align="center">');
  print ('<td width="200">������ �����:<br><b>'.$letter.'</b><br>(<a href="diags_edit.php">������� ������</a>)</td>');
  print ('<td width="300">�������� ��������:<br><b>'.$diag_name.'</b><br>(<a href="diags_edit.php?letter='.$letter.'">������� ������</a>)</td></tr></table></p>');
  print ('<form action="diags_edit.php" method="post"><input type="hidden" name="diag_id" value="'.$diag_id.'"/>');
  print ('<h2>������������� �������</h2><p><table class="edit" border="0"><tr class="edit" valign="top" align="center">');
  // ������ ������ � ��������
  $res=$db->query('select data from diag_data where diag_id='.$diag_id);
  if (!$res || !$res->num_rows) die ('���� ������ �������� ��������� �� �������� ������ � ���������� ��������! ������: '.$db->error);
  $row=$res->fetch_row();
  $data=$row[0];
  $res->free();
  //
  // ������ ������ � ����� ��������� �����
  //
  $c = 0; // ������� ��������
  $types=array(); // ������ ����� ��������
  if (strlen($data)) $cascades = explode ('|',$data);
  if (strlen($data))
  foreach ($cascades as $cascade)
  {
      $c++;
      // ������������� ����� �������������� ��������
      if (isset($_GET['delete']) && $_GET['delete']==$c && !isset($deleted)) { $c--; $deleted=1; continue; } // ���������� ������
      if (isset($_GET['insert']) && $_GET['insert']==$c)
      {
          if (!isset($_GET['type'])) die ('�� ������ ��� ������������ �������!');
          $type=$_GET['type'];
          $type+=0; // ��������� � �������� �����
          $types[$c]=$type;
          switch ($type)
          {
              case 1: // ���� ��������
                   print ("<td align='center' width='350' nowrap><input type='hidden' name='var{$c}-1' value='' size='20' disabled/><input name='value{$c}-1' value='-- �������� �������� --' size='40' onfocus='javascript:getFocus(this)'/></td>\n");
                   break;
              case 2: // ���������������� ����
                   print ("<td align='center' width='350' nowrap><input name='prompt{$c}' value='-- ����������� � ����� --' size='40' onfocus='javascript:getFocus(this)'/></td>\n");
                   break;
              case 3: // ����� ������ - ������ ��������
                   print ("<td align='center' width='550' nowrap><input name='prompt{$c}' value='' size='40' onfocus='javascript:getFocus(this)'/><br><input name='var{$c}-1' value='-- ����� ������ 1 --' size='20'/>=<input name='value{$c}-1' value='-- ������� ������ 1 --' size='40' onfocus='javascript:getFocus(this)'/>");
                   print ("<a href='diags_edit.php?diag_id=$diag_id&push=$c'>�������� ����� ����� � ������ (������)</a><br><a href='diags_edit.php?diag_id=$diag_id&pop=$c'>������� ��������� ����� ������</a></td>\n");
                   break;
              default: die ('������ ������ ������� �������! �������� "'.$type.'" �� ����������.');
          }
          $c++;
      }
      // 1. � �������� ��� ���������, �� ������� ������ �� �����
      if ($cascade{0}=='=')
      {
          $value=substr($cascade,1); // ������� ������ ���� '='
          print ("<td align='center' width='350' nowrap><input type='hidden' name='var$c-1' value='' size='20' disabled/><input name='value{$c}-1' value='$value' size='40' onfocus='javascript:getFocus(this)'/></td>\n");
          $types[$c]=1;
          break; // foreach
      }
      // ����� ������ �� �����
      $opts = explode (';',$cascade);
      // 2. ������ ������ �������� prompt, ������������ ������ ':'
      $pos=0;
      $prompt='';
      while ($pos<strlen($opts[0]) && $opts[0]{$pos}!=':' && $opts[0]{$pos}!='=') $pos++; // ���� ���� ':'
      if ($opts[0][$pos]==':') // ������ prompt
      {
          $prompt=substr($opts[0],0,$pos);
          $opts[0]=substr($opts[0],$pos+1);
      }
      // 3. ������ ������ ��������, ��� ��������� ���������������� ���� - ��� ������, ����� �����������
      $size=count($opts);
      if ($size==1 && strlen($prompt) && strlen($opts[0])==0)
      {
          print ("<td align='center' width='350' nowrap><input name='prompt$c' value='$prompt' size='40' onfocus='javascript:getFocus(this)'/></td>\n");
          $types[$c]=2;
          continue; // foreach
      }
      // 4. ������ (����� ������)
      $types[$c]=3;
      print ("<td align='center' width='550' nowrap><input name='prompt$c' value='$prompt' size='40' onfocus='javascript:getFocus(this)'/><br>\n");
      $cnt=0;
      if (isset ($_GET['pop']) && $_GET['pop'] == $c) $size--; // ������� ������� ��������� ������
      if ($size<1) die ('������� ������� ������������ ������!');
      foreach ($opts as $opt)
      {
          $cnt++;
          if ($cnt>$size) continue; // ���������� ��������� ������
          list ($var,$value) = explode ('=',$opt);
          print ("<input name='var{$c}-{$cnt}' value='$var' size='20' onfocus='javascript:getFocus(this)'/>=<input name='value{$c}-{$cnt}' value='$value' size='40' onfocus='javascript:getFocus(this)'/><br>\n");
      }
      $cnt++;
      if (isset($_GET['push']) && $_GET['push'] == $c) print ("<input name='var{$c}-{$cnt}' value='-- ����� ������ $cnt --' size='20' onfocus='javascript:getFocus(this)'/>=<input name='value{$c}-{$cnt}' value='-- ������� ������ $cnt --' size='40' onfocus='javascript:getFocus(this)'/><br>\n");
      print ("<a href='diags_edit.php?diag_id=$diag_id&push=$c'>��������</a>&nbsp;|&nbsp;<a href='diags_edit.php?diag_id=$diag_id&pop=$c'>�������</a>&nbsp;��������� �����</td>\n");
  } // ����� ������ ����
  //
  // ��������� ����� ������� (���� �����)
  //
  if (isset($_GET['insert']) && $_GET['insert']==$c+1)
  {
          $c++;
          if (!isset($_GET['type'])) die ('�� ������ ��� ������������ �������!');
          $type=$_GET['type'];
          $type+=0; // ��������� � �������� �����
          $types[$c]=$type;
          switch ($type)
          {
              case 1: // ���� ��������
                   print ("<td align='center' width='350' nowrap><input type='hidden' name='var{$c}-1' value='' size='20' disabled/>&nbsp;<input name='value{$c}-1' value='-- �������� �������� --' size='40' onfocus='javascript:getFocus(this)'/></td>\n");
                   break;
              case 2: // ���������������� ����
                   print ("<td align='center' width='550' nowrap><input name='prompt{$c}' value='-- ����������� � ����� --' size='40' onfocus='javascript:getFocus(this)'/></td>\n");
                   break;
              case 3: // ����� ������ - ������ ��������
                   print ("<td align='center' width='550' nowrap><input name='prompt{$c}' value='-- ����������� --' size='40' onfocus='javascript:getFocus(this)'/><br><input name='var{$c}-1' value='-- ����� ������ 1 --' size='20' onfocus='javascript:getFocus(this)'/>&nbsp;=&nbsp;<input name='value{$c}-1' value='-- ������� ������ 1 --' size='40' onfocus='javascript:getFocus(this)'/><br>\n");
                   print ("<a href='diags_edit.php?diag_id=$diag_id&push=$c'>�������� ����� ����� � ������ (������)</a><br><a href='diags_edit.php?diag_id=$diag_id&pop=$c'>������� ��������� ����� ������</a></td>\n");
                   break;
              default: die ('������ ������ ������� �������! �������� "'.$type.'" �� ����������.');
          }
  }
  print ('</tr>');
  //
  // ��������� ������, ���� ���� ������ ������� ��������������
  //
  if (isset($_GET['delete']) || isset($_GET['insert']) || isset($_GET['push']) || isset($_GET['pop']))
  {
      if (isset($_GET['delete']) && isset($cascades[$_GET['delete']-1]))
      {
          array_splice($cascades, $_GET['delete']-1, 1); // ������� ������
      }
      if (isset($_GET['insert']))
      {
          if (!isset($_GET['type'])) die ('�� ������ ��� ������������ �������!');
          $type=$_GET['type'];
          $type+=0; // ��������� � �������� �����
          $pos=$_GET['insert']-1;
          switch ($type)
          {
              case 1: // ���� ��������
                   if (!isset($cascades)) $cascades[0]='=-- �������� �������� --';
                   else array_splice($cascades,$pos,0,'=-- �������� �������� --');
                   break;
              case 2: // ���������������� ����
                   if (!isset($cascades)) $cascades[0]='-- ����������� � ����� --:';
                   else array_splice($cascades,$pos,0,'-- ����������� � ����� --:');
                   break;
              case 3: // ����� ������ - ������ ��������
                   if (!isset($cascades)) $cascades[0]='-- ����� ������ 1 --=-- ������� ������ 1 --';
                   else array_splice($cascades,$pos,0,'-- ����� ������ 1 --=-- ������� ������ 1 --');
                   break;
              default: die ('������ ������ ������� �������! �������� "'.$type.'" �� ����������.');
          }
      }
      if (isset($_GET['pop']))
      {
          $opts = explode (';',$cascades[$_GET['pop']-1]);
          array_pop ($opts);
          $cascades[$_GET['pop']-1]=implode(';',$opts);
      }
      if (isset($_GET['push'])) $cascades[$_GET['push']-1].=';-- ����� ����� ������ --=-- ����� ������� ������ --';
      if (count($cascades)) $data = implode ('|',$cascades);
      else $data='';
//      print ('Update: '.$data); // ��� �������
      if (!$db->query('update diag_data set data="'.$data.'" where diag_id='.$diag_id)) die ('������ ���������� ������� ��������: '.$db->error);
  }
  //
  // ������� ������ � ��������� �������������� �������
  //
  print ('<tr class="coledit">');
  for ($cnt=1; $cnt<=$c; $cnt++)
  {
      if (!isset($types[$cnt])) continue; // ������� ��� ���� ������
      print ("<td align='center' nowrap>�������:&nbsp;<a href='diags_edit.php?diag_id=$diag_id&delete=$cnt'>�������</a>");
      if ($types[$cnt]==1) print ('</td>');
      else print ("&nbsp;|&nbsp;��������:&nbsp;<a href='diags_edit.php?diag_id=$diag_id&insert=$cnt&type=2'>��������� ����</a>&nbsp;|&nbsp;<a href='diags_edit.php?diag_id=$diag_id&insert=$cnt&type=3'>������</a></td>");
  }
  //
  // ������� ������ � �������� �� ���������� ������ �������
  //
  $c++;
  print ('</tr><tr class="newcol"><td>�������� ����� �������:<br>');
  if ($c==1) print ("<a href='diags_edit.php?diag_id=$diag_id&insert=$c&type=1'>-&nbsp;������ �������� �������� (������ ������ �������)</a><br>"); // ���� �������� ��� ���
  print ("<a href='diags_edit.php?diag_id=$diag_id&insert=$c&type=2'>-&nbsp;����������� � ����� ������</a><br><a href='diags_edit.php?diag_id=$diag_id&insert=$c&type=3'>-&nbsp;������ ��������</a></tr>");
  //
  print ('</table></p><p><input class="button" type="submit" value="��������� ����� ������ ��������"/></p></form>'."\n");
  print ('<form method="post" action="diags_edit.php"><p><input type="hidden" name="delete" value="1"/><input type="hidden" name="diag_id" value="'.$diag_id.'"/><input type="submit" value="������� �������" onClick="return confirm(\'������� ������� <<'.$diag_name.'>> (������ ����� ��������)?\')"/></p></form>');
}
include('../footer.inc');
?>

