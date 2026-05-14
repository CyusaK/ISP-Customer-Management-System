<h2>Customer Registration</h2>

<form method="POST">
    <input type="text" name="name" placeholder="Full Name" required><br><br>
    <input type="email" name="email" placeholder="Email" required><br><br>
    <input type="text" name="phone" placeholder="Phone"><br><br>
    <input type="text" name="address" placeholder="Address"><br><br>
    <button type="submit">Register</button>
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    echo "<p style='color:green;'>Form submitted</p>";
}
?>