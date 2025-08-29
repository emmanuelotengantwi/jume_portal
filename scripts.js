// Page fade-in (kept simple)
document.addEventListener("DOMContentLoaded", () => {
  document.body.classList.add("fade-in");
});

// Smooth fade-out for PHP links (optional)
document.querySelectorAll("a").forEach(link => {
  link.addEventListener("click", function (e) {
    const href = this.getAttribute("href") || "";
    if (href.endsWith(".php")) {
      e.preventDefault();
      const target = this.href;
      document.body.classList.add("fade-out");
      setTimeout(() => { window.location.href = target; }, 400);
    }
  });
});

// ========= GOOGLE LOGIN (client-side) =========
// We'll initialize from HTML after loading Google's script.
// The callback below posts the ID token to our PHP endpoint.
async function handleGoogleCredentialResponse(response) {
  try {
    const res = await fetch('google_oauth.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: 'credential=' + encodeURIComponent(response.credential)
    });
    const data = await res.json();
    if (data.success) {
      window.location.href = 'index.php';
    } else {
      alert(data.message || 'Google sign-in failed');
    }
  } catch (err) {
    alert('Network error during Google login.');
  }
}

// ========= FACEBOOK LOGIN =========
function handleFacebookLogin() {
  // Uses the FB SDK loaded in the page
  FB.login(async function(response) {
    if (response.authResponse) {
      const accessToken = response.authResponse.accessToken;
      try {
        const res = await fetch('facebook_oauth.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: 'access_token=' + encodeURIComponent(accessToken)
        });
        const data = await res.json();
        if (data.success) {
          window.location.href = 'index.php';
        } else {
          alert(data.message || 'Facebook sign-in failed');
        }
      } catch (e) {
        alert('Network error during Facebook login.');
      }
    } else {
      alert('Facebook login was cancelled.');
    }
  }, {scope: 'public_profile,email'});
}
