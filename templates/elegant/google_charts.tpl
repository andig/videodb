{assign var="chs" value=""}
{assign var="chl" value=""}
{foreach item=row from=$stats.count_genre}
	{assign var="rowname" value=$row.name}
	{assign var="rowcount" value=$row.count}
	{assign var="rowid" value=$row.id}

	{if $chs}{assign var="chs" value=$chs|cat:","}{/if}
	{if $chl}{assign var="chl" value=$chl|cat:"|"}{/if}

	{assign var="chs" value=$chs|cat:"$rowcount.0"}
	{assign var="chl" value=$chl|cat:$rowname}
{/foreach}
CHS:{$chs}
CHL:{$chl}
http://chart.apis.google.com/chart?cht=p3&chd=s:hW&chs={$chs}&chl={$chl}

<img src="http://chart.apis.google.com/chart?cht=p3&chd=t:{$chs}&chl={$chl}" />



