function Select(num) {	
	c_text = num
}

function Start() {
	c_text = 0
}

function Color() {
}

function PutColor(value) {
	if (c_text == 0) {
		form1.back_color.value = value
	} else if (c_text == 1) {
		form1.desc_color.value = value
	} else if (c_text == 2) {
		form1.text_color.value = value
	} else if (c_text == 3) {
		form1.data_color.value = value
	} else if (c_text == 4) {
		form1.tag_color.value = value
	} else if (c_text == 5) {
		form1.descriptor_color.value = value
	} else if (c_text == 6) {
		form1.legend_color.value = value
	} else if (c_text == 7) {
		form1.user_color.value = value
	}
}
