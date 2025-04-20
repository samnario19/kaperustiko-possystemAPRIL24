<script lang="ts">
    import { onMount } from "svelte";
    import Sidebar from '../sidebar/+page.svelte';

    // Define the type for a voucher with the correct properties
    interface Voucher {
        voucher_id: number; // Add voucher_id property
        voucher_code: string; // Add voucher_code property
        voucher_discount: number; // Add voucher_discount property
        voucher_deadline: string; // Add voucher_deadline property
        voucher_description: string; // Add voucher_description property
    }

    // State variable for voucher data with explicit type
    let vouchers: Voucher[] = []; // Specify the type as an array of Voucher

    // State variable for popup visibility
    let showPopup = false;

    // New state variables for form inputs
    let newVoucherCode = '';
    let newVoucherDiscount = 0;
    let newVoucherDeadline = '';
    let newVoucherDescription = '';
    let isDeleteConfirmationVisible = false;
    let voucherToDelete: string | null = null;

    let editVoucherCode = '';
    let editVoucherDiscount = 0;
    let editVoucherDeadline = '';
    let editVoucherDescription = '';
    let isEditPopupVisible = false;
    let voucherToEditId = 0;

    // Function to show alert messages
    function showAlert(message: string, type: string) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `fixed top-0 left-1/2 transform -translate-x-1/2 mt-4 p-4 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white rounded shadow-lg`;
        alertDiv.innerText = message;
        document.body.appendChild(alertDiv);
        setTimeout(() => {
            alertDiv.remove();
        }, 3000); // Remove alert after 3 seconds
    }

    function formatDate(datestring: string): string {
        const options: Intl.DateTimeFormatOptions = {year: 'numeric', month: 'long', day: 'numeric'};
        return new Date(datestring).toLocaleDateString('en-US', options);
    }

    // Function to fetch vouchers
    const fetchVouchers = async () => {
        try {
            const response = await fetch('http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getVouchers');
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const fetchedVouchers: Voucher[] = await response.json();
            vouchers = fetchedVouchers.sort((a, b) => new Date(b.voucher_deadline).getTime() - new Date(a.voucher_deadline).getTime());
        } catch (error) {
            console.error('Error fetching vouchers:', error);
            showAlert('Failed to load vouchers', 'error');
        }
    };

    // Call fetchVouchers on component mount
    onMount(() => {
        fetchVouchers();
        const intervalId = setInterval(fetchVouchers, 500);
        return () => clearInterval(intervalId);
    });

    // Function to add a new voucher
    async function addVoucher() {
        const response = await fetch(
            'http://localhost/kaperustiko-possystem/backend/modules/insert.php?action=voucher_code',
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    voucher_code: newVoucherCode,
                    voucher_discount: newVoucherDiscount,
                    voucher_deadline: newVoucherDeadline,
                    voucher_description: newVoucherDescription,
                })
            }
        );

        const result = await response.json();
        if (result.status === "success") {
            showAlert("Voucher added successfully!", "success"); // Show success alert
            showPopup = false; // Close the popup after adding
            // Optionally, refresh the vouchers list here
        } else {
            showAlert(result.message, "error"); // Show error alert
            console.error(result.message);
        }
    }

    // Function to show delete confirmation
    function confirmDelete(voucherCode: string) {
        voucherToDelete = voucherCode; // Set the voucher code to delete
        isDeleteConfirmationVisible = true; // Show the confirmation popup
    }

    // Function to delete a voucher
    async function deleteVoucher() {
        if (voucherToDelete) {
            console.log('Voucher to delete:', voucherToDelete); // Log the voucher code for debugging
            const response = await fetch(
                `http://localhost/kaperustiko-possystem/backend/modules/delete.php?action=deleteVoucher&voucher_code=${voucherToDelete}`, // Use the correct action and parameter
                {
                    method: 'DELETE'
                }
            );

            const text = await response.text(); // Get the response as text
            console.log('Response Text:', text); // Log the response text for debugging

            try {
                const result = JSON.parse(text); // Parse the text as JSON
                if (result.success) {
                    showAlert('Voucher deleted successfully!', 'success');
                    fetchVouchers(); // Refresh the vouchers list
                } else {
                    showAlert('Failed to delete voucher: ' + result.message, 'error');
                }
            } catch (error) {
                console.error('Failed to parse JSON:', error);
                showAlert('An error occurred while processing your request.', 'error');
            }
        }
        isDeleteConfirmationVisible = false; // Hide the confirmation popup
        voucherToDelete = null; // Reset the voucher code
    }

    // Function to cancel deletion
    function cancelDelete() {
        isDeleteConfirmationVisible = false; // Hide the confirmation popup
        voucherToDelete = null; // Reset the voucher code
    }

    function showEditForm(voucher: Voucher) {
        editVoucherCode = voucher.voucher_code;
        editVoucherDiscount = voucher.voucher_discount;
        editVoucherDeadline = voucher.voucher_deadline;
        editVoucherDescription = voucher.voucher_description;
        voucherToEditId = voucher.voucher_id;
        isEditPopupVisible = true;
    }

    async function editVoucher(){
        const response = await fetch('http://localhost/kaperustiko-possystem/backend/modules/updates.php?action=editVoucher', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                voucher_id: voucherToEditId,
                voucher_discount: editVoucherDiscount,
                voucher_deadline: editVoucherDeadline,
                voucher_description: editVoucherDescription,
            })
        });

        const result = await response.json();
        if (result.status === "success") {
            showAlert("Voucher updated successfully!", "success");
            isEditPopupVisible = false;
        } else {
            showAlert(result.message, "error");
        }
    }
</script>

<div class="flex h-screen w-full bg-gray-100">
    <Sidebar />
    <div class="p-4 w-full">
        <button class="mb-4 bg-blue-500 w-full text-white font-bold py-2 px-4 rounded hover:bg-blue-600 transition" on:click={() => showPopup = true}>
            Add Voucher
        </button>

        {#if showPopup}
            <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                <div class="bg-white p-6 rounded shadow-lg">
                    <h2 class="font-bold text-lg">Add Voucher</h2>
                    <form on:submit|preventDefault={addVoucher}>
                        <input type="text" placeholder="Voucher Code" bind:value={newVoucherCode} class="border p-2 w-full mb-4" required />
                        <input type="number" placeholder="Voucher Discount" bind:value={newVoucherDiscount} class="border p-2 w-full mb-4" required />
                        <input type="date" placeholder="Voucher Deadline" bind:value={newVoucherDeadline} class="border p-2 w-full mb-4" required />
                        <input type="text" placeholder="Voucher Description" bind:value={newVoucherDescription} class="border p-2 w-full mb-4" required />
                        <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded">Submit</button>
                        <button type="button" class="ml-2 py-2 px-4 rounded" on:click={() => showPopup = false}>Cancel</button>
                    </form>
                </div>
            </div>
        {/if}

        <!-- Confirmation Popup -->
        {#if isDeleteConfirmationVisible}
            <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-70 z-50">
                <div class="bg-white p-6 rounded shadow-lg">
                    <h2 class="text-lg font-bold">Are you sure you want to delete this voucher?</h2>
                    <div class="mt-4 flex justify-between">
                        <button on:click={deleteVoucher} class="bg-red-500 text-white px-4 py-2 rounded">Yes, Delete</button>
                        <button on:click={cancelDelete} class="bg-gray-300 text-black px-4 py-2 rounded">Cancel</button>
                    </div>
                </div>
            </div>
        {/if}

        <!-- Edit Popup -->
        {#if isEditPopupVisible}
            <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                <div class="bg-white p-6 rounded shadow-lg">
                    <h2 class="font-bold text-lg">Edit Voucher</h2>
                    <form on:submit|preventDefault={editVoucher}>
                        <input type="text" placeholder="Voucher Code" bind:value={editVoucherCode} class="border p-2 w-full mb-4" required />
                        <input type="number" placeholder="Voucher Discount" bind:value={editVoucherDiscount} class="border p-2 w-full mb-4" required />
                        <input type="date" placeholder="Voucher Deadline" bind:value={editVoucherDeadline} class="border p-2 w-full mb-4" required />
                        <input type="text" placeholder="Voucher Description" bind:value={editVoucherDescription} class="border p-2 w-full mb-4" required />
                        <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded">Submit</button>
                        <button type="button" class="ml-2 py-2 px-4 rounded" on:click={() => isEditPopupVisible = false}>Cancel</button>
                    </form>
                </div>
            </div>
        {/if}

        <!-- Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {#if vouchers.length === 0}
                <p class="text-center text-gray-600 col-span-full">No vouchers found</p>
            {:else}
                {#each vouchers as voucher}
                    <div class={`flex border border-gray-300 rounded-lg shadow-lg overflow-hidden ${new Date(voucher.voucher_deadline) < new Date() ? 'opacity-50' : ''}`}>
                        <div class="flex-shrink-0 bg-gradient-to-r from-indigo-600 to-blue-500 text-white p-6 flex items-center justify-center">
                            <h2 class="font-bold text-5xl">{voucher.voucher_discount}% OFF</h2>
                        </div>
                        <div class="ml-4 p-6 bg-gray-100 flex-1 rounded-lg shadow-md transition-transform transform hover:scale-105">
                            <h3 class="font-bold text-xl text-gray-800">{voucher.voucher_code}</h3>
                            <p class="text-sm text-gray-600 mb-2">Limited Redemptions; Valid through {formatDate(voucher.voucher_deadline)}</p>
                            {#if new Date(voucher.voucher_deadline) >= new Date(new Date().setHours(0, 0, 0, 0))}
                                <p class="text-sm text-gray-600 mb-4">{voucher.voucher_description}</p>
                            {:else}
                                <p class="text-sm text-red-600 mb-4">Voucher expired</p>
                            {/if}
                            <button class="bg-red-600 text-white py-2 px-4 rounded-lg shadow" on:click={() => confirmDelete(voucher.voucher_code)}>Delete</button>
                            <button class="bg-blue-600 text-white py-2 px-4 rounded-lg shadow ml-2" on:click={() => showEditForm(voucher)}>Edit</button>
                        </div>
                    </div>
                {/each}
            {/if}
        </div>
    </div>
</div>