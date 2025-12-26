import os
import re

root = 'ai-bot-main/resources/views/admin'
patterns = {
    r"__\('admin\.service'\)": "__('admin.service_label')",
    r"__\('admin\.user'\)": "__('admin.user_label')",
    r"__\('admin\.subscription'\)": "__('admin.subscription_label')"
}

for r, d, files in os.walk(root):
    for f in files:
        if f.endswith('.blade.php'):
            path = os.path.join(r, f)
            with open(path, 'r', encoding='utf-8') as file:
                content = file.read()
            
            new_content = content
            for p, repl in patterns.items():
                new_content = re.sub(p, repl, new_content)
            
            if new_content != content:
                with open(path, 'w', encoding='utf-8') as file:
                    file.write(new_content)
                print(f"Updated: {path}")

