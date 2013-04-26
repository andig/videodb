{*
  Radio buttons for search engines for multi-engine support
  $Id: searchradios.tpl,v 2.3 2006/11/28 23:34:04 acidity Exp $
*}

<label for="engine0"><input type="radio" name="engine" id="engine0" value="videodb" checked="checked" />videoDB</label>
{if $engine.tvcom}
<label for="engine1"><input type="radio" name="engine" id="engine1" value="tvcom" />TV.com</label>{/if}
{if $engine.amazon || $engine.amazoncom || $engine.amazonxml}
<label for="engine2"><input type="radio" name="engine" id="engine2" value="amazon" />Amazon</label>{/if}
<label for="engine3"><input type="radio" name="engine" id="engine3" value="imdb" />IMDB</label>
{if $engine.filmweb}
<label for="engine4"><input type="radio" name="engine" id="engine4" value="filmweb" />FilmWeb</label>{/if}
