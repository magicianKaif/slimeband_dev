<?php
session_start();
require_once "cfg.php";
$conn->query("
    DELETE FROM users
    WHERE created_at < NOW() - INTERVAL 12 HOUR
");

/* -------------------------
   Init
------------------------- */
$errors = [];
$success = "";
$submitted = false;

/* -------------------------
   Handle POST
------------------------- */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $submitted = true;

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    /* Validation */
    if ($username === "" || $password === "") {
        $errors[] = "Both username and password are required.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare(
            "SELECT id, password_hash FROM users WHERE username = ? LIMIT 1"
        );
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user["password_hash"])) {

                /* Login success */
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $username;

                $success = "Login successful! Redirecting...";

                /* Optional redirect */
                header("refresh:2;url=services.php");
            } else {
                $errors[] = "Invalid username or password.";
            }
        } else {
            $errors[] = "Invalid username or password.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login - Slimeband</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="css/register.css" rel="stylesheet">
<link href="css/home.css" rel="stylesheet">
</head>

<body class="min-h-screen bg-gradient-to-br from-[#4f4986] via-[#302b63] to-[#5c5caf] text-white">
<!-- HEADER -->
  <header class="flex items-center justify-between px-6 md:px-12 py-6">

    <!-- LOGO -->
    <div
      class="w-14 h-14 rounded-full border-2 border-white bg-cover bg-center"
      style="background-image: url('logo.jpg');">
    </div>

    <!-- NAV -->
    <div class="relative flex items-center gap-6">

      <nav class="hidden md:flex gap-8 text-sm font-bold">
        <a href="index.html" class="border-b-2 border-transparent hover:border-pink-500 transition">HOME</a>
        <a href="contact.html" class="border-b-2 border-transparent hover:border-pink-500 transition">CONTACT</a>
        <a href="about.html" class="border-b-2 border-transparent hover:border-pink-500 transition">ABOUT</a>
        <a href="donate.html" class="border-b-2 border-transparent hover:border-pink-500 transition">DONATE</a>
      </nav>

      <!-- MENU BUTTON -->
      <button id="menuBtn" class="text-3xl font-bold select-none">
        ☰
      </button>

      <!-- DROPDOWN -->
      <div
        id="dropdownMenu"
        class="hidden absolute right-0 top-12 w-40 bg-slate-800 border border-slate-600 rounded-md shadow-lg overflow-hidden"
      >
        <a href="register.php" class="block px-4 py-3 hover:bg-slate-600 transition font-bold text-sm">
          Register
        </a>
        <a href="login.php" class="block px-4 py-3 hover:bg-slate-600 transition font-bold text-sm">
          Login
        </a>
      </div>

    </div>
  </header>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#0f0c29] via-[#302b63] to-[#24243e] text-white">
<div class="w-full max-w-md bg-white/10 backdrop-blur-lg p-8 rounded-xl shadow-xl">

<h1 class="text-3xl font-bold text-center text-yellow-400 mb-6">
Login
</h1>

<!-- Errors -->
<?php if ($submitted && !empty($errors)): ?>
    <?php foreach ($errors as $msg): ?>
        <div class="mb-3 bg-red-500/20 text-red-300 px-4 py-2 rounded-md text-sm">
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Success -->
<?php if ($success): ?>
    <div class="mb-4 bg-green-500/20 text-green-300 px-4 py-2 rounded-md text-sm">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<form method="POST" class="space-y-4">

<input
    type="text"
    name="username"
    placeholder="Username"
    value="<?= htmlspecialchars($username ?? '') ?>"
    required
    class="w-full px-4 py-2 rounded-md bg-white/20 text-white placeholder-white/70 outline-none focus:ring-2 focus:ring-yellow-400"
/>

<input
    type="password"
    name="password"
    placeholder="Password"
    required
    class="w-full px-4 py-2 rounded-md bg-white/20 text-white placeholder-white/70 outline-none focus:ring-2 focus:ring-yellow-400"
/>

<button
    type="submit"
    class="w-full py-2 rounded-md font-bold bg-gradient-to-r from-yellow-400 to-red-500 hover:opacity-90 transition"
>
Login
</button>
<p class="text-center text-sm text-white/70 mt-4">
Don’t have an account?
<a href="register.php" class="text-yellow-400 hover:underline">Register</a>
</p>
</form>



</div>
</div>
  <!-- SCRIPT -->
  <script>
    const menuBtn = document.getElementById("menuBtn");
    const dropdownMenu = document.getElementById("dropdownMenu");

    menuBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      dropdownMenu.classList.toggle("hidden");
    });

    document.addEventListener("click", (e) => {
      if (!menuBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
        dropdownMenu.classList.add("hidden");
      }
    });
  </script>
</body>
</html>
