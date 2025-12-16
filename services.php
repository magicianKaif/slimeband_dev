<?php
require_once "auth.php";

$username = $_SESSION['username'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Services - Slimeband</title>

<!-- Tailwind CDN -->
 <link href="css/service.css" rel="stylesheet">

<style>
/* Custom glow animation */
@keyframes glow {
  0%, 100% {
    box-shadow: 0 0 10px rgba(249, 212, 35, 0.6);
  }
  50% {
    box-shadow: 0 0 25px rgba(255, 78, 80, 0.9);
  }
}
.glow {
  animation: glow 2.5s infinite;
}
</style>
</head>

<body class="min-h-screen bg-gradient-to-br from-[#0f0c29] via-[#302b63] to-[#24243e] text-white">

<!-- HEADER -->
<header class="flex items-center justify-between px-6 md:px-12 py-6">

  <!-- LEFT -->
    <!-- LOGO -->
<a href="index.html"
   class="w-14 h-14 rounded-full border-2 border-white
          bg-cover bg-center bg-no-repeat
          flex-shrink-0"
   style="background-image: url('logo.jpg');"
   aria-label="Slimeband Home">
</a>


  <!-- CENTER NAV -->
  <nav class="hidden md:flex gap-6 text-sm font-bold">
    <a href="index.html" class="hover:text-pink-400">HOME</a>
    <a href="contact.html" class="hover:text-pink-400">CONTACT</a>
    <a href="about.html" class="hover:text-pink-400">ABOUT</a>
    <a href="donate.html" class="hover:text-pink-400">DONATE</a>
  </nav>

  <!-- RIGHT USER -->
  <div class="flex items-center gap-4 text-sm">
    <span class="text-yellow-400 font-semibold">
      👤 <?= htmlspecialchars($username) ?>
    </span>

    <a href="logout.php"
       class="px-4 py-1 rounded-md bg-red-500/80 hover:bg-red-600 transition font-bold">
      Logout
    </a>
  </div>
</header>

<!-- TITLE -->
<div class="text-center mt-16">
  <h1 class="text-4xl md:text-5xl tracking-[0.5em] font-[cursive] uppercase mb-6">
    SERVICES
  </h1>
</div>

<!-- SERVICES -->
<section class="flex justify-center items-center flex-wrap gap-20 mt-20 px-6">

  <!-- SERVICE -->
  <a href="voice-chat.php"
     class="group w-36 h-36 md:w-44 md:h-44
            bg-gradient-to-b from-gray-200 to-gray-400
            flex items-center justify-center
            rotate-45 shadow-xl
            glow
            transition transform duration-300
            hover:scale-110 hover:from-yellow-400 hover:to-red-500">

    <span class="-rotate-45 font-bold text-gray-800 group-hover:text-white">
      VOICE CHAT
    </span>
  </a>

  <a href="text-chat.php"
     class="group w-36 h-36 md:w-44 md:h-44
            bg-gradient-to-b from-gray-200 to-gray-400
            flex items-center justify-center
            rotate-45 shadow-xl
            glow
            transition transform duration-300
            hover:scale-110 hover:from-yellow-400 hover:to-red-500">

    <span class="-rotate-45 font-bold text-gray-800 group-hover:text-white">
      TEXT CHAT
    </span>
  </a>

  <a href="video-chat.php"
     class="group w-36 h-36 md:w-44 md:h-44
            bg-gradient-to-b from-gray-200 to-gray-400
            flex items-center justify-center
            rotate-45 shadow-xl
            glow
            transition transform duration-300
            hover:scale-110 hover:from-yellow-400 hover:to-red-500">

    <span class="-rotate-45 font-bold text-gray-800 group-hover:text-white">
      VIDEO CHAT
    </span>
  </a>

</section>

</body>
</html>
