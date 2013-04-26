<!-- {$smarty.template} -->

<script language="JavaScript" type="text/javascript" src="./javascript/prototype/fancyzoom.js"></script>

{literal}
<script language="JavaScript" type="text/javascript">
Event.observe(document, 'dom:loaded', function() {
    zoomdiv = $('ZoomBox');
    zoomimg = $('ZoomImage');

    if (document.getElementById('ZoomImage').style.webkitBoxShadow || browserIsIE) {
        $('ShadowBox').remove();

        if (browserIsIE) {
            $('ZoomClose').setStyle({top: '0px', left: '-1px'});
            // IE doesn't do transparent PNGs
            $('ZoomClose').down('img').src = {/literal}'{$template}images/fancyzoom/closebox.gif'{literal};
        }
    }

    $$('a').each(function(el) {
        if (el.getAttribute("rel") == "zoom") {
            // store target in object
            var eventManager = {
                theTarget: el,
                click: function(event) {
                    zoomClick(this.theTarget, event);
                    if (event) Event.stop(event);
                },
                preload: function(event) {
                    zoomPreload(this.theTarget);
                }
            };

            // bind click event to object methods
            el.observe('click', eventManager.click.bind(eventManager));
            el.observe('mouseover', eventManager.preload.bind(eventManager));
        }
    });
});
</script>
{/literal}

{literal}
<style>
#ZoomBox {
    position: absolute;
    z-index: 499;
    visibility: hidden;
}
#ZoomClose {
    position: absolute;
    left: -15px;
    top: -15px;
    width:30px;
    height:30px;
    border:0;
    cursor: pointer;
}
#ZoomImage {
    border: 0;
    cursor: pointer;
}
#ShadowBox {
    position: absolute;
    z-index: 498;
    visibility: hidden;
}
#ZoomCapDiv {
    position: absolute;
    margin-left: 13px;
    margin-right: 13px;
    visibility: hidden;
}
#ZoomCapDiv td {
    vertical-align: middle;
}
#ZoomCaption {
    valign: middle;
    fontSize: 14px;
    fontFamily: Helvetica;
    fontWeight: bold;
    color: #ffffff;
    textShadow: 0px 2px 4px #000000;
    whiteSpace: nowrap;
}
#ZoomSpin {
    position: absolute;
    visibility: hidden;
}
/* fix for xhtml strict - otherwise img is inline and has a margin */
#ZoomBox img, #ShadowBox img, #ZoomCapDiv img {
    display: block;
}
a[rel=zoom] img {
    border: 0;
}
</style>
{/literal}

<div id="ZoomBox">
    <a href="javascript:zoomOut();"><img src="{$template}images/fancyzoom/spacer.gif" id="ZoomImage"/></a>
    <div id="ZoomClose">
        <a href="javascript:zoomOut();"><img src="{$template}images/fancyzoom/closebox.png" width="30" height="30" border="0"></a>
    </div>
</div>
<div id="ZoomCapDiv">
    <table border="0" cellpadding="0" cellspacing="0">
    <tr height="26">
    <td><img src="{$template}images/fancyzoom/zoom-caption-l.png" width="13" height="26"></td>
    <td background="{$template}images/fancyzoom/zoom-caption-fill.png"><div id="ZoomCaption">Caption</div></td>
    <td><img src="{$template}images/fancyzoom/zoom-caption-r.png" width="13" height="26"></td>
    </tr>
    </table>
</div>
<div id="ShadowBox">
    <table width="100%" height="100%" cellspacing="0" cellpadding="0" border="0">
    <tr style="height: 25px;"><td style="width: 27px;"><img width="27" height="25" src="{$template}images/fancyzoom/zoom-shadow1.png"/></td><td background="{$template}images/fancyzoom/zoom-shadow2.png"><img width="1" height="1" src="{$template}images/fancyzoom/spacer.gif"/></td><td style="width: 27px;"><img width="27" height="25" src="{$template}images/fancyzoom/zoom-shadow3.png"/></td></tr>
    <tr><td background="{$template}images/fancyzoom/zoom-shadow4.png"><img width="1" height="1" src="{$template}images/fancyzoom/spacer.gif"/></td><td bgcolor="#ffffff"><img width="1" height="1" src="{$template}images/fancyzoom/spacer.gif"/></td><td background="{$template}images/fancyzoom/zoom-shadow5.png"><img width="1" height="1" src="{$template}images/fancyzoom/spacer.gif"/></td></tr>
    <tr style="height: 26px;"><td style="width: 27px;"><img width="27" height="26" src="{$template}images/fancyzoom/zoom-shadow6.png"/></td><td background="{$template}images/fancyzoom/zoom-shadow7.png"><img width="1" height="1" src="{$template}images/fancyzoom/spacer.gif"/></td><td style="width: 27px;"><img width="27" height="26" src="{$template}images/fancyzoom/zoom-shadow8.png"/></td></tr>
    </table>
</div>
{*<div id="ZoomSpin"><img src="{$template}images/fancyzoom/ajax-loader.gif"/></div>*}
