var debug = 1;

function err(m){
	console.log('WebSockets Error: ' + m);
}

function msg(m){
	if(debug) console.log('Message: ' + m);
}


//socket is a defin
function wssconnect(socket,url,type){
	socket = new WebSocket(url);
	if(socket == undefined){
		err('parameter socket is not defined');
		return false;
	}
	if(url == "" || url == undefined){
		err('parameter url is invalid');
		return false;
	}
	if(!socket || socket == undefined){
		err('failed to create socket');
		return false;
	}


	socket.onopen = function(){
		msg('Open successfully');
		if(type == "saber") registersaber(socket);
	}
	socket.onerror = function(){
		msg('Error occurs');
	}
	socket.onclose = function(){
		msg('Seockt Closed');
		if(type=='owner'){
			document.getElementById('handleopened').style.visibility="hidden";
			document.getElementById('handle').style.visibility="visible";
		}
	}
	socket.onmessage = function(e){
		if(e.data.indexOf('{')==-1) msg(e.data);
		else{
			var obj = JSON.parse(e.data);
			if(obj.to == "owner"&&obj.to==type){
				ownerprocess(obj);
			}
			else if(obj.to == "saber"&&obj.to==type){
				saberprocess(obj);
			}
		}
	}
	return socket;
}

function registersaber(socket){
	if(!socket || socket == undefined){
		err('Fail to Register, No Available Socket');
		return false;
	}
	var obj = JSON.stringify({'type':"registersaber"});
	socket.send(obj);
}

function getsaber(saberid){
	if(!socket || socket == undefined){
		err('Fail to Get Lightsaber , No Available Socket');
		return false;
	}
	var obj = JSON.stringify({'type':"getsaber",'saberid':saberid.toLowerCase()});
	socket.send(obj);
}

function sendmotionstate(socket,a,b,g){
	if(!socket || socket == undefined){
		err('Fail to Get Lightsaber , No Available Socket');
		return false;
	}
	var obj = JSON.stringify({'type':"mstate",'a':a,'b':b,'g':g});
	socket.send(obj);
}

function saberprocess(obj){
	var cmd = obj.cmd;
	switch(cmd) {
		 case "move":
		 	var alpha = obj.a;
		 	var beta = obj.b;
		 	var gamma = obj.g;
		 	var r = gamma/2.4;
			var h = 800+(beta*8);
			var rx = -beta;
	  		// document.getElementById("alphalabel").innerHTML = "Alpha: " + alpha;
			// document.getElementById("betalabel").innerHTML = "Beta: " + beta;
			// document.getElementById("gammalabel").innerHTML = "Gamma: " + gamma;
			
			document.getElementById('sword').style.transform = "rotate("+r+"deg)";
			document.getElementById('sword').style.WebkitTransform = "rotate("+r+"deg)";
			document.getElementById('sword').style.MozTransform = "rotate("+r+"deg)";
			document.getElementById('sword').style.height = h+"px";
	        break;
	    case "showsaberid":
	    	document.getElementById('saberid').innerHTML = "ID: "+obj.saberid;
	    	break;
	    default:
	        err('Saber: invalid cmd'+cmd)
	}
}
function ownerprocess(obj){
	var cmd = obj.cmd;
	switch(cmd) {
	    case "connected":
	        document.getElementById('handle').style.visibility="hidden";
	        connected = 1;
	        break;
	    default:
	        err('Owner: invalid cmd'+cmd)
	}
}