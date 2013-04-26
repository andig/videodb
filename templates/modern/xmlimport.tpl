{*
  Template for the edit interface
  $Id: xmlimport.tpl,v 2.5 2007/09/01 14:09:06 andig2 Exp $
*}

<div id="topspacer"></div>


<table class="tableborder" style="background-color:#eeeeee;">
<tr><td>

<div class="center">

{if $xmlerror}
	<br/><pre>{$xmlerror}</pre>
{/if}

<form action="edit.php?import=xml" id="edi" name="edi" method="post" enctype="multipart/form-data">
	<input type="hidden" name="xml" id="xml" value="import" />

	<table class="tableedit">
	<tr>
		<td>XML file:</td>
		<td><input type="file" name="xmlfile" id="xmlfile" size="50" value="" /></td>
	</tr>

	<tr>
		<td>XML data:</td>
		<td><textarea name="xmldata" cols=80 rows=15>{$xmldata}</textarea></td>
	</tr>

	<tr>
		<td>Import options:</td>
		<td>
			<label for="import_custom"><input type="checkbox" name="xmloptions[import_custom]" id="import_custom" value="1"/>{$lang.help_customn}</label>
			<br/>
			<label for="import_diskid"><input type="checkbox" name="xmloptions[import_diskid]" id="import_diskid" value="1"/>{$lang.diskid}</label>
			<br/>
			<label for="import_owner"><input type="checkbox" name="xmloptions[import_owner]" id="import_owner" value="1"/>{$lang.owner}</label>
		</td>
	</tr>
<!--
	<tr>
		<td>
			<label for="import_custom">Import custom fields:</label>
		</td>
		<td>
			<input type="checkbox" name="xmloptions[import_custom]" id="import_custom" value="1"/>
		</td>
	</tr>

	<tr>
		<td>
			<label for="import_diskid">Import disk fields:</label>
		</td>
		<td>
			<input type="checkbox" name="xmloptions[import_diskid]" id="import_diskid" value="1"/>
		</td>
	</tr>

	<tr>
		<td>
			<label for="import_owner">Import owner:</label>
		</td>
		<td>
			<input type="checkbox" name="xmloptions[import_owner]" id="import_owner" value="1"/>
		</td>
	</tr>
-->
	</table>

	<div class="center"><input type="submit" value="Import" class="button" accesskey="s" /></div>
</form>
</div>

</td></tr>
</table>
