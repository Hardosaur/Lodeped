<?php
//
//
// Проверка наличия в сессии (или во входных данных) логина и пароля доктора,
// а также авторизация с проверкой пароля в БД
//
// Проверка функции выхода
if (isset($_GET['logout']))
{
  session_start();
  session_destroy();
}
if (isset($_POST['doctor_id']) && isset($_POST['doctor_pass']))
{ // Авторизация, т.к. логин/пароль переданы через POST
  $db=new mysqli($dbhost,$dbuser,$dbpass,$dbname);
  if (mysqli_connect_errno()) die ('Connect error: '.mysqli_connect_error());
  $db->query ('set names cp1251');
  $login=$db->real_escape_string($_POST['doctor_id']);
  $pass=$db->real_escape_string($_POST['doctor_pass']);
  $result=$db->query ("select * from doctors where doctor_id='$login' and doctor_pass='$pass'");
  if (!$result) die ('Query error:'.$db->error);
  if (!$row=$result->fetch_object()) print ('<font color=red>Пароль неверен!</font>');
  else
  {
      session_start();
      $_SESSION['access_level']=$row->access_level;
      $_SESSION['doctor_pass']=$row->doctor_pass;
      if ($row->access_level == 2)
      {
          $_SESSION['revisor_id']=$row->doctor_id;
          $_SESSION['dep_id']=-1;
      }
      else
      {
          $_SESSION['doctor_id']=$row->doctor_id;
          $_SESSION['dep_id']=$row->dep_id;
      }
      setcookie ('doctor_id',$row->doctor_id,time()+3600*24*15);
      $result->free();
      $db->close();
      return;
  }
}
// Проверка данных в сессии
session_start();
if (!isset($_SESSION['doctor_id']) && !isset($_SESSION['revisor_id']))
{ // выводим форму авторизации
  $db=new mysqli($dbhost,$dbuser,$dbpass,$dbname);
  if (mysqli_connect_errno()) die ('Connect error: '.mysqli_connect_error());
  $db->query ('set names cp1251');
  $res=$db->query ('select doctor_id, surname from doctors order by surname');
  if (!$res) die ('Query error:'.$db->error);
  include ('header.inc');
?>
<form method='post'>
<table align='center' cellpadding=6 cellspacing=0 frame='border' rules='none' width=400 border=1 bordercolor='black'>
<col align='right'>
<col align='left'>
<tr><td>Фамилия доктора:
<td><select name='doctor_id' size='1'>
<?php
   while ($row=$res->fetch_object())
   {
      print ("<option value='$row->doctor_id'");
      if (isset($_COOKIE['doctor_id']) && $_COOKIE['doctor_id'] == $row->doctor_id) print (' selected');
      print ('>'.$row->surname."</option>\n");
   }
?>
<tr><td>Пароль доступа:
<td><input type='password' name='doctor_pass' size='20' maxlength='20'>
<tr><td><td><input type='submit' value='Войти в систему'>
</table></form>
<?php
   include ('footer.inc');
   exit;
}
else return;
?>