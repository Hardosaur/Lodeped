<?php
//
//  ��������� ���������� ����������� ��������
//  ���������� ����������� � ���� ������ � ��������� � ���������
//  (!) ��� ������������� � Chrome/Safari �������� �� table-cell
//
require('../settings.php');
include('header.inc');
require('auth.php');
require('connect.inc');
require('access.inc');
//
// �������� ���������� ������
//
if (
!isset($_SESSION['pat_id']) || !isset($_SESSION['doctor_id']) || !isset($_SESSION['date']) || !isset($_SESSION['osm_id']))
{
  die ('�� ����������� ����������� ���������� � ������! ������ �������?');
}
$date = $_SESSION['date'];
$osm_id = $_SESSION['osm_id'];
$pat_id = $_SESSION['pat_id'];
$doctor_id = $_SESSION['doctor_id'];
//
// ��������� ����� ����������� (���� ������� ��������)
//
if (isset($_POST['Lek']))
{
  if (!isset($_GET['lek_id'])) die ('<p style="color: red">�� ������� id ���������!</p>');
  $lek_id=$_GET['lek_id'];
  unset($_GET['lek_id']); // ������� � ������ ���������
  // ��������� ������ ��������
  $lek=$_POST['form'].' '.$_POST['lek_name'].' '.trim($_POST['Lek']);
  if ($lek{strlen($lek)-1}!='.') $lek.='.';
  if (strlen($_POST['Course'])) $lek.=' '.$_POST['Course'].'.';
  $lek=$db->real_escape_string($lek);
  $query = "insert into leks values (NULL, $lek_id, $pat_id, $doctor_id, \"$date\", NULL, \"$lek\",0)";
//  print ($query);
  if (!$db->query ($query)) die ('<p>����������� � ���� �� �������! ������: '.$db->error);
} // �� �������
//
//  �������� �����������
//
if (isset($_GET['delete']))
{
  $id=$_GET['delete'];
  if (!is_numeric($id)) die ('������ �������� ���������!');
  if (!$db->query('update leks set unset_date = "'.$date.'" where id='.$id)) die ('������ ������ ���������� ('.$db->error.')! �������� ��������?');
}
//
//  ������� � ���, ��� �������� �� ����������
//
if (isset($_GET['ignored']))
{
  $id=$_GET['ignored'];
  if (!is_numeric($id)) die ('������ �������� ���������!');
  if (!$db->query('update leks set ignored = (not ignored) where id='.$id)) die ('������ ��������� ���������� ('.$db->error.')! �������� ��������?');
}


//
// ������ �� ���������� ������ ����������
//
if (isset($_GET['edit']))
{
  $edit=$_GET['edit'];
  if (!is_numeric($edit)) print ('<p>������ �������� id ���������� ��� ��������������!</p>');
  elseif (!isset($_POST['text']) || !strlen($_POST['text'])) print ('<p>����� ���������� ���� ��� �� �������!</p>');
  elseif (!$db->query ('update leks set lek = "'.$_POST['text'].'" where id='.$edit)) print ('<p>����� ���������� �������� �� �������! ������: '.$db->error);
}

//
// ������� JS
//
?>
<script language="JavaScript" type="text/javascript">
function On (id)
{
  var el = document.getElementById(id);
  if (el.style.display == '' || el.style.display == 'none') el.style.display='block';
}
function Off (id)
{
  var el = document.getElementById(id);
  if (el && el.style.display == 'block') el.style.display="none";
}
function Set (id, value)
{
  var input = document.getElementById('data'+id);
  input.value=value;
  Off ('i'+id); // ���� �����, ������� ���� ����� ������
}
function Clear (id)
{
  var input = document.getElementById('data'+id);
  input.value='';
}
function showLek ()
{
  var c = 1;
  var text = '';
  while ((c<13) && (input = document.getElementById("data"+c)))
  {
      if (input.value != '')
      {
          if (c==5) text+=' -';
          text+=' '+input.value;
      }
      c++;
  }
  document.getElementById('Lek').value=text;
}
function showCourse ()
{
  var c = 13;
  var text = '';
  while (input = document.getElementById("data"+c))
  {
      if (input.value != '') text+=input.value;
      c++;
  }
  document.getElementById('Course').value=text;
}
// ----------------------------------------------------
function Add (table, id)
{
  var value = document.getElementById('data'+id).value;
  if (value.length==0) return;
  window.open("lek_add.php?table="+table+"&value="+value,"","");
}
// ----------------------------------------------------
function selectT0 (sel) // ��� 3 - ����� ���������
{
  if (sel.selectedIndex == 0) { On('S1'); On('S2'); return; }
  Off('S1');
  Off('S2');
  Clear(1);
  Clear(2);
  showLek();
}
// ----------------------------------------------------
function selectT1 (sel) // ������������ ��������
{
  var value = sel.options[sel.selectedIndex].value;
  if (value == '') On ('i1');
  else Set(1,value);
  showLek();
}
// ----------------------------------------------------
function selectT2 (sel) // ��������� ���������
{
  var value = sel.options[sel.selectedIndex].value;
  if (value == '') On ('i2');
  else Set(2,value);
  showLek();
}
// ----------------------------------------------------
function selectTa (sel) // �������� �� ������
{
  if (sel.selectedIndex == 1) Clear (3);
  else Set(3,'in ampullis');
  showLek();
}
// ----------------------------------------------------
function selectT4 (sel) // ������ ����������� ����
{
  var value = sel.options[sel.selectedIndex].value;
  if (value == '') On ('i5');
  else Set(5,value);
  selectTs(document.getElementById('Ts')); // ������������� ��������
  showLek();
}
// ----------------------------------------------------
function selectTs (sel) // ������� ����
{
  if (sel.selectedIndex==-1) return;
  if (sel.options[sel.selectedIndex].value == '') { On ('i6'); return; }
  var suffix = parseInt(sel.options[sel.selectedIndex].value);
  var doze = parseInt(document.getElementById('data5').value);
  // ��������, �� �������� �� value ������� ������
  var str = new String (document.getElementById('data5').value);
  if (str.indexOf('/')>0 || str.indexOf(',')>0) doze=2;
  if (isNaN(doze) || doze<=0) return; // �� ������������, ���� �� ����� ������� �����
  var value=sel.options[sel.selectedIndex].text;
  switch (suffix)
  {
      case 0: // ������
           if (doze==1) value="�����";
           else if (doze<5) value="�����";
           else value="������";
           break;
      case 3: // ��������
           if (doze==1) value="�������";
           else if (doze<5) value="�������";
           else value="��������";
           break;
      case 5: // �������
           if (doze==1) value="�������";
           else if (doze<5) value="�������";
           else value="������";
           break;
      case 7: // ������
           if (doze==1) value="�����";
           else if (doze<5) value="�����";
           else value="������";
           break;
      case 8: // ���
           if (doze==1) value="����";
           else if (doze<5) value="����";
           else value="���";
           break;
  }
  Set(6,value);
  showLek();
}
// ----------------------------------------------------
function selectT5 (sel) // ����� ����������
{
  Clear(7);
  // ������� ������ � �������� ������ ����������
  var text = sel.options[sel.selectedIndex].text;
  var value = parseInt(sel.options[sel.selectedIndex].value,10);
  if (value==14) // �� ���������
  {
      Clear(4);
      Clear(6);
      Clear(7);
      Off('S6');
      Off('S7');
      showLek();
      return;
  }
  Set(4,text);
  switch (value)
  {
      case 2: case 3: case 4: case 11: case 13: // ������������� ��� ������
           On('S4');
           On('S5');
           Off('S6');
           Off('S7');
           Clear(7);
           break;
     case 15: case 5: case 6: case 7: // ������
           Off('S7');
           Off('S4');
           Off('S5');
           On('S6');
           Clear(5);
           Clear(6);
           break;
      case 9: case 10: case 12: // ����� (� ��� � ��.)
           On('S4');
           On('S5');
           Off('S6');
           On('S7');
           Clear(5);
           Clear(6);
           break;
      case 8: // �����������
           Off('S4');
           Off('S5');
           Off('S6');
           Off('S7');
           Clear(5);
           Clear(6);
           Clear(7);
           break;
/*      case 15: // ������, ��������
           On('S6');
//           Clear(4);
//           Clear(5);
//           Clear(6);
           break;
*/

  }
  if (value==11)
  {
      Set(7,'��� ����');
      Clear(4);
  }
  showLek();
}
// ----------------------------------------------------
function selectT7 (sel) // ����� ���������� ������� ����������
{
  var value = sel.options[sel.selectedIndex].value;
  if (value == '') On ('i7');
  else Set(7,value);
  showLek();
}
// ----------------------------------------------------
function selectTlr (sel) // ����� �����������
{
  var index = sel.selectedIndex;
  var T5 = document.getElementById('T5');
  var index2 = T5.options[T5.selectedIndex].value;
  var text = 'error';
  switch (index2)
  {
      case '9': // � ���
           if (index == 0) text="� ����� ������";
           else if (index == 1) text="� ������ ������";
           else text="� ������";
           break;
      case '10': // � �����
           if (index == 0) text="� ����� ����";
           else if (index == 1) text="� ������ ����";
           else text="� ��� �����";
           break;
      case '12': // � ���
           if (index == 0) text="� ����� ���";
           else if (index == 1) text="� ������ ���";
           else text="� ��� ���";
           break;
  }
  Set(7,text);
  showLek();
}
// ----------------------------------------------------
function selectTk (sel) // ��������� �����
{
  var index = sel.selectedIndex;
  Set(10,sel.options[index].value);
  if (index < 2) // ����� �������, ������� ���
  {
      On('S9');
  }
  else
  {
      Off('S9');
      Clear(8);
      Clear(9);
  }
  showLek();
}
// ----------------------------------------------------
function selectTv (sel) // ��������� �����
{
  var value = sel.options[sel.selectedIndex].value;
  if (value == '') On ('i8');
  else
  {
      Set(8,value);
      changeIv();
  }
  showLek();
}
// ----------------------------------------------------
function changeIv () // ������������� "���"
{
  var value = parseInt(document.getElementById('data8').value);
  if (isNaN(value)) return;
  if (value==1) Set(9,'���');
  else if (value<5) Set(9,'����');
  else Set(9,'���');
  showLek();
}
// ----------------------------------------------------
function selectT9 (sel) // ��� ���������
{
  var index = sel.selectedIndex;
  var value = sel.options[index].value;
  if (value == '') { On ('i11'); return; }
  Set(11,value);
  if (index==0) // ����. �����
  {
     Off('S11');
     Clear(12);
  }
  else On('S11');
  showLek();
}
// ----------------------------------------------------
function selectT10 (sel) // ����� ���������
{
  var value = sel.options[sel.selectedIndex].value;
  if (value == '') On ('i12');
  else Set(12,value);
  showLek();
}
// ----------------------------------------------------
function selectT11 (sel) // ���� ������ � ����� �������
{
  if (sel.selectedIndex == 0)
  {
      On('S12');
  }
  else
  {
    Off('S12');
    Clear(13);
    Clear(14);
    Clear(15);
    Clear(16);
    Clear(17);
    Clear(18);
    Clear(19);
    Clear(20);
  }
  showCourse();
}
function selectT12 (sel)
{
  var value = sel.value;
  if (value=='')
  {
      document.getElementById('data13').value='';
      document.getElementById('data15').value='';
      showLek();
      return;
  }
  var val=parseInt(value);
  document.getElementById('data13').value='���� �������: ';
  if (document.getElementById('dayweek1').value==1)
  {
     if (val==1) text=' ������';
     else if (val<5) text=' ������';
     else text=' ������';
  }
  else
  {
     var text=' ����';
     if (val==1) text=' ����';
     else if (val<5) text=' ���';
  }
  document.getElementById('data15').value=text;
  showCourse();
}
function selectT13 (sel)
{
  var value = sel.value;
  if (value=='')
  {
      document.getElementById('data16').value='';
      showLek();
      return;
  }
  document.getElementById('data16').value=', ���������� ������: ';
  showCourse();
}
function selectT14 (sel)
{
  var value = sel.value;
  if (value=='')
  {
      document.getElementById('data18').value='';
      document.getElementById('data20').value='';
      showLek();
      return;
  }
  document.getElementById('data18').value=', �������� ����� �������: ';
  var val=parseInt(value);
  if (document.getElementById('dayweek2').value==1)
  {
     var text=' ������';
     if (val==1) text=' ������';
     else if (val<5) text=' ������';
  } else
  {
     var text=' ����';
     if (val==1) text=' ����';
     else if (val<5) text=' ���';
  }
  document.getElementById('data20').value=text;
  showCourse();
}
// ----------------------------------------------------
function clickLek (id) // ��� ����������� ������ ���������� "�� �����"
{
  var lek_span=document.getElementById('lek'+id);
  var text_span=document.getElementById('text'+id);
  if (lek_span.style.display=='block')
  {
      lek_span.style.display='none';
      text_span.style.display='block';
  }
  else
  {
      lek_span.style.display='block';
      text_span.style.display='none';
  }
}
</script>
<style>
table.list tr td { padding: 5px; }
</style>
<?php
//
// ���������� �� ������������� ��������
//
$res = $db->query('select allergies.all_id, lek_names.rname from allergies, lek_names where allergies.pat_id='.$pat_id.' and allergies.lek_id=lek_names.lek_id');
if ($res && $res->num_rows)
{
  print ('<p><b>��������!</b> ������� �������� �� ������������� �������� �� ��������� ���������:<ul>');
  while ($row = $res->fetch_row()) print ("<li>{$row[1]}</li>\n");
  $res->free();
  print ('</ul><a href="allergy.php?pat_id='.$pat_id.'" target="_blank">���������...</a></p>');
}
print ('<p><table class="list" cellpadding="3"><tr><th>����</th><th>����������</th></tr>');
//
// ������ ������ ���������� �� ���� ������
//
$res=$db->query('select * from leks where pat_id='.$pat_id.' and unset_date is null order by set_date desc');
if ($res && $res->num_rows)
{
  while ($row = $res->fetch_object())
  {
      $dat=explode('-',$row->set_date);
      print ('<tr><td>'.$dat[2].'.'.$dat[1].'.'.$dat[0].'</td>');
      //print ('<td>'.$row->lek_id.'</td>');
      print ('<td><span style="display: block" id="lek'.$row->id.'"><span onclick="javascript:clickLek('.$row->id.')">'.$row->lek);
      if ($row->ignored > 0) print ('&nbsp;<b>(�� ����������)</b>');
      print ('&nbsp;</span>[<a href="lek.php?delete='.$row->id.'" onclick="return confirm(\'������ �������� ����������?\')">��������</a>]');
      print ('&nbsp;[<a href="allergy_add.php?lek_id='.$row->lek_id.'" target="_blank" onclick="return confirm(\'������ �������� ���������� ��������?\')">��������!</a>]');
      print ('&nbsp;[<a href="lek.php?ignored='.$row->id.'">�� ����������</a>]');
      print ('</span><span style="display: none" id="text'.$row->id.'"><form method="post" action="lek.php?edit='.$row->id.'" onsubmit="clickLek('.$row->id.')"><input name="text" type="text" size="50" value="'.$row->lek.'"></form></span></td></tr>'."\n");
  }
  print ('</table></p>');
  $res->free();
}
else print ('</table></p><p>������� ���������� ���.</p>');

//
// 1. ����� ��������� �� ������
//
if (!isset($_GET['lek_id'])) // �������� ��������� �� �������
{
  print ('<h2>����� �������� ��������� <span style="font-size: 10pt">');
  if (access_level()<2) print (' <span style="font-size: 10pt">(<a href="lek_new.php" target="_blank">�������� ����� ������������</a>)</span>');
  print ('</h2>');
  print ('<p><table border="0"><tr valign="top" align="center">');
  if (!isset($_GET['letter'])) // �� ������� ������ �����, ������� �� ������ ��� ������
  {
      print ('<td>�������� ������ ����� �������� �������� ���������:<br>');
      // ������� ������ ������ ����� ��������
      $res=$db->query('select rname from lek_names order by rname');
      if (!$res || !$res->num_rows) die ('���� ������ �������� �������� ����� ��� ����������! ������: '.$db->error);
      $letters=array(); // ������ ������ ����
//      setlocale (LC_CTYPE,'ru_RU');
      while ($row=$res->fetch_array())
      {
            $letter = ucfirst($row[0]{0});
            if (!isset($letters[$letter])) $letters[$letter]=1;
            else $letters[$letter]++;
      }
      $res->free();
      foreach ($letters as $letter => $value)
      {
           print ('<input type="button" value="'.$letter.'" onclick="javascript:document.location=\'lek.php?letter='.$letter.'\'"/> ');
      }
  }
  else // �������� ������ �����, ������� ������ �������� �� ��� �����
  {
      print ('<td width="200">������ �����:<br><b>'.$_GET['letter'].'</b><br>(<a href="lek.php">������� ������</a>)</td>');
      // �������� �� ���� ��� ��������� �� ��� �����
      $res=$db->query('select * from lek_names where rname like "'.$_GET['letter'].'%" order by rname');
      if (!$res || !$res->num_rows) die ('���� ������ �������� �������� �� �������� �������� �� �����'.$_GET['letter'].'! ������: '.$db->error);
      $size=$res->num_rows;
      if ($size<2) $size=2;
      print ('<td width="300">���������:<br><select size="'.$size.'" onchange="javascript:document.location=\'lek.php?lek_id=\'+this.options[this.selectedIndex].value">');
      while ($row = $res->fetch_object())
      {
          print ("\n<option value='{$row->lek_id}'>$row->rname</option>");
      }
      $res->free();
  }
  include('footer.inc');
  exit;
}
//
// �������� ������, ����� �����
//
$lek_id = $_GET['lek_id'];
print ('<h2>���� ������� �����������</h2>');
// ������ �������� ���������
$res = $db->query ('select * from lek_names where lek_id='.$lek_id);
if (!$res) die ('<p>�������� ��������� �� �������! ������: '.$db->error.'</p>');
$row=$res->fetch_object();
$lek_name=$row->rname;
$res->free();
//
  print ('<p><table border="0" cellspacing="10"><tr valign="top" align="center">');
  print ('<td width="200">������ �����:<br><b>'.$lek_name{0}.'</b><br>(<a href="lek.php">������� ������</a>)</td>');
  print ('<td width="300">��������:<br><b>'.$lek_name.'</b><br>(<a href="lek.php?letter='.$lek_name{0}.'">������� ������ �� �� �� �����</a>)</td>');
//
// 2. ����� ����� �������
//
if (!isset($_GET['form']))
{
  $res = $db->query ('select * from lek_forms');
  if (!$res) die ('<p>�� ������� ������ � ������ �������! ������: '.$db->error.'</p>');
  $size=$res->num_rows;
  if ($size<2) $size=2;
  print ('<td width="300">����� �������:<br><select size="'.$size.'" onchange="javascript:document.location=\'lek.php?lek_id='.$lek_id.'&form=\'+this.options[this.selectedIndex].value">');
  while ($row = $res->fetch_object()) print ("\n<option value='{$row->form_id}'>$row->rname</option>");
  $res->free();
  include('footer.inc');
  exit;
}
// ������� �������� ����� �������
$form_id=$_GET['form'];
if ($form_id==20) // ������
{
  $form_name='';
}
else
{
  $res = $db->query('select * from lek_forms where form_id='.$form_id);
  if (!$res) die ('<p>�� ������� ������ � ����� �������! ������: '.$db->error.'</p>');
  $row=$res->fetch_object();
  $form_name=$row->rname;
  $res->free();
}
print ('<td width="200">����� �������:<br><b>'.$form_name.'</b><br>(<a href="lek.php?lek_id='.$lek_id.'">������� ������</a>)</td>');
?>
<td>�������� � �������?<br>
<select id="Ta" size="2" onchange="javascript:selectTa(this)">
<option value="in ampullis">��</option>
<option value="" selected>���</option>
</select>
<input type="hidden" id="data3" value=""/>
</td>
<?php
/* - ������ ������ ��������� �� ������� ���������
if ($form_id == 16 || $form_id == 4 || $form_id == 3 || $form_id == 11) // ��������, ��������, ������� ��� �����
{
*/
?>
<td>������� ���������?<br>
<select id="T0" size="2" onchange="javascript:selectT0(this)">
<option value="1">��</option>
<option value="2" selected>���</option>
</select>
</td>
</tr>
</table>
</p>
<?php
//}
// ----------------------------------------
// ����� ������ ����� ����� �����������
// ----------------------------------------
print ('<p><table cellpadding="0" cellspacing="10"><tr valign="top" align="center">');
//
// 3. ���� ��������� (���� �����)
//
// ��� �������� � ���� ����� ������������
print ('<td id="S1" style="display: none">');
if ($form_id==12 || $form_id==7)
{
  $res = $db->query('select * from lek_data where tab_id=1'); // ������� 1
  if (!$res || $res->num_rows!=1) die ('<p>�� ������� ������� 1! ������: '.$db->error.'</p>');
  $row=$res->fetch_object();
  $opts = explode (';',$row->list);
//  natsort($opts);
  print ($row->tab_name.':<br><select size="10" id="T1" onchange="javascript:selectT1(this)">');
  foreach ($opts as $opt)
  {
      print ("\n<option value='$opt'>$opt</option>");
  }
  $res->free();
  print ('<option value="">(������)</option></select>');
}
print ('<br><span id="i1" style="display: none"><input type="text" id="data1" size="6" value="" onChange="javascript:showLek()"/>');
print ('<img src="img/plus.png" onClick="Add(1,1)"></span></td>'."\n");
// ��������� ��� ��������� ����������
print ('<td id="S2" style="display: none">');
$res = $db->query('select * from lek_data where tab_id=2'); // ������� 2 - ���������
if (!$res || $res->num_rows!=1) die ('<p>�� ������� ������� 2! ������: '.$db->error.'</p>');
$row=$res->fetch_object();
$opts = explode (';',$row->list);
//natsort($opts);
print ($row->tab_name.':<br><select size="10" id="T2" onchange="javascript:selectT2(this)">');
foreach ($opts as $opt)
{
    print ("\n<option value='$opt'>$opt</option>");
}
$res->free();
print ('<option value="">(������)</option></select>');
print ('<br><span id="i2"  style="display: none"><input type="text" id="data2" size="11" value="" onChange="javascript:showLek()"/>');
print ('<img src="img/plus.png" onClick="javascript:Add(2,2)"></span></td>'."\n");
//
// 7. ����� ����������
//
?>
<td id="S3" style="display: block">
����� ����������:<br>
<select id="T5" size="10" onchange="javascript:selectT5(this)">
<?php
if ($form_id == 12 || $form_id == 16 || $form_id == 18 || $form_id == 20) // ��������� ��� ��������
   print ('<option value="1">�/�����</option><option value="2">�/�������</option><option value="3">�/�����</option><option value="4">�/�����</option>');
if ($form_id == 8 || $form_id == 9 || $form_id == 12 || $form_id == 11 || $form_id == 15 || $form_id == 16 || $form_id == 18 || $form_id == 19 || $form_id == 20) // ������ ���������
   print ('<option value="5">������������ �� �������</option><option value="6">��������� �� �������</option><option value="7">������ �� �������</option><option value="8">�����������</option><option value="9">�������������</option><option value="10">� �����</option><option value="12">��������������</option><option value="13">������</option>');
if ($form_id == 2 || $form_id == 5 || $form_id == 6 || $form_id == 7 || $form_id == 10) // ���� � �.�.
   print ('<option value="7">������ �� �������</option>');
if ($form_id == 1) // ��������
   print ('<option value="15">������ �� �������</option>');
if ($form_id == 3 || $form_id == 4 || $form_id == 17) // �������� � �.�.
   print ('<option value="11">�������������</option><option value="13">������</option>');
print ('<option value="14">(�� ���������)</option></select>');
print ('<br><span id="i4" style="display: none"><input type="text" id="data4" value="" onChange="javascript:showLek()"/></span></td>');
//
// 5.1. ����� ����� ����
//
print ('<td id="S4" style="display: block">');
$res = $db->query ('select * from lek_data where tab_id=4'); // ����������� ����
if (!$res || $res->num_rows!=1) die ('<p>�� ������� ������� 4! ������: '.$db->error.'</p>');
$row=$res->fetch_object();
$opts = explode (';',$row->list);
//natsort($opts);
print ($row->tab_name.':<br><select size="10" id="T4" onchange="javascript:selectT4(this)">');
foreach ($opts as $opt)
{
    print ("\n<option value='$opt'>$opt</option>");
}
$res->free();
print ('<option value="">(������)</option></select>');
print ('<br><span id="i5" style="display: none"><input type="text" id="data5" size="7" value="" onChange="javascript:selectTs(document.getElementById(\'Ts\'));showLek()"/>');
print ('<img src="img/plus.png" onClick="javascript:Add(4,5)"></span></td>'."\n");
// �������� ��������� ��� ����������� ���
print ('<td id="S5" style="display: block">�������:<br><select size="10" id="Ts" onchange="javascript:selectTs(this)">');
if ($form_id == 8 || $form_id == 9 || $form_id == 12 || $form_id == 15 || $form_id == 16 || $form_id == 18 || $form_id == 19 || $form_id == 20) // ������ ���������
   print ('<option value="0">������</option><option value="1">��</option><option value="9">��</option><option value="2">����. ����.</option>');
if ($form_id == 1) // ��������
   print ('<option value="9">���.</option>');
if ($form_id == 11) // �������
   print ('<option value="3">��������</option>');
if ($form_id == 17) // ��������
   print ('<option value="4">����.</option>');
if ($form_id == 4) // �������
   print ('<option value="5">������</option>');
if ($form_id == 3) // �����
   print ('<option value="6">�����</option>');
if ($form_id == 13 || $form_id == 14) // �����
   print ('<option value="7">������</option>');
print ('<option value="8">���</option>');
print ('<option value="">(������)</option></select><br><span id="i6" style="display: none"><input type="text" id="data6" size="9" value="" onChange="javascript:showLek()"></span></td>');
//
// 5.2. ����� ����� ���������� (��� ������� � -�������)
//
print ('<td id="S6" style="display: none">');
$res = $db->query ('select * from lek_data where tab_id=7'); // ����� ����������
if (!$res || $res->num_rows!=1) die ('<p>�� ������� ������� 7! ������: '.$db->error.'</p>');
$row=$res->fetch_object();
$opts = explode (';',$row->list);
//natsort($opts);
print ($row->tab_name.':<br><select size="10" id="T7" onchange="javascript:selectT7(this)">');
foreach ($opts as $opt)
{
    print ("\n<option value='$opt'>$opt</option>");
}
$res->free();
print ('<option value="">(������)</option></select>');
print('<br><span id="i7" style="display: none"><input type="text" id="data7" size="9" value="" onChange="javascript:showLek()">');
print ('<img src="img/plus.png" onClick="Add(7,7)"></span></td>'."\n");
//
// 5.4. ����� ������/����� ������ (���, �����)
//
?>
<td id="S7" style="display: none">
����� ��� ������:<br>
<select size="3" id="Tlr" onchange="javascript:selectTlr(this)">
<option value="">����� (-��, -��)</option>
<option value="">������ (-��, -��)</option>
<option value="">���</option>
</select></td>
<!--
//
// 6. ��������� �����
//
-->
<td id="S9" style="display: block">
������� ���:<br>
<select size="10" id="Tv" onchange="javascript:selectTv(this)">
<option value="1">1</option>
<option value="2">2</option>
<option value="3">3</option>
<option value="4">4</option>
<option value="5">5</option>
<option value="6">6</option>
<option value="7">7</option>
<option value="">(������)</option>
</select>
<br>
<span id="i8" style="display: none"><input type="text" id="data8" size="7" value="" onChange="javascript:changeIv();showLek()"/></span>
</td>
<td id="S8" style="display: block">
��������� �����:<br>
<select size="10" id="Tk" onchange="javascript:selectTk(this)">
<option value="� �����">...��� � �����</option>
<option value="� ������">...��� � ������</option>
<option value="����� ����">����� ����</option>
<option value="1 ��� � 3 ���">1 ��� � 3 ���</option>
<option value="����������">����������</option>
<option value="������������">������������</option>
<option value="������������">������������</option>
<option value="���������">���������</option>
</select><br>
<span id="i10" style="display: none"><input type="text" id="data10" value="" onChange="javascript:showLek()"/></span>
<input type="hidden" id="data9" value=""/>
</td>
<?php
//
// ����� �����
//
print ('<td id="S10" style="display: block">');
// ��� ���������
$res = $db->query ('select * from lek_data where tab_id=9');
if (!$res || $res->num_rows!=1) die ('<p>�� ������� ������� 9! ������: '.$db->error.'</p>');
$row=$res->fetch_object();
$opts = explode (';',$row->list);
print ($row->tab_name.':<br><select size="10" id="T9" onchange="javascript:selectT9(this)"><option value="�� ����������� �����">(�� ����������� �����)</option>');
foreach ($opts as $opt)
{
    print ("\n<option value='$opt'>$opt</option>");
}
$res->free();
print ('<option value="">(������)</option></select>');
print ('<br><span id="i11" style="display: none"><input type="text" id="data11" size="33" value="" onChange="javascript:showLek()"/>');
print ('<img src="img/plus.png" onClick="Add(9,11)"></span></td>'."\n");
// ����� ���������
print ('<td id="S11" style="display: block">');
$res = $db->query ('select * from lek_data where tab_id=10');
if (!$res || $res->num_rows!=1) die ('<p>�� ������� ������� 10! ������: '.$db->error.'</p>');
$row=$res->fetch_object();
$opts = explode (';',$row->list);
print ($row->tab_name.':<br><select size="10" id="T10" onchange="javascript:selectT10(this)">');
foreach ($opts as $opt)
{
    print ("\n<option value='$opt'>$opt</option>");
}
$res->free();
print ('<option value="">(������)</option></select>');
print ('<br><span id="i12" style="display: none"><input type="text" id="data12" size="28" value="" onChange="javascript:showLek()"/>');
print ('<img src="img/plus.png" onClick="Add(10,12)"></span></td>'."\n");
//
// ���������� � ����� �����
//
?>
</tr></table>
<table cellspacing="5" cellpadding="5" border="0">
<tr>
<td>������� ������ � ����� �����?</td><td><select id="T11" size="2" onchange="javascript:selectT11(this)">
<option value="1">��</option>
<option value="2" selected>���</option>
</select>
</td>
<td id="S12" style="display: none" align="right">
���� �������: <input type="hidden" id="data13" value=""/><!-- "���� �������" -->
<input type="text" id="data14" size="2" value="" onChange="javascript:selectT12(this)"/>&nbsp;
<select id="dayweek1" onChange="javascript:selectT12(document.getElementById('data14'))"><option value="0">����</option><option value="1">������</option></select>
<input type="hidden" id="data15" value=""/><!-- ����� "����" ��� "������" -->
<input type="hidden" id="data16" value=""/><!-- "���������� ������" -->
&nbsp;���������� ������:&nbsp;<input type="text" id="data17" size="2" value="" onChange="javascript:selectT13(this)"/>&nbsp;
<input type="hidden" id="data18" value=""/><!-- "�������� ����� �������" -->
&nbsp;�������� ����� �������:&nbsp;<input type="text" id="data19" size="2" value="" onChange="javascript:selectT14(this)"/>&nbsp;
<select id="dayweek2" onChange="javascript:selectT14(document.getElementById('data19'))"><option value="0">����</option><option value="1">������</option></select>
<input type="hidden" id="data20" value=""/><!-- ����� "����" ��� "������" -->
</td>
</tr></table></p>
<?php
//
// ������ �����������
//
print ('<form method="post"><input type="hidden" name="lek_name" value="'.$lek_name.'"><input type="hidden" name="form" value="'.$form_name.'"><p>����� �����������: <b>');
print ($form_name);
print (' '.$lek_name.' ');
print ('<input type="text" id="Lek" name="Lek" size="100" value=""/></p>');
print ('<p>���� �����: <input type="text" id="Course" name="Course" size="100" value=""/></p>');
print ('<p><input type="submit" value="��������"/></p></form>');
include ('footer.inc');
?>