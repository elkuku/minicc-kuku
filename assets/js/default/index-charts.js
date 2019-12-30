const $ = require('jquery')

function drawChart(elemantId, title, labels, data) {

    let bgColors = [
        'rgba(255, 99, 132, 0.2)',
        'rgba(54, 162, 235, 0.2)',
        'rgba(255, 206, 86, 0.2)',
        'rgba(75, 192, 192, 0.2)',
        'rgba(153, 102, 255, 0.2)',
        'rgba(255, 159, 64, 0.2)',
        'rgba(255, 99, 132, 0.2)',
        'rgba(54, 162, 235, 0.2)',
        'rgba(255, 206, 86, 0.2)'
    ];

    let borderColors = [
        'rgba(255,99,132,1)',
        'rgba(54, 162, 235, 1)',
        'rgba(255, 206, 86, 1)',
        'rgba(75, 192, 192, 1)',
        'rgba(153, 102, 255, 1)',
        'rgba(255, 159, 64, 1)',
        'rgba(255,99,132,1)',
        'rgba(54, 162, 235, 1)',
        'rgba(255, 206, 86, 1)'
    ];

    new Chart(document.getElementById(elemantId), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: title,
                data: data,
                backgroundColor: bgColors,
                borderColor: borderColors,
                borderWidth: 1
            }]
        },
        options: {scales: {yAxes: [{ticks: {beginAtZero: true}}]}}
    });
}

let chart1 = $('#chart1')
let chart2 = $('#chart2')

drawChart('chart1', 'Meses de deuda', JSON.parse(chart1.attr('data-chart-headers')), JSON.parse(chart1.attr('data-chart-data')))
drawChart('chart2', 'Saldo en $', JSON.parse(chart2.attr('data-chart-headers')), JSON.parse(chart2.attr('data-chart-data')))
