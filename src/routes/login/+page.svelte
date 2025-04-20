<script lang="ts">


	let email = '';
	let password = '';
	let isLoading = false; // New state variable to control loader visibility
	let emailError = ''; // State variable for email error
	let passwordError = ''; // State variable for password error
	let accountError = ''; // State variable for general account error
	let activeInput = ''; // Track which input is currently active
	let typingTimeout: NodeJS.Timeout; // Specify the type for typingTimeout
	let showAccountError = false; // Flag to control the display of account error messages
	let showResetPasswordPopup = false; // New state variable for reset password popup
	let resetEmail = ''; // State variable to hold the email for password reset
	let showCodeInput = false; // New state variable for code input form
	let showNewPasswordForm = false; // New state variable for new password form
	let resetCode = ''; // State variable to hold the code entered by the user
	let newPassword = ''; // State variable for new password
	let repeatPassword = ''; // State variable for repeated password

	async function handleLogin() {
		console.log('Logging in with', email, password);
		
		isLoading = true; // Set loading state to true

		console.log('Fetching login request'); // Log before login fetch
		// Send login request to the backend
		const response = await fetch('http://localhost/kaperustiko-possystem/backend/modules/auth.php', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: new URLSearchParams({
				action: 'login',
				email: email,
				password: password
			}).toString()
		});

		const result = await response.json(); // Parse JSON response
		console.log(result.message); // Log the response message

		isLoading = false; // Reset loading state

		if (result.status === "success") { // Check for success status
			console.log('Login Successful', result.staff_token); // Log the staff_token
			localStorage.setItem('staff_token', result.staff_token); // Store staff_token in local storage
			showAlert('Login Successful', 'success'); // Call showAlert with success type
			
			setTimeout(() => {
				isLoading = true; // Set loading state to true
				window.location.href = '/main-pos'; // Redirect after 3 seconds
			}, 3000); // 3000 milliseconds = 3 seconds
			
			// New lines to open additional windows
			const baseUrl = window.location.origin; // Get the base URL
			window.open(`${baseUrl}/waiter-monitor`, '_blank', 'location=no,menubar=no,scrollbars=no,status=no,resizable=no'); // Opens waiter monitor
		} else {
			// Reset error messages
			emailError = '';
			passwordError = '';
			accountError = '';

			// Set error messages based on response
			if (result.message.includes("password")) {
				passwordError = 'Wrong password'; // Set wrong password message
			} else if (result.message.includes("email")) {
				emailError = result.message; // Set email error message
			} else {
				accountError = 'Invalid account credentials'; // Set general account error message
			}
			showAccountError = true; // Show account error after submission
		}
	}

	function validateForm() {
		let isValid = true;
		emailError = '';
		passwordError = '';
		accountError = ''; // Reset account error

		// Validate email format
		const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
		if (activeInput === 'email' && !emailPattern.test(email)) {
			emailError = 'Please enter a valid email (format: youremail@gmail.com)';
			isValid = false;
		}

		return isValid;
	}

	// New function to handle input changes with debounce
	function handleInputChange() {
		clearTimeout(typingTimeout); // Clear the previous timeout
		typingTimeout = setTimeout(() => {
			validateForm(); // Validate form after a delay
		}, 300); // Adjust the delay as needed (300ms in this case)
	}

	// New function to handle input focus
	function handleFocus(input: string) {
		activeInput = input; // Set the active input
		validateForm(); // Validate form on focus
	}

	function handleSubmit() {
		if (validateForm()) {
			 handleLogin(); 
		}
	}

	// Update showAlert function to handle success type
	function showAlert(message: string, type: string) {
		const alertDiv = document.createElement('div');
		alertDiv.className = `fixed top-0 left-1/2 transform -translate-x-1/2 mt-4 p-4 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white rounded shadow-lg`;
		alertDiv.innerText = message;
		document.body.appendChild(alertDiv);
		setTimeout(() => {
			alertDiv.remove();
		}, 3000); // Remove alert after 3 seconds
	}

	function openResetPasswordPopup() {
		showResetPasswordPopup = true; // Show the reset password popup
	}

	function closeResetPasswordPopup() {
		showResetPasswordPopup = false; // Hide the reset password popup
	}

	function openCodeInput() {
		showCodeInput = true; // Show the code input popup
	}

	function closeCodeInput() {
		showCodeInput = false; // Hide the code input popup
	}

	function openNewPasswordForm() {
		showNewPasswordForm = true; // Show the new password form
	}

	function closeNewPasswordForm() {
		showNewPasswordForm = false; // Hide the new password form
	}

	// New function to handle password reset submission
	async function handleResetPassword() {
		console.log('Sending reset code to email'); // Log before reset password fetch
		console.log('Reset password for', resetEmail);
		
		// Fetch waiter code by email
		console.log('Fetching waiter code by email'); // Log before fetching waiter code
		const waiterResponse = await fetch(`http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getWaiterCodeByEmail&email=${resetEmail}`);
		const waiterResult = await waiterResponse.json();
		if (waiterResult.waiter_code) {
			console.log('Waiter Code:', waiterResult.waiter_code); // Log the waiter code
			
			// New logic to send reset code to the user's email
			const emailResponse = await fetch('http://localhost/kaperustiko-possystem/backend/modules/sendEmail.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
				},
				body: JSON.stringify({
					email: resetEmail,
					waiterCode: waiterResult.waiter_code
				})
			});
			const emailResult = await emailResponse.json();
			
			// Log the email result to see its structure
			console.log('Email Result:', emailResult); // Log the entire email result

			// Check if the email was sent successfully before showing the code input
			if (emailResult.success) { // Assuming emailResult has a success property
				
			} else {
				closeResetPasswordPopup();
				openCodeInput();
			}
			console.log(emailResult.message); // Log the email sending result
		} else {
			console.error('Waiter code not found:', waiterResult.error); // Log any error
		}
	}


	async function handleVerifyCode() {
		console.log('Verifying code:', resetCode); // Log the code being verified

		// Send request to verify the reset code
		const response = await fetch(`http://localhost/kaperustiko-possystem/backend/modules/get.php?action=verifyResetCode&code=${resetCode}`);
		const result = await response.json(); // Parse JSON response

		if (result.error) {
			console.error(result.error); // Log error if code is not found
			showAlert('Invalid code. Please try again.', 'error'); // Show error alert
			return false; // Return false if verification fails
		} else if (result.status === "success") { // Check for success status
			console.log('Code verified:', result); // Log the result if code is valid
			// Proceed to show the new password form
			showNewPasswordForm = true; // Show the new password form
			closeCodeInput(); // Close the code input popup
			openNewPasswordForm();
			return true; // Return true if verification is successful
		}
	}

	// New function to handle new password submission
	async function handleSubmitNewPassword() {
		// New logic to verify the reset code before updating the password
		const isCodeValid = await handleVerifyCode(); // Call the new function to verify the code
		if (!isCodeValid) {
			console.error('Invalid reset code'); // Log if the code is invalid
			showAlert('Invalid reset code. Please try again.', 'error'); // Show error alert
			return; // Exit the function if the code is invalid
		}

		if (newPassword === repeatPassword) {
			console.log('Submitting new password:', newPassword);
			const waiterCode = resetCode; // Use resetCode as waiter_code

			const response = await fetch('http://localhost/kaperustiko-possystem/backend/modules/update_password.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
				},
				body: JSON.stringify({ newPassword, waiterCode }) // Include the waiter_code
			});
			const result = await response.json();
			console.log(result.message);
			window.location.reload();
		} else {
			console.error('Passwords do not match');
		}
	}

</script>

{#if isLoading} <!-- Conditional rendering for loader -->
<div class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 z-50">
    
<div role="status">
    <svg fill="currentColor" viewBox="0 0 1792 1792" class="text-green-500 w-20 h-20 animate-spin"
    xmlns="http://www.w3.org/2000/svg">
    <path
        d="M1760 896q0 176-68.5 336t-184 275.5-275.5 184-336 68.5-336-68.5-275.5-184-184-275.5-68.5-336q0-213 97-398.5t265-305.5 374-151v228q-221 45-366.5 221t-145.5 406q0 130 51 248.5t136.5 204 204 136.5 248.5 51 248.5-51 204-136.5 136.5-204 51-248.5q0-230-145.5-406t-366.5-221v-228q206 31 374 151t265 305.5 97 398.5z" />
</svg>
    <span class="sr-only">Loading...</span>
</div>

</div>
{/if}
<div class="flex flex-col min-h-screen md:flex-row">
    <div class="flex-1 bg-cyan-950 flex items-center justify-center relative left-section">
       
        <ul class="circles">
            <li></li>
            <li></li>
            <li></li>
            <li></li>
            <li></li>
            <li></li>
            <li></li>
            <li></li>
            <li></li>
            <li></li>
        </ul>
        <div class="image-container">
            <img src="./icon.png" alt="Fallback description if image fails to load" class="max-w-full h-auto" aria-hidden="true" />
        </div>
        <div class="absolute bottom-10 left-10 text-white text-lg">
            <h1 class="font-bold">Welcome to Kape Rustiko POS System</h1>
            <p class="add">Your gateway to an exceptional POS experience. Discover new opportunities with us!</p>
        </div>
    </div>
    <div class="flex-1 flex items-center justify-center">
        <form on:submit|preventDefault={handleSubmit} class="p-6 w-full md:w-2/3">
            <h2 class="text-3xl font-extrabold mb-4 text-center text-gradient">Welcome Back to Your POS System!</h2>
            <p class="text-center mb-6 text-gray-600">Please enter your credentials to access the system.</p>
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" bind:value={email} on:input={handleInputChange} on:focus={() => handleFocus('email')} required placeholder="Enter your email" class="mt-1 block w-full p-2 border border-gray-300 rounded" />
                {#if activeInput === 'email' && emailError}<p class="text-red-500 text-sm">{emailError}</p>{/if} <!-- Error message for email -->
            </div>
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="password" bind:value={password} on:input={handleInputChange} on:focus={() => handleFocus('password')} required placeholder="Enter your password" class="mt-1 block w-full p-2 border border-gray-300 rounded" />
                {#if passwordError}<p class="text-red-500 text-sm">{passwordError}</p>{/if} <!-- Error message for password -->
                {#if showAccountError}<p class="text-red-500 text-sm">{accountError}</p>{/if} <!-- Error message for invalid account -->
            </div>
            <button type="submit" class="w-full bg-blue-900 text-white p-2 rounded hover:bg-sky-600">Log In</button>
            <p class="mt-4 text-center">Don't Have an Account? <button type="button" class="text-red-500" on:click={() => window.location.href='/register'}>Register</button> | Forgot your password? <button type="button" class="text-blue-500" on:click={openResetPasswordPopup}>Reset it here</button></p>
            <p class="mt-2 text-center text-sm text-gray-500">Version 1.0.0 Developed by Sam Nario & Michael Dayandante © 2025</p>
        </form>
    </div>
</div>

<!-- Popup for reset password -->
{#if showResetPasswordPopup}
<div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
    <div class="bg-white p-8 rounded-lg shadow-lg max-w-sm w-full">
        <h2 class="text-2xl font-bold mb-4 text-center">Enter Your Email</h2>
        <p class="text-center mb-4">Please enter your email to receive a reset code.</p>
        <input type="email" bind:value={resetEmail} placeholder="Enter your email" class="mt-2 block w-full p-3 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required />
        <button type="button" on:click={() => handleResetPassword()} class="mt-4 w-full bg-blue-900 text-white p-3 rounded hover:bg-blue-700 transition duration-200">Send Reset Code</button>
        <button type="button" on:click={closeResetPasswordPopup} class="mt-2 w-full text-red-500 hover:underline">Cancel</button>
    </div>
</div>
{/if}

{#if showCodeInput}
<div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
    <div class="bg-white p-8 rounded-lg shadow-lg max-w-sm w-full">
        <h2 class="text-2xl font-bold mb-4 text-center">Enter the Code</h2>
        <p class="text-center mb-4">We sent a code to your email, please put the code here.</p>
        <input type="text" bind:value={resetCode} placeholder="Enter the code" class="mt-2 block w-full p-3 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required />
        <button type="button" on:click={handleVerifyCode} class="mt-4 w-full bg-blue-900 text-white p-3 rounded hover:bg-blue-700 transition duration-200">Confirm Identity</button>
        <button type="button" on:click={closeCodeInput} class="mt-2 w-full text-red-500 hover:underline">Cancel</button>
    </div>
</div>
{/if}

{#if showNewPasswordForm}
<div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
    <div class="bg-white p-8 rounded-lg shadow-lg max-w-sm w-full">
        <h2 class="text-2xl font-bold mb-4 text-center">Set New Password</h2>
        <input type="password" bind:value={newPassword} placeholder="Enter your new password" class="mt-2 block w-full p-3 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required />
        <input type="password" bind:value={repeatPassword} placeholder="Repeat your new password" class="mt-2 block w-full p-3 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required />
        <button type="button" on:click={handleSubmitNewPassword} class="mt-4 w-full bg-blue-900 text-white p-3 rounded hover:bg-blue-700 transition duration-200">Submit New Password</button>
        <button type="button" on:click={closeNewPasswordForm} class="mt-2 w-full text-red-500 hover:underline">Cancel</button>
    </div>
</div>
{/if}