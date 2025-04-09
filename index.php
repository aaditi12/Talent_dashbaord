<?php 
require 'authentication.php'; // admin authentication check 

// Network Details
$network_info = [
    'SSID' => 'Airtel_JSPL_5(GHz)',
    'Protocol' => 'Wi-Fi 5 (802.11ac)',
    'Security' => 'WPA2-Personal',
    'Manufacturer' => 'Realtek Semiconductor Corp.',
    'Adapter' => 'Realtek RTL8851BE WiFi 6 802.11ax PCIe Adapter',
    'Driver' => '6101.19.118.0',
    'Band' => '5 GHz',
    'Channel' => '161',
    'Speed' => '433/433 Mbps',
    'IPv6' => '2401:4900:1c45:2ba3:962d:2902:d06d:2474',
    'IPv4' => '192.168.1.18',
    'MAC' => '28-2E-89-1E-F4-FC'
];

// auth check
if (isset($_SESSION['admin_id'])) {
    $user_id = $_SESSION['admin_id'];
    
    // Capture login time & Network Details
    $log_query = "INSERT INTO monitoring_logs (user_id, activity_type, data) 
                  VALUES ('$user_id', 'login', 'User logged in from IP: " . $network_info['IPv4'] . "')";
    mysqli_query($conn, $log_query);
}

if (isset($_POST['fingerprint_data'])) {
    $fingerprint_data = $_POST['fingerprint_data'];
    $query = "SELECT * FROM admin_users WHERE fingerprint_data = '$fingerprint_data'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['admin_id'] = $row['id'];
        $_SESSION['admin_name'] = $row['username'];
        $_SESSION['security_key'] = md5($row['id'] . time());
        header('Location: task-info.php');
        exit;
    } else {
        $info = "Fingerprint not recognized.";
    }
}
$page_name = "Login";
include("include/login_header.php");
?>
<style>
    html, body {
    height: 100%;
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0;
    background: linear-gradient(135deg, #a2400b,#ff9800)
    animation: fadeInBody 1.5s ease-in-out;
  }

  .login-container {
    display: flex;
    width: 90%;
    max-width: 900px;
    background: white;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    overflow: hidden;
    background: linear-gradient(135deg, #a2400b,#ff9800)
    animation: popIn 1s ease-out;
  }

  .login-left {
    flex: 1;
    background: linear-gradient(to bottom, #ff7e5f, #feb47b);
    color: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 20px;
    animation: slideInLeft 1s ease-out;
  }

  .login-left h2 {
    font-size: 24px;
    margin-bottom: 10px;
  }

  .login-left p {
    font-size: 16px;
  }

  .logo {
    max-width: 100%;
    height: auto;
    padding: 10px;
    border-radius: 50%;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    background-color: white;
    transition: transform 0.3s ease-in-out;
  }

  .logo:hover {
    transform: scale(1.1);
  }

  .login-right {
    flex: 1;
    padding: 20px;
    background-color: hsl(0, 0%, 90%);
    animation: slideInRight 1s ease-out;
  }

  .form-group {
    margin-bottom: 15px;
    animation: fadeInForm 1.2s ease-out;
  }

  .form-group input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    transition: all 0.3s ease;
  }

  .form-group input:focus {
    border-color: #ff7e5f;
    box-shadow: 0 0 8px rgba(255, 126, 95, 0.6);
    outline: none;
  }

  .btn {
    background-color: orange;
    border-color: darkorange;
    color: white;
    width: 100%;
    padding: 10px;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
  }

  .btn:hover {
    background-color: darkorange;
    transform: translateY(-3px);
  }

  @keyframes fadeInBody {
    from { opacity: 0; }
    to { opacity: 1; }
  }

  @keyframes popIn {
    0% { opacity: 0; transform: scale(0.8); }
    100% { opacity: 1; transform: scale(1); }
  }

  @keyframes slideInLeft {
    from { transform: translateX(-100%); }
    to { transform: translateX(0); }
  }

  @keyframes slideInRight {
    from { transform: translateX(100%); }
    to { transform: translateX(0); }
  }

  @keyframes fadeInForm {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }


    /* Basic Styling */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(to right, #F5F5F5, #FFFFFF);

        }
            /* background: linear-gradient(to right, #b3e0ff, #d4eaff);
        } */

        /* Container */
        .login-container {
            display: flex;
            width: 750px;
            background: white;
            border-radius: 12px;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            
        }

        /* Left Side */
        .login-left {
            flex: 1;
            background: #F8F8F8;
            color: #E65200;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }

        .login-left h2 {
            font-size: 22px;
            margin-bottom: 10px;
            color: #E65200;
        }

        .login-left p {
            font-size: 14px;
            text-align: center;
            color: #E65200;

        }

        /* Right Side */
        .login-right {
            flex: 1;
            padding: 40px;
            background: white;
            text-align: center;
        }

        .login-right h2 {
            color: #E65200;
            margin-bottom: 20px;
        }

        /* Input Fields */
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        /* Forgot Password */
        .forgot-password {
            text-align: right;
            margin-bottom: 15px;
        }

        .forgot-password a {
            color: royalblue;
            text-decoration: none;
            font-size: 14px;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        /* Sign In Button */
        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(to right, #ff9800, #e65100);
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease-in-out, transform 0.2s;
        }

        .btn:hover {
            transform: scale(1.05);
        }

  /* Floating Chat Button */
#chatbot-toggle {
    position: fixed;
    right: 20px;
    bottom: 20px;
    width: 60px;
    height: 60px;
    background: none;
    border: none;
    cursor: pointer;
    transition: transform 0.3s;
}

#chatbot-toggle:hover {
    transform: scale(1.1);
}

#chat-icon {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

/* Chatbot Window */
#chatbot-container {
    position: fixed;
    right: 20px;
    bottom: 80px;
    width: 350px;
    height: 500px;
    display: none;
    flex-direction: column;
    border-radius: 12px;
    box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.3);
    background-color: white;
    transition: all 0.3s ease-in-out;
}
   .video {
            width: 100%;
            height: auto;
        }

/* Chatbot Header */
#chatbot-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #ff7f00; /* Orange Header */
    color: white;
    padding: 15px;
    font-size: 18px;
    font-weight: bold;
}

#close-chat {
    background: none;
    border: none;
    color: white;
    font-size: 20px;
    cursor: pointer;
}

/* Chat Messages */
#chatbot-messages {
    flex-grow: 1;
    padding: 15px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
}

/* Input Box */
#chatbot-input-container {
    display: flex;
    padding: 12px;
    border-top: 1px solid #ccc;
    background: #f9f9f9;
}

#chatbot-input {
    flex-grow: 1;
    padding: 10px;
    border-radius: 8px;
    font-size: 14px;
    border: 1px solid #ccc;
    outline: none;
}

#chatbot-send {
    background-color: #ff7f00;
    color: white;
    border: none;
    padding: 10px 15px;
    margin-left: 8px;
    border-radius: 8px;
    cursor: pointer;
}

#chatbot-send:hover {
    background-color: #e67300;
}
.chatbox {
    border-radius: 15px;
    border: 1px solid #ccc;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    background-color: white;
    padding: 10px;
    max-width: 400px;
}

/* Chatbot Image Section */
.chatbot-section {
    position: absolute;
    bottom: 10px;
    right: 20px;
    text-align: center;
}

.chatbot-img {
    width: 150px;
    height: auto;
    display: block;
    margin: 0 auto;
}





</style>

<link rel="stylesheet" href="custom.css">



 <!-- Chatbot Image & Details -->
           

<button id="chatbot-toggle">
    <img src="img/Symbolchatbot.jpg" alt="Chat" id="chat-icon">
</button>

  

   <div class="chatbot-section" style="margin-bottom: 40px; margin-right: -10px;">
                <img src="img/chatbot.jpg" alt="Chatbot" class="chatbot-img">
                <p><strong style="color: black;">Founder & Coach:</strong> Sudhir Singh</p>
            </div>
 
<!-- Chatbot Container -->
<div id="chatbot-container">
    
    <div id="chatbot-header">
        JBE Chatbot
        <button id="close-chat">&times;</button>
    </div>
    <h2> Talent Dashboard Demo</h2>
     <video id="chatbot-video" controls>
            <source src="videos/Talentdash.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    <div id="chatbot-messages"></div>
    <div id="chatbot-input-container">
        <input type="text" id="chatbot-input" placeholder="Type a message..." />
        <button id="chatbot-send">Send</button>
    </div>
</div>



<div class="login-container">
  <div class="login-left">
    <h2><strong>JBE Talent Dashboard</strong></h2>
    <img src="assets/img/logo.jpg" alt="JBE logo" class="logo">
    <p><strong>Transformation.Talent. Technology. Tax Automation</strong></p>
    <div id="ai-name-display" style="font-size: 20px; font-weight: bold; color: #E65200; text-align: center; margin-top: 20px;"> Dwadashi Pathiks</div>
  </div>
  <div class="login-right">
    <form action="" method="POST">
     
     <h2>Talents Login</h2>
      <div class="form-group">
        <input type="text" class="form-control" placeholder="Username" name="username" required />
      </div>
      <div class="form-group">
        <input type="password" class="form-control" placeholder="Password" name="admin_password" required />
      </div>
      <button type="submit" name="login_btn" class="btn">Login</button>
      
    </form>
  </div>
</div>




 <script>
        document.getElementById("chatbot-toggle").addEventListener("click", function () {
            let chatbot = document.getElementById("chatbot-container");
            chatbot.style.display = chatbot.style.display === "flex" ? "none" : "flex";
            if (chatbot.style.display === "flex") {
                document.getElementById("chatbot-video").play();
            }
        });

        document.getElementById("close-chat").addEventListener("click", function () {
            document.getElementById("chatbot-container").style.display = "none";
        });
    </script>

      <script>
        const generatedOTP = "123456"; // Predefined OTP for demo

        function verifyOTP() {
            const userOTP = document.getElementById("otpInput").value;
            const message = document.getElementById("message");

            if (userOTP === generatedOTP) {
                message.style.color = "green";
                message.textContent = "OTP Verified Successfully!";
            } else {
                message.style.color = "red";
                message.textContent = "Invalid OTP. Please try again.";
            }
        }
    </script>


<script>
    

    const DwadashiPathiks = [
        "Avinash Sharma", "Prathamesh", "Ventakash", "Nikhil",
        "Shashikant", "Gaurav", "Suraj", "Bhavesh",
        "Darshan", "Abhay","Aaditi","Neha"
    ];
    
    
    let index = 0;

    function displayNextName() {
        let displayElement = document.getElementById("ai-name-display");
        
        if (index < DwadashiPathiks.length) {
            displayElement.textContent = "Dwadashi  Pathiks: " + DwadashiPathiks[index];
            index++;
        }
        setTimeout(displayNextName, 3000);
    }

    window.onload = function() {
        displayNextName();
    };
</script>


   
        



<?php include("include/footer.php"); ?>



