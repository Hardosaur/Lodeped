<?php
//
// ���������� � ��������� ������� ������� ���������
// ������� �������� (���.) : $_GET['pat_id']
//
require('../settings.php');
include('header.inc');
require('auth.php'); // �������� ������ �������
require('connect.inc');
?>
<script language="JavaScript" type="text/javascript">
//-------------------------------------------------------------------
// ������� ����������� ����������� ������
//
function showMenu(id_menu)
{
  var my_menu = document.getElementById('m'+id_menu);
  var img = document.getElementById('img'+id_menu);
  if(my_menu.style.display=="none" || my_menu.style.display=="")
  {
       my_menu.style.display="block";
       img.src='img/up.png';
  }
  else
  {
      my_menu.style.display="none";
      img.src='img/down.png';
  }
}

function hoverArrow (id)
{
  var img = document.getElementById('img'+id);
  var path=img.src.substr(img.src.lastIndexOf('/')+1);
  if (path == 'down.png') img.src='img/down2.png';
  if (path == 'up.png') img.src='img/up2.png';
}
function unhoverArrow (id)
{
  var img = document.getElementById('img'+id);
  var path=img.src.substr(img.src.lastIndexOf('/')+1);
  if (path == 'down2.png') img.src='img/down.png';
  if (path == 'up2.png') img.src='img/up.png';
}
//-------------------------------------------------------------------
// ������� ������ ������ ����������� ������
//
function select (id)
{
  var sel = document.getElementById('m'+id);
  var img = document.getElementById('img'+id);
  document.getElementById('in'+id).value=sel.options[sel.selectedIndex].text;
  sel.style.display='none';
  img.src='img/down.png';
}
// -------------------------------------------------------------------
// ������� ���������� ��������������� ������ ��������
//
function addNumber()
{
  document.getElementById("addnumber").value=1;
  document.forms[0].submit();
}
</script>
<?php
$colors = array_fill (0,10,'black'); // ������ ������ ����� �����, �� ��������� ���� ������
$errorstr = ''; // ��������� �� ������
//
// ��������� ����������� ������
//
if (isset($_POST['save'])) // �������� ������ ��� ����������
{
  $fields = $_POST; // ������ ����� �����, ����� �������� �� ���� ��� $_POST
//  print_r($fields);
  if (!(isset($_POST['surname']) && strlen($_POST['surname'])
     && isset($_POST['name']) && strlen($_POST['name'])
     && isset($_POST['lastname']) && strlen($_POST['lastname'])
     && isset($_POST['month']) && strlen($_POST['month']) && is_numeric($_POST['month'])
     && isset($_POST['day']) && strlen($_POST['day']) && is_numeric($_POST['day'])
     && isset($_POST['year']) && strlen($_POST['year']) && is_numeric($_POST['year'])
     && isset($_POST['sex']) && strlen($_POST['sex'])
     && isset($_POST['address']) && strlen($_POST['address'])
     && isset($_POST['phone1']) && strlen($_POST['phone1'])
     && (!strlen($_POST['entrance']) || is_numeric ($_POST['entrance']))
     && (!strlen($_POST['floor']) || is_numeric ($_POST['floor']))
     ))
  { // ������ ������������, ������� �� ����
    $pat_id=$_POST['pat_id'];
    $errorstr='�� ��������� ����������� ����!';
    if (!strlen($fields['surname'])) $colors[0]='red';
    if (!strlen($fields['name'])) $colors[1]='red';
    if (!strlen($fields['lastname'])) $colors[2]='red';
    if (!isset($fields['dispancer']) || !strlen($fields['dispancer'])) { $colors[3]='red'; }
    if (!isset($fields['sex']) || !strlen($fields['sex'])) { $colors[4]='red'; }
    if (!strlen($fields['day']) || !strlen($fields['month']) || !strlen($fields['year'])
    ||  !is_numeric($fields['day']) || !is_numeric($fields['month']) || !is_numeric($fields['year'])
    ) $colors[5]='red';
    if (!strlen($fields['address'])) $colors[6]='red';
    if (strlen($fields['entrance']) && !is_numeric($fields['entrance'])) $colors[7]='red';
    if (strlen($fields['floor']) && !is_numeric($fields['floor'])) $colors[8]='red';
    if (!strlen($fields['phone1'])) $colors[9]='red';
  }
  else // ��������� � ����
  {
      // ��������� ������ � ��������
      if (isset($_POST['pat_id']) && is_numeric($_POST['pat_id']))
      {
          $pat_id=$_POST['pat_id'];
          if ($_POST['year']<1900 || $_POST['month']<0 || $_POST['month']>12 || $_POST['day']<0 || $_POST['day']>31 || (!checkdate($_POST['month'],$_POST['day'],$_POST['year'])) )
          {
            $errorstr = '���� ������� �������!';
            $colors[5]='red';
          }
          else
          {
              $birth = sprintf("%4d-%02d-%02d",$_POST['year'],$_POST['month'],$_POST['day']);
              // ���������� ������ � ��������
              $_POST['floor']+=0;
              $_POST['entrance']+=0;
              $q = 'update patients set dispancer='. $_POST['dispancer']. ', surname="' . $_POST['surname'] . '", name="' . $_POST['name']. '", lastname="' . $_POST['lastname'] .
              '", sex="' . $_POST['sex'] .'", birth="' . $birth . '", address="' . $_POST['address'] . '", floor='.$_POST['floor']. ', entrance='. $_POST['entrance'].
              ', domophone = "' . $_POST['domophone'] . '", comment="' . $_POST['comment'] . '" where pat_id=' . $_POST['pat_id'];
//              print ($q);
              if (!$db->query($q)) $errorstr='��������� ������ �������� �� ���������! ������: '.$db->error;
              else
              {  // ��������� ���������� ������
                 $db->query('delete from phones where pat_id='.$_POST['pat_id']); // �� ������ �� ���������
                 $c=1;
                 $q = 'insert into phones values ';
                 while (isset($_POST['phone'.$c]) && strlen ($_POST['phone'.$c]))
                 {
                     $q.='(' . $pat_id . ', "' . $_POST['phone'.$c] . '", "';
                     if (isset($_POST['operator'.$c]) && strlen ($_POST['operator'.$c])) $q.=$_POST['operator'.$c];
                     $q.='", "';
                     if (isset($_POST['owner'.$c]) && strlen ($_POST['owner'.$c])) $q.=$_POST['owner'.$c];
                     $q.='", "';
                     if (isset($_POST['owner_name'.$c]) && strlen ($_POST['owner_name'.$c])) $q.=$_POST['owner_name'.$c];
                     $q.='"), ';
                     $c++;
                 }
                 $q = rtrim ($q,", "); // ������� ��������� �������
//                 print ('<br>'.$q);
                 if (!$db->query($q)) $errorstr='������ ���������� ������� ���������! ������: '.$db->error;
              }
          }
      }
      else // ������ ����� ������� ������
      {
          // ��������, ���� �� ����� ������� � ��
          $q='select pat_id from patients where surname="'.$_POST['surname'].'" and name="'.$_POST['name'].'" and lastname="'.$_POST['lastname'].'"';
          $chk = $db->query ($q);
          if ($chk && $chk->num_rows)
          {
              $res = $chk->fetch_object();
              $errorstr = '��������� ������� ��� ���� � ���� ������! <a href="patient.php?pat_id='.$res->pat_id.'">������� �� �������� ����� ��������</a>';
              $chk->free();
          }
          else
          {
              if ($_POST['year']<1900 || $_POST['month']<0 || $_POST['month']>12 || $_POST['day']<0 || $_POST['day']>31 || (!checkdate($_POST['month'],$_POST['day'],$_POST['year'])) )
              {
                  $errorstr = '���� ������� �������!';
                  $colors[5]='red';
              }
              else
              {
                  $birth = sprintf("%4d-%02d-%02d",$_POST['year'],$_POST['month'],$_POST['day']);
                  $_POST['floor']+=0;
                  $_POST['entrance']+=0;
                  $q = 'insert into patients values (NULL, '. $_POST['dispancer'] . ', "' . $_POST['surname'] . '", "' . $_POST['name']. '", "' . $_POST['lastname'] .
                  '", "' . $_POST['sex'] . '", "' . $birth . '", "' . $_POST['address'] . '", '. $_POST['floor'] . ', ' . $_POST['entrance'] . ', "' . $_POST['domophone'] . '", "'. $_POST['comment'] . '")';
//                  print ($q);
                  if (!$db->query($q)) $errorstr = '���������� ������ �� ���������! ������: '.$db->error;
              }
          }
          $pat_id='NaN';
          if (!strlen($errorstr))
          {  // ��������� ���������� ������, ����� �������������� ����� pat_id
             $res=$db->query('select LAST_INSERT_ID() from patients');
             if (!$res || !$res->num_rows) $errorstr='���������� ������ �� ���������! ������: '.$db->error;
             else
             {
                 $row=$res->fetch_row();
                 $pat_id=$row[0];
                 $res->free();
                 $c=1;
                 $q = 'insert into phones values ';
//                 print ('<br>'.$q);
                 while (isset($_POST['phone'.$c]) && strlen ($_POST['phone'.$c]))
                 {
                     $q.='(' . $pat_id . ', "' . $_POST['phone'.$c] . '", "';
                     if (isset($_POST['operator'.$c]) && strlen ($_POST['operator'.$c])) $q.=$_POST['operator'.$c];
                     $q.='", "';
                     if (isset($_POST['owner'.$c]) && strlen ($_POST['owner'.$c])) $q.=$_POST['owner'.$c];
                     $q.='", "';
                     if (isset($_POST['owner_name'.$c]) && strlen ($_POST['owner_name'.$c])) $q.=$_POST['owner_name'.$c];
                     $q.='"), ';
                     $c++;
                 }
                 $q = rtrim ($q,", "); // ������� ��������� �������
//                 print ('<br>'.$q);
                 if (!$db->query($q)) $errorstr='������ ���������� ������� ���������! ������: '.$db->error;
             }
          }
      }
  }
  // ��������� ����� ���� ��� ����� ����������� ������
  if (isset($_POST['addnumber']) && strlen($_POST['addnumber']))
  {
      $errorstr='������� �������������� ����� ��������';
      $c=1;
      while (isset($fields['phone'.$c])) $c++;
      $fields['phone'.$c]=$fields['operator'.$c]=$fields['owner'.$c]=$fields['owner_name'.$c]='';
  }
  // �������� �� ���������� ������
  if (!strlen($errorstr))
  {
      if (isset($_POST['contract']) && strlen($_POST['contract'])) print ('<p>������ ������� �������! <a href="contract.php?pat_id='.$pat_id.'">������� � ����������� ��������</a></p>');
      else print ('<p>������ ������� �������.</p><p><a href="patient.php?pat_id='.$pat_id.'">��������� �� �������� ��������</a></p>');
      include ('footer.inc');
      exit;
  }
}
//
// ������ ��� ��� ��� ��������, ��������� ������� �����.
// ��� �������� ��� ������.
//
// 1. ����� �� ���� ���������, ������ �������� �� ����
//
if (isset($_GET['pat_id'])) // ������ ������ �� ���� ��� ���������
{
  $pat_id=$_GET['pat_id'];
  $res=$db->query ('select * from patients where pat_id = '.$pat_id);
  if (!$res || !$res->num_rows) die ('������ ������ ������ �� ����: '.$db->error);
  $fields = $res->fetch_array();
  list ($fields['year'], $fields['month'], $fields['day']) = explode ('-',$fields['birth']); // ������������, ��� ���� �������� � MySQL � ���� YYYY-MM-DD
  if (($phones=$db->query('select * from phones where pat_id = '.$pat_id)) && $phones->num_rows)
  { // ������ ������ ��������� � ������� � ���������� ���� $phone1, $phone2...
    $c=0;
    while ($pr = $phones->fetch_object())
    {
      $c++;
      $fields['phone'.$c] = $pr->number;
      $fields['operator'.$c] = $pr->operator;
      if (isset($pr->owner)) $fields['owner'.$c] = $pr->owner; else $fields['owner'.$c]='';
      if (isset($pr->owner_name)) $fields['owner_name'.$c] = $pr->owner_name; else $fields['owner_name'.$c]='';
    }
    $phones->free();
  }
}
//
// 2. ����� ������
//
else if (!isset($_POST['save']))// ������ ���, ��������� ������ ������ ��������
{
  $fields=array();
  $fields['name']=$fields['surname']=$fields['lastname']=$fields['dispancer']=$fields['sex']=$fields['day']=$fields['month']=$fields['year']=$fields['address']
  =$fields['phone1']=$fields['comment']=$fields['entrance']=$fields['floor']=$fields['domophone']='';
  $fields['operator1']='�����';
  $pat_id='NaN';
}
//
// 3. ������ ������� ��������������� �� $_POST
//
$select1=$select2='';
$selectmale=$selectfemale='';
if ($fields['dispancer']) $select2='selected'; else $select1='selected';
if ($fields['sex']=='male') $selectmale='selected'; else $selectfemale='selected';
print <<<END
<h1>������ ��������</h1>
<p><form method="post" action="pat_edit.php">
<input type='hidden' name='pat_id' value='$pat_id'>
<input type='hidden' name='save' value='1'>
<p style='color: red'>$errorstr</p>
<table class="light">
<tr><td class="left" style='color: $colors[0]'>�������:<td><input type='text' name='surname' size='30' maxlength='30' value='{$fields['surname']}'>
<tr><td class="left" style='color: $colors[1]'>���:<td><input type='text' name='name' size='30' maxlength='30' value='{$fields['name']}'>
<tr><td class="left" style='color: $colors[2]'>��������:<td><input type='text' name='lastname' size='30' maxlength='30' value='{$fields['lastname']}'>
<tr><td class="left" style='color: $colors[3]'>������������ �������:<td><select name='dispancer' size='2'><option value="0" $select1>���</option><option value="1" $select2>��</option></select>
<tr><td class="left" style='color: $colors[4]'>���:<td><select name='sex' size='1'><option value='male' $selectmale>�������</option><option value='female' $selectfemale>�������</option></select>
<tr><td class="left" style='color: $colors[5]'>���� �������� (��.��.����):<td><input type='text' name='day' size='1' maxlength='2' value='{$fields['day']}'>.<input type='text' name='month' size='2' maxlength='2' value='{$fields['month']}'>.<input type='text' name='year' size='4' maxlength='4' value='{$fields['year']}'>
<tr><td class="left" style='color: $colors[6]'>����� ����� ����������:<td><input type='text' name='address' size='30' maxlength='99' value='{$fields['address']}'>
<tr><td class="left" style='color: $colors[7]'>�������:<td><input type='text' name='entrance' size='2' maxlength='5' value='{$fields['entrance']}'>
<tr><td class="left" style='color: $colors[8]'>����:<td><input type='text' name='floor' size='2' maxlength='5' value='{$fields['floor']}'>
<tr><td class="left" style='color: black'>��� ��������:<td><input type='text' name='domophone' size='5' maxlength='9' value='{$fields['domophone']}'>
<tr><td class="left" valign='top' style='color: $colors[9]'>������ ���������:<td>
<table><tr><td>�������� �������:</td><td><input type='hidden' name='owner1' value='���������'><input type='text' name='phone1' size='16' maxlength='30' value='{$fields['phone1']}'></td><td>&nbsp;&nbsp;�����:</td><td><input type='text' name='operator1' size='10' maxlength='12' value='{$fields['operator1']}'><td></td><td></td></td></tr>
END;
$c=2;
$c2=1;
//print_r ($fields);
while (isset($fields['phone'.$c]))
{
  print ("<tr><td>������� �������:</td><td><input type='text' name='phone$c' size='16' maxlength='30' value='{$fields['phone'.$c]}'></td>\n");
  print ("<td>&nbsp;&nbsp;��������:</td><td><input id='in$c2' type='text' name='operator$c' size='10' maxlength='12' value='{$fields['operator'.$c]}'>&nbsp;");
  print ("<img id='img$c2' src='img/down.png' align='middle' alt='������ ���������' onclick='javascript:showMenu($c2)' onmouseover='hoverArrow($c2)' onmouseout='unhoverArrow($c2)'>\n");
  print ("<span style='position:relative'><select style='position: absolute; top: 25; left: -106; display: none; border: solid 1px black;' size='3' id='m$c2' onclick='select($c2)'><option value='velcom'>velcom</option><option value='���'>MTC</option><option value='life:)'>life:)</option></select></span></td>\n");
  $c2++;
  print ("<td>&nbsp;&nbsp;��������:</td><td><input id='in$c2' type='text' name='owner$c' size='10' maxlength='10' value='{$fields['owner'.$c]}'>&nbsp;");
  print ("<img id='img$c2' src='img/down.png' align='middle' alt='������ ���������' onclick='javascript:showMenu($c2)' onmouseover='hoverArrow($c2)' onmouseout='unhoverArrow($c2)'>\n");
  print ("<span style='position:relative;'><select style='position: absolute; top: 25; left: -108; display: none; border: solid 1px black;' size='5' id='m$c2' onclick='select($c2)'><option value='������' selected>������</option><option value='����'>����</option><option value='����'>����</option><option value='�������'>�������</option><option value='�������'>�������</option><option value='����������'>����������</option></select></span></td>\n");
  print ("<td>&nbsp;&nbsp;���, ��������:</td><td><input type='text' name='owner_name$c' size='30' maxlength='49' value='{$fields['owner_name'.$c]}'></td></tr>");
  $c2++;
  $c++;
}
print <<<END2
<tr colspan='8'><td><a class='small' href='javascript:addNumber()'>�������� ����� ��������</a></td></tr>
<input id='addnumber' type='hidden' name='addnumber' value=''>
</table>
<tr><td class="left" valign='top'>���. ����������:<td><textarea name='comment' cols='50' rows='4'>{$fields['comment']}</textarea>
</table>
<p><input type="checkbox" name="contract" value="1">&nbsp;����� ���������� ������ ������� � ���������� ��������</p>
<input class="button" type="submit" value="������ ������"></form>&nbsp;<input type="button" value="������" onClick="javascript:history.go(-1)">
END2;
include ('footer.inc');
?>