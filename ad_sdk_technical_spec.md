# 📋 Technical Specification: Ad SDK (Web + Mobile)

**Version:** 1.0.0  
**Ngày:** 29/06/2026  
**Author:** Softel Solutions — Ad Platform Team  
**Status:** Draft  

---

## 1. Tổng quan

### 1.1 Mục tiêu

Xây dựng bộ SDK nhẹ, hiệu suất cao để:
- **Web SDK (JavaScript)**: Tích hợp vào hàng nghìn website, hiển thị quảng cáo native/display/commerce
- **Mobile SDK (Android/iOS)**: Tích hợp vào hệ thống app (bao gồm Airtalk/TagMobile), hiển thị in-app ads
- **Thu thập data**: User behavior tracking, event streaming, cross-device identity

### 1.2 Yêu cầu phi chức năng

| Yêu cầu | Mục tiêu |
|----------|----------|
| Kích thước SDK (Web loader) | < 8KB gzipped |
| Kích thước Web format module | Lazy-load theo format, mục tiêu < 25KB gzip/module |
| Kích thước SDK (Mobile) | < 2MB |
| Latency (ad request → render) | < 200ms (p95) |
| Crash rate | < 0.01% |
| Battery impact (Mobile) | < 2% daily drain |
| Viewability tracking | IAB MRC compliant |
| Privacy | GDPR, CCPA, COPPA compliant |
| Offline support (Mobile) | Cache last 3 ad creatives |

---

## 2. Kiến trúc Tổng thể

```
┌─────────────────────────────────────────────────────────────────────┐
│                         CLIENT LAYER                                 │
│                                                                     │
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────────────────┐ │
│  │  Web SDK    │    │ Android SDK │    │       iOS SDK           │ │
│  │  (JS/TS)   │    │  (Kotlin)   │    │      (Swift)            │ │
│  └──────┬──────┘    └──────┬──────┘    └───────────┬─────────────┘ │
│         │                  │                       │               │
│         └──────────────────┼───────────────────────┘               │
│                            │                                       │
└────────────────────────────┼───────────────────────────────────────┘
                             │ HTTPS JSON / Beacon / Pixel
                             ▼
┌─────────────────────────────────────────────────────────────────────┐
│                         DELIVERY EDGE                                │
│                                                                     │
│  ┌──────────────┐  ┌──────────────┐  ┌────────────────────────────┐ │
│  │ CDN assets   │  │ /v1/ad       │  │ /v1/event                  │ │
│  │ SDK/creative │  │ Ad delivery  │  │ Tracking ingest            │ │
│  └──────┬───────┘  └──────┬───────┘  └────────────┬───────────────┘ │
└─────────┼──────────────────┼───────────────────────┼────────────────┘
          │                  │                       │
          ▼                  ▼                       ▼
┌─────────────────┐  ┌────────────────────────────┐  ┌──────────────┐
│  CONTROL PLANE  │  │  MVP DELIVERY API          │  │  EVENT QUEUE │
│  Laravel        │  │  Laravel first, replaceable│  │  Redis queue │
│                 │  │                            │  │  + batch log │
│  • Campaigns    │  │  • Config lookup           │  └──────┬───────┘
│  • Creatives    │  │  • Targeting rules         │         │
│  • Publishers   │  │  • Frequency caps          │         ▼
│  • Billing      │  │  • Pacing cache            │  ┌──────────────┐
│  • Reports      │  │  • Creative response       │  │  ANALYTICS   │
│  • Consent/audit│  └────────────┬───────────────┘  │  Postgres    │
└────────┬────────┘               │                  │  + ClickHouse│
         │                        ▼                  │  phase 2     │
         └──────────────┬────────────────────────────┴──────────────┘
                        ▼
              PostgreSQL + Redis + Object Storage
```

### 2.1 Nguyên tắc MVP

- **Laravel là control plane + MVP API**: quản trị campaign, publisher, creative, billing, reporting, consent/audit và các API nội bộ.
- **JS SDK tách khỏi Laravel app**: SDK được build bằng TypeScript, phát qua CDN, có versioning riêng và không phụ thuộc deploy cycle của portal.
- **Ad delivery có boundary riêng**: `/v1/ad`, `/v1/event`, `/v1/config` bắt đầu bằng Laravel route/service nhưng contract phải ổn định để sau này tách sang Go/Node service nếu traffic vượt ngưỡng.
- **Event pipeline tăng dần**: MVP dùng Redis Queue + batch log/object storage; ClickHouse thêm khi dashboard/reporting bắt đầu cần truy vấn event lớn. Kafka/Flink chỉ dành cho scale phase.
- **Không fingerprint mặc định**: ưu tiên contextual targeting, first-party ID có consent, và server-side consent enforcement.

---

## 3. Web SDK — Specification

### 3.1 Integration (2 dòng code)

```html
<!-- Async loader — non-blocking, ~5KB gzip -->
<script async src="https://cdn.softelads.com/sdk/v1/softel-ads.min.js"
        data-site-id="SITE_12345"
        data-format="native-widget">
</script>

<!-- Ad placement container -->
<div id="softel-ad-unit-1" 
     data-placement="below-article"
     data-format="content-recommendation"
     data-count="6">
</div>
```

### 3.2 Module Architecture

```
softel-ads.min.js (Entry — 5KB gzip)
├── core/
│   ├── loader.ts          — Async initialization, config fetch
│   ├── identity.ts        — First-party ID, consent-gated only
│   ├── context.ts         — Page URL, category, keywords extraction
│   └── consent.ts         — CMP integration (TCF 2.2, USP)
├── ads/
│   ├── request.ts         — Ad request builder, batching
│   ├── renderer.ts        — Template engine, lazy-load images
│   └── viewability.ts     — IntersectionObserver, MRC compliance
├── tracking/
│   ├── events.ts          — Impression, click, conversion pixels
│   ├── beacon.ts          — sendBeacon / pixel fallback
│   └── error.ts           — Error reporting, SDK health
└── formats/
    ├── native-widget.ts   — Content recommendation (Taboola-style)
    ├── display-banner.ts  — Standard IAB sizes (300x250, 728x90, etc.)
    ├── commerce-card.ts   — Product cards with price/CTA
    ├── in-feed.ts         — Blended in-content ads
    └── interstitial.ts    — Full-page overlay (exit intent)
```

> MVP note: `softel-ads.min.js` chỉ là loader/core. Các format như `native-widget`,
> `display-banner`, `commerce-card` được lazy-load từ CDN theo placement config.
> Header bidding/Prebid không nằm trong core SDK; nếu cần sẽ triển khai như adapter
> tùy chọn ở Phase 2.

### 3.3 Core APIs

#### 3.3.1 Initialization

```javascript
// Auto-init via data attributes (recommended)
// OR manual initialization:
SoftelAds.init({
  siteId: 'SITE_12345',
  consent: {
    gdpr: true,          // Enable GDPR consent check
    ccpa: true,          // Enable CCPA opt-out
    cmpApi: 'iab-tcf',  // IAB TCF 2.2 integration
  },
  targeting: {
    category: 'technology',   // Page category
    keywords: ['smartphone', 'review'],
    customParams: { author: 'john' }
  },
  performance: {
    lazyLoad: true,           // Load ads when near viewport
    lazyOffset: 200,          // px before viewport to trigger
    batchRequests: true,      // Combine multiple ad requests
    prefetch: false,          // Don't prefetch on page load
  }
});
```

#### 3.3.2 Ad Request & Rendering

```javascript
// Declarative (data attributes — auto-detected)
// <div data-softel-ad data-placement="sidebar" data-format="display" data-size="300x250"></div>

// Programmatic (for SPAs / dynamic content)
const adUnit = SoftelAds.createAdUnit({
  containerId: 'my-ad-container',
  placement: 'in-feed-3',
  format: 'native-widget',
  count: 4,
  style: {
    theme: 'light',        // 'light' | 'dark' | 'auto'
    columns: 2,
    imageRatio: '16:9',
    showBranding: true,    // "Sponsored" label
  },
  callbacks: {
    onLoad: (ads) => console.log(`${ads.length} ads loaded`),
    onImpression: (ad) => analytics.track('ad_impression', ad),
    onClick: (ad) => analytics.track('ad_click', ad),
    onError: (err) => console.error(err),
    onEmpty: () => fallbackContent.show(),
  }
});

// Refresh ad unit (for infinite scroll / SPA navigation)
adUnit.refresh();

// Destroy (cleanup)
adUnit.destroy();
```

#### 3.3.3 Event Tracking API

```javascript
// Conversion tracking (advertiser pixel)
SoftelAds.trackConversion({
  eventName: 'purchase',
  value: 49.99,
  currency: 'USD',
  orderId: 'ORD-12345',
  items: [{ sku: 'PROD-001', qty: 1 }]
});

// Custom event (behavior tracking)
SoftelAds.trackEvent('article_scroll_50', {
  articleId: 'art-789',
  timeOnPage: 45
});
```

### 3.4 Ad Request Protocol

```
POST https://adx.softelads.com/v1/ad
Content-Type: application/json

{
  "version": "1.0",
  "site_id": "SITE_12345",
  "page": {
    "url": "https://example.com/tech/review-phone",
    "referrer": "https://google.com",
    "category": ["technology", "mobile"],
    "keywords": ["smartphone", "review", "2026"],
    "language": "en"
  },
  "user": {
    "id": "sfp_abc123def456",           // First-party ID (hashed)
    "consent": { "gdpr": 1, "tcf": "CPxyz...", "usp": "1YNN" },
    "geo": { "country": "US", "region": "CA", "city": "LA" },
    "device": {
      "type": "mobile",
      "os": "iOS",
      "browser": "Safari",
      "screen": { "w": 390, "h": 844 },
      "connection": "4g"
    }
  },
  "placements": [
    {
      "id": "below-article-1",
      "format": "native-widget",
      "count": 6,
      "floor_cpm": 0.50,
      "size": { "w": 360, "h": "auto" },
      "viewability_required": true
    }
  ],
  "context": {
    "time_on_page": 32,
    "scroll_depth": 0.75,
    "session_pageviews": 3
  }
}
```

#### Response:

```json
{
  "request_id": "req_abc123",
  "placements": [{
    "id": "below-article-1",
    "ads": [
      {
        "ad_id": "ad_001",
        "campaign_id": "camp_xyz",
        "format": "native",
        "creative": {
          "title": "Top 10 điện thoại 2026",
          "description": "So sánh chi tiết các flagship mới nhất",
          "image": "https://cdn.softelads.com/creatives/img_001.webp",
          "icon": "https://cdn.softelads.com/creatives/icon_001.webp",
          "cta": "Xem ngay",
          "sponsored_by": "TechReview",
          "landing_url": "https://tracking.softelads.com/click/ad_001?..."
        },
        "tracking": {
          "impression": ["https://adx.softelads.com/imp/ad_001?..."],
          "viewable": ["https://adx.softelads.com/view/ad_001?..."],
          "click": ["https://adx.softelads.com/clk/ad_001?..."]
        },
        "bid_price": 1.25
      }
      // ... more ads
    ]
  }],
  "server_time_ms": 42
}
```

### 3.5 Ad Formats (Web)

| Format | Mô tả | Kích thước | Use Case |
|--------|--------|-----------|----------|
| `native-widget` | Grid cards kiểu Taboola | Responsive, 2-4 cols | Below article, sidebar |
| `in-feed` | Blended với content | Match parent width | Giữa danh sách bài viết |
| `display-banner` | IAB standard banners | 300x250, 728x90, 320x50 | Header, sidebar, sticky |
| `commerce-card` | Product card + price/CTA | 300x400, responsive | E-commerce pages |
| `video-outstream` | Auto-play muted video | 16:9, max 640px | In-content |
| `interstitial` | Full-page overlay | 100vw x 100vh | Exit intent, page transition |
| `sticky-footer` | Bottom sticky banner | 320x50 / 728x90 | All pages (mobile/desktop) |

---

## 4. Mobile SDK — Specification

### 4.1 Android SDK (Kotlin)

#### 4.1.1 Integration (Gradle)

```kotlin
// build.gradle (app)
dependencies {
    implementation 'com.softelads:sdk-android:1.0.0'
    implementation 'com.softelads:sdk-android-native:1.0.0'    // Native ads
    implementation 'com.softelads:sdk-android-video:1.0.0'     // Video ads (optional)
}
```

#### 4.1.2 Initialization

```kotlin
// Application class
class MyApp : Application() {
    override fun onCreate() {
        super.onCreate()
        
        SoftelAds.initialize(this) {
            appId("APP_67890")
            environment(Environment.PRODUCTION)
            consent {
                gdpr(enabled = true)
                ccpa(enabled = true)
                coppa(enabled = false)  // true for children-directed apps
            }
            targeting {
                userSegment("lifeline_user")           // Airtalk segment
                customParam("plan", "lifeline_basic")
                customParam("state", "CA")
            }
            performance {
                prefetchAds(count = 3)
                cacheExpiry(minutes = 30)
                networkTimeout(seconds = 5)
            }
            logging(LogLevel.WARN)
        }
    }
}
```

#### 4.1.3 Native Ad (Airtalk Integration Example)

```kotlin
class MainActivity : AppCompatActivity() {
    
    private lateinit var nativeAdLoader: NativeAdLoader
    
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        
        // Load native ad
        nativeAdLoader = SoftelAds.createNativeAdLoader(
            adUnitId = "ADUNIT_airtalk_home_feed",
            config = NativeAdConfig(
                mediaAspectRatio = AspectRatio.RATIO_16_9,
                preferredChoicesPosition = AdChoicesPosition.TOP_RIGHT,
                returnUrlsForImageAssets = false,
            )
        )
        
        nativeAdLoader.load(
            request = AdRequest.Builder()
                .setPlacement("home_feed")
                .setContext(mapOf(
                    "screen" to "home",
                    "user_tenure_days" to "90"
                ))
                .build(),
                
            listener = object : NativeAdListener {
                override fun onAdLoaded(ad: NativeAd) {
                    renderNativeAd(ad)
                }
                override fun onAdFailed(error: AdError) {
                    Log.w("SoftelAds", "Failed: ${error.message}")
                    showFallbackContent()
                }
                override fun onAdImpression(ad: NativeAd) {
                    analytics.logEvent("ad_impression", ad.metadata)
                }
                override fun onAdClicked(ad: NativeAd) {
                    analytics.logEvent("ad_click", ad.metadata)
                }
            }
        )
    }
    
    private fun renderNativeAd(ad: NativeAd) {
        val adView = layoutInflater.inflate(
            R.layout.native_ad_layout, null
        ) as NativeAdView
        
        adView.apply {
            headlineView = findViewById(R.id.ad_headline)
            bodyView = findViewById(R.id.ad_body)
            mediaView = findViewById(R.id.ad_media)
            callToActionView = findViewById(R.id.ad_cta)
            iconView = findViewById(R.id.ad_icon)
            advertiserView = findViewById(R.id.ad_advertiser)
        }
        
        // Bind ad data
        (adView.headlineView as TextView).text = ad.headline
        (adView.bodyView as TextView).text = ad.body
        (adView.callToActionView as Button).text = ad.callToAction
        adView.mediaView?.setMediaContent(ad.mediaContent)
        
        // Register for interaction tracking
        adView.setNativeAd(ad)
        
        // Add to layout
        adContainer.removeAllViews()
        adContainer.addView(adView)
    }
}
```

#### 4.1.4 Rewarded Ad (Lifeline Rewards Model)

```kotlin
// Rewarded ad — user watches ad, earns rewards (extra data, points)
class RewardsActivity : AppCompatActivity() {
    
    private var rewardedAd: RewardedAd? = null
    
    fun loadRewardedAd() {
        SoftelAds.loadRewarded(
            adUnitId = "ADUNIT_airtalk_rewards",
            request = AdRequest.Builder()
                .setPlacement("rewards_center")
                .setRewardType("data_mb")   // Reward type: data MB
                .setRewardAmount(100)        // 100MB bonus data
                .build(),
            listener = object : RewardedAdListener {
                override fun onAdLoaded(ad: RewardedAd) {
                    rewardedAd = ad
                    showWatchAdButton()
                }
                override fun onAdFailed(error: AdError) {
                    hideWatchAdButton()
                }
            }
        )
    }
    
    fun showRewardedAd() {
        rewardedAd?.show(this, object : OnUserEarnedRewardListener {
            override fun onReward(reward: RewardItem) {
                // Grant reward to Lifeline user
                userAccountApi.addDataBonus(
                    userId = currentUser.id,
                    amountMB = reward.amount,
                    source = "ad_reward",
                    adId = reward.adId
                )
                showRewardToast("🎉 Bạn nhận được ${reward.amount}MB data!")
            }
        })
    }
}
```

### 4.2 iOS SDK (Swift)

#### 4.2.1 Integration (CocoaPods / SPM)

```swift
// Podfile
pod 'SoftelAds', '~> 1.0.0'
pod 'SoftelAds/Native', '~> 1.0.0'
pod 'SoftelAds/Video', '~> 1.0.0'

// OR Swift Package Manager
// https://github.com/softelads/ios-sdk.git
```

#### 4.2.2 Initialization

```swift
// AppDelegate.swift
import SoftelAds

@main
class AppDelegate: UIResponder, UIApplicationDelegate {
    
    func application(_ application: UIApplication,
                     didFinishLaunchingWithOptions launchOptions: [UIApplication.LaunchOptionsKey: Any]?) -> Bool {
        
        SoftelAds.configure(
            appId: "APP_67890",
            config: .init(
                environment: .production,
                consent: ConsentConfig(
                    gdpr: true,
                    ccpa: true,
                    attPrompt: true  // iOS ATT prompt
                ),
                targeting: TargetingConfig(
                    userSegment: "lifeline_user",
                    customParams: [
                        "plan": "lifeline_basic",
                        "state": "CA"
                    ]
                ),
                performance: PerformanceConfig(
                    prefetchCount: 3,
                    cacheExpiry: .minutes(30),
                    timeout: .seconds(5)
                )
            )
        )
        
        return true
    }
}
```

#### 4.2.3 SwiftUI Native Ad

```swift
import SoftelAds
import SwiftUI

struct HomeView: View {
    @StateObject private var adLoader = NativeAdLoader(
        adUnitId: "ADUNIT_airtalk_home_feed"
    )
    
    var body: some View {
        ScrollView {
            LazyVStack(spacing: 16) {
                // Regular content
                ForEach(articles) { article in
                    ArticleRow(article: article)
                }
                
                // Native Ad (blended in feed)
                if let ad = adLoader.currentAd {
                    NativeAdView(ad: ad)
                        .frame(height: 280)
                        .cornerRadius(12)
                        .onAppear { adLoader.trackImpression() }
                }
            }
        }
        .onAppear { adLoader.load() }
    }
}

struct NativeAdView: View {
    let ad: NativeAd
    
    var body: some View {
        VStack(alignment: .leading, spacing: 8) {
            // Media
            AsyncImage(url: ad.mediaURL) { image in
                image.resizable().aspectRatio(16/9, contentMode: .fill)
            } placeholder: { ProgressView() }
            .clipped()
            
            VStack(alignment: .leading, spacing: 4) {
                // Sponsored label
                Text("Sponsored • \(ad.advertiser)")
                    .font(.caption)
                    .foregroundColor(.secondary)
                
                // Headline
                Text(ad.headline)
                    .font(.headline)
                    .lineLimit(2)
                
                // Body
                Text(ad.body)
                    .font(.subheadline)
                    .foregroundColor(.secondary)
                    .lineLimit(2)
                
                // CTA Button
                Button(ad.callToAction) {
                    adLoader.handleClick(ad: ad)
                }
                .buttonStyle(.borderedProminent)
                .font(.subheadline.bold())
            }
            .padding(.horizontal, 12)
            .padding(.bottom, 12)
        }
        .background(Color(.systemBackground))
        .cornerRadius(12)
        .shadow(radius: 2)
    }
}
```

---

## 5. Tracking & Analytics

### 5.1 Event Schema

| Event | Trigger | Data Points |
|-------|---------|-------------|
| `ad_request` | SDK requests ad | placement, format, user_id, context |
| `ad_fill` | Server returns ad | ad_id, campaign_id, bid_price, latency_ms |
| `ad_no_fill` | No ad available | placement, reason |
| `ad_render` | Ad rendered in DOM/View | ad_id, render_time_ms |
| `ad_impression` | 50% pixels visible for 1s (MRC) | ad_id, viewability_pct, time_visible |
| `ad_viewable` | 100% viewable confirmed | ad_id, duration_visible |
| `ad_click` | User taps/clicks ad | ad_id, click_position, time_to_click |
| `ad_close` | User dismisses ad | ad_id, reason |
| `ad_conversion` | Post-click action | ad_id, event_name, value, order_id |
| `ad_error` | SDK error occurred | error_code, message, stack_trace |

### 5.2 Viewability Implementation

```javascript
// Web: IntersectionObserver-based viewability
class ViewabilityTracker {
    private observer: IntersectionObserver;
    private timers: Map<string, number> = new Map();
    
    constructor() {
        this.observer = new IntersectionObserver(
            this.handleIntersection.bind(this),
            {
                threshold: [0, 0.5, 1.0],  // 0%, 50%, 100% visible
                rootMargin: '0px'
            }
        );
    }
    
    track(element: HTMLElement, adId: string) {
        element.dataset.adId = adId;
        this.observer.observe(element);
    }
    
    private handleIntersection(entries: IntersectionObserverEntry[]) {
        entries.forEach(entry => {
            const adId = (entry.target as HTMLElement).dataset.adId!;
            
            if (entry.intersectionRatio >= 0.5) {
                // Start timer — MRC requires 50% visible for 1 second
                if (!this.timers.has(adId)) {
                    this.timers.set(adId, window.setTimeout(() => {
                        this.fireImpression(adId);
                    }, 1000));
                }
            } else {
                // Clear timer if no longer visible
                const timer = this.timers.get(adId);
                if (timer) {
                    clearTimeout(timer);
                    this.timers.delete(adId);
                }
            }
        });
    }
    
    private fireImpression(adId: string) {
        EventTracker.send('ad_impression', { ad_id: adId, viewable: true });
    }
}
```

### 5.3 Cross-Device Identity (Privacy-First)

```
┌──────────────────────────────────────────────────────┐
│              USER ID GRAPH                            │
│                                                      │
│  First-Party ID (sfp_xxx)                            │
│  ├── Web first-party ID (consented, publisher scope) │
│  ├── App MAID (GAID/IDFA, if consented)              │
│  ├── Lifeline account hash (for Airtalk users)       │
│  └── Probabilistic matching (IP + UA + behavior)     │
│                                                      │
│  Privacy Rules:                                      │
│  • No raw PII stored in ad system                    │
│  • Lifeline data anonymized (k-anonymity ≥ 50)       │
│  • User can opt-out via SDK API                      │
│  • Data retention: 90 days (events), 365 days (ID)   │
└──────────────────────────────────────────────────────┘
```

---

## 6. Airtalk/TagMobile — Đặc biệt

### 6.1 Ad Placements trong Airtalk App

| Placement ID | Vị trí | Format | eCPM dự kiến |
|--------------|--------|--------|-------------|
| `airtalk_home_feed` | Home screen feed | Native card | $8-12 |
| `airtalk_usage_banner` | Data usage screen | Banner 320x50 | $5-8 |
| `airtalk_rewards_video` | Rewards center | Rewarded video | $15-25 |
| `airtalk_call_end` | After call ends | Interstitial | $10-18 |
| `airtalk_notification` | Push notification ad | Rich notification | $3-5 |
| `airtalk_settings_native` | Settings/Account page | Native small | $4-7 |

### 6.2 Rewards-for-Ads System

```kotlin
// Configuration for Lifeline rewards
data class RewardConfig(
    val rewardType: RewardType,    // DATA_MB, MINUTES, LOYALTY_POINTS
    val amount: Int,               // Quantity of reward
    val frequency: Frequency,      // How often user can earn
    val dailyCap: Int,             // Max rewards per day
    val cooldown: Duration,        // Time between reward opportunities
)

enum class RewardType {
    DATA_MB,          // Bonus data (e.g., 50MB per video watched)
    MINUTES,          // Bonus talk minutes
    LOYALTY_POINTS,   // Points redeemable for account credit
    ACCOUNT_CREDIT,   // Direct $ credit (e.g., $0.25)
}

// Reward tiers
val rewardTiers = listOf(
    RewardConfig(DATA_MB, 50, EVERY_4_HOURS, dailyCap = 3, cooldown = 4.hours),
    RewardConfig(MINUTES, 10, EVERY_6_HOURS, dailyCap = 2, cooldown = 6.hours),
    RewardConfig(LOYALTY_POINTS, 100, EVERY_2_HOURS, dailyCap = 5, cooldown = 2.hours),
)
```

### 6.3 Advertiser Verticals cho Lifeline Audience

| Vertical | Budget Range | KPIs | Targeting |
|----------|-------------|------|-----------|
| Healthcare (Medicaid, Medicare) | $10-50 CPL | Enrollments | Age 55+, state eligibility |
| Financial Services (prepaid, micro-loan) | $5-30 CPA | Sign-ups | Income-qualified, credit-building |
| Government Programs (SNAP, WIC) | $3-15 CPL | Applications | Household size, income level |
| Retail (Dollar Tree, Walmart) | $1-5 CPC | Store visits | Geo-proximity, shopping behavior |
| Education (GED, community college) | $8-25 CPL | Enrollments | Age 18-45, non-degree holders |
| Insurance (ACA, auto, life) | $15-60 CPL | Quote requests | State, age, family status |

---

## 7. Anti-Fraud & Brand Safety

### 7.1 Fraud Detection Pipeline

```
Ad Request → ┬─ Bot Detection (User-Agent, behavior patterns)
             ├─ IP Intelligence (datacenter, proxy, VPN filtering)
             ├─ Click Velocity (rate limiting per user/IP)
             ├─ Device Integrity (SafetyNet/App Attest)
             ├─ Viewability Validation (in-view verification)
             └─ ML Anomaly Model (real-time scoring)
                    │
                    ▼
            Score < 0.3 → Block (no charge to advertiser)
            Score 0.3-0.7 → Flag for review
            Score > 0.7 → Valid traffic (billable)
```

### 7.2 Brand Safety

```javascript
// Content classification (run server-side on publisher pages)
const brandSafetyConfig = {
  categories_blocked: [
    'adult', 'gambling', 'weapons', 
    'hate_speech', 'illegal_drugs', 'violence'
  ],
  keyword_blocklist: [...],
  
  // Advertiser-specific settings
  advertiser_controls: {
    allow_news: true,
    allow_ugc: false,
    min_domain_authority: 30,
    geo_restrictions: ['US', 'CA'],
  }
};
```

---

## 8. Deployment & Infrastructure

### 8.1 SDK Distribution

| Platform | Distribution | Hosting |
|----------|-------------|---------|
| Web SDK | CDN (CloudFront/Cloudflare) | `cdn.softelads.com/sdk/v1/` |
| Android | Maven Central / Private Maven | `com.softelads:sdk-android` |
| iOS | CocoaPods + Swift Package Manager | GitHub Releases |
| React Native | npm package (wrapper) | `@softelads/react-native-sdk` |
| Flutter | pub.dev package | `softel_ads_flutter` |

### 8.2 Backend Infrastructure

```
Phase 1 MVP: single-region, Laravel-first

├── CDN
│   ├── JS SDK files: /sdk/v1/softel-ads.min.js
│   ├── Lazy-loaded format modules
│   └── Creative assets: images/video thumbnails
│
├── Laravel Application
│   ├── Control plane: advertisers, publishers, campaigns
│   ├── Creative approval and asset metadata
│   ├── MVP API: /v1/config, /v1/ad, /v1/event
│   ├── Admin dashboard: Filament
│   ├── Auth/RBAC: Laravel Sanctum or Passport
│   ├── Queue workers: Redis + Supervisor
│   └── Scheduler: pacing jobs, report aggregation, cleanup
│
├── Data Stores
│   ├── PostgreSQL/MySQL: campaign config, billing, accounts
│   ├── Redis: cache, rate limits, frequency caps, queues
│   ├── Object storage: creatives, SDK builds, raw event batches
│   └── ClickHouse: optional Phase 2 analytics warehouse
│
├── Observability
│   ├── Sentry: SDK and Laravel errors
│   ├── Laravel logs + structured request logs
│   └── Uptime checks: /health and synthetic ad request
│
└── Deployment
    ├── Nginx + PHP-FPM
    ├── Horizon or Supervisor for queues
    ├── GitHub Actions CI/CD
    └── Blue-green or atomic symlink release once traffic grows
```

### 8.3 Service Boundaries

| Boundary | MVP implementation | Scale replacement |
|----------|--------------------|-------------------|
| Control plane | Laravel monolith | Keep Laravel; split read models if needed |
| `/v1/config` | Laravel cached endpoint | CDN edge cache / config service |
| `/v1/ad` | Laravel service + Redis cache | Go/Node ad server behind same contract |
| `/v1/event` | Laravel ingest → Redis Queue | Dedicated ingest service + Kafka/PubSub |
| Reporting | SQL aggregates | ClickHouse dashboards |
| Pacing/frequency cap | Redis + scheduler | Dedicated pacing service |
| Fraud checks | Rules in Laravel jobs | Streaming scoring service |

The public SDK contract must not expose Laravel-specific assumptions. SDK calls
stable HTTP endpoints only, so delivery services can be replaced without changing
publisher integrations.

### 8.4 Estimated Monthly Infrastructure Cost

| Component | Specs | Cost/tháng |
|-----------|-------|-----------|
| VPS / app server | 4-8 vCPU, 8-16GB RAM | $80-250 |
| Managed PostgreSQL/MySQL | Small production instance | $100-300 |
| Redis | Managed small instance or same VPC | $50-200 |
| Object storage | Creatives + raw event batches | $20-100 |
| CDN | SDK + creative delivery | $50-300 |
| Error monitoring/logs | Sentry/log provider starter tier | $0-150 |
| **Tổng MVP** | | **~$300-1,300/tháng** |
| **Phase 2 analytics** | Add ClickHouse + larger Redis/CDN | **~$1,500-5,000/tháng** |
| **Scale phase** | Dedicated ad server + event streaming | **TBD by real traffic** |

---

## 9. SDK Release Plan

### 9.1 Milestones

| Milestone | Timeline | Deliverables |
|-----------|----------|-------------|
| **M1 — Control Plane Alpha** | Tuần 1-3 | Laravel app, auth/RBAC, advertiser/publisher/campaign/creative CRUD, asset upload |
| **M2 — Web SDK Alpha** | Tuần 3-5 | JS loader, native-widget module, `/v1/config`, `/v1/ad`, impression/click tracking |
| **M3 — MVP Reporting** | Tuần 5-7 | Event ingest, Redis Queue, daily aggregates, basic campaign dashboard |
| **M4 — Publisher Beta** | Tuần 7-9 | Site approval, placement tags, SDK versioning, CDN release flow, QA tooling |
| **M5 — Production Hardening** | Tuần 9-12 | Consent enforcement, rate limits, fraud rules, Sentry/logging, load test, CI/CD |
| **M6 — Scale Readiness** | Sau MVP | ClickHouse, dedicated ad server, Prebid/AdX adapter, mobile SDK track |

> Implementation detail: see `LARAVEL_MVP_ROADMAP.md` for the day-by-day MVP
> execution plan, including today's auth, account wallet, and top-up scope.

### 9.2 KPIs theo giai đoạn

| Giai đoạn | Ad Requests/day | Fill Rate | eCPM | Revenue/month |
|-----------|----------------|-----------|------|---------------|
| Alpha (10 sites) | 100K | 30% | $0.80 | $720 |
| Beta (100 sites) | 2M | 50% | $1.20 | $36K |
| Production (1000 sites + apps) | 20M | 60% | $2.00 | $720K |
| Scale (full network + Airtalk) | 100M+ | 70% | $3.50 | $7.35M |

---

## 10. Security & Compliance

### 10.1 Data Protection

- **Encryption**: TLS 1.3 (transit), AES-256 (at rest)
- **PII Handling**: No raw PII in ad systems; Lifeline data hashed with SHA-256 + salt
- **Data Residency**: US data stays in US-East-1; SEA data in AP-Southeast-1
- **Retention**: Events 90 days hot → 1yr cold (S3 Glacier); User IDs 365 days
- **DSAR Support**: API endpoint for user data deletion requests (within 30 days)

### 10.2 FCC Compliance (Lifeline)

> ⚠️ **Critical**: Lifeline program có quy định FCC nghiêm ngặt. Quảng cáo trong Airtalk app phải:
> - Không ảnh hưởng đến chức năng cốt lõi (calling, messaging, data)
> - Không bắt buộc user xem ad để sử dụng dịch vụ cơ bản
> - Rõ ràng label "Advertisement" / "Sponsored"
> - Rewards phải là bonus, không phải quyền lợi cơ bản
> - Tuân thủ ACP program rules

### 10.3 SDK Security

- Code obfuscation (ProGuard/R8 cho Android, bitcode cho iOS)
- Certificate pinning cho API calls
- Runtime integrity checks (root/jailbreak detection)
- Anti-tampering (checksum validation)
- Secure storage cho user preferences (KeyStore/Keychain)

---

## 11. Appendix

### 11.1 Glossary

| Term | Definition |
|------|-----------|
| **eCPM** | Effective Cost Per Mille (revenue per 1000 impressions) |
| **Fill Rate** | % ad requests that return an ad |
| **TAC** | Traffic Acquisition Cost |
| **MRC** | Media Rating Council (viewability standard) |
| **MAID** | Mobile Advertising ID (GAID/IDFA) |
| **RTB** | Real-Time Bidding |
| **SSP** | Supply-Side Platform |
| **DSP** | Demand-Side Platform |
| **CMP** | Consent Management Platform |
| **IVT** | Invalid Traffic (bot/fraud) |

### 11.2 Phụ thuộc bên thứ ba

| Dependency | Purpose | Có thể thay thế bằng |
|------------|---------|----------------------|
| Laravel | Control plane + MVP API | Keep as control plane when ad delivery is split |
| Filament | Admin dashboard | Custom Laravel UI / Nova |
| Redis | Cache, queue, frequency caps | Managed Redis / KeyDB |
| PostgreSQL/MySQL | Campaign, billing, publisher data | Managed SQL provider |
| S3-compatible storage | Creative assets, raw event batches | Local disk for dev only |
| TypeScript + esbuild/Rollup | Web SDK build | Vite library mode |
| Sentry | SDK/backend error reporting | Bugsnag / OpenTelemetry stack |
| ClickHouse | Phase 2 analytics DB | BigQuery / Postgres aggregates for MVP |
| Google Ad Manager / Prebid | Optional demand integration | Direct-sold campaigns first |

---

*Tài liệu này là living document — sẽ được cập nhật khi triển khai.*
