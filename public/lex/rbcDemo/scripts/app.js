// Edit by Kaiyang - Kamazooie Development Corporation


var debugmode = true;

function pushChat(validUser, src = 'user', kamaState = '') {

console.log(validUser);
	var d = new Date();
	console.log('pushChat starts= ' + d.getTime());


	// if there is text to be sent...
	var wisdomText = document.getElementById('wisdom');

	if (wisdomText && wisdomText.value && wisdomText.value.trim().length > 0) {

		// disable input to show we're sending it
		var wisdom = wisdomText.value.trim();
		wisdomText.value = '...';
		wisdomText.locked = true;

		// send it to the Lex runtime
		var params = {
			botAlias: '$LATEST',
			botName: 'rbcQuote',
			inputText: wisdom,
			userId: lexUserId,
			sessionAttributes: sessionAttributes
		};
		showRequest(wisdom, src, debugmode);

		if (validUser === false) {
			if (!validateEmail(wisdom)) show_L_Response (lexResponse, 'Oops, the email address is not valid, could you input a valid email address for me please?');
			else {

				IDcheck_Registration (kamaAPI.portalcode, kamaAPI.org, wisdom);
				lexResponse.dialogState = 'SYS_MESSAGE';
				show_L_Response (lexResponse, 'Thank you! I have you registered. How can I help you?');
			}

			// re-enable input
			wisdomText.value = '';
			wisdomText.locked = false;

		}

		else {

			// preprocess wisdom before sending to Lex
			lexruntime.postText(params, function(err, data) {
				if (err) {

					console.log(err, err.stack);
					showError('Error:  ' + err.message + ' (see console for details)');
				}

				if (data) {

					// capture the sessionAttributes for the next cycle
					sessionAttributes = data.sessionAttributes;
					// show response and/or error/dialog status
					show_L_Response(data, wisdom);
					console.log(data);
				}

				// re-enable input
				wisdomText.value = '';
				wisdomText.locked = false;
			});
		}
	}
	// we always cancel form submission
	return false;
}

async function IDcheck_Registration(portalcode, orgid, email) {
	try {
			const result = await customer_identification (portalcode, orgid, email)
			console.log(result.apikey);
			if (!result.result) {
				validUser = true;
				kamaAPI.user = result.id;
				kamaAPI.key = result.apikey;
			}
			else {
				const result = await customer_registration (portalcode, orgid, email)
				if (!result.result) {
					validUser = true;
					kamaAPI.user = result.id;
					kamaAPI.key = result.apikey;
				}
				else console.log('registration error' + 'email: ' + email);
			}

	} catch (error) {
		console.error('ERROR:');
		console.error(error);
	}
}

function customer_registration (portalcode, orgid, email) {
	console.log('Registration called!!!');
	console.log(email);

	return new Promise((resolve, reject) => {
		$.ajax({
			url: REG_URL,
			data: {
				'portalcode': portalcode,
				'orgid': orgid,
				'email': email
			},
			type: 'POST',
			success: function (data) {
				resolve (data)
			},
			error: function (error, response) {
				if (error) reject(error);
				if (response.statusCode != 200) {
					reject('Invalid status code <' + response.statusCode + '>');				
				}

			}, 
		});
	});
}

function customer_identification (portalcode, orgid, email) {
	console.log('IDcheck called!!!');
	console.log(portalcode);
	return new Promise((resolve, reject) => {
		$.ajax({
			url: ID_URL,
			data: {
				'portalcode': portalcode,
				'orgid': orgid,
				'email': email
			},
			type: 'POST',
			success: function (data) {
				resolve (data)
			},
			error: function (error, response) {
				if (error) reject(error);
				if (response.statusCode != 200) {
					reject('Invalid status code <' + response.statusCode + '>');				
				}

			}, 
		});
	});
}

function buttonFunc(elem, payload, kamaResponse) {

	console.log(elem.innerHTML);
	var wisdomText = document.getElementById('wisdom');
	console.log(wisdomText);
	$('.button').attr("disabled", true);


	if (elem.innerHTML === 'book a car' | 
		elem.innerHTML === 'go to a landmark' | 
		elem.innerHTML === 'rebook a trip' |
		elem.innerHTML === 'cancel a trip' ) {

		wisdomText.value = 'I want to ' + elem.innerHTML;
	}

	else {

		if (payload) {// button has a payload

			console.log('payload: ' + payload);
			//wisdomText.value =  payload;
			if (	kamaResponse.response.state !== 804 ||
					kamaResponse.response.state !== 805 ||
					kamaResponse.response.state !== 998 ||
					kamaResponse.response.state !== 999	)	{

				kamadei(kamaResponse.response.state, payload, kamaResponse.response.botState, kamaResponse.response.intentName, kamaResponse.response.slotToElicit, kamaResponse.response.language);

			}
		}

		else {// unknow button handelling

			wisdomText.value = 'Unknow action ' + ' ' + elem.innerHTML;
		}
	}
	//wisdomText.value = elem.innerHTML;
	pushChat(validUser,'kama');
}


function show_L_Response(lexResponse, daText) {

	var state = 0;// dummy variable now
/******************************************************************************************/
	var language;// default language
	// if (daText.includes('/')) {
	// 	language = daText.substr(daText.indexOf('/')+1);
	// 	daText = daText.substr(0, daText.indexOf('/'));
	// }
	// console.log(daText);
	// console.log(language);
/******************************************************************************************/

	var d = new Date();
	console.log('Lex response= ' + d.getTime());

	var conversationDiv = document.getElementById('conversation');
	var responsePara = document.createElement("P");
	responsePara.className = 'lexResponse';

	var wisdomText = document.getElementById('wisdom');
	//console.log(wisdomText);


	if (lexResponse.message) {
			responsePara.appendChild(document.createTextNode(lexResponse.message));
			responsePara.appendChild(document.createElement('br'));
		}

	if (lexResponse.dialogState === 'ElicitIntent') {
		if (debugmode) {
			responsePara.appendChild(document.createTextNode('(' + lexResponse.dialogState + ')'));
			responsePara.appendChild(document.createElement('br'));
			responsePara.appendChild(document.createElement('br'));
		}


		if (lexResponse.message === 'Sorry, what can I help you with?') {
			if (debugmode) {
				responsePara.appendChild(document.createTextNode('Lex fail extracting intent. Kama-DEI kicks in'));
				responsePara.appendChild(document.createElement('br'));
			}
			kamadei(state, daText, lexResponse.dialogState, lexResponse.intentName, lexResponse.slotToElicit, language);
		}
	}

	else if (lexResponse.dialogState === 'ElicitSlot') {
		if (debugmode) {
			responsePara.appendChild(document.createTextNode('(' + lexResponse.dialogState + ': '
			+ lexResponse.slotToElicit + ')'));
		}
	}

	else {
		
		if (lexResponse.dialogState === 'ReadyForFulfillment') {

			responsePara.appendChild(document.createTextNode(lexResponse.intentName));
			responsePara.appendChild(document.createElement('br'));
			responsePara.appendChild(document.createTextNode(JSON.stringify(lexResponse.slots)));
			responsePara.appendChild(document.createElement('br'));
		}

		else if (lexResponse.dialogState === 'Fulfilled') {

			responsePara.appendChild(document.createElement('br'));
			responsePara.appendChild(document.createTextNode('Intent "' + lexResponse.intentName + '" has been fulfilled. Please ask another question.'));
			responsePara.appendChild(document.createElement('br'));			
		}

		else if (lexResponse.dialogState === 'Failed') {
			if (debugmode) {
				responsePara.appendChild(document.createTextNode('Lex Failed, reset eliciting process. Kama-DEI kicks in'));
				responsePara.appendChild(document.createElement('br'));
				responsePara.appendChild(document.createElement('br'));
			}
			console.log(lexResponse.dialogState);
			console.log(JSON.stringify(lexResponse));
			console.log(lexResponse.intentName);
			console.log(JSON.stringify(lexResponse.slots));


			kamadei(state, daText, lexResponse.dialogState, lexResponse.intentName, lexResponse.slotToElicit, language);
			// tested, fail dalogState can still responde intent, slots and session sessionAttributes, in ver_2,
			//the FrontEnd should be able to send these info to Kama-DEI to carry on and do NLP, then return updated info, 
			//then this JSON file will be send to Lex.
		}

		else if (lexResponse.dialogState === 'SYS_MESSAGE') {
			responsePara.appendChild(document.createTextNode(daText));
			responsePara.appendChild(document.createElement('br'));
		}

		else
			console.log('Unknow Lex response state');

		if (debugmode) {
			responsePara.appendChild(document.createTextNode('(' + lexResponse.dialogState + ')'));
			responsePara.appendChild(document.createElement('br'));
		}
	}

	if (!debugmode && (lexResponse.message === 'Sorry, what can I help you with?' || lexResponse.message === 'Sorry, I am not able to assist at this time')) {}
	else
	conversationDiv.appendChild(responsePara);

	conversationDiv.scrollTop = conversationDiv.scrollHeight;
}

function show_K_Response (kamaResponse, daText) {
	
	// used for performance evaluation
	var d = new Date();
	console.log('Kama-DEI response= ' + d.getTime());
	// console.log(kamaResponse);
	if (kamaResponse.chat_controller_output) 
		kamaResponse = kamaResponse.chat_controller_output;

	// when Kama-DEI response mapping info, will generate direct instruction to Lex
	if (kamaResponse.response.state === 804 || kamaResponse.response.state === 805) {

		console.log('mapping')
		var wisdomText = document.getElementById('wisdom');

		if (kamaResponse.response.botState === 'ElicitIntent' && kamaResponse.response.mapping.intent) {
			console.log("intent found")

			// if (kamaResponse.response.mapping.slots) {

			// 	for (var i = 0; i < kamaResponse.response.mapping.slots.length; i++) {

			// 		if (JSON.parse(kamaResponse.response.mapping).slots[i].name) {
			// 			wisdomText.value = wisdomText.value + kamaResponse.response.mapping.slots[i].value;
			// 		}
			// 	}
			// }

			console.log(wisdomText.value);
			console.log('mapping used for ElicitIntent ' + kamaResponse.response.mapping.intent);

			var sampleUtterance;
			if (kamaResponse.response.mapping.intent === 'rbcTermQuote')
				sampleUtterance = kamaResponse.response.mapping.sampleUtterance;

			else {
				console.log("Unknow Intent!");
			}

			wisdomText.value = sampleUtterance;
			pushChat(validUser, 'kama');
		}

		else if (kamaResponse.response.botState === 'ElicitSlot' && kamaResponse.response.mapping.slots) {

			console.log('mapping used for ElicitSlot, slotName ' + kamaResponse.response.slotName + ', intentName ' + kamaResponse.response.mapping.intent);

			var slotMatch = 0;

			for (var i = 0; i < kamaResponse.response.mapping.slots.length; i++) {

				if (JSON.parse(kamaResponse.response.mapping).slots[i].name === kamaResponse.response.slotName) {// if mapping slot matches the slot eliciting
					wisdomText.value = JSON.parse(kamaResponse.response.mapping).slots[i].value;
					pushChat(validUser, 'kama');
					slotMatch = 1;
				}
			}						

			if (slotMatch !== 1) {// if mapping slot does not match slot eliciting

			console.log('dataMatch');
			var conversationDiv = document.getElementById('conversation');
			var responsePara = document.createElement("P");
			responsePara.className = 'kamaResponse';
			responsePara.appendChild(document.createTextNode('Please input ' + kamaResponse.response.slotName + ' you want.'));

			conversationDiv.appendChild(responsePara);
			conversationDiv.scrollTop = conversationDiv.scrollHeight;
			}				 
		}

		else if (kamaResponse.response.botState === 'Failed') {

			console.log('Lex failed, session expired');
			var sampleUtterance;
			if (kamaResponse.response.mapping.intent === 'rbcTermQuote')
				sampleUtterance = kamaResponse.response.mapping.sampleUtterance;

			else {
				console.log("Unknow Intent!");
			}

			wisdomText.value = sampleUtterance;
			pushChat(validUser, 'kama');
		}

		else {
			console.log('Mapping data error');
		}
	}

	// when Kama-DEI does not response mapping info, will return solution based on Kama-DEI knowledge base
	else {

		console.log('no mapping');
		console.log(kamaResponse.response);
		//var conversationDiv = document.getElementById('conversation');
		//var responsePara = document.createElement("P");
		//responsePara.className = 'kamaResponse';

		// when returned address can be used
		if (kamaResponse.response.state === 832) { //&& kamaResponse.response.answers[1].text.trim().match(/^\d/)) ||
			//(kamaResponse.response.state === 832 && kamaResponse.response.answers[0].text.trim().match(/^\d/)) ){

			//var index;

			for (var i = 0; i < kamaResponse.response.answers.length; i++) {

				if (kamaResponse.response.answers[i].attname && kamaResponse.response.answers[i].attname !== "Street address") {
					let conversationDiv = document.getElementById('conversation');
					let responsePara = document.createElement("P");
					responsePara.className = 'kamaResponse';

					responsePara.appendChild(document.createTextNode(kamaResponse.response.answers[i].text));
					responsePara.appendChild(document.createElement('br'));
					responsePara.appendChild(document.createElement('br'));

					conversationDiv.appendChild(responsePara);
					conversationDiv.scrollTop = conversationDiv.scrollHeight;

				}

				else if (kamaResponse.response.answers[i].attname && kamaResponse.response.answers[i].attname === "Street address") {

					//if (kamaResponse.response.state === 830) index = 0; else index = 1;
					console.log('address used');
					var wisdomText = document.getElementById('wisdom');
					console.log(wisdomText);
					var addr = kamaResponse.response.answers[i].text;
					console.log(addr.split(','));

					wisdomText.value = "go to " + addr.split(',')[0] + " and" + addr.split(',')[1];
					//wisdomText.value = "need a car to" + addr.split(',')[0];
					pushChat(validUser, 'kama');
				}
			}
		}

		// when returned address can not be used
		else {

			console.log('address not used');
			let conversationDiv = document.getElementById('conversation');
			let responsePara = document.createElement("P");
			responsePara.className = 'kamaResponse';

			if (kamaResponse.response.message) {

				responsePara.appendChild(document.createTextNode(kamaResponse.response.message));
				responsePara.appendChild(document.createElement('br'));
			}

			if (kamaResponse.response.state === 802 | kamaResponse.response.state === 813 | kamaResponse.response.state === 814 | 
				kamaResponse.response.state === 842 | kamaResponse.response.state === 806 | kamaResponse.response.state === 810 |
				kamaResponse.response.state === 830 | kamaResponse.response.state === 991 | kamaResponse.response.state === 822 |
				kamaResponse.response.state === 841) {

				var btn = [];
				btn.length = kamaResponse.response.answers.length;

				for (var i = 0; i < kamaResponse.response.answers.length; i++) {

					if (kamaResponse.response.answers[i].url) {

						btn[i] = document.createElement('button');
						btn[i].className = 'button';
						btn[i].innerHTML = 'Resource Link: Please Click Me';
						btn[i].value = kamaResponse.response.answers[i].url;
						btn[i].onclick = function() {window.open(this.value, 'popup','width=600,height=600');};
					}

					else {

						btn[i] = document.createElement('button');
						btn[i].className = 'button';
						btn[i].innerHTML = kamaResponse.response.answers[i].text;

						// if (kamaResponse.response.language) {
						// 	console.log(JSON.stringify(kamaResponse.response.language));
						// 	btn[i].value = kamaResponse.response.answers[i].value + '/' + JSON.stringify(kamaResponse.response.language);
						// }
						// else {
						// 	console.log('no language code')
							btn[i].value = kamaResponse.response.answers[i].value
						//}

						console.log(i);
						console.log (btn[i].innerHTML);
						console.log(kamaResponse.response.answers[i].value);
						console.log (btn[i].value);

						console.log(this);
						btn[i].onclick = function(){buttonFunc(this, this.value, kamaResponse);}
					}
				}

				for (var i = 0; i < kamaResponse.response.answers.length; i++) {
					if (btn[i].innerHTML !== '') {
						responsePara.appendChild(btn[i]);
						responsePara.appendChild(document.createElement('br'));
					}
				}

			}

			// else if (kamaResponse.response.state === 841) {

			// 	for (var i = 0; i < kamaResponse.response.answers.length; i++) {

			// 		if (kamaResponse.response.answers[i].url) {

			// 			//console.log('url found '+ kamaResponse.response.answers[i].url)
			// 			btn = document.createElement('button');
			// 			btn.innerHTML = 'Resource Link: Please Click Me';
			// 			btn.value = kamaResponse.response.answers[i].url

			// 			btn.onclick = function() {window.open(this.value, 'popup','width=600,height=600');};
			// 			responsePara.appendChild(btn);
			// 			responsePara.appendChild(document.createElement('br'));
			// 		}

			// 		else {

			// 			responsePara.appendChild(document.createTextNode(kamaResponse.response.answers[i].text));
			// 			responsePara.appendChild(document.createElement('br'));
			// 		}
			// 	}
			// }

			else if (kamaResponse.response.state === 300) {
					// responsePara.appendChild(document.createTextNode('slidebar type response is not supported yet.'));
					// responsePara.appendChild(document.createElement('br'));
					var a = new Date();
					var n = a.getTime();

					let slGroup = document.createElement('div');
					let rd = '';
					for (var i = 0; i < kamaResponse.response.slidebar.length; i++) {
						rd += '<p>' + kamaResponse.response.slidebar[i].name + ': <span id="out ' + n + kamaResponse.response.slidebar[i].name + '"></span></p><input class="slider" type="range" min="1" max="10" value="5" id="in ' + n + kamaResponse.response.slidebar[i].name + '">'
					}
					rd += '<br>';
					if(kamaResponse.response.buttons && kamaResponse.response.buttons.length){
						console.log("buttons triggered");
						for (var i = 0; i < kamaResponse.response.buttons.length; i++) {
							rd +='<button class= "slider_button" style="float: none; margin-left: 25%;" value=' + kamaResponse.response.buttons[i].value + '>' + kamaResponse.response.buttons[i].text + '</button><br>';
						}
					}

					slGroup.innerHTML = rd;
					responsePara.appendChild(slGroup);
			}


			else if (kamaResponse.response.state === 998 | kamaResponse.response.state === 999 | kamaResponse.response.state === 818) {

				//responsePara.appendChild(document.createTextNode('go back to Lex'));
			}

			else {

				responsePara.appendChild(document.createTextNode('Unknow Kama-DEI response state. Please try to phrase like this: "I want ..."'));
			}

			conversationDiv.appendChild(responsePara);
			conversationDiv.scrollTop = conversationDiv.scrollHeight;

// This is an extra work to define the slide bar onchange functions
if (kamaResponse.response.state === 300) {
					let slider = [];
					let output = [];
					let arr_answers = [];
					for (var i = 0; i < kamaResponse.response.slidebar.length; i++) {
						let slider_id = "in " + n + kamaResponse.response.slidebar[i].name;
						let output_id = "out " + n + kamaResponse.response.slidebar[i].name;
						slider.push(document.getElementById(slider_id));
						output.push(document.getElementById(output_id));

						output[i].innerHTML = slider[i].value;
						let a = i;// this is to locate the current index i value and create onchange function
						slider[i].onchange = function() {
							// console.log(slider[a].value);
							output[a].innerHTML = this.value;
						}
					}

					console.log(slider);

					$(".slider_button").on("click", function() {

						for(var i = 0; i < slider.length; i++) {
							let answer;
							answer = {
							text: kamaResponse.response.slidebar[i].name,
							nameId: kamaResponse.response.slidebar[i].nameId,
							value: Number(output[i].innerHTML)
							}
							arr_answers.push(answer);
						}
						// arr_answers_str = '[{"text":"investment","value":9},{"text":"peace","value":1},{"text":"family","value":1},{"text":"legacy","value":9}]';
						arr_answers_str = JSON.stringify(arr_answers);

						slider = [];
						output= [];
						arr_answers = [];

						console.log("slider request: " + '{"request":{"type":"text","message":"' + this.value + '", "answers":' + arr_answers_str + '}}');
							myajax(kamaAPI.key, CHAT_URL, 
								{
								userid: kamaAPI.user,
								orgid: kamaAPI.org,
								state: 300,
								botName: "rbcQuote",
								botVersion: "rbcQuoteVersion1",
								botAlias: "rbcQuoteAlias",
								botState: "ElicitIntent",
								// intentName: intentName,
								// slotName: slotName,
								// language: language,
								inquiry: '{"request":{"type":"text","message":"' + this.value + '", "answers":' + arr_answers_str + '}}',
								utterance_3PB: this.value,
								utterance_orig: ''
								},
							this.value);

						console.log(arr_answers_str);
						console.log(this.value);
					})


}
		}
	}
}

function showRequest(daText, src, debugmode) {

	var conversationDiv = document.getElementById('conversation');
	var requestPara = document.createElement("P");

	if (debugmode) {
		if (src === 'user')
			requestPara.className = 'userRequest';
		else if (src === 'kama')
			requestPara.className = 'kamaRequest';

		requestPara.appendChild(document.createTextNode(daText));
		conversationDiv.appendChild(requestPara);
		conversationDiv.scrollTop = conversationDiv.scrollHeight;
	}
	else {
		if (src === 'user') {
			requestPara.className = 'userRequest';	
			requestPara.appendChild(document.createTextNode(daText));
			conversationDiv.appendChild(requestPara);
			conversationDiv.scrollTop = conversationDiv.scrollHeight;
		}
	}
}

function showError(daText) {

	var conversationDiv = document.getElementById('conversation');
	var errorPara = document.createElement("P");
	errorPara.className = 'lexError';
	errorPara.appendChild(document.createTextNode(daText));
	conversationDiv.appendChild(errorPara);
	conversationDiv.scrollTop = conversationDiv.scrollHeight;
}

function kamadei(state, daText, botState, intentName, slotName,language) {
	console.log("botState = " + botState );

	console.log(daText);
	console.log("{\"request\":{\"type\":\"text\",\"message\":\"" + daText +"\"\,\"answers\":[]}}");

	// call Kama-DEI chatbox
	myajax(kamaAPI.key, CHAT_URL, 
		{
		userid: kamaAPI.user,
		orgid: kamaAPI.org,
		state: state,
		botName: "rbcQuotebot",
		botVersion: "rbcQuoteVersion1",
		botAlias: "rbcQuotebotAlias",
		botState: botState,
		intentName: intentName,
		slotName: slotName,
		language: language,
		inquiry: "{\"request\":{\"type\":\"text\",\"message\":\"" + daText + "\"\,\"answers\":[]}}",
		utterance_3PB: daText,
		utterance_orig: ''
		},
	daText);
}


function myajax(apikey, url, Kparams, daText) {
	console.log(Kparams)

	$.ajax({
		url: url,
		headers: {
		'apikey': apikey
			},
		data: Kparams,
		type: 'POST',
		async: true,
		error: (e) => {
			console.dir(e);
		},
		success: function(data){
			console.log(data);
			show_K_Response(data, daText);
		}
	});
}

function wait(ms) {
	var start = new Date().getTime();
	var end = start;
	while(end < start + ms) {
		end = new Date().getTime();			
	}
}

function validateEmail(email) {
  const re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  return re.test(email);
}

