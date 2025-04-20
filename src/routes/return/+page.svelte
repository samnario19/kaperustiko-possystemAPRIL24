<script lang="ts">
    import { onMount } from "svelte";
    import Sidebar from "../sidebar/+page.svelte";
  
    // Define the type for a sale
    type Sale = {
        receipt: string;
        items: string;
        order_name2: string;
        order_quantity: string;
        order_size: string;
        price: string;
        totalCost: string;
        payAmount: string;
        changeDue: string;
        orderDate: string;
        orderTime: string;
        orderIn: string;
        name: string;
        totalDiscount: string;
        items_ordered: string;
    };
  
    let recentSales: Sale[] = [];
  
    let selectedDate: Date = new Date(); // Change to Date object
  
    let noSalesData: boolean = false; // New variable to track no sales data
  
    let showPopup: boolean = false; // New variable to control popup visibility
    let totalSales: number = 0; // Variable to store total sales
    let shortage: number = 0.00; // New variable to store shortage input
  
    let showSecondPopup: boolean = false; // New variable to control second popup visibility
  
    let inputCode: string = "123456"; // Declare inputCode variable
  
    let isRemitDisabled: boolean = false; // New variable to track if remit is disabled
  
    // Add a new variable to control the return confirmation popup
    let showReturnConfirmation: boolean = false; // New variable for return confirmation
    let selectedReceipt: string | null = null; // Variable to store the selected receipt for return
  
    // Function to get all date options for sorting
    function getAllDateOptions() {
        const options: string[] = [];
        const startYear = 2024; // Set minimum year to 2024
        const startMonth = 10; // November (0-indexed)
        const startDay = 1; // Start from the 1st
        const today = new Date();
        const endYear = today.getFullYear(); // Current year

        for (let year = startYear; year <= endYear; year++) {
            for (let month = (year === startYear ? startMonth : 0); month < 12; month++) {
                const daysInMonth = new Date(year, month + 1, 0).getDate(); // Get the number of days in the month
                for (let day = (year === startYear && month === startMonth ? startDay : 1); day <= daysInMonth; day++) {
                    const date = new Date(year, month, day);
                    options.push(date.toISOString().split('T')[0]); // Format as YYYY-MM-DD
                }
            }
        }
        return options;
    }
  
    // Initialize dateOptions with all possible dates
    const dateOptions: string[] = getAllDateOptions();
  
    // Function to handle date change
    function handleDateChange(event: Event) {
        const target = event.target as HTMLSelectElement;
        selectedDate = new Date(target.value); // Update to create a Date object

        // Fetch sales data for the selected date
        fetchSalesData();
    }
  
    // New function to fetch sales data
    async function fetchSalesData() {
        const formattedDate = selectedDate.toLocaleDateString('en-US');
        console.log("Fetching sales data for date:", formattedDate);
        
        const apiUrl = `http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getReturnOrders&return_date=${formattedDate}`;
        console.log("API URL:", apiUrl); // Log the final API URL

        const response = await fetch(apiUrl);
        const data = await response.json();
        console.log("API Response:", data);

        if (Array.isArray(data) && data.length > 0) {
            noSalesData = false; // Reset noSalesData if data is found
            recentSales = data.map((sale) => {
                // Ensure the sale date matches the selected date
                if (new Date(sale.return_date).toLocaleDateString('en-US') === formattedDate) {
                    try {
                        // Log the raw items_ordered data to inspect its format
                        console.log("Raw Items Ordered Data:", sale.items_ordered);
                        
                        // Check if items_ordered is a valid JSON string
                        if (typeof sale.items_ordered === 'string' && sale.items_ordered.trim() !== '') {
                            // Replace escaped quotes and parse items_ordered
                            const itemsOrdered = JSON.parse(sale.items_ordered.replace(/\\/g, ''));
                            
                            // Check if itemsOrdered is an array
                            if (!Array.isArray(itemsOrdered)) {
                                throw new Error("Parsed itemsOrdered is not an array");
                            }

                            return {
                                receipt: sale.receipt_number,
                                items: itemsOrdered.map((item: { order_name: string }) => item.order_name).join(", "),
                                order_name2: itemsOrdered.map((item: { order_name2: string }) => item.order_name2).join(", "),
                                order_quantity: itemsOrdered.map((item: { order_quantity: string }) => item.order_quantity).join(", "),
                                order_size: itemsOrdered.map((item: { order_size: string }) => item.order_size).join(", "),
                                price: itemsOrdered.map((item: { price: string }) => item.price).join(", "),
                                totalCost: `₱${sale.total_amount}`,
                                payAmount: `₱${sale.amount_paid}`,
                                changeDue: `₱${sale.amount_change}`,
                                orderDate: sale.return_date,
                                orderTime: sale.return_time,
                                orderIn: sale.order_take,
                                name: sale.cashier_name,
                                totalDiscount: "₱0.00", // Adjust if you have discount data
                                items_ordered: sale.items_ordered,
                            };
                        } else {
                            throw new Error("Invalid items_ordered data: Not a valid JSON string");
                        }
                    } catch (error) {
                        console.error("Error parsing items_ordered:", error);
                        console.error("Invalid items_ordered data:", sale.items_ordered);
                        return null;
                    }
                }
                return null; // Return null if the date doesn't match
            }).filter(sale => sale !== null);
        } else if (data.message) {
            console.error("API Response Message:", data.message);
            noSalesData = true; // Set noSalesData to true if no sales data found
        } else {
            console.error("Unexpected API response:", data);
            noSalesData = true; // Handle unexpected responses
        }

        console.log("Recent Sales:", recentSales);

        await checkRemitExists(); // Check if remit exists after fetching sales data
    }
  
    onMount(() => {
        fetchSalesData(); // Fetch initial sales data on mount
    });
  
    // Function to calculate total sales
    function calculateTotalSales() {
        totalSales = recentSales.reduce((sum, sale) => {
            const amount = parseFloat(sale.totalCost.replace(/₱/, '')); // Remove currency symbol
            return sum + (isNaN(amount) ? 0 : amount);
        }, 0);
    }
  
    // Function to handle Remit button click
    function handleRemitClick() {
        calculateTotalSales(); // Calculate total sales
        showSecondPopup = true; // Show the popup
    }
  
    // Function to close the popup
    function closePopup() {
        showPopup = false;
        shortage = 0; // Reset shortage when closing the popup
    }
  
    // Function to close the second popup
    function closeSecondPopup() {
        showSecondPopup = false; // Close the second popup
    }
  
    // New function to handle remit confirmation
    function confirmRemit() {
        if (inputCode !== "123456") { // Check if the code is incorrect
            showAlert("Wrong code. Please try again.", "error"); // Show alert for incorrect code
            return; // Exit the function if the code is wrong
        }
        
        // Prepare data to send
        const returnData = {
            cashier_name: recentSales[0]?.name, // Assuming the cashier name is from the first sale
            total_sales: totalSales.toFixed(2), // Format total sales
            return_date: selectedDate.toISOString().split('T')[0], // Format date as YYYY-MM-DD
            return_time: new Date().toLocaleTimeString(), // Get current time
            return_validation: "Validated"
        };

        console.log("Return Data:", returnData); // Log remitData to check values

        // Send data to the backend
        fetch('http://localhost/kaperustiko-possystem/backend/modules/remit_returns.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(returnData),
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`); // Throw an error for non-2xx responses
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showAlert("Sales return successfully.", "success"); // Show alert for successful remit
                console.log("Return confirmed with code:", inputCode);
            } else {
                showAlert("Failed to return sales. Please try again.", "error");
                console.error("Error response:", data.message); // Log the error message
            }
        })
        .catch(error => {
            console.error("Fetch error:", error); // Log the fetch error
            showAlert("An error occurred. Please try again.", "error");
        });

        // Clear shortage from local storage
        localStorage.removeItem('shortage'); // Clear shortage from local storage

        closeSecondPopup(); // Close the second popup after confirmation
    }
  
    // Updated showAlert function to include a success message
    function showAlert(message: string, type: string) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `fixed top-0 left-1/2 transform -translate-x-1/2 mt-4 p-4 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white rounded shadow-lg`;
        alertDiv.innerText = message;
        document.body.appendChild(alertDiv);
        setTimeout(() => {
            alertDiv.remove();
        }, 3000); // Remove alert after 3 seconds
    }
  
    // New function to handle pending action
    function handlePendingClick() {
        showAlert("Sales has been moved to the pending queue.", "success"); // Show alert for pending action
        
        // Prepare data to send for pending action
        const pendingData = {
            cashier_name: recentSales[0]?.name, // Assuming the cashier name is from the first sale
            total_sales: totalSales.toFixed(2), // Format total sales
            return_date: selectedDate.toISOString().split('T')[0], // Format date as YYYY-MM-DD
            return_time: new Date().toLocaleTimeString(), // Get current time
            return_validation: "Pending" // Set validation status to Pending
        };

        console.log("Pending Data:", pendingData); // Log pendingData to check values

        // Send data to the backend for pending action
        fetch('http://localhost/kaperustiko-possystem/backend/modules/insert.php?action=remit_returns', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(pendingData),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert("Sales moved to pending successfully.", "success"); // Show alert for successful pending
            } else {
                showAlert("Failed to move sales to pending. Please try again.", "error");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            showAlert("An error occurred. Please try again.", "error");
        });

        // Clear shortage from local storage
        localStorage.removeItem('shortage'); // Clear shortage from local storage

        closeSecondPopup(); // Close the second popup after confirmation
    }
  
    function formatNumber(value: number): string {
        return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
  
    // Function to handle shortage input
    function handleShortageInput(event: Event) {
        const target = event.target as HTMLInputElement;
        const rawValue = target.value.replace(/,/g, ''); // Remove commas for parsing
        shortage = parseFloat(rawValue) || 0; // Update shortage

        // Format the input value with commas
        target.value = formatNumber(shortage);
    }
  
    // Function to load shortage from local storage on mount
    onMount(() => {
        const storedShortage = localStorage.getItem('shortage');
        if (storedShortage) {
            shortage = parseFloat(storedShortage); // Load shortage from local storage
        }
    });
  
    // Function to get the current shortage value
    function getShortageValue() {
        console.log("Current shortage value:", shortage); // Log the current shortage value
    }
  
    // New function to check if a remit exists for the selected date
    async function checkRemitExists() {
        const formattedDate = selectedDate.toISOString().split('T')[0]; // Format date as YYYY-MM-DD
        const apiUrl = `http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getRemitReturns&date=${formattedDate}`;
        
        const response = await fetch(apiUrl);
        const data = await response.json();

        // Check if any remit exists for the selected date
        isRemitDisabled = data.length > 0; // Disable remit if any records are found
    }
  
    // Function to handle Return button click
    function handleReturn(receipt: string) {
        selectedReceipt = receipt; // Store the selected receipt
        showReturnConfirmation = true; // Show the confirmation popup
    }
  
    // New function to confirm the return action
    function confirmReturn() {
        // Logic to handle the return of the sale
        console.log(`Returning sale with receipt: ${selectedReceipt}`);
        // Implement further return logic as needed

        showReturnConfirmation = false; // Close the confirmation popup
    }
  
    // New function to close the return confirmation popup
    function closeReturnConfirmation() {
        showReturnConfirmation = false; // Close the confirmation popup
    }
  </script>
  
  <div class="flex h-screen bg-gradient-to-b from-green-500 to-green-700">
    <!-- Sidebar Component -->
    <Sidebar />
  
    <!-- Main Content -->
    <div class="flex-grow p-4 bg-gray-100">
   
      <!-- Recent Sales Header -->
      <div class="flex justify-between items-center mb-2">
        <h2 class="text-2xl font-bold">Recent Returns</h2>
        <div>
          <select on:change={handleDateChange} class="bg-gray-800 text-white px-3 py-1 rounded shadow-md">
            {#each dateOptions as date}
                <option value={date} selected={date === selectedDate.toISOString().split('T')[0]}>{new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</option>
            {/each}
          </select>
          <button class="bg-gray-800 text-white px-3 py-1 rounded shadow-md ml-1" on:click={() => { selectedDate = new Date(); fetchSalesData(); }}>
            Recent Returns
          </button>
          <button class="bg-blue-600 text-white px-3 py-1 rounded shadow-md ml-1" on:click={handleRemitClick} disabled={isRemitDisabled}>
            Remit
          </button>
        </div>
      </div>
  
      <!-- Adjusted Sales Table -->
      <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <table class="w-full text-left table-fixed border-collapse">
          <thead class="bg-gray-800 text-white">
            <tr>
              <th class="p-2 text-center border-b">Receipt #</th>
              <th class="p-2 text-center border-b">Items</th>
              <th class="p-2 text-center border-b">Total Cost</th>
              <th class="p-2 text-center border-b">Pay Amount</th>
              <th class="p-2 text-center border-b">Change Due</th>
              <th class="p-2 text-center border-b">Order Date</th>
              <th class="p-2 text-center border-b">Order Time</th>
              <th class="p-2 text-center border-b">Order Type</th>
              <th class="p-2 text-center border-b">Cashier</th>

            </tr>
          </thead>
          <tbody class="bg-white">
            {#if noSalesData}
                <tr>
                    <td colspan="9" class="p-3 text-center">No return data found.</td>
                </tr>
            {:else if recentSales.length > 0}
                {#each recentSales as sale, i}
                    <tr class="border-t border-gray-300 {i % 2 === 0 ? 'bg-blue-50' : ''}">
                        <td class="p-2 text-center border-b">{sale.receipt}</td>
                        <td class="p-2 text-left border-b">
                            <ul class="list-none pl-5">
                                {#each JSON.parse(sale.items_ordered) as item}
                                    <li class="flex flex-col mb-1">
                                        <span class="font-semibold">{item.order_name} {item.order_name2}</span>
                                        <span class="text-gray-600 text-sm">{item.order_size} {item.order_quantity}</span>
                                        <ul class="list-disc pl-5 mt-1">
                                            {#if item.order_addons && item.order_addons !== 'None'}
                                                <li class="text-sm text-gray-600">{item.order_addons} ₱{item.order_addons_price}.00</li>
                                            {/if}
                                            {#if item.order_addons2 && item.order_addons2 !== 'None'}
                                                <li class="text-sm text-gray-600">{item.order_addons2} ₱{item.order_addons_price2}.00</li>
                                            {/if}
                                            {#if item.order_addons3 && item.order_addons3 !== 'None'}
                                                <li class="text-sm text-gray-600">{item.order_addons3} ₱{item.order_addons_price3}.00</li>
                                            {/if}
                                        </ul>
                                    </li>
                                {/each}
                            </ul>
                        </td>
                        <td class="p-2 text-center border-b">{sale.totalCost}.00</td>
                        <td class="p-2 text-center border-b">{sale.payAmount}.00</td>
                        <td class="p-2 text-center border-b">{sale.changeDue}.00</td>
                        <td class="p-2 text-center border-b">{new Date(sale.orderDate).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</td>
                        <td class="p-2 text-center border-b">{sale.orderTime}</td>
                        <td class="p-2 text-center border-b">{sale.orderIn}</td>
                        <td class="p-2 text-center border-b">{sale.name}</td>
                       
                    </tr>
                {/each}
            {:else}
                <tr>
                    <td colspan="9" class="p-3 text-center">No return data available.</td>
                </tr>
            {/if}
          </tbody>
        </table>
      </div>
  
      <!-- Popup for Second Sales -->
      {#if showSecondPopup}
          <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
              <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
                  <h3 class="text-xl font-bold text-gray-800">Input 6-Digit Code</h3>
                  <input
                      type="password"
                      bind:value={inputCode}
                      maxlength="6"
                      class="w-full rounded border border-gray-300 p-2 text-center"
                      placeholder="Enter 6-digit code"
                  />
                  <div class="flex justify-between mt-4 space-x-2">
                      <button class="w-full bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition duration-200" on:click={closeSecondPopup}>Cancel</button>
                      <button class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition duration-200" on:click={confirmRemit}>Confirm</button>
                      <button class="w-full bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 transition duration-200" on:click={handlePendingClick}>Pending</button>
                  </div>
              </div>
          </div>
      {/if}
  
      <!-- Popup for Return Confirmation -->
      {#if showReturnConfirmation}
          <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
              <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
                  <h3 class="text-xl font-bold text-gray-800">Confirm Return</h3>
                  <p class="mt-2 text-gray-600">Are you sure you want to return this item?</p>
                  <div class="flex justify-between mt-4 space-x-2">
                      <button class="w-full bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition duration-200" on:click={closeReturnConfirmation}>Cancel</button>
                      <button class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition duration-200" on:click={confirmReturn}>Confirm</button>
                  </div>
              </div>
          </div>
      {/if}
    </div>
  </div>
  