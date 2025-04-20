<script lang="ts">
    import { onMount } from "svelte";
    import Sidebar from '../sidebar/+page.svelte';
    import { FontAwesomeIcon } from '@fortawesome/svelte-fontawesome';
    import { faEye, faEdit, faTrash } from '@fortawesome/free-solid-svg-icons';
  
    let items: any[] = [];
    let searchQuery: string;
    let selectedStatus: string;
    let showPopup: boolean = false;
    let newProduct: { [key: string]: string } = { 
        code: '', 
        title1: '', 
        title2: '', 
        label: '', 
        label2: '', 
        price1: '', 
        price2: '', 
        price3: '', 
        qty: '', 
        image: '', 
        stock_date: '', 
        stock_time: '' 
    };
    let imageFile: File | null = null;
    let showOptionsPopup: boolean = false;
    let selectedItem: any;
    let isCodePopupVisible: boolean = false;
    let action: string;
    let showQuantityPopup: boolean = false;
    let quantityToAdd: number = 0;
    let showEditPopup: boolean = false;
    let editProductData = { code: '', title1: '', title2: '', label: '', price1: '', price2: '', price3: '', qty: '' };
    let showAddProductChoicePopup: boolean = false;
    let filteredItems: any[] = []; // New array to hold filtered items

    // Fetch menu items from the backend
    onMount(async () => {
        const response = await fetch('http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getMenu'); // Update the URL as needed
        if (response.ok) {
            items = await response.json();
            
            // Sort items by status
            items.sort((a, b) => {
                const statusA = a.qty === 0 ? 0 : a.qty < 10 ? 1 : a.qty < 20 ? 2 : a.qty < 100 ? 3 : 4;
                const statusB = b.qty === 0 ? 0 : b.qty < 10 ? 1 : b.qty < 20 ? 2 : b.qty < 100 ? 3 : 4;
                return statusA - statusB;
            });
        } else {
        }
    });

    // Watch for changes in selectedStatus to filter items
    $: filteredItems = items.filter(item => {
        if (!selectedStatus && !searchQuery) return true; // If no status or search query is selected, show all items
        const matchesStatus = selectedStatus ? 
            (selectedStatus === 'Out of Stock' && item.qty < 1) ||
            (selectedStatus === 'Critical' && item.qty < 20 && item.qty > 1) ||
            (selectedStatus === 'Good' && item.qty > 30) : true; // Filter by selected status
        const matchesSearch = searchQuery ? item.title1.toLowerCase().includes(searchQuery.toLowerCase()) || item.title2.toLowerCase().includes(searchQuery.toLowerCase()) : true; // Filter by search query
        return matchesStatus && matchesSearch; // Return true if both conditions are met
    });

    // Function to handle form submission
    const addProduct = async () => {
        showAddProductChoicePopup = true;  // Show the choice popup
    };

    // Function to handle manual product addition
    const openManualAddProduct = () => {
        showPopup = true; // Show the product form directly
        showAddProductChoicePopup = false; // Close the choice popup
    };

    // Function to handle Excel file upload (to be implemented)
    const uploadExcelFile = async (event: Event) => {
        const file = (event.target as HTMLInputElement).files?.[0];
        if (file) {
            const formData = new FormData();
            formData.append('excelFile', file); // Append the file to FormData
            
            // Log the FormData contents
            for (const [key, value] of formData.entries()) {
                console.log(`${key}:`, value);
            }
            // Log specific item details
            items.forEach(item => {
                console.log(`Code: ${item.code}, Title: ${item.title1}, Quantity: ${item.qty}`);
            });

            try {
                const response = await fetch('http://localhost/kaperustiko-possystem/backend/modules/upload_excel.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                if (result.status === 'success') {
                    showAlert('Excel file uploaded and database updated successfully', 'success');
                } else {
                    showAlert('Failed to upload Excel file: ' + result.message, 'error');
                }
            } catch (error) {
                showAlert('Error uploading Excel file', 'error');
            }
        }
    };

    const generateProduct = async () => {
        try {
            // Create a FormData object
            const formData = new FormData();
            
            // Append all product data to FormData
            for (const key in newProduct) {
                formData.append(key, newProduct[key]);
            }
            
            // Append the image file if it exists
            if (imageFile) {
                formData.append('image', imageFile);
            } else {
                showAlert('Please select an image', 'error');
                return;
            }

            const response = await fetch('http://localhost/kaperustiko-possystem/backend/modules/upload_product.php', {
                method: 'POST',
                body: formData
            });

            // Check if the response is okay
            if (response.ok) {
                // Always show success message if the request completes
                showAlert('Product uploaded successfully', 'success');
                console.log('Product added:', newProduct); // Log the added product details
                showPopup = false;
            } else {
                showAlert('Failed to upload product', 'error'); // Show error if response is not okay
            }

        } catch (error) {
            showAlert('Failed to upload product', 'error');
        }
    };

    // Update showAlert function to ensure alerts are visible
    function showAlert(message: string, type: string) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `fixed top-4 left-1/2 transform -translate-x-1/2 p-4 rounded shadow-lg text-white text-center min-w-[300px] ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        }`;
        alertDiv.style.zIndex = '9999';
        alertDiv.innerText = message;
        document.body.appendChild(alertDiv);
        
        // Remove the alert after 2 seconds (matching the reload delay)
        setTimeout(() => {
            alertDiv.remove();
        }, 2000);
    }

    const showOptions = (item: any) => {
        selectedItem = item.code; // Set the selected item code
        fetchItemDetails(); // Fetch item details
    };

    const addStock = async () => {
        await fetchQuantityToAdd(); // Fetch the quantity when adding stock
        showQuantityPopup = true; // Show the quantity input popup
    };

    const fetchQuantityToAdd = async () => {
        // Fetch the quantity from the backend
        const response = await fetch(`http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getProductQty&code=${selectedItem}`);
        if (response.ok) {
            const data = await response.json();
            quantityToAdd = data.qty; // Update the quantityToAdd variable
        } else {
        }
    };

    const confirmAddStock = async () => {
        // Prepare data to send
        const data = {
            code: selectedItem, // Use the selected item code
            qty: quantityToAdd
        };

        // Send the update request to the backend
        const response = await fetch('http://localhost/kaperustiko-possystem/backend/modules/update_qty.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ code: selectedItem, qty: quantityToAdd }),
        });

        // Log the API response
        const result = await response.json();

        // Handle response (e.g., show alert)
        if (response.ok) {
            showAlert('Stock updated successfully', 'success');
            isCodePopupVisible = false;
            showOptionsPopup = false;
            showQuantityPopup = false;
            window.location.reload(); 
        } else {
            showAlert('Failed to update stock', 'error');
        }

        showQuantityPopup = false;
        quantityToAdd = 0;
    };

    const fetchItemDetails = async () => {
        const response = await fetch(`http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getItems&code=${selectedItem}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
              },
            body: JSON.stringify({ code: selectedItem }), // Send the selected item code
        });

        if (response.ok) {
            const itemDetails = await response.json(); // Parse the JSON response

            if (itemDetails.length > 0) {
                editProductData = { ...itemDetails[0] }; // Assuming you want the first item
                showEditPopup = true; // Show the edit popup
            } else {
                showAlert('No item found with the provided code', 'error');
            }
        } else {
            showAlert('Failed to fetch item details', 'error');
        }
    };

    // Call this function when you want to edit a product
    const editProduct = () => {
        showEditPopup = true;
        fetchItemDetails();
    };

    const deleteProduct = async () => {
        if (selectedItem) {
            const response = await fetch(`http://localhost/kaperustiko-possystem/backend/modules/delete.php?action=deleteProduct`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ code: selectedItem }),
            });

            const result = await response.json();

            // Handle response (e.g., show alert)
            if (response.ok) {
                showAlert('Product deleted successfully', 'success');
                // Optionally, refresh the items list or remove the item from the UI
                items = items.filter(item => item.code !== selectedItem); // Remove deleted item from the list
                isCodePopupVisible = false;
                showOptionsPopup = false;
                window.location.reload();
            } else {
                showAlert('Failed to delete product', 'error');
            }
        }
    };

    // Update the image handling to store only the file name and format
    function handleImageUpload(event: Event) {
        const file = (event.target as HTMLInputElement).files?.[0];
        if (file) {
            newProduct.image = file.name; // Store only the file name
            imageFile = file; // Store the file for upload
        }
    }

    async function updateProduct() {
        const response = await fetch('http://localhost/kaperustiko-possystem/backend/update_product.php', { // Adjust the URL as needed
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(editProductData)
        });

        if (response.ok) {
            // Handle success (e.g., show a success message, refresh the product list, etc.)
            showEditPopup = false;
        } else {
        }
    }

    // Function to handle exporting items as Excel
    const exportAsExcel = async () => {
        // Logic to export items to Excel
        const response = await fetch('http://localhost/kaperustiko-possystem/backend/modules/export.php', {
            method: 'GET',
        });

        if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'inventory.xlsx'; // Specify the file name
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url);
        } else {
            showAlert('Failed to export as Excel', 'error');
        }
    };
</script>

<!-- Main Layout with Sidebar and Content -->
<div class="flex h-screen bg-gray-100">
    <Sidebar />
    <div class="flex-grow p-6 overflow-auto">
        <div class="flex justify-between mb-4">
            <div class="flex items-center">
                <input type="text" placeholder="Search..." class="p-2 border rounded" bind:value={searchQuery} />
                <select bind:value={selectedStatus} class="ml-4 p-2 border rounded">
                    <option value="">All</option>
                    <option value="Out of Stock">Out of Stock</option>
                    <option value="Critical">Critical</option>
                    <option value="Good">Good</option>
                </select>
            </div>
            <div class="flex items-center">
                <button class="p-2 bg-green-600 text-white rounded shadow-md hover:bg-green-700 transition duration-200" on:click={addProduct}>
                    Add Product
                </button>
                <button class="ml-2 p-2 bg-blue-600 text-white rounded shadow-md hover:bg-blue-700 transition duration-200" on:click={exportAsExcel}>
                    Export as Excel
                </button>
            </div>
        </div>
        <div class="rounded-lg shadow-lg overflow-auto max-h-[970px]">
            <table class="min-w-full table-auto border-collapse bg-white shadow-md rounded-lg overflow-hidden">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="p-4 text-left border-b-2 border-gray-300">Product Image</th>
                        <th class="p-4 text-left border-b-2 border-gray-300">Code</th>
                        <th class="p-4 text-left border-b-2 border-gray-300">Product Name</th>
                        <th class="p-4 text-left border-b-2 border-gray-300">Label</th>
                        <th class="p-4 text-center border-b-2 border-gray-300">Price</th>
                        <th class="p-4 text-center border-b-2 border-gray-300">Qty</th>
                        <th class="p-4 text-center border-b-2 border-gray-300">Last Stock Date & Time</th>
                        <th class="p-4 text-center border-b-2 border-gray-300">Actions</th>
                        <th class="p-4 text-center border-b-2 border-gray-300">Status</th>
                    </tr>
                </thead>
                <tbody>
                    {#each filteredItems as item}
                        <tr class="border-b border-gray-300 hover:bg-gray-200 transition duration-200">
                            <td class="p-2 text-center">
                                <img src={`foods/${item.image}`} alt={item.title1} class="w-16 h-16 object-cover rounded" />
                            </td>
                            <td class="p-2 text-left font-medium">{item.code}</td>
                            <td class="p-2 text-left border-b-2">{item.title1} {item.title2}</td>
                            <td class="p-4 truncate">{item.label}</td>
                            <td class="p-4 text-center">
                                <ul class="text-gray-700 px-2 mt-1">
                                    <li class="font-semibold">₱{item.price1}</li>
                                </ul>
                            </td>
                            <td class="p-4 text-center font-medium">{item.qty} {item.qty < 2 ? 'pc' : 'pcs'}</td>
                            <td class="p-4 text-center">
                                {new Date(`${item.stock_date} ${item.stock_time}`).toLocaleString('en-US', {
                                    month: 'long',
                                    day: 'numeric',
                                    year: 'numeric',
                                    hour: 'numeric',
                                    minute: 'numeric',
                                    second: 'numeric',
                                    hour12: true
                                })}
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex justify-center">
                                    <button class="p-2 bg-cyan-600 text-white rounded transition duration-200 hover:bg-cyan-700 shadow-md" on:click={() => { 
                                        isCodePopupVisible = true; 
                                        action = 'options'; 
                                        selectedItem = item.code; 
                                    }}>
                                        Options
                                    </button>
                                </div>
                            </td>
                            <td class="p-4 text-center">
                                <span class={`p-2 rounded ${item.qty == 0 || item.qty == '' ? 'bg-red-600 text-white' : item.qty >= 1 && item.qty <= 10 ? 'bg-orange-500 text-white' : item.qty > 11 && item.qty <= 20 ? 'bg-yellow-500 text-white' : item.qty > 21 && item.qty <= 1000 ? 'bg-green-500 text-white' : ''}`}>
                                    {item.qty == 0 || item.qty == '' ? 'Out of Stock' : item.qty >= 1 && item.qty <= 10 ? 'Critical' : item.qty > 11 && item.qty <= 20 ? 'Warning' : item.qty > 21 && item.qty <= 1000 ? 'Good' : ''}
                                </span>
                            </td>
                        </tr>
                    {/each}
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Popup Form -->
{#if showPopup}
    <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white p-6 rounded shadow-lg">
            <h2 class="text-lg font-bold mb-4">Add New Product</h2>
            <form on:submit|preventDefault={addProduct}>
                <div>
                    <label for="code">Product Code:</label>
                    <input type="text" bind:value={newProduct.code} class="border rounded p-2 w-full" required />
                </div>
                <div>
                    <label for="title1">Title 1:</label>
                    <input type="text" bind:value={newProduct.title1} class="border rounded p-2 w-full" required />
                </div>
                <div>
                    <label for="title2">Title 2:</label>
                    <input type="text" bind:value={newProduct.title2} class="border rounded p-2 w-full" />
                </div>
                <div>
                    <label for="label">Label:</label>
                    <input type="text" bind:value={newProduct.label} class="border rounded p-2 w-full" required />
                </div>
                <div>
                  <label for="label2">Label2:</label>
                  <input type="text" bind:value={newProduct.label2} class="border rounded p-2 w-full" required />
              </div>
                <div>
                    <label for="price1">Price 1:</label>
                    <input type="number" bind:value={newProduct.price1} class="border rounded p-2 w-full" required />
                </div>
                <div>
                    <label for="price2">Price 2:</label>
                    <input id="price2" type="number" bind:value={newProduct.price2} class="border rounded p-2 w-full" />
                </div>
                <div>
                    <label for="price3">Price 3:</label>
                    <input type="number" bind:value={newProduct.price3} class="border rounded p-2 w-full" />
                </div>
                <div>
                    <label for="quantity">Quantity:</label>
                    <input id="quantity" type="number" bind:value={newProduct.qty} class="border rounded p-2 w-full" required />
                </div>
                <div>
                    <label for="image">Upload Image:</label>
                    <input id="image" type="file" accept="image/*" on:change={handleImageUpload} class="border rounded p-2 w-full" required />
                </div>
                <div class="flex justify-end mt-4">
                    <button type="button" class="mr-2 p-2 bg-gray-300 rounded" on:click={() => showPopup = false}>Cancel</button>
                    <button type="submit" class="p-2 bg-green-600 text-white rounded" on:click={generateProduct}>Generate Product</button>
                </div>
            </form>
        </div>
    </div>
{/if}



<!-- Options Popup -->
{#if isCodePopupVisible}
    <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-70">
        <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full">
            <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Choose an Action</h2>
            <div class="flex justify-around mb-6">
                <button class="p-3 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50" on:click={addStock}>Add Stock</button>
                <button class="p-3 bg-yellow-600 text-white rounded-lg shadow hover:bg-yellow-700 transition duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-opacity-50" on:click={editProduct}>Edit Product</button>
                <button class="p-3 bg-red-600 text-white rounded-lg shadow hover:bg-red-700 transition duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50" on:click={deleteProduct}>Delete Product</button>
            </div>
            <div class="flex justify-end mt-4">
                <button type="button" class="w-full p-4 bg-gray-300 rounded-lg hover:bg-gray-400 transition duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50" on:click={() => isCodePopupVisible = false}>Close</button>
            </div>
        </div>
    </div>
{/if}

<!-- Quantity Input Popup -->
{#if showQuantityPopup}
    <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-70">
        <div class="w-full max-w-md rounded-lg bg-white p-8 shadow-lg">
            <h2 class="mb-4 text-center text-2xl font-bold">Add Stock Quantity</h2>
            <input
                type="number"
                bind:value={quantityToAdd}
                class="w-full rounded border border-gray-300 p-2 text-center"
                placeholder="Enter quantity to add"
                min="1"
            />
            <div class="flex justify-between mt-4">
                <button on:click={() => showQuantityPopup = false} class="rounded-md bg-red-500 px-4 py-2 text-white">Cancel</button>
                <button on:click={confirmAddStock} class="rounded-md bg-blue-500 px-4 py-2 text-white">Confirm</button>
            </div>
        </div>
    </div>
{/if}

<!-- Edit Form Popup -->
{#if showEditPopup}
    <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white p-6 rounded shadow-lg">
            <h2 class="text-lg font-bold mb-4">Edit Product</h2>
            <form on:submit|preventDefault={updateProduct}>
                <div>
                    <label for="code">Product Code:</label>
                    <input type="text" bind:value={editProductData.code} class="border rounded p-2 w-full" required />
                </div>
                <div>
                    <label for="title1">Title 1:</label>
                    <input type="text" bind:value={editProductData.title1} class="border rounded p-2 w-full" required />
                </div>
                <div>
                    <label for="title2">Title 2:</label>
                    <input type="text" bind:value={editProductData.title2} class="border rounded p-2 w-full" />
                </div>
                <div>
                    <label for="label">Label:</label>
                    <input type="text" bind:value={editProductData.label} class="border rounded p-2 w-full" required />
                </div>
                <div>
                    <label for="price1">Price 1:</label>
                    <input type="number" bind:value={editProductData.price1} class="border rounded p-2 w-full" required />
                </div>
                <div>
                    <label for="price2">Price 2:</label>
                    <input type="number" bind:value={editProductData.price2} class="border rounded p-2 w-full" />
                </div>
                <div>
                    <label for="price3">Price 3:</label>
                    <input type="number" bind:value={editProductData.price3} class="border rounded p-2 w-full" />
                </div>
                <div class="flex justify-end mt-4">
                    <button type="button" class="mr-2 p-2 bg-gray-300 rounded" on:click={() => showEditPopup = false}>Cancel</button>
                    <button type="submit" class="p-2 bg-green-600 text-white rounded">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
{/if}

<!-- Add Product Choice Popup -->
{#if showAddProductChoicePopup}
    <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white p-6 rounded shadow-lg">
            <h2 class="text-lg font-bold mb-4">Add New Product</h2>
            <div class="flex flex-col">
                <button class="p-2 bg-blue-600 text-white rounded mb-2" on:click={openManualAddProduct}>
                    Manually Add Product
                </button>
                <input id="excelFile" type="file" accept=".xlsx, .xls" on:change={uploadExcelFile} class="border rounded p-2" />
                <label for="excelFile" class="mt-2">Upload Excel File</label>
            </div>
            <div class="flex justify-end mt-4">
                <button type="button" class="mr-2 p-2 bg-gray-300 rounded" on:click={() => showAddProductChoicePopup = false}>Cancel</button>
                <button type="button" class="p-2 bg-green-600 text-white rounded" on:click={() => showAddProductChoicePopup = false}>Done</button>
            </div>
        </div>
    </div>
{/if}
  
