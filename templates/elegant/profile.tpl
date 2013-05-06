{*
  The user profile template
  $Id: profile.tpl,v 1.1 2005/09/28 09:03:15 andig2 Exp $
*}

<!-- {$smarty.template} -->

<div id="topspacer2"></div>

<div id="content">
    <form method="post" action="profile.php">
        <input type="hidden" name="save" value="1"/>

        <table>
            {include file="options.tpl"}
        </table>

        <div id="editbuttons">
            <input type="submit" class="button" value="{$lang.save}" id="savebutton" accesskey="s" />
        </div>
    </form>
</div>
<!-- /content -->
