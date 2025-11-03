{{-- filepath: e:\Project\tug_of_math\resources\views\player\controller.blade.php --}}
@extends('layouts.app')

@section('title', 'Controller')

@section('content')
<div class="min-h-screen flex flex-col p-4">
    <!-- Question Display -->
    <div class="game-container rounded-3xl p-8 mb-6 shadow-2xl">
        <div class="text-center">
            <p class="text-white text-lg mb-2">Your Question:</p>
            <h3 id="question-text" class="text-white text-5xl font-bold mb-4">
                Loading...
            </h3>
            <div class="bg-white/20 rounded-2xl p-4">
                <p class="text-white text-2xl">Your Answer:</p>
                <input 
                    type="text" 
                    id="answer-display" 
                    readonly 
                    class="w-full bg-transparent text-white text-6xl font-bold text-center border-none focus:outline-none"
                    value="0"
                >
            </div>
        </div>
    </div>

    <!-- Number Pad -->
    <div class="game-container rounded-3xl p-6 shadow-2xl">
        <div class="grid grid-cols-3 gap-4 mb-4">
            <button onclick="addNumber(1)" class="number-btn bg-white hover:bg-gray-100 text-purple-900 font-bold text-4xl py-6 rounded-2xl shadow-lg">1</button>
            <button onclick="addNumber(2)" class="number-btn bg-white hover:bg-gray-100 text-purple-900 font-bold text-4xl py-6 rounded-2xl shadow-lg">2</button>
            <button onclick="addNumber(3)" class="number-btn bg-white hover:bg-gray-100 text-purple-900 font-bold text-4xl py-6 rounded-2xl shadow-lg">3</button>
            <button onclick="addNumber(4)" class="number-btn bg-white hover:bg-gray-100 text-purple-900 font-bold text-4xl py-6 rounded-2xl shadow-lg">4</button>
            <button onclick="addNumber(5)" class="number-btn bg-white hover:bg-gray-100 text-purple-900 font-bold text-4xl py-6 rounded-2xl shadow-lg">5</button>
            <button onclick="addNumber(6)" class="number-btn bg-white hover:bg-gray-100 text-purple-900 font-bold text-4xl py-6 rounded-2xl shadow-lg">6</button>
            <button onclick="addNumber(7)" class="number-btn bg-white hover:bg-gray-100 text-purple-900 font-bold text-4xl py-6 rounded-2xl shadow-lg">7</button>
            <button onclick="addNumber(8)" class="number-btn bg-white hover:bg-gray-100 text-purple-900 font-bold text-4xl py-6 rounded-2xl shadow-lg">8</button>
            <button onclick="addNumber(9)" class="number-btn bg-white hover:bg-gray-100 text-purple-900 font-bold text-4xl py-6 rounded-2xl shadow-lg">9</button>
            <button onclick="clearAnswer()" class="number-btn bg-red-500 hover:bg-red-600 text-white font-bold text-3xl py-6 rounded-2xl shadow-lg">C</button>
            <button onclick="addNumber(0)" class="number-btn bg-white hover:bg-gray-100 text-purple-900 font-bold text-4xl py-6 rounded-2xl shadow-lg">0</button>
            <button onclick="submitAnswer()" class="number-btn bg-green-500 hover:bg-green-600 text-white font-bold text-3xl py-6 rounded-2xl shadow-lg">✓</button>
        </div>
    </div>
</div>

<script>
    const playerId = {{ $player->id }};
    let currentAnswer = '';
    let currentQuestionId = null;

    // Listen for new questions
    const channel = pusher.subscribe('game-channel');
    channel.bind('question-updated', function(data) {
        if (data.player_id === playerId) {
            currentQuestionId = data.question.id;
            document.getElementById('question-text').textContent = data.question.question_text;
            clearAnswer();
        }
    });

    function addNumber(num) {
        if (currentAnswer === '0') currentAnswer = '';
        currentAnswer += num;
        updateDisplay();
    }

    function clearAnswer() {
        currentAnswer = '';
        updateDisplay();
    }

    function updateDisplay() {
        document.getElementById('answer-display').value = currentAnswer || '0';
    }

    async function submitAnswer() {
        if (!currentAnswer || !currentQuestionId) return;

        try {
            await axios.post(`/player/${playerId}/answer`, {
                question_id: currentQuestionId,
                player_answer: parseInt(currentAnswer)
            });

            // Visual feedback
            document.getElementById('answer-display').classList.add('animate-pulse');
            setTimeout(() => {
                document.getElementById('answer-display').classList.remove('animate-pulse');
            }, 500);

        } catch (error) {
            console.error('Error submitting answer:', error);
        }
    }
</script>
@endsection