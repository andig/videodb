{*
  IMDB borow request popup
  $Id: borrowask.tpl,v 1.2 2004/09/20 15:14:10 andig2 Exp $
*}
{include file="xml.tpl"}

<body>

<script language="JavaScript" type="text/javascript">
//<![CDATA[
	window.focus()
//]]>
</script>

  <p>
  {if $success}
    {$lang.msg_borrowaskok}
  {else}
    {$lang.msg_borrowaskfail}
  {/if}
  </p>

  <p align="center">[ <a href="javascript:close()">{$lang.okay}</a> ]</p>
</body>
</html>
