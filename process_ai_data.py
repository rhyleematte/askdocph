import os
import json
import PyPDF2

data_dir = r"c:\websystem\AI data\data"
output_file = r"c:\websystem\storage\app\ai_knowledge.json"

chunks = []

if os.path.exists(data_dir):
    for filename in os.listdir(data_dir):
        if filename.endswith('.pdf'):
            filepath = os.path.join(data_dir, filename)
            try:
                print(f"Reading {filename}...")
                pdf_file = open(filepath, 'rb')
                reader = PyPDF2.PdfReader(pdf_file)
                
                # Extract first 3 pages as an introduction/context for each document
                # To avoid blowing up the token limit, we keep it small.
                text = ""
                for i in range(min(3, len(reader.pages))):
                    page = reader.pages[i]
                    extracted = page.extract_text()
                    if extracted:
                        text += extracted + "\n"
                
                # Clean up text
                text = " ".join(text.split())
                if text:
                    chunks.append({
                        "source": filename,
                        "content": text[:1500]  # Cap each chunk to ~300 words
                    })
                pdf_file.close()
            except Exception as e:
                print(f"Failed to read {filename}: {e}")

with open(output_file, 'w', encoding='utf-8') as f:
    json.dump(chunks, f, ensure_ascii=False, indent=4)

print(f"Extracted {len(chunks)} chunks to {output_file}")
