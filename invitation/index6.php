<?php
// invitation/index1.php - Classic Gold Theme
// This file is loaded by index.php router
// All paths use $invitation_data for dynamic content

// $invitation_data is passed from index.php
$inv = $invitation_data ?? null;
if (!$inv) {
    die('Invitation data not available.');
}

// Get user info for folder paths
$user = getUserAdminById($inv['user_admin_id']);
$username = $user['username'] ?? 'default';
$theme_id = $user['theme_id'] ?? 1;

// Get schedule items
$schedule_items = getScheduleItems($inv['id']);

// Get features
$features = getUserFeatures($inv['user_admin_id']);
$has_gallery = $features['has_gallery'] ?? 0;
$has_table_finder = $features['has_table_finder'] ?? 0;

// Get gallery images if enabled
$gallery_images = $has_gallery ? getGalleryImages($inv['id']) : [];

// Get table entries if enabled
$table_entries = $has_table_finder ? getTableFinderEntries($inv['id']) : [];

// Build config for frontend
$config = [
    'bride_name' => $inv['bride_name'],
    'groom_name' => $inv['groom_name'],
    'couple_headline' => $inv['couple_headline'],
    'wedding_date' => $inv['wedding_date'],
    'month' => $inv['month'],
    'day' => $inv['day'],
    'year' => $inv['year'],
    'countdown_target' => $inv['countdown_target'],
    'sub_headline' => $inv['sub_headline'],
    'groom_full_name' => $inv['groom_name'],
    'bride_full_name' => $inv['bride_name'],
    'venue_title' => $inv['venue_title'],
    'venue_location' => $inv['venue_location'],
    'venue_address' => $inv['venue_address'],
    'google_maps_url' => $inv['google_maps_url'],
    'event_time' => $inv['event_time'],
    'whatsapp_phone' => $inv['whatsapp_phone'],
    'primary_color' => $inv['primary_color'] ?? '#bc9c6c',
    'secondary_color' => $inv['secondary_color'] ?? '#f4efe6',
    'text_color' => $inv['text_color'] ?? '#443838',
    'bg_main_color' => $inv['bg_main_color'] ?? '#fbf9f6',
    'bg_card_color' => $inv['bg_card_color'] ?? '#ffffff',
    'text_muted_color' => $inv['text_muted_color'] ?? '#6e6e6e',
    'text_light_gray' => $inv['text_light_gray'] ?? '#9e9e9e',
    'border_light_color' => $inv['border_light_color'] ?? '#eae5dd',
    'map_bg_color' => $inv['map_bg_color'] ?? '#e5e5e3',
    'hero_image' => $inv['hero_image'],
    'bride_image' => $inv['bride_image'],
    'groom_image' => $inv['groom_image'],
    'bg_audio' => '../uploads/audio/' . ($inv['bg_audio'] ?? 'intro-music.mp3'),
    'show_profiles' => $inv['show_profiles'] ?? 1,
    'schedule' => $schedule_items,
    'has_gallery' => $has_gallery,
    'has_table_finder' => $has_table_finder,
    'gallery_images' => $gallery_images,
    'table_entries' => $table_entries,
    'username' => $username
];

// Helper function to get media path
function getMediaPath($filename, $type = 'images', $username = null) {
    if (empty($filename)) return '';
    // If already has path, return as is
    if (strpos($filename, '/') !== false) {
        return '../uploads/' . $type . '/' . $filename;
    }
    // Use username from config
    global $config;
    $user = $config['username'] ?? 'default';
    return '../uploads/' . $type . '/' . $user . '/' . $filename;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo htmlspecialchars($config['couple_headline']); ?> - Wedding Invitation</title>
    <link href="https://fonts.googleapis.com/css2?family=Alex+Brush&family=Montserrat:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@400;600;700&family=Great+Vibes&family=Marcellus&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        /* ==========================================================================
           CLASSIC GOLD THEME + FALLING PETALS
           ========================================================================== */
        :root {
            --bg-main: <?php echo htmlspecialchars($config['bg_main_color']); ?>;
            --bg-card-white: <?php echo htmlspecialchars($config['bg_card_color']); ?>;
            --text-primary: <?php echo htmlspecialchars($config['text_color']); ?>;
            --text-muted: <?php echo htmlspecialchars($config['text_muted_color']); ?>;
            --text-light-gray: <?php echo htmlspecialchars($config['text_light_gray']); ?>;
            --accent-gold: <?php echo htmlspecialchars($config['primary_color']); ?>;
            --accent-gold-light: <?php echo htmlspecialchars($config['secondary_color']); ?>;
            --border-light: <?php echo htmlspecialchars($config['border_light_color']); ?>;
            --map-bg: <?php echo htmlspecialchars($config['map_bg_color']); ?>;
            --max-content-width: 1100px;
            --primary-color: var(--accent-gold);
            --secondary-color: var(--accent-gold-light);
        }

        /* ===== FALLING PETALS ===== */
        .petals-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            pointer-events: none;
            z-index: 9999;
            overflow: hidden;
        }

        .petal {
            position: absolute;
            top: -30px;
            font-size: 18px;
            opacity: 0.6;
            animation: fall linear infinite;
            transform: rotate(0deg);
            will-change: transform;
        }

        @keyframes fall {
            0% {
                transform: translateY(0) rotate(0deg) scale(0.6);
                opacity: 0.7;
            }
            100% {
                transform: translateY(110vh) rotate(720deg) scale(1.3);
                opacity: 0.1;
            }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f3f0ea;
            color: var(--text-primary);
            overflow-x: hidden;
            min-height: 100vh;
        }

            .font-serif { font-family: 'Marcellus', serif; }
    .font-script { font-family: 'Great Vibes', cursive; }
    .font-royal { font-family: 'Crimson Pro', serif; }

        .invitation-wrapper {
            width: 100%;
            margin: 0 auto;
            background: var(--bg-main);
            min-height: 100vh;
            position: relative;
            box-shadow: 0 0 40px rgba(0,0,0,0.03);
        }

        /* ===== HERO SECTION ===== */
        /* ===== HERO SECTION ===== */
/* ===== HERO SECTION ===== */
.hero-section {
    position: relative;
    height: 50vh;
    width: 100%;
    background-image: url('<?php echo getMediaPath($config['hero_image'], 'images', $config['username']); ?>');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    overflow: hidden;
}

/* Base Mask Setup */
.brush-mask {
    position: absolute;
    bottom: -1px;
    left: 0;
    width: 200%;
    height: 80px;
    fill: var(--bg-main, #ffffff);
    z-index: 2;
    pointer-events: none;
    
    /* Smooth left-to-right infinite loop */
    animation: waveMove 12s linear infinite;
    will-change: transform;
}

@keyframes waveMove {
    0% {
        transform: translate3d(0, 0, 0);
    }
    100% {
        /* Move exactly half (1 full SVG width) for a seamless loop */
        transform: translate3d(-50%, 0, 0);
    }
}


/* ===== DETAILS SECTION (BELOW HERO) ===== */
.details-section {
    background-color: var(--bg-main);/* Warm elegant off-white background */
    padding: 20px 24px 60px;
    text-align: center;
    display: flex;
    justify-content: center;
}

/* Main Card Flutter (Floating + Tilting) */
@keyframes windFlutter {
    0% {
        transform: translateY(0px) rotate(0deg) rotateY(0deg) skewX(0deg);
    }
    25% {
        transform: translateY(-8px) rotate(1.5deg) rotateY(6deg) skewX(-1deg);
    }
    50% {
        transform: translateY(-3px) rotate(-1deg) rotateY(-4deg) skewX(1deg);
    }
    75% {
        transform: translateY(-10px) rotate(2deg) rotateY(5deg) skewX(-0.5deg);
    }
    100% {
        transform: translateY(-5px) rotate(-1.5deg) rotateY(-3deg) skewX(0.5deg);
    }
}

/* Individual Text Line Sway */
@keyframes textSway {
    0% {
        transform: rotateX(0deg) rotateZ(0deg);
    }
    50% {
        transform: rotateX(8deg) rotateZ(-1deg);
    }
    100% {
        transform: rotateX(-5deg) rotateZ(1deg);
    }
}

/* Ampersand Gentle Flutter */
@keyframes ampersandFlutter {
    0% {
        transform: scale(1) rotate(0deg);
    }
    100% {
        transform: scale(1.08) rotate(3deg);
    }
}



.details-section .container {
    max-width: 650px;
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* "Save The Date" Sub-Header */
.save-the-date {
    font-size: 0.8rem;
    letter-spacing: 5px;
    color: rgb(from var(--primary-color) r g b / 0.6);
    font-weight: 500;
    text-transform: uppercase;
    margin-bottom: 15px;
}

/* Highlighted Couple Names Styling */
.names-card {
    
    padding: 24px 36px;
    margin-bottom: 5px;
    width: 100%;
}


.couple-title {
    font-family: 'Great Vibes', cursive;
      font-size: clamp(4.2rem, 12vw, 7rem);
      font-weight: 400;
      line-height: 0.8;
      letter-spacing: 2px;
}

.name-highlight {
    color: var(--primary-color, #2c2c2c);
    display: inline-block;
}

.couple-title .ampersand {
    font-family: 'Crimson Pro', serif;
    font-size: clamp(2.2rem, 4vw, 2.6rem);
    font-style: italic;
    color: var(--primary-color, #2c2c2c);
    margin: 0 12px;
}

/* Divider Line */
.title-separator {
    width: 80px;
    height: 1px;
    background-color: var(--primary-color, #2c2c2c);
    margin-bottom: 20px;
    opacity: 0.6;
}

/* Date Row Styling */
.date-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 18px;
    font-family: 'Playfair Display', serif;
    color: #2c2c2c;
    margin-bottom: 32px;
}

.date-month, 
.date-year {
    font-size: 1.1rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 3px;
    border-top: 1px solid var(--primary-color, #2c2c2c);
    border-bottom: 1px solid var(--primary-color, #2c2c2c);
    padding: 4px 10px;
}

.date-day {
    font-size: 3.8rem;
    font-weight: 600;
    line-height: 1;
    color: var(--primary-color, #2c2c2c);
}

/* Scroll Indicator */
.scroll-indicator {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    color: #888888;
}

.scroll-indicator p {
    font-size: 0.65rem;
    letter-spacing: 3px;
    margin: 0;
}

.scroll-indicator i {
    font-size: 0.85rem;
    color: var(--primary-color, #2c2c2c);
    animation: bounce 2s infinite;
}

/* Bounce Animation */
@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-6px); }
    60% { transform: translateY(-3px); }
}

        .hero-rsvp-btn {
            background: rgb(from var(--primary-color) r g b / 0.3);
            border: 1px solid rgba(255, 255, 255, 0.4);
            color: #ffffff;
            padding: 16px 55px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.8rem;
            letter-spacing: 4px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.4s ease;
            margin-bottom: auto;
            margin-top: 160px;
            border-radius: 4px;
        }

        .hero-rsvp-btn:hover {
            background: #ffffff;
            color: var(--text-primary);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }

        .scroll-indicator {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            margin-top: auto;
        }

        .scroll-indicator p {
            font-size: 0.65rem;
            letter-spacing: 3px;
            font-weight: 500;
            opacity: 0.9;
        }

        .scroll-indicator i {
            font-size: 0.9rem;
            animation: bounceEffect 2s infinite;
        }

        @keyframes bounceEffect {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(6px); }
        }

        /* ===== AUDIO FAB ===== */
        .audio-fab {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border: none;
            background: var(--accent-gold);
            backdrop-filter: blur(8px);
            color: #ffffff;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 500;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }

        .audio-fab:hover {
            transform: scale(1.05);
            background: var(--accent-gold);
        }

        .bars {
            display: flex;
            align-items: flex-end;
            gap: 2px;
            width: 14px;
            height: 12px;
        }

        .bars span {
            display: block;
            width: 2px;
            height: 100%;
            background: #ffffff;
            transform-origin: bottom;
            animation: barDance 0.8s ease-in-out infinite alternate;
        }

        .bars span:nth-child(1) { animation-delay: -0.1s; }
        .bars span:nth-child(2) { animation-delay: -0.4s; }
        .bars span:nth-child(3) { animation-delay: -0.2s; }
        .bars span:nth-child(4) { animation-delay: -0.6s; }

        @keyframes barDance {
            0% { transform: scaleY(0.2); }
            100% { transform: scaleY(1); }
        }

        .audio-fab.muted .bars { display: none; }
        .audio-fab.muted .mute-icon { display: block; }
        .audio-fab:not(.muted) .mute-icon { display: none; }
        .audio-fab:not(.muted) .bars { display: flex; }

        /* ===== PROFILES ===== */
        /* ===== ARCH SHOWCASE COUPLE SECTION ===== */
.profiles-section {
    max-width: var(--max-content-width, 1000px);
    margin: 0 auto;
    padding: 90px 24px 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 32px;
}

.profile-card {
    flex: 1;
    max-width: 420px;
    text-align: center;
}

/* Arch Frame Container */
.arch-frame-wrapper {
    position: relative;
    padding-bottom: 24px; /* Space for the floating badge */
}

/* Gold Accent Line Frame Behind Image */
.arch-border-accent {
    position: absolute;
    top: -10px;
    left: -10px;
    right: -10px;
    bottom: 14px;
    border: 1px solid var(--accent-gold, #c5a059);
    border-radius: 200px 200px 16px 16px;
    opacity: 0.4;
    pointer-events: none;
    transition: all 0.4s ease;
}

.profile-card:hover .arch-border-accent {
    opacity: 0.9;
    top: -14px;
    bottom: 10px;
}

/* Image Arch Mask */
.img-zoom-wrapper {
    width: 100%;
    height: 480px;
    border-radius: 200px 200px 16px 16px; /* Creates the classic architectural arch */
    overflow: hidden;
    position: relative;
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.06);
    background: #f8f8f8;
}

.profile-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.9s cubic-bezier(0.16, 1, 0.3, 1);
}

.profile-card:hover .profile-img {
    transform: scale(1.06);
}

/* Floating Overlapping Badge */
.profile-badge {
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    padding: 12px 38px;
    border-radius: 25px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    border: 1px solid var(--border-light, #f0f0f0);
    display: flex;
    flex-direction: column;
    align-items: center;
    white-space: nowrap;
    z-index: 3;
    transition: transform 0.3s ease;
}

.profile-card:hover .profile-badge {
    transform: translateX(-50%) translateY(-4px);
}

.badge-role {
    font-size: 0.65rem;
    letter-spacing: 2.5px;
    color: var(--accent-gold, #c5a059);
    font-weight: 700;
    text-transform: uppercase;
}

.badge-name {
    font-family: 'Playfair Display', serif;
    font-size: 1.15rem;
    color: var(--text-primary, #111111);
    font-weight: 500;
}

/* Middle Ampersand Divider */
.profile-divider {
    display: flex;
    align-items: center;
    justify-content: center;
}

.ampersand-divider {
    font-family: 'Playfair Display', serif;
    font-size: 2.8rem;
    font-style: italic;
    color: var(--accent-gold, #c5a059);
    opacity: 0.6;
}

/* Mobile Layout */
@media (max-width: 768px) {
    .profiles-section {
        flex-direction: column;
        gap: 48px;
    }

    .profile-card {
        width: 100%;
        max-width: 340px;
    }

    .img-zoom-wrapper {
        height: 400px;
    }

    .profile-divider {
        margin: -20px 0;
    }
}

        /* ===== GETTING MARRIED ===== */
        .getting-married {
            text-align: center;
            background: var(--bg-main);
            max-width: 750px;
            margin: 0 auto;
            padding: 40px 24px;
        }

        .heart-icon-wrap {
            color: var(--accent-gold);
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        .section-tag {
            font-size: 0.75rem;
            letter-spacing: 5px;
            color: var(--accent-gold);
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .getting-married h2, 
        .location-section h2, 
        .timeline-section h2, 
        .rsvp-section h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.4rem;
            font-weight: 400;
            color: var(--text-primary);
            margin-bottom: 20px;
        }

        .getting-married .description {
            font-size: 1.05rem;
            color: var(--text-muted);
            line-height: 1.8;
            margin-bottom: 60px;
            font-weight: 300;
        }

        .getting-married .signature {
            font-family: 'Alex Brush', cursive;
            font-size: 2.5rem;
            color: var(--accent-gold);
        }

        /* ===== COUNTDOWN ===== */
        /* ===== MINIMALIST FLOATING RING COUNTDOWN ===== */
.countdown-section {
    max-width: 850px;
    margin: 0 auto;
    padding: 30px 24px 60px;
}

.countdown-orbital-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

/* Individual Ring Container */
.time-ring-unit {
    position: relative;
    width: 130px;
    height: 130px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

/* Outer Decorative Pulse Ring */
.ring-backdrop {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    border: 1px dashed var(--accent-gold, #c5a059);
    opacity: 0.35;
    animation: ringSpin 24s linear infinite;
}

/* Hover Pulse Effect */
.time-ring-unit:hover .ring-backdrop {
    opacity: 0.8;
    border-style: solid;
    transform: scale(1.05);
    transition: all 0.4s ease;
}

/* Center Content Circle */
.time-content {
    width: 110px;
    height: 110px;
    background: var(--bg-card-white, #ffffff);
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
    border: 1px solid var(--border-light, #f0f0f0);
    z-index: 2;
    transition: transform 0.3s ease;
}

.time-ring-unit:hover .time-content {
    transform: translateY(-4px);
}

.time-content span {
    font-family: 'Playfair Display', serif;
    font-size: 2rem;
    font-weight: 500;
    color: var(--accent-gold, #c5a059);
    line-height: 1;
    margin-bottom: 4px;
}

.time-content label {
    font-size: 0.6rem;
    color: var(--text-light-gray, #888888);
    letter-spacing: 1.5px;
    font-weight: 600;
}

/* Slow Rotation Animation */
@keyframes ringSpin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

/* Mobile Responsiveness */
@media (max-width: 600px) {
    .countdown-orbital-wrapper {
        gap: 12px;
    }
    
    .time-ring-unit {
        width: 80px;
        height: 80px;
    }

    .time-content {
        width: 70px;
        height: 70px;
    }

    .time-content span {
        font-size: 1.35rem;
    }

    .time-content label {
        font-size: 0.5rem;
        letter-spacing: 0.8px;
    }
}

        /* ===== LOCATION ===== */
        .location-section {
            max-width: 1000px;
            margin: 0 auto;
            padding: 80px 24px;
            text-align: center;
        }

        .venue-card {
            background: var(--bg-card-white);
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.04);
            border: 1px solid var(--border-light);
            margin-top: 45px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            overflow: hidden;
            text-align: left;
        }

        .venue-info-side {
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .venue-icon-badge {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--accent-gold-light);
            color: var(--accent-gold);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            margin-bottom: 24px;
        }

        .venue-card h3 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 400;
            color: var(--text-primary);
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .venue-loc-sub {
            font-size: 0.8rem;
            color: var(--accent-gold);
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 600;
            margin-bottom: 16px;
        }

        .venue-address {
            font-size: 0.95rem;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .time-badge-container {
            margin-bottom: 30px;
        }

        .time-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--accent-gold-light);
            padding: 8px 18px;
            border-radius: 30px;
            font-size: 0.85rem;
            color: var(--text-primary);
            font-weight: 500;
        }

        .venue-actions {
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
        }

        .action-btn {
            flex: 1;
            padding: 14px 20px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.25s ease;
        }

        .map-btn {
            background: var(--primary-color);
            color: #ffffff;
        }

        .map-btn:hover {
            background: <?php 
                $color = $config['primary_color'];
                $r = hexdec(substr($color, 1, 2));
                $g = hexdec(substr($color, 3, 2));
                $b = hexdec(substr($color, 5, 2));
                $r = max(0, $r - 30);
                $g = max(0, $g - 30);
                $b = max(0, $b - 30);
                echo '#' . sprintf("%02x%02x%02x", $r, $g, $b);
            ?>;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(170, 139, 92, 0.2);
        }

        .calendar-btn {
            background: #ffffff;
            color: var(--text-primary);
            border: 1px solid var(--border-light);
        }

        .calendar-btn:hover {
            background: #fafafa;
            transform: translateY(-2px);
            border-color: var(--accent-gold);
        }

        .gcal-link {
            display: inline-block;
            font-size: 0.75rem;
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.2s;
        }

        .gcal-link:hover {
            color: var(--accent-gold);
            text-decoration: underline;
        }

        .venue-map-side {
            width: 100%;
            height: 100%;
            min-height: 400px;
        }

        .map-wrapper {
            width: 100%;
            height: 100%;
            position: relative;
        }

        .live-map-iframe {
            width: 100%;
            height: 100%;
            min-height: 100%;
            border: 0;
            filter: grayscale(0.2) contrast(1.05);
        }

        /* ===== TIMELINE ===== */
        /* ===== HORIZONTAL TIMELINE CARDS ===== */
/* ===== ACCORDION TIMELINE LAYOUT ===== */
.timeline-section {
    max-width: 750px;
    margin: 0 auto;
    padding: 80px 24px;
    text-align: center;
}

.timeline-accordion-container {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-top: 40px;
}

/* Base Accordion Card */
.timeline-accordion-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid rgba(0, 0, 0, 0.08);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    overflow: hidden;
    text-align: left;
    transition: all 0.3s ease;
}

.timeline-accordion-card[open] {
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    border-color: var(--primary-color, var(--accent-gold));
}

/* Header (Clickable Area) */
.accordion-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    cursor: pointer;
    list-style: none; /* Removes default disclosure arrow */
    user-select: none;
}

.accordion-header::-webkit-details-marker {
    display: none; /* Chrome/Safari fix */
}

/* Header Left Group */
.header-left {
    display: flex;
    align-items: center;
    gap: 16px;
}

.step-num {
    font-size: 0.9rem;
    font-weight: 700;
    opacity: 0.3;
    font-family: sans-serif;
}

.icon-circle {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: #ffffff;
    border: 1.5px solid var(--accent-gold);
    color: var(--accent-gold);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    flex-shrink: 0;
}

.header-left h3 {
    font-family: 'Playfair Display', serif;
    font-size: 1.15rem;
    color: var(--text-primary);
    font-weight: 500;
    margin: 0;
}

/* Header Right Group */
.header-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

.time-stamp {
    font-size: 0.75rem;
    background: var(--accent-gold-light);
    color: var(--accent-gold);
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    white-space: nowrap;
}

.toggle-icon {
    font-size: 0.85rem;
    color: var(--text-muted);
    transition: transform 0.3s ease;
}

/* Rotate Chevron on Open */
.timeline-accordion-card[open] .toggle-icon {
    transform: rotate(180deg);
}

/* Body Content */
.accordion-body {
    padding: 0 24px 24px 98px; /* Indented to align cleanly with the title */
}

.accordion-body p {
    font-size: 0.9rem;
    color: var(--text-muted);
    font-weight: 300;
    line-height: 1.6;
    margin: 0;
}

/* Mobile Responsiveness */
@media (max-width: 576px) {
    .accordion-header {
        padding: 16px;
    }
    
    .header-left {
        gap: 10px;
    }

    .step-num {
        display: none; /* Hide step number on small screens to save space */
    }

    .accordion-body {
        padding: 0 16px 20px 16px;
    }
}

/* Card Body Text */
.card-body h3 {
    font-family: 'Playfair Display', serif;
    font-size: 1.25rem;
    color: var(--text-primary);
    font-weight: 500;
    margin-bottom: 10px;
}

.card-body p {
    font-size: 0.88rem;
    color: var(--text-muted);
    font-weight: 300;
    line-height: 1.6;
}

/* Subtle Step Number Background Accent */
.card-step-number {
    position: absolute;
    bottom: 12px;
    right: 18px;
    font-size: 2rem;
    font-weight: 700;
    color: rgba(0, 0, 0, 0.03);
    pointer-events: none;
}

        /* ===== RSVP ===== */
        .rsvp-section {
            max-width: 750px;
            margin: 0 auto;
            padding: 20px 24px;
            text-align: center;
        }

        .rsvp-desc {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 35px;
            font-weight: 300;
        }

        .rsvp-form {
            text-align: left;
            display: flex;
            flex-direction: column;
            gap: 20px;
            background: var(--bg-card-white);
            padding: 40px 30px;
            border-radius: 20px;
            border: 1px solid var(--border-light);
            box-shadow: 0 15px 40px rgba(0,0,0,0.02);
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            width: 100%;
        }

        .form-group label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-primary);
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 16px;
            border-radius: 10px;
            border: 1px solid var(--border-light);
            background-color: var(--bg-main);
            color: var(--text-primary);
            font-size: 0.9rem;
            font-family: 'Montserrat', sans-serif;
            outline: none;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: var(--accent-gold);
            background-color: #ffffff;
            box-shadow: 0 4px 12px rgba(188, 156, 108, 0.05);
        }

        .select-wrapper {
            position: relative;
        }

        .select-wrapper::after {
            content: '\f107';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--accent-gold);
            pointer-events: none;
        }

        .form-group select {
            appearance: none;
            -webkit-appearance: none;
            padding-right: 40px;
        }

        .rsvp-btn {
            width: 100%;
            padding: 18px;
            border-radius: 10px;
            border: none;
            background: var(--primary-color);
            color: #ffffff;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .rsvp-btn:hover {
            background: <?php 
                $color = $config['primary_color'];
                $r = hexdec(substr($color, 1, 2));
                $g = hexdec(substr($color, 3, 2));
                $b = hexdec(substr($color, 5, 2));
                $r = max(0, $r - 30);
                $g = max(0, $g - 30);
                $b = max(0, $b - 30);
                echo '#' . sprintf("%02x%02x%02x", $r, $g, $b);
            ?>;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(170, 139, 92, 0.2);
        }

        /* ===== FOOTER ===== */
        footer {
            text-align: center;
            padding: 60px 24px 40px;
            border-top: 1px solid var(--border-light);
        }

        .footer-headline {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            color: var(--text-primary);
            margin-bottom: 6px;
        }

        .footer-copy {
            font-size: 0.7rem;
            color: var(--text-light-gray);
            letter-spacing: 1px;
        }

        /* ===== FEATURE BUTTONS ===== */
        .feature-buttons-section {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 24px;
            text-align: center;
        }

        .feature-buttons-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .feature-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 40px;
            border: 2px solid var(--accent-gold);
            border-radius: 50px;
            background: transparent;
            color: var(--accent-gold);
            font-family: 'Montserrat', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .feature-btn:hover {
            background: var(--accent-gold);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(188, 156, 108, 0.3);
        }

        .feature-btn i {
            font-size: 1.2rem;
        }

        .feature-btn.active {
            background: var(--accent-gold);
            color: #ffffff;
        }

        /* ===== GALLERY ===== */
        /* ===== EDITORIAL MASONRY GALLERY ===== */
.gallery-section {
    max-width: var(--max-content-width, 1100px);
    margin: 0 auto;
    padding: 60px 20px 80px;
    text-align: center;
}

/* CSS Columns Masonry (Flows naturally without row cropping) */
.gallery-masonry {
    column-count: 3;
    column-gap: 10px;
    margin-top: 10px;
}

.gallery-item {
    break-inside: avoid;
    margin-bottom: 10px;
    position: relative;
    border-radius: 16px;
    overflow: hidden;
    background: #fdfdfd;
    cursor: pointer;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.4s ease;
}

.gallery-item:hover {
    transform: translateY(-6px);
    box-shadow: 0 18px 40px rgba(0, 0, 0, 0.1);
}

/* Image Container */
.gallery-img-wrapper {
    position: relative;
    width: 100%;
    overflow: hidden;
    display: block;
}

.gallery-item img {
    width: 100%;
    height: auto;
    display: block;
    object-fit: cover;
    transition: transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}

.gallery-item:hover img {
    transform: scale(1.06);
}

/* Golden Inner Frame on Hover */
.hover-frame-accent {
    position: absolute;
    inset: 12px;
    border: 1px solid rgba(255, 255, 255, 0.7);
    border-radius: 10px;
    opacity: 0;
    transform: scale(0.95);
    transition: all 0.35s ease;
    pointer-events: none;
    z-index: 2;
}

.gallery-item:hover .hover-frame-accent {
    opacity: 1;
    transform: scale(1);
}

/* Floating Expand Icon Badge */
.zoom-icon-badge {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 36px;
    height: 36px;
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-primary, #111);
    font-size: 0.8rem;
    opacity: 0;
    transform: translateY(-8px);
    transition: all 0.3s ease;
    z-index: 3;
}

.gallery-item:hover .zoom-icon-badge {
    opacity: 1;
    transform: translateY(0);
}

/* Floating Bottom Caption */
.gallery-caption {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 24px 20px 16px;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.75) 0%, rgba(0, 0, 0, 0) 100%);
    color: #ffffff;
    font-family: 'Playfair Display', serif;
    font-size: 0.92rem;
    font-style: italic;
    text-align: left;
    transform: translateY(10px);
    opacity: 0;
    transition: all 0.35s ease;
    z-index: 3;
}

.gallery-item:hover .gallery-caption {
    opacity: 1;
    transform: translateY(0);
}

/* Empty State */
.empty-gallery {
    padding: 80px 20px;
    color: var(--text-muted, #999);
    text-align: center;
    grid-column: 1 / -1;
}

.empty-gallery i {
    font-size: 2.5rem;
    margin-bottom: 12px;
    opacity: 0.5;
}

/*==========================
CELEBRATION SCREEN
==========================*/

.celebration-screen{

    position:relative;
    overflow:hidden;

    width:100%;
    min-height:280px;

    padding:60px 20px;

    text-align:center;

    border-radius:25px;

    background:linear-gradient(135deg,#fffdf9,#fff6ea,#fffdf9);

    animation:fadeIn 1.2s ease;

}

.celebration-screen h2{

    font-size:2.2rem;

    color:#C8A24A;

    margin-top:15px;

    margin-bottom:15px;

    font-family:'Playfair Display',serif;

    animation:glow 2s infinite alternate;

}

.celebration-screen p{

    font-size:1.1rem;

    color:#555;

}

.floating-hearts{

    font-size:38px;

    animation:floatHearts 3s infinite ease-in-out;

}

.rings{

    font-size:70px;

    margin:18px 0;

    animation:ringPulse 1.8s infinite;

}

.confetti{

    margin-top:25px;

    font-size:34px;

    animation:confettiText 1s infinite alternate;

}

/* Falling Confetti */

.confetti-piece{

    position:absolute;

    top:-30px;

    width:10px;

    height:18px;

    opacity:.8;

    animation:fall linear infinite;

}

/* Animations */

@keyframes fadeIn{

from{

opacity:0;

transform:scale(.85);

}

to{

opacity:1;

transform:scale(1);

}

}

@keyframes glow{

from{

text-shadow:0 0 5px #D4AF37;

}

to{

text-shadow:0 0 25px gold;

}

}

@keyframes floatHearts{

0%{

transform:translateY(0);

}

50%{

transform:translateY(-12px);

}

100%{

transform:translateY(0);

}

}

@keyframes ringPulse{

0%{

transform:scale(1);

}

50%{

transform:scale(1.15);

}

100%{

transform:scale(1);

}

}

@keyframes confettiText{

from{

transform:translateY(0);

}

to{

transform:translateY(-8px);

}

}

@keyframes fall{

0%{

transform:translateY(-30px) rotate(0deg);

opacity:1;

}

100%{

transform:translateY(420px) rotate(720deg);

opacity:0;

}

}

/* Responsive Layout */
@media (max-width: 900px) {
    .gallery-masonry {
        column-count: 2;
        column-gap: 14px;
    }
}

@media (max-width: 550px) {
    .gallery-masonry {
        column-count: 2;
    }
}

        /* ===== TABLE FINDER ===== */
        .table-finder-section {
            max-width: 700px;
            margin: 0 auto;
            padding: 40px 24px 60px;
            text-align: center;
        }

        .table-finder-container {
            background: var(--bg-card-white);
            border-radius: 20px;
            padding: 40px 30px;
            border: 1px solid var(--border-light);
            box-shadow: 0 15px 40px rgba(0,0,0,0.02);
            margin-top: 30px;
        }

        .search-box {
            display: flex;
            gap: 12px;
            margin-bottom: 30px;
        }

        .table-search-input {
            flex: 1;
            padding: 14px 20px;
            border: 2px solid var(--border-light);
            border-radius: 10px;
            font-size: 1rem;
            font-family: 'Montserrat', sans-serif;
            transition: border-color 0.3s;
        }

        .table-search-input:focus {
            outline: none;
            border-color: var(--accent-gold);
        }

        .table-search-btn {
            padding: 14px 30px;
            background: var(--accent-gold);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            font-family: 'Montserrat', sans-serif;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .table-search-btn:hover {
            background: <?php 
                $color = $config['primary_color'];
                $r = hexdec(substr($color, 1, 2));
                $g = hexdec(substr($color, 3, 2));
                $b = hexdec(substr($color, 5, 2));
                $r = max(0, $r - 30);
                $g = max(0, $g - 30);
                $b = max(0, $b - 30);
                echo '#' . sprintf("%02x%02x%02x", $r, $g, $b);
            ?>;
        }

        .table-result {
    text-align: center;
    padding: 20px 0;
    max-width: 500px;
    margin: 0 auto;
}

.table-result .found {
    background: #ffffff;
    border-radius: 12px;
    padding: 24px 20px;
    margin-bottom: 14px;
    border: 1px solid #e8e0d6;
    transition: all 0.3s ease;
    animation: slideInResult 0.4s ease;
}

@keyframes slideInResult {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.table-result .found:hover {
    border-color: var(--accent-gold, #bc9c6c);
    box-shadow: 0 4px 16px rgba(188, 156, 108, 0.1);
}

.table-result .found .guest-name {
    font-family: 'Playfair Display', serif;
    font-size: 1.4rem;
    color: var(--accent-gold, #bc9c6c);
    margin-bottom: 2px;
}

.table-result .found .family-name {
    color: #999;
    font-size: 0.9rem;
    margin-top: 2px;
    margin-bottom: 6px;
}

.table-result .found .table-number {
    font-size: 2.8rem;
    font-weight: 700;
    color: var(--accent-gold, #bc9c6c);
    display: block;
    margin-top: 6px;
}

.table-result .found .table-label {
    font-size: 0.6rem;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #bbb;
    display: block;
    margin-top: 4px;
}

.table-result .not-found {
    background: #ffffff;
    border-radius: 12px;
    padding: 40px 20px;
    border: 2px dashed #e5e0d8;
    animation: slideInResult 0.4s ease;
}

.table-result .not-found i {
    font-size: 2.2rem;
    color: #e5e0d8;
    display: block;
    margin-bottom: 12px;
}

.table-result .not-found p {
    color: #999;
    font-size: 1.1rem;
    margin: 4px 0;
}

.table-result .not-found p:last-child {
    font-size: 0.85rem;
    color: #bbb;
    margin-top: 6px;
}

        /* ===== ANIMATIONS ===== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .reveal-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 1s cubic-bezier(0.215, 0.61, 0.355, 1), transform 1s cubic-bezier(0.215, 0.61, 0.355, 1);
        }

        .reveal-on-scroll.active {
            opacity: 1;
            transform: translateY(0);
        }

        /* ===== RESPONSIVE ===== */
        @media (min-width: 600px) {
            .countdown-grid {
                grid-template-columns: repeat(4, 1fr);
            }
            .venue-actions {
                flex-direction: row;
            }
            .action-btn {
                flex: 1;
            }
        }

        @media (min-width: 992px) {
            .couple-title {
                font-size: 5rem;
            }
            .couple-title span {
                font-size: 5.5rem;
            }
            .profiles-section {
                grid-template-columns: repeat(2, 1fr);
                gap: 60px;
                padding-top: 120px;
            }
            .img-zoom-wrapper {
                height: 540px;
            }
            .getting-married h2, .location-section h2, .timeline-section h2, .rsvp-section h2 {
                font-size: 3rem;
            }
            .form-row-desktop {
                display: flex;
                gap: 20px;
            }
        }

        @media (max-width: 768px) {
            .venue-card {
                grid-template-columns: 1fr;
                text-align: center;
                border-radius: 20px;
            }
            .venue-info-side {
                padding: 40px 24px;
                order: 1;
            }
            .venue-icon-badge {
                margin: 0 auto 16px auto;
            }
            .venue-card h3 {
                font-size: 1.6rem;
            }
            .venue-actions {
                flex-direction: column;
                gap: 10px;
            }
            .action-btn {
                width: 100%;
            }
            .venue-map-side {
                order: 2;
                height: 300px;
                min-height: 300px;
                border-top: 1px solid var(--border-light);
            }
            
            .timeline-container::before {
                left: 50%;
                transform: translateX(-50%);
            }
            .timeline-row, 
            .timeline-row:nth-child(even) {
                width: 100%;
                margin-left: 0;
                padding: 0 16px;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                margin-bottom: 50px;
            }
            .timeline-left-icon, 
            .timeline-row:nth-child(even) .timeline-left-icon {
                position: relative;
                left: auto;
                right: auto;
                top: 0;
                margin-bottom: 16px;
            }
            .timeline-right-content, 
            .timeline-row:nth-child(even) .timeline-right-content {
                text-align: center;
                max-width: 100%;
                padding: 0;
            }
            .time-stamp {
                margin-bottom: 10px;
            }
            
            .feature-buttons-container {
                flex-direction: column;
                align-items: center;
            }
            .feature-btn {
                width: 100%;
                justify-content: center;
            }
            
            .gallery-grid {
                grid-template-columns: repeat(2, 1fr);
                grid-auto-rows: 150px;
                gap: 8px;
            }
            .gallery-item:nth-child(3n + 1) {
                grid-row: span 2;
            }
            .gallery-item {
                grid-column: span 1 !important;
            }
            .gallery-caption {
                opacity: 1;
                background: linear-gradient(to top, rgba(0, 0, 0, 0.5) 0%, transparent 100%);
                padding: 10px;
                font-size: 0.8rem;
            }
            
            .search-box {
                flex-direction: column;
            }
            .table-search-btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 375px) {
            .couple-title { font-size: 1.4rem; }
            .couple-title span { font-size: 2.6rem; }
        }
    </style>
</head>
<body>

    <!-- ===== FALLING PETALS ===== -->
    <div class="petals-container" id="petalsContainer"></div>

    <script>
        window.invitationConfig = <?php echo json_encode($config); ?>;
        window.tableData = <?php echo json_encode($table_entries); ?>;
    </script>

    <div class="invitation-wrapper">
        
        <!-- ===== HERO SECTION ===== -->
<!-- ===== HERO SECTION (IMAGE ONLY) ===== -->
<section class="hero-section">
    <svg class="brush-mask" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2880 180" preserveAspectRatio="none">
        <path d="
            M0,100 Q180,30 360,100 T720,100 T1080,100 T1440,100
            Q1620,30 1800,100 T2160,100 T2520,100 T2880,100
            L2880,180 L0,180 Z"></path>
    </svg>
</section>

<!-- ===== DETAILS & HIGHLIGHTED NAMES SECTION (BELOW HERO) ===== -->
<section class="details-section">
    <div class="container">
        <!-- Save The Date Subheading -->
        <p class="save-the-date">Save The Date</p>

        <!-- Highlighted Couple Names Card -->
        <div class="names-card" style="display: inline-block; transform-origin: top center; animation: windFlutter 5s ease-in-out infinite alternate;">
    <h1 class="couple-title">
        <span class="name-highlight" style="display: inline-block; transform-origin: center; animation: textSway 4s ease-in-out infinite alternate 0.3s;"><?php echo htmlspecialchars($config['bride_name']); ?></span> <br>
        <span class="ampersand" style="display: inline-block; transform-origin: center; animation: ampersandFlutter 3.5s ease-in-out infinite alternate 0.6s;">&amp;</span> <br>
        <span class="name-highlight" style="display: inline-block; transform-origin: center; animation: textSway 4.5s ease-in-out infinite alternate 0.9s;"><?php echo htmlspecialchars($config['groom_name']); ?></span>
    </h1>
</div>

        <div class="title-separator"></div>

        <!-- Date Display Row -->
        <div class="date-row">
            <span class="date-month"><?php echo htmlspecialchars($config['month']); ?></span>
            <span class="date-day"><?php echo htmlspecialchars($config['day']); ?></span>
            <span class="date-year"><?php echo htmlspecialchars($config['year']); ?></span>
        </div>

        <!-- Scroll Indicator -->
        <div class="scroll-indicator">
            <p>SCROLL DOWN</p>
            <i class="fa-solid fa-chevron-down"></i>
        </div>
    </div>
</section>

            <button id="audioToggle" class="audio-fab muted">
                <div class="bars">
                    <span></span><span></span><span></span><span></span>
                </div>
                <i class="fa-solid fa-volume-xmark mute-icon"></i>
            </button>
        

        <!-- Profiles Section -->
        <?php if ($config['show_profiles'] == 1): ?>
        <section class="profiles-section reveal-on-scroll" id="mainContent">
    <div class="profile-card">
        <div class="arch-frame-wrapper">
            <div class="arch-border-accent"></div>
            <div class="img-zoom-wrapper">
                <img src="<?php echo getMediaPath($config['bride_image'], 'images', $config['username']); ?>" alt="The Bride" class="profile-img">
            </div>
            <div class="profile-badge">
                <span class="badge-role">THE BRIDE</span>
                <span class="badge-name"><?php echo htmlspecialchars($config['bride_name']); ?></span>
            </div>
        </div>
    </div>

    <div class="profile-divider">
        <span class="ampersand-divider">&amp;</span>
    </div>

    <div class="profile-card">
        <div class="arch-frame-wrapper">
            <div class="arch-border-accent"></div>
            <div class="img-zoom-wrapper">
                <img src="<?php echo getMediaPath($config['groom_image'], 'images', $config['username']); ?>" alt="The Groom" class="profile-img">
            </div>
            <div class="profile-badge">
                <span class="badge-role">THE GROOM</span>
                <span class="badge-name"><?php echo htmlspecialchars($config['groom_name']); ?></span>
            </div>
        </div>
    </div>
</section>
        <?php endif; ?>

        <section class="getting-married reveal-on-scroll">
            <div class="heart-icon-wrap">
                <i class="fa-regular fa-heart"></i>
            </div>
            <p class="section-tag">WE ARE</p>
            <h2>Getting Married</h2>
            <p class="description"><?php echo nl2br(htmlspecialchars($config['sub_headline'])); ?></p>
            <p class="signature" style="display: inline-block; transform-origin: top center; animation: windFlutter 5s ease-in-out infinite alternate;">- <?php echo htmlspecialchars($config['bride_name']); ?> & <?php echo htmlspecialchars($config['groom_name']); ?> -</p>
        </section>

        <section class="countdown-section reveal-on-scroll">

    <div class="countdown-orbital-wrapper">

        <div class="time-ring-unit">
            <div class="ring-backdrop"></div>
            <div class="time-content">
                <span id="days">00</span>
                <label>DAYS</label>
            </div>
        </div>

        <div class="time-ring-unit">
            <div class="ring-backdrop"></div>
            <div class="time-content">
                <span id="hours">00</span>
                <label>HOURS</label>
            </div>
        </div>

        <div class="time-ring-unit">
            <div class="ring-backdrop"></div>
            <div class="time-content">
                <span id="minutes">00</span>
                <label>MINS</label>
            </div>
        </div>

        <div class="time-ring-unit">
            <div class="ring-backdrop"></div>
            <div class="time-content">
                <span id="seconds">00</span>
                <label>SECS</label>
            </div>
        </div>

    </div>

</section>

        <section class="location-section reveal-on-scroll">
            <p class="section-tag">JOIN US AT</p>
            <h2>Location</h2>

            <div class="venue-card">
                <div class="venue-info-side">
                    <div class="venue-icon-badge">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <h3><?php echo htmlspecialchars($config['venue_title']); ?></h3>
                    <p class="venue-loc-sub"><?php echo htmlspecialchars($config['venue_location']); ?></p>
                    <p class="venue-address"><?php echo htmlspecialchars($config['venue_address']); ?></p>
                    
                    <div class="time-badge-container">
                        <div class="time-badge">
                            <i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($config['event_time']); ?>
                        </div>
                    </div>

                    <div class="venue-actions">
                        <a href="<?php echo htmlspecialchars($config['google_maps_url']); ?>" target="_blank" class="action-btn map-btn">
                            <i class="fa-solid fa-location-dot"></i> OPEN IN MAPS
                        </a>
                        <button class="action-btn calendar-btn" id="addToCalendar">
                            <i class="fa-regular fa-calendar-plus"></i> ADD TO CALENDAR
                        </button>
                    </div>
                    <a href="#" class="gcal-link" id="googleCalHint">Or Add to Google Calendar</a>
                </div>

                <div class="venue-map-side">
                    <div class="map-wrapper">
                        <iframe 
                            class="live-map-iframe"
                            src="https://maps.google.com/maps?q=<?php echo urlencode($config['venue_title'] . ' ' . $config['venue_address']); ?>&t=&z=15&ie=UTF8&iwloc=&output=embed" 
                            frameborder="0" 
                            scrolling="no" 
                            marginheight="0" 
                            marginwidth="0"
                            allowfullscreen="" 
                            loading="lazy">
                        </iframe>
                    </div>
                </div>
            </div>
        </section>

        <section class="timeline-section reveal-on-scroll">
    <p class="section-tag">OUR CELEBRATION</p>
    <h2>Timeline</h2>
    
    <div class="timeline-accordion-container">
        <?php 
        $default_icons = ['fa-location-dot', 'fa-heart', 'fa-microphone', 'fa-utensils', 'fa-music', 'fa-wand-magic-sparkles', 'fa-moon'];
        foreach($config['schedule'] as $index => $item): 
            $currentIcon = !empty($item['icon']) ? $item['icon'] : (isset($default_icons[$index]) ? $default_icons[$index] : 'fa-star');
        ?>
            <details class="timeline-accordion-card" <?php echo $index === 0 ? 'open' : ''; ?>>
                <summary class="accordion-header">
                    <div class="header-left">
                        <span class="step-num">0<?php echo $index + 1; ?></span>
                        <div class="icon-circle" style="border-color: var(--primary-color); color: var(--primary-color);">
                            <i class="fa-solid <?php echo $currentIcon; ?>"></i>
                        </div>
                        <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                    </div>
                    <div class="header-right">
                        <span class="time-stamp" style="background: var(--secondary-color); color: var(--primary-color);"><?php echo htmlspecialchars($item['time']); ?></span>
                        <i class="fa-solid fa-chevron-down toggle-icon"></i>
                    </div>
                </summary>
                
                <div class="accordion-body">
                    <p><?php echo htmlspecialchars($item['description']); ?></p>
                </div>
            </details>
        <?php endforeach; ?>
    </div>
</section>

        <!-- Feature Buttons -->
        <?php if ($config['has_gallery'] || $config['has_table_finder']): ?>
        <section class="feature-buttons-section reveal-on-scroll">
            <div class="feature-buttons-container">
                <?php if ($config['has_gallery']): ?>
                <button class="feature-btn gallery-btn" id="galleryToggle">
                    <i class="fa-regular fa-images"></i>
                    <span>Gallery</span>
                </button>
                <?php endif; ?>
                <?php if ($config['has_table_finder']): ?>
                <button class="feature-btn table-btn" id="tableFinderToggle">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <span>Find Your Table</span>
                </button>
                <?php endif; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Gallery Section -->
        <?php if ($config['has_gallery']): ?>
        <section class="gallery-section reveal-on-scroll" id="gallerySection" style="display: none;">
    <p class="section-tag">OUR MEMORIES</p>
    <h2>Gallery</h2>

    <div class="gallery-masonry">
        <?php if (empty($config['gallery_images'])): ?>
            <div class="empty-gallery">
                <i class="fa-regular fa-image"></i>
                <p>No gallery images yet</p>
            </div>
        <?php else: ?>
            <?php foreach ($config['gallery_images'] as $index => $img): ?>
                <div class="gallery-item" data-index="<?php echo $index; ?>">
                    <div class="gallery-img-wrapper">
                        <img 
                            src="<?php echo getMediaPath($img['image_path'], 'images', $config['username']); ?>" 
                            alt="<?php echo htmlspecialchars($img['caption'] ?? 'Wedding memory'); ?>"
                            loading="lazy"
                            onerror="this.style.background='#e8d5c4'; this.src=''; this.alt='Image not found';"
                        >
                        <!-- Inner Hover Frame -->
                        <div class="hover-frame-accent"></div>
                        
                        <!-- Zoom Icon Indicator -->
                        <div class="zoom-icon-badge">
                            <i class="fa-solid fa-expand"></i>
                        </div>
                    </div>

                    <?php if (!empty($img['caption'])): ?>
                        <div class="gallery-caption">
                            <span><?php echo htmlspecialchars($img['caption']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
        <?php endif; ?>

        <!-- Table Finder Section -->
        <?php if ($config['has_table_finder']): ?>
        <section class="table-finder-section reveal-on-scroll" id="tableFinderSection" style="display: none;">
            <p class="section-tag">FIND YOUR TABLE</p>
            <h2>Find Your Table</h2>
            <div class="table-finder-container">
                <div class="search-box">
                    <input type="text" id="tableSearchInput" placeholder="Enter your name..." class="table-search-input">
                    <button id="tableSearchBtn" class="table-search-btn">
                        <i class="fa-solid fa-magnifying-glass"></i> Search
                    </button>
                </div>
                <div id="tableResult" class="table-result">
                    <div class="not-found">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <p>Search for your name to find your table</p>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <section class="rsvp-section reveal-on-scroll" id="rsvpFormSection">
            <p class="section-tag">JOIN US</p>
            <h2>RSVP</h2>
            <p class="rsvp-desc">Please let us know if you can join our celebration</p>
            
            <form id="rsvpForm" class="rsvp-form">
                <div class="form-row-desktop">
                    <div class="form-group">
                        <label>Your Name *</label>
                        <input type="text" id="guestName" placeholder="Enter your full name" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" id="guestEmail" placeholder="your.email@example.com">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Will you attend? *</label>
                    <div class="select-wrapper">
                        <select id="attendanceStatus" required>
                            <option value="" disabled selected>Select Attendance</option>
                            <option value="Joyfully Accepts">Joyfully Accepts</option>
                            <option value="Regretfully Declines">Regretfully Declines</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="rsvp-btn">Submit RSVP</button>
            </form>
        </section>

        <footer>
            <p class="footer-headline"><?php echo htmlspecialchars($config['couple_headline']); ?></p>
            <span class="footer-copy">© <?php echo date('Y'); ?></span>
        </footer>

    </div>

    <script>
    /**
     * Wedding Invitation - Main Script
     * Handles all frontend functionality + Falling Petals
     */
    document.addEventListener('DOMContentLoaded', function() {
        
        // ===== FALLING PETALS =====
        function createPetals() {
            const container = document.getElementById('petalsContainer');
            if (!container) return;
            
            const petalCount = window.innerWidth < 768 ? 18 : 35;
            const petalEmojis = ['🍃', '💍', '💚', '🤍', '🕊️', '💚', '💍', '❄️'];
            
            // Get the secondary color from CSS variable
            const style = getComputedStyle(document.documentElement);
            let secondaryColor = style.getPropertyValue('--secondary-color').trim();
            // Fallback if variable not set
            if (!secondaryColor || secondaryColor === '') {
                secondaryColor = '#f4efe6';
            }
            
            for (let i = 0; i < petalCount; i++) {
                const petal = document.createElement('div');
                petal.className = 'petal';
                
                // Use emoji instead of colored div for better visual
                const emoji = petalEmojis[Math.floor(Math.random() * petalEmojis.length)];
                petal.textContent = emoji;
                
                // Random properties
                const left = Math.random() * 100;
                const delay = Math.random() * 12;
                const duration = 10 + Math.random() * 15;
                const size = 16 + Math.random() * 14;
                
                petal.style.left = left + '%';
                petal.style.animationDelay = delay + 's';
                petal.style.animationDuration = duration + 's';
                petal.style.fontSize = size + 'px';
                petal.style.opacity = 0.4 + Math.random() * 0.5;
                
                container.appendChild(petal);
            }
        }
        
        // Create petals after a short delay
        setTimeout(createPetals, 200);
        
        // Recreate petals on resize (debounced)
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                const container = document.getElementById('petalsContainer');
                if (container) {
                    container.innerHTML = '';
                    createPetals();
                }
            }, 500);
        });

        const audioToggle = document.getElementById('audioToggle');
        const rsvpForm = document.getElementById('rsvpForm');
        const heroRsvpTrigger = document.getElementById('heroRsvpTrigger');
        const config = window.invitationConfig || {};
        const tableData = window.tableData || [];

        // SCROLL REVEAL
        const revealElements = document.querySelectorAll('.reveal-on-scroll');
        
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15, rootMargin: "0px 0px -50px 0px" });

        revealElements.forEach(element => {
            revealObserver.observe(element);
        });

        // AUDIO ENGINE
        let bgAudio = null;
        let audioLoaded = false;

        function initAudio() {
            try {
                let audioPath = config.bg_audio || '';
                if (!audioPath) {
                    console.warn('No background audio configured');
                    return;
                }
                
                if (!audioPath.includes('../uploads/audio/')) {
                    audioPath = '../uploads/audio/' + config.username + '/' + audioPath;
                }
                
                console.log('Loading background audio from:', audioPath);
                bgAudio = new Audio(audioPath);
                bgAudio.loop = true;
                bgAudio.volume = 0.3;
                bgAudio.preload = 'auto';
                
                bgAudio.addEventListener('canplaythrough', function() {
                    console.log('Background audio loaded successfully');
                    audioLoaded = true;
                });
                
                bgAudio.addEventListener('error', function(e) {
                    console.warn('Background audio error:', e);
                });
                
                function attemptAutoPlay() {
                    if (bgAudio && bgAudio.paused && audioLoaded) {
                        bgAudio.play().then(() => {
                            console.log('Background audio playing');
                            if (audioToggle) {
                                audioToggle.classList.remove('muted');
                            }
                        }).catch(() => {
                            console.log('Autoplay blocked, waiting for user interaction');
                        });
                    }
                }
                
                document.addEventListener('click', function playOnInteraction() {
                    attemptAutoPlay();
                    document.removeEventListener('click', playOnInteraction);
                }, { once: true });
                
                document.addEventListener('scroll', function playOnScroll() {
                    attemptAutoPlay();
                    document.removeEventListener('scroll', playOnScroll);
                }, { once: true });
                
                bgAudio.addEventListener('playing', function() {
                    if (audioToggle) {
                        audioToggle.classList.remove('muted');
                    }
                });
                
                bgAudio.addEventListener('pause', function() {
                    if (audioToggle) {
                        audioToggle.classList.add('muted');
                    }
                });
                
            } catch (error) {
                console.warn('Audio initialization error:', error);
            }
        }

        if (audioToggle) {
            audioToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                if (bgAudio) {
                    if (bgAudio.paused) {
                        bgAudio.play().then(() => {
                            audioToggle.classList.remove('muted');
                        }).catch(error => {
                            console.warn('Failed to play audio:', error);
                        });
                    } else {
                        bgAudio.pause();
                        audioToggle.classList.add('muted');
                    }
                } else {
                    initAudio();
                    setTimeout(() => {
                        if (bgAudio) {
                            bgAudio.play().catch(() => {});
                        }
                    }, 500);
                }
            });
        }

        // ================================
// COUNTDOWN TIMER
// ================================

const targetDate = config.countdown_target;

if (targetDate) {

    const countdownDate = new Date(targetDate).getTime();
    let interval;

    function showCelebration() {

        const wrapper = document.querySelector(".countdown-orbital-wrapper");

        if (!wrapper) return;

        wrapper.innerHTML = `
            <div class="celebration-screen">

                <div class="floating-hearts">
                    ❤ 💖 💕 💗 💞
                </div>

                <div class="rings">
                    💍
                </div>

                <h2>Our Wedding Celebration Has Begun</h2>

                <p>
                    Thank you for being part of our special day.
                </p>

                <div class="confetti">
                    ✨ 🎉 ✨ 🎊 ✨ 🎉 ✨
                </div>

            </div>
        `;

        createConfetti();
    }

    function updateCountdown() {

        const now = new Date().getTime();
        const diff = countdownDate - now;

        const days = document.getElementById("days");
        const hours = document.getElementById("hours");
        const minutes = document.getElementById("minutes");
        const seconds = document.getElementById("seconds");

        if (diff <= 0) {

            clearInterval(interval);

            showCelebration();

            return;
        }

        const d = Math.floor(diff / (1000 * 60 * 60 * 24));
        const h = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const s = Math.floor((diff % (1000 * 60)) / 1000);

        if (days) days.textContent = String(d).padStart(2, "0");
        if (hours) hours.textContent = String(h).padStart(2, "0");
        if (minutes) minutes.textContent = String(m).padStart(2, "0");
        if (seconds) seconds.textContent = String(s).padStart(2, "0");

    }

    function createConfetti() {

        const container = document.querySelector(".celebration-screen");

        if (!container) return;

        for (let i = 0; i < 80; i++) {

            const confetti = document.createElement("span");

            confetti.className = "confetti-piece";

            confetti.style.left = Math.random() * 100 + "%";

            confetti.style.animationDelay = Math.random() * 3 + "s";

            confetti.style.animationDuration = (3 + Math.random() * 4) + "s";

            confetti.style.background = [
                "#D4AF37",
                "#FFD700",
                "#E8C547",
                "#FFFFFF",
                "#F8E6B3",
                "#F4C430"
            ][Math.floor(Math.random() * 6)];

            container.appendChild(confetti);

        }

    }

    updateCountdown();

    interval = setInterval(updateCountdown, 1000);

}
        // GALLERY & TABLE FINDER TOGGLES
        const galleryBtn = document.getElementById('galleryToggle');
        const gallerySection = document.getElementById('gallerySection');
        const tableBtn = document.getElementById('tableFinderToggle');
        const tableSection = document.getElementById('tableFinderSection');

        if (galleryBtn && gallerySection) {
            galleryBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (tableSection && tableSection.style.display !== 'none') {
                    tableSection.style.display = 'none';
                    if (tableBtn) tableBtn.classList.remove('active');
                }
                if (gallerySection.style.display === 'none' || gallerySection.style.display === '') {
                    gallerySection.style.display = 'block';
                    galleryBtn.classList.add('active');
                    setTimeout(() => {
                        gallerySection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 200);
                } else {
                    gallerySection.style.display = 'none';
                    galleryBtn.classList.remove('active');
                }
            });
        }

        if (tableBtn && tableSection) {
            tableBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (gallerySection && gallerySection.style.display !== 'none') {
                    gallerySection.style.display = 'none';
                    if (galleryBtn) galleryBtn.classList.remove('active');
                }
                if (tableSection.style.display === 'none' || tableSection.style.display === '') {
                    tableSection.style.display = 'block';
                    tableBtn.classList.add('active');
                    setTimeout(() => {
                        tableSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 200);
                } else {
                    tableSection.style.display = 'none';
                    tableBtn.classList.remove('active');
                }
            });
        }

        // TABLE FINDER SEARCH
        const searchInput = document.getElementById('tableSearchInput');
        const searchBtn = document.getElementById('tableSearchBtn');
        const tableResult = document.getElementById('tableResult');

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function searchTable() {
            const query = searchInput.value.trim().toLowerCase();
            if (!query) {
                tableResult.innerHTML = '<div class="not-found"><i class="fa-solid fa-magnifying-glass"></i><p>Please enter your name</p></div>';
                return;
            }
            
            const results = tableData.filter(item => 
                item.guest_name.toLowerCase().includes(query) || 
                (item.family_name && item.family_name.toLowerCase().includes(query))
            );
            
            if (results.length > 0) {
    let html = '';
    results.forEach(result => {
        html += `
            <div class="found">
                <div class="guest-name">${escapeHtml(result.guest_name)}</div>
                ${result.family_name ? `<div class="family-name">👨‍👩‍👧‍👦 ${escapeHtml(result.family_name)}</div>` : ''}
                <span class="table-label">Table</span>
                <div class="table-number">${escapeHtml(result.table_number)}</div>
            </div>
        `;
    });
    tableResult.innerHTML = html;
} else {
    tableResult.innerHTML = `
        <div class="not-found">
            <i class="fa-solid fa-magnifying-glass"></i>
            <p>No guest found with name "<strong>${escapeHtml(searchInput.value)}</strong>"</p>
            <p>Please check the spelling or try a different name.</p>
        </div>
    `;
}
        }

        if (searchBtn && searchInput) {
            searchBtn.addEventListener('click', function(e) {
                e.preventDefault();
                searchTable();
            });
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    searchTable();
                }
            });
        }

        // CALENDAR
        function dispatchCalendar() {
            const start = new Date(config.countdown_target || new Date().toISOString());
            const end = new Date(start.getTime() + 6 * 60 * 60 * 1000);
            const startStr = start.toISOString().replace(/-|:|\.\d+/g, '');
            const endStr = end.toISOString().replace(/-|:|\.\d+/g, '');
            const url = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent(config.couple_headline + ' Wedding')}&dates=${startStr}/${endStr}&details=${encodeURIComponent('Wedding celebration of ' + config.couple_headline)}&location=${encodeURIComponent(config.venue_title + ', ' + config.venue_address)}`;
            window.open(url, '_blank');
        }

        document.getElementById('addToCalendar')?.addEventListener('click', dispatchCalendar);
        document.getElementById('googleCalHint')?.addEventListener('click', function(e) {
            e.preventDefault();
            dispatchCalendar();
        });

        // SCROLL INDICATOR
        document.querySelector('.scroll-indicator')?.addEventListener('click', function() {
            document.getElementById('mainContent')?.scrollIntoView({ behavior: 'smooth' });
        });

        if (heroRsvpTrigger) {
            heroRsvpTrigger.addEventListener('click', function() {
                document.getElementById('rsvpFormSection')?.scrollIntoView({ behavior: 'smooth' });
            });
        }

        // RSVP FORM
        if (rsvpForm) {
            rsvpForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const name = document.getElementById('guestName').value.trim();
                const email = document.getElementById('guestEmail').value.trim();
                const status = document.getElementById('attendanceStatus').value;
                const phone = config.whatsapp_phone || '';

                if (!name || !status) {
                    alert('Please complete all mandatory entry forms.');
                    return;
                }

                const msg = `*WEDDING RSVP CONFIRMATION*\n\n*Guest Name:* ${name}\n${email ? '*Email:* ' + email : ''}\n*Attendance:* ${status}\n\nWedding: ${config.couple_headline}\nDate: ${config.wedding_date}`;
                window.open(`https://api.whatsapp.com/send?phone=${phone}&text=${encodeURIComponent(msg)}`, '_blank');
            });
        }

        initAudio();
        console.log('Classic Gold theme with falling petals loaded successfully!');
    });
    </script>

</body>
</html>