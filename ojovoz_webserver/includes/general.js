function ChangeChannel(obj,mp,ip) {
	if (obj.options.value != "") {
		value = parseInt(obj.options[obj.selectedIndex].value)
		if (value >= 0) {
			document.location = mp + "?c=" + value
		} else {
			if (value == -1) {
				document.location = ip
			}
			if (value == -2) {
				document.location = ""
			}
			if (value == -3) {
				document.location = "map.php"
			}
			if (value == -4) {
				document.location = "tags.php"
			}
		}
	} 
}

function ChangeTagChannelMode(obj) {
	if (obj.options.value != "") {
		value = parseInt(obj.options[obj.selectedIndex].value)
		document.location = "tags.php?t=" + value
	}
}

function ChangeSection(form,mp,mode,c) {
	if (form.section.options.value != "") {
		if (mode==0) {
			if (typeof c=="undefined") {
				val1 = form.c.value
			} else {
				val1 = c
			}
			val2 = form.section.options[form.section.selectedIndex].value
			document.location = mp + "?c=" + val1 + "&from=" + val2 + "&r=0" + "#content"
		} else if (mode==2) {
			val1 = form.nc.value
			val2 = form.section.options[form.section.selectedIndex].value
			document.location = mp + "?c=" + val1 + "&from=" + val2
		} else if (mode==1) {
			val1 = form.section.options[form.section.selectedIndex].value
			document.location = mp + "?from=" + val1
		}
	}
}

function ChangeDate(form,mp,c) {
	if (form.dates.options.value != "") {
		val = form.dates.options[form.dates.selectedIndex].value
		document.location = mp + "?c=" + c + "&date=" + val + "#content"
	}
}

function ChangeMapSection(form) {
	if (form.section.options.value != "") {
		val=form.section.options[form.section.selectedIndex].value
		document.location = "map.php?from=" + val
	}
}

function ResetChannel(mp,c) {
	document.location = mp + "?c=" + c + "&r=1"
}

function SearchChannel(mp,c,f) {
	q=f.q.value
	document.location = mp + "?c=" + c + "&search=1&q=" + q
}

function ResetMap(mp) {
	document.location = mp + "?r=1"
}

function noenter() {
  return !(window.event && window.event.keyCode == 13); 
}

function DeletePrompt(n,p) {
	if (n==0) {
		c=document.forms[1].web_text.value
		if (c.toUpperCase() == p.toUpperCase()) {
			document.forms[1].web_text.value='';
		}
	} else if (n==1) {
		c=document.forms[1].web_alias.value
		if (c.toUpperCase() == p.toUpperCase()) {
			document.forms[1].web_alias.value='';
		}
	} else if (n==2) {
		c=document.forms[0].address.value
		if (c.toUpperCase() == p.toUpperCase()) {
			document.forms[0].address.value='';
		}
	} else if (n==3) {
		c=document.forms[1].q.value
		if (c.toUpperCase() == p.toUpperCase()) {
			document.forms[1].q.value='';
		}
	}
}

function SelectText() {
	document.forms[0].q.focus();
	document.forms[0].q.select();
}

function StartVKeyboard() {
	act_text = 0
	text_written = 0
	alias_written = 0
	accent = ''
}

function PutAccent(valor) {
	accent = valor
}

function Select(num,p) {	
	DeletePrompt(num,p);
	act_text = num
}

function ChangeState(num) {
	if (num == 0) {
		text_written = 1	
	} else {
		text_written = 1
	}
}

function Write() {
}

function PutLetter(letter) {
	if (accent != '') {
		switch(accent) {
			case "´":
				switch(letter) {
					case "A":
						letter = "Á"
						break
					case "E":
						letter = "É"
						break
					case "I":
						letter = "Í"
						break
					case "O":
						letter = "Ó"
						break
					case "U":
						letter = "Ú"
						break
					}
				break
			case "`":
				switch(letter) {
					case "A":
						letter = "À"
						break
					case "E":
						letter = "È"
						break
					case "I":
						letter = "Ì"
						break
					case "O":
						letter = "Ò"
						break
					case "U":
						letter = "Ù"
						break
					}
				break
			case "¨":
				switch(letter) {
					case "A":
						letter = "Ä"
						break
					case "E":
						letter = "Ë"
						break
					case "I":
						letter = "Ï"
						break
					case "O":
						letter = "Ö"
						break
					case "U":
						letter = "Ü"
						break
					}
				break
			case "^":
				switch(letter) {
					case "A":
						letter = "Â"
						break
					case "E":
						letter = "Ê"
						break
					case "I":
						letter = "Î"
						break
					case "O":
						letter = "Ô"
						break
					case "U":
						letter = "Û"
						break
					}
				break
		}
	}
	if (act_text == 0) {
		if (text_written == 0 && letter!= '*') {
			text_written = 1
			document.forms[1].web_text.value = letter
		} else {
			text = document.forms[1].web_text.value 
			if (letter == '*') {
				text = text.substr(0,(text.length -1))
			} else {
				text = text + letter
			}
			document.forms[1].web_text.value = text
		}
	} else if (act_text == 1) {
		if (text_written == 0 && letter != '*') {
			text_written = 1
			document.forms[1].web_alias.value = letter
		} else {
			text = document.forms[1].web_alias.value 
			if (letter == '*') {
				text = text.substr(0,(text.length -1))
			} else {
				text = text + letter
			}
			document.forms[1].web_alias.value = text
		}
	}
	accent = ''
}