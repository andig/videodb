<script language="JavaScript" type="text/javascript" src="./javascript/protoflow/lib/reflection.js"></script>
<script language="JavaScript" type="text/javascript" src="./javascript/protoflow/protoflow.js"></script>
<script language="JavaScript" type="text/javascript" src="./javascript/scriptaculous/scriptaculous.js"></script>

{assign var=IMGWIDTH value="194"}
{assign var=IMGHEIGHT value="288"}

{literal}
<style>
#bodyWrap {
    width: 800px;
    margin-left: auto;
    margin-right: auto;
}
li {
    color: white;
    font-size: 32px;
    list-style: none;

}
.li {
        border: 2px solid #77D2FF;
        list-style: none;
        display: inline;
        width: 100px;
        height: 100px;
        background: #CCFFFF;
}
#overlay {
    background: url('./javascript/protoflow/resources/trans.png') repeat-y;
}
#protoflowContainer {
    border: 5px solid #444444;
    background: black;
    width: 980px;
    margin-left: auto;
    margin-right: auto;
}
#protoflow {
    width: auto;
    height: 600px;
    border: 0px solid red;
}
.sliderTrack {
    background:transparent url('./javascript/protoflow/resources/track_fill_left.png') no-repeat scroll left top;
    height:15px;
    position:relative;
    text-align:left;
    width:137px;
}
.sliderHandle {
    background:transparent url('./javascript/protoflow/resources/knob.png') no-repeat scroll left top;
    cursor:pointer;
    height:16px;
    margin-left:-2px;
    position:absolute;
    top:-5px;
    width:16px;
}

.protoCaptions {
    display: none;
}

.captionHolder {
    font-size: 34px;
    color: white;
}
</style>
<script language="Javascript">

Event.observe(window, 'load', function() {
    var pf = new ProtoFlow($("protoflow"), {
            startIndex: 2,  //which image do you want the flow
                                            //to focus on by default
            slider: true,   //show or hide slider?
            captions: true, //show or hide captions, by default we hide it.
                            //So YOU MUST turn it on here
            useReflection: true,   //Add reflection to your images. Please
                            //note that this will slow down rendering.
            enableOnClickScroll: true //add NEW! if you wish to keep scrolling
                            //on click just set this to be true
    });

});
</script>
{/literal}

<div id="protoflowContainer">

<div id="protoflow">
    {foreach item=video from=$list}
        {if $video.imgurl}{html_image file=$video.imgurl alt=$video.title link="show.php?id="|cat:$video.id max_width=$IMGWIDTH max_height=$IMGHEIGHT}{/if}
    {/foreach}
</div>

</div>
