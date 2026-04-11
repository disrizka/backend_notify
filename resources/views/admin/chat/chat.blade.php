<x-app-layout>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
        #chat-container::-webkit-scrollbar { width: 4px; }
        #chat-container::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .highlight-msg { background-color: #e0e7ff !important; transition: background-color 0.5s; }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden flex flex-col" style="height: 700px;">
                
                <div class="p-4 border-b flex items-center justify-between bg-white z-20">
                    <h3 class="font-bold text-xl text-indigo-700">
                        <i class="fas fa-comments mr-2"></i> Grup Chat Notify
                    </h3>
                </div>

              @php 
                    $pinnedMessages = $messages->where('is_pinned', true)->values();
                    $latestPinned = $pinnedMessages->last();
                @endphp

                @if($latestPinned)
                    <div id="pinned-bar" class="bg-indigo-50 border-b px-4 py-2 flex items-center justify-between sticky top-0 z-10 shadow-sm">
                        <div class="flex items-center gap-3 overflow-hidden cursor-pointer flex-1" onclick="cyclePinnedMessages()">
                            <i class="fas fa-thumbtack text-indigo-500 rotate-45 text-sm"></i>
                            <div class="overflow-hidden">
                                <p id="pinned-count-label" class="text-[10px] font-bold text-indigo-600 uppercase">
                                    Pesan Tersemat ({{ $pinnedMessages->count() }})
                                </p>
                                <p id="pinned-text-preview" class="text-xs text-gray-600 truncate">
                                    {{ $latestPinned->message ?? '[' . ucfirst($latestPinned->type) . ']' }}
                                </p>
                            </div>
                        </div>
                        
                        <form id="unpin-form" action="{{ route('web.chats.unpin', $latestPinned->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="text-gray-400 hover:text-red-500 p-2">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </form>
                    </div>
                @endif
                
                
                <div id="chat-container" class="flex-1 p-4 bg-gray-50 overflow-y-auto shadow-inner" 
                     style="display: flex; flex-direction: column; gap: 15px;">
                    
                    @foreach($messages as $msg)
                        @php $isMe = (int)$msg->user_id === (int)Auth::id(); @endphp

                        <div id="msg-{{ $msg->id }}" class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }}" 
                             x-data="{ openMenu: false }" style="width: 100%; align-items: {{ $isMe ? 'flex-end' : 'flex-start' }}">
                            
                            <div class="flex items-center gap-1 mb-1 px-2">
                                @if($msg->is_pinned)
                                    <i class="fas fa-thumbtack text-indigo-500 text-[10px] rotate-45"></i>
                                @endif
                                @if(!$isMe)
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">{{ $msg->user->name ?? 'User' }}</span>
                                @endif
                            </div>

                            <div class="relative flex items-center gap-2 max-w-[85%]">
                                <div x-show="openMenu" x-cloak @click.away="openMenu = false"
                                     class="absolute z-30 bottom-full {{ $isMe ? 'right-0' : 'left-0' }} mb-2 w-36 bg-white rounded-lg shadow-xl border py-1 text-xs">
                                    
                                    <form action="{{ $msg->is_pinned ? route('web.chats.unpin', $msg->id) : route('web.chats.pin', $msg->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-3 py-2 hover:bg-gray-50 flex items-center gap-2">
                                            <i class="fas fa-thumbtack w-4 text-gray-400"></i> {{ $msg->is_pinned ? 'Lepas Pin' : 'Sematkan' }}
                                        </button>
                                    </form>

                                    @if($isMe)
                                    <button @click="editMsg({{ $msg->id }}, '{{ $msg->message }}'); openMenu = false" 
                                            class="w-full text-left px-3 py-2 hover:bg-gray-50 flex items-center gap-2 text-blue-600 border-t">
                                        <i class="fas fa-edit w-4"></i> Edit
                                    </button>
                                    <form action="{{ route('web.chats.destroy', $msg->id) }}" method="POST" onsubmit="return confirm('Hapus pesan?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-full text-left px-3 py-2 hover:bg-gray-50 flex items-center gap-2 text-red-500 border-t">
                                            <i class="fas fa-trash w-4"></i> Hapus
                                        </button>
                                    </form>
                                    @endif

                                    <button @click="showSeenBy({{ $msg->id }}); openMenu = false" class="w-full text-left px-3 py-2 hover:bg-gray-50 flex items-center gap-2 text-gray-600 border-t">
                                        <i class="fas fa-eye w-4"></i> Info Pesan
                                    </button>
                                </div>

                                <div @click="openMenu = !openMenu" 
                                     class="cursor-pointer shadow-sm active:scale-95 transition-all"
                                     style="padding: 10px 15px; border-radius: 18px; 
                                     {{ $isMe ? 'background: #4f46e5; color: white; border-top-right-radius: 2px;' : 'background: white; color: #1f2937; border-top-left-radius: 2px; border: 1px solid #e5e7eb;' }}">
                                    
                                    @if($msg->type == 'text')
                                        <p class="text-sm leading-relaxed">{{ $msg->message }}</p>
                                    @elseif($msg->type == 'image')
                                        <img src="{{ asset($msg->file_path) }}" class="rounded-lg mb-1 max-w-full block" style="max-height: 250px;">
                                    @elseif($msg->type == 'video')
                                        <video controls class="rounded-lg w-full max-w-[280px] mb-1"><source src="{{ asset($msg->file_path) }}"></video>
                                    @endif

                                    <div class="flex items-center justify-end mt-1 gap-1">
                                        <span style="font-size: 9px; opacity: 0.7;">{{ $msg->created_at->format('H:i') }}</span>
                                        @if($isMe)
                                            <span style="font-size: 10px;">
                                                <i class="fas {{ $msg->seenBy->count() > 0 ? 'fa-check-double text-blue-300' : 'fa-check opacity-50' }}"></i>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="p-4 bg-white border-t">
                    <form action="{{ route('web.chats.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" id="chat-type" value="text">
                        <div id="file-preview" class="hidden mb-2 px-4 py-2 bg-indigo-50 rounded-lg text-xs text-indigo-700 flex justify-between items-center font-bold">
                            <span id="file-name-label"></span>
                            <button type="button" onclick="cancelFile()" class="text-red-500 hover:underline">Batal</button>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="cursor-pointer text-gray-400 hover:text-indigo-600 transition">
                                <i class="fas fa-paperclip text-xl"></i>
                                <input type="file" name="file" id="file-input" class="hidden" onchange="handleFile(this)">
                            </label>
                            <input type="text" name="message" id="message-input" required
                                   class="flex-1 rounded-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-3 px-6" 
                                   placeholder="Ketik pesan...">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-full w-12 h-12 flex items-center justify-center shadow-md">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    const container = document.getElementById('chat-container');
    document.addEventListener('DOMContentLoaded', () => { container.scrollTop = container.scrollHeight; });

    // --- FITUR EDIT PESAN ---
    function editMsg(id, oldMessage) {
        const newMessage = prompt("Ubah pesan kamu:", oldMessage);
        
        // Pastikan ada perubahan dan tidak kosong
        if (newMessage !== null && newMessage.trim() !== "" && newMessage !== oldMessage) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/messages/${id}`; // Mengarah ke Route::put di web.php
            
            form.innerHTML = `
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="_method" value="PUT">
                <input type="hidden" name="message" value="${newMessage}">
            `;
            
            document.body.appendChild(form);
            form.submit();
        }
    }

    // --- FITUR INFO PESAN (SEEN BY) ---
    function showSeenBy(id) {
        fetch(`/api/chats/${id}/seen`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data && data.length > 0) {
                const names = data.map(u => `• ${u.name}`).join('\n');
                alert("Dibaca oleh:\n" + names);
            } else {
                alert("Belum ada yang membaca pesan ini.");
            }
        })
        .catch(() => alert("Gagal memuat info pesan."));
    }

    // --- FITUR PINNED MESSAGES ---
    const pinnedData = @json($messages->where('is_pinned', true)->values());
    let currentPinIndex = pinnedData.length - 1;

    function cyclePinnedMessages() {
        if (pinnedData.length <= 0) return;
        if (pinnedData.length === 1) {
            scrollToMessage(pinnedData[0].id);
            return;
        }
        
        currentPinIndex = (currentPinIndex - 1 + pinnedData.length) % pinnedData.length;
        const currentPin = pinnedData[currentPinIndex];
        
        const previewText = currentPin.type === 'text' ? currentPin.message : `[${currentPin.type.toUpperCase()}]`;
        document.getElementById('pinned-text-preview').innerText = previewText;
        document.getElementById('unpin-form').action = `/messages/${currentPin.id}/unpin`;
        
        scrollToMessage(currentPin.id);
    }

    function scrollToMessage(id) {
        const el = document.getElementById(`msg-${id}`);
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            el.classList.add('highlight-msg'); 
            setTimeout(() => el.classList.remove('highlight-msg'), 2000);
        }
    }

    // --- FITUR UPLOAD FILE ---
    function handleFile(input) {
        if (input.files && input.files[0]) {
            const f = input.files[0];
            const ext = f.name.split('.').pop().toLowerCase();
            document.getElementById('file-name-label').innerText = "File: " + f.name;
            document.getElementById('file-preview').classList.remove('hidden');
            document.getElementById('message-input').removeAttribute('required');
            const t = document.getElementById('chat-type');
            if (['jpg','jpeg','png','gif'].includes(ext)) t.value = 'image';
            else if (['mp4','mov','avi'].includes(ext)) t.value = 'video';
            else t.value = 'file';
        }
    }

    function cancelFile() {
        document.getElementById('file-input').value = "";
        document.getElementById('file-preview').classList.add('hidden');
        document.getElementById('chat-type').value = 'text';
        document.getElementById('message-input').setAttribute('required', 'required');
    }
    </script>
</x-app-layout>