// Get side from window global variable (set by blade)
const side = window.__PLAYER_SIDE__;
let playerId = null;

// Wait for Echo to be ready
function initializeJoinPage() {
    if (typeof window.Echo === 'undefined') {
        console.log('Waiting for Echo...');
        setTimeout(initializeJoinPage, 100);
        return;
    }

    console.log('Echo is ready!');
    setupJoinForm();
}

function setupJoinForm() {
    document.getElementById('join-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const name = document.getElementById('player-name').value;
        
        try {
            const response = await axios.post(`/join/${side}`, { name });
            playerId = response.data.player_id;
            
            console.log('Join response:', response.data);
            
            // Show waiting screen
            document.getElementById('join-form').classList.add('hidden');
            document.getElementById('waiting-screen').classList.remove('hidden');
            
            // Langsung redirect ke controller dengan player ID
            console.log('Redirecting to controller:', `/player/${playerId}/controller`);
            setTimeout(() => {
                window.location.href = `/player/${playerId}/controller`;
            }, 500);
            
        } catch (error) {
            console.error('Error joining game:', error);
            alert('Error joining game: ' + (error.response?.data?.message || error.message));
        }
    });
}

// Start initialization when page loads
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeJoinPage);
} else {
    initializeJoinPage();
}
