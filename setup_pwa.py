import os

# Create manifest.json for root
root_manifest = """{
  "name": "BFS CRM",
  "short_name": "BFS CRM",
  "description": "Premium CRM for Financial Professionals",
  "start_url": "./index.php",
  "display": "standalone",
  "background_color": "#F4F7FE",
  "theme_color": "#0f172a",
  "icons": [
    {
      "src": "https://cdn-icons-png.flaticon.com/512/10311/10311651.png",
      "sizes": "192x192",
      "type": "image/png"
    },
    {
      "src": "https://cdn-icons-png.flaticon.com/512/10311/10311651.png",
      "sizes": "512x512",
      "type": "image/png"
    }
  ]
}"""
with open(r'c:\Users\pc\Downloads\client mgmt2\manifest.json', 'w') as f: f.write(root_manifest)

# Create manifest.json for staff
staff_manifest = """{
  "name": "BFS Staff Portal",
  "short_name": "BFS Staff",
  "description": "Field Visit & Sales Portal",
  "start_url": "./index.php",
  "display": "standalone",
  "background_color": "#F4F7FE",
  "theme_color": "#0f172a",
  "icons": [
    {
      "src": "https://cdn-icons-png.flaticon.com/512/10311/10311651.png",
      "sizes": "192x192",
      "type": "image/png"
    },
    {
      "src": "https://cdn-icons-png.flaticon.com/512/10311/10311651.png",
      "sizes": "512x512",
      "type": "image/png"
    }
  ]
}"""
with open(r'c:\Users\pc\Downloads\client mgmt2\staff\manifest.json', 'w') as f: f.write(staff_manifest)

# Create sw.js for root
sw_js = """const CACHE_NAME = 'bfs-crm-v2';
const ASSETS = [
    './manifest.json',
    'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap',
    'https://unpkg.com/lucide@latest'
];

self.addEventListener('install', (e) => {
    e.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(ASSETS))
    );
});

self.addEventListener('fetch', (e) => {
    e.respondWith(
        fetch(e.request).catch(() => caches.match(e.request))
    );
});"""
with open(r'c:\Users\pc\Downloads\client mgmt2\sw.js', 'w') as f: f.write(sw_js)
with open(r'c:\Users\pc\Downloads\client mgmt2\staff\sw.js', 'w') as f: f.write(sw_js)

# Patch header.php
header_file = r'c:\Users\pc\Downloads\client mgmt2\header.php'
with open(header_file, 'r', encoding='utf-8') as f:
    header = f.read()

pwa_tags = """    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - BFS Financial Services' : 'BFS Financial Services - Client Management System'; ?></title>
    
    <!-- PWA Installable App Configuration -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#0f172a">
    <link rel="apple-touch-icon" href="https://cdn-icons-png.flaticon.com/512/10311/10311651.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="mobile-web-app-capable" content="yes">"""

header = header.replace('    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . \' - BFS Financial Services\' : \'BFS Financial Services - Client Management System\'; ?></title>', pwa_tags)
with open(header_file, 'w', encoding='utf-8') as f: f.write(header)

# Patch footer.php to register SW
footer_file = r'c:\Users\pc\Downloads\client mgmt2\footer.php'
with open(footer_file, 'r', encoding='utf-8') as f:
    footer = f.read()

sw_register = """
    <!-- PWA Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('sw.js')
                    .then(reg => console.log('PWA ServiceWorker registered successfully!'))
                    .catch(err => console.error('PWA ServiceWorker registration failed: ', err));
            });
        }
    </script>
</body>"""

footer = footer.replace('</body>', sw_register)
with open(footer_file, 'w', encoding='utf-8') as f: f.write(footer)

print("PWA functionality installed!")
