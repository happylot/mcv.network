# 📋 MCV NETWORK — TÀI LIỆU TỔNG HỢP DỰ ÁN

**Domain:** mcv.network  
**Tagline:** "Reach. Convert. Grow."  
**Ngày cập nhật:** 29/06/2026  
**Trạng thái:** Website prototype hoàn thành — sẵn sàng chuyển production  

---

## 1. TỔNG QUAN DỰ ÁN

### 1.1 Mô tả

MCV Network là **nền tảng quảng cáo hiệu suất (performance advertising)** kiểu Taboola, xây dựng trên hệ sinh thái web & app mà Softel Solutions đang sở hữu và kiểm soát.

### 1.2 Lợi thế cạnh tranh cốt lõi

| Yếu tố | MCV Network | Taboola (đối thủ) |
|---------|------------|-------------------|
| **Inventory** | 100% sở hữu (zero TAC) | Phải chia 60-70% cho publishers |
| **Gross Margin** | 70-90% | 25-30% |
| **US Audience** | 1.2M verified Lifeline users (Airtalk/TagMobile) | Không có verified income data |
| **First-party data** | Từ hệ sinh thái riêng + Lifeline demographics | Phụ thuộc publisher |
| **Quyết định** | Agile, nhanh | Bureaucracy, 1000+ nhân viên |

### 1.3 Thị trường mục tiêu

| Metric | Giá trị |
|--------|---------|
| TAM (Digital Ads toàn cầu 2026) | $950 tỷ |
| SAM (Native Advertising) | $125 tỷ |
| Performance Ads (Open Web) | $55 tỷ |
| SOM (Mục tiêu Năm 3) | $3-10 triệu |

---

## 2. BRAND IDENTITY

### 2.1 Logo

- **File:** `logo_MCV_network.png`
- **Cấu trúc:** Chữ M (Navy + Teal arrow ↗) + C (Navy + Teal arrow ↻) + V (Teal gradient)
- **"NETWORK"** — Uppercase, letter-spacing rộng, flanked by 2 teal lines
- **Tagline:** "Reach. Convert. Grow." (M=reach, C=convert, V=grow)

### 2.2 Bảng màu (từ Logo)

| Color | Hex | RGB | Usage |
|-------|-----|-----|-------|
| **MCV Navy** | `#204898` | (32, 72, 152) | Primary — chữ M, C, NETWORK, headings, CTA |
| **MCV Teal** | `#38C0B8` | (56, 192, 184) | Secondary — arrows, chữ V, accents, success |
| **Navy Dark** | `#0D1B2E` | (13, 27, 46) | Dark backgrounds, footer, hero |
| **Blue Light** | `#2D6BC4` | (45, 107, 196) | Hover states, links, lighter elements |
| **Teal Light** | `#6EE4DC` | (110, 228, 220) | Badges, highlights, subtle accents |
| **Text Primary** | `#1A2B4A` | — | Body text, headings |
| **Text Secondary** | `#6B7C93` | — | Captions, labels |
| **Background** | `#F4F6F8` | — | Page backgrounds, cards |

### 2.3 Typography

| Element | Font | Weight | Size |
|---------|------|--------|------|
| Display | Inter | 900 (Black) | 40-56px |
| H1 | Inter | 800 (ExtraBold) | 32-40px |
| H2 | Inter | 700 (Bold) | 24-28px |
| H3 | Inter | 600 (SemiBold) | 18-20px |
| Body | DM Sans | 400 (Regular) | 16px |
| Caption | DM Sans | 400 | 13px |
| Data/Code | JetBrains Mono | 400 | 14px |

### 2.4 Voice & Tone

- **Bold & Direct** — Không rào trước, đi thẳng vào giá trị
- **Data-First** — Mọi claim đều có số liệu: "+679% ROAS" > "great results"
- **Growth-Oriented** — "Scale", "Grow", "Maximize", "Reach further"
- **Tech-Forward** — AI-first, Privacy-ready, "Built for what's next"

### 2.5 Files tham khảo

- `mcv_brand_kit.html` — Brand Kit interactive (mở bằng Chrome)
- `logo_MCV_network.png` — Logo gốc

---

## 3. WEBSITE (www.mcv.network)

### 3.1 Tech Stack

| Component | Technology |
|-----------|-----------|
| Generator | `build.mjs` (Node.js) — sinh 48 pages static HTML |
| CSS Design System | `assets/css/mcv.css` (20.8KB) |
| Shared JS | `assets/js/mcv.js` (nav + footer injection) |
| Charts | Highcharts (CDN) |
| Icons | Font Awesome 6.5 (CDN) |
| Fonts | Inter + DM Sans (Google Fonts CDN) |
| Local preview | `node serve.mjs` → http://localhost:8080 |

### 3.2 Cấu trúc thư mục

```
MCV.Network/
├── index.html                        ← Homepage
├── assets/
│   ├── css/mcv.css                   ← Design system (tokens + components)
│   └── js/mcv.js                     ← Shared nav/footer
├── advertisers/
│   ├── index.html                    ← /advertisers
│   ├── platform/index.html           ← /advertisers/platform
│   ├── formats/index.html            ← /advertisers/formats
│   ├── targeting/index.html          ← /advertisers/targeting
│   ├── pricing/index.html            ← /advertisers/pricing
│   ├── case-studies/index.html       ← /advertisers/case-studies
│   └── get-started/index.html        ← /advertisers/get-started
├── technology/
│   ├── ai-engine/index.html
│   ├── audience-data/index.html
│   ├── brand-safety/index.html
│   └── privacy/index.html
├── solutions/
│   ├── ecommerce/index.html
│   ├── finance/index.html
│   ├── healthcare/index.html         ← HIGH VALUE (Lifeline audience)
│   ├── apps/index.html
│   ├── agencies/index.html
│   └── enterprise/index.html
├── commerce/
│   ├── overview/index.html
│   ├── shoppable-content/index.html
│   ├── affiliate/index.html
│   └── merchants/index.html
├── publishers/
│   ├── index.html
│   ├── monetization/index.html
│   ├── sdk/index.html
│   ├── formats/index.html
│   ├── performance/index.html
│   ├── case-studies/index.html
│   └── apply/index.html
├── resources/
│   ├── blog/index.html
│   ├── whitepapers/index.html
│   ├── webinars/index.html
│   ├── creative-gallery/index.html
│   ├── glossary/index.html
│   └── api-docs/index.html
├── company/
│   ├── about/index.html
│   ├── team/index.html
│   ├── careers/index.html
│   ├── press/index.html
│   ├── partners/index.html
│   └── contact/index.html
├── legal/
│   ├── terms/index.html
│   ├── privacy-policy/index.html
│   ├── cookie-policy/index.html
│   ├── advertiser-terms/index.html
│   └── publisher-terms/index.html
├── login/index.html
├── signup/index.html
├── build.mjs                         ← Page generator (source of truth)
├── serve.mjs                         ← Local dev server
└── logo_MCV_network.png
```

### 3.3 Cách sử dụng

```bash
# Preview local
node serve.mjs
# → http://localhost:8080

# Regenerate pages (sau khi edit content trong build.mjs)
node build.mjs

# Deploy (static hosting)
# Upload toàn bộ folder lên Vercel / Cloudflare Pages / Netlify
```

### 3.4 Subdomains (chưa build — Phase tiếp theo)

| Subdomain | Mục đích | Status |
|-----------|----------|--------|
| `www.mcv.network` | Marketing site (48 pages) | ✅ DONE |
| `ads.mcv.network` | Self-serve advertiser platform (SPA) | 🔲 TODO |
| `docs.mcv.network` | Developer documentation (SDK docs) | 🔲 TODO |
| `api.mcv.network` | API endpoints | 🔲 TODO |
| `cdn.mcv.network` | SDK & creative delivery | 🔲 TODO |
| `status.mcv.network` | System status page | 🔲 TODO |

---

## 4. AD SDK — TECHNICAL SPECIFICATION

### 4.1 Tổng quan

| Component | Technology | Size |
|-----------|-----------|------|
| **Web SDK** | JavaScript/TypeScript | < 8KB gzip |
| **Android SDK** | Kotlin | < 2MB |
| **iOS SDK** | Swift | < 2MB |
| **Latency** | Ad request → render | < 200ms (p95) |

### 4.2 Ad Formats hỗ trợ

| Format | Web | Mobile | eCPM Range |
|--------|-----|--------|-----------|
| Native Widget (kiểu Taboola) | ✅ | ✅ | $1.5-5 |
| In-Feed | ✅ | ✅ | $1-3 |
| Display Banner (IAB sizes) | ✅ | ✅ | $0.5-2 |
| Commerce Card (shoppable) | ✅ | ✅ | $2-8 |
| Video Outstream | ✅ | ✅ | $5-15 |
| Interstitial | ✅ | ✅ | $8-20 |
| Rewarded Video | ❌ | ✅ | $15-25 |
| Sticky Footer | ✅ | ❌ | $0.5-1.5 |

### 4.3 Airtalk/TagMobile Integration

| Placement | Format | eCPM dự kiến |
|-----------|--------|-------------|
| Home feed | Native card | $8-12 |
| Data usage screen | Banner 320x50 | $5-8 |
| Rewards center | Rewarded video | $15-25 |
| After call ends | Interstitial | $10-18 |
| Push notification | Rich notification | $3-5 |

**Rewards-for-Ads Model:**
- User xem video → nhận 50MB data bonus (max 3 lần/ngày)
- User hoàn thành survey → nhận 10 phút gọi miễn phí
- Tất cả rewards là bonus — không ảnh hưởng dịch vụ cơ bản (FCC compliant)

### 4.4 Infrastructure ước tính

| Phase | Monthly Cost | Ad Requests/day |
|-------|-------------|----------------|
| Phase 1 (MVP) | ~$14,000 | 2M-20M |
| Phase 2 (Scale) | ~$25,000 | 20M-50M |
| Phase 3 (Full) | ~$45,000 | 100M+ |

### 4.5 File tham khảo

- `ad_sdk_technical_spec.md` (33KB) — Full technical specification

---

## 5. BUSINESS MODEL & TÀI CHÍNH

### 5.1 Revenue Streams

| Stream | % Revenue (Năm 3) | Mô tả |
|--------|-------------------|--------|
| CPC/CPM Ads | 49% | Nhà quảng cáo trả theo click/hiển thị |
| Lifeline In-App Ads | 30% | US audience eCPM $5-15 |
| Commerce/Affiliate | 12% | Hoa hồng bán hàng qua link |
| App Install Ads | 5% | Cross-promote giữa apps |
| Data/Audience Licensing | 4% | Bán insights cho brands |

### 5.2 Dự kiến tài chính (3 năm)

| Chỉ số | Năm 1 | Năm 2 | Năm 3 |
|--------|--------|--------|--------|
| DAU (web + app) | 2,000,000 | 5,000,000 | 12,000,000 |
| Impressions/ngày | 6,000,000 | 20,000,000 | 60,000,000 |
| Fill Rate | 40% | 60% | 75% |
| eCPM (avg) | $1.20 | $2.00 | $3.50 |
| **Doanh thu/năm** | **$1.05M** | **$8.76M** | **$57.5M** |
| Chi phí vận hành | $600K | $3.5M | $18M |
| Gross Margin | 43% | 60% | 69% |
| **EBITDA** | **$450K** | **$5.26M** | **$39.5M** |

### 5.3 Pricing Strategy

| Model | Rate | Best for |
|-------|------|----------|
| CPM | $0.50 - $3.00 | Brand awareness |
| CPC | $0.05 - $0.50 | Traffic, engagement |
| CPA | $1 - $50 | Performance (e-commerce, lead gen) |
| CPI | $0.50 - $5.00 | App install campaigns |

---

## 6. GO-TO-MARKET

### 6.1 Phasing

| Phase | Timeline | Focus | Revenue Source |
|-------|----------|-------|---------------|
| 1 — Foundation | Tháng 1-3 | SDK, tracking, AdSense/AdMob | Programmatic |
| 2 — Launch | Tháng 3-6 | Self-serve platform, Airtalk integration | Programmatic + direct |
| 3 — AI & Performance | Tháng 6-12 | ML targeting, RTB, commerce | Direct + performance |
| 4 — Scale | Tháng 12-24 | Enterprise sales, open network | All streams |

### 6.2 Target Advertisers

| Giai đoạn | Đối tượng | Value Proposition |
|-----------|-----------|-------------------|
| Phase 1 | Programmatic (AdX, Prebid) | Monetize ngay, không cần sales |
| Phase 2 | SMB (VN, SEA) | Giá rẻ hơn Google/Meta 30-50% |
| Phase 3 | E-commerce, App developers | Performance CPA/ROAS |
| Phase 4 | Enterprise (US healthcare/fintech) | Unique Lifeline audience data |

### 6.3 Đội ngũ (Phase 1)

| Vai trò | Số người | Chi phí/tháng |
|---------|----------|--------------|
| Backend Engineer (Go/Rust) | 2-3 | $4-6K |
| Frontend Engineer | 2 | $3-4K |
| Mobile Engineer | 2 | $3-4K |
| Data Engineer | 1 | $2-3K |
| Product Manager | 1 | $2-3K |
| Sales/BD | 2 | $3-4K |
| **Tổng** | **10-11** | **$17-24K** |

---

## 7. RỦI RO & COMPLIANCE

### 7.1 Rủi ro chính

| Rủi ro | Mức độ | Giải pháp |
|--------|--------|-----------|
| Traffic chất lượng thấp → eCPM thấp | 🔴 Cao | SEO, content quality, fraud filtering |
| FCC Lifeline compliance | 🔴 Cao | Ads không ảnh hưởng dịch vụ cơ bản |
| Advertiser không tin mạng mới | 🟡 TB | Bắt đầu programmatic → build track record |
| Privacy (GDPR/CCPA) | 🟡 TB | Contextual + first-party only |
| Google/Apple policy changes | 🟡 TB | Server-side tracking, contextual AI |

### 7.2 Compliance checklist

- [x] GDPR — Consent management (TCF 2.2)
- [x] CCPA — Opt-out mechanism
- [x] COPPA — Children-directed app detection
- [x] FCC Lifeline — Rewards = bonus only, no service impact
- [x] Brand Safety — Content classification AI, IVT < 2%
- [x] MRC Viewability — 50% pixels visible for 1s standard

---

## 8. LỘ TRÌNH TIẾP THEO

### Đã hoàn thành ✅

- [x] Phân tích chiến lược (mô hình Taboola)
- [x] Business Plan & Financial Model
- [x] Brand Kit (Logo, Colors, Typography, Voice)
- [x] Sitemap & Domain Plan
- [x] Technical Spec — Ad SDK (Web + Mobile)
- [x] Website prototype (48 pages, build system, design system)

### Cần làm tiếp 🔲

- [ ] **ads.mcv.network** — Self-serve advertiser dashboard (React SPA)
- [ ] **docs.mcv.network** — Developer documentation (Docusaurus)
- [ ] **Ad SDK development** — Web JS SDK → Android SDK → iOS SDK
- [ ] **Backend** — Ad Decision Engine, Event Pipeline, RTB
- [ ] **ML Pipeline** — CTR prediction model, lookalike audiences
- [ ] **Airtalk integration** — SDK embed, rewards system, FCC review
- [ ] **Sales** — Programmatic demand setup (AdX, Prebid, Amazon TAM)
- [ ] **Legal** — Advertiser ToS, DPA, FCC filing review
- [ ] **Pitch Deck** — Investor/stakeholder presentation

---

## 9. FILES INDEX

| File | Size | Mô tả |
|------|------|--------|
| `README.md` | 1.7KB | Hướng dẫn project |
| `build.mjs` | 51.7KB | Page generator (source of truth) |
| `serve.mjs` | 1.2KB | Local dev server |
| `assets/css/mcv.css` | 20.8KB | Design system CSS |
| `assets/js/mcv.js` | 4.9KB | Shared nav + footer |
| `index.html` | 9.5KB | Homepage |
| `ad_sdk_technical_spec.md` | 33.2KB | Technical Spec chi tiết |
| `ad_platform_sitemap.md` | 15.1KB | Sitemap document |
| `mcv_brand_kit.html` | 30.6KB | Brand Kit interactive |
| `mcv_homepage.html` | 25.2KB | Homepage draft (với Highcharts) |
| `sitemap.html` | 23.7KB | Visual sitemap page |
| `logo_MCV_network.png` | 552KB | Logo chính thức |
| **48 page files** | ~100KB | Toàn bộ marketing pages |
| **Tổng** | **~880KB** | **Không tính mcv_website/ backup** |

---

*Tài liệu này là living document — cập nhật khi có tiến triển mới.*  
*Last updated: 29/06/2026 by PhucNguyen @ Softel Solutions*
