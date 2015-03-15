<html><head>
<?php
if (isset($page_title)) print ("<title>{$page_title} - LODE</title>\n");
else print ('<title>LODE</title>\n');
?>
<link rel="stylesheet" type="text/css" href="main.css">
<link rel="stylesheet" type="text/css" href="osm.css">
<script language="JavaScript" type="text/javascript">
//-------------------------------------------------------------------
// Функция отображения выпадающего списка
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
// Функция выбора пункта выпадающего списка
//
function select (id)
{
  var sel = document.getElementById('m'+id);
  var img = document.getElementById('img'+id);
  document.getElementById('in'+id).value=sel.options[sel.selectedIndex].text;
  sel.style.display='none';
  img.src='img/down.png';
}
//-------------------------------------------------------------------
// Функция обслуживания самодельного checkbox
//
function check (id)
{
  var chk = document.getElementById('chk'+id);
  var input = document.getElementById('in'+id);
  if (!chk.checked)
  {
//      chk.checked=false;
      input.value='';
  }
  else
  {
//      chk.checked=true;
      input.value=chk.value;
  }
}
//-------------------------------------------------------------------
// Функция обслуживания самодельного list
//
function listSelect (id)
{
  var lst = document.getElementById('list'+id);
  document.getElementById(id).value=lst.options[lst.selectedIndex].value;
}
//-------------------------------------------------------------------
// Функция очистки списка
//
function listClear (id)
{
  document.getElementById('list'+id).selectedIndex=-1;
  document.getElementById(id).value='';
}
//-------------------------------------------------------------------
// Функция очистки строки элемента типа "таблица"
//
function tableClear (id, row)
{
  var input = document.getElementById('in'+id);
  var i = 1;
  while (sel = document.getElementById(id+'-'+row+'-'+i))
  {
        sel.selectedIndex=-1; // отмена выделения пункта
        i++;
  }
  var par = document.getElementById(id+'-'+row).innerHTML; // название строки
  var str = new String (input.value);
  var strings = str.split("; ");
  for (i=0; i<strings.length; i++)
  {
     if (strings[i].indexOf(par) == 0)
     {
         strings.splice(i,1);
         break;
     }
  }
  input.value=strings.join("; ");
}
//-------------------------------------------------------------------
// Функция выбора строки элемента "таблица"
//
function tableChange (id, row)
{
  var input = document.getElementById('in'+id);
  var str = new String (input.value);
  var par = document.getElementById(id+'-'+row).innerHTML; // название строки
  var newstr = '';
  var i = 1;
  while (sel = document.getElementById(id+'-'+row+'-'+i))
  {
        if (sel.selectedIndex==-1) { i++; continue; }
        if (newstr.length) newstr+=', '+sel.options[sel.selectedIndex].value;
        else newstr=sel.options[sel.selectedIndex].value;
        i++;
  }
  newstr=par+': '+newstr;
  if (!str.length) // поле input было пустым, что облегчает нам задачу
  {
      input.value=newstr;
  }
  else // нужно удалить предыд. строку
  {
      var strings = str.split("; ");
      var found=0;
      for (i=0; i<strings.length; i++)
      {
          if (strings[i].indexOf(par) == 0)
          {
              strings[i]=newstr;
              found=1;
              break;
          }
      }
      input.value=strings.join("; ");
      if (!found) input.value+='; '+newstr;
  }
}

//--------------------------------------------------------------------
// Функция "раскрытия" секции
//
function showSection(id)
{
  var section = document.getElementById('id'+id);
  var hid = document.getElementById(id);
  var img = document.getElementById('img'+id);
  if(section.style.display=="none" || section.style.display=="")
  {
       section.style.display="block";
       hid.value=" ";
       img.src='img/up.png';
  }
  else
  {
      section.style.display="none";
      hid.value="";
      img.src='img/down.png';
  }
}
// ------------------------------------------------------------------------
// Функция добавления нового пункта в самодельный выпадающий список
// Обновляет список вариантов у заданного элемента (определяется по ID) в БД
// Формат вызова: тип шаблона осмотра (для записи в БД), ID пополняемого элемента
//
function Add (osm_type,id)
{
  var input = document.getElementById("in"+id);
  var value = input.value;
  if (value.length==0) return;
  window.open("osm_add.php?osm_type="+osm_type+"&id="+id+"&add="+value,"","");
}
// ------------------------------------------------------------------------
// Функция обслуживания самодельного мульти-селекта (с набором чек-боксов)
// Формат вызова: ссылка на активированный объект (тэг <li>), ID элемента ввода типа hidden
//
function multi(li, hidden_id)
{
  var hidden = document.getElementById(hidden_id);
  var value = new String(hidden.value);
  var val='';
  var li_value=new String(li.innerHTML);
  if (li_value.charCodeAt(li_value.length-1)==32) li_value=li_value.substr(0,li_value.length-1); // отрезаем лишний пробел
  if (value.length==0)
  {
      val=li_value;
      li.style.listStyleImage="url('checked.png')";
  }
  else
  {
      var values = value.split(", "); // разделим на элементы
      var found = 0;
      for (i=0; i<values.length; i++)
      {
          if (values[i]==li_value)
          {
              values.splice(i,1); // удалим элемент
              li.style.listStyleImage="url('unchecked.png')";
              val=values.join(', ');
              found=1;
              break;
          }
      }
      if (!found)
      {
          val = value.toString();
          val+=', '+li_value;
          li.style.listStyleImage="url('checked.png')";
      }
  }
  hidden.value=val;
}
// ------------------------------------------------------------------------
// Функция вычисления изменения веса
// Формат вызова: нет, все ID прописаны фиксированно
//
function CalcDelta1()
{
  try {
  var delta = parseInt(document.getElementById("7").value,10)-parseInt(document.getElementById("9").value,10);
  var dateStr = new String (document.getElementById("6").value);
  var dateArr = dateStr.split("-");
  var lastdate = new Date (parseInt(dateArr[0]), parseInt(dateArr[1])-1, parseInt(dateArr[2]));
  var curdate = new Date(parseInt(document.getElementById("Date3").value), parseInt(document.getElementById("Date2").value)-1, parseInt(document.getElementById("Date1").value));
  var weeks = (curdate - lastdate) / 604800000;
//  if (Math.round(weeks)-weeks > 0) weeksStr = new String (Math.round(weeks)-1) + ".5"; else
  weeksStr = new String (Math.round(weeks));
  if (weeks<=1.0) weeksStr+=" неделю";
  else if (weeks<5.0) weeksStr+=" недели";
  else weeksStr+=" недель";
  document.getElementById("8").value = delta + " г за " + weeksStr.valueOf();
  }
  catch (error) {} // для случая отсутствия нужных полей, просто ничего не выводится
}
// ------------------------------------------------------------------------
// Функция вычисления изменения роста
// Формат вызова: нет, все ID прописаны фиксированно
//
function CalcDelta2()
{
  try {
  var delta = parseInt(document.getElementById("10").value,10)-parseInt(document.getElementById("12").value,10);
  var dateStr = new String (document.getElementById("6").value);
  var dateArr = dateStr.split("-");
  var lastdate = new Date (parseInt(dateArr[0]), parseInt(dateArr[1])-1, parseInt(dateArr[2]));
  var curdate = new Date(document.getElementById("Date3").value, parseInt(document.getElementById("Date2").value)-1,document.getElementById("Date1").value);
  var weeks = (curdate - lastdate) / 604800000;
//  if (Math.round(weeks)-weeks > 0) weeksStr = new String (Math.round(weeks)-1) + ".5"; else
  weeksStr = new String (Math.round(weeks));
  if (weeks<=1.0) weeksStr+=" неделю";
  else if (weeks<5.0) weeksStr+=" недели";
  else weeksStr+=" недель";
  document.getElementById("11").value = delta + " см за " + weeksStr.valueOf();
  }
  catch (error) {} // для случая отсутствия нужных полей, просто ничего не выводится
}
// ------------------------------------------------------------------------
// Функция обслуживания "зубной формулы"
// Формат вызова: ID кнопки, номер группы (1-4), направление сортировки (1 - возр., 0 - убыв.)
//
function teethButton (id, group, direction)
{
     var button = document.getElementById ("tooth"+id);
     if (button.value == '') button.value=id;
     else if (button.value == id) button.value=id+40;
     else button.value='';
     var val = '';
     if (direction) // по возрастанию
     {
         for (i=group*10+1; i<group*10+8; i++)
         {
             val+=document.getElementById ("tooth"+i).value+' ';
         }
         val+=document.getElementById ("tooth"+(group*10+8)).value;
     }
     else
     {
         for (i=group*10+8; i>group*10+1; i--)
         {
             if (val != '' ) val+=' ';
             val+=document.getElementById ("tooth"+i).value+' ';
         }
         val+=document.getElementById("tooth"+(group*10+1)).value;
     }
     document.getElementById(24+group).value=val;
}
</script>
</head>
<body>