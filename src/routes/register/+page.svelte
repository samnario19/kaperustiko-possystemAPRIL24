<script lang="ts">
    let email = '';
    let password = '';
    let firstName = '';
    let lastName = '';
    let middleName = '';
    let contactNumber = '';
    
    // New state variables for error messages
    let emailError = '';
    let passwordError = '';
    let firstNameError = '';
    let lastNameError = '';
    let contactNumberError = '';

    async function handleSubmit() {
        // Reset error messages
        emailError = '';
        passwordError = '';
        firstNameError = '';
        lastNameError = '';
        contactNumberError = '';

        // Basic validation
        if (!email) emailError = 'Email is required';
        if (!password) passwordError = 'Password is required';
        if (!firstName) firstNameError = 'First Name is required';
        if (!lastName) lastNameError = 'Last Name is required';
        if (!contactNumber) contactNumberError = 'Contact Number is required';

        // If there are any errors, stop the submission
        if (emailError || passwordError || firstNameError || lastNameError || contactNumberError) {
            return;
        }

        const response = await fetch('http://localhost/kaperustiko-possystem/backend/modules/auth.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'register',
                firstName: firstName,
                lastName: lastName,
                middleName: middleName,
                email: email,
                password: password,
                contactNumber: contactNumber
            })
        });

        try {
            const result = await response.text();
            let jsonResult;
            
            try {
                jsonResult = JSON.parse(result);
            } catch (e) {
                // If response is not JSON, handle as before
                if (result.includes("success")) {
                    showAlert('Registration Successful', 'success');
                    setTimeout(() => {
                        window.location.href = '/login';
                    }, 3000);
                }
                return;
            }

            if (jsonResult.status === 'success') {
                // Show success message with waiter code
                showAlert(`Registration Successful! Your staff code is: ${jsonResult.waiter_code}`, 'success');
                setTimeout(() => {
                    window.location.href = '/login'; // Redirect to login page after 3 seconds
                }, 5000); // 5000 milliseconds = 5 seconds (giving extra time to see the waiter code)
            } else if (jsonResult.status === 'error') {
                // Handle specific error messages
                if (jsonResult.message.includes("Email already exists")) {
                    emailError = 'Email already exists';
                } else if (jsonResult.message.includes("Contact number already exists")) {
                    contactNumberError = 'Contact number already exists';
                } else {
                    // General error
                    showAlert(jsonResult.message, 'error');
                }
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('An error occurred during registration', 'error');
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

</script>


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
            <h2 class="text-3xl font-extrabold mb-4 text-center text-gradient">Be guided by system administrator</h2>
            <p class="text-center mb-6 text-gray-600">To get started, please fill in your details to create an account.</p>
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" bind:value={email} required class="mt-1 block w-full p-2 border border-gray-300 rounded" placeholder="Enter your email"/>
                {#if emailError}<p class="text-red-500 text-sm">{emailError}</p>{/if} <!-- Error message for email -->
            </div>
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="password" bind:value={password} required class="mt-1 block w-full p-2 border border-gray-300 rounded" placeholder="Enter your password"/>
                {#if passwordError}<p class="text-red-500 text-sm">{passwordError}</p>{/if} <!-- Error message for password -->
            </div>
            <div class="mb-4">
                <label for="firstName" class="block text-sm font-medium text-gray-700">First Name</label>
                <input type="text" id="firstName" bind:value={firstName} required class="mt-1 block w-full p-2 border border-gray-300 rounded" placeholder="Enter your first name" style="text-transform: capitalize;"/>
                {#if firstNameError}<p class="text-red-500 text-sm">{firstNameError}</p>{/if} <!-- Error message for first name -->
            </div>
            <div class="mb-4">
                <label for="lastName" class="block text-sm font-medium text-gray-700">Last Name</label>
                <input type="text" id="lastName" bind:value={lastName} required class="mt-1 block w-full p-2 border border-gray-300 rounded" placeholder="Enter your last name" style="text-transform: capitalize;"/>
                {#if lastNameError}<p class="text-red-500 text-sm">{lastNameError}</p>{/if} <!-- Error message for last name -->
            </div>
            <div class="mb-4">
                <label for="middleName" class="block text-sm font-medium text-gray-700">Middle Name</label>
                <input type="text" id="middleName" bind:value={middleName} class="mt-1 block w-full p-2 border border-gray-300 rounded" placeholder="Enter your middle name (optional)" style="text-transform: capitalize;"/>
            </div>
            <div class="mb-4">
                <label for="contactNumber" class="block text-sm font-medium text-gray-700">Contact Number</label>
                <input type="tel" id="contactNumber" bind:value={contactNumber} required class="mt-1 block w-full p-2 border border-gray-300 rounded" placeholder="Enter your contact number"/>
                {#if contactNumberError}<p class="text-red-500 text-sm">{contactNumberError}</p>{/if} <!-- Error message for contact number -->
            </div>
            <button type="submit" class="w-full bg-blue-900 text-white p-2 rounded hover:bg-sky-600">Register</button>
            <p class="mt-4 text-center">Already Have an Account? <button type="button" class="text-red-500" on:click={() => window.location.href='/login'}>Login Now</button></p>
        </form>
    </div>
</div>
