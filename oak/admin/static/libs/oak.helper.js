/**
 * Construct a new Helper object
 * @class This is the basic Helper class for miscellaneous methods
 * @constructor
 * @throws applyError on exception
 * @see Base Base is the base class for this
 */
function Helper ()
{
	try {
		// properties
				
	} catch (e) {
		_applyError(e);
	}
}

/* Inherit from Base */
Helper.prototype = new Base();


/**
 * Instance Methods from prototype @class Mediamanager
 */
Helper.prototype.launchPopup = Helper_launchPopup;
Helper.prototype.closePopup = Helper_closePopup;
Helper.prototype.lowerOpacity = Helper_lowerOpacity;



function Helper_launchPopup (width, height, url, name)
{
	try {
		Helper.lowerOpacity();
		
		// properties
		this.ttargetUrl = url;
		this.ttargetName = name;
		this.ttargetWidth = width;
		this.ttargetHeight = height;
		this.ttarget = window.open(this.ttargetUrl, this.ttargetName, 
				"scrollbars=yes,width="+this.ttargetWidth+",height="+this.ttargetHeight+"");
		this.resWidth = Mediamanager.defineWindowX(this.ttargetWidth);
		this.resHeight = Mediamanager.defineWindowY();
		
		this.ttarget.moveBy(this.resWidth, this.resHeight);
		this.ttarget.focus();
	} catch (e) {
		_applyError(e);
	}
}


function Helper_closePopup ()
{       
	try {
		self.close();
		top.parent.opener.$('lyLowerOpacity').style.display = 'none';
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
 * Building new instance for @class Helper
 */
Helper = new Helper();