@php
    $flashMessages = [];

    if (session('success')) {
        $flashMessages[] = [
            'type' => 'success',
            'title' => 'Berhasil!',
            'message' => session('success'),
        ];
    }

    if (session('error')) {
        $flashMessages[] = [
            'type' => 'error',
            'title' => 'Terjadi Kesalahan',
            'message' => session('error'),
        ];
    }

    if (session('warning')) {
        $flashMessages[] = [
            'type' => 'warning',
            'title' => 'Perhatian',
            'message' => session('warning'),
        ];
    }

    if (session('info')) {
        $flashMessages[] = [
            'type' => 'info',
            'title' => 'Informasi',
            'message' => session('info'),
        ];
    }
@endphp

@if(count($flashMessages))
    <div class="almara-notification-container" id="almaraNotifications">

        @foreach($flashMessages as $flash)
            <div
                class="almara-notification almara-notification-{{ $flash['type'] }}"
                data-notification
            >
                <div class="almara-notification-icon">
                    @if($flash['type'] === 'success')
                        ✓
                    @elseif($flash['type'] === 'error')
                        !
                    @elseif($flash['type'] === 'warning')
                        ⚠
                    @else
                        i
                    @endif
                </div>

                <div class="almara-notification-content">
                    <div class="almara-notification-title">
                        {{ $flash['title'] }}
                    </div>

                    <div class="almara-notification-message">
                        {{ $flash['message'] }}
                    </div>
                </div>

                <button
                    type="button"
                    class="almara-notification-close"
                    onclick="this.closest('[data-notification]').remove()"
                    aria-label="Tutup"
                >
                    ×
                </button>

                <div class="almara-notification-progress"></div>
            </div>
        @endforeach

    </div>
@endif

<style>
    .almara-notification-container {
        position: fixed;
        top: 22px;
        right: 22px;
        width: min(420px, calc(100vw - 32px));
        display: flex;
        flex-direction: column;
        gap: 12px;
        z-index: 99999;
        pointer-events: none;
    }

    .almara-notification {
        position: relative;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        min-height: 72px;
        padding: 14px 16px;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.7);
        border-radius: 16px;
        background: rgba(255,255,255,.96);
        box-shadow:
            0 14px 35px rgba(15,23,42,.14),
            0 3px 10px rgba(15,23,42,.08);
        backdrop-filter: blur(12px);
        pointer-events: auto;
        animation: almaraNotificationIn .35s cubic-bezier(.22,1,.36,1);
    }

    .almara-notification-icon {
        flex: 0 0 38px;
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 20px;
        font-weight: 800;
    }

    .almara-notification-content {
        flex: 1;
        min-width: 0;
        padding-top: 1px;
    }

    .almara-notification-title {
        font-size: 14px;
        font-weight: 800;
        line-height: 1.3;
        margin-bottom: 3px;
    }

    .almara-notification-message {
        font-size: 13px;
        line-height: 1.45;
        color: #64748b;
        word-break: break-word;
    }

    .almara-notification-close {
        flex: 0 0 auto;
        width: 28px;
        height: 28px;
        border: 0;
        background: transparent;
        color: #94a3b8;
        font-size: 22px;
        line-height: 1;
        cursor: pointer;
        border-radius: 8px;
        transition: .18s ease;
    }

    .almara-notification-close:hover {
        background: #f1f5f9;
        color: #334155;
    }

    .almara-notification-progress {
        position: absolute;
        left: 0;
        bottom: 0;
        height: 3px;
        width: 100%;
        transform-origin: left;
        animation: almaraNotificationProgress 3.5s linear forwards;
    }

    .almara-notification-success .almara-notification-icon {
        background: #dcfce7;
        color: #16a34a;
    }

    .almara-notification-success .almara-notification-progress {
        background: #22c55e;
    }

    .almara-notification-error .almara-notification-icon {
        background: #fee2e2;
        color: #dc2626;
    }

    .almara-notification-error .almara-notification-progress {
        background: #ef4444;
    }

    .almara-notification-warning .almara-notification-icon {
        background: #fef3c7;
        color: #d97706;
    }

    .almara-notification-warning .almara-notification-progress {
        background: #f59e0b;
    }

    .almara-notification-info .almara-notification-icon {
        background: #dbeafe;
        color: #2563eb;
    }

    .almara-notification-info .almara-notification-progress {
        background: #3b82f6;
    }

    .almara-notification-success .almara-notification-title {
        color: #15803d;
    }

    .almara-notification-error .almara-notification-title {
        color: #b91c1c;
    }

    .almara-notification-warning .almara-notification-title {
        color: #b45309;
    }

    .almara-notification-info .almara-notification-title {
        color: #1d4ed8;
    }

    @keyframes almaraNotificationIn {
        from {
            opacity: 0;
            transform: translateX(35px) scale(.96);
        }

        to {
            opacity: 1;
            transform: translateX(0) scale(1);
        }
    }

    @keyframes almaraNotificationProgress {
        from {
            transform: scaleX(1);
        }

        to {
            transform: scaleX(0);
        }
    }

    @media (max-width: 600px) {
        .almara-notification-container {
            top: 12px;
            right: 12px;
            width: calc(100vw - 24px);
        }

        .almara-notification {
            border-radius: 14px;
        }
    }
</style>

@if(count($flashMessages))
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-notification]').forEach(function (notification) {
        setTimeout(function () {
            if (!notification) return;

            notification.style.opacity = '0';
            notification.style.transform = 'translateX(35px) scale(.96)';
            notification.style.transition = 'all .25s ease';

            setTimeout(function () {
                notification.remove();
            }, 250);

        }, 3500);
    });
});
</script>
@endif
