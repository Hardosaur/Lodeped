<?php
define ('MODE_COMMON', 0); // изменение протокола осмотра
define ('MODE_EDIT', 1); // редактирование полей формы
define ('MODE_TYPE', 2); // формирование списка полей конкретного типа осмотра
define ('MODE_NEW', 3); // создание нового протокола осмотра (до первого сохранения)
define ('MODE_COPY', 4); // создание копии существующего протокола (до первого сохранения)
define ('MODE_PRINT',5); // вывод на печать
define ('MODE_VIEW',6); // просмотр результатов осмотра
define ('MODE_PREPRINT',7); // запрос параметров вывода на печать

// Класс управления формой осмотра

class cOsmotr
{
  private $editmode; // режим редактирования/заполнения формы
  private $aFields; // коллекция полей формы осмотра
  private $vEnabled; // вектор активных полей (для выбранного типа осмотра)
  private $uPatID; // ID пациента
  private $uOsmID; // ID осмотра
  private $uOsmType; // тип осмотра
  private $uDoctor; // ID доктора
  private $sDate; // строка даты
  private $sBirth; // дата рождения пациента
  public  $sTitle; // название осмотра
  public  $sDescription; // описание осмотра
  private $aValues; // значения полей, прочитанные из уже существующего осмотра
  private $aComments; // комментарии
  private $sMessage; // сообщение о выполнении некоторой операции
  function __construct ($mode) // конструктор
  {
      // выбираем режим вывода
      $this->editmode=$mode;
      $this->sMessage='';
      global $db;
      date_default_timezone_set ("Europe/Minsk"); // чтобы избежать сообщения о проблемах с получением неправильной даты
      switch ($mode)
      {
          case MODE_COPY:
          case MODE_PRINT:
          case MODE_PREPRINT:
          case MODE_COMMON:
          case MODE_VIEW:
           // обычный режим
          if (!isset($_GET['id'])) $this->error('Внутренняя ошибка [3]');
          $this->uOsmID = $_GET['id'];
          if (!is_numeric ($this->uOsmID) || !$this->uOsmID) $this->error('Неверный ID осмотра! ('.$this->uOsmID.')');
          if ($mode==MODE_COPY && $_GET['copy'])
          {
              $this->uOsmType=$_GET['copy']; // переопределить тип осмотра
              $res=$db->query ('select * from osm_info, osm_types where osm_info.osm_id='.$this->uOsmID.' and osm_types.osm_type='.$this->uOsmType);
              if (!$res || $res->num_rows!=1) $this->error ('Ошибка чтения базы осмотров! '.$db->error);
              $row=$res->fetch_object();
          }
          else
          {
              $res=$db->query ('select * from osm_info, osm_types where osm_info.osm_id='.$this->uOsmID.' and osm_info.osm_type=osm_types.osm_type');
              if (!$res || $res->num_rows!=1) $this->error ('Ошибка чтения базы осмотров! '.$db->error);
              $row=$res->fetch_object();
              $this->uOsmType=$row->osm_type;
          }
          $this->uPatID=$row->pat_id;
          $this->sDate=$row->date;
          $this->uDoctor=$row->doctor_id;
          if ($mode==MODE_COPY) // дата осмотра текущая
          {
              $this->aValues['Date3']=date('Y');
              $this->aValues['Date2']=date('m');
              $this->aValues['Date1']=date('d');
              $this->sDate=date('Y-m-d');
          }
          else // дата берется из базы
          {
              $date2=explode('-',$this->sDate);
              $this->aValues['Date3']=$date2[0];
              $this->aValues['Date2']=$date2[1];
              $this->aValues['Date1']=$date2[2];
          }
          if ($mode == MODE_PREPRINT)
          {
              $this->sTitle='Параметры печати отчета';
              break;
          }
          // данные для модулей
          $_SESSION['pat_id']=$row->pat_id;
          $_SESSION['date']=$this->sDate;
          $_SESSION['osm_id']=$this->uOsmID;
          $this->sTitle=$row->title;
          $this->sDescription=$row->description;
          $this->vEnabled = explode(',',$row->vorder);
          if (!count($this->vEnabled)) $this->sMessage='Внимание! Выбранный тип осмотра не содержит активных полей!';
          $res->free();
          // получаем дату рождения
          $res=$db->query ('select birth from patients where pat_id='.$this->uPatID);
          if (!$res || $res->num_rows!=1) $this->error('Ошибка получения данных пациента ['.$this->uPatID.'] '.$db->error);
          $row=$res->fetch_row();
          $this->sBirth=$row[0];
          $res->free();
          break;

          case MODE_NEW: // новый осмотр
          $this->uPatID=$_GET['pat_id'];
          if (!is_numeric ($this->uPatID) || !$this->uPatID) $this->error('Неверный ID пациента! ('.$this->uPatID.')');
          $this->aValues['Date3']=date('Y');
          $this->aValues['Date2']=date('m');
          $this->aValues['Date1']=date('d');
          $this->sDate=date('Y-m-d');
          // данные для модулей
          $_SESSION['pat_id']=$this->uPatID;
          $_SESSION['date']=$this->sDate;
          $_SESSION['osm_id']=0;
          // получаем дату рождения
          $res=$db->query ('select birth from patients where pat_id='.$this->uPatID);
          if (!$res || $res->num_rows!=1) $this->error('Ошибка получения данных пациента ['.$this->uPatID.'] '.$db->error);
          $row=$res->fetch_row();
          $this->sBirth=$row[0];
          $res->free();
          // break пропущен намеренно
          case MODE_TYPE:
          $this->uOsmType = $_GET['type'];
          if (!is_numeric($this->uOsmType) || !$this->uOsmType) $this->error ('Ошибка передачи типа осмотра! ('.$this->uOsmType.')');
          $res = $db->query ('select vorder, title, description from osm_types where osm_type = '.$this->uOsmType);
          if (!$res || !$res->num_rows) $this->error ('Ошибка чтения базы типов осмотров! '.$db->error);
          $row=$res->fetch_row();
          $res->free();
          $this->vEnabled = explode(',',$row[0]);
          if (!count($this->vEnabled)) $this->sMessage='Внимание! Выбранный тип осмотра не содержит активных полей!';
          $this->sTitle=$row[1];
          $this->sDescription=$row[2];
          break;

          case MODE_EDIT: // редактирование всех полей
          $this->uOsmID = 0;
          $this->sTitle='Редактирование всех полей';
          break;

          default: $this->error ('Неверный режим работы скрипта осмотра!');
      }
  }
  // -----------------------------------------------------------------------------------------------------------------
  function print_hidden_fields() // скрытые поля формы, необходимые для работы скрипта
  {
      print ('<form method="post"><p><input class="button" style="background-color: #dddddd" type="submit" value="Сохранить"/>'."\n");
      print ('<input type="button" class="button" onclick="window.location=\'patient.php?pat_id='.$this->uPatID.'\'" value="Страница пациента"/>'."\n");
      switch ($this->editmode)
      {
          case MODE_COMMON:
          print ('<input type="hidden" name="action" value="update">');
          print ('<input type="hidden" name="osm_id" value="'.$this->uOsmID.'">');
          print ('<input type="hidden" id="birth" value="'.$this->sBirth.'">');
          break;

          case MODE_NEW:
          case MODE_COPY:
          print ('<input type="hidden" name="action" value="create">');
          print ('<input type="hidden" name="pat_id" value="'.$this->uPatID.'">');
          print ('<input type="hidden" name="type" value="'.$this->uOsmType.'">');
          print ('<input type="hidden" id="birth" value="'.$this->sBirth.'">');
          break;

          case MODE_TYPE:
          print ('<input type="hidden" name="action" value="type">');
          print ('<input type="hidden" name="type" value="'.$this->uOsmType.'">');
          break;

          case MODE_EDIT:
          print ('<input type="hidden" name="action" value="edit">');
          break;

          default: $this->error('Внутренняя ошибка [1]');
      }
  }
  // -----------------------------------------------------------------------------------------------------------------
  function init_fields ()
  {
  // читаем описания полей из базы
      global $db;
      $res = $db->query ('select * from osm_fields order by ordr');
      if (!$res || !$res->num_rows) $this->error ('Ошибка чтения базы полей формы осмотра! '.$db->error);
      while ($this->aFields[]=$res->fetch_object()); // заполнение массива полей
      $res->free();
  }
  // -----------------------------------------------------------------------------------------------------------------
  function out () // вывести форму осмотра
  {

      $this->init_fields();
      //print_r ($this->aFields);
      global $delim;
      global $db;
      $values=array();
      $comments=array();
      // печать сообщения
      if (strlen($this->sMessage)) print ($this->sMessage);
      //
      switch ($this->editmode) // поля, которые обязательны для различных режимов работы
      {
          case MODE_EDIT:
               print ('&nbsp;<a href="admin/admin.php">Вернуться в панель администрирования</a>');
               break;
          case MODE_TYPE:
               print ('&nbsp;<a href="admin/admin.php">Вернуться в панель администрирования</a>');
               print ('<tr><td class="left">Заголовок отчета</td><td class="field" colspan="3"><input class="field" id="title" name="title" type="text" value="'.$this->sTitle.'" onchange="doHighlight(\'title\')"/>');
               break;
          case MODE_COMMON:
          case MODE_COPY:
               $this->read_values();
          case MODE_NEW:
               $values=$this->aValues;
               $comments=$this->aComments;
//          print_r($values);
//          print_r($comments);
               include ('osm_form/date.inc'); // жестко заданные поля для всех видов осмотров
               include ('osm_form/age.inc');
      }
//      if (isset($this->aValues)) $values=$this->aValues;
      //

      foreach ($this->aFields as $field)
      {
         if (!isset($field->type)) continue;
         $id=$field->id;
         switch ($this->editmode)
         {
             case MODE_COMMON: case MODE_NEW: case MODE_COPY:
             if (in_array($id, $this->vEnabled))
             {
                 print ('<tr>');
                 if ($field->type != 'header' && $field->type != 'section' && $field->type!= 'hr')
                 {
                     print ('<td class="left">'.$field->name.'</td><td class="field" id="td'.$id.'">'); //  onclick="doHide('.$id.')"
                     include ('osm_form/'.$field->type.'.inc');
                     print ('</td><td>(<input type="text" class="comment" name="c'.$id.'"');
                     if (isset($this->aComments[$id])) print (' value="'.htmlspecialchars($this->aComments[$id],ENT_COMPAT,'cp1251').'"');
                     print ('/>)</td>'."\n");
                 }
                 else include ('osm_form/'.$field->type.'.inc');
                 print ('</tr>');
             }
             break;

             case MODE_EDIT: // редактирование полей
                 print ('<tr><td style="width:30px"><a href="field_new.php?after='.$id.'" title="Добавить новое поле после текущего">&oplus;</a>&nbsp;');
                 print ('<a href="osmotr2.php?do=up&id='.$id.'" title="Переместить поле вверх">&uArr;</a>&nbsp;');
                 print ('<a href="field_edit.php?id='.$id.'" title="Редактировать поле">&Theta;</a>&nbsp;');
                 print ('<a href="osmotr2.php?do=delete&id='.$id.'" title="Удалить" onclick="return confirm (\'Параметр будет удален из всех осмотров, восстановление невозможно. Выполнить?\')">&otimes;</a></td>');
                 if ($field->type != 'header' && $field->type != 'section' && $field->type!='hr') print ('<td class="left">'.$field->name.'<td class="field">');
                 include ('osm_form/'.$field->type.'.inc');
                 print ('</td></tr>'."\n");
                 break;

             case MODE_TYPE: // редактирование типа осмотра
             print ('<tr>');
             if ($field->type != 'header' && $field->type != 'section' && $field->type!='hr')
             {
                 if (in_array($id, $this->vEnabled)) $enabled=true; else $enabled=false;
                 print ('<input type="text" class="hidden" name="field'.$id.'" id="field'.$id.'" value="');
                 if ($enabled) print ('1');
                 print ('"/><td class="left" onclick="doMark(this,'.$id.')"');
                 if (!$enabled) print (' style="color:#909090"');
                 print ('>'.$field->name.'<td class="field" id="td'.$id.'" style="visibility: ');
                 if ($enabled) print ('visible'); else print ('hidden');
                 print ('">');
                 include ('osm_form/'.$field->type.'.inc');
                 print ('</td>'."\n");
             }
             else
             {
                 if (in_array($id, $this->vEnabled)) $enabled=true; else $enabled=false;
                 print ('<input type="hidden" id="field'.$id.'" name="field'.$id.'" value="');
                 if ($enabled) print ('1');
                 print ('"/><td colspan="3" class="'.$field->type.'" onclick="doMarkHeader(this,'.$id.')"');
                 if (!$enabled) print (' style="color:#909090"');
                 print ('>'.$field->name."</td>\n");

             }
             print ('</tr>');
             break;
         }
      }
      print ('</table><p><input class="button" style="background-color: #dddddd" type="submit" value="Сохранить"/>'."\n");
      print ('<input type="button" class="button" onclick="window.location=\'patient.php?pat_id='.$this->uPatID.'\'" value="Страница пациента"/></p></form>'."\n");
  }
  // --------------------------------------------------------------------------------------------------------
  function process_input () // обработка входных данных из формы, возврат готовой строки для хранения в базе
  {
          global $delim;
          global $db;
          // просканируем входные данные
          reset($_POST);
          $values=array(); // ассоциативный массив значений переменных
          $comments=array();
          while (list($key,$value) = each($_POST))
          {
              if (is_numeric($key)) $values[$key]=$value;
              if (sscanf($key,'c%d',$id)) $comments[$id]=$value;
          }
          $strings=array();
          foreach ($values as $key=>$value)
          {
              if (!strlen($value) && !strlen($comments[$key])) continue;
              $strings[$key]=$key.'='.$value.'{'.$comments[$key];
              //if (isset($comments[$key])) $data[$key].=$comments[$key];
          }
          foreach ($comments as $key=>$value)
          {
              if (isset($values[$key])) continue;
              else $strings[$key]=$key.'='.'{'.$value;
          }
          return ($db->real_escape_string (implode($delim,$strings)));
  }
  // -----------------------------------------------------------------------------------------------------------------
  function dispatch_action () // обработка входных данных, переданных через POST
  {
      if (!isset($_POST['action'])) return;
      global $db;
      switch ($_POST['action'])
      {
          case 'edit': return;

          case 'update': // обновить данные в протоколе осмотра
          if (isset($_POST['osm_id']) && is_numeric($_POST['osm_id']) && $_POST['osm_id']>0) $this->uOsmID=$_POST['osm_id'];
          else $this->error ('Внутренняя ошибка [2]');
          // проверим, не изменил ли пользователь дату (а он может)
          if (isset($_POST['Date3']))
          {
              $newdate = sprintf("%4d-%02d-%02d",$_POST['Date3'],$_POST['Date2'],$_POST['Date1']);
              $res=$db->query('select date from osm_info where osm_id='.$this->uOsmID);
              if (!$res || !$res->num_rows) $this->error ('Нет данных осмотра в базе! '.$db->error);
              $row=$res->fetch_row();
              $date=$row[0];
              $res->free();
              if ($newdate != $date) // нужно обновить дату в записи об осмотре
              {   //print ($newdate);
                  if (!$db->query('update osm_info set date="'.$newdate.'" where osm_id='.$this->uOsmID)) $this->error ($db->error);
                  $date=$newdate;
              }
              $_SESSION['date']=$date;
              $this->sDate=$newdate;
              $date2=explode('-',$this->sDate);
              $this->aValues['Date3']=$date2[0];
              $this->aValues['Date2']=$date2[1];
              $this->aValues['Date1']=$date2[2];
          }
          $data = $this->process_input();
          //print ('"'.$data.'"');
          //
          // Обновление информации об осмотре
          //
          $query = 'update osm_data set data="'.$data.'" where osm_id='.$this->uOsmID;
          if (!$db->query($query)) $this->error ('Обновление данных в базе не прошло! Ошибка: '.$db->error);
          $this->sMessage.=' Данные успешно сохранены.';
          break;

          case 'create': // создать новый осмотр и сохранить в нем данные
          $date = sprintf("%4d-%02d-%02d",$_POST['Date3'],$_POST['Date2'],$_POST['Date1']);
          $this->uOsmType=$_POST['type'];
          $this->uPatID=$_POST['pat_id'];
          if (!isset($_SESSION['doctor_id'])) $this->error ('Не найден ID доктора!');
          if (!$db->query ('insert into osm_info values (NULL, '.$this->uOsmType.','.$this->uPatID.','.$_SESSION['doctor_id'].',"'.$date.'",NULL)')) $this->error('Не удалось создать запись о новом осмотре! '.$db->error);
          $res=$db->query('select LAST_INSERT_ID() from osm_info'); // выясним номер записи
          if (!$res || !$res->num_rows) die ('Ошибка: '.$db->error);
          $row=$res->fetch_row();
          $osm_id=$row[0];
          $res->free();
          $data = $this->process_input();
          //print ('"'.$data.'"');
          $query = 'insert into osm_data values ('.$osm_id.',"'.$data.'")';
          if (!$db->query($query)) $this->error ('Обновление данных в базе не прошло! Ошибка: '.$db->error);
          //print ('<a href="'.$_SERVER['PHP_SELF'].'?id='.$osm_id.'">Дальше</a>');
          header ('Location: '.$_SERVER['PHP_SELF'].'?id='.$osm_id);
          exit();

          case 'type':
          $this->save_type();
          break;

          default: $this->error ('Внутренняя ошибка [5]');
      }
  }
  // -----------------------------------------------------------------------------------------------------------------
  function read_values()
  {
      if ($this->editmode != MODE_COMMON && $this->editmode != MODE_COPY && $this->editmode != MODE_PRINT && $this->editmode != MODE_VIEW) return;
      global $db;
      global $delim;
      $res = $db->query ('select data from osm_data where osm_id='.$this->uOsmID);
      if (!$res || $res->num_rows != 1) $this->error ('Ошибка чтения базы данных осмотров! '.$db->error);
      $row=$res->fetch_row();
      $data=$row[0];
      $res->free();
      // парсим
      foreach (explode ($delim, $data) as $pair)
      {
          sscanf ($pair,'%d=%[^{]{%[^{]',$key,$val,$comments);
          if (strlen($key) && !strlen($val)) sscanf ($pair,'%d={%[^{]',$key,$comments); // отдельный шаблон для случаев, когда не указано значение, но есть комментарий
//          print ('<br>'.$key.'='.$val.'('.$comments.')');
          if (!strlen($key) || !is_numeric($key)) continue;
          if (isset($val) && strlen($val)) { $this->aValues[$key]=$val; unset($val); }
          if (isset ($comments) && strlen($comments)) { $this->aComments[$key]=$comments; unset ($comments); }
      }
  }
  // -----------------------------------------------------------------------------------------------------------------
  function preprint()
  {
      global $db;
      print ('<form method="post" action="osmotr2.php?print=1&id='.$this->uOsmID.'">'."\n");
      print ('<input type="checkbox" name="head_out" value="1"/>&nbsp;Печатать шапку<br><input type="checkbox" name="name_out" value="1"/>&nbsp;Печатать ФИО<br>'."\n");
      print ('<h2>Диагнозы</h2>'."\n");
      include('osm_form/diag.inc');
      print ('<h2>Назначения</h2>'."\n");
      include('osm_form/lek.inc');
      print ("\n".'<input type="submit" value="Далее >>"/></form>');
  }
  // -----------------------------------------------------------------------------------------------------------------
  function print_() // linear format
  {
      $this->init_fields();
      global $delim;
      global $db;
      $this->read_values();
      $values=array();
      $comments=array();
      $values=$this->aValues;
      $comments=$this->aComments;
      $id=$this->uOsmID;
      unset ($_SESSION['pat_id']);
      unset ($_SESSION['osm_id']);
      // вычислим возраст
      $birth=explode('-',$this->sBirth);
      $osmdate = explode('-',$this->sDate);
      $age = floor((mktime(0,0,0,$osmdate[1],$osmdate[2],$osmdate[0])-mktime(0,0,0,$birth[1],$birth[2],$birth[0]))/86400)-1.0; // возраст в днях
      $age2=array();
      $age2[0]=floor($age/365.25);
      $age2[1]=floor(($age%365)/30);
      $age2[2]=($age%365)%30;
      $age2[2]-=floor($age2[1]/2);
      $agestr='';
      if (!$age2[0] && $age2[2]>0) // нужны дни
      {
          $agestr=' '.$age2[2].' ';
          if ($age2[2]%10==1 && $age2[2]!=11) $agestr.='день';
          elseif ($age2[2]%10<5 && intval($age2[2]/10)!=1) $agestr.='дня';
          else $agestr.=' дней';
      }
      if ($age2[1]>0) // нужны месяцы
      {
          if ($age2[1]==1) $agestr=' месяц'.$agestr;
          elseif ($age2[1]<5) $agestr=' месяца'.$agestr;
          else $agestr=' месяцев'.$agestr;
          $agestr=$age2[1].$agestr;
      }
      if ($age2[0]) // нужны годы
      {
          if ($age2[0]%10==1) $agestr=' год '.$agestr;
          elseif ($age2[0]%10<5 && intval($age2[0]/10)!=1) $agestr=' года '.$agestr;
          else $agestr=' лет '.$agestr;
          $agestr=$age2[0].$agestr;
      }
      if ($this->editmode == MODE_PRINT)
      {
      // информация о докторе
      $res=$db->query ('select * from doctors where doctor_id='.$this->uDoctor);
      if ($res && $res->num_rows==1)
      {
          $row=$res->fetch_object();
          $signature='</p><p style="margin-left: 40%">Врач ';
          if ($row->category==0) $signature.='высшей категории';
          elseif ($row->category<3) $signature.=$row->category.'-ой категории';
          if ($row->speciality) $signature.=', '.$row->speciality.' ';
          $signature.='_________ ('.$row->surname.' '.$row->name{0}.'.'.$row->lastname{0}.'.)</p>';
          $res->free();
      }
      // Вывод шапки
print ('<html><head><link rel="stylesheet" type="text/css" href="print.css">'."\n");
require ('print.inc');
if (isset($_POST['head_out'])) print ('<img src="printhead.png" style="width:180mm;margin-bottom:10px"><br>');
else print ('<img src="smallhead.png" width="50" height="50" align="left" style="margin-right: 10px">'."\n");
      }
      else
      {
         print ('<html><head><title>Просмотр результатов осмотра</title><link rel="stylesheet" type="text/css" href="main.css">');
      }
      print ('<h1>'.$this->sTitle.'</h1>'."\n");
      print ('<h3>Дата: '.join('.',array_reverse(explode('-',$this->sDate))).'</h3>'."\n");
      // Проверка, состоит ли пациент на договоре
      $res = $db->query('select contract from contracts where pat_id = '.$this->uPatID.' and valid>0');
      if (!$res || !$res->num_rows || isset($_POST['name_out']))
      {
          $res = $db->query ('select surname, name, lastname, address from patients where pat_id = '.$this->uPatID);
          if ($res && $res->num_rows)
          {
              $pat = $res->fetch_object();
              print ('<h3>'.$pat->surname.' '.$pat->name.' '.$pat->lastname.', '.$birth[2].'.'.$birth[1].'.'.$birth[0].'<br>');
              print ($pat->address.'</h3>'."\n");
              $res->free();
          }
      }
      else $res->free();
      print ('<p>Возраст: '.$agestr.'<br>'."\n");
      // Обработка полей
      $headers = array(); // стек заголовков
      foreach ($this->aFields as $field)
      {
         if (!isset($field->type)) continue;
         $id=$field->id;
         if (!in_array($id, $this->vEnabled)) continue;
         switch ($field->type)
         {
             case 'header':
             if (isset($headers[1])) unset ($headers[1]);
             $headers[0]=$field->name;
             break;

             case 'section':
             $headers[1]=$field->name;
             break;

             case 'module':
             if (isset($headers[0]))
             {
                 print ('</p>'."\n".'<h1>'.$headers[0].'</h1>'."\n".'<p>');
                 unset ($headers[0]);
             }
             if (isset($headers[1]))
             {
                 print ('</p>'."\n".'<h2>'.$headers[1].'</h2>'."\n".'<p>');
                 unset ($headers[1]);
             }
             // print ('</p><p>'.$field->name.': ');
             include ('osm_form/module.inc');
             print ('</p><p>');
             break;

             case 'hr':
             print ('<hr style="color: black" size="0" noshade>');
             break;

             case 'teeth':
             $id=$field->id;
             if ( ($this->editmode==MODE_PRINT || $this->editmode==MODE_VIEW) && (!isset($values[$id]) || !strlen(trim($values[$id])))) break;
             if (isset($headers[0]))
             {
                 print ('</p>'."\n".'<h1>'.$headers[0].'</h1>'."\n".'<p>');
                 unset ($headers[0]);
             }
             if (isset($headers[1]))
             {
                 print ('</p>'."\n".'<h2>'.$headers[1].'</h2>'."\n".'<p>');
                 unset ($headers[1]);
             }
//             print ('['.$values[$id].']');
             include ('osm_form/teeth.inc');
             if (isset ($comments[$id])) print (' ('.$comments[$id].')');
             break;

             default:
             if (isset($values[$id]) || isset($comments[$id]))
             {
             if (isset($headers[0]))
             {
                 print ('</p>'."\n".'<h1>'.$headers[0].'</h1>'."\n".'<p>');
                 unset ($headers[0]);
             }
             if (isset($headers[1]))
             {
                 print ('</p>'."\n".'<h2>'.$headers[1].'</h2>'."\n".'<p>');
                 unset ($headers[1]);
             }
             $value='';
             if (isset($values[$id]))
             {
                if ($field->type == 'area') $value=str_replace("\n",'<br>',$values[$id]);
                elseif ($field->type == 'table') $value=rtrim(str_replace('<BR>',' ',$values[$id]),'; ');
                else $value=$values[$id];
             }
             if (isset($field->template))
             {
                print (str_replace('$',$value,$field->template));
                if (isset ($comments[$id])) print (' ('.$comments[$id].')');
                if ($field->template{strlen($field->template)-1}!='+') print ('. '); else print (' ');
             }
             else // шаблон не задан, печатаем по умолчанию
             {
                if ($field->type == 'check')
                {
                   print ($field->name);
                   if ($value > 1) print (' + ');
                   else
                   {
                      if (isset ($comments[$id])) print (' ('.$comments[$id].')');
                      print ('. ');
                   }
                }
                else
                {
                   print ($field->name.' '.$value);
                   if (isset($field->suffix)) print (' '.$field->suffix);
                   if (isset ($comments[$id])) print (' ('.$comments[$id].')');
                   print ('. ');
                }
             }
             }
         }
      }
      if ($this->editmode == MODE_PRINT) print ($signature);
      else print ('<p><a href="javascript:window.close()">Закрыть осмотр</a></p>');
      print ('</body></html>');
  }
  // -----------------------------------------------------------------------------------------------------------------
  
  function save_type()
  {
      $vector=array();
      global $db;
      //print_r ($_POST);
      if (isset ($_POST['title']) && strlen($_POST['title']))
      {
          $title=$_POST['title'];
          $this->sTitle=$title;
      }
      foreach ($_POST as $key => $value) if (sscanf($key,'field%d',$id)==1 && strlen($value)) $vector[]=$id;
      if (count ($vector))
      {
          $query = 'update osm_types set vorder = "'.join(',',$vector);
          if (isset($title)) $query .= '", title = "'.$title;
          $query .= '" where osm_type='.$this->uOsmType;
          // print ($query);
          if (!$db->query($query)) $this->error ('Ошибка обновления данных об осмотре! '.$db->error);
      }
      else $this->sMessage.=' Внимание! Не выбрано ни одно поле!';
      $this->sMessage.=' Данные успешно обновлены.';
      unset ($this->vEnabled);
      $this->vEnabled=$vector;
  }
  // -----------------------------------------------------------------------------------------------------------------
  function error ($err_msg)
  {
      die ($err_msg);
  }
}
?>