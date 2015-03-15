<?php
if (isset($_SESSION['pat_id']))
{
  $pat_id=$_SESSION['pat_id'];
  $pat = $db->query ('select * from patients where pat_id='.$_SESSION['pat_id']);
  if (!$pat || !$pat->num_rows) print ('Ошибка чтения данных пациента! '.$db->error);
  else
  {
      $row=$pat->fetch_object();
      print ('<p>Фамилия, имя, отчество: '.$row->surname.' '.$row->name.' '.$row->lastname.'</p>');
      print ('<p>Дата рождения: '.join('.',array_reverse(explode('-',$row->birth))).'</p>');
      print ('<p>Адрес: '.$row->address.'</p>');
      $pat->free();
  }
}
else print ('Нет данных о пациенте! (Неверный порядок запуска?)');
?>