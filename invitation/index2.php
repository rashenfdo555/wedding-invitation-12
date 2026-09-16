<?php
// invitation/index2.php - Romantic Pink Theme
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
$theme_id = $user['theme_id'] ?? 2;

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
    'primary_color' => $inv['primary_color'] ?? '#e8a1a1',
    'secondary_color' => $inv['secondary_color'] ?? '#fce4e4',
    'text_color' => $inv['text_color'] ?? '#3a2f2f',
    'bg_main_color' => $inv['bg_main_color'] ?? '#fffaf5',
    'bg_card_color' => $inv['bg_card_color'] ?? '#ffffff',
    'text_muted_color' => $inv['text_muted_color'] ?? '#6e6e6e',
    'text_light_gray' => $inv['text_light_gray'] ?? '#9e9e9e',
    'border_light_color' => $inv['border_light_color'] ?? '#e8d5d5',
    'map_bg_color' => $inv['map_bg_color'] ?? '#e8d0d0',
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Great+Vibes&family=Quicksand:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* ============================================================
           ROMANTIC PINK THEME - CUSTOM COLOR VARIABLES
           ============================================================ */
        :root {
            --primary-color: <?php echo htmlspecialchars($config['primary_color']); ?>;
            --secondary-color: <?php echo htmlspecialchars($config['secondary_color']); ?>;
            --text-primary: <?php echo htmlspecialchars($config['text_color']); ?>;
            --text-muted: <?php echo htmlspecialchars($config['text_muted_color']); ?>;
            --text-light-gray: <?php echo htmlspecialchars($config['text_light_gray']); ?>;
            --bg-main: <?php echo htmlspecialchars($config['bg_main_color']); ?>;
            --bg-card: <?php echo htmlspecialchars($config['bg_card_color']); ?>;
            --border-light: <?php echo htmlspecialchars($config['border_light_color']); ?>;
            --shadow-xs: 0 1px 3px rgba(0,0,0,0.04);
            --shadow-sm: 0 4px 16px rgba(0,0,0,0.06);
            --shadow-md: 0 8px 32px rgba(0,0,0,0.08);
            --shadow-lg: 0 20px 60px rgba(0,0,0,0.12);
            --shadow-xl: 0 30px 80px rgba(0,0,0,0.16);
            --radius-sm: 8px;
            --radius-md: 16px;
            --radius-lg: 24px;
            --radius-xl: 36px;
            --transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            --font-serif: 'Playfair Display', serif;
            --font-sans: 'Inter', sans-serif;
            --font-script: 'Great Vibes', cursive;
            --font-quicksand: 'Quicksand', sans-serif;
        }

        /* ============================================================
           FALLING PETALS ANIMATION
           ============================================================ */
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
            top: -20px;
            width: 12px;
            height: 12px;
            background: var(--secondary-color);
            border-radius: 50% 0 50% 50%;
            opacity: 0.6;
            animation: fall linear infinite;
            transform: rotate(0deg);
        }

        .petal::before {
            content: '🌸';
            font-size: 16px;
            display: block;
            transform: scale(0.6);
        }

        @keyframes fall {
            0% {
                transform: translateY(0) rotate(0deg) scale(0.6);
                opacity: 0.7;
            }
            100% {
                transform: translateY(110vh) rotate(720deg) scale(1.2);
                opacity: 0.1;
            }
        }

        /* Different petal shapes using pseudo-elements */
        .petal:nth-child(odd)::before {
            content: '🌺';
            font-size: 14px;
        }

        .petal:nth-child(3n)::before {
            content: '🌷';
            font-size: 12px;
        }

        .petal:nth-child(5n+2)::before {
            content: '🌹';
            font-size: 15px;
        }

        .petal:nth-child(7n+4)::before {
            content: '🌻';
            font-size: 13px;
        }

        .petal:nth-child(11n+6)::before {
            content: '🌼';
            font-size: 14px;
        }

        /* ============================================================
           RESET & BASE
           ============================================================ */
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
            font-family: var(--font-sans);
            background: var(--bg-main);
            color: var(--text-primary);
            overflow-x: hidden;
            min-height: 100vh;
            line-height: 1.6;
        }

        .invitation-wrapper {
            max-width: 100%;
            margin: 0 auto;
            background: var(--bg-card);
            min-height: 100vh;
            position: relative;
        }

        /* ============================================================
           HERO SECTION - FULL WIDTH FIX
           ============================================================ */
        .hero-section {
    position: relative;
    min-height: 100vh;
    width: 100%;
    background-image: url('<?php echo getMediaPath($config['hero_image'], 'images', $config['username']); ?>');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: #ffffff;
    padding: 60px 24px 40px;
    overflow: hidden;
}

        .hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to bottom, rgba(0,0,0,0.35) 0%, rgba(0,0,0,0.15) 50%, rgba(0,0,0,0.45) 100%);
    z-index: 1;
}

        .hero-content {
            position: relative;
            z-index: 2;
            animation: fadeInUp 1.4s ease;
            max-width: 800px;
        }

        .hero-tag {
            font-size: 0.70rem;
            letter-spacing: 6px;
            font-weight: 300;
            opacity: 0.8;
            text-transform: uppercase;
            margin-bottom: 16px;
            font-family: var(--font-sans);
        }

        .hero-names {
            font-family: var(--font-script);
            font-size: 4.2rem;
            font-weight: 400;
            line-height: 1.1;
            margin: 16px 0 8px;
            text-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }

        .hero-ampersand {
            font-family: var(--font-serif);
            font-size: 2.8rem;
            font-weight: 300;
            margin: 0 6px;
            opacity: 0.7;
        }

        .hero-sub {
            font-family: var(--font-sans);
            font-size: 1.1rem;
            font-weight: 300;
            margin: 8px 0 20px;
            opacity: 0.9;
            letter-spacing: 4px;
            text-transform: uppercase;
        }

        .hero-date {
            font-size: 0.95rem;
            letter-spacing: 2px;
            margin: 16px 0 28px;
            opacity: 0.85;
            font-weight: 300;
        }

        .hero-date strong {
            font-size: 1.3rem;
            font-weight: 600;
            display: block;
            margin-top: 4px;
            letter-spacing: 1px;
        }

        .hero-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 8px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 16px 40px;
            border-radius: 50px;
            font-family: var(--font-sans);
            font-weight: 600;
            font-size: 0.75rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            min-width: 160px;
        }

        .btn-primary {
            background: var(--primary-color);
            color: #ffffff;
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background: <?php 
                $color = $config['primary_color'];
                $r = hexdec(substr($color, 1, 2));
                $g = hexdec(substr($color, 3, 2));
                $b = hexdec(substr($color, 5, 2));
                $r = max(0, $r - 25);
                $g = max(0, $g - 25);
                $b = max(0, $b - 25);
                echo '#' . sprintf("%02x%02x%02x", $r, $g, $b);
            ?>;
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.25);
        }

        .btn-secondary {
            background: rgba(255,255,255,0.12);
            color: #ffffff;
            border-color: rgba(255,255,255,0.3);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        .btn-secondary:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-3px);
            border-color: rgba(255,255,255,0.5);
        }

        .scroll-indicator {
            position: absolute;
            bottom: 32px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            z-index: 3;
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }

        .scroll-indicator:hover {
            opacity: 1;
        }

        .scroll-indicator p {
            font-size: 0.55rem;
            letter-spacing: 4px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .scroll-indicator i {
            animation: bounceArrow 2s infinite;
            font-size: 1rem;
        }

        @keyframes bounceArrow {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(8px); }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ============================================================
           AUDIO FAB - ROMANTIC PINK
           ============================================================ */
        .audio-fab {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            border: none;
            background: var(--primary-color);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            color: #ffffff;
            cursor: pointer;
            z-index: 500;
            box-shadow: 0 4px 24px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255,255,255,0.15);
        }

        .audio-fab:hover {
            transform: scale(1.08);
            background: var(--primary-color);
        }

        .audio-fab .bars {
            display: flex;
            align-items: flex-end;
            gap: 3px;
            width: 22px;
            height: 20px;
        }

        .audio-fab .bars span {
            display: block;
            width: 3px;
            height: 100%;
            background: #ffffff;
            transform-origin: bottom;
            animation: barDance 0.8s ease-in-out infinite alternate;
            border-radius: 2px;
        }

        .audio-fab .bars span:nth-child(1) { animation-delay: -0.1s; height: 60%; }
        .audio-fab .bars span:nth-child(2) { animation-delay: -0.4s; height: 100%; }
        .audio-fab .bars span:nth-child(3) { animation-delay: -0.2s; height: 70%; }
        .audio-fab .bars span:nth-child(4) { animation-delay: -0.6s; height: 85%; }

        @keyframes barDance {
            0% { transform: scaleY(0.2); }
            100% { transform: scaleY(1); }
        }

        .audio-fab.muted .bars { display: none; }
        .audio-fab.muted .mute-icon { display: block; }
        .audio-fab:not(.muted) .mute-icon { display: none; }
        .audio-fab:not(.muted) .bars { display: flex; }

        .audio-fab .mute-icon {
            font-size: 1.2rem;
        }

        /* ============================================================
           SECTION COMMON
           ============================================================ */
        section {
            padding: 80px 24px;
            text-align: center;
            max-width: 1100px;
            margin: 0 auto;
        }

        .section-tag {
            font-size: 0.6rem;
            letter-spacing: 6px;
            color: var(--primary-color);
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 12px;
            opacity: 0.6;
        }

        .section-title {
            font-family: var(--font-serif);
            font-size: 3rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--text-primary);
            line-height: 1.2;
        }

        .section-divider {
            width: 60px;
            height: 3px;
            background: var(--primary-color);
            margin: 0 auto 40px;
            opacity: 0.3;
            border-radius: 3px;
        }

        .card {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            padding: 50px 40px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-light);
            margin-top: 20px;
            transition: var(--transition);
        }

        .card:hover {
            box-shadow: var(--shadow-md);
        }

        /* ============================================================
           PROFILES - MODERN ROUND CARD LAYOUT
           ============================================================ */
        .profiles-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 10px;
            max-width: 100%;
            margin-left: auto;
            margin-right: auto;
        }

        .profile-card {
            text-align: center;
            position: relative;
        }

        .profile-card .img-wrap {
            width: 400px;
            height: 400px;
            border-radius: 50%;
            overflow: hidden;
            margin: 0 auto 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            position: relative;
            border: 4px solid var(--bg-card);
            outline: 2px solid var(--border-light);
            transition: var(--transition);
        }

        .profile-card .img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .profile-card:hover .img-wrap {
            transform: scale(1.02);
            box-shadow: 0 12px 48px rgba(0,0,0,0.15);
        }

        .profile-card:hover .img-wrap img {
            transform: scale(1.05);
        }

        .profile-card .label {
            font-size: 0.6rem;
            letter-spacing: 4px;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            margin-top: 4px;
        }

        .profile-card .name {
            font-family: var(--font-quicksand);
            font-size: 1.3rem;
            font-weight: 600;
            margin-top: 4px;
            color: var(--text-primary);
        }

        /* ============================================================
           GETTING MARRIED - ELEGANT
           ============================================================ */
        .getting-married {
            background: var(--bg-main);
            padding: 100px 24px;
            position: relative;
            max-width: 100%;
        }

        .getting-married .wedding-icon {
            color: var(--primary-color);
            font-size: 2.5rem;
            margin-bottom: 20px;
            display: block;
            opacity: 0.6;
            animation: pulseIcon 3s infinite;
        }

        @keyframes pulseIcon {
            0%, 100% { transform: scale(1); opacity: 0.6; }
            50% { transform: scale(1.1); opacity: 1; }
        }

        .getting-married .description {
            font-size: 1.15rem;
            color: var(--text-muted);
            line-height: 2;
            margin-bottom: 32px;
            font-weight: 300;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            font-family: var(--font-quicksand);
        }

        .getting-married .signature {
            font-family: var(--font-script);
            font-size: 3.5rem;
            color: var(--primary-color);
            font-weight: 400;
            animation: shimmerText 3s infinite;
        }

        @keyframes shimmerText {
            0%, 100% { opacity: 0.9; }
            50% { opacity: 1; text-shadow: 0 0 20px rgba(232, 161, 161, 0.2); }
        }

        /* ============================================================
           COUNTDOWN - MODERN CARDS
           ============================================================ */
        .countdown-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            margin-top: 10px;
        }

        .time-box {
            background: var(--bg-card);
            border-radius: var(--radius-sm);
            padding: 32px 16px;
            text-align: center;
            border: 2px solid var(--border-light);
            transition: var(--transition);
            box-shadow: var(--shadow-xs);
            animation: floatBox 4s ease-in-out infinite;
        }

        .time-box:nth-child(1) { animation-delay: 0s; }
        .time-box:nth-child(2) { animation-delay: 0.5s; }
        .time-box:nth-child(3) { animation-delay: 1s; }
        .time-box:nth-child(4) { animation-delay: 1.5s; }

        @keyframes floatBox {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-6px); }
        }

        .time-box:hover {
            border-color: var(--primary-color);
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }

        .time-box span {
            font-family: var(--font-quicksand);
            font-size: 3.2rem;
            font-weight: 700;
            color: var(--primary-color);
            display: block;
            line-height: 1;
            margin-bottom: -8px;
        }

        .time-box label {
            font-size: 0.55rem;
            color: var(--text-light-gray);
            letter-spacing: 3px;
            font-weight: 600;
            text-transform: uppercase;
        }

        /* ============================================================
           LOCATION - REFINED
           ============================================================ */
        .location-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 20px;
        }

        .location-info {
            text-align: center;
            padding: 45px 35px;
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-light);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .location-info:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-3px);
        }

        .location-info .venue-icon {
            width: 64px;
            height: 64px;
            min-width: 64px;
            border-radius: 50%;
            background: var(--primary-color);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 16px;
            animation: pulseIcon 3s infinite;
        }

        .location-info h3 {
            font-family: var(--font-serif);
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .location-info .venue-loc {
            font-size: 0.7rem;
            color: var(--primary-color);
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .location-info .venue-address {
            font-size: 0.95rem;
            color: var(--text-muted);
            line-height: 1.8;
            margin-bottom: 16px;
        }

        .location-info .time-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--secondary-color);
            padding: 10px 28px;
            border-radius: var(--radius-lg);
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 24px;
            color: var(--text-primary);
        }

        .location-info .venue-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 100%;
            max-width: 280px;
        }

        .location-info .venue-actions .btn {
            width: 100%;
            padding: 14px 24px;
            font-size: 0.7rem;
            min-width: unset;
        }

        .btn-outline {
            background: transparent;
            color: var(--text-primary);
            border-color: var(--border-light);
        }

        .btn-outline:hover {
            background: var(--secondary-color);
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }

        .gcal-link {
            display: inline-block;
            font-size: 0.7rem;
            color: var(--text-muted);
            text-decoration: none;
            margin-top: 14px;
            transition: color 0.2s;
            font-weight: 500;
            border-bottom: 2px solid transparent;
        }

        .gcal-link:hover {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
        }

        .map-wrapper {
            border-radius: var(--radius-lg);
            overflow: hidden;
            height: 100%;
            min-height: 380px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-light);
            transition: var(--transition);
        }

        .map-wrapper:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-3px);
        }

        .map-wrapper iframe {
            width: 100%;
            height: 100%;
            min-height: 380px;
            border: 0;
        }

        /* ============================================================
           TIMELINE - NEW DESIGN WITH ANIMATIONS
           ============================================================ */
        .timeline-container {
            margin-top: 30px;
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 0;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
            padding-left: 20px;
            border-left: 3px solid var(--primary-color);
        }

        .timeline-item {
            position: relative;
            padding: 24px 30px 24px 40px;
            background: var(--bg-card);
            border-radius: var(--radius-md);
            margin-bottom: 24px;
            box-shadow: var(--shadow-xs);
            border: 1px solid var(--border-light);
            transition: var(--transition);
            text-align: left;
            opacity: 0;
            transform: translateX(-30px);
            animation: slideInTimeline 0.6s ease forwards;
        }

        .timeline-item:nth-child(1) { animation-delay: 0.1s; }
        .timeline-item:nth-child(2) { animation-delay: 0.2s; }
        .timeline-item:nth-child(3) { animation-delay: 0.3s; }
        .timeline-item:nth-child(4) { animation-delay: 0.4s; }
        .timeline-item:nth-child(5) { animation-delay: 0.5s; }
        .timeline-item:nth-child(6) { animation-delay: 0.6s; }
        .timeline-item:nth-child(7) { animation-delay: 0.7s; }

        @keyframes slideInTimeline {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .timeline-item:hover {
            transform: translateX(8px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary-color);
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -32px;
            top: 28px;
            width: 16px;
            height: 16px;
            background: var(--primary-color);
            border-radius: 50%;
            border: 3px solid var(--bg-card);
            box-shadow: 0 0 0 3px var(--primary-color);
            transition: var(--transition);
        }

        .timeline-item:hover::before {
            transform: scale(1.2);
            box-shadow: 0 0 0 6px var(--primary-color);
        }

        .timeline-item .timeline-number {
            display: none;
        }

        .timeline-content .time-stamp {
            display: inline-block;
            font-size: 0.6rem;
            background: var(--primary-color);
            color: #ffffff;
            padding: 4px 16px;
            border-radius: var(--radius-lg);
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .timeline-content h4 {
            font-family: var(--font-quicksand);
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 4px;
            color: var(--text-primary);
        }

        .timeline-content p {
            font-size: 0.9rem;
            color: var(--text-muted);
            font-weight: 300;
            line-height: 1.6;
        }

        .timeline-content .timeline-icon {
            display: none;
        }

        /* ============================================================
           FEATURE BUTTONS
           ============================================================ */
        .feature-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .feature-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 32px;
            border: 2px solid var(--border-light);
            border-radius: var(--radius-lg);
            background: var(--bg-card);
            color: var(--text-primary);
            font-family: var(--font-sans);
            font-size: 0.7rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            box-shadow: var(--shadow-xs);
        }

        .feature-btn:hover {
            border-color: var(--primary-color);
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .feature-btn.active {
            background: var(--primary-color);
            color: #ffffff;
            border-color: var(--primary-color);
        }

        .feature-btn i {
            font-size: 1rem;
        }

        /* ============================================================
           GALLERY - MASONRY STYLE
           ============================================================ */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-top: 20px;
        }

        .gallery-item {
            border-radius: var(--radius-sm);
            overflow: hidden;
            box-shadow: var(--shadow-xs);
            cursor: pointer;
            position: relative;
            aspect-ratio: 1/1;
            transition: var(--transition);
        }

        .gallery-item:nth-child(3n-1) {
            aspect-ratio: 1/1.2;
        }

        .gallery-item:nth-child(5n) {
            aspect-ratio: 1.2/1;
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .gallery-item:hover {
            transform: scale(1.02);
            box-shadow: var(--shadow-md);
            z-index: 2;
        }

        .gallery-item:hover img {
            transform: scale(1.08);
        }

        .gallery-item .overlay {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.25);
            opacity: 0;
            transition: opacity 0.4s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .gallery-item:hover .overlay {
            opacity: 1;
        }

        .gallery-item .overlay i {
            color: #ffffff;
            font-size: 2rem;
            opacity: 0.8;
        }

        .gallery-item .caption {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 24px 16px 16px;
            background: linear-gradient(to top, rgba(0,0,0,0.6), transparent);
            color: #fff;
            font-size: 0.8rem;
            opacity: 0;
            transition: opacity 0.4s ease;
            font-weight: 300;
        }

        .gallery-item:hover .caption {
            opacity: 1;
        }

        .empty-gallery {
            grid-column: 1 / -1;
            padding: 60px 20px;
            color: var(--text-light-gray);
            background: var(--bg-main);
            border-radius: var(--radius-sm);
            border: 2px dashed var(--border-light);
        }

        .empty-gallery i {
            font-size: 2.5rem;
            display: block;
            margin-bottom: 12px;
            opacity: 0.4;
        }

        /* ============================================================
           TABLE FINDER
           ============================================================ */
        .search-box {
            display: flex;
            gap: 14px;
            align-items: center;
            max-width: 600px;
            margin: 0 auto;
        }

        .search-box input {
            flex: 1;
            padding: 18px 24px;
            border: 2px solid var(--border-light);
            border-radius: var(--radius-lg);
            font-family: var(--font-sans);
            font-size: 1rem;
            outline: none;
            transition: all 0.3s ease;
            background: var(--bg-main);
            color: var(--text-primary);
            min-width: 0;
        }

        .search-box input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(232, 161, 161, 0.08);
        }

        .search-box input::placeholder {
            color: var(--text-light-gray);
            font-weight: 300;
        }

        .search-box .btn {
            padding: 18px 32px;
            white-space: nowrap;
            min-width: unset;
        }

        .table-result {
    margin-top: 28px;
    min-height: 60px;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.table-result .found {
    padding: 30px 20px;
    background: #ffffff;
    border-radius: var(--radius-xl);
    border: 1px solid var(--border-light);
    text-align: center;
    transition: var(--transition);
    animation: slideInResult 0.4s ease;
    margin-bottom: 12px;
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
    border-color: var(--primary-color);
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}

.table-result .found .guest-name {
    font-family: var(--font-quicksand);
    font-size: 1.3rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 2px;
}

.table-result .found .table-number {
    font-size: 2.8rem;
    font-weight: 700;
    color: var(--primary-color);
    display: block;
    margin-top: 2px;
    font-family: var(--font-quicksand);
}

.table-result .found .family-name {
    color: var(--text-muted);
    font-size: 0.85rem;
    margin-top: 2px;
    margin-bottom: 6px;
}

.table-result .found .table-label {
    font-size: 0.55rem;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--text-light-gray);
    font-weight: 600;
    display: block;
}

.table-result .not-found {
    color: var(--text-light-gray);
    padding: 40px 20px;
    background: #ffffff;
    border-radius: var(--radius-sm);
    border: 2px dashed var(--border-light);
    animation: slideInResult 0.4s ease;
    text-align: center;
}

.table-result .not-found i {
    font-size: 2.2rem;
    display: block;
    margin-bottom: 12px;
    color: var(--border-light);
}

.table-result .not-found p {
    margin: 4px 0;
}

.table-result .not-found p:last-child {
    font-size: 0.8rem;
    margin-top: 4px;
    color: var(--text-muted);
}

        /* ============================================================
           RSVP FORM
           ============================================================ */
        .rsvp-form {
            text-align: left;
            max-width: 600px;
            margin: 0 auto;
        }

        .rsvp-form .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .rsvp-form .form-group {
            margin-bottom: 20px;
        }

        .rsvp-form .form-group.full-width {
            grid-column: 1 / -1;
        }

        .rsvp-form .form-group label {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            display: block;
            margin-bottom: 8px;
            color: var(--text-primary);
        }

        .rsvp-form .form-group label .required {
            color: var(--primary-color);
        }

        .rsvp-form .form-group input,
        .rsvp-form .form-group select {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid var(--border-light);
            border-radius: var(--radius-sm);
            font-family: var(--font-sans);
            font-size: 1rem;
            outline: none;
            transition: all 0.3s ease;
            background: var(--bg-main);
            color: var(--text-primary);
        }

        .rsvp-form .form-group input:focus,
        .rsvp-form .form-group select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(232, 161, 161, 0.08);
        }

        .rsvp-form .form-group select {
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23999' stroke-width='2' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 20px center;
            padding-right: 50px;
            cursor: pointer;
        }

        .rsvp-form .btn {
            width: 100%;
            margin-top: 6px;
            padding: 18px;
            font-size: 0.75rem;
        }

        /* ============================================================
           FOOTER
           ============================================================ */
        footer {
            text-align: center;
            padding: 60px 24px 40px;
            border-top: 1px solid var(--border-light);
            background: var(--bg-main);
            max-width: 1100px;
            margin: 0 auto;
        }

        footer .footer-headline {
            font-family: var(--font-script);
            font-size: 2.4rem;
            margin-bottom: 8px;
            color: var(--text-primary);
        }

        footer .footer-divider {
            width: 30px;
            height: 2px;
            background: var(--border-light);
            margin: 14px auto;
        }

        footer .footer-copy {
            font-size: 0.6rem;
            color: var(--text-light-gray);
            letter-spacing: 3px;
            font-weight: 300;
        }

        /* ============================================================
           REVEAL ANIMATION
           ============================================================ */
        .reveal {
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 0.9s cubic-bezier(0.215, 0.61, 0.355, 1),
                        transform 0.9s cubic-bezier(0.215, 0.61, 0.355, 1);
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        /* ============================================================
           RESPONSIVE DESIGN
           ============================================================ */
        @media (max-width: 992px) {
            .location-wrapper {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .map-wrapper {
                min-height: 300px;
            }

            .map-wrapper iframe {
                min-height: 300px;
            }

            .gallery-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .gallery-item:nth-child(3n-1),
            .gallery-item:nth-child(5n) {
                aspect-ratio: 1/1;
            }

            .hero-names {
                font-size: 3.4rem;
            }
        }

        @media (max-width: 768px) {
            .hero-section {
                min-height: 100vh;
                padding: 40px 20px 30px;
                background-attachment: scroll;
                width: 100%;
                margin-left: 0;
                margin-right: 0;
            }

            .hero-names {
                font-size: 2.8rem;
            }

            .hero-ampersand {
                font-size: 2rem;
            }

            .hero-sub {
                font-size: 0.85rem;
            }

            .hero-buttons {
                flex-direction: column;
                width: 100%;
                max-width: 320px;
                margin: 0 auto;
            }

            .hero-buttons .btn {
                width: 100%;
                min-width: unset;
            }

            section {
                padding: 60px 16px;
            }

            .section-title {
                font-size: 2.2rem;
            }

            .profiles-grid {
                grid-template-columns: 1fr 1fr;
                gap: 24px;
            }

            .profile-card .img-wrap {
                width: 200px;
                height: 200px;
            }

            .profile-card .name {
                font-size: 1.1rem;
            }

            .countdown-grid {
                gap: 12px;
            }

            .time-box {
                padding: 24px 12px;
            }

            .time-box span {
                font-size: 2.4rem;
            }

            .card {
                padding: 30px 20px;
            }

            .location-info {
                padding: 35px 24px;
            }

            .location-info h3 {
                font-size: 1.5rem;
            }

            .gallery-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }

            .feature-btn {
                padding: 14px 24px;
                font-size: 0.65rem;
                gap: 8px;
            }

            .getting-married {
                padding: 70px 16px;
            }

            .getting-married .signature {
                font-size: 2.6rem;
            }

            .rsvp-form .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .search-box {
                flex-direction: column;
            }

            .search-box .btn {
                width: 100%;
            }

            .timeline-item {
                padding: 20px 20px 20px 30px;
                margin-bottom: 18px;
            }

            .timeline-item::before {
                left: -24px;
                top: 22px;
                width: 14px;
                height: 14px;
            }

            .timeline-container {
                padding-left: 16px;
            }

            .timeline-content h4 {
                font-size: 1.05rem;
            }

            .audio-fab {
                width: 48px;
                height: 48px;
                bottom: 20px;
                right: 20px;
            }
        }

        @media (max-width: 480px) {
            .hero-names {
                font-size: 2.8rem;
            }

            .hero-ampersand {
                font-size: 1.6rem;
            }

            .hero-sub {
                font-size: 0.7rem;
                letter-spacing: 2px;
            }

            .hero-date {
                font-size: 0.8rem;
            }

            .hero-date strong {
                font-size: 1.6rem;
            }

            .btn {
                padding: 14px 24px;
                font-size: 0.65rem;
            }

            .section-title {
                font-size: 1.8rem;
            }

            .profiles-grid {
                grid-template-columns: 1fr 1fr;
                gap: 16px;
            }

            .profile-card .img-wrap {
                width: 150px;
                height: 150px;
            }

            .profile-card .name {
                font-size: 0.95rem;
            }

            .profile-card .label {
                font-size: 0.5rem;
                letter-spacing: 3px;
            }

            .countdown-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 8px;
            }

            .time-box {
                padding: 16px 8px;
                border-radius: var(--radius-md);
            }

            .time-box span {
                font-size: 1.8rem;
            }

            .time-box label {
                font-size: 0.45rem;
                letter-spacing: 2px;
            }

            .gallery-grid {
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }

            .feature-buttons {
                flex-direction: column;
                align-items: center;
            }

            .feature-btn {
                width: 100%;
                justify-content: center;
                padding: 14px 20px;
            }

            .location-info .venue-icon {
                width: 52px;
                height: 52px;
                min-width: 52px;
                font-size: 1.2rem;
            }

            .location-info h3 {
                font-size: 1.3rem;
            }

            .map-wrapper {
                min-height: 200px;
            }

            .map-wrapper iframe {
                min-height: 200px;
            }

            .getting-married .signature {
                font-size: 2rem;
            }

            .getting-married .description {
                font-size: 1rem;
            }

            .timeline-item {
                padding: 16px 16px 16px 24px;
                margin-bottom: 14px;
            }

            .timeline-item::before {
                left: -18px;
                top: 18px;
                width: 12px;
                height: 12px;
            }

            .timeline-container {
                padding-left: 12px;
            }

            .timeline-content .time-stamp {
                font-size: 0.5rem;
                padding: 3px 12px;
            }

            .timeline-content h4 {
                font-size: 0.95rem;
            }

            .timeline-content p {
                font-size: 0.8rem;
            }

            .audio-fab {
                width: 44px;
                height: 44px;
                bottom: 16px;
                right: 16px;
            }

            .audio-fab .bars {
                width: 18px;
                height: 16px;
            }

            .card {
                padding: 24px 16px;
            }

            .table-result .found {
                padding: 20px;
            }

            .table-result .found .guest-name {
                font-size: 1.1rem;
            }

            .table-result .found .table-number {
                font-size: 2.4rem;
            }
        }

        @media (max-width: 380px) {
            .hero-names {
                font-size: 1.8rem;
            }

            .profile-card .img-wrap {
                width: 120px;
                height: 120px;
            }

            .profile-card .name {
                font-size: 0.85rem;
            }

            .time-box span {
                font-size: 1.4rem;
            }

            .section-title {
                font-size: 1.5rem;
            }

            .countdown-grid {
                gap: 6px;
            }

            .time-box {
                padding: 12px 6px;
            }
        }

        @media (min-width: 1200px) {
            section {
                padding: 100px 40px;
            }

            .hero-names {
                font-size: 5.5rem;
            }

            .hero-buttons {
                gap: 20px;
            }

            .hero-buttons .btn {
                min-width: 200px;
            }

            .gallery-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 20px;
            }

            .gallery-item:nth-child(5n) {
                grid-column: span 2;
                aspect-ratio: 2/1;
            }

            .gallery-item:nth-child(3n-1) {
                aspect-ratio: 1/1;
            }

            .profile-card .img-wrap {
                width: 500px;
                height: 500px;
            }
        }

        @media (min-width: 1400px) {
            .hero-names {
                font-size: 6.5rem;
            }
        }
    </style>
</head>
<body>

    <!-- ============================================================
    FALLING PETALS CONTAINER
    ============================================================ -->
    <div class="petals-container" id="petalsContainer"></div>

    <div class="invitation-wrapper">

        <!-- ============================================================
        HERO SECTION
        ============================================================ -->
        <section class="hero-section">
            <div class="hero-overlay"></div>
            <div class="hero-content">
                <p class="hero-tag">Together with their families</p>
                <h1 class="hero-names">
                    <?php echo htmlspecialchars($config['bride_name']); ?>
                    <span class="hero-ampersand"> <br> &amp; <br> </span>
                    <?php echo htmlspecialchars($config['groom_name']); ?>
                </h1>
                <p class="hero-sub">are getting married</p>
                <div class="hero-date">
                    <span>Join us on</span>
                    <strong><?php echo htmlspecialchars($config['wedding_date']); ?></strong>
                </div>
                <div class="hero-buttons">
                    <a href="#rsvp" class="btn btn-primary"><i class="fa-regular fa-envelope"></i> RSVP</a>
                    <?php if ($config['has_table_finder']): ?>
                    <a href="#table" class="btn btn-secondary"><i class="fa-solid fa-magnifying-glass"></i> Find My Seat</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="scroll-indicator" id="scrollIndicator">
                <p>Scroll</p>
                <i class="fa-solid fa-chevron-down"></i>
            </div>

            <button id="audioToggle" class="audio-fab muted">
                <div class="bars">
                    <span></span><span></span><span></span><span></span>
                </div>
                <i class="fa-solid fa-volume-xmark mute-icon"></i>
            </button>
        </section>

        <!-- ============================================================
        PROFILES SECTION - ROUND IMAGES, SIDE BY SIDE
        ============================================================ -->
        <?php if ($config['show_profiles'] == 1): ?>
        <section class="reveal" id="mainContent">
            <p class="section-tag">Meet The</p>
            <h2 class="section-title">Wedding Party</h2>
            <div class="section-divider"></div>
            <div class="profiles-grid">
                <div class="profile-card">
                    <div class="img-wrap">
                        <img src="<?php echo getMediaPath($config['bride_image'], 'images', $config['username']); ?>" alt="The Bride">
                    </div>
                    <p class="label">The Bride</p>
                    <p class="name"><?php echo htmlspecialchars($config['bride_name']); ?></p>
                </div>
                <div class="profile-card">
                    <div class="img-wrap">
                        <img src="<?php echo getMediaPath($config['groom_image'], 'images', $config['username']); ?>" alt="The Groom">
                    </div>
                    <p class="label">The Groom</p>
                    <p class="name"><?php echo htmlspecialchars($config['groom_name']); ?></p>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- ============================================================
        GETTING MARRIED
        ============================================================ -->
        <section class="getting-married reveal">
            <i class="fa-solid fa-glass-cheers wedding-icon"></i>
            <p class="section-tag">We Are</p>
            <h2 class="section-title">Getting Married</h2>
            <div class="section-divider"></div>
            <p class="description">
                <?php echo nl2br(htmlspecialchars($config['sub_headline'])); ?>
            </p>
            <p class="signature">
                <?php echo htmlspecialchars($config['bride_name']); ?> &amp; <?php echo htmlspecialchars($config['groom_name']); ?>
            </p>
        </section>

        <!-- ============================================================
        COUNTDOWN - ONE LINE ALL TIMES
        ============================================================ -->
        <section class="reveal">
            <p class="section-tag">Counting Down</p>
            <h2 class="section-title">To Our Big Day</h2>
            <div class="section-divider"></div>
            <div class="countdown-grid" id="countdownGrid">
                <div class="time-box">
                    <span id="days">00</span>
                    <label>Days</label>
                </div>
                <div class="time-box">
                    <span id="hours">00</span>
                    <label>Hours</label>
                </div>
                <div class="time-box">
                    <span id="minutes">00</span>
                    <label>Minutes</label>
                </div>
                <div class="time-box">
                    <span id="seconds">00</span>
                    <label>Seconds</label>
                </div>
            </div>
        </section>

        <!-- ============================================================
        LOCATION - CENTERED
        ============================================================ -->
        <section class="reveal">
            <p class="section-tag">Join Us At</p>
            <h2 class="section-title">Location</h2>
            <div class="section-divider"></div>
            <div class="location-wrapper">
                <div class="location-info">
                    <div class="venue-icon">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <h3><?php echo htmlspecialchars($config['venue_title']); ?></h3>
                    <p class="venue-loc"><?php echo htmlspecialchars($config['venue_location']); ?></p>
                    <p class="venue-address"><?php echo htmlspecialchars($config['venue_address']); ?></p>
                    <div class="time-badge">
                        <i class="fa-regular fa-clock"></i>
                        <?php echo htmlspecialchars($config['event_time']); ?>
                    </div>
                    <div class="venue-actions">
                        <a href="<?php echo htmlspecialchars($config['google_maps_url']); ?>" target="_blank" class="btn btn-primary">
                            <i class="fa-solid fa-location-dot"></i> Open in Maps
                        </a>
                        <button class="btn btn-outline" id="addToCalendar">
                            <i class="fa-regular fa-calendar-plus"></i> Add to Calendar
                        </button>
                    </div>
                    <a href="#" class="gcal-link" id="googleCalHint"><i class="fa-regular fa-calendar"></i> Or add to Google Calendar</a>
                </div>
                <div class="map-wrapper">
                    <iframe 
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
        </section>

        <!-- ============================================================
        TIMELINE - NEW DESIGN WITH ANIMATIONS
        ============================================================ -->
        <section class="reveal">
            <p class="section-tag">Our Celebration</p>
            <h2 class="section-title">Timeline</h2>
            <div class="section-divider"></div>
            <div class="timeline-container">
                <?php 
                $icons = ['fa-location-dot', 'fa-heart', 'fa-microphone', 'fa-utensils', 'fa-music', 'fa-wand-magic-sparkles', 'fa-moon'];
                foreach($config['schedule'] as $index => $item): 
                    $icon = !empty($item['icon']) ? $item['icon'] : (isset($icons[$index]) ? $icons[$index] : 'fa-star');
                    $number = str_pad($index + 1, 2, '0', STR_PAD_LEFT);
                ?>
                <div class="timeline-item">
                    <div class="timeline-content">
                        <span class="time-stamp"><?php echo htmlspecialchars($item['time']); ?></span>
                        <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                        <p><?php echo htmlspecialchars($item['description']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- ============================================================
        FEATURE BUTTONS
        ============================================================ -->
        <?php if ($config['has_gallery'] || $config['has_table_finder']): ?>
        <section class="reveal">
            <div class="feature-buttons">
                <?php if ($config['has_gallery']): ?>
                <button class="feature-btn" id="galleryToggle">
                    <i class="fa-regular fa-images"></i> Gallery
                </button>
                <?php endif; ?>
                <?php if ($config['has_table_finder']): ?>
                <button class="feature-btn" id="tableFinderToggle">
                    <i class="fa-solid fa-magnifying-glass"></i> Find Table
                </button>
                <?php endif; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- ============================================================
        GALLERY SECTION
        ============================================================ -->
        <?php if ($config['has_gallery']): ?>
        <section class="reveal" id="gallerySection" style="display: none;">
            <p class="section-tag">Our Memories</p>
            <h2 class="section-title">Gallery</h2>
            <div class="section-divider"></div>
            <div class="gallery-grid">
                <?php if (empty($config['gallery_images'])): ?>
                    <div class="empty-gallery">
                        <i class="fa-regular fa-image"></i>
                        <p>No gallery images yet</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($config['gallery_images'] as $img): ?>
                    <div class="gallery-item">
                        <img src="<?php echo getMediaPath($img['image_path'], 'images', $config['username']); ?>" 
                             alt="<?php echo htmlspecialchars($img['caption'] ?? 'Wedding memory'); ?>"
                             loading="lazy"
                             onerror="this.style.background='#f0e8e0'; this.src='';">
                        <div class="overlay">
                            <i class="fa-regular fa-eye"></i>
                        </div>
                        <?php if (!empty($img['caption'])): ?>
                        <div class="caption"><?php echo htmlspecialchars($img['caption']); ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- ============================================================
        TABLE FINDER SECTION
        ============================================================ -->
        <?php if ($config['has_table_finder']): ?>
        <section class="reveal" id="table" style="display: none;">
            <p class="section-tag">Find Your</p>
            <h2 class="section-title">Table</h2>
            <div class="section-divider"></div>
            <div class="card">
                <div class="search-box">
                    <input type="text" id="tableSearchInput" placeholder="Enter your name...">
                    <button class="btn btn-primary" id="tableSearchBtn">
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

        <!-- ============================================================
        RSVP SECTION
        ============================================================ -->
        <section class="reveal" id="rsvp">
            <p class="section-tag">Join Us</p>
            <h2 class="section-title">RSVP</h2>
            <div class="section-divider"></div>
            <p style="color:var(--text-muted); margin-bottom:28px; font-weight:300; max-width:400px; margin-left:auto; margin-right:auto;">
                Please let us know if you can join our celebration
            </p>
            <div class="card">
                <form id="rsvpForm" class="rsvp-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Your Name <span class="required">*</span></label>
                            <input type="text" id="guestName" placeholder="Enter your full name" required>
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" id="guestEmail" placeholder="your.email@example.com">
                        </div>
                    </div>
                    <div class="form-group full-width">
                        <label>Will you attend? <span class="required">*</span></label>
                        <select id="attendanceStatus" required>
                            <option value="" disabled selected>Select Attendance</option>
                            <option value="Joyfully Accepts">Joyfully Accepts</option>
                            <option value="Regretfully Declines">Regretfully Declines</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit RSVP</button>
                </form>
            </div>
        </section>

        <!-- ============================================================
        FOOTER
        ============================================================ -->
        <footer>
            <p class="footer-headline"><?php echo htmlspecialchars($config['couple_headline']); ?></p>
            <div class="footer-divider"></div>
            <p class="footer-copy">&copy; <?php echo date('Y'); ?> · All Rights Reserved</p>
        </footer>

    </div>

    <!-- ============================================================
    JAVASCRIPT
    ============================================================ -->
    <script>
        window.invitationConfig = <?php echo json_encode($config); ?>;
        window.tableData = <?php echo json_encode($table_entries); ?>;

        document.addEventListener('DOMContentLoaded', function() {

            // ============================================================
            // FALLING PETALS / FLOWERS ANIMATION
            // ============================================================
            function createPetals() {
                const container = document.getElementById('petalsContainer');
                const petalCount = window.innerWidth < 768 ? 15 : 30;

                for (let i = 0; i < petalCount; i++) {
                    const petal = document.createElement('div');
                    petal.className = 'petal';
                    
                    // Random properties
                    const left = Math.random() * 100;
                    const delay = Math.random() * 10;
                    const duration = 8 + Math.random() * 12;
                    const size = 10 + Math.random() * 14;
                    
                    petal.style.left = left + '%';
                    petal.style.animationDelay = delay + 's';
                    petal.style.animationDuration = duration + 's';
                    petal.style.width = size + 'px';
                    petal.style.height = size + 'px';
                    
                    // Random rotation starting point
                    petal.style.transform = `rotate(${Math.random() * 360}deg)`;
                    
                    // Random opacity
                    petal.style.opacity = 0.3 + Math.random() * 0.5;
                    
                    container.appendChild(petal);
                }
            }

            // Create petals after a short delay to ensure styles are applied
            setTimeout(createPetals, 100);

            // ============================================================
            // SCROLL REVEAL
            // ============================================================
            const revealElements = document.querySelectorAll('.reveal');
            const revealObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('active');
                        revealObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });

            revealElements.forEach(el => revealObserver.observe(el));

            // ============================================================
            // SCROLL INDICATOR
            // ============================================================
            document.getElementById('scrollIndicator')?.addEventListener('click', function() {
                const target = document.getElementById('mainContent') || document.querySelector('.getting-married');
                if (target) target.scrollIntoView({ behavior: 'smooth' });
            });

            // ============================================================
            // AUDIO ENGINE
            // ============================================================
            let bgAudio = null;
            let audioLoaded = false;
            const audioToggle = document.getElementById('audioToggle');
            const config = window.invitationConfig;

            function initAudio() {
                try {
                    let audioPath = config.bg_audio || '../uploads/audio/intro-music.mp3';
                    if (!audioPath.includes('../uploads/') && !audioPath.includes('uploads/')) {
                        audioPath = '../uploads/audio/' + audioPath;
                    }
                    
                    bgAudio = new Audio(audioPath);
                    bgAudio.loop = true;
                    bgAudio.volume = 0.3;
                    bgAudio.preload = 'auto';

                    bgAudio.addEventListener('canplaythrough', function() {
                        audioLoaded = true;
                    });

                    bgAudio.addEventListener('playing', function() {
                        if (audioToggle) audioToggle.classList.remove('muted');
                    });

                    bgAudio.addEventListener('pause', function() {
                        if (audioToggle) audioToggle.classList.add('muted');
                    });

                    function tryPlay() {
                        if (bgAudio && bgAudio.paused && audioLoaded) {
                            bgAudio.play().catch(() => {});
                        }
                    }

                    document.addEventListener('click', tryPlay, { once: true });
                    document.addEventListener('scroll', tryPlay, { once: true });

                } catch (e) {
                    console.warn('Audio error:', e);
                }
            }

            if (audioToggle) {
                audioToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    if (bgAudio) {
                        if (bgAudio.paused) {
                            bgAudio.play().catch(() => {});
                        } else {
                            bgAudio.pause();
                        }
                    } else {
                        initAudio();
                        setTimeout(() => { if (bgAudio) bgAudio.play().catch(() => {}); }, 300);
                    }
                });
            }

            initAudio();

            // ============================================================
            // COUNTDOWN
            // ============================================================
            const targetDate = new Date(config.countdown_target).getTime();
            const daysEl = document.getElementById('days');
            const hoursEl = document.getElementById('hours');
            const minutesEl = document.getElementById('minutes');
            const secondsEl = document.getElementById('seconds');

            function updateCountdown() {
                const now = Date.now();
                const diff = targetDate - now;

                if (diff < 0) {
                    const grid = document.getElementById('countdownGrid');
                    if (grid) {
                        grid.innerHTML = `
                            <div style="grid-column:span 4; font-family:'Quicksand',sans-serif; font-size:1.4rem; color:var(--primary-color); padding:15px;">
                                💕 Celebration Commenced 💕
                            </div>
                        `;
                    }
                    return;
                }

                const d = Math.floor(diff / 86400000);
                const h = Math.floor((diff % 86400000) / 3600000);
                const m = Math.floor((diff % 3600000) / 60000);
                const s = Math.floor((diff % 60000) / 1000);

                if (daysEl) daysEl.textContent = String(d).padStart(2, '0');
                if (hoursEl) hoursEl.textContent = String(h).padStart(2, '0');
                if (minutesEl) minutesEl.textContent = String(m).padStart(2, '0');
                if (secondsEl) secondsEl.textContent = String(s).padStart(2, '0');
            }

            updateCountdown();
            setInterval(updateCountdown, 1000);

            // ============================================================
            // GALLERY TOGGLE
            // ============================================================
            const galleryBtn = document.getElementById('galleryToggle');
            const gallerySection = document.getElementById('gallerySection');
            const tableBtn = document.getElementById('tableFinderToggle');
            const tableSection = document.getElementById('table');

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

            // ============================================================
            // TABLE FINDER SEARCH
            // ============================================================
            const searchInput = document.getElementById('tableSearchInput');
            const searchBtn = document.getElementById('tableSearchBtn');
            const tableResult = document.getElementById('tableResult');
            const tableData = window.tableData || [];

            function escapeHtml(text) {
                if (!text) return '';
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            function searchTable() {
                const query = searchInput.value.trim().toLowerCase();
                if (!query) {
                    tableResult.innerHTML = `
                        <div class="not-found">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <p>Please enter your name</p>
                        </div>
                    `;
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
                <div>
                    <span class="table-label">Table</span>
                    <div class="table-number">${escapeHtml(result.table_number)}</div>
                </div>
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

            // ============================================================
            // CALENDAR
            // ============================================================
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

            // ============================================================
            // RSVP FORM
            // ============================================================
            const rsvpForm = document.getElementById('rsvpForm');
            if (rsvpForm) {
                rsvpForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const name = document.getElementById('guestName').value.trim();
                    const email = document.getElementById('guestEmail').value.trim();
                    const status = document.getElementById('attendanceStatus').value;
                    const phone = config.whatsapp_phone || '';

                    if (!name || !status) {
                        alert('Please complete all mandatory fields.');
                        return;
                    }

                    const msg = `*WEDDING RSVP CONFIRMATION*\n\n*Guest Name:* ${name}\n${email ? '*Email:* ' + email : ''}\n*Attendance:* ${status}\n\nWedding: ${config.couple_headline}\nDate: ${config.wedding_date}`;
                    window.open(`https://api.whatsapp.com/send?phone=${phone}&text=${encodeURIComponent(msg)}`, '_blank');
                });
            }

            // ============================================================
            // GALLERY IMAGE ANIMATION
            // ============================================================
            const galleryItems = document.querySelectorAll('.gallery-item');
            galleryItems.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'scale(0.92)';
                item.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            setTimeout(() => {
                                item.style.opacity = '1';
                                item.style.transform = 'scale(1)';
                            }, index * 50);
                            observer.unobserve(item);
                        }
                    });
                }, { threshold: 0.1 });
                observer.observe(item);
            });

            console.log('Romantic Pink theme loaded successfully!');
        });
    </script>

</body>
</html>