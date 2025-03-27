import { showError, showSuccess, showConfirm, showAlert } from '../DataTable'
export default class LEXBase {
	//----------------------------------------------------
	constructor(flag=true){
		this.AES      = require("crypto-js/aes");
		this.SHA256   = require("crypto-js/sha256");
		this.CryptoJS = require("crypto-js");

		this.AccessKey = "";
		this.SecretKey = "";

		if(flag){ this.getKeys(); }
	}
	//----------------------------------------------------
	getKeys(){
		var lexClass = this;
		$.ajax({
			url: apiURL+"/api/dashboard/lex/setting/getkey",
			type: 'put',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			data: {},
			beforeSend: function(){ },
			success: function(retVal){
				lexClass.AccessKey = retVal.keys[2];
				lexClass.SecretKey = retVal.keys[4];
			},
			error: function(e){ showError('Server error'); }
		});
		
	}
	//----------------------------------------------------
	setCallTime(){
		var n = Date.now();
		var d = new Date();
		var t = (d.getTimezoneOffset()/60)-(0);
		d.setTime(n+(t*3600000));
		var Y = d.getFullYear().toString();
		var M = d.getMonth()+1;
		if(M<10){ M = "0"+M.toString(); }
		else{ M = M.toString(); }
		var D = d.getDate();
		if(D<10){ D = "0"+D.toString(); }
		else{ D = D.toString(); }

		var H = d.getHours();
		if(H<10){ H = "0"+H.toString(); }
		else{ H = H.toString(); }
		var I = d.getMinutes();
		if(I<10){ I = "0"+I.toString(); }
		else{ I = I.toString(); }
		var S = d.getSeconds();
		if(S<10){ S = "0"+S.toString(); }
		else{ S = S.toString(); }

		return [Y+M+D, Y+M+D+"T"+H+I+S+"Z"];
	}
	//----------------------------------------------------
	getSignatureKey(key, dateStamp, regionName, serviceName) {
		var kDate = this.CryptoJS.HmacSHA256(dateStamp, "AWS4"+key);
		var kRegion = this.CryptoJS.HmacSHA256(regionName, kDate);
		var kService = this.CryptoJS.HmacSHA256(serviceName, kRegion);
		var kSigning = this.CryptoJS.HmacSHA256("aws4_request", kService);
		
		return kSigning;
	}
	//------------------------------------------------------------
	callAjax(settings, calBack, stackData=null, callBackFail=null){
		$.ajax(settings)
			.done(function (response){ calBack(response, stackData); })
			.fail(function(xhr){ 
				if( callBackFail==null ){ showError(xhr.responseJSON.message); }
				else{ callBackFail(xhr); }
			});
	}
	//------------------------------------------------------------
	//**********************************************************//
	//------------------------------------------------------------
	callBot(botName, botAlias, stackData=null, calBack, callBackFail=null){
		var dates = this.setCallTime();
		var date1 = dates[0];
		var date2 = dates[1];
		var CanonicalURI = "/bots/"+botName+"/versions/"+botAlias;

		var url = "https://models.lex.us-east-1.amazonaws.com"+CanonicalURI;
		var host = "models.lex.us-east-1.amazonaws.com";
		var contentType	= "application/json";

		var HTTPRequestMethod = "GET";
		var CanonicalQueryString = "";
		var requestPayload = "";
		var xamzcontentsha256 = this.SHA256(requestPayload).toString();
		var CanonicalHeaders = 
			"content-type:"+contentType+"\n"+
			"host:"+host+"\n"+
			"x-amz-date:"+date2+"\n";
		var SignedHeaders = "content-type;host;x-amz-date";
		var CanonicalRequest =
			HTTPRequestMethod + '\n' +
			CanonicalURI + '\n' +
			CanonicalQueryString + '\n' +
			CanonicalHeaders + '\n' +
			SignedHeaders + '\n' +
			this.SHA256(requestPayload).toString();
		var HashedCanonicalRequest = this.SHA256(CanonicalRequest).toString();

		var Algorithm = "AWS4-HMAC-SHA256";
		var RequestDateTime = date2;
		var CredentialScope = date1+"/us-east-1/lex/aws4_request";
		var StringToSign =
			Algorithm + "\n" +
			RequestDateTime + "\n" +
			CredentialScope + "\n" +
			HashedCanonicalRequest;

		var SignatureKey = this.getSignatureKey(this.SecretKey, date1, 'us-east-1', 'lex');
		var signature    = this.CryptoJS.HmacSHA256(StringToSign, SignatureKey);

		var Authorization = Algorithm+" Credential="+this.AccessKey+"/"+CredentialScope+", SignedHeaders="+SignedHeaders+
							", Signature="+signature.toString();

		var settings = {
			async: true,
			crossDomain	: true,
			"url": url,
			"method": HTTPRequestMethod,
			"headers": {
//				"Host": host,
				"Accept": "*/*",
				"Authorization": Authorization,
				"cache-control": "no-cache",
//				"Connection": "keep-alive",
				"Content-Type": contentType,
				"X-Amz-Date": date2,
//				"accept-encoding": "gzip, deflate",
				"Cache-Control": "no-cache"
			},
			"processData": false,
			"data": ""
		}
		this.callAjax(settings, calBack, stackData, callBackFail);
	}
	//------------------------------------------------------------
	callIntent(intentName, intentVersion, stackData=null, calBack){
		var dates = this.setCallTime();
		var date1 = dates[0];
		var date2 = dates[1];
		var CanonicalURI = "/intents/"+intentName+"/versions/"+intentVersion;

		var url = "https://models.lex.us-east-1.amazonaws.com"+CanonicalURI;
		var host = "models.lex.us-east-1.amazonaws.com";
		var contentType	= "application/json";

		var HTTPRequestMethod = "GET";
		var CanonicalQueryString = "";
		var requestPayload = "";
		var xamzcontentsha256 = this.SHA256(requestPayload).toString();
		var CanonicalHeaders = 
			"content-type:"+contentType+"\n"+
			"host:"+host+"\n"+
			"x-amz-date:"+date2+"\n";
		var SignedHeaders = "content-type;host;x-amz-date";
		var CanonicalRequest =
			HTTPRequestMethod + '\n' +
			CanonicalURI + '\n' +
			CanonicalQueryString + '\n' +
			CanonicalHeaders + '\n' +
			SignedHeaders + '\n' +
			this.SHA256(requestPayload).toString();
		var HashedCanonicalRequest = this.SHA256(CanonicalRequest).toString();

		var Algorithm = "AWS4-HMAC-SHA256";
		var RequestDateTime = date2;
		var CredentialScope = date1+"/us-east-1/lex/aws4_request";
		var StringToSign =
			Algorithm + "\n" +
			RequestDateTime + "\n" +
			CredentialScope + "\n" +
			HashedCanonicalRequest;

		var SignatureKey = this.getSignatureKey(this.SecretKey, date1, 'us-east-1', 'lex');
		var signature    = this.CryptoJS.HmacSHA256(StringToSign, SignatureKey);

		var Authorization = Algorithm+" Credential="+this.AccessKey+"/"+CredentialScope+", SignedHeaders="+SignedHeaders+
							", Signature="+signature.toString();

		var settings = {
			async: true,
			crossDomain	: true,
			"url": url,
			"method": HTTPRequestMethod,
			"headers": {
//				"Host": host,
				"Accept": "*/*",
				"Authorization": Authorization,
				"cache-control": "no-cache",
//				"Connection": "keep-alive",
				"Content-Type": contentType,
				"X-Amz-Date": date2,
//				"accept-encoding": "gzip, deflate",
				"Cache-Control": "no-cache"
			},
			"processData": false,
			"data": ""
		}
		this.callAjax(settings, calBack, stackData);
	}
	//------------------------------------------------------------
	callIntents(intentName, calBack){
		var maxResult = 50;
		var dates = this.setCallTime();
		var date1 = dates[0];
		var date2 = dates[1];
		var CanonicalURI = "/intents";
		var CanonicalQueryString = "maxResults="+maxResult+"&nameContains="+intentName+"&nextToken=";

		var url = "https://models.lex.us-east-1.amazonaws.com"+CanonicalURI+"?"+CanonicalQueryString;
		var host = "models.lex.us-east-1.amazonaws.com";
		var contentType	= "application/json";

		var HTTPRequestMethod = "GET";
		var requestPayload = "";
		var xamzcontentsha256 = this.SHA256(requestPayload).toString();
		var CanonicalHeaders = 
			"content-type:"+contentType+"\n"+
			"host:"+host+"\n"+
			"x-amz-date:"+date2+"\n";
		var SignedHeaders = "content-type;host;x-amz-date";
		var CanonicalRequest =
			HTTPRequestMethod + '\n' +
			CanonicalURI + '\n' +
			CanonicalQueryString + '\n' +
			CanonicalHeaders + '\n' +
			SignedHeaders + '\n' +
			this.SHA256(requestPayload).toString();
		var HashedCanonicalRequest = this.SHA256(CanonicalRequest).toString();

		var Algorithm = "AWS4-HMAC-SHA256";
		var RequestDateTime = date2;
		var CredentialScope = date1+"/us-east-1/lex/aws4_request";
		var StringToSign =
			Algorithm + "\n" +
			RequestDateTime + "\n" +
			CredentialScope + "\n" +
			HashedCanonicalRequest;

		var SignatureKey = this.getSignatureKey(this.SecretKey, date1, 'us-east-1', 'lex');
		var signature    = this.CryptoJS.HmacSHA256(StringToSign, SignatureKey);

		var Authorization = Algorithm+" Credential="+this.AccessKey+"/"+CredentialScope+", SignedHeaders="+SignedHeaders+
							", Signature="+signature.toString();

		var settings = {
			async: true,
			crossDomain	: true,
			"url": url,
			"method": HTTPRequestMethod,
			"headers": {
//				"Host": host,
				"Accept": "*/*",
				"Authorization": Authorization,
				"cache-control": "no-cache",
//				"Connection": "keep-alive",
				"Content-Type": contentType,
				"X-Amz-Date": date2,
//				"accept-encoding": "gzip, deflate",
				"Cache-Control": "no-cache"
			},
			"processData": false,
			"data": ""
		}
		this.callAjax(settings, calBack);
	}
	//------------------------------------------------------------
	callIntentVersions(intentName, calBack){
		var maxResult = 50;
		var dates = this.setCallTime();
		var date1 = dates[0];
		var date2 = dates[1];
		var CanonicalURI = "/intents/"+intentName+"/versions";
		var CanonicalQueryString = "maxResults="+maxResult+"&"+"nextToken=";

		var url = "https://models.lex.us-east-1.amazonaws.com"+CanonicalURI+"?"+CanonicalQueryString;
		var host = "models.lex.us-east-1.amazonaws.com";
		var contentType	= "application/json";

		var HTTPRequestMethod = "GET";
		var requestPayload = "";
		var xamzcontentsha256 = this.SHA256(requestPayload).toString();
		var CanonicalHeaders = 
			"content-type:"+contentType+"\n"+
			"host:"+host+"\n"+
			"x-amz-date:"+date2+"\n";
		var SignedHeaders = "content-type;host;x-amz-date";
		var CanonicalRequest =
			HTTPRequestMethod + '\n' +
			CanonicalURI + '\n' +
			CanonicalQueryString + '\n' +
			CanonicalHeaders + '\n' +
			SignedHeaders + '\n' +
			this.SHA256(requestPayload).toString();
		var HashedCanonicalRequest = this.SHA256(CanonicalRequest).toString();

		var Algorithm = "AWS4-HMAC-SHA256";
		var RequestDateTime = date2;
		var CredentialScope = date1+"/us-east-1/lex/aws4_request";
		var StringToSign =
			Algorithm + "\n" +
			RequestDateTime + "\n" +
			CredentialScope + "\n" +
			HashedCanonicalRequest;

		var SignatureKey = this.getSignatureKey(this.SecretKey, date1, 'us-east-1', 'lex');
		var signature    = this.CryptoJS.HmacSHA256(StringToSign, SignatureKey);

		var Authorization = Algorithm+" Credential="+this.AccessKey+"/"+CredentialScope+", SignedHeaders="+SignedHeaders+
							", Signature="+signature.toString();

		var settings = {
			async: true,
			crossDomain	: true,
			"url": url,
			"method": HTTPRequestMethod,
			"headers": {
//				"Host": host,
				"Accept": "*/*",
				"Authorization": Authorization,
				"cache-control": "no-cache",
//				"Connection": "keep-alive",
				"Content-Type": contentType,
				"X-Amz-Date": date2,
//				"accept-encoding": "gzip, deflate",
				"Cache-Control": "no-cache"
			},
			"processData": false,
			"data": ""
		}
		this.callAjax(settings, calBack);
	}
	//------------------------------------------------------------
	callSlotType(sloTtypeName, slotTypeVersion, stackData=null, calBack){
//console.log("sloTtypeName: "+sloTtypeName+", slotTypeVersion: "+slotTypeVersion);
if(slotTypeVersion==null){ return; }
		var dates = this.setCallTime();
		var date1 = dates[0];
		var date2 = dates[1];
		var CanonicalURI = "/slottypes/"+sloTtypeName+"/versions/"+slotTypeVersion;

		var url = "https://models.lex.us-east-1.amazonaws.com"+CanonicalURI;
		var host = "models.lex.us-east-1.amazonaws.com";
		var contentType	= "application/json";

		var HTTPRequestMethod = "GET";
		var CanonicalQueryString = "";
		var requestPayload = "";
		var xamzcontentsha256 = this.SHA256(requestPayload).toString();
		var CanonicalHeaders = 
			"content-type:"+contentType+"\n"+
			"host:"+host+"\n"+
			"x-amz-date:"+date2+"\n";
		var SignedHeaders = "content-type;host;x-amz-date";
		var CanonicalRequest =
			HTTPRequestMethod + '\n' +
			CanonicalURI + '\n' +
			CanonicalQueryString + '\n' +
			CanonicalHeaders + '\n' +
			SignedHeaders + '\n' +
			this.SHA256(requestPayload).toString();
		var HashedCanonicalRequest = this.SHA256(CanonicalRequest).toString();

		var Algorithm = "AWS4-HMAC-SHA256";
		var RequestDateTime = date2;
		var CredentialScope = date1+"/us-east-1/lex/aws4_request";
		var StringToSign =
			Algorithm + "\n" +
			RequestDateTime + "\n" +
			CredentialScope + "\n" +
			HashedCanonicalRequest;

		var SignatureKey = this.getSignatureKey(this.SecretKey, date1, 'us-east-1', 'lex');
		var signature    = this.CryptoJS.HmacSHA256(StringToSign, SignatureKey);

		var Authorization = Algorithm+" Credential="+this.AccessKey+"/"+CredentialScope+", SignedHeaders="+SignedHeaders+
							", Signature="+signature.toString();

		var settings = {
			async: true,
			crossDomain	: true,
			"url": url,
			"method": HTTPRequestMethod,
			"headers": {
//				"Host": host,
				"Accept": "*/*",
				"Authorization": Authorization,
				"cache-control": "no-cache",
//				"Connection": "keep-alive",
				"Content-Type": contentType,
				"X-Amz-Date": date2,
//				"accept-encoding": "gzip, deflate",
				"Cache-Control": "no-cache"
			},
			"processData": false,
			"data": ""
		}
		this.callAjax(settings, calBack, stackData);
	}
	//------------------------------------------------------------
}
export {
	LEXBase
}
