import re
from bs4 import BeautifulSoup
from tabulate import tabulate


def parse_html(file_path):
    with open(file_path, 'r', encoding='utf-8') as file:
        soup = BeautifulSoup(file, 'html.parser')

        # Заголовки h1-h6
        headers = []
        for level in range(1, 7):
            for header in soup.find_all(f'h{level}'):
                headers.append({
                    'Type': f'H{level}',
                    'Content': header.text.strip()
                })

        # Email-адреси
        emails = []
        email_pattern = r'\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b'
        text = soup.get_text()
        for email in re.findall(email_pattern, text):
            emails.append({
                'Type': 'Email',
                'Content': email
            })

        # Гіперпосилання
        links = []
        for link in soup.find_all('a', href=True):
            links.append({
                'Type': 'Link',
                'Content': link['href']
            })

        # Об'єднання всіх результатів
        results = headers + emails + links

        # Вивід таблиці
        print(tabulate(results, headers='keys', tablefmt='grid'))


# Приклад використання
parse_html('index.html')