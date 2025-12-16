<?php
session_start();
require_once 'cfg.php'; // your DB connection

$statusMsg = '';
$usernameToCall = '';
$flag = false;
$username = $_SESSION['username'] ?? 'User';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $usernameToCall = trim($_POST['username'] ?? '');

    if(empty($usernameToCall)){
        $statusMsg = 'Please enter a username.';
    } else {
        // Prepare statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $usernameToCall);
        $stmt->execute();
        $stmt->store_result();

        if($stmt->num_rows > 0){
            $statusMsg = "Calling $usernameToCall...";
          
           $flag = true;
            // Here you could trigger WebRTC/voice call initiation
        } else {
            $statusMsg = 'Username not found.';
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slimeband - Voice Call Chat</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-[#0f0c29] via-[#302b63] to-[#24243e] text-white flex flex-col items-center justify-center min-h-screen relative font-sans">

<!-- Header -->
<header class="text-center mb-8">
    <nav class="flex justify-center gap-6 mb-4">
        <a href="index.html" class="hover:text-yellow-400">HOME</a>
        <a href="contact.html" class="hover:text-yellow-400">CONTACT</a>
        <a href="about.html" class="hover:text-yellow-400">ABOUT</a>
        <a href="donate.html" class="hover:text-yellow-400">DONATE</a>
        <a href="services.html" class="hover:text-yellow-400">SERVICES</a>
    </nav>
    <h1 class="text-3xl md:text-4xl font-bold uppercase tracking-widest text-yellow-400 mb-2">Voice Chat</h1>
    <p class="text-gray-300">Connect with others anonymously through voice calls.</p>
</header>

<!-- Call Form -->
<div class="bg-white/10 rounded-xl p-6 w-4/5 max-w-md mb-8 flex flex-col gap-4">
    <h2 class="text-xl font-semibold mb-2 text-yellow-400">Enter Username to Call</h2>
    
    <form method="POST" class="flex flex-col gap-3">
        <input name="username" type="text" placeholder="Username" value="<?= htmlspecialchars($usernameToCall) ?>" class="p-3 rounded-md bg-black/20 placeholder-gray-400 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400">
        <button type="submit" id="callButton" class="px-6 py-3 rounded-md font-bold text-white bg-gradient-to-r from-yellow-400 to-red-500 hover:from-red-500 hover:to-yellow-400 transition">
            Call
        </button>
    </form>

    <?php if(!empty($statusMsg)): ?>
        <p class="text-sm <?= strpos($statusMsg, 'Calling') === 0 ? 'text-green-400' : 'text-red-400' ?>">
            <?= $statusMsg ?>
        </p>
    <?php endif; ?>
</div>

<!-- Controls -->
<div class="flex gap-6 hidden" id="trs">
    <button id="micButton" class="flex items-center gap-2 px-6 py-3 rounded-md font-bold text-white bg-gradient-to-r from-yellow-400 to-red-500 hover:from-red-500 hover:to-yellow-400 transition">
        <img src="https://img.icons8.com/ios-filled/50/microphone.png" class="w-5 h-5" alt="Mic">
        Mic
    </button>
    <button id="hangUpButton" class="flex items-center gap-2 px-6 py-3 rounded-md font-bold text-white bg-gradient-to-r from-yellow-400 to-red-500 hover:from-red-500 hover:to-yellow-400 transition">
        <img src="https://img.icons8.com/ios-filled/50/phone-disconnected.png" class="w-5 h-5" alt="Hang Up">
        Hang Up
    </button>
</div>
<!-- Incoming Call Prompt -->
<div id="incomingCall" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50">
    <div class="bg-gray-900 p-6 rounded-xl text-center flex flex-col gap-4">
        <p class="text-xl text-yellow-400" id="callerName">Someone is calling you...</p>
        <div class="flex gap-4 justify-center">
            <button id="acceptCall" class="px-6 py-3 rounded-md bg-green-500 hover:bg-green-600 font-bold">Accept</button>
            <button id="declineCall" class="px-6 py-3 rounded-md bg-red-500 hover:bg-red-600 font-bold">Decline</button>
        </div>
    </div>
</div>
<audio id="remoteAudio" autoplay></audio>


<!-- Footer -->
<footer class="absolute bottom-4 text-gray-400 text-sm text-center">
    © 2024 Slimeband. All rights reserved.
</footer>

<script>
    document.querySelector('form').addEventListener('submit', (e) => {
    e.preventDefault();
    const toUsername = document.querySelector('input[name="username"]').value.trim();
 
    if(toUsername) {
        console.log(`Calling ${toUsername}...`);
        callUser(toUsername);
    }
});

let localStream;
let peerConnection;
let remoteUsername = null;

const configuration = { iceServers: [{ urls: "stun:stun.l.google.com:19302" }] };

async function initLocalStream() {
    try {
        localStream = await navigator.mediaDevices.getUserMedia({ audio: true });
        console.log("Microphone ready");
    } catch (err) {
        alert("Microphone access denied");
        console.error(err);
    }
}
initLocalStream();

const username = '<?= $_SESSION["username"] ?>';
const ws = new WebSocket("ws://localhost:8080");

ws.onopen = () => ws.send(JSON.stringify({ type: "register", username }));

ws.onmessage = async (event) => {
    const data = JSON.parse(event.data);

    switch(data.type) {
        case "incoming_call":
            remoteUsername = data.from;
            document.getElementById("callerName").textContent = `${remoteUsername} is calling you...`;
            document.getElementById("incomingCall").classList.remove("hidden");
            break;

        case "call_accepted":
            // Caller: callee accepted → create offer
            peerConnection = createPeerConnection(data.from);
            const offer = await peerConnection.createOffer();
            await peerConnection.setLocalDescription(offer);
            ws.send(JSON.stringify({ type: "offer", to: data.from, offer }));
            document.getElementById("trs").classList.remove("hidden");
            break;

        case "offer":
            // Callee: receive offer → create answer
            peerConnection = createPeerConnection(data.from);
            await peerConnection.setRemoteDescription(new RTCSessionDescription(data.offer));
            const answer = await peerConnection.createAnswer();
            await peerConnection.setLocalDescription(answer);
            ws.send(JSON.stringify({ type: "answer", to: data.from, answer }));
            document.getElementById("trs").classList.remove("hidden");
            break;

        case "answer":
            // Caller: receive answer → set remote description
            await peerConnection.setRemoteDescription(new RTCSessionDescription(data.answer));
            break;

        case "ice_candidate":
            if(peerConnection && data.candidate) {
                await peerConnection.addIceCandidate(data.candidate).catch(console.error);
            }
            break;

        case "call_declined":
            alert(`${data.from} declined your call.`);
            if(peerConnection) peerConnection.close();
            document.getElementById("trs").classList.add("hidden");
            break;
        case "call_ended":
            if(peerConnection) {
                peerConnection.close();
                peerConnection = null;
            }
            document.getElementById("trs").classList.add("hidden");
            const callBtn = document.getElementById("callButton");
            if(callBtn){
                callBtn.textContent = "Call";
                callBtn.disabled = false;
                    window.location.href = "services.php";
            }
            break;
    }
};

function createPeerConnection(remoteUser) {
    const pc = new RTCPeerConnection(configuration);

    // Add local stream
    if(localStream) localStream.getTracks().forEach(track => pc.addTrack(track, localStream));

    // Handle remote audio
    pc.ontrack = (event) => {
        const audioEl = document.getElementById("remoteAudio");
        if(!audioEl.srcObject){
            audioEl.srcObject = event.streams[0];
            audioEl.play();
        }
    };

    // ICE candidates
    pc.onicecandidate = (event) => {
        if(event.candidate){
            ws.send(JSON.stringify({ type: "ice_candidate", to: remoteUser, candidate: event.candidate }));
        }
    };

    return pc;
}

// Call button
function callUser(toUsername){
    remoteUsername = toUsername;
    ws.send(JSON.stringify({ type: "call", from: username, to: toUsername }));
    // alert(`Calling ${toUsername}...`);
 const btn = document.getElementById("callButton");
    if(btn){
        btn.textContent = `Calling ${toUsername}...`;
        btn.disabled = true; // optional: prevent multiple clicks
    }
}

// Accept / Decline
document.getElementById("acceptCall").addEventListener("click", () => {
    ws.send(JSON.stringify({ type: "call_accepted", to: remoteUsername }));
    document.getElementById("incomingCall").classList.add("hidden");
});

document.getElementById("declineCall").addEventListener("click", () => {
    ws.send(JSON.stringify({ type: "call_declined", to: remoteUsername }));
    document.getElementById("incomingCall").classList.add("hidden");
});

// Mic toggle
let micOn = true;
document.getElementById("micButton").addEventListener("click", () => {
    micOn = !micOn;
    if(localStream) localStream.getAudioTracks()[0].enabled = micOn;
});

// Hang up button
document.getElementById("hangUpButton").addEventListener("click", () => {
    if(peerConnection) {
        peerConnection.close();       // close the WebRTC connection
        peerConnection = null;       // reset the variable
    }

    // Hide mic/hangup controls
    document.getElementById("trs").classList.add("hidden");

    // Notify the remote user that the call has ended
    if(remoteUsername) {
        ws.send(JSON.stringify({ type: "call_ended", to: remoteUsername }));
        remoteUsername = null;
    }

    // Reset call button text
    const callBtn = document.getElementById("callButton");
    if(callBtn){
        callBtn.textContent = "Call";
        callBtn.disabled = false;
            window.location.href = "services.php";

    }

});


</script>


</body>
</html>
