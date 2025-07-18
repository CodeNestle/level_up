<?php
session_start();
include 'db.php';
date_default_timezone_set('Asia/Kolkata');

$step = 'email';
$error = '';
$email = $_POST['email'] ?? '';

if (isset($_POST['send_otp']) || isset($_POST['resend_otp'])) {
    $email = trim($_POST['email']);

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        echo "<script>alert('Email not found. Please register.'); window.location='register.php';</script>";
        exit();
    }

    // Generate and store OTP
    $otp = rand(100000, 999999);
    $otp_sent_time = date("Y-m-d H:i:s");

    $stmt = $conn->prepare("UPDATE users SET otp = ?, otp_sent_time = ? WHERE email = ?");
    $stmt->bind_param("sss", $otp, $otp_sent_time, $email);
    $stmt->execute();

    $_SESSION['temp_email'] = $email;
    $_SESSION['generated_otp'] = $otp;
    $_SESSION['send_otp_now'] = true; // ✅ only send via JS if button clicked

    $step = 'otp';
}

if (isset($_POST['verify_otp'])) {
    $entered_otp = trim($_POST['otp']);
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT otp, otp_sent_time FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($db_otp, $db_time);
    $stmt->fetch();
    $stmt->close();

    if (!$db_otp || !$db_time) {
        $error = "⚠️ OTP expired or not found. Please resend OTP.";
        $step = 'otp';
    } else {
        $now = new DateTime();
        $sent_time = new DateTime($db_time);
        $diff = $now->getTimestamp() - $sent_time->getTimestamp();

        if ($entered_otp == $db_otp && $diff <= 60) {
            $_SESSION['email'] = $email;

            // Clear OTP
            $stmt = $conn->prepare("UPDATE users SET otp = NULL, otp_sent_time = NULL WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();

            header("Location: reset_password.php");
            exit();
        } else {
            $error = "❌ Invalid or expired OTP.";
            $step = 'otp';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <script src="https://cdn.jsdelivr.net/npm/emailjs-com@3/dist/email.min.js"></script>
        <link rel="icon" href="images/LuLogo.jpeg">

    <script>
        (function() {
            emailjs.init("q6GhHFc-ur5wXCKZx");
        })();

        function sendEmailJS(email, otp) {
            emailjs.send("service_jgeho8k", "template_bmojgq5", {
                email: email,
                otp: otp
            }).then(function(response) {
                alert("📨 OTP sent successfully to " + email);
            }, function(error) {
                alert("❌ Failed to send OTP. Try again.");
            });
        }

        window.onload = function() {
            const otp = "<?php echo $_SESSION['generated_otp'] ?? ''; ?>";
            const email = "<?php echo $_SESSION['temp_email'] ?? ''; ?>";
            const shouldSend = "<?php echo isset($_SESSION['send_otp_now']) ? '1' : '0'; ?>";

            if (otp && email && shouldSend === "1") {
                sendEmailJS(email, otp);
            }

            // Countdown timer
            let countdown = 60;
            const resendBtn = document.getElementById('resendBtn');
            const verifyBtn = document.getElementById('verifyBtn');
            const timerText = document.getElementById('timerText');

            if (resendBtn && verifyBtn && timerText) {
                resendBtn.disabled = true;
                verifyBtn.disabled = false;

                const timer = setInterval(() => {
                    countdown--;
                    timerText.innerText = "⏱ OTP expires in " + countdown + " sec";

                    if (countdown <= 0) {
                        clearInterval(timer);
                        resendBtn.disabled = false;
                        verifyBtn.disabled = true;
                        timerText.innerText = "❌ OTP expired. Please click 'Resend OTP'.";
                    }
                }, 1000);
            }
        };
    </script>
</head>
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #e0f7fa, #ffffff);
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        margin: 0;
    }

    h2 {
        color: #006064;
        margin-bottom: 20px;
    }

    form {
        background: #ffffff;
        padding: 25px 30px;
        border-radius: 15px;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        margin: 10px 0;
        min-width: 300px;
        max-width: 350px;
    }

    input[type="email"],
    input[type="text"] {
        width: 100%;
        padding: 12px 10px;
        margin: 10px 0;
        border: 1px solid #ccc;
        border-radius: 10px;
        font-size: 16px;
    }

    button {
        background-color: #00796b;
        color: white;
        padding: 10px 15px;
        font-size: 16px;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: background 0.3s ease;
        width: 100%;
    }

    button:hover {
        background-color: #004d40;
    }

    #timerText {
        text-align: center;
        margin-top: 10px;
        color: #888;
    }

    p[style*="color:red"] {
        color: #c62828 !important;
        background: #ffcdd2;
        padding: 10px;
        border-radius: 10px;
        margin-bottom: 15px;
        text-align: center;
    }
</style>

<body>
    <h2>🔐 Forgot Password</h2>
    <?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>

    <?php if ($step === 'email'): ?>
        <form method="POST">
            <label>📧 Enter your Email:</label><br>
            <input type="email" name="email" required><br><br>
            <button type="submit" name="send_otp">Send OTP</button>
        </form>
    <?php endif; ?>

    <?php if ($step === 'otp'): ?>
        <form method="POST">
            <label>🔢 Enter OTP:</label><br>
            <input type="text" name="otp" required><br><br>
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($_SESSION['temp_email']); ?>">
            <button type="submit" name="verify_otp" id="verifyBtn">✅ Verify OTP</button>
        </form>
        <br>
        <form method="POST">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($_SESSION['temp_email']); ?>">
            <button type="submit" name="resend_otp" id="resendBtn">🔄 Resend OTP</button>
        </form>
        <p id="timerText" style="color:gray;"></p>
    <?php endif; ?>

    <!-- Auto-refresh OTP expiry checker -->
    <iframe src="time_calculate.php" style="display:none;" id="timerFrame"></iframe>
    <script>
        setInterval(() => {
            document.getElementById("timerFrame").contentWindow.location.reload();
        }, 2000);
    </script>
</body>
</html>

<?php unset($_SESSION['send_otp_now']); // ✅ Clear flag so it doesn't resend again ?>
