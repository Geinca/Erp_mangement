<?php
session_start();

// Initialize chat history if it doesn't exist
if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [
        [
            'type' => 'bot', 
            'content' => "👋 Hello! I'm your GEINCA HR Assistant. I can help you with:",
            'buttons' => [
                ['text' => 'Leave Requests', 'value' => 'How do I request leave?'],
                ['text' => 'Holiday Calendar', 'value' => 'Show upcoming holidays'],
                ['text' => 'Company Policies', 'value' => 'What policies are available?']
            ]
        ]
    ];
}

// Process message if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = [];
    
    if (isset($_POST['user_message'])) {
        $userMessage = trim($_POST['user_message']);
        
        if (!empty($userMessage)) {
            // Add user message to history
            $_SESSION['chat_history'][] = [
                'type' => 'user',
                'content' => $userMessage
            ];
            
            // Generate bot response
            $botResponse = generateBotResponse($userMessage);
            
            // Add bot response to history
            $_SESSION['chat_history'][] = $botResponse;
            
            $response = $botResponse;
        }
    }
    
    // If AJAX request, return JSON response
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

function generateBotResponse($message) {
    $message = strtolower(trim($message));
    
    $responses = [
        'leave' => [
            'pattern' => '/leave|vacation|time off|day off|absence|request time off/i',
            'response' => [
                'content' => "For leave requests:\n\n1. Submit through the HR portal at least 3 days in advance\n2. Your manager will review the request\n3. You'll receive a confirmation email\n\nYou currently have 12 remaining leave days this year.",
                'buttons' => [
                    ['text' => 'Request Leave Now', 'value' => 'I want to request leave'],
                    ['text' => 'Check Balance', 'value' => 'How many leave days do I have left?']
                ]
            ]
        ],
        'holiday' => [
            'pattern' => '/holiday|vacation|break|time off|upcoming holidays/i',
            'response' => [
                'content' => "📅 Upcoming Company Holidays:\n\n- New Year's Day: Jan 1 (Monday)\n- Memorial Day: May 27 (Monday)\n- Independence Day: July 4 (Thursday)\n- Labor Day: Sep 2 (Monday)\n- Thanksgiving: Nov 28-29 (Thu-Fri)\n- Christmas: Dec 25 (Wednesday)",
                'buttons' => [
                    ['text' => 'View Full Calendar', 'value' => 'Show full holiday calendar'],
                    ['text' => 'Public Holidays', 'value' => 'What are the national holidays?']
                ]
            ]
        ],
        'policy' => [
            'pattern' => '/policy|policies|rule|guideline|procedure/i',
            'response' => [
                'content' => "📚 Company Policies:\n\n1. Leave Policy (Paid time off, sick leave)\n2. Attendance Policy (Punctuality requirements)\n3. Dress Code (Office attire guidelines)\n4. Remote Work (Work from home policies)\n5. Code of Conduct (Professional behavior)\n\nWhich policy would you like details about?",
                'buttons' => [
                    ['text' => 'Leave Policy', 'value' => 'Tell me about leave policy'],
                    ['text' => 'Remote Work', 'value' => 'What is the remote work policy?'],
                    ['text' => 'Dress Code', 'value' => 'What is the dress code policy?']
                ]
            ]
        ],
        'greeting' => [
            'pattern' => '/hello|hi|hey|greetings|good morning|good afternoon/i',
            'response' => [
                'content' => "Hello! I'm your GEINCA HR Assistant. How can I help you today?",
                'buttons' => [
                    ['text' => 'Leave Requests', 'value' => 'How do I request leave?'],
                    ['text' => 'Holiday Calendar', 'value' => 'Show upcoming holidays'],
                    ['text' => 'Company Policies', 'value' => 'What policies are available?']
                ]
            ]
        ],
        'thanks' => [
            'pattern' => '/thank|thanks|appreciate|grateful/i',
            'response' => [
                'content' => "You're welcome! 😊 Is there anything else I can help you with?",
                'buttons' => [
                    ['text' => 'Yes, more help', 'value' => 'I have another question'],
                    ['text' => 'No, all set', 'value' => 'No thank you']
                ]
            ]
        ],
        'default' => [
            'response' => [
                'content' => "I'm here to help with HR-related questions about:\n- Leave requests\n- Holiday information\n- Company policies\n\nCould you please rephrase or select from these options?",
                'buttons' => [
                    ['text' => 'Leave Help', 'value' => 'I need help with leave'],
                    ['text' => 'Holiday Info', 'value' => 'Tell me about holidays'],
                    ['text' => 'Policy Questions', 'value' => 'I have policy questions']
                ]
            ]
        ]
    ];
    
    foreach ($responses as $response) {
        if (isset($response['pattern']) && preg_match($response['pattern'], $message)) {
            return array_merge(['type' => 'bot'], $response['response']);
        }
    }
    
    return array_merge(['type' => 'bot'], $responses['default']['response']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GEINCA HR Assistant</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2563eb',
                        secondary: '#1d4ed8',
                        accent: '#059669',
                        dark: '#1e293b',
                        light: '#f8fafc'
                    }
                }
            }
        }
    </script>
    <style>
        .chat-container {
            height: calc(100vh - 180px);
        }
        .message-user {
            border-bottom-right-radius: 0;
        }
        .message-bot {
            border-bottom-left-radius: 0;
        }
        .typing-indicator {
            display: inline-block;
        }
        .typing-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #64748b;
            margin-right: 4px;
            animation: typingAnimation 1.4s infinite both;
        }
        .typing-dot:nth-child(1) {
            animation-delay: 0s;
        }
        .typing-dot:nth-child(2) {
            animation-delay: 0.2s;
        }
        .typing-dot:nth-child(3) {
            animation-delay: 0.4s;
            margin-right: 0;
        }
        @keyframes typingAnimation {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-5px); }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <aside class="w-64 bg-white shadow-lg hidden md:block">
            <?php include 'sidebar.php'; ?>
        </aside>

        <main class="flex-1 flex flex-col">
            <header class="bg-white shadow-sm p-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white mr-3">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">GEINCA HR Assistant</h1>
                        <p class="text-xs text-gray-500">Online</p>
                    </div>
                </div>
            </header>

            <section class="flex-1 overflow-hidden flex flex-col">
                <div class="flex-1 p-4 overflow-y-auto" id="chat-messages">
                    <?php foreach ($_SESSION['chat_history'] as $index => $message): ?>
                        <div class="flex mb-4 <?= $message['type'] === 'user' ? 'justify-end' : 'justify-start' ?>">
                            <div class="<?= $message['type'] === 'user' ? 'bg-primary text-white message-user' : 'bg-gray-100 text-gray-800 message-bot' ?> rounded-lg px-4 py-2 max-w-xs md:max-w-md lg:max-w-lg shadow-sm">
                                <?= nl2br(htmlspecialchars($message['content'])) ?>
                                
                                <?php if (isset($message['buttons'])): ?>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <?php foreach ($message['buttons'] as $button): ?>
                                            <button 
                                                class="quick-reply-btn bg-white text-gray-800 border border-gray-300 hover:bg-gray-100 rounded-full px-3 py-1 text-xs shadow-sm"
                                                data-message="<?= htmlspecialchars($button['value']) ?>"
                                            >
                                                <?= htmlspecialchars($button['text']) ?>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="border-t border-gray-200 p-4 bg-white">
                    <form method="POST" id="chat-form" class="flex gap-2">
                        <input 
                            type="text" 
                            id="user-input" 
                            name="user_message" 
                            placeholder="Type your message here..." 
                            autocomplete="off"
                            class="flex-1 border border-gray-300 rounded-full px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            required
                        />
                        <button 
                            type="submit"
                            class="bg-primary hover:bg-secondary text-white rounded-full w-12 h-12 flex items-center justify-center transition-colors duration-200 shadow-sm"
                            aria-label="Send message"
                        >
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                    <div class="text-xs text-gray-500 mt-2 text-center">
                        GEINCA HR Assistant v2.0 - <?= date('Y') ?>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        // Auto-scroll to bottom of chat and focus input on load
        document.addEventListener('DOMContentLoaded', function() {
            scrollToBottom();
            document.getElementById('user-input').focus();
            
            // Add event listeners for quick reply buttons
            document.querySelectorAll('.quick-reply-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const message = this.getAttribute('data-message');
                    document.getElementById('user-input').value = message;
                    document.getElementById('chat-form').dispatchEvent(new Event('submit'));
                });
            });
        });

        // Handle form submission with AJAX
        document.getElementById('chat-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const formData = new FormData(form);
            const input = document.getElementById('user-input');
            const message = input.value.trim();
            
            if (message) {
                // Add user message immediately
                addMessageToChat(message, 'user');
                input.value = '';
                
                // Show typing indicator
                const typingId = 'typing-' + Date.now();
                showTypingIndicator(typingId);
                
                // Send AJAX request
                fetch('', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    // Remove typing indicator
                    removeTypingIndicator(typingId);
                    
                    // Add bot response
                    addMessageToChat(data.content, 'bot', data.buttons);
                    
                    // Scroll to bottom after new message is added
                    setTimeout(scrollToBottom, 100);
                })
                .catch(error => {
                    console.error('Error:', error);
                    removeTypingIndicator(typingId);
                    addMessageToChat("Sorry, I'm having trouble responding right now. Please try again later.", 'bot');
                });
            }
        });

        // Helper function to add messages to chat
        function addMessageToChat(content, type, buttons = null) {
            const chatMessages = document.getElementById('chat-messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `flex mb-4 ${type === 'user' ? 'justify-end' : 'justify-start'}`;
            
            let buttonsHTML = '';
            if (buttons && buttons.length > 0) {
                buttonsHTML = `<div class="mt-2 flex flex-wrap gap-2">${
                    buttons.map(button => `
                        <button 
                            class="quick-reply-btn bg-white text-gray-800 border border-gray-300 hover:bg-gray-100 rounded-full px-3 py-1 text-xs shadow-sm"
                            data-message="${button.value.replace(/"/g, '&quot;')}"
                        >
                            ${button.text}
                        </button>
                    `).join('')
                }</div>`;
            }
            
            messageDiv.innerHTML = `
                <div class="${type === 'user' ? 'bg-primary text-white message-user' : 'bg-gray-100 text-gray-800 message-bot'} rounded-lg px-4 py-2 max-w-xs md:max-w-md lg:max-w-lg shadow-sm">
                    ${content.replace(/\n/g, '<br>')}
                    ${buttonsHTML}
                </div>
            `;
            
            chatMessages.appendChild(messageDiv);
            
            // Add event listeners to new quick reply buttons
            messageDiv.querySelectorAll('.quick-reply-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const message = this.getAttribute('data-message');
                    document.getElementById('user-input').value = message;
                    document.getElementById('chat-form').dispatchEvent(new Event('submit'));
                });
            });
        }

        // Show typing indicator
        function showTypingIndicator(id) {
            const chatMessages = document.getElementById('chat-messages');
            const typingDiv = document.createElement('div');
            typingDiv.id = id;
            typingDiv.className = 'flex mb-4 justify-start';
            typingDiv.innerHTML = `
                <div class="bg-gray-100 text-gray-800 rounded-lg px-4 py-2 max-w-xs shadow-sm">
                    <div class="typing-indicator">
                        <span class="typing-dot"></span>
                        <span class="typing-dot"></span>
                        <span class="typing-dot"></span>
                    </div>
                </div>
            `;
            chatMessages.appendChild(typingDiv);
            scrollToBottom();
        }

        // Remove typing indicator
        function removeTypingIndicator(id) {
            const typingElement = document.getElementById(id);
            if (typingElement) {
                typingElement.remove();
            }
        }

        // Helper function to scroll to bottom
        function scrollToBottom() {
            const chatMessages = document.getElementById('chat-messages');
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Allow sending message with Enter key (but allow Shift+Enter for new lines)
        document.getElementById('user-input').addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                document.getElementById('chat-form').dispatchEvent(new Event('submit'));
            }
        });
    </script>
</body>
</html>