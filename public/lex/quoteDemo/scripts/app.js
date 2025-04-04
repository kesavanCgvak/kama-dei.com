// Edit by Kaiyang - Kamazooie Development Corporation

function pushChat(src = 'lex') {

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
			botName: 'CPPquote',
			inputText: wisdom,
			userId: lexUserId,
			sessionAttributes: sessionAttributes
		};
		showRequest(wisdom, src);

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
				show_L_Respo(data, wisdom);
				console.log(data);
			}

			// re-enable input
			wisdomText.value = '';
			wisdomText.locked = false;
		});
	}
	// we always cancel form submission
	return false;
}


function buttonFunc(elem, payload) {

	console.log(elem.innerHTML);
	var wisdomText = document.getElementById('wisdom');
	console.log(wisdomText);

	if (elem.innerHTML === 'book a car' | 
		elem.innerHTML === 'go to a landmark' | 
		elem.innerHTML === 'rebook a trip' |
		elem.innerHTML === 'cancel a trip' ) {

		wisdomText.value = 'I want to ' + elem.innerHTML;
	}
	
/*	else if (elem.innerHTML.length === 12 && !payload) {// takes order number

		console.log('number')
		wisdomText.value = elem.innerHTML;
		wisdomText.placeholder = '...';// to fix the repeat sending sample utterance bug
	}
*/
	else {

		if (payload) {// button has a payload

			console.log('payload: ' + payload);
			wisdomText.value =  payload;
		}

		else {// unknow button handelling

			wisdomText.value = 'Unknow action ' + ' ' + elem.innerHTML;
		}
	}
	//wisdomText.value = elem.innerHTML;
	pushChat('kama');
}

function show_L_Respo(lexResponse, daText) {

	var state = 0;// dummy variable now
/******************************************************************************************/
	var language;// default language
	if (daText.includes('/')) {
		language = daText.substr(daText.indexOf('/')+1);
		daText = daText.substr(0, daText.indexOf('/'));
	}
	console.log(daText);
	console.log(language);
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

		responsePara.appendChild(document.createTextNode('(' + lexResponse.dialogState + ')'));
		responsePara.appendChild(document.createElement('br'));
		responsePara.appendChild(document.createElement('br'));


		if (lexResponse.message === 'Sorry, what can I help you with?') {
			responsePara.appendChild(document.createTextNode('Lex fail extracting intent. Kama-DEI kicks in'));
			responsePara.appendChild(document.createElement('br'));
			responsePara.appendChild(document.createElement('br'));
			kamadei(state, daText, lexResponse.dialogState, lexResponse.intentName, lexResponse.slotToElicit, language);
		}
	}

	else if (lexResponse.dialogState === 'ElicitSlot') {

		if (lexResponse.message === 'Please select the trip you want to rebook.' || lexResponse.message === 'Please select the trip you want to cancel.') {

			if (lexResponse.intentName === 'CancelTrip') {
				orderHistory('cancel');
			}
			else if (lexResponse.intentName === 'RebookTrip') {
				orderHistory('rebook');
			}
			else
				console.log('ERROR: Unknow intent name.')
			}

		responsePara.appendChild(document.createTextNode('(' + lexResponse.dialogState + ': '
			+ lexResponse.slotToElicit + ')'));
	}

	else {
		
		if (lexResponse.dialogState === 'ReadyForFulfillment') {

			responsePara.appendChild(document.createTextNode(lexResponse.intentName));
			responsePara.appendChild(document.createElement('br'));
			responsePara.appendChild(document.createTextNode(JSON.stringify(lexResponse.slots)));
			responsePara.appendChild(document.createElement('br'));
		} 

		else if (lexResponse.dialogState === 'Failed') {

			responsePara.appendChild(document.createTextNode('Lex Failed, reset eliciting process. Kama-DEI kicks in'));
			responsePara.appendChild(document.createElement('br'));
			responsePara.appendChild(document.createElement('br'));

			console.log(lexResponse.dialogState);
			console.log(JSON.stringify(lexResponse));
			console.log(lexResponse.intentName);
			console.log(JSON.stringify(lexResponse.slots));


			kamadei(state, daText, lexResponse.dialogState, lexResponse.intentName, lexResponse.slotToElicit, language);
			// tested, fail dalogState can still responde intent, slots and session sessionAttributes, in ver_2,
			//the FrontEnd should be able to send these info to Kama-DEI to carry on and do NLP, then return updated info, 
			//then this JSON file will be send to Lex.
		}

		else 
			console.log('Unknow Lex response state');

			responsePara.appendChild(document.createTextNode('(' + lexResponse.dialogState + ')'));
			responsePara.appendChild(document.createElement('br'));
	}

	conversationDiv.appendChild(responsePara);
	conversationDiv.scrollTop = conversationDiv.scrollHeight;
}

function show_K_Respo (kamaResponse, daText) {
	
	// used for performance evaluation
	var d = new Date();
	console.log('Kama-DEI response= ' + d.getTime());
	console.log(kamaResponse.response);

	// when Kama-DEI response mapping info, will generate direct instruction to Lex
	if (kamaResponse.response.state === 804) {

		console.log('mapping')
		var wisdomText = document.getElementById('wisdom');

		if (kamaResponse.response.lexState === 'ElicitIntent' || JSON.parse(kamaResponse.response.mapping).intent) {

			if (JSON.parse(kamaResponse.response.mapping).slots) {

				for (var i = 0; i < JSON.parse(kamaResponse.response.mapping).slots.length; i++) {

					if (JSON.parse(kamaResponse.response.mapping).slots[i].name) {
						wisdomText.value = wisdomText.value + JSON.parse(kamaResponse.response.mapping).slots[i].value;
					}
				}
			}

			console.log(wisdomText.value);
			console.log('mapping used for ElicitIntent ' + JSON.parse(kamaResponse.response.mapping).intent);

			var intent;
			if (JSON.parse(kamaResponse.response.mapping).intent === 'GetQuote')
				intent = 'get quote';

			wisdomText.value = 'I want to ' + intent + ' ' + wisdomText.value;
			pushChat('kama');
		}

		else if (kamaResponse.response.lexState === 'ElicitSlot' && JSON.parse(kamaResponse.response.mapping).slots) {

			console.log('mapping used for ElicitSlot, slotName ' + kamaResponse.response.slotName + ', intentName ' + JSON.parse(kamaResponse.response.mapping).intent);

			var slotMatch = 0;

			for (var i = 0; i < JSON.parse(kamaResponse.response.mapping).slots.length; i++) {

				if (JSON.parse(kamaResponse.response.mapping).slots[i].name === kamaResponse.response.slotName) {// if mapping slot matches the slot eliciting
					wisdomText.value = JSON.parse(kamaResponse.response.mapping).slots[i].value;
					pushChat('kama');
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

		else if (kamaResponse.response.lexState === 'Failed') {

			console.log('Lex failed, session expired');
		}

		else {
			console.log('Mapping data error');
		}
	}

	// when Kama-DEI does not response mapping info, will return solution based on Kama-DEI knowledge base
	else {

		console.log('no mapping');
		console.log(kamaResponse.response);
		var conversationDiv = document.getElementById('conversation');
		var responsePara = document.createElement("P");
		responsePara.className = 'kamaResponse';

			console.log('address not used');
			var conversationDiv = document.getElementById('conversation');
			var responsePara = document.createElement("P");
			responsePara.className = 'kamaResponse';

			if (kamaResponse.response.message) {

				responsePara.appendChild(document.createTextNode(kamaResponse.response.message));
				responsePara.appendChild(document.createElement('br'));
				responsePara.appendChild(document.createElement('br'));
			}

			if (kamaResponse.response.state === 802 | kamaResponse.response.state === 813 | kamaResponse.response.state === 814 | kamaResponse.response.state === 842 | 
				kamaResponse.response.state === 806 | kamaResponse.response.state === 810 | kamaResponse.response.state === 830 |
				kamaResponse.response.state === 991 | kamaResponse.response.state === 822) {

				var btn = [];
				btn.length = kamaResponse.response.answers.length;

				for (var i = 0; i < kamaResponse.response.answers.length; i++) {

					if (kamaResponse.response.answers[i].url) {

						btn[i] = document.createElement('button');
						btn[i].innerHTML = 'Click Me';
						btn[i].value = kamaResponse.response.answers[i].url;
						btn[i].onclick = function() {window.open(this.value, 'popup','width=600,height=600');};
					}

					else {

						btn[i] = document.createElement('button');
						btn[i].innerHTML = kamaResponse.response.answers[i].text;

						if (kamaResponse.response.language) {
							console.log(JSON.stringify(kamaResponse.response.language));
							btn[i].value = kamaResponse.response.answers[i].value + '/' + JSON.stringify(kamaResponse.response.language);
						}
						else {
							console.log('no language code')
							btn[i].value = kamaResponse.response.answers[i].value
						}

						console.log(i);
						console.log (btn[i].innerHTML);
						console.log(kamaResponse.response.answers[i].value);
						console.log (btn[i].value);

						btn[i].onclick = function(){buttonFunc(this, this.value);}
					}
				}

				for (var i = 0; i < kamaResponse.response.answers.length; i++) {
					responsePara.appendChild(btn[i]);
					responsePara.appendChild(document.createElement('br'));
				}

			}

			else if (kamaResponse.response.state === 841) {

				for (var i = 0; i < kamaResponse.response.answers.length; i++) {

					if (kamaResponse.response.answers[i].url) {

						//console.log('url found '+ kamaResponse.response.answers[i].url)
						btn = document.createElement('button');
						btn.innerHTML = 'Click Me';
						btn.value = kamaResponse.response.answers[i].url

						btn.onclick = function() {window.open(this.value, 'popup','width=600,height=600');};
						responsePara.appendChild(btn);
						responsePara.appendChild(document.createElement('br'));
					}

					else {

						responsePara.appendChild(document.createTextNode(kamaResponse.response.answers[i].text));
						responsePara.appendChild(document.createElement('br'));
					}
				}
			}

			else if (kamaResponse.response.state === 998 | kamaResponse.response.state === 999 | kamaResponse.response.state === 818) {

				//responsePara.appendChild(document.createTextNode('go back to Lex'));
			}

			else {

				responsePara.appendChild(document.createTextNode('Unknow Kama-DEI response state. Please try to phrase like this: "I want ..."'));
			}

			conversationDiv.appendChild(responsePara);
			conversationDiv.scrollTop = conversationDiv.scrollHeight;
		}
}

function showRequest(daText, src) {

	var conversationDiv = document.getElementById('conversation');
	var requestPara = document.createElement("P");

	if (src === 'lex')
		requestPara.className = 'userRequest';
	else if (src === 'kama')
		requestPara.className = 'kamaRequest';

	requestPara.appendChild(document.createTextNode(daText));
	conversationDiv.appendChild(requestPara);
	conversationDiv.scrollTop = conversationDiv.scrollHeight;
}

function showError(daText) {

	var conversationDiv = document.getElementById('conversation');
	var errorPara = document.createElement("P");
	errorPara.className = 'lexError';
	errorPara.appendChild(document.createTextNode(daText));
	conversationDiv.appendChild(errorPara);
	conversationDiv.scrollTop = conversationDiv.scrollHeight;
}

function kamadei(state, daText, lexState, intentName, slotName,language) {

	console.log(daText);
	console.log("{\"request\":{\"type\":\"text\",\"message\":\"" + daText +"\"\,\"answers\":[]}}");

	//CHAT_URL = "https://staging_py.kama-dei.com/python_api/v1/that_clause"; // on staging server
	CHAT_URL = "https://staging_py.kama-dei.com/python_api/v1/multiple_language" // multilanguage on staging server

	lex_apikey = "2LEX!!ffb520bea61bcf21722b1afe96621bb8"// use API portal 2 

	// call Kama-DEI chatbox
	myajax(lex_apikey, CHAT_URL, 
		{
		userid: "413",
		orgid: "19",
		state: state,
		botName: "BookTrip",
		botVersion: "6",
		botAlias: "BookTripAlias",
		lexState: lexState,
		intentName: intentName,
		slotName: slotName,
		language: language,
		inquiry: "{\"request\":{\"type\":\"text\",\"message\":\"" + daText + "\"\,\"answers\":[]}}"
		},
	daText);
}


function myajax(apikey, url, Kparams, daText) {
	//console.log('Kparams');
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
			show_K_Respo(data, daText);
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