<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Musik Player</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(45deg,rgb(49, 0, 0), rgb(12, 0, 0));
            color: #fff;
            display: flex;
            flex-direction: column;
            height: 100vh;
            transition: 0.3s;
        }
        #artist-buttons {
            display: flex;
            justify-content: center;
            padding: 10px;
            background-color: rgba(30, 30, 30, 0.8);
            border-bottom: 1px solid #333;
        }
        .artist-btn {
            background-color: #1e1e1e;
            border: none;
            color: #fff;
            padding: 10px 20px;
            margin: 0 5px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .artist-btn:hover {
            background-color: #333;
        }
        #playlist {
            width: 300px;
            background-color: #1e1e1e;
            border-radius: 10px;
            padding: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            margin: 20px;
            overflow-y: auto;
        }
        .song {
            padding: 10px;
            border-bottom: 1px solid #333;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .song:hover {
            background-color: #333;
        }
        .song:last-child {
            border-bottom: none;
        }
        #info {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        #thumbnail {
            width: 300px;
            height: 300px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        }
        #controls {
            display: flex;
            align-items: center;
            margin-top: 20px;
            width: 100%;
            max-width: 600px;
        }
        #playPauseBtn {
            background-color: transparent;
            border: none;
            color: white;
            padding: 10px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 20px;
            transition: background-color 0.3s;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
        }
        #playPauseBtn:hover {
            background-color: transparent;
        }
        #progress {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        #progressBar {
            flex: 1;
            height: 10px;
            background-color: #333;
            border-radius: 5px;
            position: relative;
            cursor: pointer;
        }
        #progressBarFilled {
            height: 100%;
            background-color: #ff0000;
            border-radius: 5px;
            width: 0%;
        }
        #currentTime, #duration {
            font-size: 14px;
            color: #aaa;
            min-width: 40px;
            text-align: center;
        }
        #volume {
            width: 100px;
            margin-top: 10px;
            accent-color: #ff0000;
        }
        #soundcloudPlayer {
            display: none;
        }
    </style>
</head>
<body>
    <div id="artist-buttons">
        <button class="artist-btn" data-artist="Bad Bunny">Bad Bunny</button>
        <button class="artist-btn" data-artist="The Weeknd">The Weeknd</button>
        <button class="artist-btn" data-artist="Taylor Swift">Taylor Swift</button>
        <button class="artist-btn" data-artist="Bruno Mars">Bruno Mars</button>
        <button class="artist-btn" data-artist="Billie Eilish">Billie Eilish</button>
        <button class="artist-btn" data-artist="Kendrick Lamar">Kendrick Lamar</button>
        <button class="artist-btn" data-artist="SZA">SZA</button>
    </div>

    <div id="playlist">
    </div>

    <div id="info">
        <img id="thumbnail" src="" alt="Thumbnail" style="display: none;">
        <h2 id="title">Titel</h2>
        <p id="artist">Künstler</p>
        
        <div id="controls">
            <button id="playPauseBtn">▶</button>
            <div id="progress">
                <span id="currentTime">0:00</span>
                <div id="progressBar">
                    <div id="progressBarFilled"></div>
                </div>
                <span id="duration">0:00</span>
            </div>
            <input type="range" id="volume" min="0" max="6000" value="500">
        </div>
    </div>

    <iframe id="soundcloudPlayer" width="100%" height="166" scrolling="no" frameborder="no" allow="autoplay" style="display: none;"></iframe>

    <script src="https://w.soundcloud.com/player/api.js"></script>
    <script>
        const artistButtons = document.getElementById('artist-buttons');
        const playlistElement = document.getElementById('playlist');
        const soundcloudPlayer = document.getElementById('soundcloudPlayer');
        const playPauseBtn = document.getElementById('playPauseBtn');
        const titleElement = document.getElementById('title');
        const artistElement = document.getElementById('artist');
        const thumbnailElement = document.getElementById('thumbnail');
        const currentTimeElement = document.getElementById('currentTime');
        const durationElement = document.getElementById('duration');
        const progressBarFilled = document.getElementById('progressBarFilled');
        const volumeSlider = document.getElementById('volume');

        let isPlaying = false;
        let widget;

        const playlists = {
            "Bad Bunny": [
                { title: "Safaera", url: "https://soundcloud.com/badbunny15/bad-bunny-jowell-randy-nengo?utm_source=clipboard&utm_medium=text&utm_campaign=social_sharing" },
                { title: "Yonaguni", url: "https://soundcloud.com/badbunny15/bad-bunny-yonaguni?utm_source=clipboard&utm_medium=text&utm_campaign=social_sharing" },
                { title: "WELTiTA", url: "https://soundcloud.com/badbunny15/weltita?utm_source=clipboard&utm_medium=text&utm_campaign=social_sharing" },
                { title: "CAFé CON RON", url: "https://soundcloud.com/badbunny15/cafe-con-ron?utm_source=clipboard&utm_medium=text&utm_campaign=social_sharing" }
            ],
            "The Weeknd": [
                { title: "Blinding Lights", url: "https://soundcloud.com/theweeknd/blinding-lights" },
                { title: "Starboy", url: "https://soundcloud.com/theweeknd/starboy" },
                { title: "Is There Someone Else?", url: "https://soundcloud.com/theweeknd/the-weeknd-is-there-someone?in=theweeknd/sets/dawn-fm-3&utm_source=clipboard&utm_medium=text&utm_campaign=social_sharing" },
                { title: "Out of Time", url: "https://soundcloud.com/theweeknd/starboy" }
            ],
            "Taylor Swift": [
                { title: "Wildest Dreams", url: "https://soundcloud.com/taylorswiftofficial/wildest-dreams-taylors-version?in=taylorswiftofficial/sets/1989-taylors-version-deluxe&utm_source=clipboard&utm_medium=text&utm_campaign=social_sharing" },
                { title: "Bad Blood", url: "https://soundcloud.com/taylorswiftofficial/bad-blood-taylors-version-feat?in=taylorswiftofficial/sets/1989-taylors-version-deluxe&utm_source=clipboard&utm_medium=text&utm_campaign=social_sharing" },
                { title: "Slut!", url: "https://soundcloud.com/taylorswiftofficial/slut-taylors-version-from-the?in=taylorswiftofficial/sets/1989-taylors-version-deluxe&utm_source=clipboard&utm_medium=text&utm_campaign=social_sharing" },
                { title: "Is It Over Now?", url: "https://soundcloud.com/theweeknd/starboy" }
            ],
            "Bruno Mars": [
                { title: "Skate", url: "https://soundcloud.com/brunomars/bruno-mars-anderson-paak-3?utm_source=clipboard&utm_medium=text&utm_campaign=social_sharing" },
                { title: "Leave The Door Open", url: "https://soundcloud.com/brunomars/bruno-mars-anderson-paak-silk?utm_source=clipboard&utm_medium=text&utm_campaign=social_sharing" },
                { title: "Love's Train", url: "https://soundcloud.com/brunomars/bruno-mars-anderson-308457595?utm_source=clipboard&utm_medium=text&utm_campaign=social_sharing" },
                { title: "777", url: "https://soundcloud.com/brunomars/bruno-mars-anderson-739815975?utm_source=clipboard&utm_medium=text&utm_campaign=social_sharing" }
            ],
            "Billie Eilish": [
                { title: "bad guy", url: "https://soundcloud.com/billieeilish/bad-guy" },
                { title: "Ocean Eyes", url: "https://soundcloud.com/billieeilish/ocean-eyes" },
                { title: "BIRDS OF A FEATHER", url: "https://soundcloud.com/billieeilish/birds-of-a-feather?in=billieeilish/sets/hit-me-hard-and-soft-3&utm_source=clipboard&utm_medium=text&utm_campaign=social_sharing" },
                { title: "WILDFLOWER", url: "https://soundcloud.com/billieeilish/wildflower?in=billieeilish/sets/hit-me-hard-and-soft-3&utm_source=clipboard&utm_medium=text&utm_campaign=social_sharing" }
            ],
            "Kendrick Lamar": [
                { title: "wacced out murals", url: "https://soundcloud.com/kendrick-lamar-music/wacced-out-murals?in=kendrick-lamar-music/sets/gnx&utm_source=clipboard&utm_medium=text&utm_campaign=social_sharing" },
                { title: "squabble up", url: "https://soundcloud.com/kendrick-lamar-music/squabble-up?in=kendrick-lamar-music/sets/gnx&utm_source=clipboard&utm_medium=text&utm_campaign=social_sharing" },
                { title: "tv off", url: "https://soundcloud.com/kendrick-lamar-music/tv-off?in=kendrick-lamar-music/sets/gnx&utm_source=clipboard&utm_medium=text&utm_campaign=social_sharing" },
                { title: "luther", url: "https://soundcloud.com/kendrick-lamar-music/luther?in=kendrick-lamar-music/sets/gnx&utm_source=clipboard&utm_medium=text&utm_campaign=social_sharing" }
            ],
            "SZA": [
                { title: "God's Plan", url: "https://soundcloud.com/szababy2/kill-bill?in=szababy2/sets/sos-deluxe-lana-1&utm_source=clipboard&utm_medium=text&utm_campaign=social_sharing" },
                { title: "In My Feelings", url: "https://soundcloud.com/szababy2/snooze?in=szababy2/sets/sos-deluxe-lana-1&utm_source=clipboard&utm_medium=text&utm_campaign=social_sharing" },
                { title: "Low", url: "https://soundcloud.com/szababy2/low?in=szababy2/sets/sos-deluxe-lana-3&utm_source=clipboard&utm_medium=text&utm_campaign=social_sharing" },
                { title: "Open Arms", url: "https://soundcloud.com/szababy2/open-arms-feat-travis-scott?in=szababy2/sets/sos-deluxe-lana-3&utm_source=clipboard&utm_medium=text&utm_campaign=social_sharing" }
            ]
        };

        function loadPlaylist(artist) {
            const playlist = playlists[artist];
            playlistElement.innerHTML = ''; 
            playlist.forEach(song => {
                const songElement = document.createElement('div');
                songElement.className = 'song';
                songElement.textContent = song.title;
                songElement.dataset.trackUrl = song.url;
                playlistElement.appendChild(songElement);
            });
        }

        soundcloudPlayer.onload = () => {
            widget = SC.Widget(soundcloudPlayer);

            widget.bind(SC.Widget.Events.READY, () => {
                widget.setVolume(0.3); 
                volumeSlider.value = 30; 
                widget.getDuration((duration) => {
                    durationElement.innerText = formatTime(duration / 1000);
                });
            });

            widget.bind(SC.Widget.Events.PLAY_PROGRESS, (progress) => {
                const currentTime = progress.currentPosition / 1000;
                const duration = progress.duration / 1000;
                const progressPercent = (currentTime / duration) * 100;

                progressBarFilled.style.width = `${progressPercent}%`;
                currentTimeElement.innerText = formatTime(currentTime);
            });

            widget.bind(SC.Widget.Events.FINISH, () => {
                playPauseBtn.innerText = '▶';
                isPlaying = false;
            });
        };

        function loadTrack(trackUrl) {
            soundcloudPlayer.src = `https://w.soundcloud.com/player/?url=${encodeURIComponent(trackUrl)}&auto_play=false`;
            updateTrackInfo(trackUrl);
            playPauseBtn.innerText = '▶'; 
        }

        function updateTrackInfo(trackUrl) {
            fetch(`https://soundcloud.com/oembed?url=${encodeURIComponent(trackUrl)}&format=json`)
                .then(response => response.json())
                .then(data => {
                    titleElement.innerText = data.title;
                    artistElement.innerText = data.author_name;
                    thumbnailElement.src = data.thumbnail_url;
                    thumbnailElement.style.display = 'block';
                })
                .catch(error => console.error("Fehler beim Abrufen der Track-Infos:", error));
        }

        playPauseBtn.addEventListener('click', () => {
            if (isPlaying) {
                widget.pause();
                playPauseBtn.innerText = '▶';
            } else {
                widget.play();
                playPauseBtn.innerText = '⏸';
            }
            isPlaying = !isPlaying;
        });

        volumeSlider.addEventListener('input', () => {
            const volume = volumeSlider.value / 100; 
            widget.setVolume(volume);
        });

        playlistElement.addEventListener('click', (e) => {
            if (e.target.classList.contains('song')) {
                const trackUrl = e.target.dataset.trackUrl;
                loadTrack(trackUrl);
            }
        });

        artistButtons.addEventListener('click', (e) => {
            if (e.target.classList.contains('artist-btn')) {
                const artist = e.target.dataset.artist;
                loadPlaylist(artist);
        }})

        progressBar.addEventListener('click', (event) => {
            const progressBarWidth = progressBar.clientWidth;
            const clickPosition = event.offsetX;
            const seekPercentage = clickPosition / progressBarWidth;

            widget.getDuration((duration) => {
                const seekTime = duration * seekPercentage;
                widget.seekTo(seekTime);
            });
        });

        function formatTime(time) {
            const minutes = Math.floor(time / 60);
            const seconds = Math.floor(time % 60);
            return `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
        }

        loadPlaylist("Kendrick Lamar");
    </script>
</body>
</html>