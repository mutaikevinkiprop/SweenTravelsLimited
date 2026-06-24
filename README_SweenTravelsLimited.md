# Sween Travels Limited Website

A responsive website for **Sween Travels Limited**, a Nairobi-based visa, travel, and immigration consultancy. The project contains public-facing pages for visa services, immigration solutions, destination requirements, FAQs, contact enquiries, newsletter signups, SEO metadata, sitemap, favicons, local image assets, and a PHP lead handler for website form submissions.

## Table of contents

- [Project overview](#project-overview)
- [Features](#features)
- [Tech stack](#tech-stack)
- [Folder structure](#folder-structure)
- [Pages included](#pages-included)
- [Forms and lead handling](#forms-and-lead-handling)
- [Environment configuration](#environment-configuration)
- [Local development](#local-development)
- [Deployment notes](#deployment-notes)
- [GitHub Pages warning](#github-pages-warning)
- [SEO files](#seo-files)
- [Image management](#image-management)
- [Security notes](#security-notes)
- [Maintenance guide](#maintenance-guide)
- [Troubleshooting](#troubleshooting)
- [GitHub push guide](#github-push-guide)

## Project overview

This website is built as a lightweight HTML/CSS/JavaScript project with one PHP backend endpoint for forms.

The site is suitable for shared hosting environments such as cPanel/Apache hosting because it includes:

- Static HTML pages.
- Local CSS and JavaScript files.
- Local image assets and favicons.
- Apache `.htaccess` rules for HTTPS redirects, 404 handling, caching, compression, and security headers.
- `send-lead.php` for contact, visa enquiry, and newsletter form submissions.
- `private/` folder for server-side form backup files.

## Features

- Responsive multi-page website.
- Shared header and footer loaded with JavaScript.
- Mobile navigation menu.
- Scroll reveal animations.
- Contact form.
- Visa enquiry forms on visa detail pages.
- Newsletter subscription form.
- PHP form validation.
- Honeypot spam protection.
- Simple IP-based rate limiting.
- Email notification to the business.
- Automatic acknowledgement email to the user.
- Private CSV backup of submitted leads.
- SEO metadata, Open Graph tags, canonical URLs, sitemap, and robots file.
- Local destination, service, team, visa, testimonial, flag, logo, and favicon assets.

## Tech stack

| Area | Technology |
| --- | --- |
| Markup | HTML5 |
| Styling | Local CSS, Tailwind-generated CSS file, custom CSS |
| JavaScript | Vanilla JavaScript |
| Forms backend | PHP |
| Server config | Apache `.htaccess` |
| Assets | JPG, PNG, SVG, ICO, Web App Manifest |

There is no Node.js build step in the current project. The website can run directly from the included files.

## Folder structure

```text
.
├── index.html
├── about.html
├── contact.html
├── visa.html
├── immigration.html
├── faq.html
├── privacy.html
├── 404.html
├── application-checklist.html
├── country-requirements.html
├── visa-guide.html
├── header.html
├── footer.html
├── send-lead.php
├── .htaccess
├── robots.txt
├── sitemap.xml
├── site.webmanifest
├── css/
│   ├── styles.css
│   └── tailwind.css
├── js/
│   └── components.js
├── images/
│   ├── sween-travels-logo.png
│   ├── favicon.ico
│   └── favicon images
├── assets/
│   └── images/
│       ├── about/
│       ├── destinations/
│       ├── flags/
│       ├── hero/
│       ├── services/
│       ├── team/
│       ├── testimonials/
│       └── visa/
├── Visa/
│   ├── business-visa.html
│   ├── family-reunion-visa.html
│   ├── student-visa.html
│   ├── tourist-visa.html
│   ├── visitor-visa.html
│   └── work-visa.html
├── Solutions/
│   ├── citizenship-applications.html
│   ├── family-sponsorship-visa.html
│   ├── permanent-residency-solutions.html
│   ├── relocation-and-settlement-support.html
│   ├── visa-application-assistance.html
│   └── visa-refusal-appeals.html
├── private/
│   └── .htaccess
└── docs/
    └── GITHUB_PUSH_GUIDE.md
```

## Pages included

### Root pages

| File | Purpose |
| --- | --- |
| `index.html` | Homepage and main landing page. |
| `about.html` | Company profile, story, team, values, and trust sections. |
| `contact.html` | Contact information and main enquiry form. |
| `visa.html` | Visa service category landing page. |
| `immigration.html` | Immigration solutions landing page. |
| `faq.html` | Frequently asked questions. |
| `privacy.html` | Privacy policy. |
| `application-checklist.html` | General application checklist guide. |
| `country-requirements.html` | Destination/country requirements guide. |
| `visa-guide.html` | Visa process guidance page. |
| `404.html` | Custom not-found page. |

### Visa pages

| File | Purpose |
| --- | --- |
| `Visa/business-visa.html` | Business visa details and enquiry form. |
| `Visa/family-reunion-visa.html` | Family reunion visa details and enquiry form. |
| `Visa/student-visa.html` | Student visa details and enquiry form. |
| `Visa/tourist-visa.html` | Tourist visa details and enquiry form. |
| `Visa/visitor-visa.html` | Visitor visa details and enquiry form. |
| `Visa/work-visa.html` | Work visa details and enquiry form. |

### Immigration solution pages

| File | Purpose |
| --- | --- |
| `Solutions/citizenship-applications.html` | Citizenship application service page. |
| `Solutions/family-sponsorship-visa.html` | Family sponsorship service page. |
| `Solutions/permanent-residency-solutions.html` | Permanent residency service page. |
| `Solutions/relocation-and-settlement-support.html` | Relocation and settlement support service page. |
| `Solutions/visa-application-assistance.html` | Visa application assistance service page. |
| `Solutions/visa-refusal-appeals.html` | Visa refusal appeal service page. |

## Shared header and footer

The website uses `js/components.js` to load:

- `header.html` into the `#header` element.
- `footer.html` into the `#footer` element.

The script automatically detects whether a page is in the root folder, `Visa/`, or `Solutions/` and adjusts relative paths so shared navigation and assets work correctly.

Because the header and footer are loaded using `fetch()`, the website should be viewed through a local server or hosted server. Opening the HTML files directly with `file://` may prevent the shared header/footer from loading in some browsers.

## Forms and lead handling

The project uses `send-lead.php` as the backend for:

- Contact form submissions.
- Visa enquiry form submissions.
- Newsletter subscriptions.

Supported form names:

```text
contact
visa-enquiry
newsletter
```

The PHP script performs the following actions:

1. Accepts only `POST` requests.
2. Checks the honeypot field named `bot-field`.
3. Applies simple IP-based rate limiting.
4. Validates the submitted form type.
5. Requires privacy policy consent through `privacy-consent=yes`.
6. Validates required fields.
7. Sends an email notification to the business.
8. Sends a confirmation email to the user.
9. Stores a private CSV backup in `private/leads.csv`.
10. Returns JSON for JavaScript submissions or redirects for regular form submissions.

### Contact and visa enquiry fields

Typical fields:

```text
form-name
name
email
phone
subject
message
privacy-consent
bot-field
```

### Newsletter fields

Typical fields:

```text
form-name
email
privacy-consent
bot-field
```

## Environment configuration

`send-lead.php` supports environment variables:

| Variable | Purpose | Default |
| --- | --- | --- |
| `SWEEN_TO_EMAIL` | Email address that receives website leads. | `sweentravelslimited@gmail.com` |
| `SWEEN_FROM_EMAIL` | Sender email used for outgoing website emails. | `no-reply@sweentravels.co.ke` |
| `SWEEN_SITE_NAME` | Name used in email subjects and messages. | `Sween Travels` |

For best deliverability, use a real domain email address for `SWEEN_FROM_EMAIL`, such as:

```text
no-reply@sweentravels.co.ke
```

PHP `mail()` works on many shared hosting accounts, but SMTP through a trusted mail provider is usually more reliable for production.

## Local development

### Option 1: Preview static pages with Python

This previews the HTML/CSS/JS pages, but it will not execute PHP form handling.

```bash
cd SweenTravelsLimited
python -m http.server 8000
```

Open:

```text
http://localhost:8000
```

### Option 2: Preview with PHP built-in server

This is better because it can run `send-lead.php` locally if PHP mail configuration is available.

```bash
cd SweenTravelsLimited
php -S localhost:8000
```

Open:

```text
http://localhost:8000
```

### Important local testing note

Do not test by double-clicking `index.html` directly from your file manager. Use a local server so JavaScript `fetch()` can load `header.html` and `footer.html` correctly.

## Deployment notes

### Recommended production hosting

Use Apache/cPanel hosting with PHP support, such as Truehost/cPanel, because this project includes:

- `.htaccess`
- PHP form handler
- Private server-side lead backup files

Typical deployment path on cPanel:

```text
public_html/
```

Upload the project files into `public_html/` so the homepage is available at:

```text
https://sweentravels.co.ke/
```

### After uploading to hosting

1. Confirm SSL is active.
2. Confirm `.htaccess` is enabled.
3. Confirm `index.html` loads.
4. Confirm the custom `404.html` works.
5. Submit a test contact form.
6. Check that the business email receives the lead.
7. Check that `private/leads.csv` is created on the server.
8. Confirm `private/leads.csv` and `private/rate-limit.json` are not publicly accessible.
9. Submit the sitemap URL to Google Search Console.

## GitHub Pages warning

GitHub Pages is suitable only for static files. It does not execute PHP.

That means the website pages can display on GitHub Pages, but these features will not work there:

- `send-lead.php`
- Contact form email sending
- Visa enquiry form email sending
- Newsletter form email sending
- Private CSV lead backup
- PHP rate limiting

For a working live business site, deploy to PHP hosting or replace the PHP form handler with a third-party form service/API.

## SEO files

| File | Purpose |
| --- | --- |
| `sitemap.xml` | Lists important pages for search engines. |
| `robots.txt` | Allows public crawling and blocks private/backend paths. |
| `site.webmanifest` | Progressive web/app metadata and icons. |
| Meta tags in pages | Page titles, descriptions, canonical URLs, Open Graph, and Twitter previews. |
| JSON-LD blocks | Structured business information for search engines. |

### Updating SEO after a domain or page change

When changing the domain, update references in:

- All canonical URLs.
- Open Graph URLs.
- Twitter image URLs.
- JSON-LD URLs.
- `robots.txt` sitemap URL.
- `sitemap.xml` URLs.
- `site.webmanifest` if needed.

## Image management

Main image folders:

| Folder | Purpose |
| --- | --- |
| `images/` | Logo, favicon, app icons. |
| `assets/images/hero/` | Homepage hero image. |
| `assets/images/about/` | About page images. |
| `assets/images/services/` | Immigration/service images. |
| `assets/images/visa/` | Visa page images. |
| `assets/images/team/` | Team photos. |
| `assets/images/testimonials/` | Testimonial images. |
| `assets/images/destinations/` | Destination cards. |
| `assets/images/flags/` | Country flag SVGs. |

See `assets/images/README.md` for the project image shot-list and replacement guidance.

### Replacing images

To replace an image without editing code:

1. Create the new image.
2. Resize and compress it for web use.
3. Save it using the same filename and path as the existing image.
4. Replace the old file.
5. Refresh the browser cache.

## Security notes

The project includes several security measures:

- HTTPS redirect in `.htaccess`.
- Canonical non-www redirect in `.htaccess`.
- Custom 404 page.
- Security headers:
  - `X-Content-Type-Options: nosniff`
  - `X-Frame-Options: SAMEORIGIN`
  - `Referrer-Policy: strict-origin-when-cross-origin`
  - `Strict-Transport-Security`
- Honeypot field on forms.
- Simple IP-based rate limit in `send-lead.php`.
- Private CSV backup folder.
- `robots.txt` disallow rules for private/backend paths.

### Files that should not be public

Do not commit or expose generated private lead data:

```text
private/leads.csv
private/rate-limit.json
```

These files are ignored in `.gitignore`.

## Maintenance guide

### Add a new page

1. Create a new `.html` file.
2. Include the same CSS links used by similar pages.
3. Add a `#header` and `#footer` mount point if using shared components.
4. Include `js/components.js` at the bottom of the page.
5. Add navigation links in `header.html` or `footer.html` if needed.
6. Add the page to `sitemap.xml`.
7. Add canonical and Open Graph URLs.
8. Test locally through a server.

### Add a new visa page

1. Copy one of the pages in `Visa/`.
2. Rename the file.
3. Update the title, meta description, canonical URL, headings, content, image paths, and form subject options.
4. Link it from `visa.html`, `header.html`, or `footer.html` if needed.
5. Add it to `sitemap.xml`.

### Add a new immigration solution page

1. Copy one of the pages in `Solutions/`.
2. Rename the file.
3. Update the title, meta description, canonical URL, headings, content, image paths, and calls to action.
4. Link it from `immigration.html`, `header.html`, or `footer.html` if needed.
5. Add it to `sitemap.xml`.

### Update contact information

Search and update these values across the project:

```text
sweentravelslimited@gmail.com
+254759187912
(+254) 759 187 912
View Park Towers
sweentravels.co.ke
```

Files that commonly contain contact information:

- `index.html`
- `about.html`
- `contact.html`
- `footer.html`
- `privacy.html`
- `send-lead.php`
- Visa and solution pages
- JSON-LD structured data blocks

## Troubleshooting

### Header or footer does not appear

Likely cause: the page is being opened with `file://` instead of through a local or hosted server.

Fix:

```bash
python -m http.server 8000
```

Then open:

```text
http://localhost:8000
```

### Contact form does not send on GitHub Pages

GitHub Pages does not run PHP. Deploy the site to PHP hosting or use an external form service.

### Contact form sends but email does not arrive

Possible causes:

- Hosting provider has disabled PHP `mail()`.
- Sender email is not allowed by the domain.
- Email is going to spam.
- SPF/DKIM/DMARC are not configured for the domain.
- SMTP is required by the hosting provider.

Recommended fix: switch from PHP `mail()` to authenticated SMTP.

### CSS or images not loading in subfolders

Check that paths use the correct prefix:

- Root pages usually use `css/styles.css`.
- Pages inside `Visa/` or `Solutions/` usually use `../css/styles.css`.

The shared header/footer links are adjusted by `js/components.js` after loading.

### Leads CSV is not being created

Possible causes:

- PHP does not have permission to write to `private/`.
- The `private/` folder was not uploaded.
- Server restrictions are blocking file writes.

Fix:

1. Confirm `private/` exists.
2. Confirm the hosting account can write to it.
3. Submit a test form.
4. Check server error logs.

## GitHub push guide

A detailed step-by-step guide is available in:

```text
docs/GITHUB_PUSH_GUIDE.md
```

Basic push commands:

```bash
cd SweenTravelsLimited
git init
git branch -M main
git add .
git commit -m "Initial commit"
git remote add origin https://github.com/YOUR-USERNAME/YOUR-REPOSITORY.git
git push -u origin main
```

Replace `YOUR-USERNAME` and `YOUR-REPOSITORY` with your real GitHub username and repository name.

## License

No license file is included in this project. Add a `LICENSE` file before making the repository public if you want to clearly define how others may use the code.
