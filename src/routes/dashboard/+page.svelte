<script lang="ts">
    import { onMount } from "svelte";
    import { FontAwesomeIcon } from "@fortawesome/svelte-fontawesome";
    import { faStar, faUndo, faExclamationTriangle, faTrash, faDollarSign, faPrint, faArrowDown, faChartBar, faTrophy, faExclamationCircle, faTrashAlt, faUser, faFileAlt, faCalendar } from "@fortawesome/free-solid-svg-icons";
    import Sidebar from "../sidebar/+page.svelte";
    import { Chart, LineController, LineElement, PointElement, LinearScale, Title, Tooltip, Legend, CategoryScale, Filler, BarController, BarElement, ArcElement, DoughnutController } from 'chart.js';

    // Register the necessary components
    Chart.register(
        LineController,
        LineElement,
        PointElement,
        LinearScale,
        Title,
        Tooltip,
        Legend,
        CategoryScale,
        Filler,
        BarController,
        BarElement,
        ArcElement,
        DoughnutController
    );

    let salesRemitItems: any[] = [];
    let returnItems: any[] = [];
    let orderTakesData: number[] = []; // Declare orderTakesData

    let totalSalesToday = 0; // Variable to store today's total sales
    let overallTotalSales: number = 0; // Variable to store overall total sales
    let bestSeller: string = "Loading..."; // Initialize bestSeller
    let leastSeller: string = "Loading..."; // Initialize leastSeller
    let todayShortageCost = 0; // Variable to store today's shortage cost
    let showDeleteRemitModal = false;
    let showDeleteReturnModal = false;
    let itemToDelete: number | null = null;
    let waiterOrderCounts: { firstName: string; lastName: string; order_count: number }[] = []; // Update the type
    let leadingStaff: { firstName: string; lastName: string } | null = null; // Variable to store leading staff data
    let showPopup = false; // State to control popup visibility
    let popupData: any = null; // State to hold data for the popup

    // Add Z-Report related variables
    let showZReportModal = false; // Control Z-report modal visibility
    let zReportDate = new Date();
    let zReportDateString = new Date().toISOString().split('T')[0]; // Default to today's date
    let zReportData: any = null; // Data for Z-report
    let isLoadingZReport = false; // Loading state for Z-report
    let zReportError: string | null = null; // Error message for Z-report

    // Utility function to safely format numbers
    function safeNumberFormat(value: any, decimals: number = 2): string {
        if (value === null || value === undefined) return '0.00';
        const num = Number(value);
        return isNaN(num) ? '0.00' : num.toFixed(decimals);
    }

    // Function to format date for input field (YYYY-MM-DD)
    function formatDateForInput(date: Date): string {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    onMount(async () => {
        const today = new Date();
        const formattedDate = `${today.getMonth() + 1}/${today.getDate()}/${today.getFullYear()}`; // Format date as MM/DD/YYYY
        const response = await fetch(`http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getTodaySales&date=${formattedDate}`); // Use formatted date in the URL
        const data = await response.json();
        if (data.length > 0 && data[0].total_amount) { // Adjusted to check the correct structure of the response
            totalSalesToday = data[0].total_amount; // Update the total sales for today
        }

        // Fetch all sales remit items from the backend
        const responseRemit = await fetch('http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getRemitSales');
        const remitItems = await responseRemit.json();
        salesRemitItems = remitItems; // Store fetched data in salesRemitItems

        const responseReturn = await fetch('http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getRemitReturns');
        const fetchedReturnItems = await responseReturn.json(); // Fetch return items
        returnItems = fetchedReturnItems; // Store fetched data in returnItems

        try {
            const response = await fetch('http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getTotalSales', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
            });
            const data = await response.json();
            if (data.total_sales !== null) {
                overallTotalSales = data.total_sales; // Update the total sales variable
            }
        } catch (error) {
            console.error("Error fetching total sales:", error);
        }

        try {
            const response = await fetch('http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getBestsellers', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
            });
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            console.log("Best seller data:", data);
            bestSeller = data.length > 0 ? data[0] : "No sales yet"; // Update bestSeller
        } catch (error) {
            console.error("Error fetching best seller:", error);
            bestSeller = "Error fetching data"; // Handle error
        }

        // Fetch least ordered item
        try {
            const responseLeastSeller = await fetch('http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getLeastsellers', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
            });
            const data = await responseLeastSeller.json();
            leastSeller = data.length > 0 ? data[0] : "No orders yet"; // Update leastSeller
        } catch (error) {
            console.error("Error fetching least seller:", error);
            leastSeller = "Error fetching data"; // Handle error
        }

        // Fetch waiter order counts from your backend
        const responseWaiterOrderCounts = await fetch('http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getWaitersWithOrderCounts');
        const dataWaiterOrderCounts = await responseWaiterOrderCounts.json();
        waiterOrderCounts = dataWaiterOrderCounts.sort((a: { order_count: number }, b: { order_count: number }) => b.order_count - a.order_count); // Sort in descending order

        // Fetch leading staff data
        try {
            const response = await fetch('http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getLeadingStaff');
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            leadingStaff = await response.json(); // Store the leading staff data
        } catch (error) {
            console.error("Error fetching leading staff:", error);
            leadingStaff = null; // Handle error
        }

        // Order Takes data labeled by hour
        const orderLabels = ['7 AM', '8 AM', '9 AM', '10 AM', '11 AM', '12 PM', '1 PM', '2 PM', '3 PM', '4 PM', '5 PM', '6 PM', '7 PM', '8 PM', '9 PM', '10 PM', '11 PM'];

        // Fetch order counts by hour
        try {
            const response = await fetch('http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getOrderTime');
            const orderCounts = await response.json();
            console.log("Fetched order counts:", orderCounts); // Log the fetched data
            
            // Map the order counts to the orderTakesData array
            orderTakesData = orderLabels.map((label, index) => {
                const hourIndex = index + 7; // Adjust index to match 24-hour format starting from 7 AM
                return orderCounts[hourIndex] || 0; // Use the correct hour index
            });
        } catch (error) {
            console.error("Error fetching order counts by hour:", error);
        }

        // Initialize the sales chart
        const canvas = document.getElementById('salesChart') as HTMLCanvasElement;
        const ctx = canvas?.getContext('2d');

        if (ctx) {
            const salesChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: orderLabels,
                    datasets: [{
                        label: 'Order Takes',
                        data: orderTakesData,
                        borderColor: 'rgba(21, 94, 117, 1)',       
                        backgroundColor: 'rgba(8, 51, 68, 0.3)',   
                        borderWidth: 4,
                        pointRadius: 5,
                        fill: true,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        tooltip: {
                            enabled: true,
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(tooltipItem) {
                                    return `${tooltipItem.raw} orders`;
                                }
                            }
                        },
                        legend: {
                            labels: {
                                color: 'black'
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)',
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)',
                            }
                        }
                    }
                }
            });
        } else {
            console.error("Failed to get canvas context");
        }

         // Sales data from Monday to Sunday
        const salesLabels = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        const salesData = [5000, 7500, 1000, 6000, 9000, 12000, 8000]; // Sample sales data for each day


        // Initialize the sales bar chart
        const barCanvas = document.getElementById('salesBarChart') as HTMLCanvasElement;
        const barCtx = barCanvas?.getContext('2d');

        if (barCtx) {
            const salesBarChart = new Chart(barCtx, {
                type: 'bar', // Changed to bar chart
                data: {
                    labels: salesLabels, // Use the same labels
                    datasets: [{
                        label: 'Sales',
                        data: salesData, // Use the same sales data
                        borderColor: 'rgba(21, 94, 117, 1)',       
                        backgroundColor: 'rgba(8, 51, 68, 0.3)',  
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        tooltip: {
                            enabled: true,
                            callbacks: {
                                label: function(tooltipItem) {
                                    return `${tooltipItem.raw} sales`;
                                }
                            }
                        },
                        legend: {
                            labels: {
                                color: 'black'
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)',
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)',
                            }
                        }
                    }
                }
            });
        } else {
            console.error("Failed to get canvas context for bar chart");
        }

        // Sample data for pie chart
        const pieChartData = {
            labels: ['Takeout', 'Dine-in'],
            datasets: [{
                data: [30, 70], // Example data
                backgroundColor: ['rgba(75, 192, 192, 1)', 'rgba(255, 99, 132, 1)'],
                hoverOffset: 4
            }]
        };

        console.log(pieChartData);

        // Initialize the customers pie chart
        const pieCanvas = document.getElementById('customersPieChart') as HTMLCanvasElement;
        const pieCtx = pieCanvas?.getContext('2d');

        if (pieCtx) {
            const customersPieChart = new Chart(pieCtx, {
                type: 'doughnut', // Use 'doughnut' for the pie chart
                data: pieChartData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                color: 'black'
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(tooltipItem) {
                                    return `${tooltipItem.label}: ${tooltipItem.raw}`;
                                }
                            }
                        }
                    }
                }
            });
        } else {
            console.error("Failed to get canvas context for pie chart");
        }
    });

    function printPage() {
        window.print();
    }

    // Modify the delete functions to show confirmation first
    function confirmDeleteRemit(remit_id: number) {
        itemToDelete = remit_id;
        showDeleteRemitModal = true;
    }

    function confirmDeleteReturn(return_id: number) {
        itemToDelete = return_id;
        showDeleteReturnModal = true;
    }

    // Existing delete functions become the actual delete operations
    async function deleteRemit(remit_id: number) {
        try {
            const response = await fetch(`http://localhost/kaperustiko-possystem/backend/modules/delete.php?action=deleteRemit&remit_id=${remit_id}`, {
                method: 'DELETE'
            });
            const data = await response.json();
            if (data.success) {
                salesRemitItems = salesRemitItems.filter(item => item.remit_id !== remit_id);
            }
        } catch (error) {
            console.error('Error deleting remit:', error);
        } finally {
            showDeleteRemitModal = false;
            itemToDelete = null;
        }
    }

    async function deleteReturn(return_id: number) {
        try {
            const response = await fetch(`http://localhost/kaperustiko-possystem/backend/modules/delete.php?action=deleteReturn&return_id=${return_id}`, {
                method: 'DELETE'
            });
            const data = await response.json();
            if (data.success) {
                returnItems = returnItems.filter(item => item.return_id !== return_id);
            }
        } catch (error) {
            console.error('Error deleting return:', error);
        } finally {
            showDeleteReturnModal = false;
            itemToDelete = null;
        }
    }

    // Function to open the popup with the clicked cell's data
    function openPopup(item: any) {
        popupData = item; // Set the data for the popup
        showPopup = true; // Show the popup
    }

    // Function to close the popup
    function closePopup() {
        showPopup = false; // Hide the popup
        popupData = null; // Clear the data
    }

    // Function to open Z-Report modal
    function openZReportModal() {
        zReportDate = new Date(); // Reset to today
        zReportDateString = formatDateForInput(zReportDate);
        showZReportModal = true;
    }

    // Function to close Z-Report modal
    function closeZReportModal() {
        showZReportModal = false;
        zReportData = null;
        zReportError = null;
    }

    // Function to fetch Z-Report data
    async function fetchZReportData() {
        try {
            isLoadingZReport = true;
            zReportError = null;
            
            // Parse the date string to get a Date object
            zReportDate = new Date(zReportDateString);
            
            // Format the date as MM/DD/YYYY
            const formattedDate = `${zReportDate.getMonth() + 1}/${zReportDate.getDate()}/${zReportDate.getFullYear()}`;
            
            const response = await fetch(`http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getZReportData&date=${formattedDate}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            zReportData = await response.json();
            console.log("Z-Report data:", zReportData);
        } catch (error) {
            console.error("Error fetching Z-report data:", error);
            zReportError = "Failed to fetch Z-report data. Please try again.";
        } finally {
            isLoadingZReport = false;
        }
    }

    // Function to print Z-Report
    async function printZReport() {
        if (!zReportData) {
            zReportError = "No data to print. Please fetch the report first.";
            return;
        }
        
        try {
            const response = await fetch('http://localhost/kaperustiko-possystem/src/routes/z_report_printer.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    ...zReportData,
                    cashier_name: localStorage.getItem('userName') || 'Admin',
                    includeFoodItemsSummary: true, // Always include food items
                    includeOrderMonitorChart: true, // Always include order monitor chart
                })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (result.success) {
                alert("Z-Report printed successfully!");
                closeZReportModal();
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            console.error("Error printing Z-report:", error);
            zReportError = "Failed to print Z-report. Please make sure the printer is connected and try again.";
        }
    }

    // Function to open print preview in a new window
    function openPrintPreview() {
        if (!zReportData) {
            zReportError = "No data to print. Please fetch the report first.";
            return;
        }

        // Format date and time for display
        const today = new Date();
        const formattedDate = today.toLocaleDateString();
        const formattedTime = today.toLocaleTimeString();

        // Create the print window
        const printWindow = window.open('', '_blank', 'width=800,height=600');
        if (!printWindow) {
            zReportError = "Failed to open print preview window. Please check your pop-up blocker settings.";
            return;
        }

        // Write the HTML content for printing
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Z-Report - ${zReportData.report_date}</title>
                <style>
                    body {
                        font-family: 'Courier New', monospace;
                        margin: 0;
                        padding: 20px;
                        max-width: 800px;
                        margin: 0 auto;
                    }
                    .header {
                        text-align: center;
                        margin-bottom: 20px;
                    }
                    .title {
                        font-size: 20px;
                        font-weight: bold;
                        margin-bottom: 5px;
                    }
                    .subtitle {
                        font-size: 16px;
                        margin-bottom: 5px;
                    }
                    .report-header {
                        font-size: 18px;
                        font-weight: bold;
                        text-align: center;
                        margin: 15px 0;
                        border-bottom: 1px solid black;
                        padding-bottom: 5px;
                    }
                    .section {
                        margin-bottom: 20px;
                    }
                    .section-title {
                        font-weight: bold;
                        font-size: 16px;
                        margin-bottom: 10px;
                        border-bottom: 1px dashed #ccc;
                        padding-bottom: 5px;
                    }
                    .row {
                        display: flex;
                        justify-content: space-between;
                        margin-bottom: 5px;
                    }
                    .divider {
                        border-top: 1px dashed #ccc;
                        margin: 15px 0;
                    }
                    .footer {
                        text-align: center;
                        margin-top: 30px;
                        font-size: 14px;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    th, td {
                        padding: 8px;
                        text-align: left;
                    }
                    th {
                        border-bottom: 1px solid #000;
                    }
                    td {
                        border-bottom: 1px dashed #ccc;
                    }
                    .value {
                        text-align: right;
                    }
                    @media print {
                        body {
                            padding: 0;
                            margin: 0;
                        }
                        button {
                            display: none;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <div class="title">KAPE RUSTIKO</div>
                    <div class="subtitle">Cafe and Restaurant</div>
                    <div>Dewey Ave, Subic Bay Freeport Zone</div>
                    <div>VAT REG TIN: 123-456-789-12345</div>
                </div>
                
                <div class="report-header">Z-READING REPORT</div>
                
                <div class="section">
                    <div class="row">
                        <div>Report Date:</div>
                        <div>${zReportData.report_date}</div>
                    </div>
                    <div class="row">
                        <div>Printed:</div>
                        <div>${formattedDate} ${formattedTime}</div>
                    </div>
                </div>
                
                <div class="section">
                    <div class="section-title">SHIFT SUMMARY</div>
                    <table>
                        <thead>
                            <tr>
                                <th>Shift</th>
                                <th class="value">Sales</th>
                                <th class="value">Trans</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Morning Shift Duty</td>
                                <td class="value">₱${safeNumberFormat(zReportData.shifts.morning.sales)}</td>
                                <td class="value">${zReportData.shifts.morning.transactions}</td>
                            </tr>
                            <tr>
                                <td>Night Shift Duty</td>
                                <td class="value">₱${safeNumberFormat(zReportData.shifts.night.sales)}</td>
                                <td class="value">${zReportData.shifts.night.transactions}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="section">
                    <div class="section-title">SALES SUMMARY</div>
                    <div class="row">
                        <div>Gross Sales:</div>
                        <div>₱${safeNumberFormat(zReportData.gross_sales)}</div>
                    </div>
                    <div class="row">
                        <div>Cash Sales:</div>
                        <div>₱${safeNumberFormat(zReportData.cash_sales)}</div>
                    </div>
                    <div class="row">
                        <div>PWD/Senior Discount:</div>
                        <div>₱${safeNumberFormat(zReportData.pwd_senior_discount)}</div>
                    </div>
                    <div class="row">
                        <div>Service Charge:</div>
                        <div>₱${safeNumberFormat(zReportData.service_charge)}</div>
                    </div>
                    <div class="row">
                        <div>Zero Rated Sales:</div>
                        <div>₱${safeNumberFormat(zReportData.zero_rated_sales)}</div>
                    </div>
                    <div class="row">
                        <div>VAT Exempted Sales:</div>
                        <div>₱${safeNumberFormat(zReportData.vat_exempted_sales)}</div>
                    </div>
                    <div class="row">
                        <div>Vatable Sales:</div>
                        <div>₱${safeNumberFormat(zReportData.vatable_sales)}</div>
                    </div>
                    <div class="row">
                        <div>VAT:</div>
                        <div>₱${safeNumberFormat(zReportData.vat)}</div>
                    </div>
                </div>
                
                <div class="section">
                    <div class="section-title">RECEIPT RANGE</div>
                    <div class="row">
                        <div>Start Receipt:</div>
                        <div>${zReportData.start_receipt}</div>
                    </div>
                    <div class="row">
                        <div>End Receipt:</div>
                        <div>${zReportData.end_receipt}</div>
                    </div>
                </div>
                
                <div class="section">
                    <div class="section-title">VOIDS & TRANSACTIONS</div>
                    <div class="row">
                        <div>Void Items:</div>
                        <div>${zReportData.void_items}</div>
                    </div>
                    <div class="row">
                        <div>Voided Amount:</div>
                        <div>₱${safeNumberFormat(zReportData.voided_amount)}</div>
                    </div>
                    <div class="row">
                        <div>Transactions:</div>
                        <div>${zReportData.num_transactions}</div>
                    </div>
                    <div class="row">
                        <div>Net Sales:</div>
                        <div>₱${safeNumberFormat(zReportData.net_sales)}</div>
                    </div>
                    <div class="row">
                        <div>Running Total:</div>
                        <div>₱${safeNumberFormat(zReportData.running_total)}</div>
                    </div>
                </div>
                
                <!-- Food Items Summary Section -->
                <div class="section">
                    <div class="section-title">FOOD ITEMS SUMMARY</div>
                    <table>
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th class="value">Quantity</th>
                                <th class="value">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${zReportData.food_items && zReportData.food_items.length > 0 ? 
                                zReportData.food_items.map(item => `
                                    <tr>
                                        <td>${item.name}</td>
                                        <td class="value">${item.quantity}</td>
                                        <td class="value">₱${safeNumberFormat(item.amount)}</td>
                                    </tr>
                                `).join('') : 
                                `<tr><td colspan="3" style="text-align: center;">No food items data available</td></tr>`
                            }
                        </tbody>
                    </table>
                </div>
                
                <!-- Order Monitor Chart Section -->
                <div class="section">
                    <div class="section-title">ORDER MONITOR CHART</div>
                    <div style="margin: 20px auto; max-width: 600px; text-align: center;">
                        ${zReportData.order_chart_image ? 
                            `<img src="${zReportData.order_chart_image}" alt="Order Monitor Chart" style="max-width: 100%;">` :
                            `<p>Order Monitor Chart data not available</p>`
                        }
                    </div>
                </div>
                
                <div class="footer">
                    *** End of Z-Report ***<br>
                    Printed by: ${localStorage.getItem('userName') || 'Admin'}
                </div>
                
                <div style="text-align: center; margin-top: 20px;">
                    <button onclick="window.print(); return false;" style="padding: 10px 20px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">Print Report</button>
                </div>
            </body>
            </html>
        `);
        printWindow.document.close();
    }
</script>

<div class="flex h-screen bg-gradient-to-b from-green-500 to-green-700">
    <Sidebar />

    <!-- Main Content -->
    <div class="flex-grow p-6 bg-gray-100 overflow-auto">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <div class="text-gray-500">Today's Total Sales</div>
                <div class="text-3xl font-bold">₱{totalSalesToday}</div>
                <div class="text-green-600">
                    <FontAwesomeIcon icon={faDollarSign} />
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <div class="text-gray-500">Overall Total Sales</div>
                <div class="text-3xl font-bold">₱{overallTotalSales}</div>
                <div class="text-green-500">
                    <FontAwesomeIcon icon={faChartBar} />
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <div class="text-gray-500">Best Seller</div>
                <div class={`text-xl font-bold ${bestSeller.length > 18 ? 'text-md' : ''}`}>{bestSeller || 'N/A'}</div>
                <div class="text-green-500">
                    <FontAwesomeIcon icon={faTrophy} />
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <div class="text-gray-500">Least Seller</div>
                <div class={`text-xl font-bold ${leastSeller.length > 18 ? 'text-md' : ''}`}>{leastSeller || 'N/A'}</div>
                <div class="text-red-500">
                    <FontAwesomeIcon icon={faArrowDown} />
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <div class="text-gray-500">Order Processing Time</div>
                <div class="text-3xl font-bold"></div>
                <div class="text-red-500">
                    <FontAwesomeIcon icon={faExclamationTriangle} />
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <div class="text-gray-500">Leading Waiter Staff</div> 
                    <div class="text-xl font-bold">{leadingStaff ? `${leadingStaff.firstName} ${leadingStaff.lastName}` : 'N/A'}</div>
                    <div class="text-green-500">
                        <FontAwesomeIcon icon={faUser} />
                    </div>
            </div>
        </div>

        <!-- Add Z-Report Button -->
        <div class="mb-6 flex justify-end">
            <button 
                class="bg-blue-600 text-white px-4 py-2 rounded-md flex items-center hover:bg-blue-700 transition duration-200"
                on:click={openZReportModal}
            >
                <FontAwesomeIcon icon={faFileAlt} class="mr-2" />
                Generate Z-Report
            </button>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-3 gap-2 mb-6">
            <div class="bg-white rounded-lg shadow-lg p-2">
                <h3 class="text-center font-bold text-sm">Order Monitor Chart</h3>
                <canvas id="salesChart" width="400" height="200" style="max-width: 100%;"></canvas>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-2">
                <h3 class="text-center font-bold text-sm">Sales Chart</h3>
                <canvas id="salesBarChart" width="400" height="200" style="max-width: 100%;"></canvas>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-2">
                <h3 class="text-center font-bold text-sm">Total Customers</h3>
                <canvas id="customersPieChart" class="small-canvas"></canvas>
            </div>
        </div>
        <!-- Tables Section -->
        <div class="grid grid-cols-2 gap-4">
            <!-- Sales Remit Table -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="bg-gray-800 text-white text-center font-bold p-2">Sales Remit</div>
                <table class="w-full text-left table-fixed border-collapse">
                    <thead class="bg-gray-700 text-white">
                        <tr>
                            <th class="p-2 text-center">Remit ID</th>
                            <th class="p-2 text-center">Name</th>
                            <th class="p-2 text-center">Total Sales</th>    
                            <th class="p-2 text-center">Date</th>
                            <th class="p-2 text-center">Time</th>
                            <th class="p-2 text-center">Shortage</th>
                            <th class="p-2 text-center">Code</th>
                            <th class="p-2 text-center">Validate</th>
                            <th class="p-2 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        {#if salesRemitItems.length === 0}
                            <tr>
                                <td colspan="8" class="p-2 text-center">No data yet</td>
                            </tr>
                        {:else}
                            {#each salesRemitItems as item}
                                <tr class="border-t border-gray-300 hover:bg-gray-200 transition-colors duration-200">
                                    <td class="p-2 text-center" on:click={() => openPopup(item)}>{item.remit_id}</td>
                                    <td class="p-2 text-center" on:click={() => openPopup(item)}>{item.cashier_name}</td>
                                    <td class="p-2 text-center" on:click={() => openPopup(item)}>₱{item.total_sales}.00</td>
                                    <td class="p-2 text-center" on:click={() => openPopup(item)}>{item.remit_date}</td>
                                    <td class="p-2 text-center" on:click={() => openPopup(item)}>{item.remit_time}</td>
                                    <td class="p-2 text-center" on:click={() => openPopup(item)}>₱{item.remit_shortage}.00</td>
                                    <td class="p-2 text-center" on:click={() => openPopup(item)}>{item.remit_code}</td>
                                    <td class="p-2 text-center">
                                        <button class="p-1 {item.remit_validation === "Validated" ? 'bg-green-500' : item.remit_validation === "Pending" ? 'bg-yellow-500' : 'bg-gray-200'} text-white rounded">{item.remit_validation}</button>
                                    </td>
                                    <td class="p-2 text-center">
                                        <button 
                                            class="p-1 bg-red-500 text-white rounded hover:bg-red-600" 
                                            on:click={() => confirmDeleteRemit(item.remit_id)}>
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            {/each}
                        {/if}
                    </tbody>
                </table>
            </div>

            <!-- Inventory Item Returns Table -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="bg-gray-800 text-white text-center font-bold p-2">Inventory Item Returns</div>
                <table class="w-full text-left table-fixed border-collapse">
                    <thead class="bg-gray-700 text-white">
                        <tr>
                            <th class="p-2 text-center">Return ID</th>
                            <th class="p-2 text-center">Name</th>
                            <th class="p-2 text-center">Total Cost</th>    
                            <th class="p-2 text-center">Date</th>
                            <th class="p-2 text-center">Time</th>
                            <th class="p-2 text-center">Validate</th>
                            <th class="p-2 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        {#if returnItems.length === 0}
                            <tr>
                                <td colspan="7" class="p-2 text-center">No data yet</td>
                            </tr>
                        {:else}
                            {#each returnItems as item}
                                <tr class="border-t border-gray-300 hover:bg-gray-200 transition-colors duration-200">
                                    <td class="p-2 text-center">{item.return_id}</td>
                                    <td class="p-2 text-center">{item.cashier_name}</td>    
                                    <td class="p-2 text-center">{item.total_sales}</td>
                                    <td class="p-2 text-center">{item.return_date}</td>
                                    <td class="p-2 text-center">{item.return_time}</td>
                                    <td class="p-2 text-center">
                                        <button class="p-1 {item.return_validation === "Validated" ? 'bg-green-500' : item.return_validation === "Pending" ? 'bg-yellow-500' : 'bg-gray-200'} text-white rounded">{item.return_validation}</button>
                                    </td>
                                    <td class="p-2 text-center">
                                        <button 
                                            class="p-1 bg-red-500 text-white rounded hover:bg-red-600" 
                                            on:click={() => confirmDeleteReturn(item.return_id)}>
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            {/each}
                        {/if}
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add the new table for Waiter Take Order Count Ranking -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden mt-4">
            <div class="bg-gray-800 text-white text-center font-bold p-2">Waiter Take Order Count Ranking</div>
            <table class="w-full text-left table-fixed border-collapse">
                <thead class="bg-gray-700 text-white">
                    <tr>
                        <th class="p-2 text-center">Waiter Name</th>
                        <th class="p-2 text-center">Order Count</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    {#if waiterOrderCounts.length === 0}
                        <tr>
                            <td colspan="2" class="p-2 text-center">No data yet</td>
                        </tr>
                    {:else}
                        {#each waiterOrderCounts as waiter}
                            <tr class="border-t border-gray-300 hover:bg-gray-200 transition-colors duration-200">
                                <td class="p-2 text-center">{waiter.firstName} {waiter.lastName}</td>
                                <td class="p-2 text-center">{waiter.order_count}</td>
                            </tr>
                        {/each}
                    {/if}
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add these modal components just before the closing </div> of the main content -->
{#if showDeleteRemitModal}
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h3 class="text-lg font-bold mb-4">Confirm Delete</h3>
            <p class="mb-4">Are you sure you want to delete this remit record?</p>
            <div class="flex justify-end gap-2">
                <button 
                    class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400"
                    on:click={() => {showDeleteRemitModal = false; itemToDelete = null;}}>
                    Cancel
                </button>
                <button 
                    class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
                    on:click={() => itemToDelete && deleteRemit(itemToDelete)}>
                    Delete
                </button>
            </div>
        </div>
    </div>
{/if}

{#if showDeleteReturnModal}
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h3 class="text-lg font-bold mb-4">Confirm Delete</h3>
            <p class="mb-4">Are you sure you want to delete this return record?</p>
            <div class="flex justify-end gap-2">
                <button 
                    class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400"
                    on:click={() => {showDeleteReturnModal = false; itemToDelete = null;}}>
                    Cancel
                </button>
                <button 
                    class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
                    on:click={() => itemToDelete && deleteReturn(itemToDelete)}>
                    Delete
                </button>
            </div>
        </div>
    </div>
{/if}

<!-- Z-Report Modal -->
{#if showZReportModal}
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg max-w-2xl w-full">
            <h3 class="text-xl font-bold mb-4 text-gray-800">Z-Reading Report</h3>
            
            <div class="mb-4">
                <label for="z-report-date" class="block text-sm font-medium text-gray-700 mb-1">Select Date</label>
                <div class="flex items-center">
                    <input 
                        type="date" 
                        id="z-report-date" 
                        class="border rounded-md px-3 py-2 w-full"
                        bind:value={zReportDateString}
                    />
                    <button 
                        class="ml-2 bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition duration-200 flex items-center"
                        on:click={fetchZReportData}
                        disabled={isLoadingZReport}
                    >
                        <FontAwesomeIcon icon={faCalendar} class="mr-2" />
                        {isLoadingZReport ? 'Loading...' : 'Get Report'}
                    </button>
                </div>
            </div>
            
            {#if zReportError}
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {zReportError}
                </div>
            {/if}
            
            {#if zReportData}
                <div class="bg-gray-100 p-4 rounded-lg mb-4 max-h-96 overflow-y-auto">
                    <h4 class="font-bold text-lg mb-2">Z-Report Summary</h4>
                    
                    <div class="mb-4">
                        <h5 class="font-semibold text-md mb-1">Shift Summary</h5>
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-200">
                                    <th class="p-2 text-left">Shift</th>
                                    <th class="p-2 text-right">Sales</th>
                                    <th class="p-2 text-right">Transactions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="p-2">Morning Shift Duty</td>
                                    <td class="p-2 text-right">₱{safeNumberFormat(zReportData.shifts.morning.sales)}</td>
                                    <td class="p-2 text-right">{zReportData.shifts.morning.transactions}</td>
                                </tr>
                                <tr>
                                    <td class="p-2">Night Shift Duty</td>
                                    <td class="p-2 text-right">₱{safeNumberFormat(zReportData.shifts.night.sales)}</td>
                                    <td class="p-2 text-right">{zReportData.shifts.night.transactions}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mb-4">
                        <h5 class="font-semibold text-md mb-1">Sales Summary</h5>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="p-2 flex justify-between">
                                <span>Gross Sales:</span>
                                <span>₱{safeNumberFormat(zReportData.gross_sales)}</span>
                            </div>
                            <div class="p-2 flex justify-between">
                                <span>Cash Sales:</span>
                                <span>₱{safeNumberFormat(zReportData.cash_sales)}</span>
                            </div>
                            <div class="p-2 flex justify-between">
                                <span>PWD/Senior Discount:</span>
                                <span>₱{safeNumberFormat(zReportData.pwd_senior_discount)}</span>
                            </div>
                            <div class="p-2 flex justify-between">
                                <span>Service Charge:</span>
                                <span>₱{safeNumberFormat(zReportData.service_charge)}</span>
                            </div>
                            <div class="p-2 flex justify-between">
                                <span>Vatable Sales:</span>
                                <span>₱{safeNumberFormat(zReportData.vatable_sales)}</span>
                            </div>
                            <div class="p-2 flex justify-between">
                                <span>VAT:</span>
                                <span>₱{safeNumberFormat(zReportData.vat)}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h5 class="font-semibold text-md mb-1">Receipt Range</h5>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="p-2 flex justify-between">
                                <span>Start Receipt:</span>
                                <span>{zReportData.start_receipt}</span>
                            </div>
                            <div class="p-2 flex justify-between">
                                <span>End Receipt:</span>
                                <span>{zReportData.end_receipt}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h5 class="font-semibold text-md mb-1">Transactions Summary</h5>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="p-2 flex justify-between">
                                <span>Number of Transactions:</span>
                                <span>{zReportData.num_transactions}</span>
                            </div>
                            <div class="p-2 flex justify-between">
                                <span>Net Sales:</span>
                                <span>₱{safeNumberFormat(zReportData.net_sales)}</span>
                            </div>
                            <div class="p-2 flex justify-between">
                                <span>Running Total:</span>
                                <span>₱{safeNumberFormat(zReportData.running_total)}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end gap-2">
                    <button 
                        class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition duration-200 flex items-center"
                        on:click={openPrintPreview}
                    >
                        <FontAwesomeIcon icon={faPrint} class="mr-2" />
                        Print Preview
                    </button>
                    <button 
                        class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition duration-200 flex items-center"
                        on:click={printZReport}
                    >
                        <FontAwesomeIcon icon={faPrint} class="mr-2" />
                        Print to Thermal
                    </button>
                </div>
            {/if}
            
            <div class="mt-4 flex justify-end">
                <button 
                    class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition duration-200"
                    on:click={closeZReportModal}
                >
                    Close
                </button>
            </div>
        </div>
    </div>
{/if}

<!-- Popup for displaying clicked cell data -->
{#if showPopup}
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
            <h3 class="text-lg font-bold mb-4 text-center text-gray-800">Order Details</h3>
            <div class="text-center mb-4">
                <p class="font-semibold text-gray-700">Cashier: <span class="text-blue-600">{popupData.cashier_name}</span></p>
                <p class="text-gray-600">Date: {popupData.remit_date}</p>
                <p class="text-gray-600">Time: {popupData.remit_time}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left table-fixed border-collapse mb-4">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="p-2">Item Name</th>
                            <th class="p-2">Quantity</th>
                            <th class="p-2">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        {#each JSON.parse(popupData.food_summary) as item}
                            {#each JSON.parse(item.items_ordered) as foodItem}
                                <tr class="border-b hover:bg-gray-100 transition-colors duration-200">
                                    <td class="p-2 border-b">{foodItem.order_name}</td>
                                    <td class="p-2 border-b">{foodItem.order_quantity}</td>
                                    <td class="p-2 border-b">₱{foodItem.basePrice}</td>
                                </tr>
                            {/each}
                        {/each}
                    </tbody>
                </table>
            </div>
            <div class="bg-gray-100 p-4 rounded-lg mb-4">
                <p class="font-bold text-lg">Total Sales: <span class="text-green-600">₱{popupData.total_sales}</span></p>
            </div>
            <div class="flex justify-end">
                <button 
                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition duration-200"
                    on:click={closePopup}>
                    Close
                </button>
            </div>
        </div>
    </div>
{/if}

<style>
    .small-canvas {
        width: 280px !important; /* Set a specific width */
        height: 280px !important; /* Set a specific height */
        margin: 0 auto; /* Center the canvas */
    }

</style>
  