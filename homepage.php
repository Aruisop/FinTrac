 <?php   
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
require_once 'db_config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Tracker</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            background:white; 
            font-family: 'Playfair Display', serif; 
        }
        .container { 
            max-width: 650px; 
            margin: 50px auto; 
            background: white; 
            padding: 25px; 
            border-radius: 12px; 
            box-shadow: 3px 3px 15px rgba(0, 0, 0, 0.1);
        }
        h2 { 
            font-weight: 700; 
            text-align: center; 
            color: #2c3e50; 
            letter-spacing: 1px;
        }
        .welcome-msg { 
            font-size: 18px;  
            text-align: center; 
            color: #444; 
            font-weight: 600;
        }
        .logout-btn { 
            position: absolute; 
            top: 20px; 
            right: 20px; 
        }
        .card { 
            border: none; 
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.05); 
            border-radius: 10px;
            background:rgb(237, 238, 239);
        }
        .btn-primary, .btn-success { 
            border-radius: 8px; 
            transition: 0.3s ease-in-out; 
            font-weight: 600;
        }
        .btn-primary:hover, .btn-success:hover { 
            transform: scale(1.05);
            background: #28a745;
            color: white;
        }
        input, select { 
            border-radius: 8px; 
            font-size: 16px;
        }
        input:focus, select:focus { 
            border-color: #27ae60; 
            box-shadow: 0 0 5px rgba(39, 174, 96, 0.3);
        }
        .income { color: green; font-weight: bold; }
        .expense { color: red; font-weight: bold; }
        .timestamp {
            font-size: 0.8rem;
            color: #6c757d;
            display: block;
            margin-top: 3px;
        }
        .transaction-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .transaction-details {
            display: flex;
            flex-direction: column;
        }
    </style>
</head>
<body>

<div class="container position-relative">
    <h2>FinTrac</h2>
    &nbsp;
    <p class="welcome-msg">Hey <strong><?php echo htmlspecialchars($_SESSION["username"]); ?></strong>, Welcome to FinTrac</p>
    &nbsp;
    <a href="logout.php" class="btn btn-danger btn-sm logout-btn">Logout</a>

    <!-- Transaction Form -->
    <div class="card p-4">
        <h4 class="text-center">Add a New Transaction</h4>
        &nbsp;
        <form id="transactionForm">
            <div class="mb-3">
                <label><strong>Description</strong></label>
                <input type="text" id="description" class="form-control" placeholder="E.g., Salary, Rent, Grocery" required>
            </div>
            <div class="mb-3">
                <label><strong>Amount</strong></label>
                <input type="number" id="amount" class="form-control" placeholder="Enter amount" required>
            </div>
            <div class="mb-3">
                <label><strong>Type</strong></label>
                <select id="type" class="form-control">
                    <option value="income">💰 Income</option>
                    <option value="expense">💸 Expense</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success w-100">Add Transaction</button>
        </form>
    </div>

    <!-- Transaction History -->
    <div class="card p-4 mt-3">
        <h4 class="text-center">Transaction History</h4>
        &nbsp;
        <ul id="transactionList" class="list-group"></ul>
    </div>

    <!-- Summary -->
    <div class="card p-4 mt-3">
        <h4 class="text-center">Financial Summary</h4>
        &nbsp;
        <canvas id="transactionChart"></canvas>
    </div>
</div>

<script src="script.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const transactionForm = document.getElementById("transactionForm");
    const transactionList = document.getElementById("transactionList");
    let transactions = JSON.parse(localStorage.getItem("transactions")) || [];

    function updateTransactionList() {
        transactionList.innerHTML = "";
        transactions.forEach((transaction, index) => {
            const listItem = document.createElement("li");
            listItem.classList.add("list-group-item", "d-flex", "justify-content-between", "align-items-center");
            
            // Create a container for description and timestamp
            const descContainer = document.createElement("div");
            descContainer.classList.add("me-auto");
            
            // Add description
            const descText = document.createElement("strong");
            descText.textContent = transaction.description;
            descContainer.appendChild(descText);
            
            // Add timestamp below description
            if (transaction.timestamp) {
                const timeText = document.createElement("div");
                timeText.classList.add("small", "text-muted");
                timeText.textContent = transaction.timestamp;
                descContainer.appendChild(timeText);
            }
            
            // Create amount element
            const amountSpan = document.createElement("span");
            amountSpan.classList.add(transaction.type === "income" ? "income" : "expense");
            amountSpan.textContent = `${transaction.type === "income" ? "+" : "-"} $${transaction.amount}`;
            
            // Create delete button
            const deleteBtn = document.createElement("button");
            deleteBtn.classList.add("btn", "btn-sm", "btn-danger", "ms-2");
            deleteBtn.textContent = "X";
            deleteBtn.onclick = function() { deleteTransaction(index); };
            
            // Append all elements to list item
            listItem.appendChild(descContainer);
            listItem.appendChild(amountSpan);
            listItem.appendChild(deleteBtn);
            
            transactionList.appendChild(listItem);
        });
    }

    window.deleteTransaction = function(index) {
        transactions.splice(index, 1);
        localStorage.setItem("transactions", JSON.stringify(transactions));
        updateTransactionList();
    };

    function addTransaction(event) {
        event.preventDefault();
        const description = document.getElementById("description").value;
        const amount = document.getElementById("amount").value;
        const type = document.getElementById("type").value;

        if (description && amount) {
            // Create current date and time string
            const now = new Date();
            const formattedDate = `${now.toLocaleDateString()} ${now.toLocaleTimeString()}`;
            
            const newTransaction = {
                description,
                amount: parseFloat(amount).toFixed(2),
                type,
                timestamp: formattedDate
            };
            
            transactions.push(newTransaction);
            localStorage.setItem("transactions", JSON.stringify(transactions));
            updateTransactionList();
            transactionForm.reset();
        }
    }

    transactionForm.addEventListener("submit", addTransaction);
    updateTransactionList();
});


        