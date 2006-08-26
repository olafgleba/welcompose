<?php

/**
 * Project: Oak
 * File: navigations_select.php
 *
 * Copyright (c) 2006 sopic GmbH
 *
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * This file is licensed under the terms of the Open Software License
 * http://www.opensource.org/licenses/osl-2.1.php
 *
 * $Id: navigations_select.php 308 2006-08-08 12:42:23Z andreas $
 *
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Oak
 * @license http://www.opensource.org/licenses/osl-2.1.php Open Software License
 */

// na, dann printe mal schön ;)

/*
Form/HTML müsste so in etwa so aussehen:


<h2>MEDIA MANAGER <span>UPLOAD MEDIA</span></h2>

<div id="mm_modalBody">

<form class="botbg" action="whatever" method="post" id="media_upload">
<fieldset class="topbg">

<label class="cont h13" for="media_upload_description"><span class="bez">Media Description<span class="iHelp"><a href="#" title="{i18n Show help on this topic}"><img src="../static/img/icons/help.gif" alt="" /></a></span></span>
<textarea id="media_upload_description" cols="3" rows="2" class="w540h150" name="description"></textarea></label>

<label class="cont h13" for="media_upload_tags"><span class="bez">Media Tags<span class="iHelp"><a href="#" title="{i18n Show help on this topic}"><img src="../static/img/icons/help.gif" alt="" /></a></span></span>
<textarea id="media_upload_tags" cols="3" rows="2" class="w540h150" name="tags"></textarea></label>

<input class="submit200" name="submit" value="Upload Media" type="submit" />
<input class="hide200" name="hide" value="Stop and hide" type="button" />

</fieldset>
</form>

</div>



**/



?>