# EasyCommerce – Agentic AI Ecommerce & Online Store Builder

> This repository is the public, human-readable source of the EasyCommerce WordPress plugin. The distributed plugin (WordPress.org / installable zip) is a compiled build of this source — see [Development Setup](#development-setup) to build it yourself.

[![License](https://img.shields.io/badge/license-GPLv2-blue.svg)](LICENSE.txt) [![PHP Compatibility](https://img.shields.io/badge/PHP-%3E=7.4-blue.svg)]() [![WordPress Compatibility](https://img.shields.io/badge/WordPress-%3E=6.0-blue.svg)]()

Transform your WordPress site into an **AI-powered** sales machine with **EasyCommerce**.

This isn't just another ecommerce plugin - it's your intelligent store assistant. An AI shopping agent handles sales conversations on your storefront around the clock, a Store Copilot runs your store from the admin, and a full suite of generative AI tools handles content, images, and analytics. All included — no third-party subscriptions required.

[![Watch the video](https://img.youtube.com/vi/QZuWC4yq0zs/maxresdefault.jpg)](https://www.youtube.com/watch?v=QZuWC4yq0zs)

## 🔗 Quick Links
- [Live Demo](https://tastewp.com/recipe/easycommerce) – Experience EasyCommerce instantly. No signup required. 🚀
- [AI Features](https://easycommerce.dev/features/ai) – See how AI automates your store management
- [Complete Feature List](https://easycommerce.dev/features) – Explore everything EasyCommerce offers
- [Add-ons Marketplace](https://easycommerce.dev/addons) – Extend your store with one-click integrations and gateways
- [Pricing](https://easycommerce.dev/pricing) – Core free, simple Pro upgrade
- [Compare](https://easycommerce.dev/compare) – EasyCommerce vs [WooCommerce](https://easycommerce.dev/compare/woocommerce), [EDD](https://easycommerce.dev/compare/easy-digital-downloads), [FluentCart](https://easycommerce.dev/compare/fluentcart), [SureCart](https://easycommerce.dev/compare/surecart)
- [Documentation](https://easycommerce.dev/docs/) – Step-by-step tutorials
- [Developer Docs](https://easycommerce.dev/docs/dev/) – Build on EasyCommerce: REST API, hooks, models, payment gateways, addons
- [Support Center](https://support.easycommerce.dev/) – Get expert help when you need it

## 🤖 AI Features That Save Time & Money

EasyCommerce is the **only WordPress ecommerce plugin** with built-in artificial intelligence. Stop paying for multiple AI subscriptions - it's all included.

---

### 🛒 [Agentic AI](https://easycommerce.dev/features/ai/agentic) – Your AI Sales & Store Management Team

The most powerful AI in EasyCommerce. Two autonomous agents work together: one sells on your storefront 24/7, the other manages your store from the admin panel.

#### [Shopping Agent (Storefront Chatbot)](https://easycommerce.dev/features/ai/agentic/shopping-agent)

A conversational AI agent embedded on your storefront. Customers interact in natural language and can complete the entire purchase journey — from product discovery to checkout — without leaving the chat.

**Product Discovery**
- Search by keyword, color, size, or price range
- Browse by category or brand
- Get full product details with all variants
- Read verified customer reviews and ratings

**Pre-purchase**
- Get an order total estimate before committing (subtotal + shipping + coupon)
- Validate coupon codes against all rules (expiry, minimum spend, product restrictions)
- Calculate shipping cost for a delivery address

**Order Placement**
- Collects customer details conversationally, then creates a pending order with one or more products in a single transaction
- Auto-creates a WordPress account for new customers
- Generates a payment link immediately after order creation and delivers it in-chat

**Post-purchase Self-service** *(verified by email ownership)*
- Check order status and track fulfillment
- View full price breakdown and individual line items
- Apply a coupon to an unpaid order
- Update delivery address or contact information
- Add delivery notes (e.g. "leave at the door")
- Cancel pending or on-hold orders
- Resend order confirmation email
- View refund details and amounts

**Example:** A visitor types "Do you have red sneakers in size 10 under $80?" → Agent searches products, filters by color and size, shows matching variants with prices → Customer says "Order 2 pairs, ship to 123 Main St, New York" → Agent collects remaining details, confirms total, creates the order, and sends a payment link — all in one conversation.

#### [Store Copilot (Dashboard Assistant)](https://easycommerce.dev/features/ai/agentic/store-copilot)

An AI assistant available on every EasyCommerce admin page. Ask questions, get data, and take action — all in natural language.

**Analytics & Reporting**
- Revenue, orders, refunds, and net sales for any date range
- Top-selling products by units sold and revenue
- Complex cross-period comparisons and multi-table aggregations via natural language SQL

**Order Management**
- List and filter orders by status; get full order details (customer, items, totals)
- Update order status — fires email notifications automatically
- Issue refunds — validates refundable balance, updates order status, sends refund email

**Catalog Management**
- Browse and search products
- Create new products with price, stock, SKU, description, and categories
- Update product title, status, short description, or price
- Update stock quantities per variant; delete products

**Coupon Management**
- Create percentage or fixed-amount coupons
- Restrict to specific products; set minimum/maximum spend, start date, and expiry date

**Customer Lookup**
- Profile, lifetime spend, and recent order history by email

**Example:** "Create a 20% off coupon called SUMMER20, minimum spend $50, expires December 31" → Copilot confirms details and creates the coupon in one step.

**Example:** "What were my top 5 products last month and how do they compare to this month?" → Copilot queries the database, runs the comparison, and returns a ranked table with units sold and revenue — no spreadsheets needed.

---

### 🔍 [AI Smart Search](https://easycommerce.dev/features/ai/agentic/smart-search)
Intelligent product search that understands customer intent even with typos and misspellings.

[![Watch the video](https://img.youtube.com/vi/E8xWgn0f5-o/maxresdefault.jpg)](https://www.youtube.com/watch?v=E8xWgn0f5-o)

- "ifon" → Shows iPhone products
- "apple's phone" → Shows iPhone products
- "wirless hedphones" → Shows wireless headphones

While WooCommerce requires exact matches and expensive search extensions ($79-199/year), EasyCommerce includes intelligent search built-in.

### 📊 [AI Business Analyst](https://easycommerce.dev/features/ai/agentic/store-copilot)
Get instant answers to business questions in natural language.

[![Watch the video](https://img.youtube.com/vi/W3wPVeWIgEo/maxresdefault.jpg)](https://www.youtube.com/watch?v=W3wPVeWIgEo)

- "Why did sales drop this week?"
- "What are my top 5 products this month?"
- "Show customers who haven't purchased in 90 days."
- **Saves you:** $50-500/month on business intelligence tools

---

### ✍️ [AI Content Writer](https://easycommerce.dev/features/ai/generative/writer)
Generate professional product descriptions and summaries automatically based on your title and custom prompts.

[![Watch the video](https://img.youtube.com/vi/F9xy8pdaVzs/maxresdefault.jpg)](https://www.youtube.com/watch?v=F9xy8pdaVzs)

- Short summaries: 20-40 words (perfect for catalog pages)
- Long descriptions: 600-900 words (detailed product pages)
- SEO-optimized, persuasive copy that converts
- **Saves you:** $29-80/month on Jasper AI, Copy.ai, or hiring copywriters

**Example:** Enter "Wireless Noise-Cancelling Headphones" with prompt "Emphasize comfort for long flights, 30-hour battery" → AI generates complete product description in seconds.

### 🎨 [AI Image Generator](https://easycommerce.dev/features/ai/generative/image-generator)
Create stunning, professional product images from text descriptions.

[![Watch the video](https://img.youtube.com/vi/_6e0yqXaVUE/maxresdefault.jpg)](https://www.youtube.com/watch?v=_6e0yqXaVUE)

- Perfect for digital products without a physical form
- Concept visualization
- Marketing graphics and social media posts
- **Saves you:** $10-30/month on Midjourney/DALL-E subscriptions

**Example:** "Mountain Adventure Ebook" with prompt "Hiker on mountain peak at sunrise" → AI generates custom cover image instantly.

### ✨ [AI Image Editor](https://easycommerce.dev/features/ai/generative/image-editor)
Professional image editing with simple text commands.

[![Watch the video](https://img.youtube.com/vi/Lx_NlDa-MyU/maxresdefault.jpg)](https://www.youtube.com/watch?v=Lx_NlDa-MyU)

- Background removal (studio-quality product photos)
- Image enhancement (lighting, clarity, color)
- Style transformation and object editing
- **Saves you:** $9-29/month on removal.bg, Canva Pro, or Photoshop

Turn amateur product photos into studio-grade images without design skills.

### 🪟 [AI Template Builder](https://easycommerce.dev/features/ai/generative/template-generator)
Create complete store layouts or product page designs in seconds with AI-generated templates that match your brand perfectly.

> Want the agent on Messenger and WhatsApp too? See the [Multi-Channel Agent](https://easycommerce.dev/features/ai/agentic/multi-channel). Plus [Luna](https://easycommerce.dev/features/ai/agentic/luna), the always-free in-dashboard help assistant.

## 🚀 [Why Choose EasyCommerce Over WooCommerce](https://easycommerce.dev/compare/woocommerce)

After 15+ years, WooCommerce has become bloated and extension-dependent. Here's why store owners are switching to EasyCommerce:

| Feature | WooCommerce | EasyCommerce |
|---------|-------------|--------------|
| **Performance** | Uses WordPress post tables - slows down with large catalogs | Dedicated database tables - 3-5x faster queries, scales effortlessly to 10,000+ products |
| **AI Shopping Agent** | No conversational agent — customers self-serve or abandon | Built-in AI chatbot handles product discovery, order placement, and post-purchase support 24/7 |
| **AI Store Copilot** | No AI management tools | Store Copilot answers store questions, creates products/coupons, issues refunds, updates orders, and runs analytics queries in natural language |
| **AI Automation** | No AI features - requires external tools ($30-100/month each) | 8+ AI features built-in with 100 free AI credits per month |
| **Cost** | Subscriptions extension ($199/year) + License Manager ($129/year) + AI tools ($50-100/month) = $728-1,528/year | Core free + Optional Pro for subscriptions/licenses |
| **Setup** | 10-15 plugins needed for full functionality | All-in-one - everything included or simple addons |
| **User Experience** | Complex settings across multiple plugins, legacy interface | Modern, unified dashboard designed for 2025 |
| **Transaction Fees** | Zero platform fees | Zero platform fees - you keep 100% of revenue |

## 🎯 Who Is EasyCommerce For?

### [Digital Product Creators](https://easycommerce.dev/use-cases/digital-products)
Course creators, authors, designers, and software developers selling ebooks, templates, PDFs, videos, music, and downloadable files benefit from:

- ✓ Secure digital file delivery with download limits
- ✓ AI-generated product descriptions and images
- ✓ Software license management (Pro addon)
- ✓ No per-transaction fees eating into margins

### [Subscription Businesses](https://easycommerce.dev/features/ecommerce/subscriptions)
SaaS founders, membership sites, coaching programs, and online communities selling recurring access to digital content, software licenses, courses, or any subscription-based business model.

- ✓ Flexible billing intervals - weekly, monthly, quarterly, yearly
- ✓ Automatic recurring payments via Stripe and PayPal
- ✓ Customer subscription dashboard for self-service
- ✓ Pause or cancel subscriptions
- ✓ Subscription expiration management
- ✓ Email notifications for renewals, failures, cancellations

### [Software Licensing Businesses](https://easycommerce.dev/features/ecommerce/license-manager)
WordPress plugin/theme developers, software vendors, SaaS products, and any digital product requiring activation control and license management.

- ✓ Automatic license key generation
- ✓ Set activation limits per license
- ✓ Track license usage and activations
- ✓ Automatic renewal handling for subscriptions
- ✓ License expiration controls
- ✓ Remote deactivation management
- ✓ API for license validation

### [Physical Product Stores](https://easycommerce.dev/use-cases/physical-products)
Retailers, handmade goods sellers, and ecommerce stores managing inventory appreciate:

- ✓ Product variations (size, color, material)
- ✓ Inventory tracking and stock alerts
- ✓ AI product photography (eliminates expensive photoshoots)
- ✓ Shipping calculators and tax management
- ✓ Fast page loads even with 10,000+ products

### [WordPress Agencies](https://easycommerce.dev/use-cases/agencies)
Web developers building client stores choose EasyCommerce for:

- ✓ One plugin instead of 10+ extension stacks
- ✓ Clean codebase with hooks and filters
- ✓ Clients love the AI features
- ✓ Faster websites = happier clients
- ✓ Predictable, affordable pricing

## ⭐ Complete Feature List

### 🤖 [Agentic AI](https://easycommerce.dev/features/ai/agentic)

- ✓ **[Shopping Agent](https://easycommerce.dev/features/ai/agentic/shopping-agent)** - Conversational storefront chatbot handles product discovery, order placement, and post-purchase support 24/7
- ✓ **[Store Copilot](https://easycommerce.dev/features/ai/agentic/store-copilot)** - Natural language store management: analytics, orders, product creation, price/stock updates, refund issuing, coupon creation, customer lookup, complex SQL queries
- ✓ **[Multi-Channel Agent](https://easycommerce.dev/features/ai/agentic/multi-channel)** - Bring the shopping agent to Facebook Messenger and WhatsApp via addons
- ✓ **[Luna Help Assistant](https://easycommerce.dev/features/ai/agentic/luna)** - Always-free in-dashboard help for store owners
- ✓ **[AI Smart Search](https://easycommerce.dev/features/ai/agentic/smart-search)** - Fuzzy + intent-based search corrects typos and understands natural language queries
- ✓ **[Voice Search](https://easycommerce.dev/features/ai/agentic/voice-search)** - Hands-free product search (coming soon)
- ✓ **[AI Cross-sell](https://easycommerce.dev/features/ai/agentic/product-recommendation)** - AI-powered product recommendations at checkout (coming soon)

### ✨ [Generative AI](https://easycommerce.dev/features/ai/generative)

- ✓ **[AI Content Writer](https://easycommerce.dev/features/ai/generative/writer)** - Product descriptions and summaries from title + prompt
- ✓ **[AI Image Generator](https://easycommerce.dev/features/ai/generative/image-generator)** - Product images from text descriptions
- ✓ **[AI Image Editor](https://easycommerce.dev/features/ai/generative/image-editor)** - Background removal, enhancement, style transforms
- ✓ **[AI Template Builder](https://easycommerce.dev/features/ai/generative/template-generator)** - Complete store layouts and product page designs
- ✓ **[AI Attribute Generator](https://easycommerce.dev/features/ai/generative/attribute-generator)** - Auto-create product options (size, color, material) in one click
- ✓ **[AI Business Analyst](https://easycommerce.dev/features/ai/agentic/store-copilot)** - Natural language business intelligence queries

### Sell Anything

- ✓ **[Digital Products](https://easycommerce.dev/features/ecommerce/digital-delivery)** - PDFs, videos, music, software, ebooks with secure delivery
- ✓ **[Physical Products](https://easycommerce.dev/features/ecommerce/physical-digital)** - Inventory tracking, shipping, variants (size, color, etc.)
- ✓ **[Subscriptions](https://easycommerce.dev/features/ecommerce/subscriptions)** - Recurring billing for memberships, SaaS, coaching (Pro addon)
- ✓ **[Software Licenses](https://easycommerce.dev/features/ecommerce/license-manager)** - Activation keys, renewal management (Pro addon)
- ✓ **[Variable Products](https://easycommerce.dev/features/ecommerce/product-variations)** - Auto-generate all variants with one click

### [Payment Processing](https://easycommerce.dev/features/ecommerce/payments) (Zero Transaction Fees)

- ✓ **Stripe** - Credit cards, Apple Pay, Google Pay (200+ countries, 135+ currencies)
- ✓ **PayPal** - Instant checkout, PayPal Credit
- ✓ **Mollie** - European payment methods
- ✓ **Braintree** - Enterprise-grade processing, PayPal company
- ✓ **Square** - Credit cards, partial refunds
- ✓ **Local Gateways** - bKash, Nagad, and regional payment methods (via [addons](https://easycommerce.dev/addons))
- ✓ **No Platform Fees** - Keep 100% of revenue (only standard processor fees apply)

### Marketing & Conversion

- ✓ **[AI Shopping Agent](https://easycommerce.dev/features/ai/agentic/shopping-agent)** - Converts visitors through conversation, not just browse-and-click
- ✓ **[Abandoned Cart Recovery](https://easycommerce.dev/features/ecommerce/abandoned-carts)** - Automated reminder emails with personalization
- ✓ **[Coupons & Discounts](https://easycommerce.dev/features/ecommerce/coupons)** - Percentage, fixed amount, product-specific
- ✓ **[Email Automation](https://easycommerce.dev/features/ecommerce/emails)** - Order confirmations, shipping updates with dynamic placeholders
- ✓ **[Cross-Sell & Upsell](https://easycommerce.dev/features/ecommerce/cross-sell-upsell)** - Strategic suggestions at product page, cart, and checkout
- ✓ **[Product Recommendations](https://easycommerce.dev/features/ai/agentic/product-recommendation)** - AI-powered suggestions (coming soon)

### Store Management

- ✓ **[Real-Time Dashboard](https://easycommerce.dev/features/ecommerce/dashboard)** - Sales graphs, trending products, low stock alerts
- ✓ **[Inventory Control](https://easycommerce.dev/features/ecommerce/inventory)** - Stock levels per variant with SKU support
- ✓ **[Order Management](https://easycommerce.dev/features/ecommerce/order-management)** - Track status (pending, completed, failed, refunded)
- ✓ **[Customer Profiles](https://easycommerce.dev/features/ecommerce/customer-management)** - Purchase history, lifetime value, internal notes
- ✓ **[Shipping Calculators](https://easycommerce.dev/features/ecommerce/shipping)** - Weight-based, flat rate, regional zones
- ✓ **[Tax Management](https://easycommerce.dev/features/ecommerce/tax)** - Country/region-specific rates, tax-inclusive pricing
- ✓ **[Reports & Analytics](https://easycommerce.dev/features/ecommerce/reports-analytics)** - Overview, orders, revenue, customers, and product reports
- ✓ **[Activity & Audit Log](https://easycommerce.dev/features/ecommerce/audit-log)** - Full history of every significant store action

### Design & Customization

- ✓ **[Gutenberg-Native](https://easycommerce.dev/features/builder/gutenberg-blocks)** - Build product pages with WordPress blocks
- ✓ **[Variation Swatches](https://easycommerce.dev/features/ecommerce/product-variations)** - Color/image/label selectors instead of dropdowns
- ✓ **[Storefront Templates](https://easycommerce.dev/features/builder/storefront)** - Three shop designs with AJAX category, price, and attribute filters
- ✓ **[Page Builder Ready](https://easycommerce.dev/features/builder/page-builders)** - Compatible with Elementor, Beaver Builder, Divi (coming soon)
- ✓ **Responsive Templates** - Mobile-optimized shop and checkout pages
- ✓ **No-Code Product Builder** - Drag-and-drop layout customization

### Performance & Scalability

- ✓ **Dedicated Database Tables** - 3-5x faster than WordPress post-based systems
- ✓ **Optimized Queries** - Handles 10,000+ products without slowdown
- ✓ **Lightweight Architecture** - Minimal bloat vs 15-plugin WooCommerce stacks
- ✓ **Cache-Friendly** - Works with all major caching plugins
- ✓ **[Security First](https://easycommerce.dev/features/ecommerce/security)** - Nonce verification, capability checks, input sanitization, output escaping

### Developer Features

- ✓ **REST API** - Full programmatic access to products, orders, customers
- ✓ **Hooks & Filters** - Customize any functionality
- ✓ **100+ Action Hooks**
- ✓ **50+ Filter Hooks**
- ✓ **Modern Codebase** - Clean, maintainable PHP

## 💳 [Payment Gateways](https://easycommerce.dev/features/ecommerce/payments) – Simple & Secure

Six gateways are **built into the core plugin** — no separate addon required:
- **PayPal:** Support for 200+ countries and 100+ currencies.
- **Stripe:** Popular gateway with features like partial refunds.
- **Mollie:** Easy setup with quick transaction processing.
- **Braintree:** A PayPal company, global credit card support.
- **Square:** Credit cards, partial refunds.
- **Cash on Delivery:** Simple offline payment option.
- **Regional gateways:** bKash, Nagad, Paddle, Bank Transfer, and more — see the [addons marketplace](https://easycommerce.dev/addons) for the latest list.

*Unlike some platforms (e.g. SureCart's revenue cut), EasyCommerce charges **no extra fees** beyond standard gateway fees.*

## 🔗 Integration Add-Ons

Extend your store with one-click integrations:
- **[WooCommerce Migration](https://easycommerce.dev/addons/easycommerce-migration)** - Import products, orders, customers instantly
- **[HubSpot Sync](https://easycommerce.dev/addons/easycommerce-hubspot)** - Connect customer data for CRM and marketing
- **[Mailchimp](https://easycommerce.dev/addons/easycommerce-mailchimp)** - Email marketing integration
- **[PDF Invoices](https://easycommerce.dev/addons/easycommerce-pdf-invoice)** - Automatic invoice generation
- **[Checkout Editor](https://easycommerce.dev/addons/easycommerce-checkout-editor)** - Customize checkout fields
- **[Facebook Messenger](https://easycommerce.dev/addons/easycommerce-messenger)** - Let the AI shopping agent sell inside Messenger

## 🔄 Migrate From WooCommerce in One Click

Switching to EasyCommerce is easy:

**What Gets Migrated:**
- ✓ All products (simple, variable, digital)
- ✓ Product images and galleries
- ✓ Categories and tags
- ✓ All orders and order history
- ✓ Customer accounts and data
- ✓ Reviews and ratings

**Migration Process:**
1. Install free [Migration addon](https://easycommerce.dev/addons/easycommerce-migration)
2. Click "Start Migration."
3. Wait 5-30 minutes (depending on catalog size)
4. Done - Zero downtime, store stays live during migration

Need help? Pro customers get free white-glove migration assistance from our specialists.

## 📊 [EasyCommerce Dashboard](https://easycommerce.dev/features/ecommerce/dashboard)

Your store's command center provides real-time insights:

- **Colorful Sales Graphs** - Daily, weekly, monthly trends
- **Order Tracking** - Monitor pending, completed, failed, refunded orders
- **Trending Products** - See what customers are buying
- **Low-Stock Alerts** - Never run out of popular items
- **Customer Analytics** - Lifetime value, purchase history
- **AI Business Analyst** - Ask questions, get instant answers

## 📦 [Product Management Made Simple](https://easycommerce.dev/features/ecommerce/product-management)

Managing your catalog is effortless:

- **Quick Add/Edit** - Single-page product creation
- **Auto-Variants** - Define attributes once, auto-generate all combinations
- **AI Content** - Generate descriptions and images automatically
- **Inventory Control** - Stock levels per variant with SKU support
- **No-Code Builder** - Visual product page customization
- **Profit Calculator** - Product cost, profit margin calculation

## 🔄 [Abandoned Cart Recovery](https://easycommerce.dev/features/ecommerce/abandoned-carts)

Win back lost sales with built-in recovery tools:
- Set a **Cart Recovery Timer** (e.g. 1 hour after inactivity) to trigger reminder emails
- Use **Personalized Emails** with customer names and cart items
- Send **Follow-Up Campaigns** directly from the dashboard

## 🏷️ [Coupons & Discounts](https://easycommerce.dev/features/ecommerce/coupons)

Create and manage coupons to boost sales:
- Set fixed-amount or percentage discounts on specific products, categories, or the entire cart
- Restrict by products or expiry date
- Automatically apply rules (e.g. free shipping over $100) for promotions
- **Buy X Get Y** and **Free Shipping** coupon types

## 📋 [Order Management](https://easycommerce.dev/features/ecommerce/order-management)

All your orders in one place – no fuss:
- **Unified Order Screen:** See new, processing, and completed orders in one table. Filter by status.
- **Statuses & Notifications:** Update order and fulfillment status; customers get emailed updates
- **Detailed Order View:** Access billing/shipping info, order notes, and send invoices directly
- **Refunds:** Complete refund functionality with transaction ID support

## 🤖 [AI Agents & Assistant](https://easycommerce.dev/features/ai/agentic)

### [Shopping Agent](https://easycommerce.dev/features/ai/agentic/shopping-agent)
An AI agent embedded on your storefront handles the complete sales journey — from "show me red sneakers in size 10" to a paid order — entirely through conversation. No forms, no page hopping. Available 24/7 without extra staff. Extend it to [Messenger and WhatsApp](https://easycommerce.dev/features/ai/agentic/multi-channel) with addons.

### [Store Copilot](https://easycommerce.dev/features/ai/agentic/store-copilot)
An AI assistant available on every EasyCommerce admin page. Ask about sales trends, look up orders, update stock, and run complex store analytics — all in natural language.

[![Watch the video](https://img.youtube.com/vi/we1Axokjcbo/maxresdefault.jpg)](https://www.youtube.com/watch?v=we1Axokjcbo)

Since **87% of marketers** already use AI for content and support, this tool will keep your shop on the cutting edge.

### AI Credit System

Every plan includes a monthly pool of AI credits that refreshes each month. The free plan gives you **100 credits per month** (200 in your first month); paid plans include a larger monthly allowance - Personal 1,000/mo, Professional 3,000/mo, Agency 8,000/mo. AI credit cost per action:
- AI Shopping Agent: 1 credit per conversation turn
- Store Copilot: 1 credit per conversation turn
- AI Writer: 1 credit per generation
- AI Business Analyst: 2 credits per query
- AI Smart Search: 1 credit per search
- AI Template Builder: 3 credits per generation
- AI Image Generator: 50 credits per image
- AI Image Editor: 50 credits per edit
- AI Background Remover: 50 credits per action

**How conversation turns work:** The AI Shopping Agent and Store Copilot are conversational - to answer one question the AI may take several internal "turns" (each step where it reads your store data, calls a tool, or refines its answer is one turn). You are charged 1 credit per turn, so a single question usually costs 1-10 credits depending on how much work it needs. Simple questions cost less; complex, multi-step requests cost more.

Plan credits refresh monthly and don't roll over. Need more? Upgrade to a higher plan for a larger monthly allowance (top-up credit packs coming soon).

## Getting Started

### Requirements
- **WordPress**: Version 6.0 or higher
- **PHP**: Version 7.4 or higher (PHP 8.0+ recommended)
- **MySQL**: Version 5.6 or higher, OR MariaDB 10.1 or higher

### Installation
1. Log in to your WordPress dashboard
2. Navigate to **Plugins > Add New**
3. Search for "EasyCommerce"
4. Click **Install Now**
5. Click **Activate**
6. Follow the Setup Wizard to configure your store

## 🌟 Why Developers Love EasyCommerce

**Clean Architecture:**
- Modern, maintainable codebase
- Dedicated database tables (not WordPress posts)
- Follows WordPress coding standards
- Well-documented functions and filters

**Extensible:**
- Complete REST API
- 100+ action hooks
- 50+ filter hooks
- Custom endpoint support
- Headless-ready architecture

**Performance:**
- Optimized SQL queries
- Minimal database calls
- Cache-friendly design
- Lazy loading assets
- 3-5x faster than post-based systems

### 📚 Developer Documentation

Full developer guides live at **[easycommerce.dev/docs/dev](https://easycommerce.dev/docs/dev/)**:

- [Introduction & Getting Started](https://easycommerce.dev/docs/dev/#/getting-started/introduction-to-easycommerce-development)
- [Architecture & Plugin Structure](https://easycommerce.dev/docs/dev/#/getting-started/overview-of-easycommerce-architecture)
- [Using the REST API](https://easycommerce.dev/docs/dev/#/rest-api/using-easycommerce-rest-api-endpoints) · [Creating Custom Endpoints](https://easycommerce.dev/docs/dev/#/rest-api/creating-custom-rest-api-endpoints-in-easycommerce)
- [Actions & Filters](https://easycommerce.dev/docs/dev/#/extending/using-actions-and-filters)
- [Database Models & CRUD Helpers](https://easycommerce.dev/docs/dev/#/data-models/using-database-models-and-crud-helpers-in-easycommerce) · [Working with Orders](https://easycommerce.dev/docs/dev/#/data-models/working-with-orders-programmatically)
- [Developing Custom Payment Gateways](https://easycommerce.dev/docs/dev/#/payments/developing-custom-payment-gateways)
- [Creating an Addon](https://easycommerce.dev/docs/dev/#/addons/how-to-create-an-easycommerce-addon) · [Security Best Practices](https://easycommerce.dev/docs/dev/#/addons/security-best-practices-when-developing-easycommerce-addons)
- [Webhooks & Integrations](https://easycommerce.dev/docs/dev/#/integrations/webhook-integration-how-to-send-events-to-external-services)
- [Reports Architecture](https://easycommerce.dev/docs/dev/#/reports/reports-architecture-and-workflow)

## Frequently Asked Questions

### What is EasyCommerce?
EasyCommerce is the only AI-powered WordPress ecommerce plugin that automates content creation, image generation, and business analytics. Sell digital products, physical goods, and subscriptions with built-in AI features, dedicated database architecture for 3-5x faster performance, and zero transaction fees.

### Is EasyCommerce free to use?
Yes. EasyCommerce core is completely free with no transaction fees. The optional Pro version unlocks advanced features like subscription management and license management. You only pay standard payment processor fees (Stripe, PayPal).

### How is EasyCommerce different from WooCommerce?
EasyCommerce has a built-in **AI shopping agent** that sells for you 24/7 — no WooCommerce equivalent exists at any price. It also ships with a Store Copilot, AI smart search, AI content writer, AI image tools, and business analytics (8+ AI features total) that WooCommerce completely lacks. Built with dedicated database tables for 3-5x faster performance. Pro includes subscriptions and license management, while WooCommerce requires $328+ per year in separate extensions. See the full [EasyCommerce vs WooCommerce comparison](https://easycommerce.dev/compare/woocommerce).

### What can the AI Shopping Agent do?
The AI Shopping Agent is a conversational chatbot on your storefront. It helps customers find products (search, browse by category, check stock), quote an order total with shipping and coupon applied, collect their details, place the order, and send a payment link — all through chat. After purchase, customers can check order status, track fulfillment, update their address, cancel a pending order, or request a resend of their confirmation email — all without contacting support.

### What can the Store Copilot do?
The Store Copilot is available on every EasyCommerce admin page. Ask it business questions ("What were my top products last month?"), have it look up a specific order or customer, update order statuses, issue refunds, create new products, update product prices, create discount coupons with restrictions, adjust product stock, or run complex analytics queries across your store data — all in natural language, no SQL or spreadsheets needed.

### Can I sell both digital and physical products?
Yes. EasyCommerce handles digital downloads (PDFs, videos, software, ebooks), physical goods with inventory tracking, subscriptions with recurring billing, and software licenses - all in one plugin without limitations.

### What payment gateways are supported?
Stripe, PayPal, Mollie, Braintree, Square, and regional gateways like bKash and Nagad. Additional payment processors are available through addons. All major payment methods supported: credit cards, Apple Pay, Google Pay, and bank transfers.

### Do you charge transaction fees?
No. Zero platform fees, ever. You keep 100% of your revenue and only pay standard payment processor fees (Stripe ~2.9% + 30¢, PayPal similar). Unlike some competitors, EasyCommerce never takes a percentage of your sales.

### What's included in EasyCommerce Pro?
Pro includes complete Subscription Management (recurring billing, automatic renewals etc), Software License Manager (activation keys, usage tracking), and all future Pro addons. Pro customers also receive free AI credits, priority support and free migration assistance.

### Can I use EasyCommerce for a membership site?
Yes. With the Pro Subscriptions addon, create membership sites with recurring billing, content access control, member dashboards, and subscription management. Perfect for coaching programs, online courses, exclusive communities, and SaaS businesses.

### Can I migrate from WooCommerce?
Absolutely. Our free [Migration addon](https://easycommerce.dev/addons/easycommerce-migration) transfers all products, orders, customers, and reviews in one click with zero downtime. Takes 5-30 minutes depending on catalog size. Pro customers get free white-glove migration assistance.

### Does EasyCommerce work with my WordPress theme?
Yes. EasyCommerce is Gutenberg-native and compatible with all modern WordPress themes. Works seamlessly with page builders like Elementor, Beaver Builder, and Divi.

### Does EasyCommerce support multiple currencies?
Yes. EasyCommerce supports 135+ currencies worldwide through payment gateway integrations. Set your default currency and customers can pay in their preferred currency based on location.

### Is EasyCommerce fast with large product catalogs?
Yes. Built with dedicated database tables instead of WordPress posts (like WooCommerce), EasyCommerce delivers 3-5x faster queries even with 10,000+ products. Performance doesn't degrade as your catalog grows.

### Will EasyCommerce slow down my site?
No. EasyCommerce uses lightweight architecture, dedicated database tables, and optimized queries designed for performance. Much faster than post-based ecommerce systems. Works with all major caching plugins.

### Where can I get support?
Free community support via WordPress.org forums and [Facebook Community](https://www.facebook.com/groups/easycommerce.community/). Pro customers receive priority support through our [support center](https://support.easycommerce.dev/).

## Development Setup

### Tech Stack
- **Frontend**: React, Tailwind CSS
- **Backend**: WordPress PHP APIs, REST API
- **Testing**: PHPUnit, Jest

### Installation for Development
1. Clone the repository:
   ```bash
   git clone https://github.com/codexpertofficial/easycommerce.git
   ```
2. Install PHP dependencies:
   ```bash
   composer install
   ```
3. Install JavaScript dependencies:
   ```bash
   npm install
   ```
4. Build all SPAs, blocks, and assets to `build/`:
   ```bash
   npm run build
   ```
5. Generate the translation .pot file:
   ```bash
   composer run makepot
   ```
6. Build the release package:
   ```bash
   composer run release
   ```

Build tools: Node.js 20+, npm, Composer, and Webpack (configuration in `webpack.config.js`).

> Screenshots and the full feature gallery are on the [WordPress.org plugin page](https://wordpress.org/plugins/easycommerce/) and [easycommerce.dev](https://easycommerce.dev).

## Contributing

This repository is a published mirror of the released EasyCommerce source. To report a bug, request a feature, or get help:

- **Support & bug reports:** [support.easycommerce.dev](https://support.easycommerce.dev/) or the [WordPress.org support forum](https://wordpress.org/support/plugin/easycommerce/)
- **Community:** [Facebook Community](https://www.facebook.com/groups/easycommerce.community/)

### Code Standards
- Follow **WordPress Coding Standards**.
- Use **Tailwind CSS** for styling.
- Write tests for any new features.

## License
EasyCommerce is licensed under the GNU General Public License v2.0 or later. See the [LICENSE](LICENSE.txt) file for more details.

---

Thank you for using EasyCommerce! 🚀
