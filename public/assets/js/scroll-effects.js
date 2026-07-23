document.addEventListener('DOMContentLoaded', function () {
    // Scroll Reveal: elemen muncul pelan-pelan saat masuk viewport.
    // Stagger-nya (kartu muncul satu per satu) diatur lewat transition-delay di CSS (.card-grid .reveal:nth-child).
    var revealTargets = document.querySelectorAll(
        '.card, .gallery-item, .detail-info, .section-title, .section-subtitle'
    );
    revealTargets.forEach(function (el) { el.classList.add('reveal'); });

    if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -50px 0px' });

        revealTargets.forEach(function (el) { observer.observe(el); });
    } else {
        // Browser lama tanpa IntersectionObserver: langsung tampilkan semua.
        revealTargets.forEach(function (el) { el.classList.add('is-visible'); });
    }

    // Parallax: foto latar hero bergerak lebih lambat dari scroll.
    var heroBg = document.querySelector('.hero-bg');
    if (heroBg) {
        var ticking = false;
        window.addEventListener('scroll', function () {
            if (!ticking) {
                requestAnimationFrame(function () {
                    heroBg.style.transform = 'translateY(' + (window.scrollY * 0.35) + 'px)';
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });
    }
});
