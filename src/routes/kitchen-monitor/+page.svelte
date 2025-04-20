<script lang=ts>
    import { onMount } from 'svelte';

    // Define the card data with explicit types
    interface OrderItem {
        item: any; // Replace 'any' with the actual type if known
        table_number: number;
        que_order_no: number;
        items_ordered: string;
        order_status: string;
    }

    let pendingItems: OrderItem[] = [];
    let processingItems: OrderItem[] = [];
    let doneItems: OrderItem[] = [];

    // Fetch data from the backend
    onMount(() => {
        const fetchData = async () => {
            const response = await fetch('http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getQueOrders');
            const data: OrderItem[] = await response.json();

            // Assuming the data structure matches your items
            pendingItems = data.filter(item => item.order_status === 'pending');
            processingItems = data.filter(item => item.order_status === 'processing');
            doneItems = data.filter(item => item.order_status === 'done');
        };

        fetchData(); // Initial fetch
        const interval = setInterval(fetchData, 1000); // Fetch every 1 second

        return () => clearInterval(interval); // Cleanup on component unmount
    });

    // Function to update order status
    async function updateOrderStatus(receiptNumber: number, orderStatus: string) {
        try {
            const response = await fetch('http://localhost/kaperustiko-possystem/backend/modules/update.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ receipt_number: receiptNumber, order_status: orderStatus })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            console.log(data.message); // Log success or error message
        } catch (error) {
            console.error('Error updating order status:', error);
        }
    }
</script>

<div class="flex flex-col md:flex-row bg-gray-600 h-screen p-4">
    <div class="column pending w-full md:w-1/3 bg-white mx-2 my-4 p-6 rounded-lg shadow-lg border border-gray-300 hover:shadow-xl transition-shadow duration-300 overflow-auto">
        <h2 class="text-2xl font-bold mb-4 text-gray-800 flex justify-between items-center">
            Pending 
            <span class="bg-blue-500 text-white rounded-full w-8 h-8 flex items-center justify-center">{pendingItems.length}</span>
        </h2>
        <div class="space-y-2">
            {#each pendingItems as { item, table_number, que_order_no, items_ordered }}
                <div class="card p-4 bg-gray-100 rounded-lg shadow hover:bg-gray-200 transition-colors duration-300 border border-gray-300">
                    <p class="text-gray-600">Table No: <span class="font-medium">{table_number}</span></p>
                    <p class="text-gray-600">Order No: <span class="font-medium">{que_order_no}</span></p>
                    <p class="text-gray-600">Order Details:</p>
                    <div class="font-medium space-y-1">
                        {#each JSON.parse(items_ordered) as item}
                            <div class="flex items-center justify-between border-b border-gray-200 py-4">
                                <div class="flex-1">
                                    <p class="font-semibold text-lg">Name: {item.order_name} {item.order_name2}</p>
                                    <p class="font-normal text-gray-600">Quantity: {item.order_quantity}</p>
                                    <p class="font-normal text-gray-600">Size: {item.order_size}</p>
                                </div>
                                <div class="flex-none text-right">
                                    
                                    {#if item.order_addons && item.order_addons_price != null && item.order_addons_price > 0}
                                        <div class="flex justify-between">
                                            <p class="font-normal">Addons:</p>
                                            <p class="font-normal">{item.order_addons}</p>
                                        </div>
                                    {/if}
                                </div>
                            </div>
                            {#if item.order_addons2}
                                <p class="font-normal text-gray-600">Addons 2: {item.order_addons2}</p>
                            {/if}
                            {#if item.order_addons3}
                                <p class="font-normal text-gray-600">Addons 3: {item.order_addons3}</p>
                            {/if}
                        {/each}
                    </div>
                    <div class="mt-4 flex space-x-2">
                        <button class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600" 
                                on:click={() => {
                                    console.log(`Receipt Number: ${que_order_no}`);
                                    updateOrderStatus(que_order_no, 'pending');
                                }}>
                            Pending
                        </button>
                        <button class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600" 
                                on:click={() => {
                                    console.log(`Receipt Number: ${que_order_no}`);
                                    updateOrderStatus(que_order_no, 'processing');
                                }}>
                            Processing
                        </button>
                    </div>
                </div>
            {/each}
        </div>
    </div>
    <div class="column processing w-full md:w-1/3 bg-white mx-2 my-4 p-6 rounded-lg shadow-lg border border-gray-300 hover:shadow-xl transition-shadow duration-300 overflow-auto">
        <h2 class="text-2xl font-bold mb-4 text-gray-800 flex justify-between items-center">
            Processing 
            <span class ="bg-yellow-500 text-white rounded-full w-8 h-8 flex items-center justify-center">{processingItems.length}</span>
        </h2>
        <div class="space-y-2">
            {#each processingItems as { item, table_number, que_order_no, items_ordered }}
                <div class="card p-4 bg-gray-100 rounded-lg shadow hover:bg-gray-200 transition-colors duration-300 border border-gray-300">
                    <p class="text-gray-600">Table No: <span class="font-medium">{table_number}</span></p>
                    <p class="text-gray-600">Order No: <span class="font-medium">{que_order_no}</span></p>
                    <p class="text-gray-600">Order Details:</p>
                    <div class="font-medium space-y-1">
                        {#each JSON.parse(items_ordered) as item}
                            <div class="flex items-center justify-between border-b border-gray-200 py-4">
                                <div class="flex-1">
                                    <p class="font-semibold text-lg">Name: {item.order_name} {item.order_name2}</p>
                                    <p class="font-normal text-gray-600">Quantity: {item.order_quantity}</p>
                                    <p class="font-normal text-gray-600">Size: {item.order_size}</p>
                                </div>
                                <div class="flex-none text-right">
                                    
                                    {#if item.order_addons && item.order_addons_price != null && item.order_addons_price > 0}
                                        <div class="flex justify-between">
                                            <p class="font-normal">Addons:</p>
                                            <p class="font-normal">{item.order_addons}</p>
                                        </div>
                                    {/if}
                                </div>
                            </div>
                            {#if item.order_addons2}
                                <p class="font-normal text-gray-600">Addons 2: {item.order_addons2}</p>
                            {/if}
                            {#if item.order_addons3}
                                <p class="font-normal text-gray-600">Addons 3: {item.order_addons3}</p>
                            {/if}
                        {/each}
                    </div>
                    <div class="mt-4 flex space-x-2">
                        <button class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600" 
                                on:click={() => {
                                    console.log(`Receipt Number: ${que_order_no}`);
                                    updateOrderStatus(que_order_no, 'processing');
                                }}>
                            Processing
                        </button>
                        <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600" 
                                on:click={() => {
                                    console.log(`Receipt Number: ${que_order_no}`);
                                    updateOrderStatus(que_order_no, 'done');
                                }}>
                            Done
                        </button>
                    </div>
                </div>
            {/each}
        </div>
    </div>
    <div class="column done w-full md:w-1/3 bg-white mx-2 my-4 p-6 rounded-lg shadow-lg border border-gray-300 hover:shadow-xl transition-shadow duration-300 overflow-auto">
        <h2 class="text-2xl font-bold mb-4 text-gray-800 flex justify-between items-center">
            Done 
            <span class="text-white bg-green-500 rounded-full w-8 h-8 flex items-center justify-center">{doneItems.length}</span>
        </h2>
        <div class="space-y-2">
            {#each doneItems as { item, table_number, que_order_no, items_ordered }}
                <div class="card p-4 bg-gray-100 rounded-lg shadow hover:bg-gray-200 transition-colors duration-300 border border-gray-300">
                    <p class="text-gray-600">Table No: <span class="font-medium">{table_number}</span></p>
                    <p class="text-gray-600">Order No: <span class="font-medium">{que_order_no}</span></p>
                    <p class="text-gray-600">Order Details:</p>
                    <div class="font-medium space-y-1">
                        {#each JSON.parse(items_ordered) as item}
                        <div class="flex items-center justify-between border-b border-gray-200 py-4">
                            <div class="flex-1">
                                <p class="font-semibold text-lg">Name: {item.order_name} {item.order_name2}</p>
                                <p class="font-normal text-gray-600">Quantity: {item.order_quantity}</p>
                                <p class="font-normal text-gray-600">Size: {item.order_size}</p>
                            </div>
                            <div class="flex-none text-right">
                                
                                {#if item.order_addons && item.order_addons_price != null && item.order_addons_price > 0}
                                    <div class="flex justify-between">
                                        <p class="font-normal">Addons:</p>
                                        <p class="font-normal">{item.order_addons}</p>
                                    </div>
                                {/if}
                            </div>
                        </div>
                        {#if item.order_addons2}
                            <p class="font-normal text-gray-600">Addons 2: {item.order_addons2}</p>
                        {/if}
                        {#if item.order_addons3}
                            <p class="font-normal text-gray-600">Addons 3: {item.order_addons3}</p>
                        {/if}
                    {/each}
                    </div>
                </div>
            {/each}
        </div>
    </div>
 
</div>
<div class="column w-full bg-white mx-2 my-4 p-6 rounded-lg shadow-lg border border-gray-300 hover:shadow-xl transition-shadow duration-300 overflow-auto">
    <h2 class="text-2xl font-bold mb-4 text-gray-800">Total Orders</h2>
    <p class="text-gray-600">Total: {pendingItems.length + processingItems.length + doneItems.length}</p>
</div>
<div class="column w-full bg-white mx-2 my-4 p-6 rounded-lg shadow-lg border border-gray-300 hover:shadow-xl transition-shadow duration-300 overflow-auto">
    <h2 class="text-2xl font-bold mb-4 text-gray-800">Chart 1</h2>
    <!-- Placeholder for Chart 1 -->
    <div class="h-48 bg-gray-200 rounded-lg"></div>
</div>
<div class="column w-full bg-white mx-2 my-4 p-6 rounded-lg shadow-lg border border-gray-300 hover:shadow-xl transition-shadow duration-300 overflow-auto">
    <h2 class="text-2xl font-bold mb-4 text-gray-800">Chart 2</h2>
    <!-- Placeholder for Chart 2 -->
    <div class="h-48 bg-gray-200 rounded-lg"></div>
</div>