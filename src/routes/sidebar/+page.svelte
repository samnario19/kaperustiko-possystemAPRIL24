<script lang="ts">
    import { FontAwesomeIcon } from '@fortawesome/svelte-fontawesome';
    import { faUndo, faSignOutAlt, faChevronLeft, faClipboardCheck, faChevronRight, faCashRegister, faExchangeAlt, faBoxOpen, faDashboard, faTicketAlt, faUserPlus } from '@fortawesome/free-solid-svg-icons';
    import { onMount } from 'svelte';
    let isActive = { mainPos: false, transaction: false, dashboard: false, inventory: false, delivery: false, return: false, vouchers: false, addWaiter: false }; // Define isActive without activeIcon
    let showModal = false; // Define showModal to control the modal visibility
    let sidebarWidth = 'w-18'; // Define a variable for sidebar width
    let userFirstName = ''; // Variable to store the user's first name
    let showSecondPopup = false; // Define a variable to control the second popup visibility
    let inputCode = ''; // Variable to bind the input code
    let code = '123456'; // The correct code for confirmation
    let currentIcon: 'inventory' | 'dashboard' | 'vouchers' | null = null;
    let showAddWaiterPopup = false; // Variable to control the add waiter popup visibility
    let waiterName = ''; // Variable to store the waiter's name
    let lastName = ''; // Variable to store the waiter's last name
    let middleName = ''; // Variable to store the waiter's middle name
    let firstName = ''; // Variable to store the waiter's first name
    let waiterCode = ''; // Variable to store the generated waiter's code

    function setActive(icon: keyof typeof isActive) {
        // Reset all to false
        for (const key in isActive) {
            isActive[key as keyof typeof isActive] = false; // Cast key to the correct type
        }
        // Set the clicked icon to true
        isActive[icon] = true; // No need for activeIcon
    }

    onMount(async () => {
        const currentPath = window.location.pathname; // Get the current path
        if (currentPath === '/main-pos') {
            setActive('mainPos'); 
        }
        else if (currentPath === '/transaction') {
            setActive('transaction');
        }
        else if (currentPath === '/dashboard') {
            setActive('dashboard');
        }
        else if (currentPath === '/inventory') {
            setActive('inventory');
        }
        else if (currentPath === '/delivery') {
            setActive('delivery');
        }
        else if (currentPath === '/return') {
            setActive('return');
        }

        // Fetch the user's first name using staff_token
        const staffToken = localStorage.getItem('staff_token'); // Assuming the token is stored in localStorage
        if (staffToken) {
            const response = await fetch(`http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getUser&staff_token=${staffToken}`);
            if (response.ok) {
                const data = await response.json();
                userFirstName = data; // Set the user's first name
            }
        }
    });

    function handleLogout() {
        showModal = true; // Show the modal when logout is initiated
    }

    function confirmLogout() {
        localStorage.removeItem('userSession'); 
        window.location.href = '/login'; 
    }

    function cancelLogout() {
        showModal = false; // Close the modal without logging out
    }

    function toggleSidebarWidth() {
        sidebarWidth = sidebarWidth === 'w-18' ? 'w-[220px]' : 'w-18'; // Toggle width
    }

    function handleInventoryClick() {
        showSecondPopup = true; // Show the popup when inventory is clicked
        currentIcon = 'inventory'; // Store the current icon type
    }

    function handleDashboardClick() {
        showSecondPopup = true; // Show the popup when dashboard is clicked
        currentIcon = 'dashboard'; // Store the current icon type
    }

    function handleVouchersClick() {
        showSecondPopup = true; // Show the popup when vouchers is clicked
        currentIcon = 'vouchers'; // Store the current icon type
    }

    function confirmAuth(icon: 'inventory' | 'dashboard' | 'vouchers') {
        if (inputCode === code) {
            // Proceed with the action if the code is correct
            showSecondPopup = false; // Close the popup
            showAlert('Code confirmed successfully!', 'success'); // Show success alert
            // Navigate to the href of the clicked icon
            window.location.href = icon === 'inventory' ? '/inventory' : icon === 'dashboard' ? '/dashboard' : '/vouchers'; // Change this based on the clicked icon
        } else {
            showAlert('Incorrect code. Please try again.', 'error'); // Show error alert
        }
    }

    function closeSecondPopup() {
        showSecondPopup = false; // Close the popup
        inputCode = ''; // Reset the input code
    }

    function showAlert(message: string, type: string) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `fixed top-0 left-1/2 transform -translate-x-1/2 mt-4 p-4 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white rounded shadow-lg z-50`;
        alertDiv.innerText = message;
        document.body.appendChild(alertDiv);
        setTimeout(() => {
            alertDiv.remove();
        }, 3000); // Remove alert after 3 seconds
    }

    function generateRandomWaiterCode() {
        const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'; // Define characters to choose from
        let code = '';
        for (let i = 0; i < 6; i++) {
            const randomIndex = Math.floor(Math.random() * characters.length); // Get a random index
            code += characters[randomIndex]; // Append a random character to the code
        }
        waiterCode = code; // Set the generated code
    }

    function openAddWaiterPopup() {
        generateRandomWaiterCode(); // Generate a new waiter code when opening the popup
        showAddWaiterPopup = true; // Show the add waiter popup
    }

    function closeAddWaiterPopup() {
        showAddWaiterPopup = false; // Close the add waiter popup
        firstName = ''; // Reset the waiter's first name
        middleName = ''; // Reset the waiter's middle name
        lastName = ''; // Reset the waiter's last name
    }

    function submitAddWaiter() {
        // Prepare the data to be sent
        const waiterData = {
            firstName: firstName,
            middleName: middleName,
            lastName: lastName,
            waiterCode: waiterCode
        };

        // Make an API call to insert the waiter
        fetch('http://localhost/kaperustiko-possystem/backend/modules/insert.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(waiterData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showAlert('Waiter added successfully!', 'success');
            } else {
                showAlert('Error adding waiter: ' + data.message, 'error');
            }
        })
        .catch(error => {
            showAlert('Error: ' + error.message, 'error');
        });

        closeAddWaiterPopup(); // Close the popup after submission
    }

</script>

<div class={`flex flex-col h-screen bg-cyan-950 ${sidebarWidth} sidebar`}>
    <div class="flex justify-center">
        {#if sidebarWidth === 'w-[220px]'}
            <img src="./icon2.png" alt="Logo" class="w-[200px] h-[200px] mt-[-15px] mb-[-5px] " />
        {:else}
            <img src="./icon.png" alt="Logo" class="w-[50px] h-[50px] mt-4 mb-[100px] " />
        {/if}
    </div>
    <div class="flex items-center p-4 hover:bg-cyan-600 justify-center mb-[50px]">
        <button type="button" on:click={() => { toggleSidebarWidth(); }} class="flex items-center justify-center">
            {#if sidebarWidth === 'w-[220px]'}
                <span class="text-white text-2xl font-bold">
                    Collapse
                </span>
            {:else}
                <span class="text-white">
                    <FontAwesomeIcon icon={faChevronRight} class="text-3xl" />
                </span>
            {/if}
        </button>
    </div>
    <div class="flex items-center p-4 hover:bg-cyan-600 justify-center" class:bg-white={isActive.mainPos} class:rounded-[4px]={isActive.mainPos} class:ml-[5px]={isActive.mainPos} class:mr-[5px]={isActive.mainPos} class:h-[50px]={isActive.mainPos}> 
        <button type="button" on:click={() => { window.location.href = '/main-pos'; setActive('mainPos'); }} class="flex items-center justify-center">
            {#if sidebarWidth === 'w-[220px]'}
                <span class:text-cyan-950={isActive.mainPos} class:text-white={!isActive.mainPos} class="text-2xl font-bold">Main POS</span>
            {:else}
                <span class:text-white={!isActive.mainPos}>
                    <FontAwesomeIcon icon={faCashRegister} class="text-3xl" />
                </span>
            {/if}
        </button>
    </div>
    <div class="flex items-center p-4 hover:bg-cyan-600 justify-center" class:bg-white={isActive.transaction} class:rounded-[4px]={isActive.transaction} class:ml-[5px]={isActive.transaction} class:mr-[5px]={isActive.transaction} class:h-[50px]={isActive.transaction}>
        <button type="button" on:click={() => { window.location.href = '/transaction'; setActive('transaction'); }} class="flex items-center justify-center">
            {#if sidebarWidth === 'w-[220px]'}
                <span class:text-cyan-950={isActive.transaction} class:text-white={!isActive.transaction} class="text-2xl font-bold">Transaction</span>
            {:else}
                <span class:text-white={!isActive.transaction}>
                    <FontAwesomeIcon icon={faClipboardCheck} class="text-3xl" />
                </span>
            {/if}
        </button>
    </div>
    <div class="flex items-center p-4 hover:bg-green-600 justify-center" class:bg-white={isActive.return} class:rounded-[4px]={isActive.return} class:ml-[5px]={isActive.return} class:mr-[5px]={isActive.return} class:h-[50px]={isActive.return}>
        <button type="button" on:click={() => { window.location.href = '/return'; setActive('return'); }} class="flex items-center justify-center">
            {#if sidebarWidth === 'w-[220px]'}
                <span class:text-cyan-950={isActive.return} class:text-white={!isActive.return} class="text-2xl font-bold">Return</span>
            {:else}
                <span class:text-white={!isActive.return}>
                    <FontAwesomeIcon icon={faExchangeAlt} class="text-3xl" />
                </span>
            {/if}
    </div>
    <div class="flex items-center p-2 hover:bg-cyan-600 justify-center" class:bg-white={isActive.dashboard} class:rounded-[4px]={isActive.dashboard} class:ml-[5px]={isActive.dashboard} class:mr-[5px]={isActive.dashboard} class:h-[50px]={isActive.dashboard}>
        <button type="button" on:click={handleDashboardClick} class="flex items-center justify-center">
            {#if sidebarWidth === 'w-[220px]'}
                <span class:text-cyan-950={isActive.dashboard} class:text-white={!isActive.dashboard} class="text-2xl font-bold">Dashboard</span>
            {:else}
                <span class:text-white={!isActive.dashboard}>
                    <FontAwesomeIcon icon={faDashboard} class="text-3xl" />
                </span>
            {/if}
        </button>
    </div>
    <div class="flex items-center p-4 hover:bg-cyan-600 justify-center" class:bg-white={isActive.inventory} class:rounded-[4px]={isActive.inventory} class:ml-[5px]={isActive.inventory} class:mr-[5px]={isActive.inventory} class:h-[50px]={isActive.inventory}>
        <button type="button" on:click={handleInventoryClick} class="flex items-center justify-center">
            {#if sidebarWidth === 'w-[220px]'}
                <span class:text-cyan-950={isActive.inventory} class:text-white={!isActive.inventory} class="text-2xl font-bold">Inventory</span>
            {:else}
                <span class:text-white={!isActive.inventory}>
                    <FontAwesomeIcon icon={faBoxOpen} class="text-3xl" />
                </span>
            {/if}
        </button>
    </div>
    <div class="flex items-center p-4 hover:bg-cyan-600 justify-center" class:bg-white={isActive.vouchers} class:rounded-[4px]={isActive.vouchers} class:ml-[5px]={isActive.vouchers} class:mr-[5px]={isActive.vouchers} class:h-[50px]={isActive.vouchers}>
        <button type="button" on:click={handleVouchersClick} class="flex items-center justify-center">
            {#if sidebarWidth === 'w-[220px]'}
                <span class:text-cyan-950={isActive.vouchers} class:text-white={!isActive.vouchers} class="text-2xl font-bold">Vouchers</span>
            {:else}
                <span class:text-white={!isActive.vouchers}>
                    <FontAwesomeIcon icon={faTicketAlt} class="text-3xl" />
                </span>
            {/if}
        </button>
    </div>
    <div class="flex items-center p-4 hover:bg-green-600 justify-center" role="button" tabindex="0" on:click={() => window.location.reload()} on:keydown={(e) => e.key === 'Enter' && window.location.reload()}>
        {#if sidebarWidth === 'w-[220px]'}
            <span class="text-white text-2xl font-bold">Reload</span>
        {:else}
            <span class:text-white={!isActive.return}>
                <FontAwesomeIcon icon={faUndo} class="text-3xl" />
            </span>
        {/if}
    </div>
    <div class="flex-grow"></div>
    <div class="flex flex-col items-center mb-4">
        <div class="flex justify-center mt-4">
            <img src="./default.jpg" alt="Avatar" class="w-[50px] h-[50px] rounded-full border-2 border-white" />
        </div>
        <span class="text-white font-bold mt-2 text-sm">{userFirstName || 'USER'}</span>
        <button class="bg-yellow-300 text-black rounded-full p-1 mt-2 text-lg hover:bg-gray-900" on:click={handleLogout}>
          <FontAwesomeIcon icon={faSignOutAlt} class="text-black" />
        </button>
    </div>
</div>
<!-- Modal for logout confirmation -->
{#if showModal}
<div class="fixed inset-0 flex items-center justify-center z-50">
    <div class="modal-overlay fixed inset-0 bg-black opacity-50 z-40"></div>
    <div class="modal-content bg-white rounded-lg shadow-lg p-6 z-50">
        <h2 class="text-lg font-bold mb-4">Confirm Logout</h2>
        <p class="mb-4">Are you sure you want to logout?</p>
        <div class="flex justify-end">
            <button on:click={confirmLogout} class="bg-red-500 text-white rounded px-4 py-2 mr-2 hover:bg-red-600">Yes</button>
            <button on:click={cancelLogout} class="bg-gray-300 text-black rounded px-4 py-2 hover:bg-gray-400">No</button>
        </div>
    </div>
</div>
{/if}
<!-- Popup for Second Sales -->
{#if showSecondPopup}
    <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full z-50">
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
                <button class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition duration-200" on:click={() => currentIcon && confirmAuth(currentIcon)}>Confirm</button>
               
            </div>
        </div>
    </div>
{/if}
