<?php
function check_access_level ($level)
{
  if (!isset($_SESSION['access_level']) ||  $_SESSION['access_level'] > $level) die ('Недостаточно прав для доступа к этой странице! <a href="/doctor.php?logout=1">Войти под другим паролем</a>');
}

function access_level ()
{
  if (isset($_SESSION['access_level'])) return ($_SESSION['access_level']);
  else return 100; // тут можно логировать ошибку
}
?>