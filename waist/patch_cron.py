import os

footer_file = r'c:\Users\pc\Downloads\client mgmt2\footer.php'
with open(footer_file, 'r', encoding='utf-8') as f:
    content = f.read()

cron_snippet = """
    <!-- Background Auto-Cron for Document Reminders -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Trigger the background cron job to send missing document emails
            // The script itself handles the "once-per-day per applicant" logic
            fetch('cron_document_reminders.php')
                .then(res => res.json())
                .then(data => {
                    if(data.success && data.emails_sent > 0) {
                        console.log('Automated doc reminders sent: ' + data.emails_sent);
                    }
                })
                .catch(err => console.error('Auto-cron error:', err));
        });
    </script>
</body>
"""

if 'cron_document_reminders.php' not in content:
    content = content.replace('</body>', cron_snippet)
    with open(footer_file, 'w', encoding='utf-8') as f:
        f.write(content)

print("Footer patched for auto-cron!")
