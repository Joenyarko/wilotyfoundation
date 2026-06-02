// components.js

class AppNavbar extends HTMLElement {
  connectedCallback() {
    const isSolid = this.hasAttribute('solid') || this.getAttribute('class') === 'nav-solid';
    const activePage = this.getAttribute('active-page') || '';
    
    this.innerHTML = `
      <nav class="${isSolid ? 'nav-solid' : ''}">
        <div class="nav-left">
          <a href="index.php" class="nav-logo">
            <img src="assets/WhatsApp_Image_2026-03-06_at_8.22.29_AM-removebg-preview.png" alt="Wiloty Foundation Logo" />
          </a>
        </div>
        <div class="nav-center">
          <ul class="nav-links">
            <li><a href="index.php" class="${activePage === 'home' ? 'active' : ''}">Home</a></li>
            <li><a href="about.php" class="${activePage === 'about' ? 'active' : ''}">About</a></li>
            <li><a href="blog.php" class="${activePage === 'blog' ? 'active' : ''}">Blog</a></li>
            <li><a href="event.php" class="${activePage === 'event' ? 'active' : ''}">Event</a></li>
          </ul>
        </div>
        <div class="nav-right">
          <a href="#" class="btn-donate-nav">Donate</a>
          <button class="nav-burger" aria-label="Toggle Menu">
            <span></span>
            <span></span>
            <span></span>
          </button>
        </div>
      </nav>
      <div class="nav-mobile-overlay">
        <ul class="nav-mobile-links">
          <li><a href="index.php" class="${activePage === 'home' ? 'active' : ''}">Home</a></li>
          <li><a href="about.php" class="${activePage === 'about' ? 'active' : ''}">About</a></li>
          <li><a href="blog.php" class="${activePage === 'blog' ? 'active' : ''}">Blog</a></li>
          <li><a href="event.php" class="${activePage === 'event' ? 'active' : ''}">Event</a></li>
          <li><a href="#" class="btn-donate-mobile">Donate</a></li>
        </ul>
      </div>
    `;

    const burger = this.querySelector('.nav-burger');
    const overlay = this.querySelector('.nav-mobile-overlay');

    if (burger && overlay) {
      burger.addEventListener('click', () => {
        burger.classList.toggle('active');
        overlay.classList.toggle('active');
        document.body.classList.toggle('nav-open');
      });

      const links = this.querySelectorAll('.nav-mobile-links a');
      links.forEach(link => {
        link.addEventListener('click', () => {
          burger.classList.remove('active');
          overlay.classList.remove('active');
          document.body.classList.remove('nav-open');
        });
      });
    }
  }
}
customElements.define('app-navbar', AppNavbar);

class AppFooter extends HTMLElement {
  connectedCallback() {
    this.innerHTML = `
      <div class="newsletter-wrapper" style="background: transparent; padding: 40px 20px;">
        <div class="newsletter-banner" style="max-width: 1102px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
          <div style="flex: 1; min-width: 280px;">
            <h3 style="color: #333; margin: 0 0 10px 0; font-size: 24px;">Subscribe to our Newsletter</h3>
            <p style="color: #666; margin: 0;">Get the latest updates, event news, and inspiring stories directly to your inbox.</p>
          </div>
          <div style="flex: 1; min-width: 280px; max-width: 450px;">
            <form id="newsletterForm" onsubmit="submitNewsletterForm(event)" style="display: flex; gap: 10px; flex-wrap: wrap;">
              <input type="email" id="nl_email" required placeholder="Enter your email address" style="flex: 1; min-width: 200px; padding: 12px 20px; border: 1px solid #ccc; border-radius: 8px; font-size: 16px; outline: none; background: #fff; color: #333;">
              <button type="submit" style="padding: 12px 24px; background: var(--orange, #ff6b00); color: #fff; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; transition: background 0.3s;">SUBSCRIBE</button>
            </form>
            <div id="nlMessage" style="display: none; width: 100%; color: #333; margin-top: 10px; font-weight: 600; font-size: 14px;"></div>
          </div>
        </div>
      </div>

      <footer>

        <div class="footer-top">
          <div class="footer-brand">
            <!-- REMOVED THE INLINE MARGIN-BOTTOM STYLE HERE -->
            <div class="nav-logo">
              <img src="assets/WhatsApp_Image_2026-03-06_at_8.22.29_AM-removebg-preview.png" alt="Wiloty Foundation Logo"  />
            </div>
            <p>Wiloty Foundation is a youth-focused organization dedicated to bringing hope, opportunity, and transformation through education, youth empowerment, community development, and health and wellbeing.</p>
            <div class="footer-socials">
              <a href="#" title="Facebook"><svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg></a>
              <a href="#" title="TikTok"><svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M9 0h1.98c.144.715.54 1.617 1.235 2.512C12.895 3.389 13.797 4 15 4v2c-1.753 0-3.07-.814-4-1.829V11a5 5 0 1 1-5-5v2a3 3 0 1 0 3 3z"/></svg></a>
              <a href="#" title="WhatsApp"><svg width="20" height="20" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16"><path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.008-3.468c0-3.641 2.964-6.604 6.608-6.604a6.59 6.59 0 0 1 4.67 1.884 6.59 6.59 0 0 1 1.919 4.662c0 3.644-2.964 6.608-6.608 6.608z"/></svg></a>
            </div>
          </div>

          <div class="footer-col">
            <h4>Resources</h4>
            <ul>
              <li><a href="index.php">Home</a></li>
              <li><a href="about.php">About</a></li>
              <li><a href="blog.php">Blog</a></li>
              <li><a href="event.php">Event</a></li>
              <li><a href="privacy.php">Privacy Policy</a></li>
            </ul>
          </div>

          <div class="footer-col">
            <h4>Events</h4>
            <ul>
              <li><a href="#">Donation</a></li>
              <li><a href="#">Summit</a></li>
              <li><a href="#">Volunteer</a></li>
            </ul>
          </div>

          <div class="footer-col">
            <h4>Contact</h4>
            <ul>
              <li><a href="mailto:wilotyfoundation@gmail.com" style="text-transform: lowercase;">wilotyfoundation@gmail.com</a></li>
            </ul>
          </div>
        </div>

        <div class="footer-bottom">
          wilotyfoundation.org &nbsp;•&nbsp; &copy; 2026 Wiloty Foundation. All rights reserved.
        </div>
      </footer>

      <!-- Floating Scroll to Top CTA -->
      <button id="scrollToTopBtn" aria-label="Scroll to top" style="
          position: fixed;
          bottom: 30px;
          right: 30px;
          background: var(--orange, #ff6b00);
          color: #fff;
          border: none;
          border-radius: 50%;
          width: 50px;
          height: 50px;
          display: flex;
          align-items: center;
          justify-content: center;
          cursor: pointer;
          box-shadow: 0 4px 15px rgba(255, 107, 0, 0.4);
          opacity: 0;
          visibility: hidden;
          transform: translateY(20px);
          transition: all 0.3s ease;
          z-index: 9999;
      ">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
              <line x1="12" y1="19" x2="12" y2="5"></line>
              <polyline points="5 12 12 5 19 12"></polyline>
          </svg>
      </button>
    `;

    // Scroll to top logic
    const scrollBtn = this.querySelector('#scrollToTopBtn');
    if (scrollBtn) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                scrollBtn.style.opacity = '1';
                scrollBtn.style.visibility = 'visible';
                scrollBtn.style.transform = 'translateY(0)';
            } else {
                scrollBtn.style.opacity = '0';
                scrollBtn.style.visibility = 'hidden';
                scrollBtn.style.transform = 'translateY(20px)';
            }
        });
        scrollBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
  }
}
customElements.define('app-footer', AppFooter);

// Global Newsletter Submit Handler
window.submitNewsletterForm = function(e) {
  e.preventDefault();
  const form = e.target;
  const email = form.querySelector('#nl_email').value;
  const msgContainer = document.getElementById('nlMessage');
  const btn = form.querySelector('button[type="submit"]');

  btn.innerText = 'WAIT...';
  btn.disabled = true;

  fetch('api/subscribe.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email: email })
  })
  .then(res => res.json())
  .then(data => {
    msgContainer.style.display = 'block';
    if (data.success) {
      msgContainer.style.color = 'var(--dark-green, #1B3D2F)';
      msgContainer.innerHTML = '&#10004; ' + data.message;
      form.reset();
    } else {
      msgContainer.style.color = 'red';
      msgContainer.innerHTML = '&#10008; ' + data.message;
    }
  })
  .catch(err => {
    msgContainer.style.display = 'block';
    msgContainer.style.color = 'red';
    msgContainer.innerHTML = '&#10008; An error occurred. Please try again.';
  })
  .finally(() => {
    btn.innerText = 'SUBSCRIBE';
    btn.disabled = false;
    setTimeout(() => { msgContainer.style.display = 'none'; }, 5000);
  });
};

// Scroll Animation Observer for sleek slide-in effects
document.addEventListener('DOMContentLoaded', () => {
  const scrollObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        // Once animated, stop observing so it doesn't bounce in repeatedly
        scrollObserver.unobserve(entry.target); 
      }
    });
  }, {
    threshold: 0.15,
    rootMargin: "0px 0px -50px 0px"
  });

  const animatedElements = document.querySelectorAll('.animate-on-scroll');
  animatedElements.forEach(el => scrollObserver.observe(el));
});

// Cookie Consent Banner Logic
document.addEventListener('DOMContentLoaded', () => {
  if (!localStorage.getItem('wiloty_cookie_consent')) {
    const banner = document.createElement('div');
    banner.id = 'cookieConsentBanner';
    banner.innerHTML = `
      <div style="position: fixed; bottom: 0; left: 0; right: 0; background: #111; color: #fff; padding: 20px; z-index: 10000; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; font-family: 'Poppins', sans-serif; box-shadow: 0 -5px 15px rgba(0,0,0,0.5); transform: translateY(100%); transition: transform 0.6s cubic-bezier(0.22, 1, 0.36, 1);">
        <div style="max-width: 1100px; display: flex; flex-direction: row; align-items: center; justify-content: space-between; gap: 30px; width: 100%; margin: 0 auto;">
          <p style="margin: 0; font-size: 14px; line-height: 1.6; text-align: left; color: #ddd;">
            We use cookies to enhance your browsing experience, serve personalized features, and analyze site traffic. By clicking "Accept", you consent to our use of cookies. Read our <a href="privacy.php" style="color: var(--orange, #ff6b00); text-decoration: underline; font-weight: 600;">Privacy Policy</a>.
          </p>
          <div style="display: flex; gap: 10px; flex-shrink: 0;">
            <button id="acceptCookiesBtn" style="background: var(--orange, #ff6b00); color: #fff; border: none; padding: 12px 30px; border-radius: 8px; font-weight: 700; font-family: 'Poppins', sans-serif; cursor: pointer; transition: background 0.3s; white-space: nowrap; font-size: 14px;">Accept</button>
          </div>
        </div>
      </div>
    `;

    document.body.appendChild(banner);

    // Responsive styling for the banner
    const style = document.createElement('style');
    style.innerHTML = `
      @media (max-width: 768px) {
        #cookieConsentBanner > div > div {
          flex-direction: column !important;
          text-align: center !important;
        }
        #cookieConsentBanner p {
          text-align: center !important;
        }
        #acceptCookiesBtn {
          width: 100%;
        }
      }
    `;
    document.head.appendChild(style);

    // Slide up animation trigger
    setTimeout(() => {
      banner.firstElementChild.style.transform = 'translateY(0)';
    }, 1000); // 1 second delay so the page loads first

    // Accept action
    document.getElementById('acceptCookiesBtn').addEventListener('click', () => {
      localStorage.setItem('wiloty_cookie_consent', 'true');
      banner.firstElementChild.style.transform = 'translateY(100%)';
      setTimeout(() => banner.remove(), 600);
    });
  }
});

// ── POOR MAN'S CRON: SILENT BACKGROUND EMAIL QUEUE PROCESSOR ──
// This runs a few seconds after the page loads, completely invisibly, 
// to check if there are any pending emails in the database and send them out.
document.addEventListener('DOMContentLoaded', () => {
  setTimeout(() => {
    fetch('api/process_email_queue.php')
      .then(res => res.json())
      .catch(err => {
         // Silently ignore queue processor errors in frontend to prevent disruption
      });
  }, 3000); // Wait 3 seconds to avoid blocking initial page load rendering
});
