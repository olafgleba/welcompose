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
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * $Id: compiler.i18n.php 3 2006-06-06 08:05:42Z andreas $
 *
 * @copyright (c) 2008 creatics
 * @author sopic GmbH
 * @package SmartyGettext
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */
 
/**
 *Smarty {i18n} compiler function plugin
 *
 * Type:     compiler function<br>
 * Name:     i18n<br>
 * Purpose:  Translate string into requested language using gettext
 *
 * @param string containing var-attribute and value-attribute
 * @param Smarty_Compiler
 * @return Template code
 */
function smarty_compiler_i18n ($tag_attrs, &$compiler)
{
	// make sure that we have access to the smarty compiler
	// within Smarty_GettextHelper, because we need it to interpolate
	// the template vars.
	if (!(Smarty_GettextHelper::$compiler instanceof Smarty_Compiler)) {
		Smarty_GettextHelper::$compiler = &$compiler;
	}
	
	// skip processing if tag_attrs is empty
	if (empty($tag_attrs)) {
		$smarty->trigger_error("i18n: No message supplied", E_USER_WARNING);
		return false;
	}
	
	if (preg_match(SMARTY_GETTEXT_REGEX_PLURAL_MATCH, $tag_attrs, $matches)) {
		// import found strings to translate
		$singular_string = $matches[1];
		$plural_string = $matches[3];
		$count = $matches[5];
		
		// substitute escaped pipes
		$singular_string = str_replace('\|', '|', $singular_string);
		$plural_string = str_replace('\|', '|', $plural_string);
		
		// do the first preg_replace run. we can go over the whole string, because the translator
		// callback survives not used template vars.
		preg_replace_callback(SMARTY_GETTEXT_REGEX_PLURAL_SUBSTITUTION, array("Smarty_GettextHelper",
			"preg_collector_callback"), $tag_attrs);
		
		// prepare first part of template code. we have to keep a serialized copy
		// of the var map, because we don't have access to the compiler from the compiled
		// template. 
		$template_code = "
			\$map = '".addslashes(serialize(Smarty_GettextHelper::$map))."';
			Smarty_GettextHelper::\$map = unserialize(stripslashes(\$map));
			\$printf_args = array();
		";

		// now we need to write all variable->value mappings down, so that
		// they can be used in vprintf(). we have to do that now, because
		// otherwise it's impossible to access the template vars from the compiled
		// template without using eval. 
		foreach (Smarty_GettextHelper::$map as $_key => $_value) {
			$template_code .= "\$printf_args[".$_value['counter']."] = ".$_value['value'].";";
		}

		// last but not least we can complete the required code for the ngettext() call
		// and for the variable substitution.
		$template_code .= "
			\$translated = ngettext(stripslashes('".addslashes($singular_string)."'),
			    stripslashes('".addslashes($plural_string)."'), ".$compiler->_parse_var_props($count).");
			\$prepared = preg_replace_callback('".SMARTY_GETTEXT_REGEX_PLURAL_SUBSTITUTION."',
			    array('Smarty_GettextHelper', 'preg_translator_callback'), \$translated);
			ksort(\$printf_args);
			vprintf(\$prepared, \$printf_args);
		";

		// reset the map and the counter in our gettext helper class. if we don't do
		// that, we'll waste resources and get weired results.
		Smarty_GettextHelper::$map = array();
		Smarty_GettextHelper::$counter = 1;

		// return the created template code to the compiler
		return $template_code;
	} else {
		// it's time to do our first preg_replace run to find all template vars we
		// have to substitute and to create the map for the substitution that can
		// be used by the compiled template.
		preg_replace_callback(SMARTY_GETTEXT_REGEX_SINGULAR_SUBSTITUTION, array("Smarty_GettextHelper",
			"preg_collector_callback"), $tag_attrs);

		// prepare first part of template code. we have to keep a serialized copy
		// of the var map, because we don't have access to the compiler from the compiled
		// template. 
		$template_code = "
			\$map = '".addslashes(serialize(Smarty_GettextHelper::$map))."';
			Smarty_GettextHelper::\$map = unserialize(stripslashes(\$map));
			\$printf_args = array();
		";

		// now we need to write all variable->value mappings down, so that
		// they can be used in vprintf(). we have to do that now, because
		// otherwise it's impossible to access the template vars from the compiled
		// template without using eval. 
		foreach (Smarty_GettextHelper::$map as $_key => $_value) {
			$template_code .= "\$printf_args[".$_value['counter']."] = ".$_value['value'].";";
		}

		// last but not least we can complete the required code for the gettext() call
		// and for the variable substitution.
		$template_code .= "
			\$translated = gettext(stripslashes('".addslashes($tag_attrs)."'));
			\$prepared = preg_replace_callback('".SMARTY_GETTEXT_REGEX_SINGULAR_SUBSTITUTION."',
			    array('Smarty_GettextHelper', 'preg_translator_callback'), \$translated);
			ksort(\$printf_args);
			vprintf(\$prepared, \$printf_args);
		";

		// reset the map and the counter in our gettext helper class. if we don't do
		// that, we'll waste resources and get weired results.
		Smarty_GettextHelper::$map = array();
		Smarty_GettextHelper::$counter = 1;

		// return the created template code to the compiler
		return $template_code;
	}
	
}

?>