<?php

/**
 * Project: Welcompose
 * File: function.social_bookmarks.php
 * 
 * Copyright (c) 2008 creatics media.systems
 * 
 * Project owner:
 * creatics media.systems, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 * 
 * $Id: function.social_bookmarks.php 1370 2009-10-14 19:21:10Z olafgleba $
 * 
 * @copyright 2009 creatics media.systems, Olaf Gleba
 * @author Olaf Gleba
 * @package Welcompose
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

function smarty_function_social_bookmarks ($params, &$smarty)
{
	// input check
	if (!is_array($params)) {
		$smarty->trigger_error("Input for parameter params is not an array");
	}

	// import object name from params array
	$page = Base_Cnc::filterRequest($params['page'], WCOM_REGEX_NUMERIC);
	$posting = Base_Cnc::filterRequest($params['posting'], WCOM_REGEX_NUMERIC);
	$services = $params['services'];
	$var = Base_Cnc::filterRequest($params['var'], WCOM_REGEX_ALPHANUMERIC);
	
	if (isset($posting) && !empty($posting)) {
		$params = array(
			'page_id' => $page,
			'posting_id' => $posting,
			'action' => 'item'
		);
		
		// load page class
		$BLOGPOSTING = load('Content:BlogPosting');	
		$posting = $BLOGPOSTING->selectBlogPosting($posting);
		$title = $posting['title'];
	} else {
		// is type page	
		$params = array(
			'page_id' => $page
		);
		
		// load page class
		$PAGE = load('Content:SimplePage');
		$page = $PAGE->selectSimplePage($page);
		$title = $page['title'];
	}
	// 
	// // send params to url generator. we hope to get back something useful.
	$URLGENERATOR = load('Utility:UrlGenerator');
	$url = $URLGENERATOR->generateExternalLink($params);

   	$given_services = array(
		'mister-wong' => array(
				'url' => 'http://www.mister-wong.de/addurl/?bm_url',
				'title' => 'bm_description',
				'image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAFo9M/3AAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAQgSURBVHjaYnRwcGDp09R/w+T57M2pJ7LS/AABxHgqu+ADy9//jCwn9+3kZ2RgYAAIACIA3f8Bs3N40iBAPS377/EA+OrnAALVyscCw6qsAO4YFAC/nKAAAgBEALv/AcplbuILOzYOBxAQAOWwsqoE/i4lHf8TEgAPMjAA797dZQLRlpgA/RgXAM+prADQm5sABOHo5gASzMwA/P/+AOzp5gACAIQAe/8BwFtiy/3w8DQFJB4AAQoLAPjS0gA3n6AAx2FeAAwJEQAE09zYBBAKCwAB398ABBUWAPjr6QDl4uIA5+7uAAYB8wAEASgoASFiYQDWmJoA6rm3ADWNjwCzUE0AMIuMAAsXGAACg7Gudclqav0ADAsA+OzsAPn8/QDz7OwAAhMTAKxOTOACAAgB9/4Bu1tgbhIMDoQVYVz+6p+hAAMVFwD97/EA//r7/MsC+ycEDfwAke7k3QAkJSMA2eTgAAT//wAGDAoA+ODkACgPD8wEBD01ABEGCQD6/PwAE1dbAAAGBAAAAP4ABBoZAOPGygQE37e7APTt6AAKMS4A0pSQAAkoKwD99PMA+urtAPr5+f4E8d7gAPoA+QDTnaAAFlhcABs7OQDBYF4ABAAAAPf+Bf8EGm9qAAYUFADQcHAAFEtKAM6QjwAfTE0AHk5MAOXOzwIBiRsf//kDAwAeYF8AFC0rAPv29wDqycoA5KekAP4FCOYDIQD+SUaenhzRcG0A+O3uAOKkpgD76+sAFzQ3AKzj4W8CdEw+r01EQRz/vpft/iBpG1hqpcZYkgqitMZQCwV70YpeVIjSo2ApKBS0UA+N9BIMgj2ICv4DHjSlKHjIIfQgCpaWgm5Dj1ViitWSrckm2V03G7PO+uCdZt7MfN58v2x1du5eb9183LJMyW2bEI5EkHyYxbdcDpXVAjgYuqKDOL2wAO1RFu3dMgQuQw52wwgqzwWz/HWGcy5Ztg2XBhfbLizbwu5WEZZlwdeVqB/QBlz8abXQpLwAbMiuCceQrwvytVTn5MVJOBR0a1VwD2hzhiHar0c8/gl0h+AKHLHbd9ChGCQFMAxsPn3WYU9OJT6rXfwMPL+XB4cqJDJZ6MUtlFaWCQFQxycQn7qBL4uLEMyGDwUwjlrHKwsNu84EJwCEVRyaOIcwaeFTOk2PxjGytASFRLOTW8GHubuIXLgEJRrFb01Dc3sbRl0Hy+fzRVY5GP5eKEBfW4Njm8RIGIyh//wkYleuYjOTgVfV8df/D7pK5Cj6EkmII8M/2OzAwHK8aU1ViO3E/H0cHhtFRSuiViohRN3C8Th+bmxAFCX0kZxaDQsaFeypVrGvBD8KL/b25vtV9fXl6LHpwd6e4+9u3uKBhkGO8xAaHUMslcLOm7f/TQ0pxJIP0nDPJvdfrq+/+uU47/8BcOSbEepX4P0AAAAASUVORK5CYII='
				),
		'technorati' => array(
				'url' => 'http://technorati.com/faves?add',
				'title' => '',
				'image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAIGNIUk0AAHolAAB6JQAAAAAAAHolAAAAAAAAeiUAAAAAAAB6JXIyHmAAAALMSURBVDiNdZNNaJxVFIafe79v/tJpmpSYpE1xaBvFWqPYYvA3ghtDo7jQiuAirW5cFMGVS7cuXIW6baGLqFAKIuKi0ILoIlYtGquhUuIYbUiTftPMZGbud3/OdeEoSM2BlwMHDrzn4T0K4PiHh6v7H917erC6c1YLxBh5buxNHrtnhm3KGWOmK5XKpRTQ+w6NzBX70tnVRkZEeOX+U/8u32j+wLXbC7T9JoOlYSaGnmKsb7xQKpXOra2tTaWTp2o1Ynz51sYmeZ7z8OiTTI0ex0fH/NIHfPn7Z7iQo4AIfFo8wwsHTzCz/+RYlmXHdGi7wW5ui7lxeBd4fHQagMsr5/li6SNsbkE0UTSIpt3Z4pPF09TNIq1Gu5o6B92uiU4FxAcGikMA/LS6gMsdqqDvApA7w1L2PcMcQTsHHZNjjaXbNdxurwJwYOAhjMnJjcUah+1108kJNlKrHsJj0TiL6RhyYzFdy8XrFwCYPvg6UweO0e0YWq0mra0tWq0mzgVeOnyCB/on6TpLMjBe2FO9N30jhlgQiaxsLFMpl5jY8wRP12YYH3mQaqmf0f59HK09y8nJd3n+vtcAuLTw+UU1/mLfkb3P7PoqSVQlRggxsLPSz9nZywxXx/43BCuNXzn73ft8fO7CO6mz4KwnKIgRggSKfRV2V0ZodNaZvzJHubCDXeUBGibjl5tXWax/SzvcARFSHITg8AJRwAfH0I4Rfl67wnvn3+LG6nW00iiliDGiFCQ6pVhNcD6QOufwXiFeEQNIFP64VeftM6+ycWedNClAL0SgiIBH0F6Q4EldQHzwiP3bASjqfy4DoHWCF3cXgygKLRHvRPT6j+6maYZ6LAWCF4IPxN45wf8z+69IPabp883leC3B0hYjWWFQHknKUo6JBBLx20qLC06yxtU4l30j86rnKgWOlnczoRWF7X64h0E6LX4j52tg6y+bw549V9OvnAAAAABJRU5ErkJggg=='
				),
		'delicious' => array(
				'url' => 'http://del.icio.us/post?url',
				'title' => 'title',
				'image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAIGNIUk0AAHolAAB6JQAAAAAAAHolAAAAAAAAeiUAAAAAAAB6JXIyHmAAAAH+SURBVDiNdZM/T1RBFMV/M2/ePlE3RKNZ4xYWmI2NFha2Jho+gR/DwtLaWNBL6XeglYqEYEgsKbByEykworCr6PLenX/XAoF9u3rKmZtfzjl3xgDs7u5eHQwGa865ZaYUArx8ruzvQVFcnE9+6f7h18PH2596QwfYfr//xjm3HGNEVQEwBrzA+Ej58R3sFMCVZf/6ze7ao/rDE7e6unoHeBZCIMZ4PmQMeA91rcgJWHcBkDpypbvwIC6Mn7rj4+Nr3vuOMYaccwsgHmJQQgSrtCRNgW+ObrnJZIKIKEBK6QJgQQS8V6JvRwAQawlZcAAigqrOO2ggeCVIOwKAqiWHv4CcMznnlgNrIWWoTwLNWLFVG2ALkCS44XDI1tYWnU5nLkIMhhv9AZ3Ll7AzJRQ240YBs7i4+LDb7b4HFmZM4lzJysoKS0t3W3CAqqpYX3/3woUQ8N4zq9NOFBHB+4aUYuveGCXGeNrB9P6nAcYYRIS6bv4xYxERXAiBoigwxsxBzgAiMgcoy5KcMw7Is/mm9T9AVVU0TZNdCOGLMWbPWnvv7B9MO0gpEWNslVhVFePxWHZ2dj464Jv3/lVZlq9V9TZgTytQrLXn7yOlhKoaY4yORqOfm5ubbzc2NrbPgjvgIXAf6AAKYK2l1+vhnCPnjLUWY0w+ODj4LCLbwO8/qGY14/bZ/XoAAAAASUVORK5CYII='
				),
		'facebook' => array(
				'url' => 'http://www.facebook.com/share.php?u',
				'title' => 't',
				'image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAIGNIUk0AAHolAAB6JQAAAAAAAHolAAAAAAAAeiUAAAAAAAB6JXIyHmAAAAJNSURBVDiNfZM7aBRRGIW/OzOZnd3Z3cT4QHz2iiIIauELQUtBRMXCQiwstbCykHRaR2JlYyPaWAjaaWfhA8R34fuV6JponMzuzL3/nXstQgTN48Ap//Ofc35+BXBo90hzzb5VN5YuGdirqPAsBMXkz/zr2NfRXVcunnyrgODU0PXLabP/eJZlKNSC4x5PvV7HO3n6+NHDPdGWncfWAgc7nXEqsQsOzyDPeixbvnhj0oz2RpU3iwqjY6MtzlULb/eemXyF1uju1PJIRCiKnrfG45ybJzXYqiJJYmpJH7oUut0ettBEiFDkBa5S+HkEbFWxfes6jh7YxuJFTa7dvM/tO8/QoolEoOyVeNS0xdm+CcOQw/u3sHrFIHfvveTtu1F0WSBaiIQuRdklUNG8Aq1WSprW+J0VjFy+xWSWE/clWGsIAIw2GKOR/6i1Jgjh3JkjDPY3aaYJw+dPsGPbevLuFCJCJCKIsSglzDLgHYFydMYncX4lCkXnR8a37xOIWJwVAkSwlWDEIKL/pRWyLGPowhXGf+VM5SWnz17i/oMnKO8RZ6cdWGNxTubswDlPXxSCAwdUYjBiCCLBVo4ojivXHmjgROP97DN676k3aoSBIlDQbtdwLiZtp5SToQvGx96MBl5/bLXb1OJwbtYiGo0aaT2hFoekaUqI6Hziy4vQGNM1ppxopAObor564gkrCO1fqsCqILS+Evv81Rv7/NVnKXu9n5/ePx5+//rB1ZnXi4DNrdbgBh+pGBf+V0ZF3tMANBuJK7tTH6zV94D8D3QoZ8d9CzilAAAAAElFTkSuQmCC'
				),
		'linkarena' => array(
				'url' => 'http://linkarena.com/bookmarks/addlink/?url',
				'title' => 'title',
				'image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAFo9M/3AAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAALISURBVHjaYjA2Nmb9/ef3W+bwYvMzL949VAIIIEYQjwEImNlUnnaKsqlxAgQQw8RVBZe2npr3/827N2DMcvXOVaab928xbN2/hkFKUIUBIADHcogEIAwDAfDol0HxjjqeAQrFa8i1TFMdkYYBvzu5+zNipO+Z2YXtXENVf956i0QS+ZhRWSG3IIkQlIK8L1DteAUQ46rDPadFhWRNLJX9Gf4BITJgZGRkYLly/SKLoMhjhgs3j4FttVIJYABailDw5v0bxh+/f4AFnj9/xnDm2iEGaQEVBkugQgag2xjFJIRdwyvNt////48ZxXygIfz/5R8ABCCDjFUQBmIwnJ6Cm4uLq08h+By+gbu7os/gA1h8A6mrYHEVV1eh4N7au9xdUz1bU4ej4PBBCF8S8sP9cVu7j0ubDNrwf3l0iAawv25qY7G2ZP9IszQRhbWwO6/AaA2W6zZSyq54ygxcCRDGS0BULBoPaqyEVDlojSxVEJ4WoGTOIv7QqGtBVPjGmyrYxiwp2UwDEQlRvog3GI8/xxJ/1wmm88llOOqPIWhFzJDTkBx7s68AjZG7SgNBFIbPXMQL2IhBC4t4qYTYKGpla6MW4hto74PY+RB5CAnYiRdQiRHxkoQYFslCFnSZ2d3ZHY9ngoiaRByY7vCf73w/FEsH+xfVI23SBLMsQ5Oaf31yhZ7nHUqdvO41/MrwZbUE07kCrMxuAmeiq9jfz/VIKNuuy3eUALQcHr0y3D6fQX6cgmY2QHD5VXyvAKdRBu22FQP8x+BD8wpuaiefRFtExHsShW8hSh3HTCmyZ23XwH3zGsp1F7QAq0TEvxE5AqUVSItGJEZDFJm+93aIXBCdtkyOBDnikkNqUiGDF303MjpY0DoGRPxT3BM5qjROO0Rr8zvgt/yaq3RqYjK3tLie3x0awznO6GCEfkkMGEIU2lb9XBV9Lzj+AAjT3VHhHiPdAAAAAElFTkSuQmCC'
				),
		'twitter' => array(
				'url' => 'http://twitter.com/home?status',
				'title' => '',
				'image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAIGNIUk0AAHolAAB6JQAAAAAAAHolAAAAAAAAeiUAAAAAAAB6JXIyHmAAAAKqSURBVDiNfZNNa5xVGIavc97zzjvpJE5MbCIJotaCHxAFC37sitCd4EK6UaS4c+N/EPIPEpdCd9m5lqASUAIBG8RqKFo/OqkEW+NMZub9Ouc5Hy4Gu2q94dlePFw3twL4ZG9v/tILL3362HzvGiIkHp0szymtfDecjK6+f+HCQAH68+PjzxaXzn94Nh6j4AFAPQSQgF6vhy2rmwdf//SW+WBz8+ko/t37oyHeCylBpjRdrSmDRz2EUjvH8nzvZXNudMW4SfO4Fen4kAgx0M0yLq+tUJgOf9UlB/fuk2JCaYVSPPiwdTnVvb+f1LXU2KZNzrU0TctzvTlu2y4f/aBZ6fV5ceEcT+SGIkZsY3HWIdbStA2NWAwCVWMhBJwIcwq+L+HuBPb+SWwsrtPvw1ru2R8M+GU4IlOKTGnEeYyjprENOuRYL4gXMgVawfatmTYf4e1nM66dX+bHkxMypVAarBWMCDRVjS4KnAjeezAzWa+twlIBIcKV5cTxaExbt2SZIqaEjw4jIjS2xaSIc4KIoHJICa6uwxv9WQ2tbdi6/RvOtWg1A9jaYhBBxBFixDrhz+GIV9fguoLNI3hqIREivLM+x+WLz7BzcIMs0wCIFzQCwQmtbQleuPH7MUv1KR9vQG7gzgT+mMKghYVOB+ss1raIOKJ4jCAE75GYiDHQti3bu1/y3puvs/38CknnM5FNyc7+Ic5aMqXomA4xBkw7nUYRNwOECMDp2ZitL3aZ7xbkmQESpbU4CWRaE0iE6HHWRv3r4eHJZHg2MEUXHzw+eGIMxBiZVDWnkwmnkynWOlKK+ODRJqMcje3do1tHmauqytbNcHFl+ZWi6Ha1IuiE1zziFOLKavjzt99s3fxqd+e/qRjgUn91dUMbk//PmiHGOB0O73hr94HyX2+aotonkag2AAAAAElFTkSuQmCC'
				),
		'furl' => array(
				'url' => 'http://www.furl.net/storeIt.jsp?u',
				'title' => 't',
				'image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAIGNIUk0AAHolAAB6JQAAAAAAAHolAAAAAAAAeiUAAAAAAAB6JXIyHmAAAAKVSURBVDiNdZLPa1xVFMc/9933ZjKZJE0HIxGMU4td2EItojAo2CCKCzeFoi7FjQvFhX+CiCKiGxGKICiCLhRbcCUGLAqlQi1o2rQKFVpjTGnal5bM+3HfOfdeF2MDY+OBszvfL5/vl2MATr6yONXfe++H3dldL6pNIPK/00oM1c2tM9c3bj338FvHrxggufj2Cx/3ZiZf2toswYwOQwS8EonYNNs2CBG63RaVsnzq9O9Ppm8+cbAfDEc3r+aoBABijBhg6v59mGyCrUsrEAO33Zutgun52YNmV/vp9EYsd1d107K1EGMciRND/+jL9AZHaK7/Sf7+a0QRMMk2hqsdG8NqPhWBpqijFSGESBBhfnCY3uAI60ufsv7DNzTDYjvaCBHSssI5JRWBqipJFbz3BGmYeXBA1IbL335FmefYVov/NmuGoCqkglAVjoxI1pmk0+vR3bOfOl8nZimm3aaua8YRwBARp6QClIWj07YMXn+XmQcOgUkBeOyd46wufc6Zj94jybIxgxgUJ0IqUuBkmuiVc18e477Hn2Fh8XlWf/yaaxfPsnHhV5x6TBj/h+gNqoEUARFHIGFl6Tum7umzsAjnT3zC6i/nyCbamCQB/LhDsMiIAJx4kugJ1nLXgUfRaki+toZPLFH9nWLAEtCgoxK9KuIjrc4Eu/ceIP9jmc2NG/9+o7lDDNCyAa+Q1pUG7z2uVqbvnmNyboFL35+gGNbYVrajmAhqLc6FkCyvFX/nw+ZKN0vY/9SzgGH9/M+oBrz3O64lcKtu3IWb5YotoKgk5vv2zB06/Oob3au/nfU/fXZMVVUxKIyvBSl9yE9fqz84+dfwi9sB004ne6Q/O/lQVZdp5SQk7IxvIOSNXHbKKWD4D2R9cxZJWrLgAAAAAElFTkSuQmCC'
				),
		'yigg' => array(
				'url' => 'http://yigg.de/neu?exturl',
				'title' => '',
				'image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAFo9M/3AAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAANBSURBVHjaYnBwcGD5DwRM8fHxvbdW5TEABBAjiMcABCxXJvkzvFBIYgAIIIbdu3fPenLrxv+3b9/+f7y0/j/Lo0ePGECqri+Yy6Dp4MoAEEAMf//+ff0fCj5+/DidEaT0769fDG8vn2VgFRZmYLp5+TLDs4VhDAwvtjJ8Z+NhYPkClL0vEscgKiHPwPrmDQNAADFcu3Zt0o8fP/5jwz9//vzPcuXKFUYuLi6G34e6Gfj4mRken73OoJC3nOHNgYkM/9TDGZg+ffrE/PTpU4YHIl4M598pM1z7pc4wJyOK4a2kB8PHL58ZGCUlJU0WL158+t+/fwzo4MOHD88BAojh5cuXqf+xAKCGb319fZyMIIdAwwcDvHr16iDLt2/fGP4Avfb3928GVk5Ohk9PHzPwM71iuLtjJwObaxwHC9CRDO9vnmeQEnjB8PrwFgZ2cVmGP0rmDF+ERBmAkn9ZPn/+zPD+wWUG5s+HGFhEFRle37vKcO+3FoOYqTcDMOD/MX358oWBTduJ4fwHXYYHbHYMJy7/Y/jGI84AMvnXr1/MLEBHMoAwn645w7kNSxhMU+vADvz69Sso2liYrl69ehIkwCUgyGCakAv3ASMjI0NnZ2cHQIDKyaYlgSgKw+/kdWYsZsZE/EghJCZEW7jIRVCLXGSUQat+Q3+gTbv2/YjWraJNgWRQ2CIocBERpWCGWYgTLlLnQzt3IGgRQQcOXLj33Huf97wHlUqlYNu2RTn6b9br9T1GT21S49hvSv+MbrOBfuMeok+BzSYQmE2B6lYZGcjioFzsoW3h4WAfWjwKjygBWgxjvkmgWsR0YRv+dBIfJ7swugq1cQt2IOowwzBMrkjt4gxK4xj6tIyB1QQGQzA1TUZ8wVBy0H2+Jm4HVbaB8fkk1FAIhOAwy7JcqYOZLJx0BpfFI/g9bUzFvOhXH/HZaYOZHbzeCZDmlhFLZVwBeQ0hCIzwRG4HushljSzm8XR6iJvyLZRwBMJIRHxlB5qmEpbXLXQHjzE+UIyPWS2RSKDX6+HbdNGldYQW8qiVz+ELRiCpJBzt2QOTem66Z2RZ5nZ+F2gdpsjmcrk1Xddn6Hse/B2jVqv1ViqVinTB1RdKXekUCVhfMgAAAABJRU5ErkJggg=='
				),
		'webnews' => array(
				'url' => 'http://www.webnews.de/einstellen?url',
				'title' => 'title',
				'image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAFo9M/3AAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAL/SURBVHjaYnRwcGDZp6b2m2GugsLEq+fO/wcIIMZ/aWn/GYCA5cr5cwz/P35iAAggxl1OTrNMOThSOZycGL5u2cLA8ujuXYZ/794xMBw+DFLJABBAjH+Skl4zsbCIgDiffv6cwfTh3j2Rn2JiDD81NBj+Pn2awfT02TOGe+rqDPdcnBmePHrEwLiDlxdsDQiwMTIyAAQQ49WUlEmKz5/nMmABjJycDCxXT5xg5GJnZ2ARFGTgrqxkYNDSArqSheGjhwcDI9AElk+fPjE//fWLgQFo14/v3xkYOzsY/hkaMXA+fcrABLJCUlLSZNGrV6f/YbHiAwvLc4AAbJA9CsJAEIVnYwqTQAwInsAuB7DMHbRIkdq7iJ2WXkMPoKWxUpAcwRgU2fy4WdjNOLFTUgwM7z2Gbx5Lo2g+su0N3fsxUWux8v0hE2GIWJZdjJB53sEUrxc0xOAQFD6poncFumlAJglwIfpmTgEkgU1nwC5nAIJTUkIVx8C11mbOObTNvLP795M2gMSjiwK4Uo1R0VLkOWSrNdwcG9IggMd299VUXffY3nWxRuyErCzrZF6VOo6lnPybBs1yMFh8BKic/FkaBsIw/lzvWhJL7VLwzyCIIAhdnURwFHFw1KnfpIOfQ0ejmx9AcHTvJDRQiqlW02Ji7Jk0l1x801aoOogvHBz353m55/ccazWbh1vd7jWkFEgS/PTjV+XdCgWwchlPlcqpkK3W0VhroaMI/ylG1oW+vy+GrqtGBFen6WSjWKuhYllAu02+esDuDtjVJXB8AnVh4f38bCpADX2lUuENBnFAsclmRnDOEdo2mPMAeD7gOEDvEazfR9rrISL3pgoMb3GcChXHhRG9Xc8EsiSFpoNZdREwF4D8A9TrxN0Fex2CE5bpfYZIaybyYH/QovpCQfOs0UC4ugY+dFGMQoxXlsGlRGkuUyKHLagc0+ysk3pIAt9o2vcgJpOBTgd5nNXctkECfrXq5syWqLb3TPNgMwg2iC//A0D2bBgvt6XSjS/l3SeM61loRwcixwAAAABJRU5ErkJggg=='
				),
		'google' => array(
				'url' => 'http://www.google.com/bookmarks/mark?op=edit&bkmk',
				'title' => 'title',
				'image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAACvUlEQVQ4jY2SzWucVRTGf++8952PFE1mTGwjMQS68ANMS/3CIhgQdSGCQa3tzj/JrWvRVasisYKgkQ61hYCWamtrGujQNIkzCZPMm8n9OvdeFxkt2fmDZ3M458A5zwPAUvuL5tbgwVL6n+zq3uovN5efBWBhYUFtdDsXU0rJOXdE3h9KxCcR/189pZT6u717n1/6bFq9tHDq+Wox9p5zDhEBIMtAqYy9PVAKRBLWeCaaBQDeCxPjkycPSv+R2l7fOe6cU6ZiiDECQMz46vIOm1slD9Yfo3O/y/uLdT5YnCOMWoqqYmtzZ7ZiJWC1wVqLMYYYHD+0B1xbucW5C1Vefl1YWyt5vCWE4DHGHEprgrNURASjDVprjDFYp1lZMZSlpVAZb5xt8PRszq3f72O9e7TAWFwQlATBWEueF4QQiAmmmp7lbwuuXR/QbEJD9XjxzHHKwRCRwxuySoY3HoUEDswBFXJiihgD77wJpIKr7QqdTp8Ln0wxN9uiLA/IMkYkJAgqiKCNQVEQUwQiKq9w9pUn+e1Gxk+XHVd+rDEzrYHEIxJeAkoCOGMxqSCkQCXPaf9asPbnOjNzkbcXhSvfNUmZ5fzHx3B+NJ+BeIcCi3cOjYYU6A+b/Lzc4/y5XZ6anqRWz3lissL3Sz2G+xBSDkCuckQCysphMDIcGUK/V7LdaXB3tUV9LMJupLuhOf0CePH4UdiKakGIEWXsPl48KUIIgalJx1vvBm6ujLP6V43xuuHETMn8q2MMtSONglRv1DFWo+7cuLe+sfmw+8zJ504MBgNCgFOnM+bn93AeVF4hV+B9JI1+WKvV2N7upjt/3L6da6373e7f1Ynm+Jl6o14LMeCcx4kQouCDxzmPiEeiR4LQ7W3Zr7+5dPFq+/qn/7p6DPiw1Wq+litVIzvi1xFSTHG4X97V2n4JPPwH/vnyaiHKDQkAAAAASUVORK5CYII='
				)
		);		
		
	// process comma seperated inputs if assigned
	if (!empty($services) && $services != 'all') {
	
		// build array
		$assortment = split(',', $services);
	
		// delete whitespaces
		$assortment = array_map('trim', $assortment);
	
		// switch keys 
		$assortment = array_flip($assortment); 
		
		// intersect from $given_services array
		$given_services = array_intersect_key($given_services, $assortment);
	}
			
	foreach ($given_services as $service => $parts) {
				
		if (empty($parts['title'])) {
			$content[] = array(
				'url' => $parts['url'].'='.$url,
				'image' => $parts['image'],
				'service' => $service
				);
		} else {
			$content[] = array(
				'url' => $parts['url'].'='.$url.'&amp;'.$parts['title'].'='.rawurldecode($title),
				'image' => $parts['image'],
				'service' => $service
				);
		}
	}
	
	// assign paths
	$smarty->assign($var, $content);
}
?>