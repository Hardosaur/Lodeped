<?php
require('../settings.php');
require('auth.php');
// -----------------------------------------------------------------------------
// ������ �������� ������ (����� ��������� ������ ������)
//
if (isset($_POST['print']))
{
?>
<html><head><title>�� ������</title>
<style>
body { font: italic 11pt "Book Antiqua" }
</style>
<?php
  foreach (explode ("\n",$_POST['print']) as $line) print (rtrim($line).'<br>');
  exit;
}
// -----------------------------------------------------------------------------
// �������� ������ ����� �������
//
include('header.inc');
include('connect.inc');
if (!isset($_GET['id'])) die ('��� ��������� ���������� � ������! (������ �������?)');
$osm_id=$_SESSION['osm_id'];
$osm_type=$_SESSION['osm_type'];
$pat_id=$_SESSION['pat_id'];
$date=$_SESSION['date'];
$res=$db->query('select data from osm_data where osm_id='.$osm_id);
if (!$res || !$res->num_rows) die ('�������� ID ��������� �������! ��� ������ � ����!');
$row = $res->fetch_row();
$vals = explode ($delim,$row[0]); // �������� ���� "��� = ��������"
$res->free();
foreach ($vals as $pair)
{
  list ($id, $value) = explode ('=',$pair);
//  print ($id.':'.$value.'.<br>');
  $values[$id]=stripslashes($value);
}
// ������ ��������
$res=$db->query('select diag from diags where pat_id='.$pat_id.' and redefined=0 and unset_date is null order by set_date desc');
if ($res && $res->num_rows)
{
  $diags='';
  while ($row=$res->fetch_row()) $diags.=' '.$row[0];
  $res->free();
  $values['700']=$diags;
}
// ������ ������������� ����������
$res=$db->query('select lek from leks where pat_id='.$pat_id.' and unset_date is null order by set_date desc');
if ($res && $res->num_rows)
{
  $leks='';
  while ($row=$res->fetch_row()) $leks.=' '.$row[0];
  $res->free();
  $values['1000']=$leks;
}
?>
<table border="0" cellpadding="0" cellspacing="20" width="100%"><col width="250"><col>
<tr valign="top"><td align="left">
<a class="pages" href="osmotr.php?page=1">1. �����</a><br>
<?php
$res=$db->query ('select id, name, suffix, value from osm_template where osm_type='.$osm_type.' and type="page"');
if (!$res || !$res->num_rows) die ('��� ������ � ������������ ������� � ������� �������!');
while ($row = $res->fetch_object())
{
//  if ($row->value == $osm_page) { print ('<span class="pages"><b>'.$row->value.'. '.$row->name.'</b></span><br>'); $page_id=$row->id; }
//  else
      if (strlen($row->suffix)) print ('<a class="pages" href="'.$row->suffix.'.php?page='.$row->value.'">'.$row->value.'. '.$row->name.'</a><br>');
      else print ('<a class="pages" href="osmotr.php?page='.$row->value.'">'.$row->value.'. '.$row->name.'</a><br>');
}
$res->free();
print('</td><td align="left">');
print ('<h2>�������� ����� �������</h2>');
print ('<form method="post" target="_blank"><textarea name="print" cols="80" rows="40">');
$d=explode('-',$date);
print ($d[2].'.'.$d[1].'.'.$d[0].' ');
//
// ������ ������
//
$res=$db->query('select template from osm_print where osm_type='.$osm_type);
if (!$res || !$res->num_rows) die ('�� ������ ������ ������� �'.$osm_type);
$row = $res->fetch_row();
$tmpl=explode("\n",$row[0]);
$res->free();
$count=0;
foreach ($tmpl as $line)
{
  $line = rtrim($line); // ������ ������ ������� � ����� ������, ����, \r
  $count++;
  if (!strlen($line)) continue;
  if ($line{0} == ';') continue; // �����������
  $out = '';
  $found = 0; // ���� ����������� ����������
  $vars = 0; // ����� ��������� ����������
  $p1 = 0; // ������ ���������
  while (1) // �������� ������ �������, � ������� ���������� ���������� ��� %���������� ��� %[������]
  {
      $len = strlen($line);
      if ($p1>=$len) break; // ������ ���������
      while ($p1<$len && $line{$p1}!='%') { $out.=$line{$p1}; $p1++; }
      if ($p1==$len) break; // ������ ���������
      if ($line{$p1+1}=='%') { $p1++; continue; } // ��������� ���� %%
      $p2=$p1+1;
      if ($line{$p2}=='[') // ��������� ������ ���������� ���� %[a,b,c,...n]
      {
          $p2++;
          $id='';
          while ($p2<$len && (($line{$p2}>='0' && $line{$p2}<='9') || $line{$p2}==',' || $line{$p2}==' ')) {$id.=$line{$p2}; $p2++; }
          if ($line{$p2}!=']') die ('<p>������ � ������ '.$count.'! ��� ����������� ������ ��� ������ ���������� �����������!</p>');
          $p2++;
          $vars++;
          $ids = explode (',',$id);
          if (!count($ids)) die ('<p>������ � ������ '.$count.'! ������ ���������� ����!</p>');
          $list=array();
          foreach ($ids as $id)
          {
              $id=trim($id);
              if (isset($values[$id])) $list[] = $values[$id]; // ������ �������� ����������, ��������� � ������
          }
          if (count($list))
          {
              $out .= join(', ', $list);
              $found++;
          }
      }
      else // ��������� ����� ���������� ���� %a
      {
          $id='';
          while ($p2<$len && $line{$p2}>='0' && $line{$p2}<='9') {$id.=$line{$p2}; $p2++; }
          //      $id+=0; // ��������� � �������� ��������
          if ($id<=0) die ('<p>������ � ������ '.$count.'! ������� ������� ���������� ��� �����������!</p>');
          $vars++;
          if (isset($values[$id]))
          {
              $out.=$values[$id];
              $found++;
          }
       }
       $p1=$p2; // ��������� ����������
  }
  if ($found || !$vars) print (str_replace ('<br>',"\n",$out));
}
/*
      $len = strlen($line); // ������������ ����� ������
      while ($pos<$len && $line{$pos}!='[') $pos++;
      if ($pos==$len) break;
      $p1=$pos;
      while ($pos<$len && $line{$pos}!=']') $pos++;
      if ($pos==$len) break; // �� ��� ������ � ������!
      $found--; // ������� ����������
      $var=substr ($line, $p1+1, $pos-$p1-1);

//      print ('������� ���������� '.$var.' � ������� '.$p1.'<br>'); // ��� �������

      if (isset($values[$var])) // ������� ����������, ��������� �� ��������
      {
          $found++; // $found ����� ���������� � 0, ���� ���������� ������� � ���������
          $line = substr_replace($line,$values[$var],$p1,$pos-$p1+1);
          $pos=$p1; // ������ �������, �.�. ����� ������ ����� ����������
      }
  }
  if (!$found) print ($line); // ������� � ��������� ��� ����������, ���� ���������� ���
                              // � ����� ������ �� ���������
}
*/
print('</textarea><br><input type="submit" value="�� ������"/></form><br><input type="button" value="������� ����� �������" onclick="location=\'doctor.php\'"/></table>');
include('footer.inc');
?>