/*
 * core javascript - includes JS needed by all users
 *
 *
 */
 
function url(target)
{
	return BASE_URL + target;
}

function add_load_event(func)
{
	var oldonload = window.onload;
	if (typeof window.onload != 'function')
	{
		window.onload = func;
	}
	else
	{
		window.onload = function()
		{
			oldonload();
			func();
		}
	}
}

function add_resize_event(func)
{
	var oldonresize = window.onresize;
	if (typeof window.onresize != 'function')
	{
		window.onresize = func;
	}
	else
	{
		window.onresize = function()
		{
			oldonresize();
			func();
		}
	}
}

function add_scroll_event(func)
{
	var oldonscroll = window.onscroll;
	if (typeof window.onscroll != 'function')
	{
		window.onscroll = func;
	}
	else
	{
		window.onscroll = function()
		{
			oldonscroll();
			func();
		}
	}
}

function set_opacity(obj, value)
{
	obj.style.opacity = value/100;
	obj.style.filter = 'alpha(opacity=' + value + ')';
}

// END: PROTOTYPE

function urlencode(plaintext)
{
	// The Javascript escape and unescape functions do not correspond
	// with what browsers actually do...
	var SAFECHARS = "0123456789" +					// Numeric
					"ABCDEFGHIJKLMNOPQRSTUVWXYZ" +	// Alphabetic
					"abcdefghijklmnopqrstuvwxyz" +
					"-_.!~*'()";					// RFC2396 Mark characters
	var HEX = "0123456789ABCDEF";

	var encoded = "";
	for (var i = 0; i < plaintext.length; i++ ) {
		var ch = plaintext.charAt(i);
	    if (ch == " ") {
		    encoded += "+";				// x-www-urlencoded, rather than %20
		} else if (SAFECHARS.indexOf(ch) != -1) {
		    encoded += ch;
		} else {
		    var charCode = ch.charCodeAt(0);
			if (charCode > 255) {
			    alert( "Unicode Character '" 
                        + ch 
                        + "' cannot be encoded using standard URL encoding.\n" +
				          "(URL encoding only supports 8-bit characters.)\n" +
						  "A space (+) will be substituted." );
				encoded += "+";
			} else {
				encoded += "%";
				encoded += HEX.charAt((charCode >> 4) & 0xF);
				encoded += HEX.charAt(charCode & 0xF);
			}
		}
	} // for
	return encoded;
};


function getElementsByClassName(className, tag, elm){
	var testClass = new RegExp("(^|\\\\s)" + className + "(\\\\s|$)");
	var tag = tag || "*";
	var elm = elm || document;
	var elements = (tag == "*" && elm.all)? elm.all : elm.getElementsByTagName(tag);
	var returnElements = [];
	var current;
	var length = elements.length;
	for(var i=0; i<length; i++){
		current = elements[i];
		var classNames = current.className.split(' ');
		for (var y=0; y<classNames.length; y++)
		{
			var check = classNames[y];
			if (check == className)
			{
				returnElements.push(current);
				break;
			}
		}
	}
	return returnElements;
}

function updateStylesheets()
{
	var i,a,s;
	a=document.getElementsByTagName('link');
	for(i=0;i<a.length;i++)
	{
		s=a[i];
		if(s.rel.toLowerCase().indexOf('stylesheet')>=0&&s.href)
		{
			var h=s.href.replace(/(&|\\?)forceReload=d /,'');
			s.href=h+(h.indexOf('?')>=0?'&':'?')+'forceReload='+(new Date().valueOf());
		}
	}
}

function loadjscssfile(filename)
{
	var filetype = filename.split('.').pop();
	
	if (filetype=="js")
	{ //if filename is a external JavaScript file
		var fileref=document.createElement('script')
		fileref.setAttribute("type","text/javascript")
		fileref.setAttribute("src", filename)
	}
	else if (filetype=="css")
	{ //if filename is an external CSS file
		var fileref=document.createElement("link")
		fileref.setAttribute("rel", "stylesheet")
		fileref.setAttribute("type", "text/css")
		fileref.setAttribute("href", filename)
	}
	if (typeof fileref!="undefined")
		document.getElementsByTagName("head")[0].appendChild(fileref)
}


function Pico_LoadAP(url, title, complete_func)
{
	var ap_content  = document.getElementById('ap_content');
	var ap_title    = document.getElementById('ap_title');
	var ap_title_bg = document.getElementById('ap_title_bg');
	set_opacity(ap_content, 0);
	set_opacity(ap_title, 0);
	set_opacity(ap_title_bg, 100);
	
	ap_title.innerHTML = title;
	
	new Ajax.Updater('ap_content', url, { onComplete: function() { 
		Pico_FadeIn(ap_content, 0);
		Pico_FadeIn(ap_title, 0); 
		if (complete_func != null)
		{
			complete_func();
		}
	} });
}

function Pico_FadeIn(obj, alpha, complete_function)
{
	var new_alpha = alpha + 5;
	if (new_alpha > 100) { new_alpha = 100; }
	set_opacity(obj, new_alpha);
	
	if (new_alpha != 100)
	{
		var func = function() { Pico_FadeIn(obj, new_alpha, complete_function); };
		setTimeout(func, 1);
	}
	else
	{
		if (complete_function != null)
		{
			complete_function();
		}
	}
}

function Pico_FadeOut(obj, alpha, complete_function)
{
	
	var new_alpha = alpha - 5;
	set_opacity(obj, new_alpha);
	if (new_alpha != 0)
	{
		var func = function() { Pico_FadeOut(obj, new_alpha, complete_function); };
		setTimeout(func, 1);
	}
	else
	{
		if (complete_function != null)
		{
			complete_function();
		}
	}
}

function Pico_MoveAP(current, finish, finish_func)
{
	var next = current - 40;
	
	var ap = document.getElementById('action_panel');
	
	if (next < finish)
	{
		next = finish;
	}
	ap.style.right = next + 'px';
	
	if (next != finish)
	{
		var next_func = function() { Pico_MoveAP(next, finish, finish_func); }
		setTimeout(next_func, 1);
	}
	else
	{
		var ap_content = document.getElementById('ap_content');
		if (finish_func != null)
		{
			finish_func();
		}
		//Pico_FadeIn(ap_content, 0);
	}
}

function Pico_scrollTop() {
	var ScrollTop = document.body.scrollTop;
	if (ScrollTop == 0)
	{
		if (window.pageYOffset)
		{
			ScrollTop = window.pageYOffset;
		}
		else
		{
			ScrollTop = (document.body.parentElement) ? document.body.parentElement.scrollTop : 0;
		}
	}
	return ScrollTop;
}

function Pico_DisplayAP(url, title, width, height, complete_func)
{
	// if the window is displayed, fade out, hide text, resize
	var ap         = document.getElementById('action_panel');
	var ap_content = document.getElementById('ap_content');
	var ap_title   = document.getElementById('ap_title');
	
	if (ap_content.innerHTML.length > 0)
	{
		// fade out
		
		var close_func_obj = document.getElementById('on_ap_close');
		if (close_func_obj)
		{
			eval(close_func_obj.value);
		}
		
		var func = function() { 
			ap_content.innerHTML = '';
			ap_title.innerHTML = '';
			Pico_DisplayAP(url, title, width, height, complete_func);
		};
		
		Pico_FadeOut(ap_title, 100);
		Pico_FadeOut(ap_content, 100, func);
		return;
	}
	
	if (ap.style.display == 'block')
	{
		// resize
		
		
	
		var current_width  = parseFloat(ap.style.width);
		var current_height = parseFloat(ap.style.height);
		var func = function () { Pico_LoadAP(url, title, complete_func); };
		
		if ( (current_width == width) && (current_height == height) )
		{
			func();
		}
		else
		{
			Pico_ResizeAP(current_width, current_height, width, height, 1, func);
		}
		return;
	}
	else
	{
		// else slide over
		var window_size = WindowSize();
		
		if ((width == null) || (height == null))
		{
			var ap_width = window_size.width - 180;
			var ap_height = window_size.height - 180;
		}
		else
		{
			var ap_width = width;
			var ap_height = height;
		}
		
		var right = (window_size.width - ap_width) / 2;
		var top  = (window_size.height - ap_height - 85) / 2; // where the window will stop when moving 
		
		ap.style.width  = ap_width + 'px';
		ap.style.height = ap_height + 'px';
		ap_content.style.height = (ap_height - 30) + 'px';
		
		var new_top = (Pico_scrollTop()+top);
		
		var current = window_size.width;
		
		ap.style.display = 'block';
		
		if (ap.style.display == 'block')
		{
			ap.style.top = new_top + 'px';
			ap.style.right = window_size.width + 'px';
		}
		
		var func = function() {
			Pico_LoadAP(url, title, complete_func);
		};
		
		Pico_MoveAP(current, right, func);
	}
}

function Pico_CenterAP()
{
	var ap = document.getElementById('action_panel');
	if (ap)
	{
		var window_size = WindowSize();
		
		var ap_width  = parseFloat(ap.style.width);
		var ap_height = parseFloat(ap.style.height);
		
		var right = (window_size.width - ap_width) / 2;
		var top  = (window_size.height - ap_height - 85) / 2; // where the window will stop when moving 
		
		if (ap.style.display == 'block')
		{
			var new_top = (Pico_scrollTop()+top);
			ap.style.top = new_top + 'px';
			ap.style.right = right + 'px';
		}
	}
}

function Pico_ResizeAP(original_width, original_height, final_width, final_height, step, finish_func)
{
	var ap = document.getElementById('action_panel');
	var ap_content = document.getElementById('ap_content');
	// there will always be X steps spanning Y milliseconds
	var num_steps = 50;
	var total_milliseconds = 250;
	var delay = total_milliseconds / num_steps;

	if (step == num_steps)
	{
		// this is the last resize, just resize specifically and end it
		ap.style.width  = final_width + 'px';
		ap.style.height = final_height + 'px';
		ap_content.style.height = (final_height - 30) + 'px';
		
		if (finish_func != null)
		{
			finish_func();
		}
	}
	else
	{
		
		var width_mod = (final_width-original_width) * (step/num_steps);
		var current_temp_width  = original_width + width_mod;
		
		var height_mod = (final_height-original_height) * (step/num_steps);
		var current_temp_height = original_height + height_mod;
		
		ap.style.width  = current_temp_width + 'px';
		ap.style.height = current_temp_height + 'px';
		
		// recenter the window
		Pico_CenterAP();
		
		ap_content.style.height = (current_temp_height - 30) + 'px';
		var next_step = step + 1;
		
		var func = function() {
			Pico_ResizeAP(original_width, original_height, final_width, final_height, next_step, finish_func);
		}
		setTimeout(func, delay);
	}
}

function Pico_CloseAP(complete_func)
{
	var ap         = document.getElementById('action_panel');
	var ap_title   = document.getElementById('ap_title');
	var ap_content = document.getElementById('ap_content');
	
	var close_func_obj = document.getElementById('on_ap_close');
	if (close_func_obj)
	{
		eval(close_func_obj.value);
	}
	
	var func = function() {
		ap.style.display = 'none';
		ap_content.innerHTML = '';
		
		ap_title.innerHTML = '';
		set_opacity(ap, 100); // make it visible for later
		if (complete_func != null)
		{
			complete_func();
		}
	};
	Pico_FadeOut(ap, 100, func);
}

function WindowSize()
{
	var myWidth = 0, myHeight = 0;
	if( typeof( window.innerWidth ) == 'number' )
	{
		//Non-IE
		myWidth = window.innerWidth;
		myHeight = window.innerHeight;
	}
	else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) )
	{
		//IE 6+ in 'standards compliant mode'
		myWidth = document.documentElement.clientWidth;
		myHeight = document.documentElement.clientHeight;
	}
	else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) )
	{
		//IE 4 compatible
		myWidth = document.body.clientWidth;
		myHeight = document.body.clientHeight;
	}
	var return_val = new Array();
	return_val.width = myWidth;
	return_val.height = myHeight;
	return return_val;
}

var ap_center = function() {
	Pico_CenterAP();
}

add_resize_event(ap_center);
add_scroll_event(ap_center);


document.observe("dom:loaded", function() {

  // replace mouseovers
  
  $$('img.hover').each(function(el) {
    var src = el.readAttribute('src');
    var parts = src.split('.');
    var ext   = parts.pop();
    var path  = parts.pop();
    path      = path + '-hover';
    parts.push(path);
    parts.push(ext);
    var hover_src = parts.join('.');

    el.orig_src  = src;
    el.hover_src = hover_src;

    $(el).observe('mouseover', function() {
      $(this).setAttribute('src',this.hover_src);
    });

    $(el).observe('mouseout', function() {
      $(this).setAttribute('src',this.orig_src);
    });
  });

  // find form elements with default text

  $$('input.text, textarea').each(function(el) {
    if ($(el).getAttribute('dummytext'))
    {
      var dummytext = $(el).getAttribute('dummytext');

      if ($(el).getValue() == '') {
        $(el).setValue(dummytext);
      }

      $(el).observe('focus', function() {
        if ($(this).getValue() == dummytext) {
          $(this).setValue('');
        }

        if ($(this).getAttribute('pwd') == 'yes') {
          $(this).setAttribute('type', 'password');
        }
      });

      $(el).observe('blur', function() {
        if ($(this).getValue() == '') {
          $(this).setValue(dummytext);

          if ($(this).getAttribute('pwd') == 'yes') {
            $(el).setAttribute('type', 'text');
          }
        }
      });
    }
  });
});