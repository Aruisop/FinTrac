document.addEventListener("DOMContentLoaded", function () { 
  fetchTransactions();
  document.getElementById("transactionForm").addEventListener("submit", addTransaction);
});

function addTransaction(event) {
  event.preventDefault();

  const description = document.getElementById("description").value.trim();
  const amount = parseFloat(document.getElementById("amount").value);
  const type = document.getElementById("type").value;

  if (!description || isNaN(amount) || amount <= 0) {
      alert("Enter valid transaction details.");
      return;
  }

  fetch("add_transaction.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ description, amount, type })
  })
  .then(response => response.json())
  .then(data => {
      if (data.success) {
          document.getElementById("transactionForm").reset();
          fetchTransactions();
      } else {
          alert("Error: " + data.message);
      }
  })
  .catch(error => console.error("Error adding transaction:", error));
}

function fetchTransactions() {
  fetch("get_transactions.php")
      .then(response => response.json())
      .then(data => {
          if (!data.success) {
              document.getElementById("transactionList").innerHTML = `<li class="text-muted text-center">No transactions found</li>`;
              return;
          }

          const transactions = data.transactions;
          let income = 0, expenses = 0;
          const transactionList = document.getElementById("transactionList");
          transactionList.innerHTML = transactions
              .map(tx => {
                  if (tx.type === "income") income += parseFloat(tx.amount);
                  else expenses += parseFloat(tx.amount);
                  return `<li class="list-group-item d-flex justify-content-between ${tx.type === 'income' ? 'income' : 'expense'}">
                      <span>${tx.description}</span> <strong>$${parseFloat(tx.amount).toFixed(2)}</strong>
                  </li>`;
              })
              .join("");

          updateChart(income, expenses);
      })
      .catch(error => console.error("Error fetching transactions:", error));
}


function updateChart(income, expenses) {
  const ctx = document.getElementById("transactionChart").getContext("2d");

  if (window.chart) window.chart.destroy();

  window.chart = new Chart(ctx, {
      type: "pie",
      data: {
          labels: ["Income", "Expenses"],
          datasets: [{ data: [income, expenses], backgroundColor: ["#28a745", "#dc3545"] }]
      }
  });
}








