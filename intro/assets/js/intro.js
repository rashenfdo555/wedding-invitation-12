/**
 * INTRO VIDEO CONTROLLER - FIXED AUDIO
 * Handles video playback with audio synchronization
 */
document.addEventListener('DOMContentLoaded', function() {
    const introOverlay = document.getElementById('introVideoOverlay');
    const introVideo = document.getElementById('introVideo');
    const playBtn = document.getElementById('videoPlayBtn');
    const overlayContent = document.getElementById('videoOverlayContent');
    const loadingIndicator = document.getElementById('loadingIndicator');
    
    const config = window.introConfig || {};
    const invitationUrl = config.invitation_url || '../invitation/index.php';
    
    let isVideoPlaying = false;
    let isTransitioning = false;
    let isVideoEnded = false;
    let introAudio = null;
    let playbackStarted = false;
    let audioLoaded = false;

    /**
     * Initialize audio with better error handling
     */
    function initAudio() {
        try {
            if (!config.audio_path) {
                console.warn('No audio path configured');
                return;
            }
            
            console.log('Loading audio from:', config.audio_path);
            introAudio = new Audio(config.audio_path);
            introAudio.loop = false;
            introAudio.volume = 0.85;
            introAudio.preload = 'auto';
            
            // Check if audio loads properly
            introAudio.addEventListener('canplaythrough', function() {
                console.log('Intro audio loaded successfully');
                audioLoaded = true;
            });
            
            introAudio.addEventListener('error', function(e) {
                console.warn('Intro audio error:', e);
                // Try alternative path if available
                if (config.audio_path.includes('../uploads/')) {
                    // If using uploads path, also try the old path
                    console.log('Trying alternative audio path...');
                }
            });
            
            // Log audio state
            console.log('Audio initialized:', {
                src: config.audio_path,
                volume: introAudio.volume,
                loop: introAudio.loop
            });
            
        } catch (error) {
            console.warn('Audio initialization error:', error);
        }
    }

    /**
     * Handle video ending - transition to invitation
     */
    function handleVideoEnd() {
        if (isTransitioning || isVideoEnded) return;
        isTransitioning = true;
        isVideoEnded = true;
        
        console.log('Video ended - transitioning to invitation');
        
        // Stop intro audio
        if (introAudio) {
            try {
                introAudio.pause();
                introAudio.currentTime = 0;
            } catch(e) {
                console.warn('Error stopping audio:', e);
            }
        }
        
        // Show loading indicator
        if (loadingIndicator) {
            loadingIndicator.classList.remove('state-hidden');
        }
        
        // Transition out
        setTimeout(() => {
            introOverlay.classList.add('dismissed');
            
            // After transition, redirect to invitation
            setTimeout(() => {
                window.location.href = invitationUrl;
            }, config.transition_delay || 800);
            
        }, 300);
    }

    /**
     * Start video playback with audio
     */
    function startVideo() {
        if (isVideoPlaying || playbackStarted) return;
        playbackStarted = true;
        isVideoPlaying = true;
        
        console.log('Starting video playback');
        
        // Hide overlay content
        overlayContent.classList.add('hidden');
        
        // Play the video
        introVideo.play().then(() => {
            console.log('Video playing');
            introVideo.classList.add('playing-state');
            
            // Start intro audio with better handling
            if (introAudio) {
                try {
                    // Reset audio
                    introAudio.currentTime = 0;
                    
                    // Play with promise handling
                    const audioPromise = introAudio.play();
                    if (audioPromise !== undefined) {
                        audioPromise.then(() => {
                            console.log('Intro audio playing successfully');
                            audioLoaded = true;
                        }).catch(error => {
                            console.warn('Intro audio autoplay blocked:', error);
                            // Try to play on user interaction
                            document.addEventListener('click', function playAudio() {
                                if (introAudio && introAudio.paused) {
                                    introAudio.play().catch(e => console.warn('Audio play failed:', e));
                                }
                                document.removeEventListener('click', playAudio);
                            }, { once: true });
                        });
                    }
                } catch(e) {
                    console.warn('Audio play error:', e);
                }
            }
            
        }).catch(error => {
            console.warn('Video playback failed:', error);
            // If video fails, still try to play audio and redirect
            if (introAudio) {
                introAudio.play().catch(e => console.warn('Audio play failed:', e));
            }
            // Fallback: redirect after delay
            setTimeout(() => {
                handleVideoEnd();
            }, 3000);
        });
    }

    /**
     * Setup video - preload but don't play
     */
    function setupVideo() {
        // Load video
        introVideo.load();
        introVideo.pause();
        introVideo.currentTime = 0;
        overlayContent.classList.remove('hidden');
        
        // Log video info
        console.log('Video setup complete:', {
            src: introVideo.querySelector('source')?.src,
            readyState: introVideo.readyState
        });
    }

    /**
     * Event Listeners
     */
    // Play button click
    if (playBtn) {
        playBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            startVideo();
        });
    }
    
    // Click on video container
    const videoContainer = document.querySelector('.video-container');
    if (videoContainer) {
        videoContainer.addEventListener('click', function(e) {
            if (!isVideoPlaying && !playbackStarted) {
                startVideo();
            }
        });
    }
    
    // Video ended event
    if (introVideo) {
        introVideo.addEventListener('ended', function() {
            console.log('Video ended event fired');
            handleVideoEnd();
        });
        
        introVideo.addEventListener('error', function(e) {
            console.warn('Video error:', e);
            // Try to continue anyway
            setTimeout(() => {
                handleVideoEnd();
            }, 2000);
        });
        
        introVideo.addEventListener('timeupdate', function() {
            if (introVideo.currentTime > 0 && !isVideoPlaying) {
                isVideoPlaying = true;
            }
        });
        
        // Log video loading
        introVideo.addEventListener('loadeddata', function() {
            console.log('Video data loaded');
        });
    }

    /**
     * Handle visibility change
     */
    document.addEventListener('visibilitychange', function() {
        if (document.hidden && isVideoPlaying && introVideo && !introVideo.ended) {
            introVideo.pause();
            if (introAudio && !introAudio.paused) {
                introAudio.pause();
            }
        } else if (!document.hidden && isVideoPlaying && introVideo && !introVideo.ended && introVideo.paused) {
            introVideo.play().catch(() => {});
            if (introAudio && introAudio.paused && audioLoaded) {
                introAudio.play().catch(() => {});
            }
        }
    });

    /**
     * Initialize
     */
    function init() {
        console.log('Initializing intro with config:', config);
        initAudio();
        setupVideo();
        
        // Fallback: if no interaction within 10s, ensure play button is visible
        setTimeout(() => {
            if (!isVideoPlaying && !playbackStarted) {
                overlayContent.classList.remove('hidden');
                if (playBtn) {
                    playBtn.style.opacity = '1';
                    playBtn.style.pointerEvents = 'auto';
                }
            }
        }, 10000);
    }

    init();
});