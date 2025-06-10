<?php
$saveDir = __DIR__ . '/json_answers';
if (!is_dir($saveDir)) {
    mkdir($saveDir, 0755, true);
}

$error = '';
$success = false;
$pythonOutput = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = trim($_POST['studentId'] ?? '');
    $q1 = trim($_POST['q1'] ?? '');
    $q2 = trim($_POST['q2'] ?? '');
    $q3 = trim($_POST['q3'] ?? '');
    $q4 = trim($_POST['q4'] ?? '');
    $q5 = trim($_POST['q5'] ?? '');

    $q1_time = intval($_POST['q1_time'] ?? 0);
    $q2_time = intval($_POST['q2_time'] ?? 0);
    $q3_time = intval($_POST['q3_time'] ?? 0);
    $q4_time = intval($_POST['q4_time'] ?? 0);
    $q5_time = intval($_POST['q5_time'] ?? 0);

    if ($studentId === '' || !ctype_digit($studentId)) {
        $error = 'Please enter a valid Student ID (digits only).';
    } elseif ($q1 === '' || $q2 === '' || $q3 === '' || $q4 === '' || $q5 === '') {
        $error = 'Please answer all questions.';
    } else {
        $answers = [
            [
                "qnumber" => 1,
                "description" => $q1,
                "time_taken" => $q1_time
            ],
            [
                "qnumber" => 2,
                "description" => $q2,
                "time_taken" => $q2_time
            ],
            [
                "qnumber" => 3,
                "description" => $q3,
                "time_taken" => $q3_time
            ],
            [
                "qnumber" => 4,
                "description" => $q4,
                "time_taken" => $q4_time
            ],
            [
                "qnumber" => 5,
                "description" => $q5,
                "time_taken" => $q5_time
            ]
        ];
        $filename = "answers.json";
        $filePath = $saveDir . '/' . $filename;
        $allData = [];
        if (file_exists($filePath)) {
            $allData = json_decode(file_get_contents($filePath), true) ?? [];
        }
        $allData[$studentId] = $answers;
        $jsonString = json_encode($allData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if (file_put_contents($filePath, $jsonString) !== false) {
            $pythonScript = __DIR__ . '/algorithms.py';
            $outputFile = $saveDir . '/analysis.json';
            exec("python " . escapeshellarg($pythonScript) . " " . escapeshellarg($filePath) . " " . escapeshellarg($outputFile) . " 2>&1", $output, $return_var);
            $pythonOutput = implode("\n", $output);
            if ($return_var === 0) {
                $success = true;
            } else {
                $error = 'Error processing answers: ' . $pythonOutput;
            }
        } else {
            $error = 'Error saving JSON file.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Quiz Form</title>
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56e4;
            --secondary: #7209b7;
            --success: #06d6a0;
            --warning: #ffd166;
            --danger: #ef476f;
            --dark: #212529;
            --light: #f8f9fa;
            --gray: #6c757d;
            --border: #dee2e6;
            --shadow: 0 4px 12px rgba(0,0,0,0.08);
            --transition: all 0.3s ease;
        }

        .dark-mode {
            --primary: #4895ef;
            --primary-dark: #3a7bd5;
            --secondary: #b5179e;
            --success: #06d6a0;
            --warning: #ffd166;
            --danger: #f72585;
            --dark: #121212;
            --light: #1e1e1e;
            --gray: #9e9e9e;
            --border: #333;
            --shadow: 0 4px 12px rgba(0,0,0,0.3);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            color: #333;
            line-height: 1.6;
            transition: var(--transition);
            min-height: 100vh;
            padding: 20px;
            background-image: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
        }

        body.dark-mode {
            background-color: #121212;
            background-image: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #e0e0e0;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            margin-bottom: 25px;
            border-bottom: 1px solid var(--border);
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-icon {
            font-size: 28px;
        }

        .theme-toggle {
            background: var(--primary);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 30px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: var(--transition);
            box-shadow: var(--shadow);
        }

        .theme-toggle:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .timer-container {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--primary);
            transition: var(--transition);
        }

        .dark-mode .timer-container {
            background: #1e1e1e;
        }

        .timer-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .timer-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary);
        }

        #countdown {
            font-size: 32px;
            font-weight: 700;
            color: var(--dark);
            text-align: center;
            font-family: 'Courier New', monospace;
            transition: var(--transition);
        }

        .dark-mode #countdown {
            color: white;
        }

        .timer-warning {
            color: var(--danger);
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.6; }
            100% { opacity: 1; }
        }

        .progress-container {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .dark-mode .progress-container {
            background: #1e1e1e;
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .progress-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary);
        }

        .progress-bar {
            height: 20px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 10px;
            transition: var(--transition);
        }

        .dark-mode .progress-bar {
            background: #333;
        }

        .progress-fill {
            height: 100%;
            background: var(--primary);
            border-radius: 10px;
            transition: width 0.5s ease;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 10px;
            color: white;
            font-size: 12px;
            font-weight: bold;
        }

        .progress-text {
            text-align: center;
            font-size: 14px;
            font-weight: 500;
            color: var(--gray);
        }

        .main-box {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border-top: 4px solid var(--primary);
        }

        .dark-mode .main-box {
            background: #1e1e1e;
            border-top: 4px solid var(--primary);
        }

        .login-title {
            font-size: 24px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
            text-align: center;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .student-id-label {
            display: block;
            margin-top: 20px;
            font-size: 16px;
            font-weight: 500;
            color: var(--dark);
        }

        .dark-mode .student-id-label {
            color: #e0e0e0;
        }

        .student-id-input {
            width: 100%;
            padding: 12px 15px;
            font-size: 16px;
            margin-top: 8px;
            border: 1px solid var(--border);
            border-radius: 8px;
            transition: var(--transition);
            background: var(--light);
        }

        .dark-mode .student-id-input {
            background: #2d2d2d;
            border-color: #444;
            color: #e0e0e0;
        }

        .student-id-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        .dark-mode .student-id-input:focus {
            box-shadow: 0 0 0 3px rgba(72, 149, 239, 0.3);
        }

        .question-label {
            margin-top: 25px;
            font-weight: 600;
            font-size: 17px;
            display: block;
            color: var(--dark);
        }

        .dark-mode .question-label {
            color: #e0e0e0;
        }

        .question-input {
            width: 100%;
            margin-top: 10px;
            margin-bottom: 20px;
            font-size: 16px;
            padding: 12px 15px;
            border: 1px solid var(--border);
            border-radius: 8px;
            transition: var(--transition);
            background: var(--light);
        }

        .dark-mode .question-input {
            background: #2d2d2d;
            border-color: #444;
            color: #e0e0e0;
        }

        .question-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        .dark-mode .question-input:focus {
            box-shadow: 0 0 0 3px rgba(72, 149, 239, 0.3);
        }

        .question-textarea {
            width: 100%;
            height: 120px;
            margin-top: 10px;
            margin-bottom: 20px;
            font-size: 16px;
            padding: 12px 15px;
            border: 1px solid var(--border);
            border-radius: 8px;
            resize: vertical;
            transition: var(--transition);
            background: var(--light);
            font-family: inherit;
        }

        .dark-mode .question-textarea {
            background: #2d2d2d;
            border-color: #444;
            color: #e0e0e0;
        }

        .question-textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        .dark-mode .question-textarea:focus {
            box-shadow: 0 0 0 3px rgba(72, 149, 239, 0.3);
        }

        .divider {
            border-top: 1px solid var(--border);
            margin: 25px 0;
        }

        .submit-btn {
            display: block;
            width: 100%;
            padding: 14px;
            margin-top: 25px;
            font-size: 18px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            box-shadow: var(--shadow);
        }

        .submit-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(67, 97, 238, 0.3);
        }

        .dark-mode .submit-btn:hover {
            box-shadow: 0 6px 15px rgba(72, 149, 239, 0.4);
        }

        .message {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            font-weight: 500;
            transition: var(--transition);
        }

        .error {
            color: var(--danger);
            background-color: #ffebee;
            border: 1px solid #ffcdd2;
        }

        .dark-mode .error {
            background-color: #2d0b13;
            border-color: #7a1c32;
        }

        .success {
            color: var(--success);
            background-color: #e8f5e9;
            border: 1px solid #c8e6c9;
        }

        .dark-mode .success {
            background-color: #0d261f;
            border-color: #1a604c;
        }

        .icon {
            margin-right: 8px;
            font-size: 20px;
            vertical-align: middle;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .main-box {
                padding: 20px;
            }
            
            .login-title {
                font-size: 22px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            .timer-container, 
            .progress-container,
            .main-box {
                padding: 15px;
            }
            
            #countdown {
                font-size: 28px;
            }
            
            .question-textarea {
                height: 100px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <span class="icon">📝</span>
                <span>Quiz</span>
            </div>
            <button id="themeToggle" class="theme-toggle">
                <span class="icon">🌓</span>
                <span>Toggle Theme</span>
            </button>
        </div>
        
        <div class="timer-container">
            <div class="timer-header">
                <div class="timer-title">
                    <span class="icon">⏱️</span>
                    Time Remaining:
                </div>
            </div>
            <div id="countdown">15:00</div>
        </div>
        
        <div class="progress-container">
            <div class="progress-header">
                <div class="progress-title">
                    <span class="icon">📊</span>
                    Quiz Progress:
                </div>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" id="progress-fill" style="width: 0%">0%</div>
            </div>
            <div class="progress-text" id="progress-text">0 out of 5 questions answered (0%)</div>
        </div>
        
        <div class="main-box">
            <div class="login-title">
                <span class="icon">📝</span>
                Quiz Form
            </div>
            
            <?php if ($error): ?>
                <div class="message error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="message success">✅ Your answers were saved and analyzed successfully!</div>
            <?php endif; ?>
            
            <form method="post" action="" id="studentForm" autocomplete="off">
                <label class="student-id-label">Student ID</label>
                <input type="text" id="studentId" name="studentId" required pattern="\d+" 
                    class="student-id-input" value="<?= htmlspecialchars($_POST['studentId'] ?? '') ?>" />
                
                <div class="divider"></div>
                
                <label class="question-label"><b>Question 1:</b> Where is the capital city of France?</label>
                <input type="text" id="q1" name="q1" class="question-input" required 
                    value="<?= htmlspecialchars($_POST['q1'] ?? '') ?>" />
                <input type="hidden" name="q1_time" id="q1_time" value="0" />
                
                <div class="divider"></div>
                
                <label class="question-label"><b>Question 2:</b> Solve for x in the equation: 2x + 5 = 15. Write the solution.</label>
                <input type="text" id="q2" name="q2" class="question-input" required 
                    value="<?= htmlspecialchars($_POST['q2'] ?? '') ?>" />
                <input type="hidden" name="q2_time" id="q2_time" value="0" />
                
                <div class="divider"></div>
                
                <label class="question-label"><b>Question 3:</b> If all roses are flowers and some flowers fade quickly, which of the following statements must be true? Explain it.</label>
                <textarea id="q3" name="q3" class="question-textarea" required><?= htmlspecialchars($_POST['q3'] ?? '') ?></textarea>
                <input type="hidden" name="q3_time" id="q3_time" value="0" />
                
                <div class="divider"></div>
                
                <label class="question-label"><b>Question 4:</b> What is the chemical formula for water? How was the first soup made?</label>
                <textarea id="q4" name="q4" class="question-textarea" required><?= htmlspecialchars($_POST['q4'] ?? '') ?></textarea>
                <input type="hidden" name="q4_time" id="q4_time" value="0" />
                
                <div class="divider"></div>
                
                <label class="question-label"><b>Question 5:</b> Write one of Ferdowsi's books, a little bio about him, and include your favorite poem from him.</label>
                <textarea id="q5" name="q5" class="question-textarea" required><?= htmlspecialchars($_POST['q5'] ?? '') ?></textarea>
                <input type="hidden" name="q5_time" id="q5_time" value="0" />
                
                <button type="submit" class="submit-btn">Submit Answers</button>
            </form>
        </div>
    </div>

    <script>
        // زمان‌سنج 15 دقیقه‌ای
        let totalTime = 15 * 60; // 15 دقیقه به ثانیه
        let timerInterval;
        let lastTime = Date.now();
        const totalQuestions = 5;
        let questionTimes = Array(totalQuestions).fill(0);

        function startTimer() {
            timerInterval = setInterval(function() {
                totalTime--;
                let minutes = Math.floor(totalTime / 60);
                let seconds = totalTime % 60;
                
                // فرمت زمان به صورت 00:00
                let timeString = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                document.getElementById('countdown').innerText = timeString;
                
                // تغییر رنگ برای زمان کم
                if (totalTime <= 60) { // 1 دقیقه باقی مانده
                    document.getElementById('countdown').classList.add('timer-warning');
                }
                
                // پایان زمان
                if (totalTime <= 0) {
                    clearInterval(timerInterval);
                    document.getElementById('countdown').innerText = "00:00";
                    alert('Time is up! Your answers will be submitted automatically.');
                    document.getElementById('studentForm').submit();
                }
            }, 1000);
        }

        // محاسبه زمان صرف شده برای هر سوال
        function setQuestionTime(questionNumber) {
            const now = Date.now();
            if (questionNumber > 1) {
                const timeSpent = Math.round((now - lastTime) / 1000);
                document.getElementById(`q${questionNumber-1}_time`).value = timeSpent;
                questionTimes[questionNumber-2] = timeSpent;
            }
            lastTime = now;
        }

        // ردیابی زمان برای هر سوال
        for(let i = 1; i <= totalQuestions; i++) {
            document.getElementById('q'+i).addEventListener('focus', function() {
                setQuestionTime(i);
            });
        }

        // شروع تایمر برای اولین سوال
        document.getElementById('q1').addEventListener('focus', function() {
            lastTime = Date.now();
        });

        // ثبت زمان برای آخرین سوال هنگام سابمیت
        document.getElementById('studentForm').addEventListener('submit', function() {
            const now = Date.now();
            document.getElementById(`q${totalQuestions}_time`).value = Math.round((now - lastTime) / 1000);
            questionTimes[totalQuestions-1] = Math.round((now - lastTime) / 1000);
            clearInterval(timerInterval);
        });

        // تغییر تم
        const toggleBtn = document.getElementById("themeToggle");
        const currentTheme = localStorage.getItem("theme");

        if (currentTheme === "dark") {
            document.body.classList.add("dark-mode");
        }

        toggleBtn.addEventListener("click", () => {
            document.body.classList.toggle("dark-mode");
            
            if (document.body.classList.contains("dark-mode")) {
                localStorage.setItem("theme", "dark");
                toggleBtn.innerHTML = '<span class="icon">☀️</span> Light Mode';
            } else {
                localStorage.setItem("theme", "light");
                toggleBtn.innerHTML = '<span class="icon">🌙</span> Dark Mode';
            }
        });

        // بروزرسانی آیکون دکمه تم بر اساس حالت فعلی
        if (document.body.classList.contains("dark-mode")) {
            toggleBtn.innerHTML = '<span class="icon">☀️</span> Light Mode';
        } else {
            toggleBtn.innerHTML = '<span class="icon">🌙</span> Dark Mode';
        }

        // نوار پیشرفت
        function updateProgress() {
            let answered = 0;
            
            // بررسی سوالات متنی
            for (let i = 1; i <= totalQuestions; i++) {
                const input = document.getElementById('q' + i);
                if (input.value.trim() !== '') {
                    answered++;
                }
            }
            
            const percent = Math.round((answered / totalQuestions) * 100);
            document.getElementById('progress-fill').style.width = percent + '%';
            document.getElementById('progress-fill').innerText = percent + '%';
            document.getElementById('progress-text').innerText = 
                `${answered} out of ${totalQuestions} questions answered (${percent}%)`;
        }

        // ردیابی تغییرات در ورودی‌ها
        const inputs = document.querySelectorAll('input[type="text"], textarea');
        inputs.forEach(input => {
            input.addEventListener('input', updateProgress);
        });

        // شروع تایمر کلی و به‌روزرسانی اولیه
        window.addEventListener('DOMContentLoaded', function() {
            startTimer();
            updateProgress();
        });
    </script>
</body>
</html>