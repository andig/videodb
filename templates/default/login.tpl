{*
  Output of login page
*}


<div align="center">
  <p>{$lang.enterusername}</p>

  <form action="login.php" id="login" method="post">
  <input type="hidden" value="{$refer}" name="refer"/>
  <table class="tableborder">
    <tr>
      <td>{$lang.username}:</td>
      <td><input type="textbox" id="username" name="username"/></td>
    </tr>
    <tr>
      <td>{$lang.password}:</td>
      <td><input type="password" name="password"/></td>
    </tr>
    <tr>
      <td colspan="2"><input type="checkbox" name="permanent" id="permanent" value="1"/><label for="permanent">{$lang.stayloggedin}</label></td>
    </tr>
    <tr>
      <td colspan="2" align="middle" style="text-align:center"><input type="submit" value="{$lang.login}"/></td>
    </tr>
  </table>
  </form>

  <p>{$error}</p>
</div>

<script language="JavaScript" type="text/javascript">
	document.forms['login'].username.focus();
</script>
