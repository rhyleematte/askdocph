import sys
import PyPDF2
import json

def main():
    if len(sys.argv) < 2:
        print(json.dumps({"success": False, "error": "No file path provided"}))
        sys.exit(1)

    filepath = sys.argv[1]
    
    try:
        pdf_file = open(filepath, 'rb')
        reader = PyPDF2.PdfReader(pdf_file)
        
        text = ""
        for i in range(min(3, len(reader.pages))):
            page = reader.pages[i]
            extracted = page.extract_text()
            if extracted:
                text += extracted + "\n"
        
        text = " ".join(text.split())
        
        print(json.dumps({"success": True, "text": text[:1500]}))
        pdf_file.close()

    except Exception as e:
        print(json.dumps({"success": False, "error": str(e)}))

if __name__ == "__main__":
    main()
