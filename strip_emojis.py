import os
import re

# Simple regex to match most common emoji ranges
emoji_pattern = re.compile(r'[\U00010000-\U0010ffff]', flags=re.UNICODE)
# Also include common symbol emojis like 📝 🏦 etc in BMP
emoji_pattern_bmp = re.compile(r'[\u2600-\u27BF\u2300-\u23FF\u2B50\u2B55]', flags=re.UNICODE)
# Some specific mojibake like ðŸ“‚
mojibake = 'ðŸ“‚'

directory = 'c:/Users/pc/Downloads/client mgmt2'

for filename in os.listdir(directory):
    if filename.endswith('.php'):
        filepath = os.path.join(directory, filename)
        with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()
        
        new_content = emoji_pattern.sub('', content)
        new_content = emoji_pattern_bmp.sub('', new_content)
        new_content = new_content.replace(mojibake, '')
        
        if new_content != content:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(new_content)
            print(f'Stripped emojis from {filename}')

