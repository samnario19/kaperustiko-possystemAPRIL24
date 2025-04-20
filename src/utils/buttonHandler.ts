// src/utils/buttonHandler.ts
export function handleButtonClick(key: string, index: number, orderedItems: any[], payment: string, handleBackspace: Function, handleClear: Function, voidOrder: Function, handlePlaceOrder: Function, handleNumberInput: Function, isDineIn: boolean, isTakeOut: boolean) {
	if (key === '⌫') {
		handleBackspace();
	} else if (key === 'Clr') {
		handleClear();
	} else if (key === 'Void') {
		voidOrder(index);
	} else if (key === 'Place Order') {
		const order_price = orderedItems.reduce((total, item) => {
			const price = item.total_amount ? item.total_amount : 0;
			return total + price;
		}, 0);
		console.log('Calculated Order Price:', order_price);
		const amountPaid = parseFloat(payment.replace('₱', '').replace(',', ''));

		console.log('Amount Paid:', amountPaid);

		if (!payment || amountPaid <= 0) {
			showAlert('Error: No amount paid yet.');
		} else if (amountPaid < order_price) {
			showAlert('Error: Not enough amount.');
		} else if (amountPaid >= order_price) {
			handlePlaceOrder();
		}
	} else {
		handleNumberInput(key);	
	}
}

// New function to show a styled alert
function showAlert(message: string) {
	const alertDiv = document.createElement('div');
	alertDiv.className = 'fixed top-0 left-1/2 transform -translate-x-1/2 mt-4 p-4 bg-red-500 text-white rounded shadow-lg';
	alertDiv.innerText = message;
	document.body.appendChild(alertDiv);
	setTimeout(() => {
		alertDiv.remove();
	}, 3000); // Remove alert after 3 seconds
}