<script lang="ts">
    import { onMount } from "svelte";
    import { FontAwesomeIcon } from "@fortawesome/svelte-fontawesome";
    import { faStar, faUndo, faExclamationTriangle, faTrash, faDollarSign, faPrint, faArrowDown, faChartBar, faTrophy, faExclamationCircle, faTrashAlt, faUser } from "@fortawesome/free-solid-svg-icons";
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

         <!-- Date Sorter -->
         

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
  