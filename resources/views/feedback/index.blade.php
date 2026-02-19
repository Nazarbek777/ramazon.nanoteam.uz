@extends('layouts.app')
@section('title', 'Jamoatchilik Fikri')

@section('content')
<div class="page-header" style="text-align:center; margin-bottom:30px;">
    <h2 style="font-size:1.8rem; color:var(--gold);"><i class="ri-chat-voice-line"></i> Jamoatchilik Fikri</h2>
    <p class="text-muted" style="margin-bottom:8px;">Biz haqimizda istalgan fikringizni yoki saytga nima qo'shish kerakligini bemalol bildirishingiz mumkin.</p>
    <p style="font-size:0.85rem; color:var(--accent); font-weight:600;"><i class="ri-shield-user-fill"></i> Bildirilgan fikrlar mutlaqo anonim – kim yozganini hech kim ko'ra olmaydi.</p>
</div>

{{-- 📢 DIRECT FEEDBACK FORM --}}
<div class="card" style="max-width:600px; margin:0 auto 40px auto; padding:25px; border:1px solid var(--gold-border); background:linear-gradient(135deg, rgba(212,168,67,0.05), rgba(0,0,0,0.5));">
    <h3 style="font-size:1.1rem; color:var(--gold); margin-bottom:15px; text-align:center;"><i class="ri-edit-line"></i> O'z fikringizni qoldiring</h3>
    <form action="{{ route('feedback.store') }}" method="POST">
        @csrf
        <textarea name="content" placeholder="Ilova haqida fikringiz yoki taklifingiz..." style="width:100%; min-height:100px; background:rgba(255,255,255,0.03); border:1px solid var(--white-15); border-radius:12px; padding:15px; color:var(--text-primary); font-family:inherit; resize:none; margin-bottom:15px;" required></textarea>
        <div style="display:flex; align-items:center; justify-content:space-between; gap:10px;">
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:0.85rem; color:var(--text-secondary);">
                <input type="checkbox" name="is_public" value="1" checked style="width:18px; height:18px; accent-color:var(--gold);">
                Ommaga ko'rsatilsin (anonim)
            </label>
            <button type="submit" class="btn btn-gold">
                <i class="ri-send-plane-fill"></i> Yuborish
            </button>
        </div>
    </form>
</div>

<div class="feedback-grid" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap:20px;">
    @foreach($feedbacks as $feedback)
    <div class="card feedback-card" style="padding:20px; position:relative; overflow:hidden; border:1px solid var(--white-10); transition:var(--transition); display:flex; flex-direction:column;">
        <div class="quote-icon" style="position:absolute; top:-10px; right:10px; font-size:4rem; opacity:0.05; color:var(--gold);">
            <i class="ri-double-quotes-r"></i>
        </div>
        <div style="font-size:0.95rem; line-height:1.6; color:var(--text-primary); margin-bottom:15px; font-style:italic; flex:1;">
            "{{ $feedback->content }}"
        </div>
        
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; padding:0 5px;">
            <div style="display:flex; gap:15px;">
                <button onclick="interactFeedback('{{ $feedback->id }}', 'like')" class="interaction-btn {{ session('voted_feedback_'.$feedback->id) === 'liked' ? 'active' : '' }}" style="background:none; border:none; color:var(--text-muted); cursor:pointer; display:flex; align-items:center; gap:5px; font-size:0.85rem; transition:all 0.3s;">
                    <i class="ri-thumb-up-line"></i> <span id="likes-{{ $feedback->id }}">{{ $feedback->likes_count }}</span>
                </button>
                <button onclick="interactFeedback('{{ $feedback->id }}', 'dislike')" class="interaction-btn {{ session('voted_feedback_'.$feedback->id) === 'disliked' ? 'active' : '' }}" style="background:none; border:none; color:var(--text-muted); cursor:pointer; display:flex; align-items:center; gap:5px; font-size:0.85rem; transition:all 0.3s;">
                    <i class="ri-thumb-down-line"></i> <span id="dislikes-{{ $feedback->id }}">{{ $feedback->dislikes_count }}</span>
                </button>
            </div>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--white-5); padding-top:10px;">
            <span style="font-size:0.75rem; color:var(--text-muted);">
                <i class="ri-user-smile-line"></i> Anonim
            </span>
            <span style="font-size:0.7rem; color:var(--text-muted);">
                {{ $feedback->created_at->translatedFormat('d F, Y') }}
            </span>
        </div>
    </div>
    @endforeach
</div>

@if($feedbacks->isEmpty())
    <div style="text-align:center; padding:60px 0;">
        <i class="ri-chat-history-line" style="font-size:4rem; color:var(--white-10); display:block; margin-bottom:20px;"></i>
        <p class="text-muted">Hozircha ommaviy fikrlar yo'q. Birinchilardan bo'lib yozing!</p>
    </div>
@endif

<div class="pagination-wrapper" style="margin-top:40px; display:flex; justify-content:center;">
    {{ $feedbacks->links() }}
</div>

<script>
    function interactFeedback(id, type) {
        const url = `/feedback/${id}/${type}`;
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const countEl = document.getElementById(`${type}s-${id}`);
                countEl.textContent = data[type + 's'];
                // Update button style
                const btn = countEl.parentElement;
                btn.style.color = type === 'like' ? '#4ade80' : '#f87171';
                btn.classList.add('active');
            } else {
                showToast(data.message || 'Xatolik yuz berdi');
            }
        });
    }

    function showToast(msg) {
        let t = document.getElementById('dash-toast');
        if (!t) {
            t = document.createElement('div');
            t.id = 'dash-toast';
            t.style = 'position:fixed;bottom:80px;left:50%;transform:translateX(-50%);background:var(--gold);color:white;padding:12px 24px;border-radius:50px;z-index:1000;box-shadow:0 10px 30px rgba(0,0,0,0.3);font-size:0.9rem;font-weight:700;pointer-events:none;transition:all 0.4s;opacity:0;';
            document.body.appendChild(t);
        }
        t.textContent = msg;
        t.style.opacity = '1';
        t.style.transform = 'translateX(-50%) translateY(-10px)';
        setTimeout(() => { t.style.opacity = '0'; t.style.transform = 'translateX(-50%) translateY(0)'; }, 3000);
    }
</script>

<style>
    .feedback-card:hover {
        transform: translateY(-5px);
        border-color: var(--gold-border);
        background: var(--bg-card-hover);
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
</style>
@endsection
