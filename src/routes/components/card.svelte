<script>
    import { Button } from 'flowbite-svelte';
    import { createEventDispatcher } from 'svelte';

    const dispatch = createEventDispatcher();

    // Define props for dynamic data
    export let menu_no = '';
    export let code = '';
    export let title1 = '';
    export let title2 = '';
    export let price1 = '';
    export let price2 = '';
    export let price3 = '';
    export let image = '';
    export let label = '';
    export let label2 = '';
    export let qty = '';
    export let onAdd;

    // Function to adjust font size based on title length
    /**
     * @param {string} title - The title to adjust font size for
     */
    function adjustFontSize(title) {
        if (title.length > 30) return 'text-sm'; // Adjust as needed
        if (title.length > 20) return 'text-md';
        return 'text-xl';
    }
</script>
  
<div class="flex items-center justify-center">
    <div class="relative bg-white shadow-lg rounded-lg w-full h-64 p-5 flex">
      
      <!-- Left Image Section -->
      <div class="w-1/2 h-62 bg-gray-200 rounded-lg mr-4">
        <img src={`foods/${image}`} alt="Image" class="w-full h-full rounded-lg object-cover" aria-hidden="true">
      </div>
  
      <!-- Right Information Section -->
      <div class="w-1/2 flex flex-col justify-between">
        <!-- Centered Code Section -->
        <div class="flex justify-between">
          <span class="bg-gray-200 text-gray-500 text-xs rounded-full px-2 py-1">
            CODE: {code}
          </span>
          <span class={`bg-gray-200 text-gray-500 text-xs text-right rounded-full px-2 py-1 ${qty === '0' ? 'bg-red-500 text-white' : ''}`}>
            {qty === '0' ? 'Sold Out' : `QTY: ${qty}`}
          </span>
        </div>
  
        <!-- Text Section -->
        <div class="space-y-2 text-left">
          <p class={`font-bold ${adjustFontSize(title1)} mt-2 text-gray-800`}>{title1}</p>
          <p class="text-gray-500 text-sm">{title2}</p>
        </div>
  
        <!-- Price Section -->
        <p class="text-4xl font-bold text-gray-800">₱{price1}<span class="text-[17px] text-gray-500">.00</span></p>
  
        <!-- Order Button -->
        <Button color="dark" class="w-full" on:click={() => {
            console.log(menu_no);
            onAdd({ code, title1, title2, price1, price2, price3, image, label, label2 });
        }} disabled={qty === '0'}>
          ADD
        </Button>
      </div>
    </div>
</div>
  