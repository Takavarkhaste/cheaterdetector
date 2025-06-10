import json
from Levenshtein import distance as levenshtein_distance
from typing import List, Dict
import numpy as np
import sys
from datetime import datetime

# محاسبه شباهت ژاکارد
def jaccard_similarity(set1: set, set2: set) -> float:
    intersection = len(set1.intersection(set2))
    union = len(set1.union(set2))
    return intersection / union if union != 0 else 0.0

# تحلیل شباهت متنی بین دو پاسخ
def analyze_similarity(answer1: str, answer2: str) -> Dict[str, float]:
    # محاسبه فاصله لونشتین
    levenshtein_dist = levenshtein_distance(answer1, answer2)
    max_len = max(len(answer1), len(answer2))
    levenshtein_score = 1 - (levenshtein_dist / max_len) if max_len != 0 else 0.0

    # محاسبه شباهت ژاکارد
    jaccard_score = jaccard_similarity(set(answer1.split()), set(answer2.split()))

    # میانگین دو معیار
    combined_score = (levenshtein_score + jaccard_score) / 2

    return {
        "levenshtein_score": levenshtein_score,
        "jaccard_score": jaccard_score,
        "combined_score": combined_score,
    }

# تحلیل زمان پاسخدهی برای شناسایی تقلب زمانی
def detect_time_anomaly(times: List[float], threshold: float = 0.5) -> List[int]:
    mean_time = np.mean(times)
    anomalies = [i for i, t in enumerate(times) if t < mean_time * threshold]
    return anomalies

# پردازش فایل JSON ورودی و تولید خروجی
def process_exam_data(input_file: str, output_file: str):
    with open(input_file, "r", encoding="utf-8") as f:
        data = json.load(f)

    # ساختار داده برای ذخیره نتایج
    analysis_results = []

    # لیست دانشجویان
    students = list(data.keys())

    # بررسی پاسخهای هر دانشجو با سایر دانشجویان
    for i in range(len(students)):
        for j in range(i + 1, len(students)):
            student1_name = students[i]
            student2_name = students[j]
            student1 = data[student1_name]
            student2 = data[student2_name]
            student1_id = student1_name
            student2_id = student2_name
            matching_segments = []

            # مقایسه پاسخهای هر سوال بین دو دانشجو
            for qnumber in range(1, min(len(student1), len(student2)) + 1):
                answer1 = next((ans["description"] for ans in student1 if ans["qnumber"] == qnumber), None)
                answer2 = next((ans["description"] for ans in student2 if ans["qnumber"] == qnumber), None)

                if answer1 and answer2:
                    similarity_scores = analyze_similarity(str(answer1), str(answer2))
                    matching_segments.append({
                        "question_id": f"q{qnumber}",
                        "segment1": {"text": answer1},
                        "segment2": {"text": answer2},
                        "similarity_percentage": similarity_scores["combined_score"] * 100  # Convert to percentage
                    })

            # محاسبه امتیاز کلی شباهت
            if matching_segments:
                overall_similarity = np.mean([seg["similarity_percentage"] for seg in matching_segments])
            else:
                overall_similarity = 0.0

            # تعیین سطح ریسک
            threshold_settings = {"minimum_similarity": 30, "suspicious_threshold": 70}  # Example values
            if overall_similarity > threshold_settings["suspicious_threshold"]:
                risk_level = "HIGH"
            elif overall_similarity > threshold_settings["minimum_similarity"]:
                risk_level = "MEDIUM"
            else:
                risk_level = "LOW"

            analysis_results.append({
                "student_pair": {
                    "student1_id": student1_id,
                    "student2_id": student2_id
                },
                "similarity_score": overall_similarity / 100,  # Convert to a value between 0 and 1
                "matching_segments": matching_segments,
                "overall_risk_level": risk_level
            })

    # تحلیل زمان پاسخدهی
    all_times = []
    for student_name in students:
        student = data[student_name]
        student_times = [ans["time_taken"] for ans in student]
        all_times.extend(student_times)
    time_anomalies = detect_time_anomaly(all_times)

    # ساختار خروجی نهایی
    output_data = {
        "quiz_id": "example_quiz",
        "timestamp": datetime.now().isoformat(),
        "analysis_results": analysis_results,
        "time_anomalies": time_anomalies,
        "analysis_metadata": {
            "algorithm_version": "v1.0",
            "threshold_settings": {
                "minimum_similarity": 0.3,
                "suspicious_threshold": 0.7
            }
        }
    }

    with open(output_file, "w", encoding="utf-8") as f:
        json.dump(output_data, f, ensure_ascii=False, indent=4)

# اجرای برنامه
import sys
if __name__ == "__main__":
    if len(sys.argv) == 3:
        input_file_path = sys.argv[1]
        output_file_path = sys.argv[2]
    else:
        input_file_path = "json_answers/answers.json"
        output_file_path = "analysis.json"
    process_exam_data(input_file_path, output_file_path)

