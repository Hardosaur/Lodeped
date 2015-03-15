<?php
//
// REPORT.PHP
// ���� ������ ��� ���������� ������ �� �������
// ��� ������ ���������� � $_GET['report']
//
require('../settings.php');
require('auth.php');
require('connect.inc');
//
if (!isset($_SESSION['doctor_id']) || !isset ($_SESSION['pat_id'])) die ('�������� ������ �������! (��� ������ � ������� � ��������)');
// �������� ������
if (isset($_GET['delete']) && is_numeric ($_GET['delete']))
{
  if ($db->query ('delete from report_data where report_id='.$_GET['delete']) && $db->query ('delete from reports where report_id='.$_GET['delete'])) die ('����� ������ �������! <a class="small" href="patient.php?pat_id='.$_SESSION['pat_id'].'">��������� �� �������� ��������</a>');
  else die ('������� ����� �� �������! '.$db->error);
}
// 1: �������� ������ ������
if (isset ($_GET['report']) && is_numeric($_GET['report']))
{
  $report_type = $_GET['report'];
  $report_id=0;
}
// 2: �������������� ��� �������� ������
if (isset($_GET['mode'])) $mode=$_GET['mode'];
//
$vars = array();
date_default_timezone_set ("Europe/Minsk"); // ����� �������� ��������� � ��������� � ���������� ������������ ����
// ID ������
if (isset($_POST['report_id'])) // ������ ������ ��������� �� ����
{
  $mode='edit';
  $report_id = $_POST['report_id'];
  if (isset($_POST['report_type']) && is_numeric($_POST['report_type'])) $report_type=$_POST['report_type'];
  if (!isset($report_type)) die ('������: ������� ��� ������!');
  if (!$report_id) // ������ ��� �� �����������, ����� �� ����������
  {
      if (!isset($_SESSION['doctor_id'])) print ('<p style="color: red">��� ������ � �������!</p>');
      else
      {
          if (!$db->query('insert into reports values (NULL,'.$report_type.',"'.date('Y-m-d').'",'.$_SESSION['doctor_id'].','.$_SESSION['pat_id'].')')) die ('<p style="color: red">������ ���������� ������! '.$db->error.'</p>');
          $res=$db->query('select LAST_INSERT_ID() from reports'); // ������� ����� ������
          if (!$res || !$res->num_rows) die ('<p style="color: red">������: '.$db->error.'</p>');
          $row=$res->fetch_row();
          $report_id=$row[0];
          $res->free();
      }
  }
  else // ������ ��� ���������� ������
  {
      if (!$db->query('delete from report_data where report_id='.$report_id)) die ('<p style="color: red">������ �������� ���������� ������! '.$db->error.'</p>');
  }
  // ��������� ������ �� ���������� ���� ������
  $query = 'insert into report_data values ';
  // ��������� ����������, ������� ��������� ��� ����������� �������
  reset($_POST);
  foreach ($_POST as $key=>$value)
  {
      $value=trim($value);
      if (is_numeric($key) && strlen ($value)) $query .= '('.$report_id.','.$key.',"'.addslashes($value).'"), ';
      $vars[$key]=$value;
  }
  $query = substr ($query,0,-2); // �������� ��� ��������� �������
//  print ('<p>'.$query.'</p>');
  if (!$db->query($query)) die ('<p style="color: red">������ ���������� ������! '.$db->error.'</p>');
}
elseif (isset($_GET['id']) && is_numeric ($_GET['id']))
{
  $report_id=$_GET['id'];
  $res=$db->query ('select report_type from reports where report_id='.$_GET['id']);
  if (!$res || !$res->num_rows) die ('�������� ����� ������! �� ������ � ����. '.$db->error);
  $row=$res->fetch_object();
  $report_type=$row->report_type;
  $res->free();
}
// ������ ������, ���� ��� �� ��������� ����� POST
if (!isset($_POST['report_id']) && $report_id)
{
  $res = $db->query ('select var_id, value from report_data where report_id='.$report_id);
  if (!$res || !$res->num_rows) die ('<p style="color: red">������ ������ �'.$report_id.' �� ��������! ������: '.$db->error.'</p>');
  while ($row = $res->fetch_object())
  {
       $vars[$row->var_id]=$row->value;
  }
  $res->free();
}
// ������ ����� ������
$res = $db->query ('select * from report_types where report_type = '.$report_type);
if (!$res || !$res->num_rows) die ('<p style="color: red">������ ������ �� ��������! ������: '.$db->error.'</p>');
$row=$res->fetch_object();
$report_template = explode("\n",$row->body);
$report_title=$WINDOW_TITLE=$row->title;
$res->free();
// ������� ���������
if (isset($mode))
{
  if ($mode=='print')
  {
      print ('<html><head><link rel="stylesheet" type="text/css" href="main.css">'."\n");
      require ('print.inc');
  }
  elseif ($mode=='view') print ('<html><head><title>�������� ������</title><link rel="stylesheet" type="text/css" href="main.css">');
  else
  {
       include('header.inc');
       print ('<form action="report.php" method="post"><input name="report_id" type="hidden" value="'.$report_id.'"/><input name="report_type" type="hidden" value="'.$report_type.'"/>'."\n");
  }
}
else
{
  include('header.inc');
  print ('<form action="report.php" method="post"><input name="report_id" type="hidden" value="'.$report_id.'"/><input name="report_type" type="hidden" value="'.$report_type.'"/>'."\n");
}
// ������ �����: ��������� #xxx �������� �� ���� ����� ���������� xxx
// print_r ($vars);
foreach ($vars as $key=>$value) $vars[$key]=nl2br(htmlspecialchars($value,ENT_COMPAT,'cp1251'));
//$srch = array ('/#(\d+):(\d+)/e','/@(\d+):(\d+):(\d+)/e');
$srch = array ('/#(\d+):(\d+)/','/@(\d+):(\d+):(\d+)/');
if (isset($mode) && ($mode=='view' || $mode=='print'))
   $rplc = function ($matches) use ($vars) {if (isset ($vars[$matches[1]])) return $vars[$matches[1]];else return '';};
else
$rplc = function ($matches) use ($vars) {if (!isset ($vars[$matches[1]])) $vars[$matches[1]]=''; if ($matches[0][0]=='#') return '<input name="'.$matches[1].'" type="text" size="'.$matches[2].'" value="'.$vars[$matches[1]].'"/>';
else return '<textarea style="vertical-align:top" name="'.$matches[1].'" cols="'.$matches[2].'" rows="'.$matches[3].'">'.$vars[$matches[1]].'</textarea>';};
foreach ($report_template as $line)
{
  // ���� ������������ ������
  if (preg_match('/module\((\w+)\)/i',$line,$matches)) include('report/'.$matches[1].'.inc');
  else
  {
      $line=preg_replace_callback ($srch,$rplc,$line);
      print ('<p>'.$line.'</p>');
  }
}
if (!isset($mode) || $mode=='edit') print ('<br><input type="submit" value="���������"/>&nbsp;<a class="small" href="patient.php?pat_id='.$_SESSION['pat_id'].'">��������� �� �������� ��������</a></form>');
include('footer.inc');
?>