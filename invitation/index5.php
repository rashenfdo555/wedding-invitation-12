<?php
// invitation/index1.php - Classic Gold Theme
$inv = $invitation_data ?? null;
if (!$inv) die('Invitation data not available.');

$user = getUserAdminById($inv['user_admin_id']);
$username = $user['username'] ?? 'default';
$features = getUserFeatures($inv['user_admin_id']);
$has_gallery = $features['has_gallery'] ?? 0;
$has_table_finder = $features['has_table_finder'] ?? 0;

$config = [
    'bride_name'       => $inv['bride_name'] ?? '',
    'groom_name'       => $inv['groom_name'] ?? '',
    'couple_headline'  => $inv['couple_headline'] ?? '',
    'wedding_date'     => $inv['wedding_date'] ?? '',
    'month'            => $inv['month'] ?? '',
    'day'              => $inv['day'] ?? '',
    'year'             => $inv['year'] ?? '',
    'countdown_target' => $inv['countdown_target'] ?? '',
    'sub_headline'     => $inv['sub_headline'] ?? '',
    'venue_title'      => $inv['venue_title'] ?? '',
    'venue_location'   => $inv['venue_location'] ?? '',
    'venue_address'    => $inv['venue_address'] ?? '',
    'google_maps_url'  => $inv['google_maps_url'] ?? '',
    'event_time'       => $inv['event_time'] ?? '',
    'whatsapp_phone'   => $inv['whatsapp_phone'] ?? '',
    'primary_color'    => $inv['primary_color'] ?? '#bc9c6c',
    'secondary_color'  => $inv['secondary_color'] ?? '#f4efe6',
    'text_color'       => $inv['text_color'] ?? '#443838',
    'bg_main_color'    => $inv['bg_main_color'] ?? '#fbf9f6',
    'bg_card_color'    => $inv['bg_card_color'] ?? '#ffffff',
    'text_muted_color' => $inv['text_muted_color'] ?? '#6e6e6e',
    'text_light_gray'  => $inv['text_light_gray'] ?? '#9e9e9e',
    'border_light_color'=> $inv['border_light_color'] ?? '#eae5dd',
    'map_bg_color'     => $inv['map_bg_color'] ?? '#e5e5e3',
    'hero_image'       => $inv['hero_image'] ?? '',
    'bride_image'      => $inv['bride_image'] ?? '',
    'groom_image'      => $inv['groom_image'] ?? '',
    'bg_audio'         => '../uploads/audio/' . ($inv['bg_audio'] ?? 'intro-music.mp3'),
    'show_profiles'    => $inv['show_profiles'] ?? 1,
    'schedule'         => getScheduleItems($inv['id']),
    'has_gallery'      => $has_gallery,
    'has_table_finder' => $has_table_finder,
    'gallery_images'   => $has_gallery ? getGalleryImages($inv['id']) : [],
    'table_entries'    => $has_table_finder ? getTableFinderEntries($inv['id']) : [],
    'username'         => $username
];

function getMediaPath($fn, $type = 'images', $u = null) {
    if (empty($fn)) return '';
    global $config;
    $user = $u ?? $config['username'] ?? 'default';
    return (strpos($fn, '/') !== false) ? "../uploads/{$type}/{$fn}" : "../uploads/{$type}/{$user}/{$fn}";
}

$hex = ltrim($config['primary_color'], '#');
if (strlen($hex) === 3) $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
$c = array_map(fn($p) => max(0, hexdec($p) - 30), str_split($hex, 2));
$dark_gold = '#' . sprintf('%02x%02x%02x', ...$c);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= htmlspecialchars($config['couple_headline']); ?> - Wedding Invitation</title>
    <link href="https://fonts.googleapis.com/css2?family=Alex+Brush&family=Crimson+Pro:wght@400;600;700&family=Great+Vibes&family=Marcellus&family=Montserrat:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-main: <?= htmlspecialchars($config['bg_main_color']); ?>;
            --bg-card-white: <?= htmlspecialchars($config['bg_card_color']); ?>;
            --text-primary: <?= htmlspecialchars($config['text_color']); ?>;
            --text-muted: <?= htmlspecialchars($config['text_muted_color']); ?>;
            --text-light-gray: <?= htmlspecialchars($config['text_light_gray']); ?>;
            --accent-gold: <?= htmlspecialchars($config['primary_color']); ?>;
            --accent-gold-light: <?= htmlspecialchars($config['secondary_color']); ?>;
            --border-light: <?= htmlspecialchars($config['border_light_color']); ?>;
            --primary-color: var(--accent-gold);
            --secondary-color: var(--accent-gold-light);
            --hover-gold: <?= $dark_gold; ?>;
            --max-content-width: 1100px;
        }
        * { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color:transparent; }
        html { scroll-behavior:smooth; }
        body { font-family:'Montserrat',sans-serif; background:#f3f0ea; color:var(--text-primary); overflow-x:hidden; min-height:100vh; }
        .invitation-wrapper { width:100%; margin:0 auto; background:var(--bg-main); min-height:100vh; position:relative; box-shadow:0 0 40px rgba(0,0,0,0.03); }

        /* Petals */
        .petals-container { position:fixed; inset:0; pointer-events:none; z-index:9999; overflow:hidden; }
        .petal { position:absolute; top:-30px; font-size:18px; opacity:0.6; animation:fall linear infinite; will-change:transform; }
        @keyframes fall {
            0% { transform:translateY(0) rotate(0deg) scale(0.6); opacity:0.7; }
            100% { transform:translateY(110vh) rotate(720deg) scale(1.3); opacity:0.1; }
        }

        /* Hero */
        .hero-section { position:relative; height:50vh; width:100%; background:url('<?= getMediaPath($config['hero_image'], 'images', $config['username']); ?>') center/cover no-repeat; overflow:hidden; }
        .brush-mask { position:absolute; bottom:-1px; left:0; width:200%; height:80px; fill:var(--bg-main); z-index:2; pointer-events:none; animation:waveMove 12s linear infinite; will-change:transform; }
        @keyframes waveMove { 0%{transform:translate3d(0,0,0);} 100%{transform:translate3d(-50%,0,0);} }

        /* Details */
        .details-section { background:var(--bg-main); padding:20px 24px 60px; text-align:center; display:flex; justify-content:center; }
        .details-section .container { max-width:650px; width:100%; display:flex; flex-direction:column; align-items:center; }
        .save-the-date { font-size:0.8rem; letter-spacing:5px; color:rgb(from var(--primary-color) r g b / 0.6); font-weight:500; text-transform:uppercase; margin-bottom:15px; }
        .names-card { padding:24px 36px; margin-bottom:5px; width:100%; display:inline-block; transform-origin:top center; animation:windFlutter 5s ease-in-out infinite alternate; }
        .couple-title { font-family:'Great Vibes',cursive; font-size:clamp(4.2rem,12vw,7rem); font-weight:400; line-height:0.8; letter-spacing:2px; }
        .name-highlight { color:var(--primary-color); display:inline-block; animation:textSway 4s ease-in-out infinite alternate 0.3s; }
        .couple-title .ampersand { font-family:'Crimson Pro',serif; font-size:clamp(2.2rem,4vw,2.6rem); font-style:italic; color:var(--primary-color); margin:0 12px; display:inline-block; animation:ampersandFlutter 3.5s ease-in-out infinite alternate 0.6s; }
        @keyframes windFlutter {
            0%{transform:translateY(0) rotate(0deg);}
            25%{transform:translateY(-8px) rotate(1.5deg) rotateY(6deg);}
            50%{transform:translateY(-3px) rotate(-1deg) rotateY(-4deg);}
            75%{transform:translateY(-10px) rotate(2deg) rotateY(5deg);}
            100%{transform:translateY(-5px) rotate(-1.5deg) rotateY(-3deg);}
        }
        @keyframes textSway { 0%{transform:rotateX(0deg);} 50%{transform:rotateX(8deg) rotateZ(-1deg);} 100%{transform:rotateX(-5deg) rotateZ(1deg);} }
        @keyframes ampersandFlutter { 0%{transform:scale(1) rotate(0deg);} 100%{transform:scale(1.08) rotate(3deg);} }
        .title-separator { width:80px; height:1px; background:var(--primary-color); margin-bottom:20px; opacity:0.6; }
        .date-row { display:flex; align-items:center; justify-content:center; gap:18px; font-family:'Playfair Display',serif; color:#2c2c2c; margin-bottom:32px; }
        .date-month, .date-year { font-size:1.1rem; font-weight:500; text-transform:uppercase; letter-spacing:3px; border-top:1px solid var(--primary-color); border-bottom:1px solid var(--primary-color); padding:4px 10px; }
        .date-day { font-size:3.8rem; font-weight:600; line-height:1; color:var(--primary-color); }
        .scroll-indicator { display:flex; flex-direction:column; align-items:center; gap:6px; color:#888; cursor:pointer; }
        .scroll-indicator p { font-size:0.65rem; letter-spacing:3px; }
        .scroll-indicator i { font-size:0.85rem; color:var(--primary-color); animation:bounce 2s infinite; }
        @keyframes bounce { 0%,20%,50%,80%,100%{transform:translateY(0);} 40%{transform:translateY(-6px);} 60%{transform:translateY(-3px);} }

        /* Audio FAB */
        .audio-fab { position:fixed; bottom:30px; right:30px; width:48px; height:48px; border-radius:50%; border:none; background:var(--accent-gold); backdrop-filter:blur(8px); color:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center; z-index:500; box-shadow:0 4px 15px rgba(0,0,0,0.2); transition:0.3s ease; }
        .audio-fab:hover { transform:scale(1.05); }
        .bars { display:flex; align-items:flex-end; gap:2px; width:14px; height:12px; }
        .bars span { width:2px; height:100%; background:#fff; transform-origin:bottom; animation:barDance 0.8s ease-in-out infinite alternate; }
        .bars span:nth-child(1){animation-delay:-0.1s;} .bars span:nth-child(2){animation-delay:-0.4s;} .bars span:nth-child(3){animation-delay:-0.2s;} .bars span:nth-child(4){animation-delay:-0.6s;}
        @keyframes barDance { 0%{transform:scaleY(0.2);} 100%{transform:scaleY(1);} }
        .audio-fab.muted .bars, .audio-fab:not(.muted) .mute-icon { display:none; }
        .audio-fab.muted .mute-icon { display:block; }
        .audio-fab:not(.muted) .bars { display:flex; }

        /* Profiles */
        .profiles-section { max-width:var(--max-content-width); margin:0 auto; padding:90px 24px 60px; display:flex; align-items:center; justify-content:center; gap:32px; }
        .profile-card { flex:1; max-width:420px; text-align:center; }
        .arch-frame-wrapper { position:relative; padding-bottom:24px; }
        .arch-border-accent { position:absolute; top:-10px; left:-10px; right:-10px; bottom:14px; border:1px solid var(--accent-gold); border-radius:200px 200px 16px 16px; opacity:0.4; pointer-events:none; transition:0.4s ease; }
        .profile-card:hover .arch-border-accent { opacity:0.9; top:-14px; bottom:10px; }
        .img-zoom-wrapper { width:100%; height:480px; border-radius:200px 200px 16px 16px; overflow:hidden; position:relative; box-shadow:0 16px 40px rgba(0,0,0,0.06); background:#f8f8f8; }
        .profile-img { width:100%; height:100%; object-fit:cover; transition:transform 0.9s cubic-bezier(0.16,1,0.3,1); }
        .profile-card:hover .profile-img { transform:scale(1.06); }
        .profile-badge { position:absolute; bottom:0; left:50%; transform:translateX(-50%); background:rgba(255,255,255,0.95); backdrop-filter:blur(10px); padding:12px 38px; border-radius:25px; box-shadow:0 10px 25px rgba(0,0,0,0.08); border:1px solid var(--border-light); display:flex; flex-direction:column; align-items:center; white-space:nowrap; z-index:3; transition:0.3s; }
        .profile-card:hover .profile-badge { transform:translateX(-50%) translateY(-4px); }
        .badge-role { font-size:0.65rem; letter-spacing:2.5px; color:var(--accent-gold); font-weight:700; text-transform:uppercase; }
        .badge-name { font-family:'Playfair Display',serif; font-size:1.15rem; color:var(--text-primary); font-weight:500; }
        .ampersand-divider { font-family:'Playfair Display',serif; font-size:2.8rem; font-style:italic; color:var(--accent-gold); opacity:0.6; }

        /* Story */
        .getting-married, .rsvp-section { text-align:center; background:var(--bg-main); max-width:750px; margin:0 auto; padding:40px 24px; }
        .heart-icon-wrap { color:var(--accent-gold); font-size:2.5rem; margin-bottom:20px; }
        .section-tag { font-size:0.75rem; letter-spacing:5px; color:var(--accent-gold); font-weight:600; text-transform:uppercase; margin-bottom:8px; }
        .getting-married h2, .location-section h2, .timeline-section h2, .rsvp-section h2 { font-family:'Playfair Display',serif; font-size:2.4rem; font-weight:400; color:var(--text-primary); margin-bottom:20px; }
        .getting-married .description { font-size:1.05rem; color:var(--text-muted); line-height:1.8; margin-bottom:60px; font-weight:300; }
        .getting-married .signature { font-family:'Alex Brush',cursive; font-size:2.5rem; color:var(--accent-gold); display:inline-block; transform-origin:top center; animation:windFlutter 5s ease-in-out infinite alternate; }

        /* Countdown */
        .countdown-section { max-width:850px; margin:0 auto; padding:30px 24px 60px; }
        .countdown-orbital-wrapper { display:flex; justify-content:center; align-items:center; gap:20px; flex-wrap:wrap; }
        .time-ring-unit { position:relative; width:130px; height:130px; display:flex; align-items:center; justify-content:center; border-radius:50%; }
        .ring-backdrop { position:absolute; inset:0; border-radius:50%; border:1px dashed var(--accent-gold); opacity:0.35; animation:ringSpin 24s linear infinite; }
        .time-ring-unit:hover .ring-backdrop { opacity:0.8; border-style:solid; transform:scale(1.05); transition:0.4s; }
        .time-content { width:110px; height:110px; background:var(--bg-card-white); border-radius:50%; display:flex; flex-direction:column; align-items:center; justify-content:center; box-shadow:0 10px 30px rgba(0,0,0,0.04); border:1px solid var(--border-light); z-index:2; transition:0.3s; }
        .time-ring-unit:hover .time-content { transform:translateY(-4px); }
        .time-content span { font-family:'Playfair Display',serif; font-size:2rem; font-weight:500; color:var(--accent-gold); line-height:1; margin-bottom:4px; }
        .time-content label { font-size:0.6rem; color:var(--text-light-gray); letter-spacing:1.5px; font-weight:600; }
        @keyframes ringSpin { to{transform:rotate(360deg);} }

        /* Celebration Screen */
        .celebration-screen { position:relative; overflow:hidden; width:100%; min-height:280px; padding:60px 20px; text-align:center; border-radius:25px; background:linear-gradient(135deg,#fffdf9,#fff6ea,#fffdf9); animation:fadeIn 1.2s ease; }
        .celebration-screen h2 { font-size:2.2rem; color:#C8A24A; margin:15px 0; font-family:'Playfair Display',serif; animation:glow 2s infinite alternate; }
        .celebration-screen p { font-size:1.1rem; color:#555; }
        .floating-hearts { font-size:38px; animation:floatHearts 3s infinite ease-in-out; }
        .rings { font-size:70px; margin:18px 0; animation:ringPulse 1.8s infinite; }
        .confetti { margin-top:25px; font-size:34px; animation:confettiText 1s infinite alternate; }
        .confetti-piece { position:absolute; top:-30px; width:10px; height:18px; opacity:.8; animation:fall linear infinite; }
        @keyframes fadeIn { from{opacity:0; transform:scale(.85);} to{opacity:1; transform:scale(1);} }
        @keyframes glow { from{text-shadow:0 0 5px #D4AF37;} to{text-shadow:0 0 25px gold;} }
        @keyframes floatHearts { 0%,100%{transform:translateY(0);} 50%{transform:translateY(-12px);} }
        @keyframes ringPulse { 0%,100%{transform:scale(1);} 50%{transform:scale(1.15);} }
        @keyframes confettiText { from{transform:translateY(0);} to{transform:translateY(-8px);} }

        /* Location */
        .location-section, .timeline-section { max-width:1000px; margin:0 auto; padding:80px 24px; text-align:center; }
        .timeline-section { max-width:750px; }
        .venue-card { background:var(--bg-card-white); border-radius:24px; box-shadow:0 20px 50px rgba(0,0,0,0.04); border:1px solid var(--border-light); margin-top:45px; display:grid; grid-template-columns:1fr 1fr; overflow:hidden; text-align:left; }
        .venue-info-side { padding:50px; display:flex; flex-direction:column; justify-content:center; }
        .venue-icon-badge { width:48px; height:48px; border-radius:50%; background:var(--accent-gold-light); color:var(--accent-gold); display:flex; align-items:center; justify-content:center; font-size:1.1rem; margin-bottom:24px; }
        .venue-card h3 { font-family:'Playfair Display',serif; font-size:2rem; font-weight:400; color:var(--text-primary); margin-bottom:8px; line-height:1.3; }
        .venue-loc-sub { font-size:0.8rem; color:var(--accent-gold); text-transform:uppercase; letter-spacing:2px; font-weight:600; margin-bottom:16px; }
        .venue-address { font-size:0.95rem; color:var(--text-muted); line-height:1.6; margin-bottom:24px; }
        .time-badge-container { margin-bottom:30px; }
        .time-badge { display:inline-flex; align-items:center; gap:10px; background:var(--accent-gold-light); padding:8px 18px; border-radius:30px; font-size:0.85rem; color:var(--text-primary); font-weight:500; }
        .venue-actions { display:flex; gap:12px; margin-bottom:16px; }
        .action-btn { flex:1; padding:14px 20px; border-radius:8px; font-size:0.75rem; font-weight:600; letter-spacing:1.5px; text-transform:uppercase; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; text-decoration:none; transition:0.25s ease; }
        .map-btn { background:var(--primary-color); color:#fff; }
        .map-btn:hover, .table-search-btn:hover, .rsvp-btn:hover { background:var(--hover-gold); transform:translateY(-2px); box-shadow:0 4px 12px rgba(170,139,92,0.2); }
        .calendar-btn { background:#fff; color:var(--text-primary); border:1px solid var(--border-light); }
        .calendar-btn:hover { background:#fafafa; transform:translateY(-2px); border-color:var(--accent-gold); }
        .gcal-link { display:inline-block; font-size:0.75rem; color:var(--text-muted); text-decoration:none; transition:color 0.2s; }
        .gcal-link:hover { color:var(--accent-gold); text-decoration:underline; }
        .venue-map-side, .map-wrapper, .live-map-iframe { width:100%; height:100%; min-height:400px; border:0; }
        .live-map-iframe { filter:grayscale(0.2) contrast(1.05); }

        /* Timeline Accordion */
        .timeline-accordion-container { display:flex; flex-direction:column; gap:16px; margin-top:40px; }
        .timeline-accordion-card { background:#fff; border-radius:16px; border:1px solid rgba(0,0,0,0.08); box-shadow:0 4px 20px rgba(0,0,0,0.03); overflow:hidden; text-align:left; transition:all 0.3s ease; }
        .timeline-accordion-card[open] { box-shadow:0 10px 30px rgba(0,0,0,0.08); border-color:var(--primary-color); }
        .accordion-header { display:flex; align-items:center; justify-content:space-between; padding:20px 24px; cursor:pointer; list-style:none; user-select:none; }
        .accordion-header::-webkit-details-marker { display:none; }
        .header-left, .header-right { display:flex; align-items:center; gap:16px; }
        .header-right { gap:12px; }
        .step-num { font-size:0.9rem; font-weight:700; opacity:0.3; font-family:sans-serif; }
        .icon-circle { width:42px; height:42px; border-radius:50%; background:#fff; border:1.5px solid var(--primary-color); color:var(--primary-color); display:flex; align-items:center; justify-content:center; font-size:0.95rem; flex-shrink:0; }
        .header-left h3 { font-family:'Playfair Display',serif; font-size:1.15rem; color:var(--text-primary); font-weight:500; margin:0; }
        .time-stamp { font-size:0.75rem; background:var(--secondary-color); color:var(--primary-color); padding:6px 12px; border-radius:20px; font-weight:600; letter-spacing:0.5px; text-transform:uppercase; white-space:nowrap; }
        .toggle-icon { font-size:0.85rem; color:var(--text-muted); transition:transform 0.3s ease; }
        .timeline-accordion-card[open] .toggle-icon { transform:rotate(180deg); }
        .accordion-body { padding:0 24px 24px 98px; }
        .accordion-body p { font-size:0.9rem; color:var(--text-muted); font-weight:300; line-height:1.6; margin:0; }

        /* Feature Buttons */
        .feature-buttons-section { max-width:800px; margin:0 auto; padding:40px 24px; text-align:center; }
        .feature-buttons-container { display:flex; justify-content:center; gap:20px; flex-wrap:wrap; }
        .feature-btn { display:flex; align-items:center; gap:12px; padding:16px 40px; border:2px solid var(--accent-gold); border-radius:50px; background:transparent; color:var(--accent-gold); font-size:1rem; font-weight:600; cursor:pointer; transition:0.3s; text-transform:uppercase; letter-spacing:2px; }
        .feature-btn:hover, .feature-btn.active { background:var(--accent-gold); color:#fff; transform:translateY(-2px); box-shadow:0 10px 30px rgba(188,156,108,0.3); }

        /* Gallery */
        .gallery-section { max-width:var(--max-content-width); margin:0 auto; padding:60px 20px 80px; text-align:center; }
        .gallery-masonry { column-count:3; column-gap:10px; margin-top:10px; }
        .gallery-item { break-inside:avoid; margin-bottom:10px; position:relative; border-radius:16px; overflow:hidden; background:#fdfdfd; cursor:pointer; box-shadow:0 10px 30px rgba(0,0,0,0.03); transition:transform 0.4s cubic-bezier(0.16,1,0.3,1), box-shadow 0.4s ease; }
        .gallery-item:hover { transform:translateY(-6px); box-shadow:0 18px 40px rgba(0,0,0,0.1); }
        .gallery-img-wrapper { position:relative; width:100%; overflow:hidden; display:block; }
        .gallery-item img { width:100%; height:auto; display:block; object-fit:cover; transition:transform 0.8s cubic-bezier(0.16,1,0.3,1); }
        .gallery-item:hover img { transform:scale(1.06); }
        .hover-frame-accent { position:absolute; inset:12px; border:1px solid rgba(255,255,255,0.7); border-radius:10px; opacity:0; transform:scale(0.95); transition:all 0.35s ease; pointer-events:none; z-index:2; }
        .gallery-item:hover .hover-frame-accent { opacity:1; transform:scale(1); }
        .zoom-icon-badge { position:absolute; top:20px; right:20px; width:36px; height:36px; background:rgba(255,255,255,0.85); backdrop-filter:blur(8px); border-radius:50%; display:flex; align-items:center; justify-content:center; color:var(--text-primary); font-size:0.8rem; opacity:0; transform:translateY(-8px); transition:all 0.3s ease; z-index:3; }
        .gallery-item:hover .zoom-icon-badge { opacity:1; transform:translateY(0); }
        .gallery-caption { position:absolute; bottom:0; left:0; right:0; padding:24px 20px 16px; background:linear-gradient(to top, rgba(0,0,0,0.75) 0%, rgba(0,0,0,0) 100%); color:#fff; font-family:'Playfair Display',serif; font-size:0.92rem; font-style:italic; text-align:left; transform:translateY(10px); opacity:0; transition:all 0.35s ease; z-index:3; }
        .gallery-item:hover .gallery-caption { opacity:1; transform:translateY(0); }
        .empty-gallery { padding:80px 20px; color:var(--text-muted); text-align:center; }
        .empty-gallery i { font-size:2.5rem; margin-bottom:12px; opacity:0.5; }

        /* Table Finder */
        .table-finder-section { max-width:700px; margin:0 auto; padding:40px 24px 60px; text-align:center; }
        .table-finder-container { background:var(--bg-card-white); border-radius:20px; padding:40px 30px; border:1px solid var(--border-light); box-shadow:0 15px 40px rgba(0,0,0,0.02); margin-top:30px; }
        .search-box { display:flex; gap:12px; margin-bottom:30px; }
        .table-search-input { flex:1; padding:14px 20px; border:2px solid var(--border-light); border-radius:10px; font-size:1rem; outline:none; transition:border-color 0.3s; }
        .table-search-input:focus { border-color:var(--accent-gold); }
        .table-search-btn { padding:14px 30px; background:var(--accent-gold); color:#fff; border:none; border-radius:10px; font-size:0.9rem; font-weight:600; cursor:pointer; text-transform:uppercase; letter-spacing:1px; transition:background 0.3s; }
        .table-result { text-align:center; padding:20px 0; max-width:500px; margin:0 auto; }
        .table-result .found { background:#fff; border-radius:12px; padding:24px 20px; margin-bottom:14px; border:1px solid #e8e0d6; animation:slideInResult 0.4s ease; }
        @keyframes slideInResult { from{opacity:0; transform:translateY(20px);} to{opacity:1; transform:translateY(0);} }
        .table-result .found .guest-name { font-family:'Playfair Display',serif; font-size:1.4rem; color:var(--accent-gold); margin-bottom:2px; }
        .table-result .found .family-name { color:#999; font-size:0.9rem; margin:2px 0 6px; }
        .table-result .found .table-number { font-size:2.8rem; font-weight:700; color:var(--accent-gold); display:block; margin-top:6px; }
        .table-result .found .table-label { font-size:0.6rem; letter-spacing:2px; text-transform:uppercase; color:#bbb; display:block; margin-top:4px; }
        .table-result .not-found { background:#fff; border-radius:12px; padding:40px 20px; border:2px dashed #e5e0d8; animation:slideInResult 0.4s ease; }
        .table-result .not-found i { font-size:2.2rem; color:#e5e0d8; margin-bottom:12px; display:block; }
        .table-result .not-found p { color:#999; font-size:1.1rem; margin:4px 0; }
        .table-result .not-found p:last-child { font-size:0.85rem; color:#bbb; margin-top:6px; }

        /* RSVP */
        .rsvp-desc { font-size:0.9rem; color:var(--text-muted); margin-bottom:35px; font-weight:300; }
        .rsvp-form { text-align:left; display:flex; flex-direction:column; gap:20px; background:var(--bg-card-white); padding:40px 30px; border-radius:20px; border:1px solid var(--border-light); box-shadow:0 15px 40px rgba(0,0,0,0.02); }
        .form-group { display:flex; flex-direction:column; gap:8px; width:100%; }
        .form-group label { font-size:0.75rem; font-weight:600; color:var(--text-primary); letter-spacing:1px; text-transform:uppercase; }
        .form-group input, .form-group select { width:100%; padding:16px; border-radius:10px; border:1px solid var(--border-light); background:var(--bg-main); color:var(--text-primary); font-size:0.9rem; outline:none; transition:0.3s ease; }
        .form-group input:focus, .form-group select:focus { border-color:var(--accent-gold); background:#fff; box-shadow:0 4px 12px rgba(188,156,108,0.05); }
        .select-wrapper { position:relative; }
        .select-wrapper::after { content:'\f107'; font-family:'Font Awesome 6 Free'; font-weight:900; position:absolute; right:18px; top:50%; transform:translateY(-50%); color:var(--accent-gold); pointer-events:none; }
        .form-group select { appearance:none; -webkit-appearance:none; padding-right:40px; }
        .rsvp-btn { width:100%; padding:18px; border-radius:10px; border:none; background:var(--primary-color); color:#fff; font-size:0.85rem; font-weight:600; text-transform:uppercase; letter-spacing:2px; margin-top:10px; cursor:pointer; transition:0.3s; }
        footer { text-align:center; padding:60px 24px 40px; border-top:1px solid var(--border-light); }
        .footer-headline { font-family:'Playfair Display',serif; font-size:1.6rem; color:var(--text-primary); margin-bottom:6px; }
        .footer-copy { font-size:0.7rem; color:var(--text-light-gray); letter-spacing:1px; }

        .reveal-on-scroll { opacity:0; transform:translateY(30px); transition:opacity 1s cubic-bezier(0.215,0.61,0.355,1), transform 1s cubic-bezier(0.215,0.61,0.355,1); }
        .reveal-on-scroll.active { opacity:1; transform:translateY(0); }

        /* Media Queries */
        @media(max-width:900px){ .gallery-masonry { column-count:2; } }
        @media(min-width:992px){
            .profiles-section { gap:60px; padding-top:120px; }
            .img-zoom-wrapper { height:540px; }
            .getting-married h2, .location-section h2, .timeline-section h2, .rsvp-section h2 { font-size:3rem; }
            .form-row-desktop { display:flex; gap:20px; }
        }
        @media(max-width:768px){
            .profiles-section, .feature-buttons-container, .search-box { flex-direction:column; }
            .profiles-section { gap:48px; }
            .profile-card { max-width:340px; }
            .img-zoom-wrapper { height:400px; }
            .profile-divider { margin:-20px 0; }
            .venue-card { grid-template-columns:1fr; text-align:center; }
            .venue-info-side { padding:40px 24px; order:1; }
            .venue-icon-badge { margin:0 auto 16px; }
            .venue-actions { flex-direction:column; gap:10px; }
            .venue-map-side { order:2; height:300px; min-height:300px; border-top:1px solid var(--border-light); }
            .feature-btn, .table-search-btn { width:100%; justify-content:center; }
        }
        @media(max-width:600px){
            .countdown-orbital-wrapper { gap:12px; }
            .time-ring-unit { width:80px; height:80px; }
            .time-content { width:70px; height:70px; }
            .time-content span { font-size:1.35rem; }
            .time-content label { font-size:0.5rem; letter-spacing:0.8px; }
        }
        @media(max-width:576px){
            .accordion-header { padding:16px; }
            .header-left { gap:10px; }
            .step-num { display:none; }
            .accordion-body { padding:0 16px 20px; }
        }
    </style>
</head>
<body>
    <div class="petals-container" id="petalsContainer"></div>
    <script>
        window.invitationConfig = <?= json_encode($config); ?>;
        window.tableData = <?= json_encode($config['table_entries']); ?>;
    </script>

    <div class="invitation-wrapper">
        <section class="hero-section">
            <svg class="brush-mask" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2880 180" preserveAspectRatio="none">
                <path d="M0,100 Q180,30 360,100 T720,100 T1080,100 T1440,100 Q1620,30 1800,100 T2160,100 T2520,100 T2880,100 L2880,180 L0,180 Z"></path>
            </svg>
        </section>

        <section class="details-section">
            <div class="container">
                <p class="save-the-date">Save The Date</p>
                <div class="names-card">
                    <h1 class="couple-title">
                        <span class="name-highlight"><?= htmlspecialchars($config['bride_name']); ?></span><br>
                        <span class="ampersand">&amp;</span><br>
                        <span class="name-highlight"><?= htmlspecialchars($config['groom_name']); ?></span>
                    </h1>
                </div>
                <div class="title-separator"></div>
                <div class="date-row">
                    <span class="date-month"><?= htmlspecialchars($config['month']); ?></span>
                    <span class="date-day"><?= htmlspecialchars($config['day']); ?></span>
                    <span class="date-year"><?= htmlspecialchars($config['year']); ?></span>
                </div>
                <div class="scroll-indicator">
                    <p>SCROLL DOWN</p>
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </div>
        </section>

        <button id="audioToggle" class="audio-fab muted" aria-label="Toggle background music">
            <div class="bars"><span></span><span></span><span></span><span></span></div>
            <i class="fa-solid fa-volume-xmark mute-icon"></i>
        </button>

        <?php if ($config['show_profiles'] == 1): ?>
        <section class="profiles-section reveal-on-scroll" id="mainContent">
            <div class="profile-card">
                <div class="arch-frame-wrapper">
                    <div class="arch-border-accent"></div>
                    <div class="img-zoom-wrapper">
                        <img src="<?= getMediaPath($config['bride_image'], 'images', $config['username']); ?>" alt="The Bride" class="profile-img">
                    </div>
                    <div class="profile-badge">
                        <span class="badge-role">THE BRIDE</span>
                        <span class="badge-name"><?= htmlspecialchars($config['bride_name']); ?></span>
                    </div>
                </div>
            </div>
            <div class="profile-divider"><span class="ampersand-divider">&amp;</span></div>
            <div class="profile-card">
                <div class="arch-frame-wrapper">
                    <div class="arch-border-accent"></div>
                    <div class="img-zoom-wrapper">
                        <img src="<?= getMediaPath($config['groom_image'], 'images', $config['username']); ?>" alt="The Groom" class="profile-img">
                    </div>
                    <div class="profile-badge">
                        <span class="badge-role">THE GROOM</span>
                        <span class="badge-name"><?= htmlspecialchars($config['groom_name']); ?></span>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <section class="getting-married reveal-on-scroll">
            <div class="heart-icon-wrap"><i class="fa-regular fa-heart"></i></div>
            <p class="section-tag">WE ARE</p>
            <h2>Getting Married</h2>
            <p class="description"><?= nl2br(htmlspecialchars($config['sub_headline'])); ?></p>
            <p class="signature">- <?= htmlspecialchars($config['bride_name']); ?> &amp; <?= htmlspecialchars($config['groom_name']); ?> -</p>
        </section>

        <section class="countdown-section reveal-on-scroll">
            <div class="countdown-orbital-wrapper">
                <?php foreach(['days' => 'DAYS', 'hours' => 'HOURS', 'minutes' => 'MINS', 'seconds' => 'SECS'] as $id => $label): ?>
                <div class="time-ring-unit">
                    <div class="ring-backdrop"></div>
                    <div class="time-content">
                        <span id="<?= $id; ?>">00</span>
                        <label><?= $label; ?></label>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="location-section reveal-on-scroll">
            <p class="section-tag">JOIN US AT</p>
            <h2>Location</h2>
            <div class="venue-card">
                <div class="venue-info-side">
                    <div class="venue-icon-badge"><i class="fa-solid fa-location-dot"></i></div>
                    <h3><?= htmlspecialchars($config['venue_title']); ?></h3>
                    <p class="venue-loc-sub"><?= htmlspecialchars($config['venue_location']); ?></p>
                    <p class="venue-address"><?= htmlspecialchars($config['venue_address']); ?></p>
                    <div class="time-badge-container">
                        <div class="time-badge"><i class="fa-regular fa-clock"></i> <?= htmlspecialchars($config['event_time']); ?></div>
                    </div>
                    <div class="venue-actions">
                        <a href="<?= htmlspecialchars($config['google_maps_url']); ?>" target="_blank" class="action-btn map-btn"><i class="fa-solid fa-location-dot"></i> OPEN IN MAPS</a>
                        <button class="action-btn calendar-btn" id="addToCalendar"><i class="fa-regular fa-calendar-plus"></i> ADD TO CALENDAR</button>
                    </div>
                    <a href="#" class="gcal-link" id="googleCalHint">Or Add to Google Calendar</a>
                </div>
                <div class="venue-map-side">
                    <div class="map-wrapper">
                        <iframe class="live-map-iframe" src="https://maps.google.com/maps?q=<?= urlencode($config['venue_title'].' '.$config['venue_address']); ?>&t=&z=15&ie=UTF8&iwloc=&output=embed" loading="lazy"></iframe>
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
                    $currentIcon = !empty($item['icon']) ? $item['icon'] : ($default_icons[$index] ?? 'fa-star');
                ?>
                <details class="timeline-accordion-card" <?= $index === 0 ? 'open' : ''; ?>>
                    <summary class="accordion-header">
                        <div class="header-left">
                            <span class="step-num">0<?= $index + 1; ?></span>
                            <div class="icon-circle"><i class="fa-solid <?= $currentIcon; ?>"></i></div>
                            <h3><?= htmlspecialchars($item['title']); ?></h3>
                        </div>
                        <div class="header-right">
                            <span class="time-stamp"><?= htmlspecialchars($item['time']); ?></span>
                            <i class="fa-solid fa-chevron-down toggle-icon"></i>
                        </div>
                    </summary>
                    <div class="accordion-body"><p><?= htmlspecialchars($item['description']); ?></p></div>
                </details>
                <?php endforeach; ?>
            </div>
        </section>

        <?php if ($config['has_gallery'] || $config['has_table_finder']): ?>
        <section class="feature-buttons-section reveal-on-scroll">
            <div class="feature-buttons-container">
                <?php if ($config['has_gallery']): ?>
                <button class="feature-btn" id="galleryToggle"><i class="fa-regular fa-images"></i><span>Gallery</span></button>
                <?php endif; ?>
                <?php if ($config['has_table_finder']): ?>
                <button class="feature-btn" id="tableFinderToggle"><i class="fa-solid fa-magnifying-glass"></i><span>Find Your Table</span></button>
                <?php endif; ?>
            </div>
        </section>
        <?php endif; ?>

        <?php if ($config['has_gallery']): ?>
        <section class="gallery-section reveal-on-scroll" id="gallerySection" style="display:none;">
            <p class="section-tag">OUR MEMORIES</p>
            <h2>Gallery</h2>
            <div class="gallery-masonry">
                <?php if (empty($config['gallery_images'])): ?>
                    <div class="empty-gallery"><i class="fa-regular fa-image"></i><p>No gallery images yet</p></div>
                <?php else: foreach ($config['gallery_images'] as $index => $img): ?>
                    <div class="gallery-item" data-index="<?= $index; ?>">
                        <div class="gallery-img-wrapper">
                            <img src="<?= getMediaPath($img['image_path'], 'images', $config['username']); ?>" alt="<?= htmlspecialchars($img['caption'] ?? 'Wedding memory'); ?>" loading="lazy" onerror="this.style.background='#e8d5c4';this.src='';this.alt='Image not found';">
                            <div class="hover-frame-accent"></div>
                            <div class="zoom-icon-badge"><i class="fa-solid fa-expand"></i></div>
                        </div>
                        <?php if (!empty($img['caption'])): ?>
                        <div class="gallery-caption"><span><?= htmlspecialchars($img['caption']); ?></span></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </section>
        <?php endif; ?>

        <?php if ($config['has_table_finder']): ?>
        <section class="table-finder-section reveal-on-scroll" id="tableFinderSection" style="display:none;">
            <p class="section-tag">FIND YOUR TABLE</p>
            <h2>Find Your Table</h2>
            <div class="table-finder-container">
                <div class="search-box">
                    <input type="text" id="tableSearchInput" placeholder="Enter your name..." class="table-search-input">
                    <button id="tableSearchBtn" class="table-search-btn"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
                </div>
                <div id="tableResult" class="table-result">
                    <div class="not-found"><i class="fa-solid fa-magnifying-glass"></i><p>Search for your name to find your table</p></div>
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
                    <div class="form-group"><label>Your Name *</label><input type="text" id="guestName" placeholder="Enter your full name" required></div>
                    <div class="form-group"><label>Email Address</label><input type="email" id="guestEmail" placeholder="your.email@example.com"></div>
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
            <p class="footer-headline"><?= htmlspecialchars($config['couple_headline']); ?></p>
            <span class="footer-copy">© <?= date('Y'); ?></span>
        </footer>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const config = window.invitationConfig || {}, tableData = window.tableData || [];

        // 1. Falling Petals
        const container = document.getElementById('petalsContainer');
        function createPetals() {
            if (!container) return;
            container.innerHTML = '';
            const emojis = ['🍃', '💍', '💚', '🤍', '🕊️', '💚', '💍', '❄️'];
            const count = window.innerWidth < 768 ? 18 : 35;
            for (let i = 0; i < count; i++) {
                const el = document.createElement('div');
                el.className = 'petal';
                el.textContent = emojis[Math.floor(Math.random() * emojis.length)];
                Object.assign(el.style, {
                    left: (Math.random() * 100) + '%',
                    animationDelay: (Math.random() * 12) + 's',
                    animationDuration: (10 + Math.random() * 15) + 's',
                    fontSize: (16 + Math.random() * 14) + 'px',
                    opacity: (0.4 + Math.random() * 0.5)
                });
                container.appendChild(el);
            }
        }
        setTimeout(createPetals, 200);
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(createPetals, 500);
        });

        // 2. Scroll Reveal
        const observer = new IntersectionObserver(entries => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    e.target.classList.add('active');
                    observer.unobserve(e.target);
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -50px 0px' });
        document.querySelectorAll('.reveal-on-scroll').forEach(el => observer.observe(el));

        // 3. Audio Player
        let bgAudio = null, audioLoaded = false;
        const audioBtn = document.getElementById('audioToggle');

        function initAudio() {
            let src = config.bg_audio || '';
            if (!src) return;
            if (!src.includes('../uploads/audio/')) src = '../uploads/audio/' + config.username + '/' + src;
            try {
                bgAudio = new Audio(src);
                bgAudio.loop = true;
                bgAudio.volume = 0.3;
                bgAudio.preload = 'auto';
                bgAudio.addEventListener('canplaythrough', () => audioLoaded = true);
                bgAudio.addEventListener('playing', () => audioBtn?.classList.remove('muted'));
                bgAudio.addEventListener('pause', () => audioBtn?.classList.add('muted'));
            } catch (err) {
                console.warn('Audio init error:', err);
            }
        }

        function playAudio() {
            if (!bgAudio) initAudio();
            if (bgAudio && bgAudio.paused) {
                bgAudio.play().then(() => audioBtn?.classList.remove('muted')).catch(() => {});
            }
        }

        const autoPlayOnce = () => {
            playAudio();
            ['click', 'touchstart', 'scroll'].forEach(evt => document.removeEventListener(evt, autoPlayOnce));
        };
        ['click', 'touchstart', 'scroll'].forEach(evt => document.addEventListener(evt, autoPlayOnce, { once: true }));

        audioBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (!bgAudio) initAudio();
            if (!bgAudio) return;
            if (bgAudio.paused) {
                bgAudio.play().then(() => audioBtn.classList.remove('muted')).catch(() => {});
            } else {
                bgAudio.pause();
                audioBtn.classList.add('muted');
            }
        });
        initAudio();

        // 4. Countdown Timer & Celebration
        if (config.countdown_target) {
            const target = new Date(config.countdown_target).getTime();
            const timer = setInterval(() => {
                const diff = target - Date.now();
                if (diff <= 0) {
                    clearInterval(timer);
                    const wrap = document.querySelector('.countdown-orbital-wrapper');
                    if (wrap) {
                        wrap.innerHTML = `
                            <div class="celebration-screen">
                                <div class="floating-hearts">❤ 💖 💕 💗 💞</div>
                                <div class="rings">💍</div>
                                <h2>Our Wedding Celebration Has Begun</h2>
                                <p>Thank you for being part of our special day.</p>
                                <div class="confetti">✨ 🎉 ✨ 🎊 ✨ 🎉 ✨</div>
                            </div>
                        `;
                        const c = wrap.querySelector('.celebration-screen');
                        const colors = ['#D4AF37','#FFD700','#E8C547','#FFFFFF','#F8E6B3','#F4C430'];
                        for (let i = 0; i < 80; i++) {
                            const p = document.createElement('span');
                            p.className = 'confetti-piece';
                            Object.assign(p.style, {
                                left: (Math.random() * 100) + '%',
                                animationDelay: (Math.random() * 3) + 's',
                                animationDuration: (3 + Math.random() * 4) + 's',
                                background: colors[Math.floor(Math.random() * colors.length)]
                            });
                            c.appendChild(p);
                        }
                    }
                    return;
                }
                const setVal = (id, val) => {
                    const el = document.getElementById(id);
                    if (el) el.textContent = String(val).padStart(2, '0');
                };
                setVal('days', Math.floor(diff / 864e5));
                setVal('hours', Math.floor((diff % 864e5) / 36e5));
                setVal('minutes', Math.floor((diff % 36e5) / 6e4));
                setVal('seconds', Math.floor((diff % 6e4) / 1e3));
            }, 1000);
        }

        // 5. Gallery & Table Finder Toggles
        const galleryBtn = document.getElementById('galleryToggle');
        const gallerySec = document.getElementById('gallerySection');
        const tableBtn = document.getElementById('tableFinderToggle');
        const tableSec = document.getElementById('tableFinderSection');

        function togglePanel(triggerBtn, targetSec, alternateBtn, alternateSec) {
            if (!triggerBtn || !targetSec) return;
            triggerBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (alternateSec && alternateSec.style.display !== 'none') {
                    alternateSec.style.display = 'none';
                    alternateBtn?.classList.remove('active');
                }
                const isHidden = targetSec.style.display === 'none' || targetSec.style.display === '';
                targetSec.style.display = isHidden ? 'block' : 'none';
                triggerBtn.classList.toggle('active', isHidden);
                if (isHidden) {
                    setTimeout(() => targetSec.scrollIntoView({ behavior: 'smooth', block: 'start' }), 200);
                }
            });
        }
        togglePanel(galleryBtn, gallerySec, tableBtn, tableSec);
        togglePanel(tableBtn, tableSec, galleryBtn, gallerySec);

        // 6. Table Finder Search
        const searchInput = document.getElementById('tableSearchInput');
        const searchBtn = document.getElementById('tableSearchBtn');
        const tableResult = document.getElementById('tableResult');

        function esc(str) {
            return str ? String(str).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])) : '';
        }

        function searchTable() {
            const q = searchInput?.value.trim().toLowerCase();
            if (!q) {
                if (tableResult) tableResult.innerHTML = '<div class="not-found"><i class="fa-solid fa-magnifying-glass"></i><p>Please enter your name</p></div>';
                return;
            }
            const res = tableData.filter(i => 
                (i.guest_name && i.guest_name.toLowerCase().includes(q)) || 
                (i.family_name && i.family_name.toLowerCase().includes(q))
            );
            if (!tableResult) return;
            if (res.length > 0) {
                tableResult.innerHTML = res.map(r => `
                    <div class="found">
                        <div class="guest-name">${esc(r.guest_name)}</div>
                        ${r.family_name ? `<div class="family-name">👨‍👩‍👧‍👦 ${esc(r.family_name)}</div>` : ''}
                        <span class="table-label">Table</span>
                        <div class="table-number">${esc(r.table_number)}</div>
                    </div>
                `).join('');
            } else {
                tableResult.innerHTML = `
                    <div class="not-found">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <p>No guest found with name "<strong>${esc(searchInput.value)}</strong>"</p>
                        <p>Please check the spelling or try a different name.</p>
                    </div>
                `;
            }
        }
        searchBtn?.addEventListener('click', (e) => { e.preventDefault(); searchTable(); });
        searchInput?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchTable();
            }
        });

        // 7. Google Calendar
        function dispatchCalendar(e) {
            if (e) e.preventDefault();
            const s = new Date(config.countdown_target || new Date().toISOString());
            const end = new Date(s.getTime() + 6 * 36e5);
            const fmt = d => d.toISOString().replace(/-|:|\.\d+/g, '');
            const url = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent((config.couple_headline || 'Wedding') + ' Wedding')}&dates=${fmt(s)}/${fmt(end)}&details=${encodeURIComponent('Wedding celebration of ' + (config.couple_headline || ''))}&location=${encodeURIComponent((config.venue_title || '') + ', ' + (config.venue_address || ''))}`;
            window.open(url, '_blank');
        }
        document.getElementById('addToCalendar')?.addEventListener('click', dispatchCalendar);
        document.getElementById('googleCalHint')?.addEventListener('click', dispatchCalendar);

        // 8. Navigation Jump
        document.querySelector('.scroll-indicator')?.addEventListener('click', () => {
            document.getElementById('mainContent')?.scrollIntoView({ behavior: 'smooth' });
        });

        // 9. RSVP WhatsApp Dispatch
        document.getElementById('rsvpForm')?.addEventListener('submit', (e) => {
            e.preventDefault();
            const name = document.getElementById('guestName')?.value.trim();
            const email = document.getElementById('guestEmail')?.value.trim();
            const status = document.getElementById('attendanceStatus')?.value;
            const phone = config.whatsapp_phone || '';

            if (!name || !status) {
                alert('Please complete all mandatory entry forms.');
                return;
            }
            const msg = `*WEDDING RSVP CONFIRMATION*\n\n*Guest Name:* ${name}\n${email ? '*Email:* ' + email + '\n' : ''}*Attendance:* ${status}\n\nWedding: ${config.couple_headline}\nDate: ${config.wedding_date}`;
            window.open(`https://api.whatsapp.com/send?phone=${phone}&text=${encodeURIComponent(msg)}`, '_blank');
        });
    });
    </script>
</body>
</html>