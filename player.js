/*
 * Song-Player: spielt die 30-Sekunden-Vorschauen von Deezer ab.
 *
 * Jeder abspielbare Song ist ein Element mit data-track-id (Deezer-ID) sowie data-title,
 * data-artist, data-cover und data-link. Alle Songs einer Seite bilden zusammen die Playlist.
 * Die MP3 kommt über preview.php, weil Deezer die Links nur kurz gültig signiert.
 */
(function () {
    'use strict';

    var songs = Array.prototype.slice.call(document.querySelectorAll('[data-track-id]'));
    if (!songs.length) {
        return;
    }

    var ICONS = {
        prev:  '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 5h2v14H6zM20 5v14L9 12z"/></svg>',
        next:  '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 5h2v14h-2zM4 5l11 7-11 7z"/></svg>',
        play:  '<svg class="icon-play" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 4v16l13-8z"/></svg>',
        pause: '<svg class="icon-pause" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 4h4v16H6zM14 4h4v16h-4z"/></svg>',
        vol:   '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 9v6h4l5 4V5L8 9zm12.5 3a4.5 4.5 0 0 0-2.5-4v8a4.5 4.5 0 0 0 2.5-4z"/></svg>',
        close: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18.3 5.7 12 12l6.3 6.3-1.4 1.4L10.6 13.4 4.3 19.7 2.9 18.3 9.2 12 2.9 5.7 4.3 4.3l6.3 6.3 6.3-6.3z"/></svg>'
    };

    // ---- Leiste aufbauen (Texte aus der Seite nur per textContent, nie als HTML)
    var bar = document.createElement('div');
    bar.className = 'player';
    bar.hidden = true;
    bar.setAttribute('role', 'region');
    bar.setAttribute('aria-label', 'Song-Player');
    bar.innerHTML =
        '<div class="player-track">' +
            '<img class="player-cover" alt="">' +
            '<div class="player-text">' +
                '<div class="player-title"></div>' +
                '<div class="player-artist"></div>' +
                '<div class="player-message" aria-live="polite"></div>' +
            '</div>' +
        '</div>' +
        '<div class="player-center">' +
            '<div class="player-buttons">' +
                '<button type="button" class="player-btn prev" aria-label="Vorheriger Song">' + ICONS.prev + '</button>' +
                '<button type="button" class="player-btn play" aria-label="Abspielen">' + ICONS.play + ICONS.pause + '</button>' +
                '<button type="button" class="player-btn next" aria-label="Nächster Song">' + ICONS.next + '</button>' +
            '</div>' +
            '<div class="player-progress">' +
                '<span class="player-time">0:00</span>' +
                '<input type="range" class="player-range player-seek" min="0" max="30" step="0.1" value="0" aria-label="Position im Song">' +
                '<span class="player-duration">0:30</span>' +
            '</div>' +
        '</div>' +
        '<div class="player-extra">' +
            '<label class="player-volume" title="Lautstärke">' + ICONS.vol +
                '<input type="range" class="player-range player-vol" min="0" max="1" step="0.01" value="0.7" aria-label="Lautstärke">' +
            '</label>' +
            '<a class="player-link" target="_blank" rel="noopener">Ganzer Song auf Deezer</a>' +
            '<button type="button" class="player-btn close" aria-label="Player schließen">' + ICONS.close + '</button>' +
        '</div>';
    document.body.appendChild(bar);

    var $ = function (sel) { return bar.querySelector(sel); };
    var cover = $('.player-cover'), title = $('.player-title'), artist = $('.player-artist');
    var message = $('.player-message'), playBtn = $('.play'), seek = $('.player-seek');
    var timeEl = $('.player-time'), durationEl = $('.player-duration'), vol = $('.player-vol');
    var link = $('.player-link');

    var audio = new Audio();
    audio.preload = 'none';
    var current = -1;
    var seeking = false;

    // ---- Lautstärke merken (nur Komfort; ohne localStorage gilt der Standardwert)
    try {
        var saved = parseFloat(localStorage.getItem('playerVolume'));
        if (saved >= 0 && saved <= 1) { vol.value = saved; }
    } catch (e) { /* privates Fenster o. Ä. */ }
    audio.volume = parseFloat(vol.value);

    function setFill(range) {
        var min = parseFloat(range.min), max = parseFloat(range.max) || 1;
        range.style.setProperty('--fill', ((parseFloat(range.value) - min) / (max - min) * 100) + '%');
    }
    setFill(vol);
    setFill(seek);

    function formatTime(seconds) {
        seconds = Math.max(0, Math.floor(seconds || 0));
        return Math.floor(seconds / 60) + ':' + String(seconds % 60).padStart(2, '0');
    }

    function setState(playing) {
        bar.classList.toggle('is-playing', playing);
        playBtn.setAttribute('aria-label', playing ? 'Pause' : 'Abspielen');
        songs.forEach(function (song, i) {
            song.classList.toggle('is-current', i === current);
            song.classList.toggle('is-playing', i === current && playing);
            song.setAttribute('aria-pressed', i === current && playing ? 'true' : 'false');
        });
    }

    function open() {
        if (!bar.classList.contains('is-open')) {
            bar.hidden = false;
            document.body.classList.add('player-open');
            requestAnimationFrame(function () { bar.classList.add('is-open'); });
        }
    }

    function load(index) {
        current = (index + songs.length) % songs.length;
        var data = songs[current].dataset;

        title.textContent = data.title || '';
        artist.textContent = data.artist || '';
        message.textContent = '';
        cover.src = data.cover || 'image/logo.png';
        if (data.link) {
            link.href = data.link;
            link.hidden = false;
        } else {
            link.hidden = true;
        }

        audio.src = 'preview.php?id=' + encodeURIComponent(data.trackId);
        seek.value = 0;
        setFill(seek);
        timeEl.textContent = '0:00';
        durationEl.textContent = '0:30';
        open();
        play();
    }

    function play() {
        bar.classList.add('is-loading');
        var promise = audio.play();
        if (promise && promise.catch) {
            promise.catch(function (err) {
                if (err && err.name === 'NotAllowedError') {
                    setState(false); // Browser blockiert Autoplay: Nutzer drückt selbst auf Play
                }
            });
        }
    }

    function toggle() {
        if (current < 0) { load(0); return; }
        if (audio.paused) { play(); } else { audio.pause(); }
    }

    // ---- Klicks in der Song-Liste
    songs.forEach(function (song, i) {
        song.addEventListener('click', function (event) {
            event.preventDefault();
            if (i === current) { toggle(); } else { load(i); }
        });
    });

    playBtn.addEventListener('click', toggle);
    $('.next').addEventListener('click', function () { load(current + 1); });
    $('.prev').addEventListener('click', function () {
        // Wie bei Spotify: erst an den Anfang, beim zweiten Druck ein Song zurück
        if (audio.currentTime > 3) { audio.currentTime = 0; } else { load(current - 1); }
    });
    $('.close').addEventListener('click', function () {
        audio.pause();
        bar.classList.remove('is-open');
        document.body.classList.remove('player-open');
        setTimeout(function () { if (!bar.classList.contains('is-open')) { bar.hidden = true; } }, 350);
    });

    seek.addEventListener('input', function () {
        seeking = true;
        setFill(seek);
        timeEl.textContent = formatTime(seek.value);
    });
    seek.addEventListener('change', function () {
        seeking = false;
        if (isFinite(audio.duration)) { audio.currentTime = parseFloat(seek.value); }
    });

    vol.addEventListener('input', function () {
        audio.volume = parseFloat(vol.value);
        setFill(vol);
        try { localStorage.setItem('playerVolume', vol.value); } catch (e) { /* egal */ }
    });

    // ---- Audio-Ereignisse
    audio.addEventListener('playing', function () { bar.classList.remove('is-loading'); setState(true); });
    audio.addEventListener('pause', function () { bar.classList.remove('is-loading'); setState(false); });
    audio.addEventListener('loadedmetadata', function () {
        seek.max = audio.duration;
        durationEl.textContent = formatTime(audio.duration);
    });
    audio.addEventListener('timeupdate', function () {
        if (seeking) { return; }
        seek.value = audio.currentTime;
        setFill(seek);
        timeEl.textContent = formatTime(audio.currentTime);
    });
    audio.addEventListener('ended', function () { load(current + 1); });
    audio.addEventListener('error', function () {
        bar.classList.remove('is-loading');
        setState(false);
        message.textContent = 'Vorschau gerade nicht verfügbar.';
    });

    // ---- Tastatur: Leertaste = Play/Pause (nicht in Formularfeldern oder auf Knöpfen/Links)
    document.addEventListener('keydown', function (event) {
        if (current < 0 || event.code !== 'Space' || event.ctrlKey || event.altKey || event.metaKey) { return; }
        var tag = (event.target.tagName || '').toLowerCase();
        if (tag === 'input' || tag === 'textarea' || tag === 'select' || tag === 'button' || tag === 'a' || event.target.isContentEditable) { return; }
        event.preventDefault();
        toggle();
    });
})();
