<?php
function check_access_level ($level)
{
  if (!isset($_SESSION['access_level']) ||  $_SESSION['access_level'] > $level) die ('������������ ���� ��� ������� � ���� ��������! <a href="/doctor.php?logout=1">����� ��� ������ �������</a>');
}

function access_level ()
{
  if (isset($_SESSION['access_level'])) return ($_SESSION['access_level']);
  else return 100; // ��� ����� ���������� ������
}
?>