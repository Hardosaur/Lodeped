<?php
//
// RECIPE.PHP
// ���������� �������
//
require('../settings.php');
require('auth.php');
require('connect.inc');
$WINDOW_TITLE = '���������� ��������';
date_default_timezone_set ("Europe/Minsk"); // ����� �������� ��������� � ��������� � ���������� ������������ ����
if (!isset($_SESSION['pat_id'])) die ('�������� ��������� �������! [1]');
$pat_id=$_SESSION['pat_id'];
if (!is_numeric($pat_id)) die ('�������� ��������� �������! [2]');
if (!isset($_SESSION['doctor_id'])) die ('�������� ��������� �������! [3]');
$doctor_id=$_SESSION['doctor_id'];
if (!is_numeric($doctor_id)) die ('�������� ��������� �������! [4]');
// ------------------------------------------------------
if (isset($_GET['preprint'])) // ����� �����������
{
  include('header.inc');
  print ('<h1>���������� ��������</h1>'."\n");
  // ������� �������� ������
  if (isset($_POST['printer']))
  {
      if (!is_numeric($_POST['printer'])) die ('�������� ��������� �������! [6]');
      $printer=$_POST['printer'];
      $_SESSION['printer']=$printer;
  }
  if (!isset($printer) && isset($_SESSION['printer'])) $printer=$_SESSION['printer'];
  $res=$db->query('select * from printer');
  if (!$res || !$res->num_rows) print ('(��� ��������� � ����) ');
  else
  {
      print ('<form name="printer" method="post">������ �������: <select name="printer" size="1">');
      if (!isset($printer)) { print ('<option value="">(�� ������)</option>'."\n"); $printer=0; }
      while ($row = $res->fetch_object())
      {
          print ('<option value="'.$row->id.'"');
          if ($printer == $row->id) print (' selected');
          print ('>'.$row->model.'</option>'."\n");
      }
      $res->free();
      print ('</select><input type="submit" value="�������"></form>'."\n");
  }
  print ('<a href="printer_settings.php" target="_blank">[���������...]</a>&nbsp;<a href="printer_settings.php?new=1" target="_blank">[������� �����...]</a>');
  // ����� ����������
  $date = date('Y-m-d');
  print ('<br><br><h2>����������</h2><table width="100%" border="0">');
  $otc = array(); // �������������� ������
  $res = $db->query ('select lek_id from otc');
  if ($res && $res->num_rows)
  { 
    while ($row=$res->fetch_row())
    {
      $otc[$row[0]]=1;
    }
    $res->free();
  }
  $res=$db->query('select * from leks where pat_id='.$pat_id.' and unset_date is null order by set_date desc');
  if ($res && $res->num_rows)
  {
      while ($row = $res->fetch_object())
      {
          print <<<END1
<form method="post" action="recipe.php" target="_blank"><input type="hidden" name="lek_id" value="$row->id"/>
<tr><td style="padding-bottom: 5px; padding-top: 5px; border-bottom: solid 1px #707070">$row->lek<br>
D.t.dosis No.&nbsp;<input type="text" name="dosis" value="" size="3" maxlength="3"/>&nbsp;
<select name="units" size="1"><option value="" selected>---</option><option value="��.">��.</option><option value="in ampullis">in ampullis</option></select>&nbsp;
<input type="checkbox" name="second"/>&nbsp;��� ������ �����������&nbsp;
END1;
        print ('<input type="submit" value="������" ');
        if (isset($otc[$row->lek_id])) print ('style="border: dotted grey 1px; color: grey"');
        else print ('style="border: double black 2px"');
        print ('/></td></tr></form>');
      }
     $res->free();
     print ('</table>');
  }
}
$paddings=array();
// ------------------------------------------------------
if (isset($_POST['lek_id'])) // ����� �������
{
  $lek_id=$_POST['lek_id'];
  if (!is_numeric($lek_id)) die ('�������� ��������� �������! [5]');
  if (!isset($_POST['dosis']) || !is_numeric($_POST['dosis'])) die ('�� ������� ���������� ���!');
  $dosis = $_POST['dosis'];
  if (isset($_POST['units'])) $units=$_POST['units']; else $units='';
  if (isset($_POST['second'])) $second=1; else $second=0;
  if (!isset($_SESSION['printer']) || !is_numeric ($_SESSION['printer'])) die ('�� ������� ��������� ������!');
  // ��������� ������� ��� ���� ������
  $res=$db->query('select * from printpaddings where printer_id='.$_SESSION['printer']);
  if ($res && $res->num_rows)
  {
      while ($row = $res->fetch_object())
      {
          $paddings[$row->id][0]=$row->padding_x;
          $paddings[$row->id][1]=$row->padding_y;
      }
      $res->free();
  } else die ('������ ������ ���������� ������: '.$db->error);
  // �����
  print ('<html><head><link rel="stylesheet" type="text/css" href="recipe.css">'."\n");
  if (!$second) // ��������� ����� �������
  {
  // ���������� � ������� ����
  $date = date ('d').'&nbsp;&nbsp;&nbsp;&nbsp;'. date ('m').'&nbsp;&nbsp;&nbsp;'.date(' Y');
  print_field1(1,1,$date);

  // ���������� � ������� �������, �������
  $res = $db->query ('select surname, name, lastname, birth from patients where pat_id = '.$pat_id);
  if ($res && $res->num_rows)
  {
      $row = $res->fetch_object();
      $birth=explode('-',$row->birth);
      $patient = $row->surname.' '.$row->name{0}.'. '.$row->lastname{0}.'.';
      $res->free();
  }
  print_field1 (2,2,$patient);

  $days =  (int)((time()-mktime(0,0,0,$birth[1],$birth[2],$birth[0]))/86400); // ������� � ����
  if ($days < 31 ) $age = $days . ' ����';
  elseif ($days < 365)
  {
       $mday = array (31,28,31,30,31,30,31,31,30,31,30,31,31,28,31,30,31,30,31,31,30,31,30,31);
       print ($days.' ');
       $month=0; $off=$birth[1];
       while ($days >= $mday[$month+$off]) {  $days-=$mday[$month+$off]; $month++; }
       $age = $month .' ���. ';
       if ($days) $age.= $days .' ����';
  }
  elseif ($days < 1097)
    {
       $age = (int)($days/365);
       $days-=$age*365;
       if ($age == 1) $age.=' ���'; else $age.=' ����';
       if ((int)($days/30)) $age .= ' '.(int)($days/30).' ���.';
  }
  else $age = (int) ($days/365.26) . ' ���';
  print_field1(3,2,$age);

  // ���������� � ������� ������� �����
  $res=$db->query ('select * from doctors where doctor_id='.$doctor_id);
  if ($res && $res->num_rows==1)
      {
          $row=$res->fetch_object();
          $doctor=$row->surname.' '.$row->name{0}.'.'.$row->lastname{0}.'.';
          $res->free();
      }
      else die ('�� ������� ������ � ����� ('.$db->error.')!');
  print_field1 (4,2,$doctor);
  } // if second
  // ������� �������� � ����� ���������, ����� �����������
  $res=$db->query('select * from leks, lek_names where leks.id='.$lek_id.' and lek_names.lek_id=leks.lek_id');
  if ($res && $res->num_rows==1)
  {
      $row = $res->fetch_object();
      $lek = $row->lek;
      $rp = $row->lname;
      $rname = $row->rname;
      $res->free();
  } else die ('�� ������� �������� ���������� ('.$db->error.')!');
  $indent='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
  $form= $db->query('select * from lek_forms where form_id='.$row->form_id);
  if ($form && $form->num_rows==1)
  {
      $frow = $form->fetch_object();
      if ($lek{strlen($frow->rname)} == ' ') // �������� �������� ����� �������
      {
          $lek = substr ($lek, strlen($frow->rname)+1);
      }
      else // ���-�� ������� ��������� ����� �������
      {
          // print ('[!]');
          $cnt=0;
          while ($lek{$cnt}!=' ') $cnt++;
          $lek = substr ($lek, $cnt+1);
      }
      $rp = $frow->lname.' '.$rp;
      if ($frow->form_id == 11) // �������
         $addition = $indent.' Glucosi 0.1<br>Misce, fiat pulvis.<br>';
      else $addition = '';
      $form->free();
  } else print ('�� ������� �������� ����� ������� ��������� ('.$db->error.')!');
  // �������� � ���������� ����� �����������
  if ($lek{strlen($rname)} == ' ') // �������� �������� ���������
  {
     $lek = substr ($lek, strlen($rname)+1);
  }
  else // ���-�� ������� ��������� �������� ���������
  {
      print ('[!]');
      $cnt=0;
      while ($lek{$cnt}!=' ') $cnt++;
      $lek = substr ($lek, $cnt+1);
  }
  $words = explode (' ',$lek);
  while (is_numeric($words[0]{0}) || $words[0]{0}=='{' || $words[0]{0}=='(' || $words[0] == '��') $rp.=' '.array_shift($words); // ��������� ���������, ������������ � ������ ��������� ��������
  $signa=implode(' ',$words);
  if ($second) { $paddings[5]=$paddings[7]; $paddings[6]=$paddings[8]; $div='<div style="text-align:center">#</div>'; } else $div='';
  print_field2 (5,6,3,$div.$indent.$rp.'<br>'.$addition.'D.t.dosis No. '.$dosis.' '.$units.'<br>S. '.$signa);
  include ('footer.inc');
}
function print_field1 ($id, $style, $string)
{
  global $paddings;
  print ('<div class="style'.$style.'" style="margin: 0; padding: 0; position:absolute; top: '.$paddings[$id][1].'mm; left: '.$paddings[$id][0].'mm;">'.$string.'</div>'."\n");
}
function print_field2 ($id1, $id2, $style, $string)
{
  global $paddings;
  print ('<div class="style'.$style.'" style="margin: 0; padding: 1mm; position:absolute; top: '.$paddings[$id1][1].'mm; left: '.$paddings[$id1][0].'mm; width: '.($paddings[$id2][0]-$paddings[$id1][0]).'mm; height: '.($paddings[$id2][1]-$paddings[$id1][1]).'mm;">'.$string.'</div>'."\n");
}

?>