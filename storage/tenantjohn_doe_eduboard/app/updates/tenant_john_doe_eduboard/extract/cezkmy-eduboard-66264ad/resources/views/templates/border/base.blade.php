<div class="border-template-container {{ $borderClass ?? '' }}">
    <div class="border-content-wrapper">
        {{ $slot }}
    </div>
</div>

<style>
    .border-template-container {
        width: 100%;
        height: 100%;
        min-height: 120px;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
        transition: all 0.3s ease;
    }
    
    .border-content-wrapper {
        padding: 10px;
        text-align: center;
        color: #6c757d;
        font-size: 0.8rem;
    }
</style>
