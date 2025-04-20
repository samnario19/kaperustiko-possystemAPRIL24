// src/stores/currentInputStore.ts
import { writable } from 'svelte/store';

export const currentInputStore = writable({
    currentInput: '',
    amountPaid: 0
});