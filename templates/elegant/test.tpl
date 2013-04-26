{*
  Template for testing data encoding
*}

<h3>Pre</h3>
<div id="pre"><pre>{$data}</pre></div>

<h3>Literal</h3>
<div id="literal">{$data}</div>

<h3>JS</h3>
<div id="js"></div>

<h3>HTML</h3>
<div id="html">{$data|escape:html}</div>

<h3>HTML all</h3>
<div id="htmlall">{$data|escape:htmlall}</div>

<h3>Alert</h3>
<div>JS '<a href="#" onclick="javascript:alert('{$data|escape:javascript}')">Click here</a></div>
<div>JS "<a href="#" onclick='javascript:alert("{$data|escape:javascript}")'>Click here</a></div>
<div>JS & HTML <a href="#" onclick='javascript:alert("{$data|escape:javascript|escape}")'>Click here</a></div>

<script language="JavaScript" type="text/javascript">
	document.getElementById("js").innerHTML = '{$data|escape:javascript}';
</script>
