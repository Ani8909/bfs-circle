import re

file_path = r'c:\Users\pc\Downloads\client mgmt2\search_track.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

old_code = """            if(window.lucide) lucide.createIcons();
            
        } catch (error) {"""

new_code = """            if(window.lucide) lucide.createIcons();
            
            // Automatically select the first applicant if this is a fresh search and results exist
            if (reset && applicants.length > 0) {
                // Ensure a small delay so the DOM has time to render the cards before adding 'active' class
                setTimeout(() => {
                    selectApplicantCard(applicants[0].id);
                }, 50);
            }
            
        } catch (error) {"""

content = content.replace(old_code, new_code)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Auto-select logic injected successfully.")
