<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>
<?php
print ($Osmotr->sTitle);
?>
</title>
<meta http-equiv="content-type" content="text/html; charset=win-1251" />
<link rel="stylesheet" type="text/css" href="osmotr.css">
<script>
//-------------------------------------------------------------------
// ���������� ��������
//
function CalcAge()
{
  var birth_str = new String (document.getElementById('birth').value);
  if (!birth_str) return;
  var birth = birth_str.split("-");
  var bDate = new Date (birth[0],birth[1],birth[2]);
  var cDate = new Date (document.getElementById('Date3').value,
                       document.getElementById('Date2').value,
                       document.getElementById('Date1').value);
  var age = Math.floor((cDate-bDate)/86400000); // ������� � ����
  var age_years = Math.floor(age/365.25); // ����� ������ ���
  var age_months = Math.floor((age%365)/30.5); // ����� ������ ����
  var age_days = Math.floor((age%365)%30.5);
  var agestr = '';
  if (!age_years && age_days>0) // ����� ���
  {
      agestr=' '+age_days+' ';
      if (age_days%10==1 && age_days!=11) agestr+='����';
      else if (age_days%10<5 && Math.floor(age_days/10)!=1) agestr+='���';
      else agestr+='����';
  }
  if (age_months>0) // ����� ������
  {
      if (age_months==1) agestr=' �����'+agestr;
      else if (age_months<5) agestr=' ������'+agestr;
      else agestr=' �������'+agestr;
      agestr=age_months+agestr;
  }
  if (age_years) // ����� ����
  {
      if (age_years%10==1) agestr=' ��� '+agestr;
      else if (age_years%10<5 && Math.floor(age_years/10)!=1) agestr=' ���� '+agestr;
      else agestr=' ��� '+agestr;
      agestr=age_years+agestr;
  }
  document.getElementById('age').value=agestr;
}
//-------------------------------------------------------------------
// ������� ��������� ����������� ����
//
function doHighlight (id)
{
  document.getElementById(id).style.backgroundColor='#d0ffd0';
}
//-------------------------------------------------------------------
// ������� ��������/����������� ����
//
function doHide (id)
{
  var td = document.getElementById('td'+id);
  if (td.style.visibility == 'hidden')
  {
      td.style.visibility = 'visible';
  }
  else
  {
      td.style.visibility = 'hidden';
//      document.getElementById(id).value = '';
  }
}
//-------------------------------------------------------------------
// ������� ��������/����������� ����
//
function doMark (obj, id)
{
  var td = document.getElementById('td'+id);
  if (td.style.visibility == 'hidden')
  {
      td.style.visibility = 'visible';
      hidfield=document.getElementById('field'+id);
      hidfield.value="1";
      obj.style.color='black';
  }
  else
  {
      td.style.visibility = 'hidden';
//      document.getElementById(id).value = '';
      hidfield=document.getElementById('field'+id);
      hidfield.value="";
      obj.style.color='#909090';
  }
}
//-------------------------------------------------------------------
// ������� �������/������ ������� ���������
//
function doMarkHeader (obj, id)
{
  var field = document.getElementById('field'+id);
  if (field.value == "1")
  {
      field.value="";
      obj.style.color='#909090';
  }
  else
  {
      field.value="1";
      obj.style.color='black';
  }
}

//-------------------------------------------------------------------
// ������� ������������ ������������ checkbox
//
function doCheck (id)
{
  var chk = document.getElementById('chk'+id);
  var input = document.getElementById(id);
  if (!chk.checked)
  {
      input.value='';
  }
  else
  {
      input.value=1;
  }
  doHighlight('chk'+id);
}
//-------------------------------------------------------------------
// ������� ������������ �������� checkbox, ��������� 1-�� ����
//
function doCheck1 (id)
{
  var chk = document.getElementById('chk1'+id);
  var input = document.getElementById(id);
  if (!chk.checked)
  {
      input.value=parseInt(input.value) & 0xFE;
  }
  else
  {
      input.value=parseInt(input.value) | 1;
  }
  doHighlight('chk1'+id);
}

//-------------------------------------------------------------------
// ������� ������������ �������� checkbox, ��������� 2-�� ����
//
function doCheck2 (id)
{
  var chk = document.getElementById('chk2'+id);
  var input = document.getElementById(id);
  if (!chk.checked)
  {
      input.value=parseInt(input.value) & 0xFD;
  }
  else
  {
      input.value=parseInt(input.value) | 2;
  }
  doHighlight('chk2'+id);
}
//-------------------------------------------------------------------
// ������� ������������ ������������ checkbox � "��������"
//
function doPlusCheck (id)
{
  var chk = document.getElementById('chk'+id);
  var input = document.getElementById(id);
  if (chk.checked)
  {
      if (input.value==1)
      {
         input.value=2;
         document.getElementById ('pluschk'+id).innerHTML=' + ';
      }
      else if (input.value==2)
      {
         input.value=1;
         document.getElementById ('pluschk'+id).innerHTML='&nbsp;&nbsp;&nbsp;';
      }
      else input.value=0;
  }
  doHighlight('chk'+id);
}
//-------------------------------------------------------------------
// ������� ������������ ������������ select
//
function doSelect (id)
{
  var lst = document.getElementById('sel'+id);
  document.getElementById(id).value=lst.options[lst.selectedIndex].value;
  doHighlight('sel'+id);
}
function selectShow (id)
{
  var lst = document.getElementById('sel'+id);
  lst.style.display='none';
  document.getElementById('span'+id).style.display='inline';
}
function selectAdd (id)
{
  var lst = document.getElementById('sel'+id);
  var op = document.createElement('option');
  op.value=document.getElementById('add'+id).value;
  op.text=op.value;
  op.innerHTML=op.value;
  lst.appendChild(op);
  document.getElementById('span'+id).style.display='none';
  lst.style.display='inline';
  lst.selectedIndex=lst.length-1;
  doSelect(id);
}
function selectSave (id)
{
  newwin=window.open("field_add.php?id="+id+"&text="+document.getElementById('add'+id).value, '');
//  newwin.location=url;
  selectAdd(id);
}
//-------------------------------------------------------------------
// ������� ������������ ������������ multi-select
//
function doMulti (id)
{
  var lst = document.getElementById('sel'+id);
  var newstr = '';
  for (i=0; i<lst.options.length; i++)
      if (lst.options[i].selected)
         if (newstr.length > 0) newstr += ', ' + lst.options[i].text;
         else newstr = lst.options[i].text;
  document.getElementById(id).value=newstr;
  doHighlight('sel'+id);
}
//-------------------------------------------------------------------
// ������� ������������ �������� dualnum
//
function doDualnum (id)
{
  document.getElementById(id).value=document.getElementById('f'+id).value+'/'+document.getElementById('s'+id).value;
  if (document.getElementById(id).value == '/') document.getElementById(id).value='';
  doHighlight('f'+id);
  doHighlight('s'+id);
}
//-------------------------------------------------------------------
// ������� ���������� ������ � table
//
function tableAdd (id, columns)
{
  // ��������� ����� ������ � �������. ������ �������� � �������� tab### � ���� �����, ����������� <BR>
  var select = document.getElementById('tab'+id+'col0');
  var newstr = select.options[select.selectedIndex].value;
  var oldstr = document.getElementById('tab'+id).innerHTML;
  var strings = oldstr.split('<BR>');
  for (i=1; i<columns; i++)
  {
      select = document.getElementById('tab'+id+'col'+i);
      if (select.selectedIndex > 0) newstr += ', ' + select.options[select.selectedIndex].value;
  }
  document.getElementById(id).value += newstr + ';<BR>';
  document.getElementById('tab'+id).innerHTML += newstr + ';&nbsp;<a href="javascript:tableRemove(' + id + ', ' + (strings.length-1) +')">&otimes;</a><BR>';
  document.getElementById('sel'+id).style.display='none';
  document.getElementById('but'+id).style.display='inline';
}
//-------------------------------------------------------------------
// ������� ����������� ���������� ������� ��� ���������� ������ � table
//
function tableShow (id)
{
  document.getElementById('sel'+id).style.display='inline';
  document.getElementById('but'+id).style.display='none';
}
//-------------------------------------------------------------------
// ������� �������� ������ � table
//
function tableRemove (id, line)
{
  var str = new String ();
  str = document.getElementById(id).value; // ����������� ����� ��������
  var strings = str.split (";<BR>");
  if (strings.length == 1) // ��������� ������������ �������
  {
       document.getElementById(id).value = '';
       document.getElementById('tab'+id).innerHTML = '';
       return;
  }
  strings.splice (line,1);
  document.getElementById(id).value = strings.join (";<BR>");
   var newstr='';
  for (i=0; i<strings.length-1; i++)
      newstr += strings[i] + ';&nbsp;<a href="javascript:tableRemove(' + id + ', ' + i +')">&otimes;</a><BR>';
  document.getElementById('tab'+id).innerHTML = newstr;
}
// ------------------------------------------------------------------------
// ������� ������������ "������ �������"
// ������ ������: ID ��������, ID ������
//
function teethButton (id_teeth, id)
{
     var button = document.getElementById ("tooth"+id);
     if (button.value == '') button.value=id;
     else if (button.value == id) button.value=id+40;
     else button.value='';
     var val = '';
     for (j=1; j<=4; j++)
     {
         for (i=j*10+1; i<=j*10+8; i++)
         {
             if (document.getElementById ("tooth"+i).value != '') val+=document.getElementById ("tooth"+i).value+' ';
         }
     }
     document.getElementById(id_teeth).value=val;
}

</script>
</head>
<body onload="javascript:CalcAge()">
<table class="osmotr" width="100%">

