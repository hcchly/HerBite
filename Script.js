// SIGN UP -> go to login page
const signupForm = document.getElementById("signupForm");
if (signupForm) {
  document.getElementById("profileImg").setAttribute("accept", "image/*"); // images only

  signupForm.addEventListener("submit", function (e) {
    e.preventDefault();
    window.location.href = "user.html";
  });
}

// LOGIN USER -> go to user page
const loginForm = document.getElementById("loginForm");
if (loginForm) {
  loginForm.addEventListener("submit", function (e) {
    e.preventDefault();
    window.location.href = "user.html";
  });

  // LOGIN ADMIN -> go to admin page
  document.getElementById("adminBtn").addEventListener("click", function () {
    window.location.href = "admin.html";
  });
}

console.log("User Page loaded (Phase 1).");
// Category filtering will be implemented in Phase 2.
