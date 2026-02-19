@extends('layouts.app')
@section('title', 'Jamoatchilik Fikri')

@section('content')
<div class="page-header" style="text-align:center; margin-bottom:25px; padding:0 15px;">
    <div style="display:inline-flex; align-items:center; justify-content:center; width:48px; height:48px; background:var(--gold-bg); border-radius:14px; margin-bottom:12px; box-shadow:0 8px 15px rgba(212,168,67,0.15);">
        <i class="ri-chat-voice-line" style="font-size:1.5rem; color:var(--gold);"></i>
    </div>
    <h2 style="font-size:1.6rem; font-weight:900; color:var(--text-primary); margin-bottom:8px; letter-spacing:-0.5px;">Jamoatchilik Fikri</h2>
    <div style="max-width:500px; margin:0 auto;">
        <p style="font-size:0.88rem; color:var(--text-secondary); line-height:1.5; margin-bottom:10px;">Biz haqimizda istalgan fikringizni bemalol bildirishingiz mumkin.</p>
        <div style="display:inline-flex; align-items:center; gap:6px; padding:4px 12px; background:rgba(74, 222, 128, 0.1); border:1px solid rgba(74, 222, 128, 0.2); border-radius:50px; color:#4ade80; font-size:0.75rem; font-weight:600;">
            <i class="ri-shield-user-fill"></i> Mutlaqo anonim
        </div>
    </div>
</div>

{{-- 📢 FEEDBACK FORM --}}
<div style="max-width:600px; margin:0 auto 30px auto; padding:0 15px;">
    <div class="card" style="padding:20px; border:1px solid var(--white-10); background:linear-gradient(145deg, rgba(255,255,255,0.03), rgba(0,0,0,0.2)); border-radius:20px;">
        <h3 style="font-size:1rem; font-weight:800; color:var(--gold); margin-bottom:15px; display:flex; align-items:center; gap:8px;">
            <i class="ri-edit-2-line"></i> Fikr qoldirish
        </h3>
        <form action="{{ route('feedback.store') }}" method="POST">
            @csrf
            <div style="position:relative; margin-bottom:15px;">
                <textarea name="content" placeholder="Fikringiz yoki taklifingiz..." required
                        style="width:100%; min-height:90px; background:rgba(0,0,0,0.2); border:1px solid var(--white-10); border-radius:12px; padding:15px; color:var(--text-primary); font-family:inherit; resize:none; font-size:0.9rem; transition:all 0.3s;"></textarea>
            </div>
            
            <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:0.85rem; color:var(--text-secondary); user-select:none;">
                    <div class="checkbox-wrapper" style="position:relative; width:18px; height:18px;">
                        <input type="checkbox" name="is_public" value="1" checked 
                               style="width:100%; height:100%; opacity:0; position:absolute; cursor:pointer; z-index:2;">
                        <div class="checkbox-custom" style="width:100%; height:100%; border:2px solid var(--white-20); border-radius:4px; transition:0.3s; display:flex; align-items:center; justify-content:center;">
                            <i class="ri-check-line" style="font-size:0.9rem; color:white; display:none;"></i>
                        </div>
                    </div>
                    <span>Ommaga ko'rsatilsin</span>
                </label>
                
                <button type="submit" class="btn btn-gold" style="padding:8px 20px; border-radius:10px; font-size:0.9rem;">
                    <i class="ri-send-plane-fill"></i> Yuborish
                </button>
            </div>
        </form>
    </div>
</div>

<div style="border-top:1px solid var(--white-10); margin-bottom:25px; position:relative;">
    <span style="position:absolute; top:-10px; left:50%; transform:translateX(-50%); background:var(--bg-main); padding:0 15px; color:var(--text-muted); font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:1px;">Fikrlar</span>
</div>

<div class="feedback-grid" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap:15px; padding:0 10px;">
    @foreach($feedbacks as $feedback)
    <div class="card feedback-card" style="padding:25px; border:1px solid var(--white-10); border-radius:20px; background:var(--white-3); transition:all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); display:flex; flex-direction:column;">
        <div style="font-size:3rem; line-height:1; color:var(--gold); opacity:0.1; height:20px; margin-top:-10px;">
            <i class="ri-double-quotes-l"></i>
        </div>
        
        <div style="font-size:1rem; line-height:1.7; color:var(--text-primary); margin-bottom:20px; font-weight:500; flex:1;">
            {{ $feedback->content }}
        </div>
        
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
            <div style="display:flex; background:rgba(255,255,255,0.05); padding:4px 12px; border-radius:50px; gap:16px;">
                <button onclick="interactFeedback('{{ $feedback->id }}', 'like')" 
                        class="interaction-btn {{ session('voted_feedback_'.$feedback->id) === 'liked' ? 'active' : '' }}" 
                        style="background:none; border:none; color:var(--text-muted); cursor:pointer; display:flex; align-items:center; gap:5px; font-size:0.8rem; font-weight:700; transition:0.3s;">
                    <i class="ri-thumb-up-line"></i> <span id="likes-{{ $feedback->id }}">{{ $feedback->likes_count }}</span>
                </button>
                <button onclick="interactFeedback('{{ $feedback->id }}', 'dislike')" 
                        class="interaction-btn {{ session('voted_feedback_'.$feedback->id) === 'disliked' ? 'active' : '' }}" 
                        style="background:none; border:none; color:var(--text-muted); cursor:pointer; display:flex; align-items:center; gap:5px; font-size:0.8rem; font-weight:700; transition:0.3s;">
                    <i class="ri-thumb-down-line"></i> <span id="dislikes-{{ $feedback->id }}">{{ $feedback->dislikes_count }}</span>
                </button>
            </div>
            
            <div style="font-size:0.75rem; color:var(--text-muted); font-weight:600;">
                <i class="ri-calendar-line"></i> {{ $feedback->created_at->translatedFormat('d F') }}
            </div>
        </div>

        <div style="border-top:1px solid var(--white-5); padding-top:12px; display:flex; align-items:center; gap:8px;">
            <div style="width:24px; height:24px; background:var(--gold-bg); border-radius:50%; display:flex; align-items:center; justify-content:center;">
                <i class="ri-user-smile-line" style="font-size:0.8rem; color:var(--gold);"></i>
            </div>
            <span style="font-size:0.8rem; color:var(--text-secondary); font-weight:600;">Anonim foydalanuvchi</span>
        </div>
    </div>
    @endforeach
</div>

@if($feedbacks->isEmpty())
    <div style="text-align:center; padding:80px 0; background:rgba(255,255,255,0.02); border-radius:30px; margin:0 20px;">
        <i class="ri-chat-history-line" style="font-size:4.5rem; color:var(--white-5); display:block; margin-bottom:20px;"></i>
        <h4 style="color:var(--text-muted);">Hozircha hech nima yo'q</h4>
        <p class="text-muted">Birinchilardan bo'lib o'z fikringizni bildiring!</p>
    </div>
@endif

<div class="pagination-wrapper" style="margin-top:60px; display:flex; justify-content:center;">
    {{ $feedbacks->links() }}
</div>

<script>
    function interactFeedback(id, type) {
        const url = `/feedback/${id}/${type}`;
        const btn = event.currentTarget;
        
        fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById(`${type}s-${id}`).textContent = data[type + 's'];
                btn.style.color = type === 'like' ? '#4ade80' : '#f87171';
                btn.classList.add('active');
                
                // Animation effect
                btn.animate([
                    { transform: 'scale(1)' },
                    { transform: 'scale(1.2)' },
                    { transform: 'scale(1)' }
                ], { duration: 300 });
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
            t.style = 'position:fixed;bottom:80px;left:50%;transform:translateX(-50%);background:var(--gold);color:white;padding:12px 24px;border-radius:50px;z-index:1000;box-shadow:0 10px 30px rgba(0,0,0,0.3);font-size:0.9rem;font-weight:700;transition:all 0.4s;';
            document.body.appendChild(t);
        }
        t.textContent = msg;
        t.style.opacity = '1';
        t.style.transform = 'translateX(-50%) translateY(-10px)';
        setTimeout(() => { t.style.opacity = '0'; t.style.transform = 'translateX(-50%) translateY(0)'; }, 3000);
    }

    // Checkbox logic
    document.querySelector('input[type="checkbox"]').addEventListener('change', function() {
        const custom = document.querySelector('.checkbox-custom');
        const icon = custom.querySelector('i');
        if (this.checked) {
            custom.style.background = 'var(--gold)';
            custom.style.borderColor = 'var(--gold)';
            icon.style.display = 'block';
        } else {
            custom.style.background = 'none';
            custom.style.borderColor = 'var(--white-20)';
            icon.style.display = 'none';
        }
    });

    // Init checkbox
    window.onload = () => {
        const cb = document.querySelector('input[type="checkbox"]');
        if (cb.checked) {
            const custom = document.querySelector('.checkbox-custom');
            custom.style.background = 'var(--gold)';
            custom.style.borderColor = 'var(--gold)';
            custom.querySelector('i').style.display = 'block';
        }
    };
</script>

<style>
    .feedback-card:hover {
        transform: translateY(-8px);
        border-color: var(--gold);
        background: rgba(212,168,67,0.05);
        box-shadow: 0 15px 35px rgba(0,0,0,0.4);
    }
    textarea:focus {
        outline: none;
        border-color: var(--gold) !important;
        background: rgba(0,0,0,0.4) !important;
        box-shadow: 0 0 15px rgba(212,168,67,0.2);
    }
    .interaction-btn.active {
        color: var(--gold) !important;
    }
</style>
@endsection
