<title>ֽאזלטעו Ctrl-P</title>
<script>
function indent()
{ var body = document.getElementById("spacer");
  switch (body.style.height)
  {
      case "210mm": body.style.height="0mm"; break;
      case "180mm": body.style.height="210mm"; break;
      case "150mm": body.style.height="180mm"; break;
      case "120mm": body.style.height="150mm"; break;
      case "90mm": body.style.height="120mm"; break;
      case "60mm": body.style.height="90mm"; break;
      case "30mm": body.style.height="60mm"; break;
      case "0mm": body.style.height="30mm"; break;
  }
}
</script></head>
<body id="body" onclick="javascript:indent()">
<div id="spacer" style="color: white; height: 0mm"></div>

