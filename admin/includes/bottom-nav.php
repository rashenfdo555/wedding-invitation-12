<!-- ========================================
   BOTTOM NAVIGATION - PREMIUM
   ======================================== -->
<nav class="bottom-nav" role="navigation" aria-label="Main navigation">
    <a href="dashboard.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
        <span class="nav-icon"><i class="fa-solid fa-house"></i></span>
        <span class="nav-label">Home</span>
    </a>
    <a href="edit-invitation.php" class="nav-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['edit-invitation.php', 'create-invitation.php']) ? 'active' : ''; ?>">
        <span class="nav-icon"><i class="fa-regular fa-pen-to-square"></i></span>
        <span class="nav-label">Edit</span>
    </a>
    <?php if ($has_gallery ?? false): ?>
    <a href="gallery.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'gallery.php' ? 'active' : ''; ?>">
        <span class="nav-icon"><i class="fa-regular fa-image"></i></span>
        <span class="nav-label">Photos</span>
    </a>
    <?php else: ?>
    <a href="#" class="nav-item" style="opacity:0.3;pointer-events:none;">
        <span class="nav-icon"><i class="fa-regular fa-image"></i></span>
        <span class="nav-label">Photos</span>
    </a>
    <?php endif; ?>
    <?php if ($has_table_finder ?? false): ?>
    <a href="table-finder.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'table-finder.php' ? 'active' : ''; ?>">
        <span class="nav-icon"><i class="fa-solid fa-users"></i></span>
        <span class="nav-label">Guests</span>
        <?php if (isset($table_entries) && count($table_entries) > 0): ?>
        <span class="nav-badge"><?php echo min(count($table_entries), 99); ?></span>
        <?php endif; ?>
    </a>
    <?php else: ?>
    <a href="#" class="nav-item" style="opacity:0.3;pointer-events:none;">
        <span class="nav-icon"><i class="fa-solid fa-users"></i></span>
        <span class="nav-label">Guests</span>
    </a>
    <?php endif; ?>
    <a href="logout.php" class="nav-item">
        <span class="nav-icon"><i class="fa-solid fa-right-from-bracket"></i></span>
        <span class="nav-label">logout</span>
    </a>
</nav>

<style>
    /* ========================================
       BOTTOM NAVIGATION
       ======================================== */
    .bottom-nav {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        height: var(--nav-height, 72px);
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-top: 1px solid rgba(0,0,0,0.04);
        display: flex;
        align-items: center;
        justify-content: space-around;
        padding: 0 8px 8px;
        z-index: 999;
        box-shadow: 0 -4px 20px rgba(0,0,0,0.04);
        max-width: var(--max-width, 480px);
        margin: 0 auto;
    }

    .bottom-nav .nav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2px;
        text-decoration: none;
        color: var(--gray-400, #B0B0C4);
        font-size: 10px;
        font-weight: 500;
        padding: 4px 12px;
        border-radius: var(--radius-sm, 12px);
        transition: var(--transition, all 0.3s cubic-bezier(0.4, 0, 0.2, 1));
        position: relative;
        min-width: 48px;
        background: transparent;
        border: none;
        cursor: pointer;
        font-family: 'Inter', sans-serif;
    }

    .bottom-nav .nav-item .nav-icon {
        font-size: 20px;
        transition: var(--transition, all 0.3s cubic-bezier(0.4, 0, 0.2, 1));
        position: relative;
    }

    .bottom-nav .nav-item .nav-label {
        font-size: 9px;
        font-weight: 600;
        letter-spacing: 0.2px;
        text-transform: uppercase;
    }

    .bottom-nav .nav-item.active {
        color: var(--primary, #6C5CE7);
    }

    .bottom-nav .nav-item.active .nav-icon {
        transform: translateY(-2px);
    }

    .bottom-nav .nav-item.active::before {
        content: '';
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 20px;
        height: 3px;
        background: var(--primary-gradient, linear-gradient(135deg, #6C5CE7 0%, #A29BFE 100%));
        border-radius: 0 0 4px 4px;
    }

    .bottom-nav .nav-item:active {
        transform: scale(0.92);
    }

    .bottom-nav .nav-item .nav-badge {
        position: absolute;
        top: -2px;
        right: 4px;
        width: 18px;
        height: 18px;
        background: var(--rose, #FD79A8);
        color: #fff;
        border-radius: 50%;
        font-size: 9px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #fff;
    }

    @media (min-width: 641px) {
        .bottom-nav {
            max-width: 768px;
            border-radius: 20px 20px 0 0;
            border: 1px solid rgba(0,0,0,0.04);
        }
    }

    @media (min-width: 1025px) {
        .bottom-nav {
            max-width: 1024px;
            padding: 0 20px 8px;
            justify-content: center;
            gap: 40px;
        }
        .bottom-nav .nav-item {
            padding: 4px 20px;
            min-width: 60px;
        }
        .bottom-nav .nav-item .nav-icon {
            font-size: 22px;
        }
        .bottom-nav .nav-item .nav-label {
            font-size: 10px;
        }
    }

    @media (min-width: 1280px) {
        .bottom-nav {
            max-width: 1200px;
            gap: 60px;
        }
    }
</style>

<script>
    // ========================================
    // BOTTOM NAV ACTIVE STATE
    // ========================================
    document.querySelectorAll('.bottom-nav .nav-item').forEach(item => {
        item.addEventListener('click', function(e) {
            if (this.getAttribute('href') === '#') {
                e.preventDefault();
                return;
            }
            document.querySelectorAll('.bottom-nav .nav-item').forEach(n => n.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // ========================================
    // HAPTIC FEEDBACK
    // ========================================
    document.querySelectorAll('.bottom-nav .nav-item').forEach(el => {
        el.addEventListener('touchstart', function() {
            if (navigator.vibrate) navigator.vibrate(6);
        }, { passive: true });
    });
</script>