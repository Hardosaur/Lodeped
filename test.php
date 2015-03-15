<html>
<script>
function multi(li, hidden_id)
{
  var hidden = document.getElementById(hidden_id);
  var value = new String(hidden.value);
  var val='';
  if (value.length==0)
  {
      val=li.innerHTML;
      li.style.listStyleImage="url('checked.png')";
  }
  else
  {
      var values = value.split(', '); // разделим на элементы
      var text = li.innerHTML;
      var found = 0;
      for (i=0; i<values.length; i++)
      {
          if (values[i]==text)
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
          val+=', '+text;
          li.style.listStyleImage="url('checked.png')";
      }
  }
  hidden.value=val;
}

</script>
<body>
<ul>
<li style="list-style-image: url('unchecked.png')" onclick="multi(this, 'test1')">Option 1</li>
<li style="list-style-image: url('unchecked.png')" onclick="multi(this, 'test1')">Option 2</li>
<li style="list-style-image: url('unchecked.png')" onclick="multi(this, 'test1')">Option 3</li>
</ul>
<input type="text" name="test1" value="">