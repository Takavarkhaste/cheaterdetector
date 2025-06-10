# Cheating Detection System for Exams
This project is a web-based solution for detecting potential cheating among students' exam answers. It combines PHP and Python scripts to collect, store, and analyze student responses, identifying suspicious similarities and abnormal response times.

## Features
- **Web form (PHP):** Collects student answers and stores them in a structured JSON file.
- **Automated analysis (Python):** Compares answers using Levenshtein and Jaccard similarity metrics.
- **Time anomaly detection:** Flags unusually fast responses as potential anomalies.
- **JSON-based workflow:** All data is stored and processed in JSON files for easy integration and review.

## How It Works
1. **Students submit answers** via a web form.
2. **Answers are saved** in `json_answers/answers.json` in a standardized format.
3. **Python script (`algorithms.py`)** analyzes the answers for textual similarity and time anomalies.
4. **Results are saved** in `json_answers/analysis.json`, highlighting suspicious answer pairs and anomalous timings.

## File Structure
- `form.php` — Web form and backend logic for saving answers and running analysis.
- `algorithms.py` — Python script for similarity and anomaly detection.
- `json_answers/answers.json` — Stores all student responses.
- `json_answers/analysis.json` — Stores the analysis results.

## Getting Started

1. Clone the repository.
2. Make sure you have PHP and Python (with `python-Levenshtein` and `numpy` installed).
3. Run the web form (`form.php`) on your local server.
4. Submit answers and view the analysis in `json_answers/analysis.json`.

This project is for educational and research purposes.
