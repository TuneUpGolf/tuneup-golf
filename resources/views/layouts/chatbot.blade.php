<div id="chat-toggle" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;">
    <button id="chat-open-btn" style="position: relative; padding: 10px 20px; background-color: #007bff; color: white; border-radius: 9999px; cursor: pointer; border: none;">
        💬 Chat
        <span id="chat-notification" style="display: none; position: absolute; top: 0; right: 0; background: red; color: white; border-radius: 9999px; padding: 2px 6px; font-size: 12px;">●</span>
    </button>
</div>

<!-- Chat Window -->
<div id="floating-chat" style="display: none; position: fixed; bottom: 80px; right: 20px; width: 345px; background-color: white; border: 1px solid #ccc; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); z-index: 10000;">
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background-color: #f9f9f9; border-bottom: 1px solid #ddd;">
        <strong>Live Chat</strong>
        <button id="chat-close-btn" style="background: none; border: none; font-size: 18px; cursor: pointer;">&times;</button>
    </div>
    <div id="chat-box" style="height: 200px; overflow-y: auto; padding: 10px; font-size: 14px; background-color: #f5f5f5;">
        <!-- Messages will appear here -->
    </div>
    <form id="chat-form" style="display: flex; align-items: center; padding: 10px; border-top: 1px solid #ddd;">
        <button type="button" class="bg-warning" id="record-btn" style="margin: 8px; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer;">🎤</button>
        <input type="text" id="message" placeholder="Type a message..." style="flex: 1; padding: 6px 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px;">
        <input type="file" id="file" style="display: none;">
        <label for="file" style="margin-left: 8px; cursor: pointer;">📎</label>
        <button type="submit" style="margin-left: 8px; background-color: #007bff; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer;">Send</button>
    </form>
</div>

@push('javascript')
<script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    const API_URL = 'http://localhost:3000';
    const socket = io(API_URL);
    const chatBox = document.getElementById('chat-box');
    const chatForm = document.getElementById('chat-form');
    const messageInput = document.getElementById('message');
    const fileInput = document.getElementById('file');

    const openBtn = document.getElementById('chat-open-btn');
    const closeBtn = document.getElementById('chat-close-btn');
    const chatWindow = document.getElementById('floating-chat');
    const notificationDot = document.getElementById('chat-notification');

    let isChatOpen = false;

    openBtn.addEventListener('click', () => {
        chatWindow.style.display = 'block';
        isChatOpen = true;
        notificationDot.style.display = 'none';
    });

    closeBtn.addEventListener('click', () => {
        chatWindow.style.display = 'none';
        isChatOpen = false;
    });

    axios.get(`${API_URL}/messages`).then(res => {
        res.data.forEach(renderMessage);
    });

    socket.on('message', msg => renderMessage(msg));

    chatForm.addEventListener('submit', async e => {
        e.preventDefault();
        const text = messageInput.value;
        const file = fileInput.files[0];
        if (!text && !file) return;

        const formData = new FormData();
        formData.append('text', text);
        if (file) formData.append('file', file);
        formData.append('user', @json(auth()->user()->name));

        const res = await axios.post(`${API_URL}/messages`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        messageInput.value = '';
        fileInput.value = '';
    });

    socket.on('audio-message', ({ user, url }) => {
        const chatBox = document.getElementById('chat-box');
        const message = document.createElement('div');
        message.innerHTML = `<strong>${user}:</strong><br><audio controls src="${url}" style="width: 100%;"></audio>`;
        chatBox.appendChild(message);
    });

    function renderMessage(msg) {
        const div = document.createElement('div');
        div.className = 'mb-1';
        div.innerHTML = `<strong>${msg.user}:</strong> ${msg.text}
            ${msg.file ? `<br><a href="${API_URL}/${msg.file}" target="_blank">📎</a>` : ''}`;
        chatBox.appendChild(div);
        chatBox.scrollTop = chatBox.scrollHeight;

        if (!isChatOpen) {
            notificationDot.style.display = 'inline';
        }
    }
</script>

<script>
    let mediaRecorder;
    let audioChunks = [];
    
    document.getElementById('record-btn').addEventListener('click', async () => {
        if (!mediaRecorder || mediaRecorder.state === 'inactive') {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            mediaRecorder = new MediaRecorder(stream);
            audioChunks = [];
    
            mediaRecorder.ondataavailable = (event) => {
                audioChunks.push(event.data);
            };
    
            mediaRecorder.onstop = () => {
                const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                const audioFile = new File([audioBlob], `audio-${Date.now()}.webm`, { type: 'audio/webm' });
    
                const formData = new FormData();
                formData.append('audio', audioFile);
                formData.append('user', 'You');
    
                fetch('http://localhost:3000/upload-audio', {
                    method: 'POST',
                    body: formData
                });
    
                // Optionally show local audio
                const audioURL = URL.createObjectURL(audioBlob);
                const audioEl = document.createElement('audio');
                audioEl.controls = true;
                audioEl.src = audioURL;
                document.getElementById('chat-box').appendChild(audioEl);
            };
    
            mediaRecorder.start();
            alert('Recording... Click mic again to stop');
        } else {
            mediaRecorder.stop();
        }
    });
    </script>
    

@endpush
