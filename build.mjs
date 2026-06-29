/* ============================================================
   MCV Network — static site generator
   Run:  node build.mjs
   Emits index.html files mirroring the sitemap URL structure.
   Shared look comes from /assets/css/mcv.css + /assets/js/mcv.js
   ============================================================ */
import { mkdirSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = dirname(fileURLToPath(import.meta.url));

/* ---------- low-level helpers ---------- */
const esc = (s = '') => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

function layout(page) {
  const desc = page.description || 'MCV Network — Performance Advertising at Scale. Reach. Convert. Grow.';
  return `<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>${esc(page.title)} — MCV Network</title>
<meta name="description" content="${esc(desc)}">
<link rel="icon" type="image/png" href="/logo_MCV_network.png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="/assets/css/mcv.css">
${page.head || ''}</head>
<body data-nav="${page.navKey || ''}">
<div id="mcv-nav"></div>
${page.body}
<div id="mcv-footer"></div>
<script src="/assets/js/mcv.js"></script>
${page.scripts || ''}</body>
</html>
`;
}

/* ---------- block renderers (return HTML strings) ---------- */
function breadcrumb(trail) {
  if (!trail || !trail.length) return '';
  const parts = trail.map((t, i) =>
    i === trail.length - 1
      ? `<span class="muted">${esc(t.label)}</span>`
      : `<a href="${t.href}">${esc(t.label)}</a>`
  ).join('<span>/</span>');
  return `<div class="breadcrumb">${parts}</div>`;
}

function hero(h) {
  const eyebrow = h.eyebrow ? `<span class="eyebrow">${esc(h.eyebrow)}</span>` : '';
  const lead = h.lead ? `<p class="lead">${h.lead}</p>` : '';
  const ctas = (h.ctas || []).map(c =>
    `<a href="${c.href}" class="btn ${c.btn || 'btn-primary'}">${c.icon ? `<i class="${c.icon}"></i> ` : ''}${esc(c.label)}</a>`
  ).join('');
  const ctaWrap = ctas ? `<div class="hero-ctas">${ctas}</div>` : '';
  const crumb = h.breadcrumb ? breadcrumb(h.breadcrumb) : '';
  const cls = ['hero', h.variant === 'dark' ? 'dark' : '', h.split ? '' : 'center'].filter(Boolean).join(' ');

  if (h.split) {
    return `<section class="${cls}">
  <div class="hero-inner hero-split">
    <div>${crumb}${eyebrow}<h1>${h.title}</h1>${lead}${ctaWrap}</div>
    <div class="hero-visual">${h.split}</div>
  </div>
</section>`;
  }
  return `<section class="${cls}">
  <div class="hero-inner">${crumb}${eyebrow}<h1>${h.title}</h1>${lead}${ctaWrap}</div>
</section>`;
}

function sectionHeader(s) {
  if (!s.title && !s.tag && !s.text) return '';
  const tag = s.tag ? `<div class="section-tag">${esc(s.tag)}</div>` : '';
  const title = s.title ? `<h2>${esc(s.title)}</h2>` : '';
  const text = s.text ? `<p>${s.text}</p>` : '';
  return `<div class="section-header${s.align === 'left' ? ' left' : ''}">${tag}${title}${text}</div>`;
}

function cards(items, cols = 3) {
  const inner = items.map(it => {
    const num = it.num ? `<div class="card-num">${esc(it.num)}</div>` : '';
    const icon = it.icon ? `<div class="card-icon"${it.iconBg ? ` style="background:${it.iconBg}"` : ''}><i class="${it.icon}"></i></div>` : '';
    return `<div class="card">${num}${icon}<h3>${esc(it.title)}</h3><p>${it.text}</p></div>`;
  }).join('');
  return `<div class="grid grid-${cols}">${inner}</div>`;
}

function tiles(items) {
  const inner = items.map(it =>
    `<div class="tile"><div class="tile-icon">${it.emoji || `<i class="${it.icon}"></i>`}</div><h4>${esc(it.title)}</h4><p>${it.text}</p></div>`
  ).join('');
  return `<div class="grid grid-auto">${inner}</div>`;
}

function metrics(items) {
  const inner = items.map(it =>
    `<div class="metric"><div class="metric-value">${esc(it.value)}</div><div class="metric-label">${esc(it.label)}</div></div>`
  ).join('');
  return `<div class="grid grid-auto">${inner}</div>`;
}

function featureRows(items, cols = 2) {
  const inner = items.map(it =>
    `<div class="feature-row"><div class="fr-icon"><i class="${it.icon}"></i></div><div><h4>${esc(it.title)}</h4><p>${it.text}</p></div></div>`
  ).join('');
  return `<div class="grid grid-${cols}">${inner}</div>`;
}

function table(t) {
  const head = `<tr>${t.head.map(h => `<th>${esc(h)}</th>`).join('')}</tr>`;
  const rows = t.rows.map(r => `<tr>${r.map(c => `<td>${c}</td>`).join('')}</tr>`).join('');
  return `<div class="table-wrap"><table class="mcv"><thead>${head}</thead><tbody>${rows}</tbody></table></div>`;
}

function pricing(plans) {
  const inner = plans.map(p => {
    const feats = `<ul class="checklist">${p.features.map(f => `<li>${esc(f)}</li>`).join('')}</ul>`;
    const btn = `<a href="${p.href || '/signup/'}" class="btn ${p.featured ? 'btn-teal' : 'btn-outline'}">${esc(p.cta || 'Get Started')}</a>`;
    return `<div class="price-card${p.featured ? ' featured' : ''}">
      <div class="plan-name">${esc(p.name)}</div>
      <div class="plan-price">${esc(p.price)}${p.unit ? ` <small>${esc(p.unit)}</small>` : ''}</div>
      <div class="plan-desc">${esc(p.desc)}</div>${feats}${btn}</div>`;
  }).join('');
  return `<div class="pricing-grid">${inner}</div>`;
}

function checklistSplit(s) {
  const list = `<ul class="checklist">${s.items.map(i => `<li>${i}</li>`).join('')}</ul>`;
  const right = s.panel || '';
  return `<div class="grid grid-2" style="align-items:center;gap:48px">
    <div>${s.heading ? `<h2 style="font-family:var(--font-heading);font-size:28px;font-weight:800;margin-bottom:16px;color:var(--text-primary)">${esc(s.heading)}</h2>` : ''}${s.intro ? `<p class="muted" style="margin-bottom:20px">${s.intro}</p>` : ''}${list}</div>
    <div>${right}</div>
  </div>`;
}

function logos(items) {
  return `<div class="logo-strip">${items.map(i => `<span>${esc(i)}</span>`).join('')}</div>`;
}

function prose(html) { return `<div class="prose">${html}</div>`; }

const renderers = { cards, tiles, metrics, featureRows, table, pricing, checklistSplit, logos, prose };

function section(s) {
  let bodyHTML = s.html || '';
  if (s.type && renderers[s.type]) bodyHTML = renderers[s.type](s.items || s.data, s.cols);
  const cls = ['section', s.bg === 'alt' ? 'alt' : '', s.bg === 'dark' ? 'dark' : ''].filter(Boolean).join(' ');
  return `<section class="${cls}">
  <div class="section-inner">${sectionHeader(s)}${bodyHTML}</div>
</section>`;
}

function statsBar(items) {
  const inner = items.map(i => `<div class="stat-item"><div class="stat-value">${esc(i.value)}</div><div class="stat-label">${esc(i.label)}</div></div>`).join('');
  return `<div class="stats-bar"><div class="stats-inner">${inner}</div></div>`;
}

function ctaBand(c) {
  const btns = (c.ctas || [{ label: 'Get Started', href: '/signup/', btn: 'btn-teal', icon: 'fa-solid fa-rocket' }])
    .map(b => `<a href="${b.href}" class="btn btn-lg ${b.btn || 'btn-teal'}">${b.icon ? `<i class="${b.icon}"></i> ` : ''}${esc(b.label)}</a>`).join(' ');
  return `<section class="cta-band"><h2>${esc(c.title)}</h2><p>${esc(c.text)}</p><div class="hero-ctas" style="justify-content:center">${btns}</div></section>`;
}

/* compose a standard marketing page from parts */
function compose(parts) {
  return parts.map(p => {
    if (p.kind === 'hero') return hero(p);
    if (p.kind === 'stats') return statsBar(p.items);
    if (p.kind === 'cta') return ctaBand(p);
    if (p.kind === 'raw') return p.html;
    return section(p);
  }).join('\n');
}

/* ============================================================
   PAGE CONTENT
   ============================================================ */
const pages = [];
const add = (path, page) => pages.push({ path, ...page });

/* shared bits */
const footerStats = [
  { value: '600M+', label: 'Daily Active Users' },
  { value: '14,000+', label: 'Digital Properties' },
  { value: '100B+', label: 'Recommendations/Mo' },
  { value: '679%', label: 'Average ROAS' }
];
const standardCTA = { kind: 'cta', title: 'Ready to Grow?', text: 'Start with $100 — no minimum commitment. Launch your first campaign in 5 minutes.' };

/* ---------------- HOME ---------------- */
add('', {
  title: 'Performance Advertising at Scale',
  navKey: '',
  description: 'MCV Network connects advertisers with millions of users across web & apps. AI-powered targeting, first-party data, real-time measurement.',
  head: `<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>`,
  body: compose([
    {
      kind: 'hero', split: `<div class="panel">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
          <div><div style="font-family:var(--font-heading);font-size:13px;font-weight:600;color:var(--text-secondary)">Campaign Performance</div>
          <div style="font-family:var(--font-heading);font-size:24px;font-weight:800;color:var(--mcv-navy)">$12,450 <span style="font-size:13px;color:var(--mcv-teal);font-weight:600">&uarr; 34%</span></div></div>
          <div class="pill teal">Live</div>
        </div>
        <div id="hero-chart" style="height:220px"></div>
      </div>`,
      eyebrow: 'Performance Advertising',
      title: `Performance Advertising<br><span class="highlight">Beyond Walled Gardens</span>`,
      lead: 'Reach millions of users across a web &amp; app network you control. AI-powered targeting, first-party data, real-time measurement.',
      ctas: [
        { label: 'Start Advertising', href: '/advertisers/get-started/', icon: 'fa-solid fa-rocket', btn: 'btn-primary' },
        { label: 'See How It Works', href: '/advertisers/platform/', icon: 'fa-solid fa-play', btn: 'btn-outline' }
      ]
    },
    { kind: 'stats', items: footerStats },
    {
      tag: 'How It Works', title: 'Reach. Convert. Grow.', text: 'Three simple steps to scale performance campaigns on the open web.',
      type: 'cards', items: [
        { num: '01', icon: 'fa-solid fa-bullseye', iconBg: 'var(--mcv-navy)', title: 'Reach Your Audience', text: 'AI matching finds the right users across the web &amp; app network — contextual targeting, lookalike audiences, first-party segments.' },
        { num: '02', icon: 'fa-solid fa-arrows-rotate', title: 'Convert at Scale', text: 'Native ads blend naturally with content. Performance AI optimizes in real time for conversions, leads and purchases — not just clicks.' },
        { num: '03', icon: 'fa-solid fa-chart-line', iconBg: 'var(--mcv-teal)', title: 'Grow Revenue', text: 'Measure ROAS, CPA and LTV precisely. Auto-scale winning campaigns. Cut CPA an average 42% vs walled gardens.' }
      ]
    },
    {
      bg: 'alt', tag: 'Ad Formats', title: 'Diverse Formats, Maximum Impact', text: 'Ads render naturally across trusted media properties.',
      type: 'tiles', items: [
        { emoji: '📰', title: 'Native Widget', text: 'Content recommendation cards — below article, in-feed' },
        { emoji: '🛒', title: 'Commerce Cards', text: 'Shoppable product cards with price, rating, CTA' },
        { emoji: '🎥', title: 'Video Outstream', text: 'Auto-play muted video, in-content placement' },
        { emoji: '📱', title: 'In-App Native', text: 'Seamless mobile experience, rewarded &amp; interstitial' },
        { emoji: '🖼️', title: 'Display Banner', text: 'IAB standard sizes — header, sidebar, sticky' },
        { emoji: '⚡', title: 'Dynamic Creative', text: 'AI-generated headlines &amp; visuals, auto A/B tested' }
      ]
    },
    {
      tag: 'Proven Results', title: 'Advertisers Winning with MCV', text: 'Average performance metrics across campaigns on the platform.',
      type: 'metrics', items: [
        { value: '679%', label: 'Average ROAS' }, { value: '-42%', label: 'Cost per Sale' },
        { value: '+130%', label: 'Conversion Rate' }, { value: '+50%', label: 'Click-Through Rate' },
        { value: '100K', label: 'Leads / Month' }, { value: '2X', label: 'Session Duration' }
      ]
    },
    {
      bg: 'alt', tag: 'Technology', title: 'AI-Powered Performance Engine', text: '17+ years of recommendation data, cookieless-ready, privacy-first.',
      html: `<div class="grid grid-2" style="gap:48px;align-items:center">
        <div class="grid" style="gap:16px">${featureRows([
          { icon: 'fa-solid fa-brain', title: 'Predictive AI Targeting', text: 'Deep-learning models predict user intent and optimize conversions in real time on every placement.' },
          { icon: 'fa-solid fa-database', title: 'First-Party Data', text: 'Signals straight from the ecosystem — browsing behavior, app usage, verified demographics.' },
          { icon: 'fa-solid fa-shield-halved', title: 'Brand Safety &amp; Fraud Prevention', text: 'ML-powered fraud detection, content classification, IVT filtering below 2%.' },
          { icon: 'fa-solid fa-cookie-bite', title: 'Cookieless Ready', text: 'Contextual AI + on-device signals + publisher first-party IDs. Built for what is next.' }
        ], 1)}</div>
        <div><div id="tech-chart" style="height:320px;background:#fff;border-radius:var(--radius-xl);border:1px solid var(--border-light);padding:16px"></div></div>
      </div>`
    },
    {
      bg: 'alt', html: `<div class="text-center" style="margin-bottom:24px"><p class="muted" style="text-transform:uppercase;letter-spacing:0.05em;font-size:13px">Trusted by leading brands &amp; publishers</p></div>${logos(['Yahoo', 'Samsung', 'NBC', 'Hyundai', 'eToro', 'Bosch'])}`
    },
    { kind: 'cta', title: 'Ready to Grow?', text: 'Start with $100 — no minimum budget. Setup in 5 minutes.', ctas: [{ label: 'Start Your First Campaign', href: '/advertisers/get-started/', btn: 'btn-teal', icon: 'fa-solid fa-rocket' }] }
  ]),
  scripts: `<script>
Highcharts.chart('hero-chart', {
  chart: { type: 'areaspline', backgroundColor: 'transparent', height: 220, margin: [10,0,30,40] },
  title: { text: null },
  xAxis: { categories: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'], labels:{style:{color:'#8993A4',fontSize:'10px'}}, lineColor:'#E8ECF0', tickColor:'#E8ECF0' },
  yAxis: { title:{text:null}, labels:{format:'\${value}',style:{color:'#8993A4',fontSize:'10px'}}, gridLineColor:'#F0F2F5' },
  tooltip: { valuePrefix:'$' },
  plotOptions: { areaspline:{ fillColor:{linearGradient:{x1:0,y1:0,x2:0,y2:1},stops:[[0,'rgba(56,192,184,0.2)'],[1,'rgba(56,192,184,0)']]}, lineWidth:3, marker:{radius:0} } },
  series: [{ name:'Revenue', data:[1420,1680,1550,1890,2100,1950,2450], color:'#38C0B8' }],
  legend:{enabled:false}, credits:{enabled:false}
});
Highcharts.chart('tech-chart', {
  chart: { type:'column', backgroundColor:'transparent', style:{fontFamily:'Inter, sans-serif'} },
  title: { text:'Performance vs Walled Gardens', style:{color:'#1A2B4A',fontSize:'14px',fontWeight:'700'} },
  xAxis: { categories:['CPA','CTR','ROAS','Viewability'], labels:{style:{color:'#6B7C93',fontSize:'11px'}}, lineColor:'#E8ECF0' },
  yAxis: { title:{text:null}, labels:{style:{color:'#6B7C93',fontSize:'10px'}}, gridLineColor:'#F0F2F5' },
  plotOptions: { column:{ borderRadius:6, borderWidth:0 } },
  series: [{ name:'MCV Network', data:[85,92,95,88], color:'#204898' }, { name:'Industry Avg', data:[55,60,50,65], color:'#E8ECF0' }],
  legend: { align:'center', verticalAlign:'bottom', itemStyle:{color:'#6B7C93',fontSize:'11px'} },
  credits:{enabled:false}
});
</script>`
});

/* ---------------- helper for inner marketing pages ---------------- */
function bc(...trail) { return trail; }
const HOME = { label: 'Home', href: '/' };

/* ---------------- ADVERTISERS ---------------- */
add('advertisers', {
  title: 'Advertisers — Why MCV Network', navKey: 'advertisers',
  description: 'Reach millions of users on premium web & apps. AI targeting, first-party data, measurable performance at scale.',
  body: compose([
    { kind: 'hero', eyebrow: 'For Advertisers', title: 'Performance Advertising, <span class="highlight">Beyond Walled Gardens</span>',
      lead: 'One platform to reach, convert and grow across the open web &amp; app ecosystem — with the targeting power you expect from Google and Meta, minus the black box.',
      breadcrumb: bc(HOME, { label: 'Advertisers' }),
      ctas: [{ label: 'Get Started', href: '/advertisers/get-started/', icon: 'fa-solid fa-rocket' }, { label: 'View Pricing', href: '/advertisers/pricing/', btn: 'btn-outline' }] },
    { kind: 'stats', items: footerStats },
    { tag: 'Why MCV', title: 'Everything You Need to Perform', text: 'A complete demand-side stack built for measurable outcomes.',
      type: 'cards', items: [
        { icon: 'fa-solid fa-bullseye', title: 'Precision Targeting', text: 'Contextual, lookalike and first-party audiences powered by predictive AI.' },
        { icon: 'fa-solid fa-wand-magic-sparkles', title: 'Creative Studio', text: 'Generate and A/B test native, video and commerce creatives automatically.' },
        { icon: 'fa-solid fa-chart-line', title: 'Real-Time Reporting', text: 'Track ROAS, CPA and LTV with full-funnel attribution — no waiting.' }
      ] },
    { bg: 'alt', tag: 'Explore', title: 'Dive Deeper', align: 'center',
      type: 'tiles', items: [
        { icon: 'fa-solid fa-gauge-high', title: 'Platform', text: 'Dashboard, AI targeting, automation' },
        { icon: 'fa-solid fa-shapes', title: 'Ad Formats', text: 'Native, video, commerce, display' },
        { icon: 'fa-solid fa-users-viewfinder', title: 'Targeting', text: 'Contextual, lookalike, first-party' },
        { icon: 'fa-solid fa-tags', title: 'Pricing', text: 'CPM / CPC / CPA — flexible' },
        { icon: 'fa-solid fa-trophy', title: 'Case Studies', text: 'Proven ROI across verticals' },
        { icon: 'fa-solid fa-rocket', title: 'Get Started', text: 'Launch in 5 minutes' }
      ] },
    standardCTA
  ])
});

add('advertisers/platform', {
  title: 'The MCV Platform', navKey: 'advertisers',
  description: 'Your ads on premium websites & apps at scale. AI targeting, creative studio, real-time reporting and deep integrations.',
  body: compose([
    { kind: 'hero', split: `<div class="panel"><div style="font-family:var(--font-heading);font-weight:700;color:var(--mcv-navy);margin-bottom:12px">Campaign Dashboard</div>${metrics([{ value: '$12.4K', label: 'Spend' }, { value: '2.4M', label: 'Impressions' }, { value: '679%', label: 'ROAS' }, { value: '3,210', label: 'Conversions' }])}</div>`,
      eyebrow: 'Platform', title: 'Your Ads on Premium Sites &amp; Apps — <span class="highlight">At Scale</span>',
      lead: 'A self-serve platform that puts enterprise-grade targeting, automation and analytics in your hands.',
      breadcrumb: bc(HOME, { label: 'Advertisers', href: '/advertisers/' }, { label: 'Platform' }),
      ctas: [{ label: 'Start Free', href: '/advertisers/get-started/', icon: 'fa-solid fa-rocket' }] },
    { tag: 'Capabilities', title: 'One Platform, Full Funnel',
      type: 'featureRows', items: [
        { icon: 'fa-solid fa-brain', title: 'AI Targeting', text: 'Predictive audiences, lookalikes and contextual matching tuned to your KPI.' },
        { icon: 'fa-solid fa-wand-magic-sparkles', title: 'Creative Studio', text: 'AI-generate ad variants and run continuous A/B tests automatically.' },
        { icon: 'fa-solid fa-chart-pie', title: 'Reporting & Attribution', text: 'Real-time analytics, multi-touch attribution and ROAS tracking.' },
        { icon: 'fa-solid fa-plug', title: 'Integrations', text: 'Conversion pixel, REST API, Google Analytics, Shopify and more.' }
      ] },
    { bg: 'alt', tag: 'vs Walled Gardens', title: 'How MCV Compares',
      type: 'table', data: { head: ['Metric', 'MCV Network', 'Google', 'Meta'], rows: [
        ['Average CPM', '<span class="hl">$3.50</span>', '$9.20', '$11.40'],
        ['Transparency', '<span class="hl">Full log-level</span>', 'Limited', 'Limited'],
        ['First-party data', '<span class="hl">Included</span>', 'Add-on', 'Add-on'],
        ['Minimum budget', '<span class="hl">$100</span>', '$500+', '$500+']
      ] } },
    standardCTA
  ])
});

add('advertisers/formats', {
  title: 'Ad Formats & Placements', navKey: 'advertisers',
  description: 'Native widgets, in-feed, display, commerce cards, video outstream and interstitial — with interactive demos and benchmarks.',
  body: compose([
    { kind: 'hero', eyebrow: 'Ad Formats', title: 'Diverse Formats, <span class="highlight">Maximum Impact</span>',
      lead: 'Choose the placement that fits your goal — every format renders natively on trusted media properties.',
      breadcrumb: bc(HOME, { label: 'Advertisers', href: '/advertisers/' }, { label: 'Formats' }),
      ctas: [{ label: 'See Specs', href: '#specs', icon: 'fa-solid fa-ruler' }] },
    { tag: 'Formats', title: 'Six Ways to Show Up',
      type: 'tiles', items: [
        { emoji: '📰', title: 'Native Widget', text: 'Recommendation cards below articles &amp; in-feed' },
        { emoji: '🛒', title: 'Commerce Card', text: 'Shoppable products with price, rating &amp; CTA' },
        { emoji: '🎥', title: 'Video Outstream', text: 'Auto-play muted video in-content' },
        { emoji: '📱', title: 'In-App Native', text: 'Rewarded &amp; interstitial mobile placements' },
        { emoji: '🖼️', title: 'Display Banner', text: 'IAB standard — header, sidebar, sticky' },
        { emoji: '⚡', title: 'Dynamic Creative', text: 'AI headlines &amp; visuals, auto A/B tested' }
      ] },
    { bg: 'alt', tag: 'Benchmarks', title: 'Performance by Format',
      html: `<a id="specs"></a>` + table({ head: ['Format', 'CTR', 'eCPM', 'Best for'], rows: [
        ['Native Widget', '0.8–2.4%', '$2–6', 'Awareness &amp; consideration'],
        ['Commerce Card', '1.2–3.1%', '$4–10', 'Direct response &amp; sales'],
        ['Video Outstream', '0.5–1.5%', '$6–14', 'Brand lift'],
        ['In-App Native', '1.0–2.8%', '$3–8', 'App installs &amp; engagement']
      ] }) },
    standardCTA
  ])
});

add('advertisers/targeting', {
  title: 'Targeting & Audiences', navKey: 'advertisers',
  description: 'Contextual, lookalike and first-party targeting powered by predictive AI — privacy-first and cookieless-ready.',
  body: compose([
    { kind: 'hero', eyebrow: 'Targeting', title: 'Reach the <span class="highlight">Right</span> People',
      lead: 'Blend contextual signals, lookalike modeling and your own first-party data into audiences that convert.',
      breadcrumb: bc(HOME, { label: 'Advertisers', href: '/advertisers/' }, { label: 'Targeting' }) },
    { tag: 'Audience Tools', title: 'Three Ways to Target',
      type: 'cards', items: [
        { icon: 'fa-solid fa-newspaper', title: 'Contextual', text: 'Match ads to page meaning in real time — no cookies required.' },
        { icon: 'fa-solid fa-users', title: 'Lookalike', text: 'Expand reach by modeling users similar to your best customers.' },
        { icon: 'fa-solid fa-fingerprint', title: 'First-Party', text: 'Upload CRM segments and retarget with full consent management.' }
      ] },
    standardCTA
  ])
});

add('advertisers/pricing', {
  title: 'Pricing & Packages', navKey: 'advertisers',
  description: 'Transparent CPM, CPC and CPA pricing. Start with $100 — no minimum commitment.',
  body: compose([
    { kind: 'hero', eyebrow: 'Pricing', title: 'Transparent Pricing, <span class="highlight">No Surprises</span>',
      lead: 'Pay for outcomes. Choose CPM, CPC or CPA — start with $100 and scale as you grow.',
      breadcrumb: bc(HOME, { label: 'Advertisers', href: '/advertisers/' }, { label: 'Pricing' }) },
    { html: pricing([
        { name: 'Starter', price: '$100', unit: 'min. deposit', desc: 'For testing the waters', features: ['Self-serve dashboard', 'All ad formats', 'Contextual targeting', 'Standard reporting', 'Email support'], cta: 'Start Now' },
        { name: 'Growth', price: '$1,000+', unit: '/ month', desc: 'For scaling brands', featured: true, features: ['Everything in Starter', 'AI targeting &amp; lookalikes', 'Creative studio', 'Conversion API &amp; pixels', 'Priority support'], cta: 'Get Started' },
        { name: 'Enterprise', price: 'Custom', desc: 'Managed service', features: ['Everything in Growth', 'Dedicated account manager', 'Custom audiences', 'White-label options', 'SLA &amp; onboarding'], cta: 'Contact Sales', href: '/company/contact/' }
      ]) },
    { bg: 'alt', tag: 'Models', title: 'Flexible Buying Models',
      type: 'table', data: { head: ['Model', 'You pay for', 'Best for'], rows: [
        ['CPM', 'Per 1,000 impressions', 'Awareness &amp; reach'],
        ['CPC', 'Per click', 'Traffic &amp; consideration'],
        ['CPA', 'Per acquisition', 'Performance &amp; ROAS']
      ] } },
    standardCTA
  ])
});

add('advertisers/case-studies', {
  title: 'Case Studies', navKey: 'advertisers',
  description: 'Real ROI stories from advertisers winning with MCV Network across e-commerce, finance and healthcare.',
  body: compose([
    { kind: 'hero', eyebrow: 'Case Studies', title: 'Advertisers <span class="highlight">Winning</span> with MCV',
      lead: 'Measurable outcomes across industries — see how brands grew with the open web.',
      breadcrumb: bc(HOME, { label: 'Advertisers', href: '/advertisers/' }, { label: 'Case Studies' }) },
    { type: 'cards', items: [
        { icon: 'fa-solid fa-bag-shopping', iconBg: 'var(--mcv-navy)', title: 'E-commerce: +679% ROAS', text: 'A DTC retailer scaled spend 4x while cutting cost per sale 42% with dynamic commerce cards.' },
        { icon: 'fa-solid fa-heart-pulse', iconBg: 'var(--mcv-teal)', title: 'Healthcare: 10K enrollments', text: 'An ACA insurer generated 10,000 enrollments at a $12 CPL with compliant audience targeting.' },
        { icon: 'fa-solid fa-building-columns', title: 'Finance: -38% CPA', text: 'A fintech app drove qualified sign-ups at 38% lower CPA vs social channels.' }
      ] },
    standardCTA
  ])
});

add('advertisers/get-started', {
  title: 'Get Started', navKey: 'advertisers',
  description: 'Launch your first MCV campaign in 5 minutes. Create an account, add budget, go live.',
  body: compose([
    { kind: 'hero', eyebrow: 'Get Started', title: 'Launch in <span class="highlight">5 Minutes</span>',
      lead: 'Create an account, fund with $100, and your first campaign can be live today.',
      breadcrumb: bc(HOME, { label: 'Advertisers', href: '/advertisers/' }, { label: 'Get Started' }),
      ctas: [{ label: 'Create Account', href: '/signup/', icon: 'fa-solid fa-user-plus' }] },
    { tag: 'Onboarding', title: 'Four Steps to Live',
      type: 'cards', cols: 4, items: [
        { num: '01', icon: 'fa-solid fa-user-plus', title: 'Sign Up', text: 'Create your advertiser account in under a minute.' },
        { num: '02', icon: 'fa-solid fa-wallet', title: 'Add Budget', text: 'Fund from $100 with card or bank transfer.' },
        { num: '03', icon: 'fa-solid fa-sliders', title: 'Build Campaign', text: 'Pick format, audience and creative in the wizard.' },
        { num: '04', icon: 'fa-solid fa-rocket', title: 'Go Live', text: 'Launch and watch results in real time.' }
      ] },
    { kind: 'cta', title: 'Create Your Account', text: 'No minimum commitment. Cancel anytime.', ctas: [{ label: 'Sign Up Free', href: '/signup/', btn: 'btn-teal', icon: 'fa-solid fa-user-plus' }] }
  ])
});

/* ---------------- PUBLISHERS (Phase 4) ---------------- */
const pubPill = '<span class="pill amber" style="margin-left:8px">Phase 4</span>';
add('publishers', {
  title: 'Publishers — Monetize Your Traffic', navKey: 'publishers',
  description: 'Turn your audience into revenue with native recommendation widgets and a fair revenue share.',
  body: compose([
    { kind: 'hero', eyebrow: 'For Publishers', title: 'Monetize Your Traffic, <span class="highlight">Your Network</span>',
      lead: 'Earn revenue with native recommendation widgets that respect your reader experience. <strong>Publisher program — launching Phase 4.</strong>',
      breadcrumb: bc(HOME, { label: 'Publishers' }),
      ctas: [{ label: 'Join the Waitlist', href: '/publishers/apply/', icon: 'fa-solid fa-paper-plane' }] },
    { tag: 'Why Publishers', title: 'Revenue Without Compromise',
      type: 'cards', items: [
        { icon: 'fa-solid fa-coins', title: 'Fair Revenue Share', text: 'Keep the majority of every dollar with transparent reporting.' },
        { icon: 'fa-solid fa-puzzle-piece', title: 'Easy Integration', text: 'Drop in a single JS tag or use our SDK — live in minutes.' },
        { icon: 'fa-solid fa-feather', title: 'Reader-First', text: 'Native widgets that match your design and protect engagement.' }
      ] },
    standardCTA
  ])
});

[
  ['publishers/monetization', 'Monetization Solutions', 'Widget types, revenue share and yield optimization for publishers.'],
  ['publishers/sdk', 'Publisher SDK', 'Integrate the MCV JS tag and SDK to start earning.'],
  ['publishers/formats', 'Widget Formats', 'Customizable native widget formats that match your site.'],
  ['publishers/performance', 'Revenue Optimization', 'Tips and tools to maximize publisher revenue.'],
  ['publishers/case-studies', 'Publisher Success Stories', 'How publishers grew revenue with MCV Network.'],
  ['publishers/apply', 'Apply to the Network', 'Join the MCV publisher network — apply today.']
].forEach(([path, title, desc]) => {
  add(path, {
    title, navKey: 'publishers', description: desc,
    body: compose([
      { kind: 'hero', eyebrow: 'Publishers', title: `${title} ${pubPill}`, lead: desc + ' This page is part of the Phase 4 publisher rollout.',
        breadcrumb: bc(HOME, { label: 'Publishers', href: '/publishers/' }, { label: title }),
        ctas: [{ label: 'Join the Waitlist', href: '/publishers/apply/', icon: 'fa-solid fa-paper-plane' }] },
      { tag: 'Overview', title: 'Built for Publisher Growth',
        type: 'cards', items: [
          { icon: 'fa-solid fa-coins', title: 'Maximize Yield', text: 'Smart auction and fill optimization for every impression.' },
          { icon: 'fa-solid fa-gauge', title: 'Lightweight', text: 'Async loading that keeps Core Web Vitals healthy.' },
          { icon: 'fa-solid fa-chart-line', title: 'Clear Reporting', text: 'Real-time earnings, fill rate and RPM dashboards.' }
        ] },
      standardCTA
    ])
  });
});

/* ---------------- COMMERCE ---------------- */
add('commerce/overview', {
  title: 'Commerce Media', navKey: 'commerce',
  description: 'Shoppable content, dynamic product feeds and affiliate commerce across the open web.',
  body: compose([
    { kind: 'hero', eyebrow: 'Commerce', title: 'Shoppable Content That <span class="highlight">Converts</span>',
      lead: 'Turn editorial and recommendation surfaces into commerce with dynamic, shoppable product cards.',
      breadcrumb: bc(HOME, { label: 'Commerce', href: '/commerce/overview/' }, { label: 'Overview' }),
      ctas: [{ label: 'Talk to Sales', href: '/company/contact/', icon: 'fa-solid fa-comments' }] },
    { tag: 'Commerce Suite', title: 'From Discovery to Checkout',
      type: 'tiles', items: [
        { icon: 'fa-solid fa-cart-shopping', title: 'Shoppable Content', text: 'Dynamic product cards with live pricing' },
        { icon: 'fa-solid fa-handshake-angle', title: 'Affiliate', text: 'Commission-based commerce at scale' },
        { icon: 'fa-solid fa-store', title: 'Merchants', text: 'SKU feeds &amp; catalog integration' },
        { icon: 'fa-solid fa-bolt', title: 'Dynamic Pricing', text: 'Real-time price &amp; inventory sync' }
      ] },
    standardCTA
  ])
});
[
  ['commerce/shoppable-content', 'Shoppable Content', 'Shoppable widgets with product feeds and dynamic pricing.'],
  ['commerce/affiliate', 'Affiliate Program', 'Commission structure and affiliate commerce tools.'],
  ['commerce/merchants', 'Merchant Integration', 'Connect SKU feeds and catalogs via API.']
].forEach(([path, title, desc]) => {
  add(path, {
    title, navKey: 'commerce', description: desc,
    body: compose([
      { kind: 'hero', eyebrow: 'Commerce', title, lead: desc,
        breadcrumb: bc(HOME, { label: 'Commerce', href: '/commerce/overview/' }, { label: title }),
        ctas: [{ label: 'Talk to Sales', href: '/company/contact/', icon: 'fa-solid fa-comments' }] },
      { tag: 'Highlights', title: 'How It Works',
        type: 'cards', items: [
          { icon: 'fa-solid fa-rss', title: 'Connect Feed', text: 'Sync your product catalog with live pricing and inventory.' },
          { icon: 'fa-solid fa-wand-magic-sparkles', title: 'Auto Cards', text: 'Generate shoppable cards tuned to each placement.' },
          { icon: 'fa-solid fa-chart-line', title: 'Measure', text: 'Track add-to-cart, sales and ROAS end to end.' }
        ] },
      standardCTA
    ])
  });
});

/* ---------------- TECHNOLOGY ---------------- */
[
  ['technology/ai-engine', 'AI Recommendation Engine', 'Deep-learning models that predict intent and optimize conversions in real time.', [
    { icon: 'fa-solid fa-brain', title: 'Deep Learning', text: 'Neural models trained on 17+ years of recommendation data.' },
    { icon: 'fa-solid fa-bolt', title: 'Real-Time Bidding', text: 'Sub-100ms decisioning on every impression.' },
    { icon: 'fa-solid fa-arrows-rotate', title: 'Continuous Learning', text: 'Models retrain on outcomes to keep improving ROAS.' }
  ]],
  ['technology/audience-data', 'First-Party Data & Segments', 'A privacy-safe user graph built from first-party signals across the ecosystem.', [
    { icon: 'fa-solid fa-database', title: 'User Graph', text: 'Unified, consented identity across web &amp; app.' },
    { icon: 'fa-solid fa-layer-group', title: 'Rich Segments', text: 'Behavioral, contextual and demographic segments.' },
    { icon: 'fa-solid fa-lock', title: 'Consent-Aware', text: 'Every signal respects user consent and regional law.' }
  ]],
  ['technology/brand-safety', 'Brand Safety & Fraud Prevention', 'ML-powered IVT filtering and content classification keep your brand safe.', [
    { icon: 'fa-solid fa-shield-halved', title: 'IVT Filtering', text: 'Invalid traffic kept below 2% with ML detection.' },
    { icon: 'fa-solid fa-filter', title: 'Content Classification', text: 'Page-level safety scoring before every placement.' },
    { icon: 'fa-solid fa-user-shield', title: 'Verified Inventory', text: 'Third-party verified, transparent supply paths.' }
  ]],
  ['technology/privacy', 'Privacy-First Approach', 'Cookieless-ready, GDPR and CCPA compliant by design.', [
    { icon: 'fa-solid fa-cookie-bite', title: 'Cookieless Ready', text: 'Contextual AI + on-device signals + publisher IDs.' },
    { icon: 'fa-solid fa-scale-balanced', title: 'GDPR & CCPA', text: 'Built-in consent management and opt-out.' },
    { icon: 'fa-solid fa-eye-slash', title: 'No PII Exposure', text: 'Privacy-preserving targeting with no raw PII.' }
  ]]
].forEach(([path, title, desc, items]) => {
  add(path, {
    title, navKey: 'technology', description: desc,
    body: compose([
      { kind: 'hero', eyebrow: 'Technology', title, lead: desc,
        breadcrumb: bc(HOME, { label: 'Technology', href: '/technology/ai-engine/' }, { label: title }),
        ctas: [{ label: 'See the Platform', href: '/advertisers/platform/', icon: 'fa-solid fa-arrow-right' }] },
      { tag: 'Capabilities', title: 'Under the Hood', type: 'cards', items },
      standardCTA
    ])
  });
});

/* ---------------- SOLUTIONS ---------------- */
/* Healthcare gets a richer treatment per spec */
add('solutions/healthcare', {
  title: 'Healthcare & Insurance Solutions', navKey: 'solutions',
  description: 'Reach 1.2M+ verified low-income Americans — compliant and effective targeting for Medicare, Medicaid and ACA.',
  body: compose([
    { kind: 'hero', variant: 'dark', eyebrow: 'Healthcare', title: 'Reach 1.2M+ Verified Americans — <span class="highlight">Compliant &amp; Effective</span>',
      lead: 'Connect with a verified low-income Lifeline audience for Medicare enrollment, Medicaid awareness, health plans and telehealth.',
      breadcrumb: bc(HOME, { label: 'Solutions', href: '/solutions/ecommerce/' }, { label: 'Healthcare' }),
      ctas: [{ label: 'Schedule a Demo', href: '/company/contact/', icon: 'fa-solid fa-calendar-check', btn: 'btn-teal' }] },
    { tag: 'Audience', title: 'A High-Value, Verified Vertical',
      type: 'metrics', items: [
        { value: '1.2M+', label: 'Verified Users' }, { value: '50', label: 'States Covered' },
        { value: '$12', label: 'Example CPL' }, { value: '10K', label: 'Enrollments (case)' }
      ] },
    { bg: 'alt', tag: 'Use Cases', title: 'Built for Health Marketers',
      type: 'cards', cols: 4, items: [
        { icon: 'fa-solid fa-id-card', title: 'Medicare', text: 'Enrollment &amp; plan awareness campaigns.' },
        { icon: 'fa-solid fa-hand-holding-medical', title: 'Medicaid', text: 'Eligibility &amp; renewal outreach.' },
        { icon: 'fa-solid fa-file-medical', title: 'ACA Plans', text: 'Open-enrollment acquisition.' },
        { icon: 'fa-solid fa-video', title: 'Telehealth', text: 'Sign-ups for virtual care services.' }
      ] },
    { tag: 'Compliance', title: 'Safe by Design',
      type: 'featureRows', items: [
        { icon: 'fa-solid fa-shield-halved', title: 'HIPAA-Safe Targeting', text: 'No exposure of protected health information.' },
        { icon: 'fa-solid fa-scale-balanced', title: 'FCC Compliant', text: 'Lifeline-aligned audience handling.' },
        { icon: 'fa-solid fa-user-lock', title: 'No PII Exposure', text: 'Privacy-preserving segments only.' },
        { icon: 'fa-solid fa-sliders', title: 'Granular Controls', text: 'Target by age, state, household size and eligibility.' }
      ] },
    { kind: 'cta', title: 'Schedule a Demo', text: 'See how compliant targeting drives enrollments at scale.', ctas: [{ label: 'Talk to Sales', href: '/company/contact/', btn: 'btn-teal', icon: 'fa-solid fa-calendar-check' }] }
  ])
});

[
  ['solutions/ecommerce', 'E-commerce Solutions', 'ROAS optimization and dynamic product ads for online retailers.', [
    { icon: 'fa-solid fa-bag-shopping', title: 'Dynamic Product Ads', text: 'Auto-generated shoppable cards from your catalog.' },
    { icon: 'fa-solid fa-arrow-trend-up', title: 'ROAS Optimization', text: 'AI bidding tuned to return on ad spend.' },
    { icon: 'fa-solid fa-rotate', title: 'Retargeting', text: 'Recover carts and re-engage shoppers across the web.' }
  ]],
  ['solutions/finance', 'Financial Services Solutions', 'Compliant performance marketing for fintech, insurance and lending.', [
    { icon: 'fa-solid fa-building-columns', title: 'Fintech & Banking', text: 'Drive qualified account sign-ups.' },
    { icon: 'fa-solid fa-umbrella', title: 'Insurance', text: 'Quote and lead generation at scale.' },
    { icon: 'fa-solid fa-hand-holding-dollar', title: 'Lending', text: 'Micro-loan and credit acquisition.' }
  ]],
  ['solutions/apps', 'App Developer Solutions', 'CPI campaigns and cross-promotion to grow installs and engagement.', [
    { icon: 'fa-solid fa-mobile-screen', title: 'CPI Campaigns', text: 'Pay per install with quality safeguards.' },
    { icon: 'fa-solid fa-arrows-left-right', title: 'Cross-Promote', text: 'Reach users across the app ecosystem.' },
    { icon: 'fa-solid fa-users-gear', title: 'Re-engagement', text: 'Win back lapsed users with deep links.' }
  ]],
  ['solutions/agencies', 'Agencies & Resellers', 'White-label tooling and bulk pricing for agencies and resellers.', [
    { icon: 'fa-solid fa-tag', title: 'White-Label', text: 'Run MCV under your own brand.' },
    { icon: 'fa-solid fa-layer-group', title: 'Multi-Client', text: 'Manage many advertisers from one seat.' },
    { icon: 'fa-solid fa-percent', title: 'Bulk Discounts', text: 'Volume pricing and rev-share options.' }
  ]],
  ['solutions/enterprise', 'Enterprise Solutions', 'Managed service and dedicated support for enterprise brands.', [
    { icon: 'fa-solid fa-user-tie', title: 'Account Manager', text: 'Dedicated strategist for your account.' },
    { icon: 'fa-solid fa-gears', title: 'Managed Service', text: 'We run campaigns end to end for you.' },
    { icon: 'fa-solid fa-file-signature', title: 'SLA & Onboarding', text: 'Guaranteed support and custom onboarding.' }
  ]]
].forEach(([path, title, desc, items]) => {
  add(path, {
    title, navKey: 'solutions', description: desc,
    body: compose([
      { kind: 'hero', eyebrow: 'Solutions', title, lead: desc,
        breadcrumb: bc(HOME, { label: 'Solutions', href: '/solutions/ecommerce/' }, { label: title }),
        ctas: [{ label: 'Get Started', href: '/advertisers/get-started/', icon: 'fa-solid fa-rocket' }, { label: 'Talk to Sales', href: '/company/contact/', btn: 'btn-outline' }] },
      { tag: 'Why MCV', title: 'Outcomes for Your Vertical', type: 'cards', items },
      standardCTA
    ])
  });
});

/* ---------------- RESOURCES ---------------- */
add('resources/blog', {
  title: 'Blog & Industry Insights', navKey: 'resources',
  description: 'Thought leadership and practical guides on performance advertising, the open web and ad tech.',
  body: compose([
    { kind: 'hero', eyebrow: 'Blog', title: 'Insights for the <span class="highlight">Open Web</span>',
      lead: 'Strategies, benchmarks and product updates from the MCV Network team.',
      breadcrumb: bc(HOME, { label: 'Resources', href: '/resources/blog/' }, { label: 'Blog' }) },
    { type: 'cards', items: [
        { icon: 'fa-solid fa-arrow-trend-up', title: 'How to Improve Ad ROAS', text: 'Five levers that move return on ad spend the fastest.' },
        { icon: 'fa-solid fa-newspaper', title: 'Native vs Display Ads', text: 'When each format wins — and how to combine them.' },
        { icon: 'fa-solid fa-cookie-bite', title: 'Life After Cookies', text: 'A practical guide to cookieless targeting in 2026.' }
      ] },
    standardCTA
  ])
});
[
  ['resources/whitepapers', 'Reports & Whitepapers', 'Downloadable research and industry reports.'],
  ['resources/webinars', 'Webinars & Events', 'Live and on-demand sessions with the MCV team.'],
  ['resources/creative-gallery', 'Creative Gallery', 'Best-performing ad creative for inspiration.'],
  ['resources/glossary', 'Ad Tech Glossary', 'Plain-language definitions of advertising terms.'],
  ['resources/api-docs', 'API Documentation', 'Developer documentation for the MCV API and SDKs.']
].forEach(([path, title, desc]) => {
  const external = path === 'resources/api-docs';
  add(path, {
    title, navKey: 'resources', description: desc,
    body: compose([
      { kind: 'hero', eyebrow: 'Resources', title, lead: desc,
        breadcrumb: bc(HOME, { label: 'Resources', href: '/resources/blog/' }, { label: title }),
        ctas: external ? [{ label: 'Open docs.mcv.network', href: 'https://docs.mcv.network', icon: 'fa-solid fa-arrow-up-right-from-square' }] : [{ label: 'Browse Resources', href: '/resources/blog/', icon: 'fa-solid fa-arrow-right' }] },
      { tag: 'Library', title: 'What You Will Find',
        type: 'cards', items: [
          { icon: 'fa-solid fa-file-lines', title: 'Practical Guides', text: 'Step-by-step playbooks you can apply today.' },
          { icon: 'fa-solid fa-chart-column', title: 'Benchmarks', text: 'Industry data to calibrate your campaigns.' },
          { icon: 'fa-solid fa-graduation-cap', title: 'Education', text: 'Learn the open web and performance advertising.' }
        ] },
      standardCTA
    ])
  });
});

/* ---------------- COMPANY ---------------- */
add('company/about', {
  title: 'About MCV Network', navKey: 'company',
  description: 'Our mission: performance advertising at scale, beyond walled gardens, for the open web.',
  body: compose([
    { kind: 'hero', eyebrow: 'About', title: 'Where the <span class="highlight">Open Web</span> Wins',
      lead: 'We build the performance advertising platform that gives advertisers and publishers an alternative to the walled gardens.',
      breadcrumb: bc(HOME, { label: 'Company', href: '/company/about/' }, { label: 'About' }) },
    { tag: 'Our Values', title: 'What We Stand For',
      type: 'cards', items: [
        { icon: 'fa-solid fa-bullseye', title: 'Bold & Direct', text: 'No fluff. We lead with value and let data speak.' },
        { icon: 'fa-solid fa-chart-line', title: 'Data-First', text: 'Every claim is backed by numbers, not adjectives.' },
        { icon: 'fa-solid fa-rocket', title: 'Growth-Oriented', text: 'We exist to help our partners scale and grow.' }
      ] },
    { kind: 'stats', items: footerStats },
    standardCTA
  ])
});
[
  ['company/team', 'Leadership Team', 'Meet the people building MCV Network.'],
  ['company/careers', 'Careers', 'Join us and help the open web win.'],
  ['company/press', 'Press & Media', 'Press releases, news and our media kit.'],
  ['company/partners', 'Strategic Partners', 'Our partner program and integrations.']
].forEach(([path, title, desc]) => {
  add(path, {
    title, navKey: 'company', description: desc,
    body: compose([
      { kind: 'hero', eyebrow: 'Company', title, lead: desc,
        breadcrumb: bc(HOME, { label: 'Company', href: '/company/about/' }, { label: title }),
        ctas: [{ label: 'Contact Us', href: '/company/contact/', icon: 'fa-solid fa-envelope' }] },
      { tag: 'Company', title: 'Learn More',
        type: 'cards', items: [
          { icon: 'fa-solid fa-flag', title: 'Our Mission', text: 'Performance advertising at scale, beyond walled gardens.' },
          { icon: 'fa-solid fa-handshake', title: 'Work With Us', text: 'Advertisers, publishers and partners welcome.' },
          { icon: 'fa-solid fa-location-dot', title: 'Get in Touch', text: 'Reach our team for sales and support.' }
        ] },
      standardCTA
    ])
  });
});

/* Contact gets a real form */
add('company/contact', {
  title: 'Contact MCV Network', navKey: 'company',
  description: 'Get in touch with sales and support. We respond within one business day.',
  body: compose([
    { kind: 'hero', eyebrow: 'Contact', title: 'Let’s <span class="highlight">Talk</span>',
      lead: 'Questions about the platform, pricing or a custom solution? Our team is here to help.',
      breadcrumb: bc(HOME, { label: 'Company', href: '/company/about/' }, { label: 'Contact' }) },
    { html: `<div class="grid grid-2" style="gap:48px;align-items:start">
      <div>
        <h2 style="font-family:var(--font-heading);font-size:24px;font-weight:800;margin-bottom:16px">Send us a message</h2>
        <form class="form-card" onsubmit="event.preventDefault(); this.querySelector('.form-ok').style.display='block';">
          <div class="grid grid-2" style="gap:16px"><div class="form-field"><label>First name</label><input type="text" required></div><div class="form-field"><label>Last name</label><input type="text" required></div></div>
          <div class="form-field"><label>Work email</label><input type="email" required></div>
          <div class="form-field"><label>Company</label><input type="text"></div>
          <div class="form-field"><label>I am a…</label><select><option>Advertiser</option><option>Publisher</option><option>Agency</option><option>Partner</option></select></div>
          <div class="form-field"><label>Message</label><textarea rows="4"></textarea></div>
          <button class="btn btn-primary" type="submit"><i class="fa-solid fa-paper-plane"></i> Send Message</button>
          <p class="form-ok" style="display:none;color:var(--success);font-weight:600;margin-top:14px">Thanks! We’ll be in touch within one business day.</p>
        </form>
      </div>
      <div>
        <h2 style="font-family:var(--font-heading);font-size:24px;font-weight:800;margin-bottom:16px">Other ways to reach us</h2>
        <ul class="checklist">
          <li><strong>Sales:</strong> sales@mcv.network</li>
          <li><strong>Support:</strong> support@mcv.network</li>
          <li><strong>Press:</strong> press@mcv.network</li>
          <li><strong>Status:</strong> status.mcv.network</li>
          <li><strong>Docs:</strong> docs.mcv.network</li>
        </ul>
      </div>
    </div>` }
  ])
});

/* ---------------- LEGAL ---------------- */
[
  ['legal/terms', 'Terms of Service', 'The terms governing use of MCV Network.'],
  ['legal/privacy-policy', 'Privacy Policy', 'How MCV Network collects, uses and protects data. GDPR & CCPA compliant.'],
  ['legal/cookie-policy', 'Cookie Policy', 'How and why MCV Network uses cookies and similar technologies.'],
  ['legal/advertiser-terms', 'Advertiser Terms of Service', 'Terms specific to advertisers on MCV Network.'],
  ['legal/publisher-terms', 'Publisher Terms of Service', 'Terms specific to publishers on MCV Network.']
].forEach(([path, title, desc]) => {
  add(path, {
    title, navKey: '', description: desc,
    body: compose([
      { kind: 'hero', eyebrow: 'Legal', title, lead: desc,
        breadcrumb: bc(HOME, { label: 'Legal' }, { label: title }) },
      { html: prose(`
        <p class="muted">Last updated: June 29, 2026 — this is placeholder legal copy for the static prototype.</p>
        <h2>1. Introduction</h2>
        <p>These terms describe the agreement between you and MCV Network regarding your use of our services, websites and platform.</p>
        <h2>2. Use of the Service</h2>
        <p>You agree to use the service in compliance with applicable laws and our acceptable-use policies. You are responsible for the content you submit and the campaigns you run.</p>
        <h2>3. Data & Privacy</h2>
        <p>We handle personal data in line with our Privacy Policy and applicable regulations including GDPR and CCPA. We do not expose personally identifiable information in targeting.</p>
        <h2>4. Billing</h2>
        <p>Charges are based on the buying model you select (CPM, CPC or CPA). Minimum deposits and payment terms are shown at checkout.</p>
        <h2>5. Contact</h2>
        <p>Questions about these terms? Email <strong>legal@mcv.network</strong>.</p>
      `) }
    ])
  });
});

/* ---------------- AUTH (login / signup) ---------------- */
function authPage(mode) {
  const isLogin = mode === 'login';
  const sideChecklist = ['600M+ daily active users', 'AI targeting &amp; first-party data', 'Real-time ROAS reporting', 'Start from just $100'];
  const form = `<div class="auth-form-inner">
    <h1>${isLogin ? 'Welcome back' : 'Create your account'}</h1>
    <p class="sub">${isLogin ? 'Log in to your MCV Network dashboard.' : 'Start advertising on the open web in minutes.'}</p>
    <form onsubmit="event.preventDefault(); window.location.href='https://ads.mcv.network';">
      ${isLogin ? '' : `<div class="form-field"><label>Full name</label><input type="text" required></div>`}
      <div class="form-field"><label>Work email</label><input type="email" required></div>
      <div class="form-field"><label>Password</label><input type="password" required></div>
      ${isLogin ? '' : `<div class="form-field"><label>I am a…</label><select><option>Advertiser</option><option>Publisher</option><option>Agency</option></select></div>`}
      <button class="btn btn-primary" type="submit">${isLogin ? 'Log In' : 'Create Account'} <i class="fa-solid fa-arrow-right"></i></button>
    </form>
    <p class="auth-alt">${isLogin
      ? 'New to MCV? <a href="/signup/">Create an account</a>'
      : 'Already have an account? <a href="/login/">Log in</a>'}</p>
  </div>`;
  return compose([{ kind: 'raw', html: `<div class="auth-wrap">
    <div class="auth-side">
      <h2>${isLogin ? 'Performance Advertising at Scale' : 'Reach. Convert. Grow.'}</h2>
      <p>${isLogin ? 'Beyond walled gardens — reach millions across the open web.' : 'Join thousands of advertisers growing on the open web.'}</p>
      <ul class="checklist">${sideChecklist.map(i => `<li>${i}</li>`).join('')}</ul>
    </div>
    <div class="auth-form">${form}</div>
  </div>` }]);
}
add('login', { title: 'Log In', navKey: '', description: 'Log in to your MCV Network advertiser or publisher account.', body: authPage('login') });
add('signup', { title: 'Sign Up', navKey: '', description: 'Create your MCV Network account and start advertising in minutes.', body: authPage('signup') });

/* ============================================================
   WRITE FILES
   ============================================================ */
let count = 0;
for (const page of pages) {
  const outDir = page.path === '' ? ROOT : join(ROOT, page.path);
  mkdirSync(outDir, { recursive: true });
  writeFileSync(join(outDir, 'index.html'), layout(page), 'utf8');
  count++;
}
console.log(`Generated ${count} pages.`);
