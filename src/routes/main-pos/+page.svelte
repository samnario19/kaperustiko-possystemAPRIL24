<script lang="ts">
	import Sidebar from '../sidebar/+page.svelte';
	import { orderedItemsStore } from '../../stores/orderedItemsStore';
	import { onMount, onDestroy } from 'svelte';
	import { handleButtonClick } from '../../utils/buttonHandler'; // Import the reusable function
	import { currentInputStore } from '../../stores/currentInputStore'; // Import the store
	import { WindowsSolid } from 'flowbite-svelte-icons';

	let amountPaid = '₱0.00';
	let cashierName = '';
	let staffToken: string | null = null; // Declare a variable to hold the staff_token
	let currentTime: string;
	let currentDay: string;
	let orderedItems: OrderedItem[] = []; // Declare and initialize orderedItems
	let isTakeOut = false; // Declare and initialize isTakeOut
	let isDineIn = false; // Declare and initialize isDineIn
	let queuedOrders: QueuedOrder[] = []; // Use the defined type for queuedOrders
	let orders: any[] = []; // Declare a variable to hold fetched orders
	let isReservePopupVisible = false; // State variable to control the visibility of the reserve popup
	let tableStatus: { [key: string]: boolean } = {};
	let isReservePopup2Visible = false; // State variable to control the visibility of the reserve popup
	let isReceiptPopupVisible = false;

	let reserveDate = '';
	let reserveTime = '';
	let selectedTableNumber = '';
	let orderNumber = '';
	let selectedCategory = 'All';
	let payment = '';
	let isPopupVisible = false;

	let isCodePopupVisible = false;
	let voidIndex: number | null = null;
	let inputCode = '';

	let reservedTables: string[] = []; // Declare an array to hold reserved table numbers

	let waiterName = ''; // Declare the variable to hold the waiter's name

	let serviceCharge = ''; // Declare the variable to hold the service charge

	let includeServiceCharge = false; // Declare the variable to manage service charge inclusion

	type QueuedOrder = {
		que_order_no: string;
		receipt_number: string;
		date: string;
		time: string;
		table_number: string;
		order_status: string;
		items_ordered: string;
		total_amount: number;
		basePrice: number;
		waiter_name: string;
	};

	let totalOrderedItemsPrice = 0; // Variable to hold the total price of ordered items
	let totalServiceCharge = 0; // Variable to hold the total service charge
	let voucherCode = ''; // Declare the voucherCode variable
	let voucherDiscount = 0; // Declare the voucherDiscount variable
	let voidReceiptNumber = ''; // Added to store receipt number for voiding
	let adminPassword = '123456'; // Set the admin password (this should eventually come from a secure backend)
	let voidItemIndex = -1;

	// Define the Voucher type
	type Voucher = {
		code: string;
		discount: number;
		voucher_discount: string;
		voucher_code: string;
		// Add other fields as necessary
	};

	// Function to calculate total price of ordered items
	function calculateTotalOrderedItemsPrice() {

		totalOrderedItemsPrice = orderedItems.reduce((total, item) => {
			
			// Calculate total for this item
			const itemTotal = item.basePrice; // Ensure this is a number
			const addonsPrice = item.order_addons_price || 0; // Get addons price if available

			// Return the accumulated total
			return total + itemTotal + addonsPrice; // Sum the item total and addons price
		}, 0); // Initialize total to 0

		// Calculate VAT and service charge
		const serviceCharge = Math.round(totalOrderedItemsPrice * 0.05); // 5% service charge
		const vat = Math.round(totalOrderedItemsPrice * 0.12); // 12% VAT

		// Update totalOrderedItemsPrice to include VAT and service charge
		totalOrderedItemsPrice += serviceCharge + vat;

		// Log the total ordered items price
		console.log('Total Ordered Items Price:', totalOrderedItemsPrice); // Log the total price
	}

	// Call this function whenever orderedItems changes
	$: calculateTotalOrderedItemsPrice();

	// Function to calculate voucher discount
	async function calculateVoucherDiscount() {
		const response = await fetch(
			`http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getVouchersbyCode&voucher_code=${voucherCode}`,
			{
				method: 'GET' // Change to GET method
			}
		);
		if (response.ok) {
			const data = await response.json();
			voucherDiscount = data[0].voucher_discount;
		} else {
			console.error('Failed to fetch vouchers:', response.statusText);
			voucherDiscount = 0; // Reset discount on fetch failure
		}
		console.log('Voucher Code Inputted:', voucherCode); // Log the voucher code inputted
		console.log('Fetched Discount:', voucherDiscount); // Log the fetched discount
		// Log the link with the inputted voucher code
		console.log(
			`Fetch URL: http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getVouchersbyCode&voucher_code=${voucherCode}`
		);
	}

	async function fetchCashierName() {
		// Retrieve staff_token from local storage only if not already fetched
		if (!staffToken) {
			staffToken = localStorage.getItem('staff_token'); // Get the staff_token
		}
		console.log('Fetched staff_token:', staffToken); // Log the fetched staff_token
		console.log(
			`Fetching user data from: http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getUser&staff_token=${staffToken}`
		); // Log the URL with the staff_token
		const response = await fetch(
			`http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getUser&staff_token=${staffToken}`
		);
		if (response.ok) {
			const userData = await response.json();
			console.log('Fetched user data:', userData); // Log the fetched user data
			cashierName = userData || 'Unknown'; // Default to 'Unknown' if firstName is not available
			waiterName = userData.firstName || 'Unknown'; // Example assignment
		}
	}

	// Function to fetch orders
	async function fetchQueuedOrders() {
		try {
			const response = await fetch(
				'http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getQueOrders'
			);
			if (!response.ok) {
				throw new Error(`Failed to fetch queued orders: ${response.statusText}`);
			}
			queuedOrders = await response.json(); // Check the response here
			// Convert basePrice to number for each order
			queuedOrders.forEach(order => {
				order.basePrice = Number(order.basePrice); // Convert basePrice to number
				console.log('Table Number:', order.table_number); // Log the table number for each order
			});
		} catch (error) {
			console.error('Error fetching queued orders:', error); // Improved error logging
			showAlert('Failed to fetch queued orders. Please try again later.', 'error'); // Notify user
		}
	}

	// Function to fetch reserve tables
	async function fetchReserveTables() {
		const response = await fetch(
			'http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getReserveTables'
		);
		if (response.ok) {
			const data = await response.json();
			reservedTables = data.map((table: { table_number: string }) => table.table_number); // Assuming the response contains an array of reserved table objects
		} else {
			console.error('Failed to fetch reserved tables', response.statusText);
		}
	}

	async function fetchOrders() {
		const response = await fetch(
			'http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getQueOrders'
		);
		if (response.ok) {
			orders = await response.json(); // Store the fetched orders
		} else {
			console.error('Failed to fetch orders:', response.statusText);
		}
	}

	// Function to void an individual item from a queued order
	async function voidIndividualItem(receiptNumber: string, itemIndex: number) {
		try {
			console.log(`Attempting to void item index ${itemIndex} from receipt ${receiptNumber}`);
			
			// Get the current order data first
			const getResponse = await fetch(
				`http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getOrderItemsByReceipt&receipt_number=${receiptNumber}`
			);
			
			if (!getResponse.ok) {
				throw new Error(`Error fetching order: ${getResponse.statusText}`);
			}
			
			const orderData = await getResponse.json();
			console.log("Received order data:", orderData);
			
			if (!orderData || orderData.length === 0) {
				throw new Error("Order not found");
			}
			
			// Since we need to modify the original order in the database,
			// we need to get the original order object from que_orders
			const fetchOriginalOrder = await fetch(
				`http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getQueOrders`
			);
			
			if (!fetchOriginalOrder.ok) {
				throw new Error(`Error fetching queued orders: ${fetchOriginalOrder.statusText}`);
			}
			
			const allOrders = await fetchOriginalOrder.json();
			const originalOrder = allOrders.find((order: any) => order.receipt_number === receiptNumber);
			
			if (!originalOrder) {
				throw new Error("Original order not found");
			}
			
			console.log("Found original order:", originalOrder);
			
			// Parse items from original order
			let items = JSON.parse(originalOrder.items_ordered);
			
			if (!Array.isArray(items) || items.length <= itemIndex) {
				throw new Error(`Invalid items array or index out of bounds. Items length: ${items?.length}, Index: ${itemIndex}`);
			}
			
			// Remove the item at the specified index
			const removedItem = items.splice(itemIndex, 1)[0];
			console.log("Removed item:", removedItem);
			
			// Create a special DELETE request for this specific action to void the item
			const voidResponse = await fetch(
				`http://localhost/kaperustiko-possystem/backend/modules/delete.php?action=voidQueuedOrder&receipt_number=${receiptNumber}`,
				{
					method: 'DELETE',
					headers: {
						'Content-Type': 'application/json'
					}
				}
			);
			
			if (!voidResponse.ok) {
				throw new Error(`Error voiding order: ${voidResponse.statusText}`);
			}
			
			// If there are still items, re-add the order with updated items
			if (items.length > 0) {
				// Re-insert the order with the updated items list
				const reinsertData = {
					saveQueOrder: true,
					receiptNumber: receiptNumber,
					date: originalOrder.date,
					time: originalOrder.time,
					cashierName: "Cashier", // Fill in with default 
					waiterName: originalOrder.waiter_name || "",
					waiterCode: originalOrder.waiter_code || "",
					itemsOrdered: items,
					totalAmount: calculateTotalFromItems(items),
					amountPaid: originalOrder.amount_paid || 0,
					change: originalOrder.amount_change || 0,
					order_take: originalOrder.order_take || "Dine In",
					table_number: originalOrder.table_number || ""
				};
				
				const reinsertResponse = await fetch(
					'http://localhost/kaperustiko-possystem/backend/modules/save_que_order.php',
					{
						method: 'POST',
						headers: {
							'Content-Type': 'application/json'
						},
						body: JSON.stringify(reinsertData)
					}
				);
				
				if (!reinsertResponse.ok) {
					throw new Error(`Error reinserting order: ${reinsertResponse.statusText}`);
				}
				
				const reinsertResult = await reinsertResponse.json();
				console.log("Reinsert result:", reinsertResult);
			}
			
			// Update UI
			showAlert("Item voided successfully", "success");
			await fetchQueuedOrders();
			
			// Re-trigger checkout logic if a card is selected
			if (selectedCard && selectedCard.table) {
				handleCheckOut(selectedCard.table);
			}
			
		} catch (error) {
			console.error('Error voiding item:', error);
			showAlert(`Error voiding item: ${error instanceof Error ? error.message : 'Unknown error'}`, 'error');
		}
	}

	// Helper function to calculate total from items
	function calculateTotalFromItems(items: any[]): number {
		let total = 0;
		items.forEach((item: any) => {
			// Skip voided items
			if (item.voided) return;
			
			// Base price
			const basePrice = parseFloat(item.basePrice ? String(item.basePrice) : '0');
			// Addons prices
			const addonsPrice1 = parseFloat(item.order_addons_price ? String(item.order_addons_price) : '0');
			const addonsPrice2 = parseFloat(item.order_addons_price2 ? String(item.order_addons_price2) : '0');
			const addonsPrice3 = parseFloat(item.order_addons_price3 ? String(item.order_addons_price3) : '0');
			// Add to total
			total += basePrice + addonsPrice1 + addonsPrice2 + addonsPrice3;
		});
		return total;
	}
	

	function updateTime() {
		const now = new Date();
		currentTime = now.toLocaleString('en-US', {
			year: 'numeric',
			month: 'long',
			day: 'numeric',
			hour: '2-digit',
			minute: '2-digit',
			second: '2-digit'
		});
		currentDay = now.toLocaleString('en-US', { weekday: 'long' });
	}

	onMount(() => {
		fetchQueuedOrders();
		fetchCashierName(); // Automatically fetch cashier name on mount
		fetchOrders(); // Fetch orders on component mount
		updateTime(); // Initial call to set the time
		const intervalTime = setInterval(updateTime, 1000); // Update time every second
		fetchReserveTables(); // Fetch reserved tables on component mount

		// Add event listener for keydown
		document.addEventListener('keydown', handleKeyDown);

		// Clean up the event listener on component unmount
		return () => {
			clearInterval(intervalTime); // Clear interval on component unmount
			document.removeEventListener('keydown', handleKeyDown);
		};
	});

	function handleNumberInput(num: string) {
		payment += num;
	}

	function handleBackspace() {
		payment = payment.slice(0, -1);
	}

	function handleClear() {
		payment = '';
	}

	function showAlert(message: string, type: string) {
		const alertDiv = document.createElement('div');
		alertDiv.className = `fixed top-0 left-1/2 transform -translate-x-1/2 mt-4 p-4 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white rounded shadow-lg`;
		alertDiv.innerText = message;
		document.body.appendChild(alertDiv);
		setTimeout(() => {
			alertDiv.remove();
		}, 3000); // Remove alert after 3 seconds
	}

	function voidOrder(index: number) {
		voidIndex = index;
		isCodePopupVisible = true;
	}

	function confirmVoid() {
		// Check if the entered code matches the admin password
		if (inputCode === adminPassword) {
			// Password is correct, proceed with voiding the item
			if (voidReceiptNumber && voidItemIndex >= 0) {
				voidIndividualItem(voidReceiptNumber, voidItemIndex);
				closeCodePopup();
			} else {
				showAlert('Invalid void information', 'error');
			}
		} else {
			// Password is incorrect
			showAlert('Incorrect password. Void operation cancelled.', 'error');
			closeCodePopup();
		}
	}

	function closeCodePopup() {
		isCodePopupVisible = false;
		inputCode = '';
		voidReceiptNumber = '';
		voidItemIndex = -1;
	}

	// Function to show password popup before voiding
	function promptPasswordForVoid(receiptNum: string, itemIdx: number) {
		voidReceiptNumber = receiptNum;
		voidItemIndex = itemIdx;
		isCodePopupVisible = true;
	}

	let cards = Array.from({ length: 20 }, (_, index) => ({
		table: `${index + 1}`
	}));

	// Add 6 takeout tables
	let takeoutCards = Array.from({ length: 20 }, (_, index) => ({
		table: `T${index + 1}` // Prefixing with 'T' for takeout tables
	}));


	let outdoorCards = Array.from({ length: 15 }, (_, index) => ({
		table: `O${index + 1}`
	}));

	let gardenCards = Array.from({ length: 6 }, (_, index) => ({
		table: `G${index + 1}`
	}));

	type OrderedItem = {
		que_order_no: string;
		receipt_number: string;
		date: string;
		time: string;
		items_ordered: string;
		total_amount: number;
		amount_paid: number;
		amount_change: number;
		order_status: string;
		table_number: string;
		order_name: string;
		order_name2?: string;
		order_quantity: number;
		order_size: string;
		basePrice: number;
		order_addons?: string;
		order_addons_price?: number;
		order_addons2?: string;
		order_addons_price2?: number;
		order_addons3?: string;
		order_addons_price3?: number;
		order_price?: number;
	};

	function handlePlaceOrder() {
		// Check if there are ordered items before opening the receipt popup
		if (orderedItems.length === 0) {
			showAlert('No items ordered yet. Please add items to your order.', 'error');

			return; // Exit the function if no items are ordered
		}

		// Calculate total cost based on discounts and service charge
		const totalCost = Math.round(totalOrderedItemsPrice - Math.round(totalOrderedItemsPrice * voucherDiscount / 100) - Math.round(seniorDiscount) + (includeServiceCharge ? Math.round(totalOrderedItemsPrice * 0.05) : 0));

		// Log the total cost for debugging
		console.log('Total Cost:', totalCost); // Log the total cost

		const paymentAmount = Math.round(Number(payment)) || 0;

		// Allow exact amount or more
		if (paymentAmount < totalCost) {
			showAlert('Invalid amount. Please enter at least the exact amount.', 'error');
			return;
		}

		// Save to local storage
		localStorage.setItem('totalCost', totalCost.toString()); // Save total cost
		localStorage.setItem('serviceCharge', Math.round(totalOrderedItemsPrice * 0.05).toString()); // Save service charge
		localStorage.setItem('paymentAmount', paymentAmount.toString()); // Save payment amount
		localStorage.setItem('change', Math.max(0, paymentAmount - totalCost).toString()); // Save change

		isReceiptPopupVisible = true; // Show the receipt popup
	}

	async function previewReceipt() {
		try {
			const thermalData = {
				restaurantName: "Kape Rustiko Cafe and Restaurant",
				address: "Dewey Ave, Subic Bay Freeport Zone",
				tin: "VAT REG TIN: 123-456-789-12345",
				transactionDate: new Date().toLocaleDateString(),
				transactionTime: new Date().toLocaleTimeString(),
				cashierName: cashierName,
				receiptNumber: orderNumber,
				waiter_name: waiterName,
				table_number: selectedCard?.table || 'Take Out',
				itemsOrdered: orderedItems.map(item => ({
					name: item.order_name + (item.order_name2 ? ` ${item.order_name2}` : ''),
					quantity: item.order_quantity,
					price: item.basePrice
				})),
				subtotal: totalOrderedItemsPrice,
				seniorDiscount: seniorDiscount,
				service_charge: includeServiceCharge ? Math.round(totalOrderedItemsPrice * 0.05) : 0,
				total: Math.round(totalOrderedItemsPrice - Math.round(totalOrderedItemsPrice * voucherDiscount / 100) - Math.round(seniorDiscount) + (includeServiceCharge ? Math.round(totalOrderedItemsPrice * 0.05) : 0)),
			};

			// Log the thermal data to be printed
			console.log('Thermal Data to be printed:', thermalData); // Log the data

			const thermalResponse = await fetch('http://localhost/kaperustiko-possystem/src/routes/thermal_printer.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
				},
				body: JSON.stringify(thermalData)
			});

			if (!thermalResponse.ok) {
				throw new Error('Failed to print preview receipt');
			}

			// Clear waiter name from local storage
			localStorage.removeItem('waiter_name');
			console.log('Waiter name cleared from local storage.');

			showAlert('Preview receipt printed successfully!', 'success');
		} catch (error: any) {
			console.error('Error:', error);
			showAlert(error.message || 'Failed to print preview receipt.', 'error');
		}
	}

	async function printReceipt() {
		try {
			// Calculate total amount based on discounts and service charge
			const totalAmount = Math.round(totalOrderedItemsPrice - Math.round(totalOrderedItemsPrice * voucherDiscount / 100) - Math.round(seniorDiscount) + (includeServiceCharge ? Math.round(totalOrderedItemsPrice * 0.05) : 0));

			const thermalData = {
				restaurantName: "Kape Rustiko Cafe and Restaurant",
				address: "Dewey Ave, Subic Bay Freeport Zone",
				tin: "VAT REG TIN: 123-456-789-12345",
				transactionDate: new Date().toLocaleDateString(),
				transactionTime: new Date().toLocaleTimeString(),
				cashierName: cashierName,
				receiptNumber: orderNumber,
				waiter_name: waiterName,
				table_number: selectedCard?.table || 'Take Out',
				itemsOrdered: orderedItems.map(item => ({
					name: item.order_name + (item.order_name2 ? ` ${item.order_name2}` : ''),
					quantity: item.order_quantity,
					price: item.basePrice
				})),
				subtotal: totalOrderedItemsPrice,
				seniorDiscount: seniorDiscount,
				service_charge: includeServiceCharge ? Math.round(totalOrderedItemsPrice * 0.05) : 0,
				totalAmount: totalAmount,
				amountPaid: Number(payment),
				change: Math.max(0, Number(payment) - totalAmount),
				order_take: "Dine In",
				cashier_shift: localStorage.getItem('selectedShift'),
				sales_code: localStorage.getItem('shiftCode')
			};

			// Log the thermal data to be printed
			console.log('Thermal Data to be printed:', thermalData); // Log the data

			const thermalResponse = await fetch('http://localhost/kaperustiko-possystem/src/routes/thermal_printer.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
				},
				body: JSON.stringify(thermalData)
			});

			if (!thermalResponse.ok) {
				throw new Error('Failed to print to thermal printer');
			}

			// Then proceed with saving receipt data
			const totalCost = parseFloat(localStorage.getItem('totalCost')!) || 0; // Get total cost
			const serviceCharge = parseFloat(localStorage.getItem('serviceCharge')!) || 0; // Get service charge
			const paymentAmount = parseFloat(localStorage.getItem('paymentAmount')!) || 0; // Get payment amount
			const change = parseFloat(localStorage.getItem('change')!) || 0; // Get change

			// Retrieve cashier_shift and sales_code from local storage
			const cashierShift = localStorage.getItem('selectedShift'); // Retrieve cashier_shift
			const salesCode = localStorage.getItem('shiftCode'); // Retrieve sales_code

			const receiptData = {
				receiptNumber: orderNumber,
				date: new Date().toLocaleDateString(),
				time: new Date().toLocaleTimeString(),
				cashierName: cashierName,
				waiter_name: waiterName,
				table_number: selectedCard?.table || 'Take Out',
				order_take: isTakeOut ? 'Take Out' : 'Dine In',
				amountPaid: paymentAmount, // Use retrieved payment amount
				change: change, // Use retrieved change
				service_charge: serviceCharge, // Use retrieved service charge
				totalAmount: totalCost, // Use retrieved total cost
				itemsOrdered: orderedItems.map(item => ({
					order_name: item.order_name,
					order_quantity: item.order_quantity,
					basePrice: item.basePrice,
					// Add other necessary fields here
				})),
				cashier_shift: cashierShift, // Add cashier_shift to receiptData
				sales_code: salesCode // Add sales_code to receiptData
			};

			console.log('Data to be sent in insertReceipt:', receiptData); // Log the data being sent
			const response = await fetch('http://localhost/kaperustiko-possystem/backend/modules/insert.php?action=insertReceipt', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify(receiptData)
			});

			const data = await response.json();
			if (data.error) {
				throw new Error(data.error);
			}

			// Clear waiter name from local storage
			localStorage.removeItem('waiter_name');
			console.log('Waiter name cleared from local storage.');

			showAlert('Receipt printed and saved successfully!', 'success');
			isReceiptPopupVisible = false;

			// Delete table occupancy
			const deleteResponse = await fetch(
				`http://localhost/kaperustiko-possystem/backend/modules/delete.php?action=deleteTableOccupancy&table_number=${selectedCard.table}`,
				{ method: 'DELETE' }
			);

			const deleteData = await deleteResponse.json();
			if (!deleteData.success) {
				console.error('Failed to delete table data:', deleteData.message);
			} else {
				// New code to clear local storage and reload the page
				localStorage.removeItem('totalCost');
				localStorage.removeItem('serviceCharge');
				localStorage.removeItem('paymentAmount');
				localStorage.removeItem('change');
			}

			window.location.reload();
		} catch (error: any) {
			console.error('Error:', error);
			showAlert(error.message || 'Failed to process receipt.', 'error');
		}
	}

	let isCardPopupVisible = false;
	let selectedCard: any;

	function openCardPopup(card: any) {
		isCardPopupVisible = true;
		selectedCard = card;
		waiterName = card.waiter_name; // Fetch the waiter name from the selected card
	}

	function closeCardPopup() {
		isCardPopupVisible = false;
	}

	// Function to handle checkout
	function handleCheckOut(table: string) {
		const ordersToCheckOut = queuedOrders.filter((order) => order.table_number === table);
		if (ordersToCheckOut.length > 0) {
			try {
				// Clear existing ordered items
				orderedItems = []; 
				let totalAmount = 0; // Initialize total amount variable

				// Loop through each order and parse items
				ordersToCheckOut.forEach(order => {
					const items = JSON.parse(order.items_ordered); // Parse items
					orderedItems.push(...items); // Add items to orderedItems
					totalAmount += Number(order.total_amount); // Convert total_amount to number and accumulate
				});

				totalOrderedItemsPrice = totalAmount; // Set totalOrderedItemsPrice to the total amount of all orders
				orderNumber = ordersToCheckOut[0].que_order_no; // Set the order number from the first order

				// New code to store waiter_name in local storage and log it
				waiterName = ordersToCheckOut[0].waiter_name; // Get the waiter name from the first order
				localStorage.setItem('waiter_name', waiterName); // Store in local storage
				console.log('Waiter Name:', waiterName); // Log the waiter name

				console.log(totalOrderedItemsPrice);
				closeCardPopup(); // Close the popup after checking out
			} catch (error) {
				console.error("Error parsing items_ordered JSON during checkout:", error);
				showAlert('Error processing order data. Please contact support.', 'error');
			}
		} else {
			showAlert('No orders found for this table.', 'error'); // Show alert if no orders found
		}
	}

	// Function to handle table reservation
	async function handleReserveTable() {
		const response = await fetch(
			'http://localhost/kaperustiko-possystem/backend/modules/insert.php?action=reserve_date',
			{
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify({
					reserve_date: reserveDate,
					reserve_time: reserveTime,
					table_number: selectedTableNumber
				})
			}
		);

		const result = await response.json();
		if (result.success) {
			showAlert('Table reserved successfully!', 'success');
			isReservePopupVisible = false;
			location.reload();
		} else {
			showAlert('Failed to reserve table: ' + result.error, 'error');
		}
	}

	async function openReservedTable2Popup(card: any) {
		isReservePopup2Visible = true; // Set the popup visibility to true
		selectedCard = card; // Store the selected card data

		// Fetch reserved table details
		const response = await fetch(
			`http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getInfoReserveTables&table_number=${card.table}`
		);
		if (response.ok) {
			const reservedTableDetails = await response.json();
			// You can now use reservedTableDetails to display more information in the popup if needed
			reserveDate = reservedTableDetails[0]?.reserve_date || ''; // Assuming the response contains reserve_date
			reserveTime = reservedTableDetails[0]?.reserve_time || ''; // Assuming the response contains reserve_time
		} else {
			console.error('Failed to fetch reserved table details:', response.statusText);
		}
	}

	async function handleDeleteReservation() {
		const response = await fetch(
			`http://localhost/kaperustiko-possystem/backend/modules/delete.php?action=deleteByTableNumber&table_number=${selectedCard.table}`,
			{
				method: 'DELETE'
			}
		);

		const result = await response.json();
		if (result.success) {
			showAlert('Reservation deleted successfully!', 'success');
			isReservePopup2Visible = false; // Close the popup
			location.reload(); // Optionally reload to reflect changes
		} else {
			showAlert('Failed to delete reservation: ' + result.message, 'error');
		}
	}

	let clickedKey: string | null = null; // Variable to track the clicked key

	// Function to handle keydown events
	function handleKeyDown(event: KeyboardEvent) {
		if (event.key === 'Escape') {
			// Close popups when 'Esc' is pressed
			if (isCodePopupVisible) {
				closeCodePopup();
			}
			if (isReservePopupVisible) {
				isReservePopupVisible = false;
			}
			if (isReservePopup2Visible) {
				isReservePopup2Visible = false;
			}
			if (isReceiptPopupVisible) {
				isReceiptPopupVisible = false;
			}
			if (isCardPopupVisible) {
				closeCardPopup();
			}
		} else if (event.key === 'Enter') {
			// Confirm actions when 'Enter' is pressed
			if (isCodePopupVisible) {
				confirmVoid();
			}
			if (isReservePopupVisible) {
				handleReserveTable();
			}
			if (isReceiptPopupVisible) {
				printReceipt();
			}
			// Trigger the button click functionality for placing an order
			handleButtonClick(
				'Place Order',
				0,
				orderedItems,
				payment,
				handleBackspace,
				handleClear,
				voidOrder,
				handlePlaceOrder,
				handleNumberInput,
				isDineIn,
				isTakeOut
			);
		}
	}

	// Add these variables near the top of the script section with other variable declarations
	let totalPax = 1;
	let seniorCount = 0;
	let seniorDiscount = 0;

	// Add this function in the script section
	function calculateSeniorDiscount() {
		if (totalPax < 1) totalPax = 1;
		if (seniorCount > totalPax) seniorCount = totalPax;
		if (seniorCount < 0) seniorCount = 0;

		// Calculate per person amount
		const perPersonAmount = totalOrderedItemsPrice / totalPax;
		
		// Calculate senior discount (20% of their portion)
		seniorDiscount = (perPersonAmount * 0.20) * seniorCount;
	}

	// Add reactive statement to recalculate discount when total price changes
	$: {
		if (totalOrderedItemsPrice) {
			calculateSeniorDiscount();
		}
	}

	let total = 0; // Initialize total variable

	// Reactive statement to calculate total whenever relevant values change
	$: total = Math.round(totalOrderedItemsPrice - Math.round(seniorDiscount) + (includeServiceCharge ? Math.round(totalOrderedItemsPrice * 0.05) : 0));

	// Example array of occupied table numbers (this should come from your actual data)
	let occupiedTableNumbers = ['1', '2', 'O1', 'G1', 'T1']; // Replace with actual occupied table numbers
</script>

<div class="flex h-screen">
	<Sidebar />
	<div class="flex flex-grow overflow-hidden bg-gray-100">
		<div class="flex-start w-full overflow-auto p-4">
			<div class="mb-4 flex w-full space-x-4">
				{#each ['All', 'Occupied', 'Outdoor', 'Garden', 'Take Out'] as category}
					<button
						class="w-full rounded-md px-6 py-2 font-bold text-black"
						class:bg-cyan-950={selectedCategory === category}
						class:text-white={selectedCategory === category}
						class:bg-white={selectedCategory !== category}
						class:shadow-md={selectedCategory !== category}
						on:click={() => {
							selectedCategory = category; // Update selected category
						}}
					>
						{category}
					</button>
				{/each}
				<button
					class="w-full rounded-md bg-cyan-950 px-6 py-2 font-bold text-white hover:bg-blue-600"
					on:click={() => (isReservePopupVisible = true)}
				>
					Reserve Table
				</button>
			</div>

			<div class="mb-4 flex items-center justify-between font-bold text-black">
				{#if selectedCategory === 'All'}
					<p>Display All Tables</p>
				{:else if selectedCategory === 'Occupied'}
					<p>Display Occupied Tables</p>
				{:else if selectedCategory === 'Outdoor'}
					<p>Display Outdoor Tables</p>
				{:else if selectedCategory === 'Garden'}
					<p>Display Garden Tables</p>
				{:else if selectedCategory === 'Take Out'}
					<p>Display Take Out Tables</p>
				{/if}
				<p class="mr-4">{currentDay} - {currentTime}</p>
			</div>

			<!-- Conditional rendering of cards based on selected category -->
			{#if selectedCategory === 'All'}
				<div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6">
					{#each [...cards, ...outdoorCards, ...gardenCards, ...takeoutCards] as card}
						<button
							class={`border-2 ${orders.some((order) => order.table_number === card.table) ? 'border-white bg-cyan-950 text-white' : reservedTables.includes(card.table) ? 'border-white bg-red-950 text-white' : 'border-cyan-950 bg-white text-black'} flex flex-col items-center justify-center rounded-full p-8 shadow-lg`}
							on:click={() => {
								if (reservedTables.includes(card.table)) {
									openReservedTable2Popup(card);
								} else if (orders.some((order) => order.table_number === card.table)) {
									openCardPopup(card);
								}
							}}
							aria-label={`Open popup for table ${card.table}`}
						>
							<h3 class="text-5xl font-bold">{card.table}</h3>
							<span
								class={`mt-2 rounded-full bg-gray-200 px-4 py-2 text-xs text-gray-500 ${orders.some((order) => order.table_number === card.table) ? 'bg-red-500 text-white' : reservedTables.includes(card.table) ? 'bg-red-500 text-white' : 'bg-green-500 text-white'}`}
							>
								{orders.some((order) => order.table_number === card.table)
									? 'Occupied'
									: reservedTables.includes(card.table)
										? 'Reserved'
										: 'Available'}
							</span>
						</button>
					{/each}
				</div>
			{:else if selectedCategory === 'Occupied'}
				<div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6">
					{#each [...gardenCards, ...outdoorCards, ...takeoutCards] as card}
						{#if occupiedTableNumbers.includes(card.table)}
							<button
								class={`border-2 ${orders.some((order) => order.table_number === card.table) ? 'border-white bg-cyan-950 text-white' : reservedTables.includes(card.table) ? 'border-white bg-red-950 text-white' : 'border-cyan-950 bg-white text-black'} flex flex-col items-center justify-center rounded-full p-8 shadow-lg`}
								on:click={() => {
									if (reservedTables.includes(card.table)) {
										openReservedTable2Popup(card);
									} else if (orders.some((order) => order.table_number === card.table)) {
										openCardPopup(card);
									}
								}}
								aria-label={`Open popup for table ${card.table}`}
							>
								<h3 class="text-5xl font-bold">{card.table}</h3>
								<span
									class={`mt-2 rounded-full bg-gray-200 px-4 py-2 text-xs text-gray-500 ${orders.some((order) => order.table_number === card.table) ? 'bg-red-500 text-white' : reservedTables.includes(card.table) ? 'bg-red-500 text-white' : 'bg-green-500 text-white'}`}
								>
									{orders.some((order) => order.table_number === card.table)
										? 'Occupied'
										: reservedTables.includes(card.table)
											? 'Reserved'
											: 'Available'}
								</span>
							</button>
						{/if}
					{/each}
				</div>
			{:else if selectedCategory === 'Outdoor'}
				<div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
					{#each outdoorCards as card}
						<button
							class={`border-2 ${orders.some((order) => order.table_number === card.table) ? 'border-white bg-cyan-950 text-white' : reservedTables.includes(card.table) ? 'border-white bg-red-950 text-white' : 'border-cyan-950 bg-white text-black'} flex flex-col items-center justify-center rounded-full p-8 shadow-lg`}
							on:click={() => {
								if (reservedTables.includes(card.table)) {
									openReservedTable2Popup(card);
								} else if (orders.some((order) => order.table_number === card.table)) {
									openCardPopup(card);
								}
							}}
							aria-label={`Open popup for outdoor table ${card.table}`}
						>
							<h3 class="text-5xl font-bold">{card.table}</h3>
							<span
								class={`mt-2 rounded-full bg-gray-200 px-4 py-2 text-xs text-gray-500 ${orders.some((order) => order.table_number === card.table) ? 'bg-red-500 text-white' : reservedTables.includes(card.table) ? 'bg-red-500 text-white' : 'bg-green-500 text-white'}`}
							>
								{orders.some((order) => order.table_number === card.table)
									? 'Occupied'
									: reservedTables.includes(card.table)
										? 'Reserved'
										: 'Available'}
							</span>
						</button>
					{/each}
				</div>
			{:else if selectedCategory === 'Garden'}
				<div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6">
					{#each gardenCards as card}
						<button
							class={`border-2 ${orders.some((order) => order.table_number === card.table) ? 'border-white bg-cyan-950 text-white' : reservedTables.includes(card.table) ? 'border-white bg-red-950 text-white' : 'border-cyan-950 bg-white text-black'} flex flex-col items-center justify-center rounded-full p-6 shadow-lg`}
							on:click={() => {
								if (reservedTables.includes(card.table)) {
									openReservedTable2Popup(card);
								} else if (orders.some((order) => order.table_number === card.table)) {
									openCardPopup(card);
								}
							}}
							aria-label={`Open popup for garden table ${card.table}`}
						>
							<h3 class="text-4xl font-bold">{card.table}</h3>
							<span
								class={`mt-2 rounded-full bg-gray-200 px-4 py-2 text-xs text-gray-500 ${orders.some((order) => order.table_number === card.table) ? 'bg-red-500 text-white' : reservedTables.includes(card.table) ? 'bg-red-500 text-white' : 'bg-green-500 text-white'}`}
							>
								{orders.some((order) => order.table_number === card.table)
									? 'Occupied'
									: reservedTables.includes(card.table)
										? 'Reserved'
										: 'Available'}
							</span>
						</button>
					{/each}
				</div>
			{:else if selectedCategory === 'Take Out'}
				<div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6">
					{#each takeoutCards as card}
						<button
							class={`border-2 ${orders.some((order) => order.table_number === card.table) ? 'border-white bg-cyan-950 text-white' : reservedTables.includes(card.table) ? 'border-white bg-red-950 text-white' : 'border-cyan-950 bg-white text-black'} flex flex-col items-center justify-center rounded-full p-6 shadow-lg`}
							on:click={() => {
								if (reservedTables.includes(card.table)) {
									openReservedTable2Popup(card);
								} else if (orders.some((order) => order.table_number === card.table)) {
									openCardPopup(card);
								}
							}}
							aria-label={`Open popup for garden table ${card.table}`}
						>
							<h3 class="text-4xl font-bold">{card.table}</h3>
							<span
								class={`mt-2 rounded-full bg-gray-200 px-4 py-2 text-xs text-gray-500 ${orders.some((order) => order.table_number === card.table) ? 'bg-red-500 text-white' : reservedTables.includes(card.table) ? 'bg-red-500 text-white' : 'bg-green-500 text-white'}`}
							>
								{orders.some((order) => order.table_number === card.table)
									? 'Occupied'
									: reservedTables.includes(card.table)
										? 'Reserved'
										: 'Available'}
							</span>
						</button>
					{/each}
				</div>
			{:else}
				<!-- Render all other categories or a default message -->
				<p>Select a category to see the tables.</p>
			{/if}

			{#if isCardPopupVisible}
				<div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-70">
					<div class="w-full max-w-md rounded-lg bg-white p-6 shadow-lg max-h-[90vh] overflow-y-auto">
						<h2 class="mb-4 text-center text-2xl font-bold text-gray-800">
							Table {selectedCard.table}
						</h2>
						{#if queuedOrders.length > 0}
							{#each queuedOrders as order, itemIndex}
								{#if order.table_number === selectedCard.table}
									<div class="border-b border-gray-300 py-2">
										<p class="font-semibold">
											Order Nos: <span class="font-normal">{order.que_order_no}</span>
										</p>
										<p class="font-semibold">
											Receipt No: <span class="font-normal">{order.receipt_number}</span>
										</p>
										<p class="font-semibold">Date: <span class="font-normal">{order.date}</span></p>
										<p class="font-semibold">Time: <span class="font-normal">{order.time}</span></p>
										<p class="font-semibold">Items Ordered:</p>
										{#each (() => {
											try {
												return JSON.parse(order.items_ordered);
											} catch (error) {
												console.error("Error parsing items_ordered JSON:", error);
												console.log("Raw JSON data:", order.items_ordered);
												return []; // Return empty array in case of parsing error
											}
										})() as item, innerItemIndex}
											<div class="flex items-center justify-between border-b border-gray-200 py-2">
												<div class="flex-1">
													<p class="font-normal">Name: {item.order_name} {item.order_name2}</p>
													<p class="font-normal">Quantity: {item.order_quantity}</p>
													<p class="font-normal">Size: {item.order_size}</p>
												</div>
												<div class="flex-none text-right">
													<p class="font-normal">₱{parseFloat(item.basePrice ? String(item.basePrice) : '0').toFixed(2)}</p>
													<button
														on:click={() => promptPasswordForVoid(order.receipt_number, innerItemIndex)}
														class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-2 rounded text-xs mt-1"
													>
														Void
													</button>
												</div>
											</div>
											<p class="font-normal">Addons:</p>
											{#if item.order_addons && item.order_addons_price != null && item.order_addons_price > 0}
												<div class="flex justify-between">
													<p class="font-normal">{item.order_addons}</p>
													<p class="text-right font-normal">₱{parseFloat(item.order_addons_price ? String(item.order_addons_price) : '0').toFixed(2)}</p>
												</div>
											{/if}
											{#if item.order_addons2}
												<div class="flex justify-between">
													<p class="font-normal">{item.order_addons2}</p>
													<p class="text-right font-normal">₱{parseFloat(item.order_addons_price2 ? String(item.order_addons_price2) : '0').toFixed(2)}</p>
												</div>
											{/if}
											{#if item.order_addons3}
												<div class="flex justify-between">
													<p class="font-normal">{item.order_addons3}</p>
													<p class="text-right font-normal">₱{parseFloat(item.order_addons_price3 ? String(item.order_addons_price3) : '0').toFixed(2)}</p>
												</div>
											{/if}
										{/each}
										<div class="flex justify-between">
											<p class="text-lg font-bold">Total Price:</p>
											<p class="text-right text-lg font-normal">₱{order.total_amount}.00</p>
										</div>
									</div>
									<p class="font-semibold">
										Status: <span class="font-normal">{order.order_status}</span>
									</p>
									
								{/if}
							{/each}
						{:else}
							<p class="text-center text-gray-600">No orders for this table.</p>
						{/if}
						<div class="mt-4 flex justify-center space-x-4">
							<button
								on:click={closeCardPopup}
								class="w-full max-w-xs rounded-md bg-red-500 px-4 py-2 text-white transition hover:bg-red-600"
								>Close</button
							>
							<button
								on:click={() => handleCheckOut(selectedCard.table)}
								class="w-full max-w-xs rounded-md bg-green-500 px-4 py-2 text-white transition hover:bg-green-600"
								>Check Out</button
							>
						</div>
					</div>
				</div>
			{/if}
		</div>
	</div>

	<div class="w-[350px]">
		<div
			class="fixed right-0 top-0 flex h-full w-[350px] flex-col items-center bg-gray-100 p-4 shadow-lg"
		>
			<div class="mb-4 w-full rounded-md bg-green-800 py-2 text-center text-white">
				<p class="text-sm font-bold">Order Number {orderNumber}</p>
			</div>

			<div class="mb-4 max-h-[400px] w-full flex-grow space-y-2 overflow-y-auto">
				{#if orderedItems.length > 0}
					{#each orderedItems as item}
						<div class="rounded-lg border bg-white p-4 shadow-md">
							<p class="font-semibold">
								{item.order_name}
								{item.order_name2} {item.order_quantity}
							</p>
							<div class="mb-2 flex w-full items-center justify-between border-b pb-1">
								<p class="font-normal">{item.order_size}</p>
								<p class="text-right font-normal">₱{item.basePrice}.00</p>
							</div>

							{#if item.order_addons && item.order_addons_price != null && item.order_addons_price > 0}
								<p class="font-normal">Addons:</p>
								<div class="flex flex-col">
									{#if item.order_addons}
										<div class="flex justify-between">
											<p class="font-normal">{item.order_addons}</p>
											<p class="text-right font-normal">₱{item.order_addons_price}.00</p>
										</div>
									{/if}
									{#if item.order_addons2}
										<div class="flex justify-between">
											<p class="font-normal">{item.order_addons2}</p>
											<p class="text-right font-normal">₱{item.order_addons_price2}.00</p>
										</div>
									{/if}
									{#if item.order_addons3}
										<div class="flex justify-between">
											<p class="font-normal">{item.order_addons3}</p>
											<p class="text-right font-normal">₱{item.order_addons_price3}.00</p>
										</div>
									{/if}
								</div>
							{/if}
						</div>
					{/each}
				{:else}
					<p class="text-center text-gray-600">No items ordered yet.</p>
				{/if}
			</div>

			<div class="mt-auto w-full rounded-lg p-2 shadow-md">
				<div class="mb-4 flex w-full items-center justify-between border-b pb-2">
					<p class="text-sm font-semibold text-gray-700">Voucher Code:</p>
					<input
						type="text"
						bind:value={voucherCode}
						class="text-sm font-bold text-gray-800 flex-grow mr-2 w-[60%]"
						placeholder="Enter code"
					/>
					<button
						on:click={calculateVoucherDiscount}
						class="rounded-md bg-blue-500 px-4 py-2 text-white transition hover:bg-blue-600"
					>
						Redeem
					</button>
				</div>
				<!-- New Subtotal Display -->
				<div class="mb-4 flex w-full items-center justify-between border-b pb-2">
					<p class="text-sm font-semibold text-gray-700">Subtotal:</p>
					<p class="text-sm font-bold text-gray-800">₱{Math.round(totalOrderedItemsPrice)}</p>
				</div>
				<!-- Add Senior/PWD Discount Fields -->
				<div class="mb-4 flex w-full items-center justify-between border-b pb-2">
					<div class="flex items-center w-1/2 mr-2">
						<p class="text-sm font-semibold text-gray-700 mr-2">Total PAX:</p>
						<input
							type="number"
							bind:value={totalPax}
							min="1"
							class="text-sm font-bold text-gray-800 w-16 border rounded px-2 py-1"
							on:input={calculateSeniorDiscount}
						/>
					</div>
					<div class="flex items-center w-1/2">
						<p class="text-sm font-semibold text-gray-700 mr-2">Senior/PWD:</p>
						<input
							type="number"
							bind:value={seniorCount}
							min="0"
							max={totalPax}
							class="text-sm font-bold text-gray-800 w-16 border rounded px-2 py-1"
							on:input={calculateSeniorDiscount}
						/>
					</div>
				</div>
				<!-- New Checkbox for Service Charge -->
				<div class="mb-4 flex items-center justify-between border-b pb-2">
					<p class="text-sm font-semibold text-gray-700">Include Service Charge:</p>
					<input
						type="checkbox"
						bind:checked={includeServiceCharge}
						class="ml-2"
					/>
				</div>
				<div class="flex w-full items-center justify-between border-b pb-1">
					<p class="text-sm font-semibold text-gray-700">Senior/PWD Discount:</p>
					<p class="text-sm font-bold text-gray-800">₱{Math.round(seniorDiscount)}</p>
				</div>
				<div class="flex w-full items-center justify-between border-b pb-1">
					<p class="text-sm font-semibold text-gray-700">Total Discount:</p>
					<p class="text-sm font-bold text-gray-800">₱{Math.round(totalOrderedItemsPrice * voucherDiscount / 100)}</p>
				</div>
				<div class="flex w-full items-center justify-between border-b pb-1">
					<p class="text-sm font-semibold text-gray-700">Service Charge (5%):</p>
					<p class="text-sm font-bold text-gray-800">₱{includeServiceCharge ? Math.round(totalOrderedItemsPrice * 0.05) : 0}</p>
				</div>
				<div class="flex w-full items-center justify-between border-b pb-1">
					<p class="text-sm font-semibold text-gray-700">Total:</p>
					<p class="text-sm font-bold text-gray-800">₱{Math.round(totalOrderedItemsPrice - Math.round(totalOrderedItemsPrice * voucherDiscount / 100) - Math.round(seniorDiscount) + (includeServiceCharge ? Math.round(totalOrderedItemsPrice * 0.05) : 0))}</p>
				</div>
				<div class="flex justify-between">
					<p class="text-sm">Amount Paid:</p>
					<span class="text-sm">₱{Math.round(parseFloat(payment) || 0)}</span>
				</div>
				<div class="flex justify-between">
					<p class="text-sm">Change:</p>
					<span class="text-sm">
						₱{Math.round(Math.max(0, (Math.round(parseFloat(payment) || 0) - 
							(Math.round(totalOrderedItemsPrice - Math.round(totalOrderedItemsPrice * voucherDiscount / 100) - Math.round(seniorDiscount) + (includeServiceCharge ? Math.round(totalOrderedItemsPrice * 0.05) : 0)))
						)))}
					</span>
				</div>
			</div>

			<div class="w-full">
				<div class="mb-4">
					<input
						id="payment-input"
						type="text"
						bind:value={payment}
						placeholder="Enter amount"
						class="w-full rounded-md border border-gray-300 p-3 text-gray-800 focus:border-blue-500 focus:ring-blue-500"
					/>
				</div>
				
				<div class="grid grid-cols-1 gap-4">
					<button
						on:click={() => {
							// Place Order functionality
							handleButtonClick(
								'Place Order',
								0,
								orderedItems,
								payment,
								handleBackspace,
								handleClear,
								voidOrder,
								handlePlaceOrder,
								handleNumberInput,
								isDineIn,
								isTakeOut
							);
							
							currentInputStore.update((store) => {
								return {
									...store,
									currentInput: 'Place Order',
									amountPaid: parseFloat(amountPaid.replace('₱', '').replace(',', ''))
								};
							});
						}}
						class="rounded py-3 font-bold text-white bg-blue-600 hover:bg-blue-700 w-full"
					>
						Checkout Bill
					</button>
				</div>
			</div>
		</div>
	</div>
</div>

{#if isCodePopupVisible}
	<div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-70">
		<div class="w-full max-w-md rounded-lg bg-white p-8 shadow-lg">
			<h2 class="mb-4 text-center text-2xl font-bold">Input 6-Digit Code</h2>
			<input
				type="text"
				bind:value={inputCode}
				maxlength="6"
				class="w-full rounded border border-gray-300 p-2 text-center"
				placeholder="Enter 6-digit code"
			/>
			<div class="mt-4 flex justify-between">
				<button on:click={closeCodePopup} class="rounded-md bg-red-500 px-4 py-2 text-white"
					>Cancel</button
				>
				<button on:click={confirmVoid} class="rounded-md bg-blue-500 px-4 py-2 text-white"
					>Confirm</button
				>
			</div>
		</div>
	</div>
{/if}

{#if isReservePopupVisible}
	<div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-70">
		<div class="w-full max-w-md rounded-lg bg-white p-8 shadow-lg">
			<h2 class="mb-6 text-center text-2xl font-bold text-gray-800">Reserve a Table</h2>
			<input
				type="date"
				bind:value={reserveDate}
				class="mb-4 w-full rounded border border-gray-300 p-3 shadow-sm focus:outline-none focus:ring focus:ring-blue-200"
				min={new Date().toISOString().split('T')[0]}
			/>
			<input
				type="time"
				bind:value={reserveTime}
				class="mb-4 w-full rounded border border-gray-300 p-3 shadow-sm focus:outline-none focus:ring focus:ring-blue-200"
			/>
			<select
				bind:value={selectedTableNumber}
				class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
			>
				<option value="">Select Table Number</option>
				<!-- Indoor Tables -->
				<optgroup label="Indoor Tables">
					{#each Array(20) as _, index}
						<option
							value={(index + 1).toString()}
							class={queuedOrders.some((order) => order.table_number === (index + 1).toString())
								? 'bg-red-500 text-white'
								: reservedTables.includes((index + 1).toString())
									? 'bg-orange-950 text-white'
									: ''}
							disabled={tableStatus[index + 1]}
						>
							Table {index + 1}
						</option>
					{/each}
				</optgroup>
				
				<!-- Outdoor Tables -->
				<optgroup label="Outdoor Tables">
					{#each Array(15) as _, index}
						<option
							value={`O${index + 1}`}
							class={queuedOrders.some((order) => order.table_number === `O${index + 1}`)
								? 'bg-red-500 text-white'
								: reservedTables.includes(`O${index + 1}`)
									? 'bg-orange-950 text-white'
									: ''}
							disabled={tableStatus[`O${index + 1}`]}
						>
							Table O{index + 1}
						</option>
					{/each}
				</optgroup>
				
				<!-- Garden Tables -->
				<optgroup label="Garden Tables">
					{#each Array(6) as _, index}
						<option
							value={`G${index + 1}`}
							class={queuedOrders.some((order) => order.table_number === `G${index + 1}`)
								? 'bg-red-500 text-white'
								: reservedTables.includes(`G${index + 1}`)
									? 'bg-orange-950 text-white'
									: ''}
							disabled={tableStatus[`G${index + 1}`]}
						>
							Table G{index + 1}
						</option>
					{/each}
				</optgroup>
			</select>
			<div class="mt-6 flex justify-between">
				<button
					on:click={() => (isReservePopupVisible = false)}
					class="rounded-md bg-red-600 px-4 py-2 text-white transition hover:bg-red-700"
					>Cancel</button
				>
				<button
					on:click={handleReserveTable}
					class="rounded-md bg-blue-600 px-4 py-2 text-white transition hover:bg-blue-700"
					>Confirm</button
				>
			</div>
		</div>
	</div>
{/if}

{#if isReservePopup2Visible}
	<div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-70">
		<div class="w-full max-w-md rounded-lg bg-white p-8 shadow-lg">
			<h2 class="mb-4 text-center text-2xl font-bold">Reserved Table Details</h2>
			<p>Table Number: {selectedCard.table}</p>
			<p>Reserved Date: {reserveDate}</p>
			<p>Reserved Time: {reserveTime}</p>
			<div class="mt-4 flex justify-center space-x-4">
				<button
					on:click={() => (isReservePopup2Visible = false)}
					class="w-full max-w-xs rounded-md bg-red-500 px-4 py-2 text-white transition hover:bg-red-600"
					>Close</button
				>
				<button
					on:click={handleDeleteReservation}
					class="w-full max-w-xs rounded-md bg-red-600 px-4 py-2 text-white transition hover:bg-red-700"
					>Delete Reservation</button
				>
			</div>
		</div>
	</div>
{/if}

{#if isReceiptPopupVisible}
	<div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-70">
		<div class="rounded-lg bg-white p-8 shadow-lg max-w-lg w-full receipt-container max-h-[90vh] overflow-y-auto">
			<div class="mb-4 flex justify-center">
				<img src="icon.png" alt="Restaurant Logo" class="h-24" />
			</div>
			<h2 class="text-center text-2xl font-bold">Kape Rustiko Cafe and Restaurant</h2>
			<p class="text-center text-lg">Dewey Ave, Subic Bay Freeport Zone</p>
			<p class="text-center text-lg">VAT REG TIN: 123-456-789-12345</p>
			<h2 class="mb-4 mt-4 text-center text-2xl font-bold">SALES INVOICE</h2>
			<p class="text-lg">Transaction Date: {new Date().toLocaleDateString()}</p>
			<p class="text-lg">Transaction Time: {new Date().toLocaleTimeString()}</p>
			<p class="text-lg">Cashier Name: {cashierName}</p>
			<p class="text-lg">Receipt Number: {orderNumber}</p>
			<p class="text-lg">Waiter Name: {waiterName}</p> <!-- Added waiter name here -->
			<p class="text-lg">Table Number: {selectedCard?.table || 'Take Out'}</p>

			<div class="mt-4">
				<div class="flex justify-between font-semibold">
					<h2 class="mt-4 text-lg">Items Ordered:</h2>
					<span class="mt-4 text-lg">Price</span>
				</div>
				{#if orderedItems.length > 0}
					{#each orderedItems as item}
						<div class="flex justify-between border-b py-2">
							<p class="font-normal">{item.order_name} {item.order_name2} x{item.order_quantity}</p>
							<p class="text-right font-normal">₱{item.basePrice}.00</p>
						</div>
						{#if item.order_addons && item.order_addons_price != null && item.order_addons_price > 0}
							<p class="font-normal">Addons:</p>
							<div class="flex flex-col">
								{#if item.order_addons}
									<div class="flex justify-between">
										<p class="font-normal">{item.order_addons}</p>
										<p class="text-right font-normal">₱{item.order_addons_price}.00</p>
									</div>
								{/if}
								{#if item.order_addons2}
									<div class="flex justify-between">
										<p class="font-normal">{item.order_addons2}</p>
										<p class="text-right font-normal">₱{item.order_addons_price2}.00</p>
									</div>
								{/if}
								{#if item.order_addons3}
									<div class="flex justify-between">
										<p class="font-normal">{item.order_addons3}</p>
										<p class="text-right font-normal">₱{item.order_addons_price3}.00</p>
									</div>
								{/if}
							</div>
						{/if}
					{/each}
				{:else}
					<p class="text-center text-gray-600">No items ordered yet.</p>
				{/if}
			</div>

			<div class="mt-4 w-full rounded-lg p-2 shadow-md">
				<div class="mb-4 flex w-full items-center justify-between border-b pb-2">
					<p class="text-sm font-semibold text-gray-700">Subtotal:</p>
					<p class="text-sm font-bold text-gray-800">₱{Math.round(totalOrderedItemsPrice)}</p>
				</div>
				<div class="flex w-full items-center justify-between border-b pb-1">
					<p class="text-sm font-semibold text-gray-700">Senior/PWD Discount:</p>
					<p class="text-sm font-bold text-gray-800">₱{Math.round(seniorDiscount)}</p>
				</div>
				<div class="flex w-full items-center justify-between border-b pb-1">
					<p class="text-sm font-semibold text-gray-700">Total Discount:</p>
					<p class="text-sm font-bold text-gray-800">₱{Math.round(totalOrderedItemsPrice * voucherDiscount / 100)}</p>
				</div>
				<div class="flex w-full items-center justify-between border-b pb-1">
					<p class="text-sm font-semibold text-gray-700">Service Charge (5%):</p>
					<p class="text-sm font-bold text-gray-800">₱{includeServiceCharge ? Math.round(totalOrderedItemsPrice * 0.05) : 0}</p>
				</div>
				<div class="flex w-full items-center justify-between border-b pb-1">
					<p class="text-sm font-semibold text-gray-700">Total:</p>
					<p class="text-sm font-bold text-gray-800">₱{Math.round(totalOrderedItemsPrice - Math.round(totalOrderedItemsPrice * voucherDiscount / 100) - Math.round(seniorDiscount) + (includeServiceCharge ? Math.round(totalOrderedItemsPrice * 0.05) : 0))}</p>
				</div>
				<div class="flex justify-between">
					<p class="text-sm">Amount Paid:</p>
					<span class="text-sm">₱{Math.round(parseFloat(payment) || 0)}</span>
				</div>
				<div class="flex justify-between">
					<p class="text-sm">Change:</p>
					<span class="text-sm">
						₱{Math.round(Math.max(0, (Math.round(parseFloat(payment) || 0) - 
							(Math.round(totalOrderedItemsPrice - Math.round(totalOrderedItemsPrice * voucherDiscount / 100) - Math.round(seniorDiscount) + (includeServiceCharge ? Math.round(totalOrderedItemsPrice * 0.05) : 0)))
						)))}
					</span>
				</div>
			</div>

			<div class="mt-6 flex justify-center space-x-4">
				<button 
					on:click={() => previewReceipt()} 
					class="rounded-md bg-blue-500 px-6 py-2 text-white transition hover:bg-blue-600"
				>
					Preview Receipt
				</button>
				<button 
					on:click={() => printReceipt()} 
					class="rounded-md bg-green-500 px-6 py-2 text-white transition hover:bg-green-600"
				>
					Print & Save
				</button>
				<button 
					on:click={() => isReceiptPopupVisible = false} 
					class="rounded-md bg-red-500 px-6 py-2 text-white transition hover:bg-red-600"
				>
					Cancel
				</button>
			</div>
		</div>
	</div>
{/if}