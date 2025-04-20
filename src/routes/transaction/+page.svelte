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
        waiterName?: string;
        serviceCharge?: number;
        cashier_shift?: string;
    };
  
    // Add this type definition near your Sale type
    type OrderItem = {
        order_name: string;
        order_name2: string;
        order_size: string;
        order_quantity: string;
    };
  
    let recentSales: Sale[] = [];
  
    let selectedDate: Date = new Date(); // Change to Date object
  
    let noSalesData: boolean = false; // New variable to track no sales data
  
    let showPopup: boolean = false; // New variable to control popup visibility
    let totalSales: number = 0; // Variable to store total sales
    let shortage: number = 0.00; // New variable to store shortage input
  
    let showSecondPopup: boolean = false; // New variable to control second popup visibility
  
    let isRemitDisabled: boolean = false; // New variable to track if remit is disabled
  
    // Add a new variable to control the return confirmation popup
    let showReturnConfirmation: boolean = false; // New variable for return confirmation
    let selectedReceipt: string | null = null; // Variable to store the selected receipt for return
  
    let showEndReport: boolean = false; // Add end report visibility
    let endReportData: any = {}; // Add end report data
  
    // Add new type for end report with detailed categories
    type FoodCategory = {
        appetizer: number;
        salad: number;
        riceMeal: number;
        steakAndSalmon: number;
        pasta: number;
        sandwich: number;
        pizza: number;
        soup: number;
        breakfast: number;
        sideDish: number;
        chicken: number;
        pork: number;
        beef: number;
        specialty: number;
        vegetables: number;
        fish: number;
    };

    type DrinksCategory = {
        frappe: number;
        icedCoffee: number;
        hotCoffee: number;
        soda: number;
        fruitShake: number;
        beverage: number;
        juice: number;
    };

  
    // Function to get all date options for sorting
    function getAllDateOptions() {
        const options: string[] = [];
        const startYear = 2024; // Set minimum year to 2024
        const startMonth = 3; // April (0-indexed)
        const startDay = 13; // Start from the 13th
        const today = new Date();
        const endYear = today.getFullYear(); // Current year

        for (let year = startYear; year <= endYear; year++) {
            for (let month = (year === startYear ? startMonth : 0); month < 12; month++) {
                const daysInMonth = new Date(year, month + 1, 0).getDate(); // Get the number of days in the month
                for (let day = (year === startYear && month === startMonth ? startDay : 1); day <= daysInMonth; day++) {
                    const date = new Date(year, month, day);
                    options.push(date.toISOString().split('T')[0]); // Format as YYYY-MM-DD
                    options.push(date.toISOString().split('T')[0]); // Add evening option
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
        selectedSalesCode = target.value; // Update selectedSalesCode directly from the dropdown
        const selectedOption = salesCodes.find(code => code.sales_code === selectedSalesCode);
        if (selectedOption) {
            selectedDate = new Date(selectedOption.date); // Update selectedDate based on the selected sales code
        }
        fetchSalesData(); // Fetch sales data for the selected date and sales code
    }
  
    // Function to fetch sales data
    async function fetchSalesData() {
        if (!selectedSalesCode) {
            console.error("No sales code selected.");
            return; // Exit if no sales code is selected
        }

        // Format the date to M/D/YYYY (without leading zero for month)
        const formattedDate = (selectedDate.getMonth() + 1).toString() + '/' + 
                              selectedDate.getDate().toString() + '/' + 
                              selectedDate.getFullYear();
        
        
        const apiUrl = `http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getSalesInformationByCodeAndDate&sales_code=${selectedSalesCode}&date=${formattedDate}`;
        console.log("API URL:", apiUrl);

        const response = await fetch(apiUrl);
        const data = await response.json();

        if (Array.isArray(data) && data.length > 0) {
            noSalesData = false;
            
            // Process all sales data
            recentSales = data.map((sale) => {
                if (new Date(sale.date).toLocaleDateString('en-US') === formattedDate) {
                    try {
                        const itemsOrdered = JSON.parse(sale.items_ordered.replace(/\\/g, ''));
                        
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
                            orderDate: sale.date,
                            orderTime: sale.time,
                            orderIn: sale.order_take,
                            name: sale.cashier_name,
                            totalDiscount: "₱0.00",
                            items_ordered: sale.items_ordered,
                            waiterName: sale.waiter_name,
                            serviceCharge: sale.service_charge,
                            cashier_shift: sale.cashier_shift,
                        };
                    } catch (error) {
                        console.error("Error parsing items_ordered:", error);
                        console.error("Invalid items_ordered data:", sale.items_ordered);
                        return null;
                    }
                }
                return null;
            }).filter(sale => sale !== null);
        } else if (data.message) {
            console.error("API Response Message:", data.message);
            noSalesData = true;
        } else {
            console.error("Unexpected API response:", data);
            noSalesData = true;
        }

        await checkRemitExists();
    }
  
    onMount(() => {
        fetchSalesData(); // Fetch initial sales data on mount
        const intervalId = setInterval(fetchSalesData, 1000); // Update sales data every second

        return () => clearInterval(intervalId); // Clear interval on component unmount
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
        calculateTotalSales();
        showPopup = true;
    }
  
    // Function to close the popup
    function closePopup() {
        showPopup = false;
        shortage = 0; // Reset shortage when closing the popup
    }
  
    // Function to handle Submit button click
    function handleSubmitClick() {
        // Save the shortage value in local storage when submitting
        localStorage.setItem('shortage', shortage.toString()); // Save as string

        // Log the shortage value to the console
        console.log("Shortage value saved to local storage:", shortage);

        closePopup(); // Close the first popup
        confirmRemit(); // Call confirmRemit to process the remit
    }
  
    // Function to close the second popup
    function closeSecondPopup() {
        showSecondPopup = false; // Close the second popup
    }
  
    // Update the confirmRemit function
    function confirmRemit() {
        // Send data to the here to insert on remit_sales
        const apiUrl = `http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getTotalSalesByCode&sales_code=${selectedSalesCode}`;
 
        // Prepare data to send
        const remitData = {
            cashier_name: recentSales[0]?.name || "Unknown", // Cashier name
            total_sales: Array.isArray(recentSales) ? recentSales.reduce((sum, sale) => {
                const amount = parseFloat(sale.totalCost.replace(/₱/, '')); // Remove currency
                return sum + (isNaN(amount) ? 0 : amount); // Handle NaN
            }, 0).toFixed(2) : "0.00", // Provide initial value of 0
            remit_date: selectedDate.toISOString().split('T')[0],
            remit_time: new Date().toLocaleTimeString(),
            remit_shortage: (localStorage.getItem('shortage') || "0").toString(),
            remit_validation: "Validated",
            food_summary: JSON.stringify(recentSales.map(sale => ({
                receipt_number: sale.receipt,
                items_ordered: sale.items_ordered,
                total_cost: sale.totalCost,
                service_charge: sale.serviceCharge
            }))),
            service_charge: Array.isArray(recentSales) ? recentSales.reduce((sum, sale) => {
                const charge = sale.serviceCharge || 0; // Default to 0 if undefined
                return sum + charge;
            }, 0) : 0, // Provide initial value of 0
            remit_code: selectedSalesCode, // Add this line to include remit_code
            cashier_shift: recentSales[0]?.cashier_shift || "Unknown" // Cashier shift
        };

        console.log("Remit Data:", remitData);

        // Send data to the backend
        fetch('http://localhost/kaperustiko-possystem/backend/modules/remit_sales.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(remitData),
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showAlert("Sales remit successfully.", "success");
                console.log("Remit confirmed with code:");
                isRemitDisabled = true;
                
                // Clear the recent sales display but keep data for end-of-day report
                recentSales = [];
                noSalesData = true;
            } else {
                showAlert("Sales remit successfully.", "success");
                console.error("Error response:", data.message);
            }
        })
        .catch(error => {
            console.error("Error:", error);
            showAlert("An error occurred. Please try again.", "error");
        });

        localStorage.removeItem('shortage');
        closeSecondPopup();
    }
  
    // Function to calculate subtotal
    function calculateSubtotal() {
        return recentSales.reduce((sum, sale) => {
            const amount = parseFloat(sale.totalCost.replace(/₱/, '')); // Remove currency symbol
            return sum + (isNaN(amount) ? 0 : amount);
        }, 0);
    }
  
    // Updated showAlert function to include a success message
    function showAlert(message: string, type: string) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `fixed top-0 left-1/2 transform -translate-x-1/2 mt-4 p-4 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white rounded shadow-lg z-50`;
        alertDiv.innerText = message;
        document.body.appendChild(alertDiv);
        setTimeout(() => {
            alertDiv.remove();
        }, 3000); // Remove alert after 3 seconds
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
  
  
    // New function to check if both shifts have remitted for the selected date
    async function checkRemitExists() {
        const formattedDate = selectedDate.toLocaleDateString('en-US');
        const apiUrl = `http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getRemitSales&remit_code=${selectedSalesCode}`;
        
        const response = await fetch(apiUrl);
        const data = await response.json();

        // Check if both shifts have remitted
        if (data.length > 0) {
            // Count remits for each shift
            let morningRemits = 0;
            let nightRemits = 0;

            data.forEach((remit: any) => {
                const remitHour = new Date(`${remit.remit_date} ${remit.remit_time}`).getHours();
                if (remitHour >= 6 && remitHour < 14) {
                    morningRemits++;
                } else {
                    nightRemits++;
                }
            });

            // Disable remit if either shift has already remitted
            isRemitDisabled = morningRemits > 0 || nightRemits > 0;
        } else {
            isRemitDisabled = false;
        }
    }
  
    // Function to handle Return button click
    function handleReturn(receipt: string) {
        const saleToReturn = recentSales.find(sale => sale.receipt === receipt); // Find the selected sale
        if (saleToReturn) {
            selectedReceipt = saleToReturn.receipt; // Store the selected receipt for confirmation
            showReturnConfirmation = true; // Show return confirmation popup
        }
    }

    // New function to delete the sale from total_sales
    function deleteSale(receipt: string) {
        fetch('http://localhost/kaperustiko-possystem/backend/modules/delete.php?action=deleteSalesInformation', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ receipt_number: receipt }),
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`); // Log HTTP errors
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Refresh the sales data after deletion
                fetchSalesData();
            } else {
                console.error("Failed to delete sale:", data.message);
            }
        })
        .catch(error => {
            console.error("Error deleting sale:", error);
        });
    }
  
    // New function to confirm the return action
    async function confirmReturn() {
        const saleToReturn = recentSales.find(sale => sale.receipt === selectedReceipt); // Find the selected sale
        if (saleToReturn) {
            const returnData = {
                action: 'return_order',
                receipt_number: saleToReturn.receipt,
                return_date: saleToReturn.orderDate,
                return_time: saleToReturn.orderTime,
                cashier_name: saleToReturn.name,
                items_ordered: saleToReturn.items_ordered,
                total_amount: saleToReturn.totalCost.replace(/₱/, ''), // Remove currency symbol
                amount_paid: saleToReturn.payAmount.replace(/₱/, ''), // Remove currency symbol
                amount_change: saleToReturn.changeDue.replace(/₱/, ''), // Remove currency symbol
                order_take: saleToReturn.orderIn,
            };

            try {
                const response = await fetch('http://localhost/kaperustiko-possystem/backend/modules/insert.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(returnData),
                });

                const data = await response.json();
                if (data.success) {
                    // Check if selectedReceipt is not null before calling deleteSale
                    if (selectedReceipt) { 
                        deleteSale(selectedReceipt); // Call deleteSale to remove the sale from the database
                    }
                    showAlert("Return processed successfully.", "success"); // Show success alert
                    location.reload(); // Reload the page after successful return
                } else {
                    console.error("Failed to process return:", data.message);
                }
            } catch (error) {
                console.error("Error:", error);
                showAlert("An error occurred. Please try again.", "error");
            }
        }

        showReturnConfirmation = false; // Close the confirmation popup
    }
  
    // New function to close the return confirmation popup
    function closeReturnConfirmation() {
        showReturnConfirmation = false; // Close the confirmation popup
    }

    async function generateEndReport(): Promise<void> {
        try {
            const morningShift = {
                start: '06:00:00',
                end: '14:00:00'
            };

            const nightShift = {
                start: '14:00:00',
                end: '22:00:00'
            };

            // Get all sales for the selected date
            const formattedDate = selectedDate.toLocaleDateString('en-US');
            console.log("Formatted Date:", formattedDate);
            const response = await fetch(`http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getSalesInformationByDate&date=${formattedDate}`);
            const salesData = await response.json();
            console.log("Sales Data:", salesData); // Log the fetched sales data

            // Initialize counters
            let totalSales = 0;
            let totalShortage = 0;
            let totalFood = 0;
            let totalCoffee = 0;
            let foodItems: { [key: string]: number } = {};
            let morningTotalSales = 0; // New variable for morning total sales
            let nightTotalSales = 0; // New variable for night total sales

            // Process morning shift
            const morningSales = salesData.filter((sale: any) => {
                return sale.cashier_shift === 'morning'; // Check for morning shift
            });

            // Calculate total sales for morning shift
            morningTotalSales = morningSales.reduce((sum: number, sale: any) => sum + parseFloat(sale.total_amount), 0);

            // Process night shift
            const nightSales = salesData.filter((sale: any) => {
                return sale.cashier_shift === 'night'; // Check for night shift
            });

            // Calculate total sales for night shift
            nightTotalSales = nightSales.reduce((sum: number, sale: any) => sum + parseFloat(sale.total_amount), 0);

            // Process all sales to count food items
            salesData.forEach((sale: any) => {
                try {
                    // Clean and parse the items_ordered string
                    let itemsString = sale.items_ordered;
                    // Remove any backslashes
                    itemsString = itemsString.replace(/\\/g, '');
                    // Remove any extra quotes
                    itemsString = itemsString.replace(/^"|"$/g, '');
                    
                    const items = JSON.parse(itemsString);
                    
                    if (Array.isArray(items)) {
                        items.forEach((item: any) => {
                            // Parse quantity from "x2" format
                            const quantity = parseInt(item.order_quantity.replace('x', '')) || 1;
                            const itemName = `${item.order_name}`.trim();
                            
                            if (!foodItems[itemName]) {
                                foodItems[itemName] = 0;
                            }
                            foodItems[itemName] += quantity;

                            if (itemName.toLowerCase().includes('coffee')) {
                                totalCoffee += quantity;
                            } else {
                                totalFood += quantity;
                            }
                        });

                        // Add to total sales
                        totalSales += parseFloat(sale.total_amount);
                    }
                } catch (error) {
                    console.error('Error processing sale:', error);
                    console.error('Problematic items_ordered:', sale.items_ordered);
                }
            });

            // Create the report
            const report = {
                date: selectedDate,
                morningShift: {
                    sales: morningTotalSales, // Use calculated morning total sales
                    transactions: morningSales.length
                },
                nightShift: {
                    sales: nightTotalSales, // Use calculated night total sales
                    transactions: nightSales.length
                },
                totalSales,
                totalFood,
                totalCoffee,
                foodItems,
                grandTotal: totalSales - totalShortage
            };

            // Save report values in local storage
            localStorage.setItem('endReport', JSON.stringify(report)); // Save the entire report

            // Show the report in a popup
            const reportHtml = `
                <div class="p-6 bg-white rounded-lg shadow-lg max-w-2xl mx-auto">
                    <h2 class="text-3xl font-bold mb-6 text-center text-gray-800">End of Day Report - ${selectedDate.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</h2>
                    
                    <!-- Shift Summary Table -->
                    <div class="mb-6">
                        <h3 class="text-2xl font-semibold text-gray-700">Shift Summary</h3>
                        <table class="min-w-full border-collapse bg-gray-100 rounded-lg overflow-hidden mt-2">
                            <thead class="bg-gray-200">
                                <tr>
                                    <th class="p-3 text-center border-b">Shift</th>
                                    <th class="p-3 text-center border-b">Sales</th>
                                    <th class="p-3 text-center border-b">Transactions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="p-3 text-center border-b">Morning Shift Duty</td>
                                    <td class="p-3 text-center border-b text-green-600">₱${report.morningShift.sales.toFixed(2)}</td>
                                    <td class="p-3 text-center border-b text-green-600">${report.morningShift.transactions}</td>
                                </tr>
                                <tr>
                                    <td class="p-3 text-center border-b">Night Shift Duty</td>
                                    <td class="p-3 text-center border-b text-green-600">₱${report.nightShift.sales.toFixed(2)}</td>
                                    <td class="p-3 text-center border-b text-green-600">${report.nightShift.transactions}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Food Items Summary Table -->
                    <div class="mb-6">
                        <h3 class="text-2xl font-semibold text-gray-700">Food Items Summary</h3>
                        <table class="min-w-full border-collapse bg-gray-100 rounded-lg overflow-hidden mt-2">
                            <thead class="bg-gray-200">
                                <tr>
                                    <th class="p-3 text-left border-b">Item Name</th>
                                    <th class="p-3 text-center border-b">Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${Object.entries(report.foodItems)
                                    .map(([name, quantity]) => `
                                        <tr class="hover:bg-gray-50">
                                            <td class="p-3 border-b text-gray-800">${name}</td>
                                            <td class="p-3 text-center border-b text-gray-800">x${quantity}</td>
                                        </tr>
                                    `).join('')}
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals Table -->
                    <div class="mb-6">
                        <h3 class="text-2xl font-semibold text-gray-700">Totals</h3>
                        <table class="min-w-full border-collapse bg-gray-100 rounded-lg overflow-hidden mt-2">
                            <tbody>
                                <tr>
                                    <td class="p-3 border-b">Total Sales:</td>
                                    <td class="text-right p-3 border-b font-bold text-green-600">₱${report.totalSales.toFixed(2)}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-center mt-4">
                        <button class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-200 text-lg" onclick="this.closest('.fixed').remove()">
                            Close
                        </button>
                    </div>
                </div>
            `;

            // Create and show the popup
            const popup = document.createElement('div');
            popup.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
            popup.innerHTML = `
                <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                    ${reportHtml}
                </div>
            `;
            document.body.appendChild(popup);
        } catch (error) {
            console.error('Error generating end report:', error);
            alert('Error generating end report. Please try again.');
        }
    }

  
    let showNewShiftForm: boolean = false; // New variable to control the visibility of the new shift form
    let selectedShift: 'morning' | 'night' = 'morning'; // Variable to track the selected shift

    // Function to handle New Shift button click
    function handleNewShift() {
        showNewShiftForm = true; // Show the new shift form
    }

    // Function to submit the new shift
    function submitNewShift() {
        // Remove existing shift and code if they exist
        localStorage.removeItem('selectedShift');
        localStorage.removeItem('shiftCode');

        // Generate a random code
        const randomCode = Math.random().toString(36).substring(2, 8); // Generate a random alphanumeric code

        // Save the selected shift and random code to local storage
        localStorage.setItem('selectedShift', selectedShift);
        localStorage.setItem('shiftCode', randomCode);

        // Log the selected shift and generated code
        console.log("New shift submitted:", selectedShift, "with code:", randomCode);

        showNewShiftForm = false; // Close the form after submission
    }

    let salesCodes: { sales_code: string; date: string }[] = []; // New variable to store sales codes
    let selectedSalesCode: string | null = null; // Variable to track selected sales code

    // New function to fetch sales codes and dates
    async function fetchSalesCodes() {
        const response = await fetch('http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getAllTotalSales');
        const data = await response.json();
        
        if (Array.isArray(data)) {
            salesCodes = data.map((item: { sales_code: string; date: string }) => ({
                sales_code: item.sales_code,
                date: item.date // Ensure date is included
            })); // Map to the correct type
        } else {
            console.error("Unexpected API response:", data);
        }

        if (salesCodes.length > 0) {
            selectedSalesCode = salesCodes[salesCodes.length - 1].sales_code; // Assuming salesCodes are sorted by date
        }
    }

    // Call fetchSalesCodes on component mount
    onMount(() => {
        fetchSalesCodes(); // Fetch sales codes on mount
        fetchSalesData(); // Fetch initial sales data on mount
        const intervalId = setInterval(fetchSalesData, 1000); // Update sales data every second

        return () => clearInterval(intervalId); // Clear interval on component unmount
    });

    // New function to export the end report to Excel
    async function exportData() {
        // Retrieve the report data from localStorage
        const reportData = localStorage.getItem('endReport');
        
        // Log the fetched report data
        console.log("Fetched report data from localStorage:", reportData);
        
        // Check if reportData exists
        if (!reportData) {
            console.error("No report data found in localStorage.");
            return;
        }

        // Parse the report data
        const parsedReportData = JSON.parse(reportData);

        // Ensure the structure matches what the backend expects
        const dataToSend = {
            date: parsedReportData.date,
            report: {
                foodItems: parsedReportData.foodItems,
                totalSales: parsedReportData.totalSales,
            }
        };

        const response = await fetch('http://localhost/kaperustiko-possystem/backend/modules/export_today_reports.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(dataToSend), // Send the structured data
        });

        if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = `End_of_Day_Report_${parsedReportData.date}.xlsx`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
        } else {
            console.error("Failed to export data");
        }
    }

    function formatTime(time: string): string {
        const [hours, minutes, seconds] = time.split(':');
        const formattedHours = parseInt(hours) % 12 || 12;
        const formattedMinutes = minutes;
        const formattedSeconds = seconds;
        const ampm = parseInt(hours) >= 12 ? 'PM' : 'AM';
        return `${formattedHours}:${formattedMinutes}:${formattedSeconds} ${ampm}`;
    }

</script>
  
<div class="flex h-screen bg-gradient-to-b from-green-500 to-green-700">
    <!-- Sidebar Component -->
    <Sidebar />
  
    <!-- Main Content -->
    <div class="flex-grow p-4 bg-gray-100 overflow-auto">
   
      <!-- Recent Sales Header -->
      <div class="flex justify-between items-center mb-2">
        <h2 class="text-2xl font-bold">Recent Sales</h2>
        <div>
            {#if salesCodes.length > 0}
                <select bind:value={selectedSalesCode} class="bg-white border border-gray-300 rounded shadow-md ml-1" on:change={handleDateChange}>
                    <option value="" disabled>Select Sales Code</option>
                    {#each salesCodes as { sales_code, date }}
                        <option value={sales_code} selected={sales_code === selectedSalesCode && date === selectedDate.toISOString().split('T')[0]}>{date} - {sales_code}</option>
                    {/each}
                </select>
            {/if}
            <button class="bg-blue-600 text-white px-3 py-1 rounded shadow-md ml-1" on:click={handleRemitClick} disabled={isRemitDisabled}>
                Remit
            </button>
            <button class="bg-green-600 text-white px-3 py-1 rounded shadow-md ml-1" on:click={generateEndReport}>
                End Report
            </button>
            <button class="bg-yellow-500 text-white px-3 py-1 rounded shadow-md ml-1" on:click={handleNewShift}>
                New Shift
            </button>
            <button class="bg-purple-600 text-white px-3 py-1 rounded shadow-md ml-1" on:click={exportData}>
                <i class="fas fa-file-export mr-1"></i> Export
            </button>
        </div>
      </div>
  
      <!-- Adjusted Sales Table -->
      <div class="bg-white rounded-lg shadow-lg overflow-auto">
        <table class="w-full text-left table-fixed border-collapse">
          <thead class="bg-gray-800 text-white">
            <tr>
              <th class="p-3 text-center border-b">Receipt #</th>
              <th class="p-3 text-center border-b">Items</th>
              <th class="p-3 text-center border-b">Total Cost</th>
              <th class="p-3 text-center border-b">Service Charge</th>
              <th class="p-3 text-center border-b">Pay Amount</th>
              <th class="p-3 text-center border-b">Change Due</th>
              <th class="p-3 text-center border-b">Order Date</th>
              <th class="p-3 text-center border-b">Order Time</th>
              <th class="p-3 text-center border-b">Order Type</th>
              <th class="p-3 text-center border-b">Cashier</th>
              <th class="p-3 text-center border-b">Waiter</th>
              <th class="p-3 text-center border-b">Return</th>
            </tr>
          </thead>
          <tbody class="bg-white">
            {#if noSalesData}
                <tr>
                    <td colspan="12" class="p-3 text-center">No sales data found.</td>
                </tr>
            {:else if recentSales.length > 0}
                {#each recentSales as sale, i}
                    <tr class="border-t border-gray-300 hover:bg-gray-100 transition duration-200 {i % 2 === 0 ? 'bg-gray-50' : ''}">
                        <td class="p-3 text-center border-b">{sale.receipt}</td>
                        <td class="p-3 text-left border-b">
                            <ul class="list-none pl-5">
                                {#each JSON.parse(sale.items_ordered) as item}
                                    <li class="flex flex-col mb-1">
                                        <span class="font-semibold">{item.order_name}</span>
                                        <span class="text-gray-600 text-sm">Quantity: {item.order_quantity}</span>
                                        <ul class="list-disc pl-5 mt-1">
                                            {#if item.order_addons && item.order_addons !== 'None'}
                                                <li class="text-sm text-gray-600">{item.order_addons} ₱{item.order_addons_price}.00</li>
                                            {/if}
                                            {#if item.order_addons2 && item.order_addons2 !== 'None'}
                                                <li class="text-sm text-gray-600">{item.order_addons2} ₱{item.order_addons_price2}.00</li>
                                            {/if}
                                            {#if item.order_addons3 && item.order_addons3 !== 'None'}
                                                <li class="text-sm text-gray-600">{item.order_addons3} c{item.order_addons_price3}.00</li>
                                            {/if}
                                        </ul>
                                    </li>
                                {/each}
                            </ul>
                        </td>
                        <td class="p-3 text-center border-b">{sale.totalCost}</td>
                        <td class="p-3 text-center border-b">₱{sale.serviceCharge}</td>
                        <td class="p-3 text-center border-b">{sale.payAmount}</td>
                        <td class="p-3 text-center border-b">{sale.changeDue}</td>
                        <td class="p-3 text-center border-b">{new Date(sale.orderDate).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</td>
                        <td class="p-3 text-center border-b">{formatTime(sale.orderTime)}</td>
                        <td class="p-3 text-center border-b">{sale.orderIn}</td>
                        <td class="p-3 text-center border-b">{sale.name}</td>
                        <td class="p-3 text-center border-b">{sale.waiterName}</td>
                        <td class="p-3 text-center border-b">
                            <button class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 transition duration-200" on:click={() => handleReturn(sale.receipt)}>Return</button>
                        </td>
                    </tr>
                {/each}
            {:else}
                <tr>
                    <td colspan="12" class="p-3 text-center">No sales data available.</td>
                </tr>
            {/if}
          </tbody>
        </table>
      </div>
  
      <!-- Popup for Total Sales -->
      {#if showPopup}
          <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
              <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
                  <h3 class="text-xl font-bold text-gray-800">Remit Sales</h3>
                  <p class="mt-2 text-gray-600">Total Sales for {selectedDate.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}: <span class="font-semibold">₱{totalSales.toFixed(2)}</span></p>
                  
                  <!-- Input for Shortage with Peso Sign -->
                  <div class="mt-4">
                      <label for="shortage" class="block text-sm font-medium text-gray-700">Shortage:</label>
                      <div class="flex items-center border border-gray-300 rounded-md">
                          <span class="text-gray-700 px-2 mt-1">₱</span>
                          <input type="text" id="shortage" bind:value={shortage} on:input={handleShortageInput} class="mt-1 block w-full border-0 focus:ring-0 focus:outline-none p-2" placeholder="Enter shortage amount" />
                      </div>
                  </div>
                  
                  <button class="mt-4 w-full bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition duration-200" on:click={closePopup}>Close</button>
                  <button class="mt-4 w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition duration-200" on:click={handleSubmitClick}>Submit</button>
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

      <!-- New Shift Form Popup -->
      {#if showNewShiftForm}
          <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
              <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
                  <p class="text-gray-600 text-center mb-4">Please select the shift type:</p>
                  <div class="flex justify-center mb-4">
                      <div class="flex items-center">
                          <button 
                              class={`px-6 py-3 text-lg rounded-l ${selectedShift === 'morning' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700'}`} 
                              on:click={() => selectedShift = 'morning'}>
                              Morning Shift
                          </button>
                          <button 
                              class={`px-6 py-3 text-lg rounded-r ${selectedShift === 'night' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700'}`} 
                              on:click={() => selectedShift = 'night'}>
                              Night Shift
                          </button>
                      </div>
                  </div>
                  <div class="flex justify-between mt-4 space-x-2">
                      <button class="w-full bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition duration-200 flex items-center justify-center" on:click={() => showNewShiftForm = false}>Cancel
                      </button>
                      <button class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition duration-200 flex items-center justify-center" on:click={submitNewShift}>Submit
                      </button>
                  </div>
              </div>
          </div>
      {/if}

    </div>
  </div>
  