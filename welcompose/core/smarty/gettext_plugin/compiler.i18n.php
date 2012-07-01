<?php

/**
 * Project: Smarty::Gettext
 * File: compiler.i18n.php
 *
 * Copyright (c) 2008-2012 creatics, Olaf Gleba <og@welcompose.de>
 *
 * Project owner:
 * creatics, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author Olaf Gleba
 * @package SmartyGettext
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */
 
/**
 * Smarty {i18n} compiler function plugin
 *
 * Type:     compiler function
 * Name:     i18n
 * Purpose:  Translate string into requested language using gettext
 *
 * @param Array containing attributes and values 
 * @param Smarty Reference
 * @return Template code
 */
function smarty_compiler_i18n ($tag_attrs, $smarty)
{	
	$map = array();
	
	foreach ($tag_attrs as $tag_attrs_value) {
		$map = $tag_attrs_value;
	}

	$template_code = "<?php ";	
	$template_code .= "echo gettext($map);";
	$template_code .= "?>";
	
	return $template_code;	
}
?>