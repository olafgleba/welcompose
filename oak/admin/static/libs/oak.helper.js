/**
 * Construct a new Helper object
 * @class This is the basic Helper class for miscellaneous methods
 * @constructor
 * @throws applyError on exception
 * @see Helper Helper is the base class for this
 */
function Helper ()
{
	try {
		// properties
				
	} catch (e) {
		_applyError(e);
	}
}

/* Inherit from Helper */
Helper.prototype = new Base();


/**
 * Instance Methods from prototype @class Mediamanager
 */
Helper.prototype.launchPopup = Helper_launchPopup;
Helper.prototype.closePopup = Helper_closePopup;
Helper.prototype.lowerOpacity = Helper_lowerOpacity;
Helper.prototype.unsupportsEffects = Helper_unsupportsEffects;
Helper.prototype.unsupportsElems = Helper_unsupportsElems;
Helper.prototype.defineWindowX = Helper_defineWindowX;
Helper.prototype.defineWindowY = Helper_defineWindowY;



function Helper_launchPopup (width, height, name, trigger, elem)
{
	try {
		Helper.lowerOpacity();
		
		// properties
		this.elem = elem;
		this.trigger = trigger;
		
		switch (this.trigger) {
			case 'mm_upload' :
					this.url = '../mediamanager/mediamanager_upload.php';
				break;
			case 'mm_edit' :
					this.url = '../mediamanager/mediamanager_edit.php?id=' + this.elem.name;
				break;
		}
		// properties
		this.ttargetUrl = this.url;
		this.ttargetName = name;
		this.ttargetWidth = width;
		this.ttargetHeight = height;
		this.ttarget = window.open(this.ttargetUrl, this.ttargetName, 
				"scrollbars=yes,width="+this.ttargetWidth+",height="+this.ttargetHeight+"");
		this.resWidth = Helper.defineWindowX(this.ttargetWidth);
		this.resHeight = Helper.defineWindowY();
		
		this.ttarget.moveBy(this.resWidth, this.resHeight);
		this.ttarget.focus();
	} catch (e) {
		_applyError(e);
	}
}




function Helper_closePopup ()
{       
	try {
		/* invoke function in parent window */
		self.opener.$('lyLowerOpacity').style.display = 'none';
		self.opener.Mediamanager.invokeInputs();
		
		/* disable all elements */
		var form_id = document.forms[0].getAttribute('id');
	
		e = Form.getElements(form_id);
			for(i = 0; i < e.length; i++) {
    			e[i].disabled = true;
			}
			
		/* has to be a timeout since the opened window has
		 to be present til process function in parent is executed */
		setTimeout ("self.close()", 400);
	} catch (e) {
		_applyError(e);
	}
}


function Helper_lowerOpacity ()
{       
	try {
 		// properties
        this.cLeft = '0px';
        this.cTop = '0px';
        this.cPosition = 'absolute';
        this.cDisplay = 'block';
        this.imagePath = '../static/img/bg_overlay.png';
		this.lyContainer = $("container");   
        this.buildHeight = this.lyContainer.offsetHeight;
        this.buildWidth = this.lyContainer.offsetWidth;
		this.imageStr = '<img src="' + this.imagePath + '" width="' + this.buildWidth + '" height="' + this.buildHeight +'" alt="" />';
		this.ttarget_lower = $('lyLowerOpacity');

        if (this.ttarget_lower) {

		 	this.ttarget_lower.style.display = this.cDisplay;
			this.ttarget_lower.style.position = this.cPosition;
			this.ttarget_lower.style.top = this.cTop;
			this.ttarget_lower.style.left = this.cLeft;
			this.ttarget_lower.style.height = this.buildHeight + 'px';
			this.ttarget_lower.style.width = this.buildWidth + 'px';
			
			Element.update(this.ttarget_lower, this.imageStr);
        }
	} catch (e) {
		_applyError(e);
	}
}


/**
 * Implements method of prototype class Helper
 * Simply examine id IE is on air
 * @requires Helper The Helper Class
 */
function Helper_unsupportsEffects(exception)
{	
	try {
		//properties
		this.browser = _setBrowserString();
		this.exception = exception;
			
		if ((this.browser == "Internet Explorer") || (this.browser == "Safari" && !this.exception)) {
			return true;
		} else { 
			return false;
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Helper
 * Simply examine id IE is on air
 * @requires Helper The Helper Class
 */
function Helper_unsupportsElems()
{	
	try {
		//properties
		this.browser = _setBrowserString();
		
		if (this.browser == "Internet Explorer") {
			return true;
		} else { 
			return false;
		}
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Helper
 * Simply examine id IE is on air
 * @private
 * @requires Helper The Helper Class
 */
function _compare (string)
{
	try {
		var res = detect.indexOf(string) + 1;
		return res;
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Helper
 * Simply examine id IE is on air
 * @private
 * @requires Helper The Helper Class
 */
function _setBrowserString ()
{
	try {			
		detect = navigator.userAgent.toLowerCase();
		var browser;

		if (_compare('safari')) {
			browser = 'Safari';
		}
		else if (_compare('msie')) {
			browser = 'Internet Explorer';
		}
		else {
			browser = 'Unknown Browser';
		}
		return browser;
	} catch (e) {
		_applyError(e);
	}
}


/**
 * Implements method of prototype class Helper
 * Examine the giving var is empty
 * @requires Helper The Helper Class
 * @param {var} elem Actual element
 * @return Boolean true or false
 */
function Helper_defineWindowX (elemWidth)
{
	try {
		//properties
		this.el = elemWidth;
		var x;
		
		if (self.innerHeight) {
			// all except Explorer {
			x = Math.round(self.innerWidth) - (Math.round(this.el));
		}
		else if (document.documentElement && document.documentElement.clientHeight) {
			// Explorer 6 Strict Mode
			x = Math.round(document.documentElement.clientWidth) - (Math.round(this.el));
		}
		else if (document.body) {
			// other Explorers
			x = Math.round(document.body.clientWidth) - (Math.round(this.el));
		}
		x = Math.round(x/2);
		return x;
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Implements method of prototype class Helper
 * Examine the giving var is empty
 * @requires Helper The Helper Class
 * @param {var} elem Actual element
 * @return Boolean true or false
 */
function Helper_defineWindowY ()
{
	try {
		//properties
		var y;
	
		if (self.innerHeight) { 
		// all except Explorer
			y = Math.round(self.innerHeight/6);
		}
		else if (document.documentElement && document.documentElement.clientHeight) {
			// Explorer 6 Strict Mode
			y = Math.round(document.documentElement.clientHeight/6);
		}
		else if (document.body) {
			// other Explorers
			y = Math.round(document.body.clientHeight/6)																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																				;
		}
		return y;
	} catch (e) {
		_applyError(e);
	}
}

/**
 * Building new instance for @class Helper
 */
Helper = new Helper();