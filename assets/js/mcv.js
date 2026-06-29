/* ============================================================
   MCV Network — shared nav + footer renderer
   Pages set <body data-nav="advertisers"> to mark the active item.
   Injected as template strings so it also works over file://.
   ============================================================ */
(function () {
  var active = document.body.getAttribute('data-nav') || '';
  var adsBaseUrl = 'https://ads.mcv.network';

  var navItems = [
    { key: 'advertisers', label: 'Advertisers', href: '/advertisers/' },
    { key: 'publishers', label: 'Publishers', href: '/publishers/' },
    { key: 'commerce', label: 'Commerce', href: '/commerce/overview/' },
    { key: 'technology', label: 'Technology', href: '/technology/ai-engine/' },
    { key: 'solutions', label: 'Solutions', href: '/solutions/ecommerce/' },
    { key: 'resources', label: 'Resources', href: '/resources/blog/' },
    { key: 'company', label: 'Company', href: '/company/about/' }
  ];

  var links = navItems.map(function (i) {
    return '<a href="' + i.href + '"' + (i.key === active ? ' class="active"' : '') + '>' + i.label + '</a>';
  }).join('');

  var navHTML =
    '<nav class="mcv-nav">' +
      '<div class="nav-inner">' +
        '<a href="/" class="nav-logo">' +
          '<img src="/logo_MCV_network.png" alt="MCV Network">' +
        '</a>' +
        '<button class="nav-toggle" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>' +
        '<div class="nav-links" id="navLinks">' + links +
          '<a href="' + adsBaseUrl + '/login" class="nav-login mobile-only">Log in</a>' +
        '</div>' +
        '<div class="nav-cta">' +
          '<a href="' + adsBaseUrl + '/login" class="nav-login">Log in</a>' +
          '<a href="' + adsBaseUrl + '/signup" class="btn-nav">Get Started <i class="fa-solid fa-arrow-right"></i></a>' +
        '</div>' +
      '</div>' +
    '</nav>';

  var footerHTML =
    '<footer class="mcv-footer">' +
      '<div class="footer-inner">' +
        '<div class="footer-grid">' +
          '<div class="footer-brand">' +
            '<h3>MCV<span>.</span>Network</h3>' +
            '<p>Performance advertising at scale. Reach. Convert. Grow. Beyond walled gardens.</p>' +
          '</div>' +
          '<div class="footer-col">' +
            '<h4>Advertisers</h4>' +
            '<a href="/advertisers/platform/">Platform</a>' +
            '<a href="/advertisers/formats/">Ad Formats</a>' +
            '<a href="/advertisers/targeting/">Targeting</a>' +
            '<a href="/advertisers/pricing/">Pricing</a>' +
            '<a href="/advertisers/case-studies/">Case Studies</a>' +
          '</div>' +
          '<div class="footer-col">' +
            '<h4>Solutions</h4>' +
            '<a href="/solutions/ecommerce/">E-commerce</a>' +
            '<a href="/solutions/finance/">Finance</a>' +
            '<a href="/solutions/healthcare/">Healthcare</a>' +
            '<a href="/solutions/apps/">App Developers</a>' +
            '<a href="/solutions/enterprise/">Enterprise</a>' +
          '</div>' +
          '<div class="footer-col">' +
            '<h4>Resources</h4>' +
            '<a href="/resources/blog/">Blog</a>' +
            '<a href="https://docs.mcv.network">Documentation</a>' +
            '<a href="/resources/api-docs/">API Reference</a>' +
            '<a href="/resources/webinars/">Webinars</a>' +
            '<a href="/resources/glossary/">Glossary</a>' +
          '</div>' +
          '<div class="footer-col">' +
            '<h4>Company</h4>' +
            '<a href="/company/about/">About Us</a>' +
            '<a href="/company/careers/">Careers</a>' +
            '<a href="/company/press/">Press</a>' +
            '<a href="/company/partners/">Partners</a>' +
            '<a href="/company/contact/">Contact</a>' +
          '</div>' +
        '</div>' +
        '<div class="footer-bottom">' +
          '<div>&copy; <span id="mcvYear"></span> MCV Network. All rights reserved. ' +
            '<a href="/legal/privacy-policy/">Privacy</a> &middot; ' +
            '<a href="/legal/terms/">Terms</a> &middot; ' +
            '<a href="/legal/cookie-policy/">Cookies</a></div>' +
          '<div class="footer-social">' +
            '<a href="#" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>' +
            '<a href="#" aria-label="X"><i class="fa-brands fa-x-twitter"></i></a>' +
            '<a href="#" aria-label="GitHub"><i class="fa-brands fa-github"></i></a>' +
          '</div>' +
        '</div>' +
      '</div>' +
    '</footer>';

  var navMount = document.getElementById('mcv-nav');
  var footerMount = document.getElementById('mcv-footer');
  if (navMount) navMount.outerHTML = navHTML;
  if (footerMount) footerMount.outerHTML = footerHTML;

  var yearEl = document.getElementById('mcvYear');
  if (yearEl) yearEl.textContent = new Date().getFullYear();

  var toggle = document.querySelector('.nav-toggle');
  var linksEl = document.getElementById('navLinks');
  if (toggle && linksEl) {
    toggle.addEventListener('click', function () { linksEl.classList.toggle('open'); });
  }
})();
