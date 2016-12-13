/* Плагин: сервис опечаток от компании Etersoft
*  email: info@etersoft.ru
*  автор: barbass@etersoft.ru
*  дата: 2012-04-18
*/

//////////////////////////////////////////////////////

window.document.onkeydown = function(e)  {
	if  (e.ctrlKey==1 && e.keyCode == 13)  { 
		ETY.control_panel();
	}
}

var ETY = {
	time: 0, //время последнего запроса
	server_url: "http://eterfund.ru/api/typos/server.php",
	select_text: "",

	//Выделенный текст
	get_select_text: function()  {
		var text = String(window.getSelection()).trim().substr(0, 50);
		if  (typeof(text) == 'undefined')  {
			text = "";
		}
		return text;
	},
	
	control_panel: function()  {
		if  (document.getElementById("e_typos_div").style.display == "block")  {
			this.close();
		}  else  {
			this.div();
			ETY.select_text = ETY.get_select_text();
		}
	},

	div: function()  {
		//Определяем на какой позиции X, Y всплывет элемент
		var top = window.pageYOffset + window.innerHeight/3;
		var left = window.pageXOffset + window.innerWidth/3;
		document.getElementById("e_typos_div").style.top = top + "px";
		document.getElementById("e_typos_div").style.left = left + "px";
	
		document.getElementById("e_typos_error").style.display = "none";
		document.getElementById("e_typos_comment").value = "";
	
		document.getElementById("e_typos_div").style.display = "block";
	},

	close: function()  {
		document.getElementById("e_typos_div").style.display = "none";
	},

	post_data: function()  {
		var this_url = window.location.href; //Текущая страница
		var post_text;
		if  (ETY.select_text == "")  {
			post_text = ETY.get_select_text();
		}  else  {
			post_text = ETY.select_text;
		}

		var new_text = ETY.get_select_text();

		if  (new_text != ETY.select_text && new_text.length != 0)  {
			post_text = new_text;
		}

		//Выделенный текст
		var user_comment = document.getElementById("e_typos_comment").value.trim().substr(0, 50);
			if  (user_comment == '')  {
				user_comment = "";
			}
		
		if  (post_text.length == 0)  {
			this.error("red", "Вы ничего не выделили");
		}  else if  (post_text.length < 5)  {
			this.error("red", "Выделенный текст слишком короток");
		}  else  if  (post_text.length > 30)  {
			this.error("red", "Выделенный текст слишком длинный");
		}  else  {
			ETY.select_text = post_text;
			//Ajax-запрос
			this.ajax_query(this.server_url, "e_typos_url="+this_url+"&e_typos_comment="+encodeURIComponent(user_comment)+"&e_typos_error_text="+encodeURIComponent(post_text));	
		}
	},

	//Отправка запроса
	ajax_query: function(url, post_data)  {
		this.error("black", "Идет отправка данных...");

		var XHR = window.XDomainRequest || window.XMLHttpRequest;
		request = new XHR;
	
		//Если не поддерживаются кроссдоменные запросы
		if  (request.withCredentials == undefined)  {
			var old_url = window.location.href;
			
			newWin = window.open(url+'&e_typos_oldbrowser=1', '_blank');
			window.parent.focus();

			this.error("green", "Спасибо за ваше внимание");
			window.setTimeout('ETY.close()', 2000);
			return false;
		}
	
		var result = this.timer();
		if  (result == 0)  {
			this.error("red", "Не отправляйте данные часто");
			return false;
		}  else  {
			this.time = this.now_time();
			this.set_storage("etersoft_typos/"+window.location.hostname+"", this.time);
		}
	
		request.open("POST", url, true);
		request.setRequestHeader("Content-type","application/x-www-form-urlencoded");
		
		request.onload = function()  {
			var response = request.responseText;
			switch (response)  {
				case '10robot': 
					ETY.error("red", "Есть подозрения что вы робот");
					break;
				case '10dataerror': 
					ETY.error("red", "Данные некорректны");
					break;
				case '10siteerror': 
					ETY.error("red", "Данный сайт не поддерживается");
					break;
				case '10emailerror': 
					ETY.error("red", "Сервер не смог отправить письмо");
					break;
				case '10win': 
					ETY.error("green", "Спасибо за ваше внимание");
					window.setTimeout('ETY.close()', 2000);
					break;
				case '10inserterror': 
					ETY.error("red", "Ошибка добавления данных");
					break;
				case '10servererror': 
					ETY.error("red", "На сервере произошла ошибка");
					break;
				case '10timeerror': 
					ETY.error("red", "Не отправляйте данные часто");
					break;
				default:
					ETY.error("red", "На сервере произошла ошибка");
					break;
			}
		}
		request.onerror = function()  {
			ETY.error("red", "Ошибка отправки данных. Повторите позже");
			this.time = 0;
		}
		request.send(post_data);
	},

	error: function(color, text)  {
		document.getElementById("e_typos_error").style.display = "block";
		document.getElementById("e_typos_error").innerHTML = text;
	
		document.getElementById("e_typos_error").style.color = color;
		document.getElementById("e_typos_error").style.borderColor = color;
	},
	
	text: function(text)  {
		document.getElementById("e_typos_user_text").style.display = "block";
		document.getElementById("e_typos_user_text").innerHTML = "Ваш текст: "+text;
	},

	timer: function()  {
		var sec = this.now_time();
		var time_s = this.get_storage("etersoft_typos/"+window.location.hostname+"");
		if  (time_s != 0)  {
			this.time = time_s;
		}
	
		if  (this.now_time() - parseFloat(this.time) < 60000)  {
			return 0;
		}  else  {
			return 1;
		}
	},

	now_time: function()  {
		var day = new Date();
		var sec = day.getTime();
		return sec; 
	},

	get_storage: function(key) {
		if (window['sessionStorage'] != null) {
			if (!sessionStorage.getItem(key+"")) {
				return 0;
			} else {
				var data = sessionStorage.getItem(key+"");
				return data;
			}
		} else {
			return 0;
		}
	},

	set_storage: function(key, data) {
		if (window['sessionStorage'] != null) {
			sessionStorage.setItem(key+"", data);
		} 
	}

}

