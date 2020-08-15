const $ = require('jquery')

function drawChart(elemantId, labels, data1, data2) {
    new Chart(document.getElementById(elemantId), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Pagos',
                    fill: false,
                    lineTension: 0.1,
                    backgroundColor: 'rgba(75,192,192,0.4)',
                    borderColor: 'rgba(75,192,192,1)',
                    borderCapStyle: 'butt',
                    borderDash: [],
                    borderDashOffset: 0.0,
                    borderJoinStyle: 'miter',
                    pointBorderColor: 'rgba(75,192,192,1)',
                    pointBackgroundColor: '#fff',
                    pointBorderWidth: 1,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: 'rgba(75,192,192,1)',
                    pointHoverBorderColor: 'rgba(220,220,220,1)',
                    pointHoverBorderWidth: 2,
                    pointRadius: 1,
                    pointHitRadius: 10,
                    data: data1,
                    spanGaps: false
                },
                {
                    label: 'Alquiler',
                    fill: false,
                    lineTension: 0.1,
                    backgroundColor: 'rgba(255, 206, 86, 0.2)',
                    borderColor: 'rgba(255, 206, 86, 0.2)',
                    borderCapStyle: 'butt',
                    borderDash: [],
                    borderDashOffset: 0.0,
                    borderJoinStyle: 'miter',
                    pointBorderColor: 'rgba(75,192,192,1)',
                    pointBackgroundColor: '#fff',
                    pointBorderWidth: 1,
                    pointRadius: 1,
                    pointHitRadius: 10,
                    data: data2,
                    spanGaps: false
                }
            ]
        },
        options: {scales: {yAxes: [{ticks: {beginAtZero: true}}]}}
    })
}

let chart = $('#chart')

drawChart(
    'chart',
    JSON.parse(chart.attr('data-chart-headers')),
    JSON.parse(chart.attr('data-chart-data')),
    JSON.parse(chart.attr('data-chart-data2'))
)
