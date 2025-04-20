<script lang="ts">
    import { onMount } from "svelte";
    import { FontAwesomeIcon } from "@fortawesome/svelte-fontawesome";
    import { faStar, faUndo, faExclamationTriangle, faTrash, faPesoSign, faPrint } from "@fortawesome/free-solid-svg-icons";
    import Sidebar from "../sidebar/+page.svelte";

    let preparedOrders = [
      { orderID: 1, itemName: "Nandy pogi", quantity: "1x", reOrderQty: "1x" },
      { orderID: 2, itemName: "Nandy padin", quantity: "1x", reOrderQty: "1x"},
    ];

    let rawMaterials = [
        { orderID: 1, itemName: "Nandy pogi", quantity: "1x", reOrderQty: "1x" },
        { orderID: 2, itemName: "Nandy padin", quantity: "1x", reOrderQty: "1x"},
    ];


    function printPage() {
        window.print();
    }
</script>

<div class="flex h-screen bg-gradient-to-b from-green-500 to-green-700">
    <Sidebar />

    <!-- Main Content -->
    <div class="flex-grow p-6 bg-gray-100">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <div class="text-gray-500">Prepared Meals</div>
                <div class="text-3xl font-bold">4</div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <div class="text-gray-500">Raw Ingredients</div>
                <div class="text-3xl font-bold">4</div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <div class="text-gray-500">Items Not Received</div>
                <div class="text-3xl font-bold">0</div>
            </div>
        </div>

        <!-- Buttons Section -->
        <div class="flex space-x-4 mb-6">
            <button class="p-2 bg-blue-500 text-white rounded">Add to Raw Items</button>
            <button class="p-2 bg-blue-500 text-white rounded">Add to Inventory</button>
            <button class="p-2 bg-blue-500 text-white rounded">Generate PO</button>
        </div>

        <!-- Tables Section -->
        <div class="grid grid-cols-2 gap-4">
            <!-- Sales Remit Table -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="bg-gray-800 text-white text-center font-bold p-2">Prepared Orders</div>
                <table class="w-full text-left table-fixed border-collapse">
                    <thead class="bg-gray-700 text-white">
                        <tr>
                            <th class="p-2 text-center">Order ID</th>
                            <th class="p-2 text-center">Item Name</th>
                            <th class="p-2 text-center">Quantity</th>
                            <th class="p-2 text-center">Re-order Qty</th>
                            <th class="p-2 text-center">Delivered</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        {#each preparedOrders as item}
                            <tr class="border-t border-gray-300 hover:bg-gray-200 transition-colors duration-200">
                                <td class="p-2 text-center">{item.orderID}</td>
                                <td class="p-2 text-center">{item.itemName}</td>
                                <td class="p-2 text-center">{item.quantity}</td>
                                <td class="p-2 text-center">{item.reOrderQty}</td>
                                <td class="p-2 text-center">
                                    <button class="p-1 bg-gray-200 text-gray-700 rounded">Delivered</button>
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>

            <!-- Inventory Item Dispose Table -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="bg-gray-800 text-white text-center font-bold p-2">Raw & Packaged Materials</div>
                <table class="w-full text-left table-fixed border-collapse">
                    <thead class="bg-gray-700 text-white">
                        <tr>
                            <th class="p-2 text-center">Order ID</th>
                            <th class="p-2 text-center">Item Name</th>
                            <th class="p-2 text-center">Quantity</th>
                            <th class="p-2 text-center">Re-order Qty</th>
                            <th class="p-2 text-center">Delivered</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        {#each rawMaterials as item}
                            <tr class="border-t border-gray-300 hover:bg-gray-200 transition-colors duration-200">
                                <td class="p-2 text-center">{item.orderID}</td>
                                <td class="p-2 text-center">{item.itemName}</td>
                                <td class="p-2 text-center">{item.quantity}</td>
                                <td class="p-2 text-center">{item.reOrderQty}</td>
                                <td class="p-2 text-center">
                                    <button class="p-1 bg-gray-200 text-gray-700 rounded">Delivered</button>
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
  