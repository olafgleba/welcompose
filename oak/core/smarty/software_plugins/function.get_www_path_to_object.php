<?php

/**
 * Project: Oak
 * File: function.get_www_path_to_object.php
 * 
 * Copyright (c) 2006 sopic GmbH
 * 
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
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
 * $Id$
 * 
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Oak
 * @license http://www.opensource.org/licenses/apache2.0.php Apache License, Version 2.0
 */

function smarty_function_get_www_path_to_object ($params, &$smarty)
{
	// input check
	if (!is_array($params)) {
		$smarty->trigger_error("Input for parameter params is not an array");
	}
	
	// load media object class
	$OBJECT = load('Media:Object');
	
	// import object name from params array
	$name = Base_Cnc::filterRequest($params['name'], OAK_REGEX_OBJECT_FILENAME);
	
	return $OBJECT->getWwwPathToObject($name);
}

?>