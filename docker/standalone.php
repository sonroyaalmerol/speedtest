<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, user-scalable=no" />
<link rel="apple-touch-icon" href="favicon.ico">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta charset="UTF-8" />
<link rel="shortcut icon" href="favicon.ico">
<script type="text/javascript" src="speedtest.js"></script>
<script type="text/javascript">
function I(i){return document.getElementById(i);}
//INITIALIZE SPEED TEST
var s=new Speedtest(); //create speed test object
<?php if(getenv("TELEMETRY")=="true"){ ?>
s.setParameter("telemetry_level","basic");
<?php } ?>
<?php if(getenv("DISABLE_IPINFO")=="true"){ ?>
s.setParameter("getIp_ispInfo",false);
<?php } ?>
<?php if(getenv("DISTANCE")){ ?>
s.setParameter("getIp_ispInfo_distance","<?=getenv("DISTANCE") ?>");
<?php } ?>

var meterBk=/Trident.*rv:(\d+\.\d+)/i.test(navigator.userAgent)?"#EAEAEA":"#80808040";
var dlColor="#6060AA",
	ulColor="#616161";
var progColor=meterBk;

//CODE FOR GAUGES
function drawMeter(c,amount,bk,fg,progress,prog){
	var ctx=c.getContext("2d");
	var dp=window.devicePixelRatio||1;
	var cw=c.clientWidth*dp, ch=c.clientHeight*dp;
	var sizScale=ch*0.0055;
	if(c.width==cw&&c.height==ch){
		ctx.clearRect(0,0,cw,ch);
	}else{
		c.width=cw;
		c.height=ch;
	}
	ctx.beginPath();
	ctx.strokeStyle=bk;
	ctx.lineWidth=12*sizScale;
	ctx.arc(c.width/2,c.height-58*sizScale,c.height/1.8-ctx.lineWidth,-Math.PI*1.1,Math.PI*0.1);
	ctx.stroke();
	ctx.beginPath();
	ctx.strokeStyle=fg;
	ctx.lineWidth=12*sizScale;
	ctx.arc(c.width/2,c.height-58*sizScale,c.height/1.8-ctx.lineWidth,-Math.PI*1.1,amount*Math.PI*1.2-Math.PI*1.1);
	ctx.stroke();
	if(typeof progress !== "undefined"){
		ctx.fillStyle=prog;
		ctx.fillRect(c.width*0.3,c.height-16*sizScale,c.width*0.4*progress,4*sizScale);
	}
}
function mbpsToAmount(s){
	return 1-(1/(Math.pow(1.3,Math.sqrt(s))));
}
function format(d){
    d=Number(d);
    if(d<10) return d.toFixed(2);
    if(d<100) return d.toFixed(1);
    return d.toFixed(0);
}

//UI CODE
var uiData=null;
function startStop(){
    if(s.getState()==3){
		//speed test is running, abort
		s.abort();
		data=null;
		I("startStopBtn").className="";
		I("descriptionBox").style.display="inline-block";
        I("description").readOnly=false;
		initUI();
	}else{
		//test is not running, begin
		I("startStopBtn").className="running";
		I("descriptionBox").style.display="none";
        I("description").readOnly=true;
		I("shareArea").style.display="none";
		s.onupdate=function(data){
            uiData=data;
		};
		s.onend=function(aborted){
            I("startStopBtn").className="";
            updateUI(true);
            if(!aborted){
                //if testId is present, show sharing panel, otherwise do nothing
                try{
                    var testId=uiData.testId;
                    if(testId!=null){
                        var shareURL=window.location.href.substring(0,window.location.href.lastIndexOf("/"))+"/results/?id="+testId;
                        I("resultsImg").src=shareURL;
                        I("resultsURL").value=shareURL;
                        I("testId").innerHTML=testId;
                        I("descriptionBox").style.display="inline-block";
                        I("description").readOnly=true;
                        I("shareArea").style.display="";
                    }
                }catch(e){}
            }
		};
		s.start();
	}
}
//this function reads the data sent back by the test and updates the UI
function updateUI(forced){
	if(!forced&&s.getState()!=3) return;
	if(uiData==null) return;
	var status=uiData.testState;
	I("ip").textContent=uiData.clientIp;
	I("dlText").textContent=(status==1&&uiData.dlStatus==0)?"...":format(uiData.dlStatus);
	drawMeter(I("dlMeter"),mbpsToAmount(Number(uiData.dlStatus*(status==1?oscillate():1))),meterBk,dlColor,Number(uiData.dlProgress),progColor);
	I("ulText").textContent=(status==3&&uiData.ulStatus==0)?"...":format(uiData.ulStatus);
	drawMeter(I("ulMeter"),mbpsToAmount(Number(uiData.ulStatus*(status==3?oscillate():1))),meterBk,ulColor,Number(uiData.ulProgress),progColor);
	I("pingText").textContent=format(uiData.pingStatus);
	I("jitText").textContent=format(uiData.jitterStatus);
}
function oscillate(){
	return 1+0.02*Math.sin(Date.now()/100);
}
//update the UI every frame
window.requestAnimationFrame=window.requestAnimationFrame||window.webkitRequestAnimationFrame||window.mozRequestAnimationFrame||window.msRequestAnimationFrame||(function(callback,element){setTimeout(callback,1000/60);});
function frame(){
	requestAnimationFrame(frame);
	updateUI();
}
frame(); //start frame loop
//function to (re)initialize UI
function initUI(){
	drawMeter(I("dlMeter"),0,meterBk,dlColor,0);
	drawMeter(I("ulMeter"),0,meterBk,ulColor,0);
	I("dlText").textContent="";
	I("ulText").textContent="";
	I("pingText").textContent="";
	I("jitText").textContent="";
	I("ip").textContent="";
	I("description").value="";
}
</script>
<style type="text/css">
	html,body{
		border:none; padding:0; margin:0;
		background:#FFFFFF;
		color:#202020;
	}
	body{
		text-align:center;
		font-family:"Roboto",sans-serif;
	}
	h1{
		color:#404040;
	}
	#descriptionBox{
		display:inline-block;
		margin:0 auto;
	}
	#startStopBtn{
		display:inline-block;
		margin:0 auto;
		color:#6060AA;
		background-color:rgba(0,0,0,0);
		border:0.15em solid #6060FF;
		border-radius:0.3em;
		transition:all 0.3s;
		box-sizing:border-box;
		width:8em; height:3em;
		line-height:2.7em;
		cursor:pointer;
		box-shadow: 0 0 0 rgba(0,0,0,0.1), inset 0 0 0 rgba(0,0,0,0.1);
	}
	#startStopBtn:hover{
		box-shadow: 0 0 2em rgba(0,0,0,0.1), inset 0 0 1em rgba(0,0,0,0.1);
	}
	#startStopBtn.running{
		background-color:#FF3030;
		border-color:#FF6060;
		color:#FFFFFF;
	}
	#startStopBtn:before{
		content:"Start";
	}
	#startStopBtn.running:before{
		content:"Abort";
	}
	#test{
		margin-top:2em;
		margin-bottom:12em;
	}
	div.testArea{
		display:inline-block;
		width:16em;
		height:12.5em;
		position:relative;
		box-sizing:border-box;
	}
	div.testArea2{
		display:inline-block;
		width:14em;
		height:7em;
		position:relative;
		box-sizing:border-box;
		text-align:center;
	}
	div.testArea div.testName{
		position:absolute;
		top:0.1em; left:0;
		width:100%;
		font-size:1.4em;
		z-index:9;
	}
	div.testArea2 div.testName{
        display:block;
        text-align:center;
        font-size:1.4em;
	}
	div.testArea div.meterText{
		position:absolute;
		bottom:1.55em; left:0;
		width:100%;
		font-size:2.5em;
		z-index:9;
	}
	div.testArea2 div.meterText{
        display:inline-block;
        font-size:2.5em;
	}
	div.meterText:empty:before{
		content:"0.00";
	}
	div.testArea div.unit{
		position:absolute;
		bottom:2em; left:0;
		width:100%;
		z-index:9;
	}
	div.testArea2 div.unit{
		display:inline-block;
	}
	div.testArea canvas{
		position:absolute;
		top:0; left:0; width:100%; height:100%;
		z-index:1;
	}
	div.testGroup{
		display:block;
        margin: 0 auto;
	}
	#shareArea{
		width:95%;
		max-width:40em;
		margin:0 auto;
		margin-top:2em;
	}
	#shareArea > *{
		display:block;
		width:100%;
		height:auto;
		margin: 0.25em 0;
	}
	#privacyPolicy{
        position:fixed;
        top:2em;
        bottom:2em;
        left:2em;
        right:2em;
        overflow-y:auto;
        width:auto;
        height:auto;
        box-shadow:0 0 3em 1em #000000;
        z-index:999999;
        text-align:left;
        background-color:#FFFFFF;
        padding:1em;
	}
	a.privacy{
        text-align:center;
        font-size:0.8em;
        color:#808080;
        display:block;
	}
	@media all and (max-width:40em){
		body{
			font-size:0.8em;
		}
	}
</style>
<title><?= getenv('TITLE') ?: 'LibreSpeed Example' ?></title>
</head>
<body>
<h1><?= getenv('TITLE') ?: 'LibreSpeed Example' ?></h1>
<div id="testWrapper">
	<div id="descriptionBox">
		<label for="description">Location Description:</label>
		<input type="text" id="description" name="description"></input>
	</div><br/>
	<div id="startStopBtn" onclick="startStop()"></div><br/>
	<?php if(getenv("TELEMETRY")=="true"){ ?>
        <a class="privacy" href="#" onclick="I('privacyPolicy').style.display=''">Privacy</a>
	<?php } ?>
	<div id="test">
		<div class="testGroup">
			<div class="testArea2">
				<div class="testName">Ping</div>
				<div id="pingText" class="meterText" style="color:#AA6060"></div>
				<div class="unit">ms</div>
			</div>
			<div class="testArea2">
				<div class="testName">Jitter</div>
				<div id="jitText" class="meterText" style="color:#AA6060"></div>
				<div class="unit">ms</div>
			</div>
		</div>
		<div class="testGroup">
			<div class="testArea">
				<div class="testName">Download</div>
				<canvas id="dlMeter" class="meter"></canvas>
				<div id="dlText" class="meterText"></div>
				<div class="unit">Mbit/s</div>
			</div>
			<div class="testArea">
				<div class="testName">Upload</div>
				<canvas id="ulMeter" class="meter"></canvas>
				<div id="ulText" class="meterText"></div>
				<div class="unit">Mbit/s</div>
			</div>
		</div>
		<div id="ipArea">
			<span id="ip"></span>
		</div>
		<div id="shareArea" style="display:none">
			<h3>Share results</h3>
			<p>Test ID: <span id="testId"></span></p>
			<input type="text" value="" id="resultsURL" readonly="readonly" onclick="this.select();this.focus();this.select();document.execCommand('copy');alert('Link copied')"/>
			<img src="" id="resultsImg" />
		</div>
	</div>
	<a href="/outpost.goauthentik.io/sign_out">Log out</a>
</div>
<div id="privacyPolicy" style="display:none">
    <h2>Privacy Policy</h2>
    <p>This HTML5 speed test server is configured with telemetry enabled.</p>
    <h4>What data we collect</h4>
    <p>
        At the end of the test, the following data is collected and stored:
        <ul>
            <li>Test ID</li>
            <li>Time of testing</li>
            <li>Test results (download and upload speed, ping and jitter)</li>
            <li>IP address</li>
            <li>ISP information</li>
            <li>Approximate location (inferred from IP address, not GPS)</li>
            <li>User agent and browser locale</li>
            <li>Test log (contains no personal information)</li>
        </ul>
    </p>
    <h4>How we use the data</h4>
    <p>
        Data collected through this service is used to:
        <ul>
            <li>Allow sharing of test results (sharable image for forums, etc.)</li>
            <li>To improve the service offered to you (for instance, to detect problems on our side)</li>
        </ul>
        No personal information is disclosed to third parties.
    </p>
    <h4>Your consent</h4>
    <p>
        By starting the test, you consent to the terms of this privacy policy.
    </p>
    <h4>Data removal</h4>
    <p>
        If you want to have your information deleted, you need to provide either the ID of the test or your IP address. This is the only way to identify your data, without this information we won't be able to comply with your request.<br/><br/>
        Contact this email address for all deletion requests: <a href="mailto:<?=getenv("EMAIL") ?>"><?=getenv("EMAIL") ?></a>.
    </p>
    <br/><br/>
    <a class="privacy" href="#" onclick="I('privacyPolicy').style.display='none'">Close</a><br/>
</div>
<script type="text/javascript">setTimeout(function(){initUI()},100);</script>
</body>
</html>
