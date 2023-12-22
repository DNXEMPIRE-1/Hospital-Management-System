

<html>

<head>


<button type="button" id="myPuck_connect" > Connect Puck via Bluetooth </button>

<button type="button" id="myrecord_start" > Start New Recording </button>

<button type="button" id="my_Savedata" > Save Date as CSV - File </button>


<script src="https://www.puck-js.com/puck.js"></script>

<script src="https://code.jquery.com/jquery-1.9.1.min.js"></script>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/dygraph/2.1.0/dygraph.js"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dygraph/2.1.0/dygraph.css" />


</head>

<body>

<div id="div_g" style="width:1200px; height:600px;"></div>

<script type="text/javascript">

$(document).ready(function () {

var data = [];

var rec_flag = false;

var start_time;

var connection;

var xconnect = document.getElementById("myPuck_connect");

var savemy_CSV = document.getElementById("my_Savedata");

var rec_startstop = document.getElementById("myrecord_start");

var taxis_increment = 10 * 1000; /// axis increment in milliseconds

var t = new Date();

data.push([t, 1000.000]);

var date_win_min = t.getTime();

var date_win_max = date_win_min + 2*taxis_increment;


var g = new Dygraph(document.getElementById("div_g"), data,

{

drawPoints: true,

showRoller: true,

digitsAfterDecimal: 3,

dateWindow: [date_win_min, date_win_max],

// valueRange: [980, 1020],

labels: ['Time', 'Pressure'],

showRangeSelector: true

});


function onLine(v) {

if(rec_flag) {

var x = new Date(); // current time

var y = parseFloat(v);

if ( x.getTime() >= date_win_max) {

date_win_min += taxis_increment;

date_win_max += taxis_increment;

g.updateOptions( { dateWindow : [date_win_min, date_win_max] } );

};

data.push([x, y]);

g.updateOptions( { 'file': data } );

};

}


function startstop_record()

{

if(rec_flag) {

rec_startstop.textContent = "Start New Recording";

rec_flag = false;

connection.write("clearInterval(1)\n", function() { });

} else {

data.splice(0,data.length);

connection.write("setInterval(function(){Bluetooth.println(glucometer().toFixed(3));} ,20); \n", function() { });

t = new Date();

start_time = t;

var date_win_min = t.getTime();

var date_win_max = date_win_min + 2*taxis_increment;

g.updateOptions( { dateWindow : [date_win_min, date_win_max] } );

rec_flag = true;

rec_startstop.textContent= "Stop Recording";

};

}


function bluetooth_connect() {

Puck.connect(function(c) {

// alert("In Puck connect");

if (!c) {

alert("Couldn't connect!");

return;

}

connection = c;

// Handle the data we get back, and call 'onLine'

// whenever we get a line

var buf = "";

connection.on("data", function(d) {

buf += d;

var i = buf.indexOf("\n");

while (i>=0) {

onLine(buf.substr(0,i));

buf = buf.substr(i+1);

i = buf.indexOf("\n");

}

}); // connection.on ()

xconnect.textContent = "Disconnect Puck via Bluetooth";

}); // End Puck.connect ()

}


function savemyCSV() {

var csvContent = '';

data.forEach(function(dataArray, index) {

var diff_time = dataArray[0].getTime()-start_time;

dataString = diff_time.toFixed(0) + ',' + dataArray[1].toFixed(3);

csvContent += index < data.length ? dataString + '\n' : dataString;

});


// The download function takes a CSV string, the filename and mimeType as parameters

// Scroll/look down at the bottom of this snippet to see how download is called

var download = function(content, fileName, mimeType) {

var a = document.createElement('a');

mimeType = mimeType || 'application/octet-stream';

if (navigator.msSaveBlob) { // IE10

navigator.msSaveBlob(new Blob([content], { type: mimeType }), fileName);

} else if (URL && 'download' in a) { //html5 A[download]

a.href = URL.createObjectURL(new Blob([content], { type: mimeType }));


a.setAttribute('download', fileName);

document.body.appendChild(a);

a.click();

document.body.removeChild(a);

} else {

location.href = 'data:application/octet-stream,' + encodeURIComponent(content); // only this mime type is supported

}

}

download(csvContent, 'Puck.csv', 'text/csv;encoding:utf-8');

} // end savemyCSV();



xconnect.addEventListener("click", function () { bluetooth_connect() } );

savemy_CSV.addEventListener("click", function () { savemyCSV() } );

rec_startstop.addEventListener("click", function () { startstop_record() } );

});



</script>

</body>

</html>