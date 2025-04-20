<script lang="ts">
	import { onMount } from 'svelte';
	import { faSignOutAlt, faStar, faTrophy, faBoxOpen } from '@fortawesome/free-solid-svg-icons';
	import { FontAwesomeIcon } from '@fortawesome/svelte-fontawesome';
	import { fade, fly } from 'svelte/transition';
	

	// Define the logout function
	function logout() {
		window.location.href = "/login";
		console.log("User logged out");
	}

	let userName = ""; // Default value
	let userLevel = ""; // Default value
	let userExp = ""; // Default value
	let userRank = ""; // Default value
	let userDiamonds = 0; // Change type to number
	let userId = ""; // Define userId variable
	let userStars = 0; // Set the number of active stars
	let userAvatarBorder = ""; // Define userAvatarBorder variable
	const totalStars = 5; // Total number of stars
	let showRankUpPopup = false;
	let previousRank = '';
	let showContinueButton = false;

	// Define a mapping for user ranks
	const rankMapping: { [key: number]: string } = {
		1: 'Grand-Master',
		2: 'Epic',
		3: 'Legend',
		4: 'Mythic',
		5: 'Mythical-Honor',
		6: 'Mythical-Glory',
	};

	// Fetch user information on component mount
	onMount(async () => {
		userId = localStorage.getItem('user-id')!; // Use non-null assertion
		if (userId) {
			const response = await fetch('http://localhost/codemaster/codemasters-backend/modules/get_user_info.php?user-id=' + userId, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify({ 'user-id': userId })
			});
			const data = await response.json();
			if (data.message) {
				console.error(data.message);
			} else {
				userName = data['user-name']; // Assuming 'username' is a field in your database
				userLevel = data['user-level']; // Assuming 'level' is a field in your database
				userExp = data['user-exp']; // Assuming 'exp' is a field in your database
				userRank = rankMapping[data['user-rank']] || 'unknown'; // Map the numeric rank to its name
				userStars = data['rank-star']; // Ensure this is a number, default to 0 if conversion fails
				userDiamonds = data['user-diamonds']; // Convert to number
				userAvatarBorder = data['user-avatarBorder']; // Get the avatar border
				// Show popup if rank increased
				if (data.rank_increased) {
					showRankUpPopup = true;
				}
			}
		}
	});
</script>
<div class="flex bg-gray-800 w-full h-[100px] items-center justify-between">  
    <div class="flex items-center p-2 rounded-lg shadow-md">
        <div class="relative rounded-full p-1 shadow-inner">
            <div class="flex items-center justify-center ml-5">
                <img src="default.jpg" alt="Default Avatar" class="max-w-[80px] w-[70px] h-[60px]" />
                <img src={`./borders/${userAvatarBorder}`} alt="Usuário" class="absolute max-w-[100px] h-[80px]" />
            </div>
        </div>
        <div class="ml-2 flex flex-col items-start">
            <span class="text-white font-semibold">{userName}</span>
            <div class="relative w-24 h-2 bg-gray-600 rounded-full mt-1">
                <div class="h-full bg-green-500 rounded-full" style="width: {userExp}%;"></div>
                <span class="absolute left-1/2 transform -translate-x-1/2 text-white text-[10px] top-1/2 -translate-y-1/2">Level {userLevel}</span>
            </div>
        </div>
        <div class="flex items-center space-x-2 ml-4">	
            <img src={`./ranks/${userRank.replace(' ', '')}.png`} alt="Current Rank" class="w-[50px] h-[50px] inline-block" />
            <span class="text-white text-lg font-semibold">{userRank}</span>
        </div>
        <div class="flex items-center space-x-1 ml-2">
            <FontAwesomeIcon icon={faStar} class="text-yellow-500" />
            <p class="text-white font-bold mr-5">x {userStars}</p>
        </div>
    </div>

	<!-- Logout -->
    <div class="flex items-center bg-gray-800 p-2 rounded-lg shadow-md">
		<div class="flex items-center space-x-2 mr-4">	
			<img src="diamond.png" alt="Diamonds" class="w-10 h-10" />
			<span class="text-white font-semibold">{userDiamonds}</span>
		</div>
		<button 
			class="flex items-center space-x-2 mr-4 bg-gray-800 p-2 rounded-lg hover:bg-gray-700 transition" 
			on:click={() => window.location.href='/inventory'}
		>
			<FontAwesomeIcon icon={faBoxOpen} class="text-yellow-500 w-6 h-6" />
			<span class="text-white font-semibold">Inventory</span>
		</button>
		<button 
			class="flex items-center space-x-2 mr-4 bg-gray-800 p-2 rounded-lg hover:bg-gray-700 transition" 
			on:click={() => window.location.href='/leaderboards'}
		>
			<FontAwesomeIcon icon={faTrophy} class="text-yellow-500 w-6 h-6" />
			<span class="text-white font-semibold">Leaderboards</span>
		</button>
        <div class="flex items-center space-x-4">
            <button class="bg-blue-600 text-white px-6 py-2 rounded-lg flex items-center hover:bg-blue-500 transition" on:click={logout}>
                <FontAwesomeIcon icon={faSignOutAlt} class="mr-2" />
                Logout
            </button>
            <button 
                class="bg-purple-600 text-white px-6 py-2 rounded-lg flex items-center hover:bg-purple-500 transition"
                on:click={() => window.location.href='/dashboard'}
            >
                <span>Back to Dashboard</span>
            </button>
        </div>
    </div>
</div>

{#if showRankUpPopup}
    <div class="fixed top-0 left-0 w-full h-full bg-black bg-opacity-75 flex items-center justify-center z-50">
        <video class="w-full h-full object-cover" autoplay muted on:ended={() => {
            showContinueButton = true;
            const videoElement = document.querySelector('video');
            if (videoElement) {
                videoElement.pause(); // Pause the video when it ends
            }
        }}>
            <source src={`./resources/${userRank.replace(' ', '')}.mp4`} type="video/mp4" />
            Your browser does not support the video tag.
        </video>
        {#if showContinueButton}
            <button class="absolute bottom-10 bg-gradient-to-r from-blue-500 to-purple-600 text-white px-8 py-3 rounded-lg shadow-lg hover:scale-105 transition-transform" on:click={() => { 
                showRankUpPopup = false; 
                showContinueButton = false; 
            }}>
                Continue Game
            </button>
        {/if}
    </div>
{/if}