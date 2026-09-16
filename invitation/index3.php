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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <title><?php echo htmlspecialchars($config['couple_headline'] ?? 'Aarav & Diya'); ?> - Wedding Invitation</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@400;600;700&family=Great+Vibes&family=Marcellus&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    /* ----- ROYAL INDIAN VARIABLES (DB driven) ----- */
    :root {
      --maroon: <?php echo htmlspecialchars($config['primary_color'] ?? '#8B2635'); ?>;
      --gold: <?php echo htmlspecialchars($config['secondary_color'] ?? '#D4AF37'); ?>;
      --cream: <?php echo htmlspecialchars($config['bg_main_color'] ?? '#FDF7E7'); ?>;
      --dark: <?php echo htmlspecialchars($config['text_color'] ?? '#2D1418'); ?>;
      --text-muted: <?php echo htmlspecialchars($config['text_muted_color'] ?? '#6B4F5A'); ?>;
      --bg-card: <?php echo htmlspecialchars($config['bg_card_color'] ?? '#FFFFFF'); ?>;
      --primary-color: var(--maroon);
      --secondary-color: var(--gold);
      --text-primary: var(--dark);
      --bg-main: var(--cream);
      --border-light: rgba(212, 175, 55, 0.25);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: var(--bg-main);
      color: var(--text-primary);
      scroll-behavior: smooth;
      overflow-x: hidden;
      -webkit-font-smoothing: antialiased;
    }

    .font-serif { font-family: 'Marcellus', serif; }
    .font-script { font-family: 'Great Vibes', cursive; }
    .font-royal { font-family: 'Crimson Pro', serif; }

    /* ----- REVEAL ANIMATION ----- */
    .reveal {
      opacity: 0;
      transform: translateY(30px);
      transition: all 0.8s ease-out;
    }
    .reveal.active {
      opacity: 1;
      transform: translateY(0);
    }

    /* ----- BUTTONS ----- */
    .btn-royal {
      background: var(--maroon);
      color: #fff;
      border: 1px solid var(--gold);
      transition: all 0.3s ease;
      font-weight: 500;
      letter-spacing: 0.5px;
    }
    .btn-royal:hover {
      background: #6D1E29;
      transform: translateY(-2px);
      box-shadow: 0 10px 20px rgba(139, 38, 53, 0.2);
    }
    .btn-outline {
      background: transparent;
      color: var(--text-primary);
      border: 1.5px solid var(--gold);
    }
    .btn-outline:hover {
      background: var(--gold);
      color: var(--dark);
      transform: translateY(-2px);
    }

    /* ----- TIME BOX ----- */
    .time-box {
      background: rgba(255,255,255,0.08);
      backdrop-filter: blur(6px);
      border: 1.5px solid var(--gold);
      border-radius: 16px;
      padding: 20px 12px;
      text-align: center;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .time-box:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 24px rgba(212, 175, 55, 0.2);
    }
    .time-box span {
      font-family: 'Crimson Pro', serif;
      font-size: clamp(2rem, 5vw, 3.8rem);
      font-weight: 700;
      color: var(--gold);
      display: block;
      line-height: 1;
      letter-spacing: 2px;
    }
    .time-box label {
      font-size: 0.6rem;
      color: rgba(255,255,255,0.7);
      letter-spacing: 4px;
      font-weight: 500;
      text-transform: uppercase;
      margin-top: 6px;
      display: block;
    }

    /* ----- HERO ----- */
    .hero-section {
      position: relative;
      min-height: 100vh;
      width: 100%;
      background: linear-gradient(rgba(0, 0, 0, 0.45), rgba(0, 0, 0, 0.55)),
        url('<?php echo getMediaPath($config['hero_image'] ?? 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&q=80&w=1920', 'images', $config['username'] ?? ''); ?>') center/cover no-repeat;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      color: #fff;
      padding: 60px 24px 40px;
    }
    .hero-section .pattern-overlay {
      position: absolute;
      inset: 0;
      opacity: 0.05;
    }
    .hero-content {
      position: relative;
      z-index: 2;
      max-width: 100%;
      width: 100%;
    }
    .hero-tag {
      font-size: clamp(0.7rem, 1.2vw, 0.9rem);
      letter-spacing: 6px;
      text-transform: uppercase;
      opacity: 0.8;
      font-weight: 300;
      margin-bottom: 8px;
    }
    .hero-names {
      font-family: 'Great Vibes', cursive;
      font-size: clamp(3rem, 12vw, 7rem);
      font-weight: 400;
      line-height: 1.0;
      margin: 28px 0 36px;
      text-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
      letter-spacing: 2px;
    }
    .hero-ampersand {
      font-family: 'Crimson Pro', serif;
      font-size: clamp(2rem, 5vw, 4rem);
      font-weight: 300;
      opacity: 0.6;
      display: inline-block;
    }
    .hero-sub {
      font-size: clamp(0.8rem, 1.5vw, 1.1rem);
      font-weight: 300;
      letter-spacing: 6px;
      text-transform: uppercase;
      font-family: 'Marcellus', serif;
      opacity: 0.9;
      margin-bottom: 12px;
    }
    .hero-date {
      font-size: clamp(0.75rem, 1.2vw, 0.95rem);
      letter-spacing: 3px;
      font-weight: 300;
      opacity: 0.9;
    }
    .hero-date strong {
      font-size: clamp(1.4rem, 3vw, 2.2rem);
      font-weight: 600;
      display: block;
      margin-top: 4px;
      font-family: 'Marcellus', serif;
      letter-spacing: 2px;
    }
    .hero-buttons {
      display: flex;
      gap: 16px;
      justify-content: center;
      margin-top: 28px;
      flex-wrap: wrap;
    }
    .hero-buttons .btn {
      padding: 14px 36px;
      border-radius: 50px;
      font-weight: 600;
      font-size: 0.7rem;
      letter-spacing: 2px;
      text-transform: uppercase;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      transition: all 0.3s ease;
      border: 1.5px solid rgba(255,255,255,0.2);
      background: rgba(255,255,255,0.1);
      backdrop-filter: blur(4px);
      color: #fff;
      cursor: pointer;
    }
    .hero-buttons .btn-primary {
      background: var(--maroon);
      border-color: var(--gold);
      color: #fff;
    }
    .hero-buttons .btn-primary:hover {
      background: #6D1E29;
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(139, 38, 53, 0.3);
    }
    .hero-buttons .btn-secondary {
      background: rgba(255,255,255,0.08);
      border-color: rgba(255,255,255,0.25);
    }
    .hero-buttons .btn-secondary:hover {
      background: rgba(255,255,255,0.2);
      transform: translateY(-2px);
    }

    /* ----- SCROLL INDICATOR ----- */
    .scroll-indicator {
      position: absolute;
      bottom: 28px;
      left: 50%;
      transform: translateX(-50%);
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 6px;
      cursor: pointer;
      z-index: 3;
      opacity: 0.6;
      transition: opacity 0.3s ease;
    }
    .scroll-indicator:hover { opacity: 1; }
    .scroll-indicator p {
      font-size: 0.5rem;
      letter-spacing: 5px;
      font-weight: 500;
      text-transform: uppercase;
    }
    @keyframes bounceArrow {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(8px); }
    }
    .scroll-indicator iconify-icon {
      animation: bounceArrow 2s infinite;
      font-size: 1.2rem;
    }

    /* ----- AUDIO FAB ----- */
    .audio-fab {
      position: fixed;
      bottom: 30px;
      right: 30px;
      width: 56px;
      height: 56px;
      border-radius: 50%;
      border: 2px solid var(--gold);
      background: var(--maroon);
      color: #fff;
      cursor: pointer;
      z-index: 500;
      box-shadow: 0 4px 24px rgba(0, 0, 0, 0.25);
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .audio-fab:hover {
      transform: scale(1.08);
      box-shadow: 0 6px 32px rgba(212, 175, 55, 0.3);
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
      background: var(--gold);
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
    .audio-fab .mute-icon { font-size: 1.2rem; color: var(--gold); }

    /* ----- SECTION COMMON ----- */
    section {
      padding: 80px 24px;
      text-align: center;
      
      margin: 0 auto;
      position: relative;
    }
    .section-tag {
      font-size: 0.6rem;
      letter-spacing: 7px;
      color: var(--maroon);
      font-weight: 700;
      text-transform: uppercase;
      margin-bottom: 8px;
      opacity: 0.5;
    }
    .section-title {
      font-family: 'Marcellus', serif;
      font-size: clamp(2.2rem, 5vw, 3.6rem);
      font-weight: 600;
      margin-bottom: 16px;
      line-height: 1.15;
      letter-spacing: 1px;
    }
    .section-divider {
      width: 60px;
      height: 3px;
      background: var(--gold);
      margin: 0 auto 36px;
      border-radius: 3px;
    }
    .card {
      background: var(--bg-card);
      border-radius: 20px;
      padding: 44px 36px;
      box-shadow: 0 4px 24px rgba(0, 0, 0, 0.05);
      border: 1px solid var(--border-light);
      transition: all 0.3s ease;
    }
    .card:hover {
      box-shadow: 0 8px 48px rgba(0, 0, 0, 0.08);
    }

    /* ----- PROFILES ----- */
    .profiles-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 48px;
      margin-top: 20px;
    }
    .profile-card {
      text-align: center;
    }
    .profile-card .img-wrap {
      width: clamp(200px, 30vw, 320px);
      height: clamp(200px, 30vw, 320px);
      border-radius: 50%;
      overflow: hidden;
      margin: 0 auto 16px;
      box-shadow: 0 8px 40px rgba(0, 0, 0, 0.08);
      border: 6px solid var(--gold);
      transition: all 0.5s ease;
      position: relative;
    }
    .profile-card .img-wrap img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.7s ease;
    }
    .profile-card:hover .img-wrap {
      transform: scale(1.02);
      box-shadow: 0 12px 56px rgba(0, 0, 0, 0.12);
    }
    .profile-card:hover .img-wrap img {
      transform: scale(1.06);
    }
    .profile-card .label {
      display: inline-block;
      font-size: 0.55rem;
      letter-spacing: 4px;
      background: var(--maroon);
      color: #fff;
      padding: 4px 24px;
      border-radius: 30px;
      font-weight: 600;
      text-transform: uppercase;
      border: 1px solid var(--gold);
    }
    .profile-card .name {
      font-family: 'Great Vibes', cursive;
      font-size: clamp(1.8rem, 3.5vw, 2.8rem);
      font-weight: 400;
      margin-top: 10px;
      color: var(--maroon);
      letter-spacing: 1px;
    }
    .profile-card .bio {
      font-size: 0.85rem;
      color: var(--text-muted);
      font-weight: 300;
      max-width: 320px;
      margin: 4px auto 0;
      font-style: italic;
      line-height: 1.6;
    }

    /* ----- LOCATION ----- */
    .location-wrapper {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 40px;
      margin-top: 16px;
    }
    .location-info {
      text-align: center;
      padding: 44px 36px;
      background: var(--bg-card);
      border-radius: 20px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
      border: 2px solid var(--gold);
      transition: all 0.3s ease;
    }
    .location-info:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 40px rgba(0, 0, 0, 0.08);
    }
    .location-info .venue-icon {
      width: 72px;
      height: 72px;
      border-radius: 50%;
      background: var(--maroon);
      color: var(--gold);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2rem;
      margin: 0 auto 16px;
      border: 2px solid var(--gold);
    }
    .location-info h3 {
      font-family: 'Marcellus', serif;
      font-size: 1.8rem;
      font-weight: 600;
      color: var(--dark);
      letter-spacing: 0.5px;
    }
    .location-info .venue-loc {
      font-size: 0.7rem;
      color: var(--maroon);
      text-transform: uppercase;
      letter-spacing: 3px;
      font-weight: 600;
      margin-top: 2px;
    }
    .location-info .venue-address {
      font-size: 0.9rem;
      color: var(--text-muted);
      line-height: 1.8;
      margin: 8px 0 18px;
      font-weight: 300;
    }
    .location-info .time-badge {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      background: var(--cream);
      padding: 8px 28px;
      border-radius: 40px;
      font-size: 0.8rem;
      font-weight: 500;
      margin-bottom: 20px;
      border: 1px solid var(--gold);
    }
    .location-info .venue-actions {
      display: flex;
      flex-direction: column;
      gap: 12px;
      width: 100%;
      max-width: 300px;
      margin: 0 auto;
    }
    .location-info .venue-actions .btn {
      padding: 14px 24px;
      border-radius: 50px;
      font-weight: 600;
      font-size: 0.7rem;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      transition: all 0.3s ease;
      border: 1.5px solid var(--gold);
      cursor: pointer;
    }
    .location-info .venue-actions .btn-primary {
      background: var(--maroon);
      color: #fff;
    }
    .location-info .venue-actions .btn-primary:hover {
      background: #6D1E29;
      transform: translateY(-2px);
    }
    .location-info .venue-actions .btn-outline {
      background: transparent;
      color: var(--text-primary);
    }
    .location-info .venue-actions .btn-outline:hover {
      background: var(--gold);
      color: var(--dark);
      transform: translateY(-2px);
    }
    .map-wrapper {
      border-radius: 20px;
      overflow: hidden;
      height: 100%;
      min-height: 380px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
      border: 2px solid var(--gold);
    }
    .map-wrapper iframe {
      width: 100%;
      height: 100%;
      min-height: 380px;
      border: 0;
    }

    /* ----- TIMELINE ----- */
    .timeline-container {
      position: relative;
      padding-left: 36px;
      max-width: 820px;
      margin: 0 auto;
      text-align: left;
    }
    .timeline-container::before {
      content: '';
      position: absolute;
      left: 10px;
      top: 0;
      bottom: 0;
      width: 2.5px;
      background: linear-gradient(to bottom, var(--gold), var(--maroon));
    }
    .timeline-item {
      position: relative;
      padding: 24px 28px;
      margin-bottom: 24px;
      background: var(--bg-card);
      border-radius: 16px;
      box-shadow: 0 2px 16px rgba(0, 0, 0, 0.04);
      border-left: 4px solid var(--gold);
      transition: all 0.4s ease;
    }
    .timeline-item:hover {
      transform: translateX(10px);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
    }
    .timeline-item::before {
      content: '';
      position: absolute;
      left: -31px;
      top: 28px;
      width: 16px;
      height: 16px;
      border-radius: 50%;
      background: var(--maroon);
      border: 3px solid var(--gold);
      box-shadow: 0 0 0 4px #fff, 0 0 0 6px var(--gold);
    }
    .timeline-item .time-stamp {
      display: inline-block;
      font-size: 0.7rem;
      font-weight: 600;
      color: var(--maroon);
      letter-spacing: 1.5px;
      text-transform: uppercase;
      margin-bottom: 4px;
    }
    .timeline-item h4 {
      font-family: 'Marcellus', serif;
      font-size: 1.3rem;
      font-weight: 600;
      color: var(--dark);
      margin-bottom: 4px;
    }
    .timeline-item p {
      color: var(--text-muted);
      font-size: 0.9rem;
      font-weight: 300;
      line-height: 1.7;
      margin-bottom: 0;
    }

    /* ----- GALLERY MASONRY - TRUE COLLAGE WITH VARIED SIZES ----- */
    .gallery-masonry {
      column-count: 3;
      column-gap: 1.25rem;
    }
    @media (max-width: 1024px) {
      .gallery-masonry { column-count: 2; column-gap: 1rem; }
    }
    @media (max-width: 640px) {
      .gallery-masonry { column-count: 2; column-gap: 0.75rem; }
    }
    @media (max-width: 420px) {
      .gallery-masonry { column-count: 1; }
    }
    .gallery-item {
      break-inside: avoid;
      margin-bottom: 1.25rem;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
      transition: all 0.4s ease;
      background: var(--bg-card);
      border: 1px solid var(--border-light);
    }
    .gallery-item:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
    }
    .gallery-item img {
      width: 100%;
  height: 100%; /* Forces image to fill the container height */
  object-fit: cover; /* Crops and fits image perfectly without stretching */
  display: block;
  transition: transform 0.6s ease;
    }
    .gallery-item:hover img {
      transform: scale(1.03);
    }
    /* VARIED ASPECT RATIOS FOR COLLAGE EFFECT */
    .gallery-item:nth-child(1) img { aspect-ratio: 3/4; }
    .gallery-item:nth-child(2) img { aspect-ratio: 4/3; }
    .gallery-item:nth-child(3) img { aspect-ratio: 1/1; }
    .gallery-item:nth-child(4) img { aspect-ratio: 4/5; }
    .gallery-item:nth-child(5) img { aspect-ratio: 3/2; }
    .gallery-item:nth-child(6) img { aspect-ratio: 2/3; }
    .gallery-item:nth-child(7) img { aspect-ratio: 1/1; }
    .gallery-item:nth-child(8) img { aspect-ratio: 4/3; }
    .gallery-item:nth-child(9) img { aspect-ratio: 3/4; }
    .gallery-item:nth-child(10) img { aspect-ratio: 5/4; }
    .gallery-item:nth-child(11) img { aspect-ratio: 2/3; }
    .gallery-item:nth-child(12) img { aspect-ratio: 1/1; }
    .gallery-item:nth-child(13) img { aspect-ratio: 4/5; }
    .gallery-item:nth-child(14) img { aspect-ratio: 3/2; }
    .gallery-item:nth-child(15) img { aspect-ratio: 3/4; }

    /* ----- TABLE FINDER ----- */
    .search-box {
      display: flex;
      gap: 16px;
      align-items: center;
      max-width: 600px;
      margin: 0 auto;
    }
    .search-box input {
      flex: 1;
      padding: 16px 24px;
      border: 2px solid var(--border-light);
      border-radius: 14px;
      font-family: 'Poppins', sans-serif;
      font-size: 1rem;
      outline: none;
      transition: all 0.3s ease;
      background: var(--bg-main);
      color: var(--text-primary);
    }
    .search-box input:focus {
      border-color: var(--gold);
      box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.08);
    }
    .search-box .btn {
      padding: 16px 36px;
      white-space: nowrap;
      border-radius: 50px;
      font-weight: 600;
      font-size: 0.7rem;
      letter-spacing: 2px;
      text-transform: uppercase;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      cursor: pointer;
      transition: all 0.3s ease;
      border: 1.5px solid var(--gold);
      background: var(--gold);
      color: var(--dark);
    }
    .search-box .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(212, 175, 55, 0.25);
    }
    .table-result {
      margin-top: 28px;
      min-height: 70px;
    }
    .table-result .found {
      padding: 8px 12px;
      background: rgba(255,255,255,0.06);
      border-radius: 16px;
      border-left: 5px solid var(--gold);
      text-align: center;
      animation: slideInResult 0.4s ease;
      backdrop-filter: blur(4px);
    }
    @keyframes slideInResult {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .table-result .found .guest-name {
      font-family: 'Marcellus', serif;
      font-size: 1.4rem;
      font-weight: 400;
      color: var(--gold);
    }
    .table-result .found .table-number {
      font-size: 3rem;
      font-weight: 700;
      color: var(--gold);
      font-family: 'Crimson Pro', serif;
      line-height: 0.7;
    }
    .table-result .found .family-name {
      color: rgba(255,255,255,0.6);
      font-size: 0.85rem;
    }
    .table-result .found .table-label {
      font-size: 0.55rem;
      letter-spacing: 4px;
      text-transform: uppercase;
      color: rgba(255,255,255,0.4);
      font-weight: 600;
    }
    .table-result .not-found {
      color: rgba(255,255,255,0.5);
      padding: 36px;
      background: rgba(255,255,255,0.03);
      border-radius: 16px;
      border: 2px dashed rgba(212,175,55,0.2);
    }

    /* ----- RSVP FORM ----- */
    .rsvp-form {
      text-align: left;
      max-width: 640px;
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
      letter-spacing: 2.5px;
      text-transform: uppercase;
      display: block;
      margin-bottom: 8px;
      color: var(--text-primary);
    }
    .rsvp-form .form-group label .required {
      color: var(--maroon);
    }
    .rsvp-form .form-group input,
    .rsvp-form .form-group select,
    .rsvp-form .form-group textarea {
      width: 100%;
      padding: 14px 20px;
      border: 2px solid var(--border-light);
      border-radius: 14px;
      font-family: 'Poppins', sans-serif;
      font-size: 0.95rem;
      outline: none;
      transition: all 0.3s ease;
      background: var(--bg-main);
      color: var(--text-primary);
    }
    .rsvp-form .form-group input:focus,
    .rsvp-form .form-group select:focus,
    .rsvp-form .form-group textarea:focus {
      border-color: var(--gold);
      box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.06);
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
    .rsvp-form .btn-submit {
      padding: 18px;
      border-radius: 50px;
      font-weight: 600;
      letter-spacing: 2.5px;
      text-transform: uppercase;
      font-size: 0.75rem;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      cursor: pointer;
      transition: all 0.3s ease;
      width: 100%;
      border: 1.5px solid var(--gold);
      background: var(--maroon);
      color: #fff;
    }
    .rsvp-form .btn-submit:hover {
      background: #6D1E29;
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(139, 38, 53, 0.2);
    }

    /* ----- FOOTER ----- */
    footer {
      text-align: center;
      padding: 60px 24px 40px;
      border-top: 2px solid var(--gold);
      background: var(--maroon);
      color: #fff;
      max-width: 100%;
      margin: 0;
      position: relative;
    }
    footer .pattern-overlay {
      position: absolute;
      inset: 0;
      opacity: 0.04;
      background-image: url("https://www.transparenttextures.com/patterns/oriental-tiles.png");
    }
    footer .footer-headline {
      font-family: 'Great Vibes', cursive;
      font-size: clamp(2.4rem, 5vw, 4rem);
      margin-bottom: 6px;
      color: var(--gold);
      position: relative;
      z-index: 1;
      letter-spacing: 2px;
    }
    footer .footer-divider {
      width: 40px;
      height: 2px;
      background: var(--gold);
      margin: 14px auto;
      opacity: 0.4;
      position: relative;
      z-index: 1;
    }
    footer .footer-copy {
      font-size: 0.6rem;
      color: rgba(255, 255, 255, 0.3);
      letter-spacing: 4px;
      font-weight: 300;
      position: relative;
      z-index: 1;
    }

    /* ----- RESPONSIVE ----- */
    @media (max-width: 992px) {
      .location-wrapper {
        grid-template-columns: 1fr;
        gap: 30px;
      }
      .map-wrapper { min-height: 300px; }
      .map-wrapper iframe { min-height: 300px; }
      .profiles-grid { gap: 32px; }
    }
    @media (max-width: 768px) {
      .hero-section { padding: 40px 20px 30px; min-height: 100vh; }
      .hero-buttons { flex-direction: column; align-items: center; width: 100%; max-width: 320px; margin: 24px auto 0; }
      .hero-buttons .btn { width: 100%; justify-content: center; padding: 14px 24px; }
      section { padding: 60px 16px; }
      .profiles-grid { grid-template-columns: 1fr; gap: 48px; }
      .countdown-grid { gap: 12px; }
      .time-box { padding: 16px 10px; }
      .time-box span { font-size: 1.8rem; }
      .rsvp-form .form-row { grid-template-columns: 1fr; gap: 0; }
      .search-box { flex-direction: column; }
      .search-box .btn { width: 100%; justify-content: center; }
      .gallery-masonry { column-count: 2; column-gap: 0.75rem; }
      .gallery-item { margin-bottom: 0.75rem; border-radius: 12px; }
      .audio-fab { bottom: 20px; right: 20px; width: 50px; height: 50px; }
      .timeline-container { padding-left: 24px; }
      .timeline-item { padding: 18px 20px; margin-bottom: 18px; }
      .timeline-item::before { left: -20px; top: 22px; width: 14px; height: 14px; }
      .location-info { padding: 32px 20px; }
      .card { padding: 28px 18px; }
      .profile-card .img-wrap { width: 180px; height: 180px; }
    }
    @media (max-width: 480px) {
      .hero-names { font-size: 3.6rem; }
      .hero-ampersand {
      font-family: 'Crimson Pro', serif;
      font-size: clamp(3rem, 5vw, 4rem);
      font-weight: 300;
      opacity: 0.6;
      display: inline-block;

    }
      .countdown-grid { grid-template-columns: repeat(4, 1fr); gap: 8px; }
      .time-box { padding: 12px 6px; }
      .time-box span { font-size: 1.4rem; }
      .time-box label { font-size: 0.45rem; letter-spacing: 2px; }
      .gallery-masonry { column-count: 2; column-gap: 0.8rem; }
      .section-title { font-size: 1.8rem; }
      .profile-card .img-wrap { width: 150px; height: 150px; }
      .audio-fab { width: 44px; height: 44px; bottom: 16px; right: 16px; }
      .hero-buttons { max-width: 260px; }
      .hero-sub {
      font-size: clamp(0.8rem, 1.5vw, 1.1rem);
      font-weight: 200;
      letter-spacing: 1px;
      text-transform: uppercase;
      font-family: 'Marcellus', serif;
      opacity: 0.9;
      margin-bottom: 12px;
    }
    }
    @media (max-width: 380px) {
      .hero-names { font-size: 2rem; }
      .time-box span { font-size: 1.1rem; }
      .profile-card .img-wrap { width: 130px; height: 130px; }
    }
    @media (min-width: 1200px) {
      section { padding: 100px 40px; }
      .hero-names { font-size: 6.5rem; }
      .profiles-grid { gap: 64px; }
      .profile-card .img-wrap { width: 340px; height: 340px; }
      .gallery-masonry { column-count: 3; column-gap: 1.5rem; }
      .gallery-item { margin-bottom: 1.5rem; border-radius: 18px; }
    }
  </style>
</head>
<body>

  <!-- ===== FALLING PETALS ===== -->
  <div id="petalsContainer" class="fixed inset-0 pointer-events-none z-50"></div>

  <!-- ===== AUDIO TOGGLE ===== -->
  <button id="audioToggle" class="audio-fab muted">
    <div class="bars">
      <span></span><span></span><span></span><span></span>
    </div>
    <iconify-icon icon="lucide:volume-x" class="mute-icon text-2xl"></iconify-icon>
  </button>

  <div class="invitation-wrapper">

    <!-- ============================================================
    HERO SECTION
    ============================================================ -->
    <section class="hero-section">
      <div class="pattern-overlay"></div>
      <div class="hero-content">
        <div class="mb-4 animate-bounce">
          <iconify-icon icon="fa6-solid:om" class="text-4xl md:text-5xl text-[#D4AF37]"></iconify-icon>
        </div>
        <p class="hero-tag"><?php echo htmlspecialchars($config['hero_tag'] ?? 'Together with their families'); ?></p>
        <h1 class="hero-names">
          <?php echo htmlspecialchars($config['bride_name'] ?? 'Diya'); ?> <br>
          <span class="hero-ampersand"> &amp; </span> <br>
          <?php echo htmlspecialchars($config['groom_name'] ?? 'Aarav'); ?>
        </h1>
        <p class="hero-sub"><?php echo htmlspecialchars($config['sub_headline'] ?? 'Are getting married'); ?></p>
        <div class="hero-date">
          <span>Join us on</span>
          <strong><?php echo htmlspecialchars($config['wedding_date'] ?? 'November 24, 2024'); ?></strong>
        </div>
        <div class="hero-buttons">
          <a href="#rsvp" class="btn btn-primary">
            <iconify-icon icon="lucide:mail" class="text-lg"></iconify-icon> RSVP
          </a>
          <?php if ($config['has_table_finder'] ?? false): ?>
          <a href="#table" class="btn btn-secondary">
            <iconify-icon icon="lucide:search" class="text-lg"></iconify-icon> Find My Seat
          </a>
          <?php endif; ?>
        </div>
      </div>
      <div class="scroll-indicator" id="scrollIndicator">
        <p>Scroll</p>
        <iconify-icon icon="lucide:chevron-down" class="text-2xl"></iconify-icon>
      </div>
    </section>

    <!-- ============================================================
    PROFILES SECTION
    ============================================================ -->
    <?php if ($config['show_profiles'] ?? true): ?>
    <section class="reveal" id="mainContent">
      <iconify-icon icon="mdi:heart-outline" class="text-4xl text-[#8B2635] mb-2"></iconify-icon>
      <p class="section-tag">Meet The</p>
      <h2 class="section-title">Wedding Party</h2>
      <div class="section-divider"></div>
      <div class="profiles-grid">
        <div class="profile-card">
          <div class="img-wrap">
            <img src="<?php echo getMediaPath($config['bride_image'] ?? 'https://images.unsplash.com/photo-1583939003579-730e3918a45a?auto=format&fit=crop&q=80&w=800', 'images', $config['username'] ?? ''); ?>" alt="The Bride">
          </div>
          <span class="label">The Bride</span>
          <p class="name"><?php echo htmlspecialchars($config['bride_name'] ?? 'Diya Sharma'); ?></p>
          <p class="bio"><?php echo htmlspecialchars($config['bride_bio'] ?? 'An artist at heart who found her missing stroke in love.'); ?></p>
        </div>
        <div class="profile-card">
          <div class="img-wrap">
            <img src="<?php echo getMediaPath($config['groom_image'] ?? 'https://images.unsplash.com/photo-1595152772835-219674b2a8a6?auto=format&fit=crop&q=80&w=800', 'images', $config['username'] ?? ''); ?>" alt="The Groom">
          </div>
          <span class="label">The Groom</span>
          <p class="name"><?php echo htmlspecialchars($config['groom_name'] ?? 'Aarav Malhotra'); ?></p>
          <p class="bio"><?php echo htmlspecialchars($config['groom_bio'] ?? 'An engineer who designed his most beautiful project with her.'); ?></p>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <!-- ============================================================
    COUNTDOWN / GETTING MARRIED
    ============================================================ -->
    <section class="reveal" style="background:var(--maroon);color:#fff;padding:80px 24px;max-width:100%;">
      <div class="pattern-overlay" style="position:absolute;inset:0;opacity:0.1;background-image:url('https://www.transparenttextures.com/patterns/oriental-tiles.png');"></div>
      <div style="position:relative;z-index:1;max-width:1100px;margin:0 auto;">
        <h2 class="font-serif text-3xl uppercase tracking-[0.4em] mb-8 opacity-90 text-[#D4AF37]">Days to Celebration</h2>
        <div class="countdown-grid" id="countdownGrid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px;">
          <div class="time-box"><span id="days">00</span><label>Days</label></div>
          <div class="time-box"><span id="hours">00</span><label>Hours</label></div>
          <div class="time-box"><span id="minutes">00</span><label>Minutes</label></div>
          <div class="time-box"><span id="seconds">00</span><label>Seconds</label></div>
        </div>
      </div>
    </section>

    <!-- ============================================================
    LOCATION SECTION
    ============================================================ -->
    <section class="reveal" id="location">
      
      <div class="location-wrapper">
        <div class="location-info">
            <p class="section-tag">The Venue</p>
      <div class="section-divider"></div>
          <div class="venue-icon">
            <iconify-icon icon="lucide:map-pin" class="text-3xl"></iconify-icon>
          </div>
          <h3><?php echo htmlspecialchars($config['venue_title'] ?? 'Grand Palace Udaipur'); ?></h3>
          <p class="venue-loc"><?php echo htmlspecialchars($config['venue_location'] ?? 'Udaipur, Rajasthan'); ?></p>
          <p class="venue-address"><?php echo htmlspecialchars($config['venue_address'] ?? 'Near Lake Pichola, Haridas Ji Ki Magri, Shavri Colony, Udaipur, Rajasthan 313001'); ?></p>
          <div class="time-badge">
            <iconify-icon icon="lucide:clock"></iconify-icon>
            <?php echo htmlspecialchars($config['event_time'] ?? 'Muhrat: 6:30 PM Onwards'); ?>
          </div>
          <div class="venue-actions">
            <a href="<?php echo htmlspecialchars($config['google_maps_url'] ?? 'https://maps.google.com'); ?>" target="_blank" class="btn btn-primary">
              <iconify-icon icon="lucide:navigation"></iconify-icon> Open in Maps
            </a>
            <button class="btn btn-outline" id="addToCalendar">
              <iconify-icon icon="lucide:calendar-plus"></iconify-icon> Add to Calendar
            </button>
          </div>
        </div>
        <div class="map-wrapper">
          <iframe 
            src="https://maps.google.com/maps?q=<?php echo urlencode($config['venue_title'] ?? 'Grand Palace Udaipur') . '+' . urlencode($config['venue_address'] ?? 'Udaipur'); ?>&t=&z=15&ie=UTF8&iwloc=&output=embed" 
            frameborder="0" scrolling="no" marginheight="0" marginwidth="0"
            allowfullscreen="" loading="lazy">
          </iframe>
        </div>
      </div>
    </section>

    <!-- ============================================================
    TIMELINE SECTION
    ============================================================ -->
    <section class="reveal" style="background:var(--bg-card);">
      <iconify-icon icon="lucide:calendar-check" class="text-4xl text-[#8B2635] mb-2"></iconify-icon>
      <p class="section-tag">Our Celebration</p>
      <h2 class="section-title">Event Itinerary</h2>
      <div class="section-divider"></div>
      <div class="timeline-container">
        <?php 
        $schedule = $config['schedule'] ?? [
          ['time' => 'Nov 22, 10:00 AM', 'title' => 'Ganesh Vandana', 'description' => 'A divine start to our wedding festivities seeking Lord Ganesha\'s blessings.'],
          ['time' => 'Nov 23, 04:00 PM', 'title' => 'Mehendi & Sangeet', 'description' => 'An evening of henna, music, dance and boundless celebration.'],
          ['time' => 'Nov 24, 07:00 PM', 'title' => 'The Wedding Ceremony', 'description' => 'The sacred pheras and union of two souls under the stars.'],
        ];
        foreach($schedule as $item): 
        ?>
        <div class="timeline-item reveal">
          <span class="time-stamp"><?php echo htmlspecialchars($item['time'] ?? ''); ?></span>
          <h4><?php echo htmlspecialchars($item['title'] ?? ''); ?></h4>
          <p><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- ============================================================
    GALLERY SECTION - TRUE COLLAGE WITH VARIED ASPECT RATIOS
    ============================================================ -->
    <?php if ($config['has_gallery'] ?? false): ?>
    <section class="reveal" id="gallerySection" style="background:var(--bg-main);">
      <iconify-icon icon="ph:camera-light" class="text-4xl text-[#8B2635] mb-2"></iconify-icon>
      <p class="section-tag">Our Memories</p>
      <h2 class="section-title">Love in Frames</h2>
      <div class="section-divider"></div>
      <div class="gallery-masonry">
        <?php if (empty($config['gallery_images'])): ?>
          <div style="grid-column:1/-1;padding:40px;color:var(--text-muted);background:#fff;border-radius:16px;border:2px dashed var(--gold);">
            <iconify-icon icon="lucide:image" class="text-4xl block mb-2 opacity-40"></iconify-icon>
            <p>No gallery images yet</p>
          </div>
        <?php else: ?>
          <?php foreach ($config['gallery_images'] as $index => $img): ?>
          <div class="gallery-item reveal">
            <img src="<?php echo getMediaPath($img['image_path'] ?? '', 'images', $config['username'] ?? ''); ?>" 
                 alt="<?php echo htmlspecialchars($img['caption'] ?? 'Wedding memory'); ?>"
                 loading="lazy"
                 onerror="this.style.background='#f0e8e0'; this.src='https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&q=80&w=400';">
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>
    <?php endif; ?>

    <!-- ============================================================
    TABLE FINDER SECTION (if enabled)
    ============================================================ -->
    <?php if ($config['has_table_finder'] ?? false): ?>
    <section class="reveal" id="table" style="background:var(--maroon);color:#fff;max-width:100%;padding:80px 24px;">
      <div class="pattern-overlay" style="position:absolute;inset:0;opacity:0.1;background-image:url('https://www.transparenttextures.com/patterns/oriental-tiles.png');"></div>
      <div style="position:relative;z-index:1;max-width:820px;margin:0 auto;">
        <h2 class="font-serif text-3xl uppercase tracking-widest mb-8 text-[#D4AF37]">Find Your Table</h2>
        <div class="card" style="background:rgba(255,255,255,0.05);backdrop-filter:blur(8px);border:1px solid rgba(212,175,55,0.15);color:#fff;">
          <div class="search-box">
            <input type="text" id="tableSearchInput" placeholder="Enter your name..." style="background:rgba(255,255,255,0.06);color:#fff;border-color:rgba(212,175,55,0.25);">
            <button class="btn" id="tableSearchBtn" style="background:var(--gold);color:var(--dark);border-color:var(--gold);padding:16px 36px;border-radius:50px;font-weight:600;letter-spacing:2px;text-transform:uppercase;font-size:0.7rem;display:inline-flex;align-items:center;gap:10px;cursor:pointer;transition:all 0.3s ease;">
              <iconify-icon icon="lucide:search"></iconify-icon> Search
            </button>
          </div>
          <div id="tableResult" class="table-result">
  <!-- Added flex properties and text-center to the inline style, fixed the broken 'ite' attribute -->
  <div class="not-found" style="background:rgba(255,255,255,0.03); border: 1px solid rgba(212,175,55,0.15); color:rgba(255,255,255,0.5); display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; min-height: 100px; padding: 1rem;">
    <iconify-icon icon="lucide:search" class="text-4xl block mb-2"></iconify-icon>
    <p>Find your designated seat for the evening</p>
  </div>
</div>

        </div>
      </div>
    </section>
    <?php endif; ?>

    <!-- ============================================================
    RSVP SECTION
    ============================================================ -->
    <section class="reveal" id="rsvp">
      <iconify-icon icon="lucide:scroll-text" class="text-4xl text-[#8B2635] mb-2"></iconify-icon>
      <p class="section-tag">Join Us</p>
      <h2 class="section-title">Will You Join Us?</h2>
      <p style="color:var(--text-muted);font-weight:300;margin-bottom:16px;letter-spacing:1px;">Kindly respond by <?php echo htmlspecialchars($config['rsvp_deadline'] ?? 'November 10th'); ?></p>
      <div class="section-divider"></div>
      <div class="card" style="border:2px solid var(--gold);position:relative;">
        <form id="rsvpForm" class="rsvp-form">
          <div class="form-row">
            <div class="form-group">
              <label>Full Name <span class="required">*</span></label>
              <input type="text" id="guestName" placeholder="First & Last Name" required>
            </div>
            <div class="form-group">
              <label>Email Address</label>
              <input type="email" id="guestEmail" placeholder="you@example.com">
            </div>
          </div>
          <div class="form-group full-width">
            <label>Attendance <span class="required">*</span></label>
            <select id="attendanceStatus" required>
              <option value="" disabled selected>Select an option</option>
              <option value="Joyfully Accepts">Joyfully Accepts</option>
              <option value="Regretfully Declines">Regretfully Declines</option>
            </select>
          </div>
          <div class="form-group full-width">
            <label>Number of Guests</label>
            <input type="number" id="guestCount" min="1" max="10" placeholder="Total count including you">
          </div>
          <button type="submit" class="btn-submit">
            <iconify-icon icon="lucide:send"></iconify-icon> Send Blessings
          </button>
        </form>
      </div>
    </section>

    <!-- ============================================================
    FOOTER
    ============================================================ -->
    <footer>
      <div class="pattern-overlay"></div>
      <div style="position:relative;z-index:1;">
        <h2 class="footer-headline"><?php echo htmlspecialchars($config['couple_headline'] ?? 'Aarav & Diya'); ?></h2>
        <div style="display:flex;justify-content:center;gap:16px;align-items:center;margin-bottom:12px;">
          <div style="width:40px;height:1px;background:var(--gold);opacity:0.3;"></div>
          <iconify-icon icon="lucide:heart" class="text-2xl text-[#8B2635]"></iconify-icon>
          <div style="width:40px;height:1px;background:var(--gold);opacity:0.3;"></div>
        </div>
        <p style="font-family:'Marcellus',serif;font-size:0.7rem;letter-spacing:4px;opacity:0.4;text-transform:uppercase;color:rgba(255,255,255,0.5);">Made with love for the big day</p>
        <p class="footer-copy">&copy; <?php echo date('Y'); ?> · Celebration of Love</p>
      </div>
    </footer>

  </div>

  <!-- ============================================================
  JAVASCRIPT
  ============================================================ -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const config = <?php echo json_encode($config); ?>;
      const tableData = <?php echo json_encode($table_entries ?? []); ?>;

      // ----- FALLING PETALS -----
      function createPetals() {
        const container = document.getElementById('petalsContainer');
        if (!container) return;
        const petalCount = window.innerWidth < 768 ? 18 : 30;
        const icons = ['🌸', '💮', '🏵️', '✨', '🌺', '🌷'];
        for (let i = 0; i < petalCount; i++) {
          const petal = document.createElement('div');
          petal.className = 'petal';
          petal.innerHTML = icons[Math.floor(Math.random() * icons.length)];
          petal.style.cssText = `
            position: absolute;
            top: -20px;
            left: ${Math.random() * 100}vw;
            font-size: ${Math.random() * 18 + 10}px;
            animation: fall ${Math.random() * 7 + 5}s linear infinite;
            animation-delay: ${Math.random() * 10}s;
            opacity: ${0.25 + Math.random() * 0.5};
            pointer-events: none;
            z-index: 50;
          `;
          container.appendChild(petal);
        }
      }
      if (!document.getElementById('petalKeyframes')) {
        const style = document.createElement('style');
        style.id = 'petalKeyframes';
        style.textContent = `
          @keyframes fall {
            0% { transform: translateY(0) rotate(0deg); opacity: 0.7; }
            100% { transform: translateY(100vh) rotate(360deg); opacity: 0; }
          }
        `;
        document.head.appendChild(style);
      }
      createPetals();

      // ----- SCROLL REVEAL -----
      const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('active');
          }
        });
      }, { threshold: 0.1 });
      document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

      // ----- SCROLL INDICATOR -----
      document.getElementById('scrollIndicator')?.addEventListener('click', function() {
        const target = document.getElementById('mainContent') || document.querySelector('.profile-card');
        if (target) target.scrollIntoView({ behavior: 'smooth' });
      });

      // ----- AUDIO -----
      let bgAudio = null;
      let audioLoaded = false;
      const audioToggle = document.getElementById('audioToggle');

      function initAudio() {
        try {
          const audioPath = config.bg_audio || '../uploads/audio/intro-music.mp3';
          bgAudio = new Audio(audioPath);
          bgAudio.loop = true;
          bgAudio.volume = 0.3;
          bgAudio.preload = 'auto';
          bgAudio.addEventListener('canplaythrough', function() { audioLoaded = true; });
          bgAudio.addEventListener('playing', function() { audioToggle.classList.remove('muted'); });
          bgAudio.addEventListener('pause', function() { audioToggle.classList.add('muted'); });
          document.addEventListener('click', function tryPlay() {
            if (bgAudio && bgAudio.paused && audioLoaded) {
              bgAudio.play().catch(() => {});
            }
            document.removeEventListener('click', tryPlay);
          }, { once: true });
        } catch (e) { console.warn('Audio error:', e); }
      }

      if (audioToggle) {
        audioToggle.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          if (bgAudio) {
            bgAudio.paused ? bgAudio.play().catch(() => {}) : bgAudio.pause();
          } else {
            initAudio();
            setTimeout(() => { if (bgAudio) bgAudio.play().catch(() => {}); }, 300);
          }
        });
      }
      initAudio();

      // ----- COUNTDOWN -----
      const targetDate = new Date(config.countdown_target || '2024-11-24T18:30:00').getTime();
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
            grid.innerHTML =
              `<div style="grid-column:span 4;font-family:'Marcellus',serif;font-size:1.6rem;color:var(--gold);padding:15px;">💕 Celebration Commenced 💕</div>`;
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

      // ----- TABLE FINDER -----
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
          tableResult.innerHTML =
            `<div class="not-found" style="background:rgba(255,255,255,0.03);border-color:rgba(212,175,55,0.15);color:rgba(255,255,255,0.5);"><iconify-icon icon="lucide:search" class="text-4xl block mb-2"></iconify-icon><p>Please enter your name</p></div>`;
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
              <div class="found" style="margin-bottom:16px;background:rgba(255,255,255,0.06);border-left-color:var(--gold);color:#fff;">
                <div class="guest-name" style="color:var(--gold);">${escapeHtml(result.guest_name)}</div>
                ${result.family_name ? `<div class="family-name" style="color:rgba(255,255,255,0.5);">👨‍👩‍👧‍👦 Family: ${escapeHtml(result.family_name)}</div>` : ''}
                <div style="margin-top:6px;">
                  <span class="table-label" style="color:rgba(255,255,255,0.35);">Table</span>
                  <div class="table-number" style="color:var(--gold);">${escapeHtml(result.table_number)}</div>
                </div>
              </div>
            `;
          });
          tableResult.innerHTML = html;
        } else {
          tableResult.innerHTML =
            `<div class="not-found" style="background:rgba(255,255,255,0.03);border-color:rgba(212,175,55,0.15);color:rgba(255,255,255,0.5);"><iconify-icon icon="lucide:search" class="text-4xl block mb-2"></iconify-icon><p>No guest found with name "<strong style="color:#fff;">${escapeHtml(searchInput.value)}</strong>"</p><p style="font-size:0.8rem;margin-top:4px;opacity:0.5;">Please check the spelling or try a different name.</p></div>`;
        }
      }

      if (searchBtn && searchInput) {
        searchBtn.addEventListener('click', function(e) { e.preventDefault();
          searchTable(); });
        searchInput.addEventListener('keypress', function(e) { if (e.key === 'Enter') { e.preventDefault();
            searchTable(); } });
      }

      // ----- CALENDAR -----
      function dispatchCalendar() {
        const start = new Date(config.countdown_target || Date.now());
        const end = new Date(start.getTime() + 6 * 60 * 60 * 1000);
        const startStr = start.toISOString().replace(/-|:|\.\d+/g, '');
        const endStr = end.toISOString().replace(/-|:|\.\d+/g, '');
        const url =
          `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent((config.couple_headline || 'Aarav & Diya') + ' Wedding')}&dates=${startStr}/${endStr}&details=${encodeURIComponent('Wedding celebration')}&location=${encodeURIComponent((config.venue_title || 'Grand Palace Udaipur') + ', ' + (config.venue_address || ''))}`;
        window.open(url, '_blank');
      }
      document.getElementById('addToCalendar')?.addEventListener('click', dispatchCalendar);

      // ----- RSVP FORM -----
      const rsvpForm = document.getElementById('rsvpForm');
      if (rsvpForm) {
        rsvpForm.addEventListener('submit', function(e) {
          e.preventDefault();
          const name = document.getElementById('guestName').value.trim();
          const email = document.getElementById('guestEmail').value.trim();
          const status = document.getElementById('attendanceStatus').value;
          const count = document.getElementById('guestCount').value || '1';
          const phone = config.whatsapp_phone || '';
          if (!name || !status) {
            alert('Please complete all mandatory fields.');
            return;
          }
          const msg =
            `*WEDDING RSVP CONFIRMATION*\n\n*Guest Name:* ${name}\n${email ? '*Email:* ' + email : ''}\n*Attendance:* ${status}\n*Number of Guests:* ${count}\n\nWedding: ${config.couple_headline || 'Aarav & Diya'}\nDate: ${config.wedding_date || 'November 24, 2024'}`;
          window.open(`https://api.whatsapp.com/send?phone=${phone}&text=${encodeURIComponent(msg)}`,
          '_blank');
        });
      }

      console.log('Royal Indian Wedding Theme (DB driven) loaded successfully!');
    });
  </script>

</body>
</html>
