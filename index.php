<?php
session_start();
require_once 'config/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ImmuTrack · Vaccination Management System</title>
<meta name="description" content="ImmuTrack connects parents, hospitals, and administrators on one platform to schedule, approve, and track childhood vaccinations.">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="assets/css/style.css">
<style>
  html { scroll-behavior: smooth; }
  body { background: #fff; }

  /* ---------- Reveal-on-scroll ---------- */
  .reveal { opacity: 0; transform: translateY(28px); transition: opacity .7s cubic-bezier(.16,1,.3,1), transform .7s cubic-bezier(.16,1,.3,1); }
  .reveal.in-view { opacity: 1; transform: translateY(0); }
  .reveal-delay-1.in-view { transition-delay: .08s; }
  .reveal-delay-2.in-view { transition-delay: .16s; }
  .reveal-delay-3.in-view { transition-delay: .24s; }
  .reveal-delay-4.in-view { transition-delay: .32s; }

  /* ---------- Nav ---------- */
  .lp-nav {
    position: fixed; top: 0; left: 0; right: 0; z-index: 200;
    padding: 16px 0; transition: background .25s ease, box-shadow .25s ease, padding .25s ease;
  }
  .lp-nav.scrolled { 
    background: radial-gradient(circle at 15% 8%, #123A6B 0%, var(--ink, #0b192c) 48%, #04101F 100%); 
    backdrop-filter: blur(10px); 
    box-shadow: 0 4px 20px rgba(0,0,0,.3); 
    padding: 11px 0; 
  }
  .lp-nav-inner { max-width: 1160px; margin: 0 auto; padding: 0 28px; display: flex; align-items: center; justify-content: space-between; }
  .lp-nav-brand { display: flex; align-items: center; gap: 10px; font-family: var(--font-poppins); font-weight: 600; font-size: 18px; color: #fff; transition: color .25s ease; }
  .lp-nav.scrolled .lp-nav-brand { color: #fff; }
  .lp-nav-links { display: flex; align-items: center; gap: 30px; }
  .lp-nav-links a { font-size: 13.8px; font-weight: 600; color: rgba(255,255,255,.75); transition: color .2s ease; }
  .lp-nav.scrolled .lp-nav-links a { color: rgba(255,255,255,.85); }
  .lp-nav-links a:hover, .lp-nav.scrolled .lp-nav-links a:hover { color: #38BDF8; }
  .lp-nav-right { display: flex; align-items: center; gap: 14px; }
  .lp-nav-cta { padding: 8px 18px !important; }
  @media (max-width: 760px) { .lp-nav-links { display: none; } }

  /* ---------- Hero ---------- */
  .lp-hero {
    position: relative; overflow: hidden;
    background: radial-gradient(circle at 15% 8%, #123A6B 0%, var(--ink) 48%, #04101F 100%);
    padding: 150px 28px 110px;
  }
  .lp-hero::before, .lp-hero::after { content: ""; position: absolute; border-radius: 50%; border: 1px dashed rgba(56,189,248,.22); }
  .lp-hero::before { width: 420px; height: 420px; top: -140px; right: -120px; animation: floatSlow 11s ease-in-out infinite; }
  .lp-hero::after { width: 260px; height: 260px; bottom: -100px; left: 4%; animation: floatSlow 9s ease-in-out infinite reverse; }
  .lp-hero-glow { position: absolute; width: 520px; height: 520px; border-radius: 50%; background: radial-gradient(circle, rgba(37,99,235,.35), transparent 70%); top: 10%; right: 8%; filter: blur(10px); pointer-events: none; }

  .lp-hero-inner { max-width: 1160px; margin: 0 auto; position: relative; z-index: 2; display: grid; grid-template-columns: 1.05fr .95fr; gap: 50px; align-items: center; }
  @media (max-width: 980px) { .lp-hero-inner { grid-template-columns: 1fr; } }

  .lp-eyebrow {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(56,189,248,.12); border: 1px solid rgba(56,189,248,.3);
    color: #93C5FD; font-size: 12.5px; font-weight: 700; letter-spacing: .04em;
    padding: 7px 14px; border-radius: 30px; margin-bottom: 22px;
  }
  .lp-eyebrow i { font-size: 11px; }

  .lp-hero h1 { color: #fff; font-size: 46px; line-height: 1.16; margin: 0 0 20px; max-width: 560px; }
  .lp-hero h1 .accent { color: #38BDF8; }
  .lp-hero-sub { color: #A9BCCC; font-size: 16px; line-height: 1.65; max-width: 480px; margin-bottom: 34px; }

  .lp-hero-cta { display: flex; gap: 14px; flex-wrap: wrap; margin-bottom: 40px; }
  .btn-lg { padding: 13px 24px; font-size: 15px; }
  .lp-hero .btn-outline { background: transparent; border-color: rgba(255,255,255,.25); color: #fff; }
  .lp-hero .btn-outline:hover { background: rgba(255,255,255,.08); }

  .lp-hero-trust { display: flex; gap: 26px; flex-wrap: wrap; }
  .lp-trust-item { display: flex; align-items: center; gap: 9px; font-size: 12.8px; color: #93A9BC; font-weight: 500; }
  .lp-trust-item i { color: #38BDF8; font-size: 13px; }

  /* Hero visual mock */
  .lp-hero-visual { position: relative; display: flex; justify-content: center; align-items: center; min-height: 380px; }
  .lp-mock-card {
    width: 100%; max-width: 330px; background: #fff; border-radius: 18px; padding: 22px 22px 20px;
    box-shadow: 0 30px 60px -20px rgba(0,0,0,.45); position: relative; z-index: 2;
    animation: floatSlow 6s ease-in-out infinite;
  }
  .lp-mock-head { display: flex; align-items: center; gap: 12px; padding-bottom: 16px; margin-bottom: 14px; border-bottom: 1px solid var(--line); }
  .lp-mock-avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(145deg, var(--teal), var(--ink)); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; }
  .lp-mock-name { font-weight: 700; font-size: 14.5px; color: var(--ink); }
  .lp-mock-sub { font-size: 11.5px; color: var(--slate); }
  .lp-mock-row { display: flex; align-items: center; justify-content: space-between; padding: 9px 0; font-size: 13px; color: var(--ink); font-weight: 500; }
  .lp-mock-row:not(:last-child) { border-bottom: 1px dashed var(--line); }

  .lp-float-badge {
    position: absolute; background: #fff; border-radius: 12px; padding: 10px 15px;
    box-shadow: var(--shadow-lift); display: flex; align-items: center; gap: 8px;
    font-size: 12.5px; font-weight: 700; color: var(--ink); z-index: 3;
  }
  .lp-float-badge i { font-size: 13px; }
  .lp-badge-1 { top: 6%; left: -4%; color: var(--teal-dark); animation: floatSlow 5s ease-in-out infinite; }
  .lp-badge-1 i { color: var(--teal); }
  .lp-badge-2 { bottom: 10%; right: -6%; animation: floatSlow 7s ease-in-out infinite reverse; }
  .lp-badge-2 i { color: var(--blue); }
  @media (max-width: 980px) { .lp-badge-1, .lp-badge-2 { display: none; } }

  /* ---------- Section shell ---------- */
  .lp-section { padding: 80px 28px; }
  .lp-section-inner { max-width: 1160px; margin: 0 auto; }
  .lp-section-head { text-align: center; max-width: 620px; margin: 0 auto 40px; }
  .lp-section-tag { display: inline-block; font-size: 12.5px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--teal); margin-bottom: 10px; }
  .lp-section-title { font-size: 30px; color: var(--ink); margin: 0 0 10px; }
  .lp-section-sub { color: var(--slate); font-size: 14.5px; line-height: 1.5; }

  /* ---------- Features ---------- */
  .lp-features { background: var(--cloud); }
  .lp-feature-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
  @media (max-width: 980px) { .lp-feature-grid { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 560px) { .lp-feature-grid { grid-template-columns: 1fr; } }
  .lp-feature-card {
    background: #fff; border: 1px solid var(--line); border-radius: 16px; padding: 22px 18px;
    transition: transform .2s ease, box-shadow .2s ease;
  }
  .lp-feature-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lift); }
  .lp-feature-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; margin-bottom: 12px; transition: transform .2s ease; }
  .lp-feature-card:hover .lp-feature-icon { transform: scale(1.08) rotate(-4deg); }
  .lp-feature-card h3 { font-size: 14.5px; color: var(--ink); margin: 0 0 6px; font-family: var(--font-poppins); font-weight: 700; }
  .lp-feature-card p { font-size: 13px; color: var(--slate); line-height: 1.5; margin: 0; }

  /* ---------- How it works ---------- */
  .lp-steps { position: relative; display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; }
  @media (max-width: 860px) { .lp-steps { grid-template-columns: 1fr; } }
  .lp-steps::before {
    content: ""; position: absolute; top: 26px; left: 12%; right: 12%; height: 2px;
    background: repeating-linear-gradient(90deg, var(--mint-line) 0 10px, transparent 10px 18px);
  }
  @media (max-width: 860px) { .lp-steps::before { display: none; } }
  .lp-step { position: relative; text-align: center; padding: 0 10px; }
  .lp-step-num {
    width: 48px; height: 48px; border-radius: 50%; background: var(--ink); color: #fff;
    display: flex; align-items: center; justify-content: center; font-family: var(--font-poppins); font-weight: 600; font-size: 17px;
    margin: 0 auto 16px; position: relative; z-index: 2; box-shadow: 0 8px 20px -8px rgba(18,40,60,.4);
    transition: transform .2s ease;
  }
  .lp-step:hover .lp-step-num { transform: scale(1.08); background: var(--teal); }
  .lp-step h3 { font-size: 15px; color: var(--ink); margin: 0 0 6px; }
  .lp-step p { font-size: 13px; color: var(--slate); line-height: 1.5; max-width: 250px; margin: 0 auto; }

  /* ---------- Portals (Exact Layout Matching Provided Image) ---------- */
  .lp-portals { background: radial-gradient(circle at 15% 8%, #123A6B 0%, var(--ink, #0b192c) 48%, #04101F 100%); position: relative; overflow: hidden; }
  .lp-portals::before { content: ""; position: absolute; width: 360px; height: 360px; border-radius: 50%; border: 1px dashed rgba(56,189,248,.16); top: -120px; right: -100px; animation: floatSlow 10s ease-in-out infinite; }
  .lp-portals .lp-section-title, .lp-portals .lp-section-tag { color: #fff; }
  .lp-portals .lp-section-tag { color: #38BDF8; }
  .lp-portals .lp-section-sub { color: #93A9BC; }
  
  .lp-role-grid { 
    display: flex; 
    justify-content: center; 
    align-items: stretch;
    gap: 24px; 
    position: relative; 
    z-index: 2; 
    width: 100%; 
    flex-wrap: wrap;
  }
  
  /* Fixed Narrow Width Portal Cards */
  .lp-role-card {
    background: #fff; 
    border-radius: 18px; 
    padding: 32px 24px 28px; 
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15); 
    transition: transform .25s ease, box-shadow .25s ease;
    display: flex; 
    flex-direction: column; 
    align-items: center; 
    width: 100%;
    max-width: 320px; /* Precise Narrow Width */
    box-sizing: border-box;
  }
  .lp-role-card:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,0.25); }
  
  .lp-role-icon { 
    width: 52px; 
    height: 52px; 
    border-radius: 14px; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    font-size: 22px; 
    margin: 0 auto 20px; 
    transition: transform .2s ease; 
  }
  .lp-role-card:hover .lp-role-icon { transform: scale(1.06); }
  
  .lp-role-card h3 { margin: 0 0 12px; font-size: 20px; color: #0b192c; font-weight: 700; }
  .lp-role-card p { color: #64748b; font-size: 13.5px; margin: 0 0 24px; line-height: 1.5; flex-grow: 1; }
  
  /* Blue Buttons with Right Arrow */
  .lp-role-card .btn-portal { 
    width: 100%; 
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 16px; 
    font-size: 14px; 
    font-weight: 600;
    border-radius: 10px;
    background: #0099e6;
    color: #fff;
    text-decoration: none;
    transition: background .2s ease;
  }
  .lp-role-card .btn-portal:hover { background: #0088cc; }
  .lp-role-card .btn-admin { background: #0b192c; }
  .lp-role-card .btn-admin:hover { background: #152844; }

  .lp-role-sub { display: block; margin-top: 14px; font-size: 12px; color: #64748b; }
  .lp-role-sub a { color: #0099e6; font-weight: 600; text-decoration: none; }
  .lp-role-sub a:hover { text-decoration: underline; }

  /* ---------- Footer ---------- */
  .lp-footer { background: radial-gradient(circle at 15% 8%, #123A6B 0%, var(--ink) 48%, #04101F 100%); padding: 40px 28px 22px; }
  .lp-footer-inner { max-width: 1160px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; }
  .lp-footer-brand { display: flex; align-items: center; gap: 10px; color: #fff; font-family: var(--font-poppins); font-weight: 600; font-size: 15.5px; }
  .lp-footer-links { display: flex; gap: 20px; }
  .lp-footer-links a { font-size: 12.5px; color: #7C93A8; font-weight: 500; transition: color .15s ease; }
  .lp-footer-links a:hover { color: #fff; }
  .lp-footer-copy { font-size: 12px; color: #4B5F71; margin-top: 20px; text-align: center; }
</style>
</head>
<body>

<nav class="lp-nav" id="lpNav">
  <div class="lp-nav-inner">
    <a href="#top" class="lp-nav-brand">
      <div class="logo">
        <img src="./assets/media/logo.png" alt="" style="width: 30px; filter: invert(1);">
      </div>
      Vaccination Management System
    </a>
    <div class="lp-nav-links">
      <a href="#features">Features</a>
      <a href="#how-it-works">How It Works</a>
      <a href="#portals">Portals</a>
    </div>
    <div class="lp-nav-right">
      <a href="#portals" class="btn btn-primary btn-sm lp-nav-cta">Get Started</a>
    </div>
  </div>
</nav>

<header class="lp-hero" id="top">
  <div class="lp-hero-glow"></div>
  <div class="lp-hero-inner">
    <div class="lp-hero-copy reveal">
      <span class="lp-eyebrow"><i class="fa-solid fa-shield-heart"></i> Vaccination Management, Simplified</span>
      <h1>Every dose. Every child.<br><span class="accent">One trusted record.</span></h1>
      <p class="lp-hero-sub">Vaccination System connects parents, hospitals, and administrators on a single platform to schedule, approve, and track childhood vaccinations — safely, transparently, and in real time.</p>
      <div class="lp-hero-cta">
        <a href="#portals" class="btn btn-primary btn-lg">Get Started <i class="fa-solid fa-arrow-right"></i></a>
        <a href="#how-it-works" class="btn btn-outline btn-lg">See How It Works</a>
      </div>
      <div class="lp-hero-trust">
        <div class="lp-trust-item"><i class="fa-solid fa-lock"></i> Secure record-keeping</div>
        <div class="lp-trust-item"><i class="fa-solid fa-hospital"></i> Admin-verified hospitals</div>
        <div class="lp-trust-item"><i class="fa-solid fa-bolt"></i> Real-time status updates</div>
      </div>
    </div>

    <div class="lp-hero-visual reveal reveal-delay-2">
      <div class="lp-float-badge lp-badge-1"><i class="fa-solid fa-syringe"></i> Appointment booked</div>
      <div class="lp-mock-card">
        <div class="lp-mock-head">
          <div class="lp-mock-avatar">AM</div>
          <div>
            <div class="lp-mock-name">Ayan Memon</div>
            <div class="lp-mock-sub">Vaccination Record</div>
          </div>
        </div>
        <div class="lp-mock-row"><span>BCG</span><span class="status-badge badge-approved">Vaccinated</span></div>
        <div class="lp-mock-row"><span>Hepatitis B</span><span class="status-badge badge-pending">Pending</span></div>
        <div class="lp-mock-row"><span>Polio (OPV)</span><span class="status-badge badge-approved">Vaccinated</span></div>
      </div>
      <div class="lp-float-badge lp-badge-2"><i class="fa-solid fa-circle-check"></i> Hospital approved</div>
    </div>
  </div>
</header>

<section class="lp-section lp-features" id="features">
  <div class="lp-section-inner">
    <div class="lp-section-head reveal">
      <span class="lp-section-tag">Why ImmuTrack</span>
      <h2 class="lp-section-title">Built for clarity, speed, and trust</h2>
      <p class="lp-section-sub">Everything a family, a hospital, and a health system need to stay on top of childhood immunizations — in one place.</p>
    </div>

    <div class="lp-feature-grid">
      <div class="lp-feature-card reveal reveal-delay-1">
        <div class="lp-feature-icon" style="background:var(--mint);color:var(--teal-dark);"><i class="fas fa-file-medical"></i></div>
        <h3>Centralized Records</h3>
        <p>Every child's vaccination history lives in one secure place — no more paper cards or scattered spreadsheets.</p>
      </div>
      <div class="lp-feature-card reveal reveal-delay-2">
        <div class="lp-feature-icon" style="background:var(--blue-bg);color:var(--blue);"><i class="fa-solid fa-bolt"></i></div>
        <h3>Real-Time Approvals</h3>
        <p>Booking requests move instantly from parent to hospital, with live status updates at every step.</p>
      </div>
      <div class="lp-feature-card reveal reveal-delay-3">
        <div class="lp-feature-icon" style="background:var(--amber-bg);color:#8A5A00;"><i class="fa-solid fa-user-shield"></i></div>
        <h3>Role-Based Access</h3>
        <p>Parents, hospitals, and admins each see exactly what they need — nothing more, nothing less.</p>
      </div>
      <div class="lp-feature-card reveal reveal-delay-4">
        <div class="lp-feature-icon" style="background:var(--coral-bg);color:#9E2C2B;"><i class="fa-solid fa-chart-line"></i></div>
        <h3>System-Wide Reports</h3>
        <p>Administrators get child-, hospital-, and vaccine-wise insights to track coverage at a glance.</p>
      </div>
    </div>
  </div>
</section>

<section class="lp-section" id="how-it-works">
  <div class="lp-section-inner">
    <div class="lp-section-head reveal">
      <span class="lp-section-tag">How It Works</span>
      <h2 class="lp-section-title">From booking to vaccinated, in three steps</h2>
      <p class="lp-section-sub">A simple, transparent flow that keeps every family and every hospital in sync.</p>
    </div>

    <div class="lp-steps">
      <div class="lp-step reveal reveal-delay-1">
        <div class="lp-step-num">1</div>
        <h3>Register &amp; Add a Child</h3>
        <p>Parents create an account and add a profile for each child they want to track.</p>
      </div>
      <div class="lp-step reveal reveal-delay-2">
        <div class="lp-step-num">2</div>
        <h3>Book an Appointment</h3>
        <p>Choose a vaccine and an approved hospital, then submit a request for a preferred date.</p>
      </div>
      <div class="lp-step reveal reveal-delay-3">
        <div class="lp-step-num">3</div>
        <h3>Get Vaccinated &amp; Track</h3>
        <p>The hospital approves the request and logs the completed dose — visible instantly to the parent.</p>
      </div>
    </div>
  </div>
</section>

<section class="lp-section lp-portals" id="portals">
  <div class="lp-section-inner">
    <div class="lp-section-head reveal">
      <span class="lp-section-tag">Get Started</span>
      <h2 class="lp-section-title">Choose your portal</h2>
      <p class="lp-section-sub">Sign in as a parent, hospital, or administrator to access your dashboard.</p>
    </div>

    <div class="lp-role-grid">
      <div class="lp-role-card reveal reveal-delay-1">
        <div class="lp-role-icon" style="background:#e6f4ff;color:#0099e6;"><i class="fa-solid fa-user-group"></i></div>
        <h3>Parent Portal</h3>
        <p>Register your children, schedule vaccination slots, and access official immunization cards.</p>
        <a href="parent/login.php" class="btn-portal">Sign In <i class="fa-solid fa-arrow-right"></i></a>
        <span class="lp-role-sub">New here? <a href="parent/register.php">Create account</a></span>
      </div>

      <div class="lp-role-card reveal reveal-delay-2">
        <div class="lp-role-icon" style="background:#e6f4ff;color:#0099e6;"><i class="fa-solid fa-hospital"></i></div>
        <h3>Hospital Portal</h3>
        <p>Manage incoming requests, approve appointments, and update child vaccination logs.</p>
        <a href="hospital/login.php" class="btn-portal">Sign In <i class="fa-solid fa-arrow-right"></i></a>
        <span class="lp-role-sub">Accounts managed by Admin</span>
      </div>

      <div class="lp-role-card reveal reveal-delay-3">
        <div class="lp-role-icon" style="background:#e2e8f0;color:#0b192c;"><i class="fa-solid fa-user-shield"></i></div>
        <h3>Admin Portal</h3>
        <p>Control system users, oversee hospital verification, manage vaccine stocks, and view analytics.</p>
        <a href="admin/login.php" class="btn-portal btn-admin">Sign In <i class="fa-solid fa-arrow-right"></i></a>
        <span class="lp-role-sub">System Management</span>
      </div>
    </div>
  </div>
</section>

<footer class="lp-footer">
  <div class="lp-footer-inner">
    <div class="lp-footer-brand">
       <div class="logo">
        <img src="./assets/media/logo.png" alt="" style="width: 30px; filter: invert(1);">
      </div>
      Vaccination Management System
    </div>
    <div class="lp-footer-links">
      <a href="#features">Features</a>
      <a href="#how-it-works">How It Works</a>
      <a href="#portals">Portals</a>
    </div>
  </div>
  <div class="lp-footer-copy">&copy; <?= date('Y') ?> &middot; Vaccination Management System</div>
</footer>

<script>
  // Navbar background on scroll
  const nav = document.getElementById('lpNav');
  window.addEventListener('scroll', function () {
    if (window.scrollY > 40) nav.classList.add('scrolled');
    else nav.classList.remove('scrolled');
  });

  // Reveal-on-scroll
  const revealEls = document.querySelectorAll('.reveal');
  const io = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('in-view');
        io.unobserve(entry.target);
      }
    });
  }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
  revealEls.forEach(function (el) { io.observe(el); });
</script>

</body>
</html>