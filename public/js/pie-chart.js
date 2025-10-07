fetch('../src/get_fund_data.php')
  .then(response => response.json())
  .then(data => {
    const labels = data.map(row => row.fund_name); // fund source names
    const values = data.map(row => parseInt(row.total_items)); // number of items

    if (values.every(v => v === 0)) {
      document.getElementById('chartContainer').innerHTML =
        "<p>No data available for chart.</p>";
      return;
    }

    const ctx = document.getElementById('fundChart').getContext('2d');
    new Chart(ctx, {
      type: 'pie',
      data: {
        labels: labels,
        datasets: [{
          label: 'Number of Items',
          data: values,
          backgroundColor: [
            '#007bff', // blue
            '#00c9a7', // green
            '#ffb84d', // orange
            '#9966ff', // purple
            '#ff6384', // pink
            '#4bc0c0'  // teal
          ],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'right',
            align: 'center',
            labels: {
              boxWidth: 10,
              padding: 10,
              font: {
                size: 10
              }
            }
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                let value = context.raw;
                let total = context.chart.getDatasetMeta(0).total;
                let percentage = ((value / total) * 100).toFixed(1);
                return `${context.label}: ${value} items (${percentage}%)`;
              }
            }
          }
        }
      }
    });
  });