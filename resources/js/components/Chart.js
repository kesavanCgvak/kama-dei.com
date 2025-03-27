import Chart from 'chart.js';
import ChartjsPluginLabels from 'chartjs-plugin-labels';
import { DataTable, showError, showSuccess } from './DataTable'

/*
const App1 = () => (
	<Line data={passWeekChatsData} />
)
const App2 = () => (
	<Bar data={last24HRSData} width={100} height={250} options={{ maintainAspectRatio: false }} />
)

const App3 = () => (
	<Bar data={dayChatData} width={100} height={250} options={dayChatOption} />
)

if(document.getElementById('passWeekChats')){ render(<App1 />, document.getElementById('passWeekChats')); }
if(document.getElementById('last24HRS')){ render(<App2 />, document.getElementById('last24HRS')); }
if(document.getElementById('7DayChat')){ render(<App3 />, document.getElementById('7DayChat')); }
*/

if($('#passWeekChats').length != 0) {
	$("#passWeekChats").html("<canvas></canvas>");
	var ctx = $("#passWeekChats canvas")[0].getContext('2d');
	var chart = new Chart(ctx, {
		type: 'line',
		data: passWeekChatsData
	});
}

if($('#last24HRS').length != 0) {
	$("#last24HRS").html("<canvas width='100' height='250'></canvas>");
	var ctx = $("#last24HRS canvas")[0].getContext('2d');
	var chart = new Chart(ctx, {
		type: 'bar',
		data: last24HRSData,
		options: {
			maintainAspectRatio: false
		}
	});
}

if($('#7DayChat').length != 0) {
	$("#7DayChat").html("<canvas width='100' height='250'></canvas>");
	var ctx = $("#7DayChat canvas")[0].getContext('2d');
	var chart = new Chart(ctx, {
		type: 'bar',
		data: dayChatData,
		options: dayChatOption,
	});
}
