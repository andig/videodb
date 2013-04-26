{*
  Login page
  $Id: login.tpl,v 1.5 2005/06/04 16:59:36 andig2 Exp $
*}

<!-- {$smarty.template} -->

<div id="topspacer"></div>

<div id="content">

<div id="contentlogin">
    <br/>
    <p class="center">{$lang.enterusername}</p>

    <form action="login.php" id="login" method="post">
        <input type="hidden" value="{$refer}" name="refer"/>

        <table class="center">
        <tr>
            <td>{$lang.username}:</td>
            <td><input type="textbox" id="username" name="username"/></td>
        </tr>
        <tr>
            <td>{$lang.password}:</td>
            <td><input type="password" name="password"/></td>
        </tr>
        <tr>
            <td colspan="2"><input type="checkbox" name="permanent" id="permanent" value="1" /><label for="permanent">{$lang.stayloggedin}</label></td>
        </tr>

        <tr>
            <td colspan="2" class="center">
                <input type="submit" class="button" value="{$lang.login}" />
            </td>
        </tr>
        </table>
    </form>

    {if $error}
        <p>{$error}</p>
    {/if}
    <br/>
</div>
<!-- /contentlogin -->

</div>
<!-- /content -->

<script language="JavaScript" type="text/javascript">
// <!--
    document.forms['login'].username.focus();
// -->
</script>
