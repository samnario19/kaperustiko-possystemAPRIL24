// src/stores/orderedItemsStore.ts
import { writable } from 'svelte/store';

// Define the interface for the items
interface OrderedItem {
    title: string;
    price: string;
    size: string;
    quantity: number;
    addons: string[];
}

// Update the store to use the defined interface
export const orderedItemsStore = writable<OrderedItem[]>([]);