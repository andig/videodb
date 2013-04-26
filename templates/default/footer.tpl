{*
  This is the footer which is displayed on bottom of every page
  $Id: footer.tpl,v 2.5 2008/02/17 17:44:54 andig2 Exp $
*}

{$DEBUG}
    <table width="100%" class="footertable">
      <tr>
        <td width="50">
          <a href="#top"><img src="images/top.gif" border="0" alt="" class="toplink" /></a>
        </td>
        <td align="left" style="text-align:left">
          {if $loggedin}
            <span class="version">{$lang.loggedinas} {$loggedin}</span>
          {/if}
        </td>
        <td align="right" style="text-align:right">
          <a href="http://www.videodb.net" class="splitbrain">v.{$version}</a>
        </td>
      </tr>
    </table>

    </div>

  </body>
</html>
