<dialog id="welcome-screen" class="welcome-screen" aria-label="Selamat datang di zeAUTORENT" aria-describedby="welcome-summary">
    <p id="welcome-summary" class="sr-only">Sempurnakan perjalanan Anda bersama keluarga. Sambutan selesai otomatis dalam 5 detik. Tekan Escape untuk langsung masuk.</p>
    <div class="welcome-atmosphere" aria-hidden="true">
        <div class="welcome-orbit welcome-orbit-one"></div>
        <div class="welcome-orbit welcome-orbit-two"></div>
        <div class="welcome-light"></div>
    </div>
    <div class="welcome-content">
        <section class="welcome-stage" data-welcome-stage="1">
            <h2 class="welcome-greeting" aria-label="Selamat Datang">
                <span class="welcome-word-mask"><span class="welcome-greeting-word">Selamat</span></span>
                <span class="welcome-word-mask"><span class="welcome-greeting-word">Datang</span></span>
            </h2>
            <span class="welcome-stroke" aria-hidden="true"></span>
        </section>
        <section class="welcome-stage" data-welcome-stage="2" hidden aria-hidden="true">
            <h2 class="welcome-wordmark" aria-label="zeAUTORENT">
                <span class="welcome-brand-part welcome-brand-ze" aria-hidden="true">ze</span><span class="welcome-brand-part" aria-hidden="true">AUTO</span><span class="welcome-brand-part welcome-brand-rent" aria-hidden="true">RENT</span>
            </h2>
            <p class="welcome-description">Sempurnakan perjalanan Anda<br> bersama keluarga.</p>
        </section>
        <section class="welcome-stage" data-welcome-stage="3" hidden aria-hidden="true">
            <button id="welcome-start" class="welcome-start" type="button">
                <span class="welcome-word-mask"><span class="welcome-start-text">Ayo Mulai</span></span>
                <span class="welcome-start-arrow" aria-hidden="true"><span class="material-symbols-outlined">arrow_forward</span></span>
            </button>
        </section>
    </div>
    <div class="welcome-footer" aria-hidden="true">
        <div class="welcome-steps"><span></span><span></span><span></span></div>
        <div class="welcome-progress"><span></span></div>
    </div>
</dialog>
