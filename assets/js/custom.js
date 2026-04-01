function myFunction() {
  var x = document.getElementById("myTopnav");
  if (x.className === "topnav") {
    x.className += " responsive";
  } else {
    x.className = "topnav";
  }
}

(function() {
  const IDLE_TIMEOUT = 30000;
  let idleTimer;
  let countdownInterval;
  let timerDisplay = null;

  function redirectToLogin() {
    window.location.href = 'login.php';
  }

  function removeTimer() {
    if (timerDisplay) {
      timerDisplay.remove();
      timerDisplay = null;
    }
    clearInterval(countdownInterval);
  }

  function showTimer() {
    const div = document.createElement('div');
    div.id = 'idle-timer';
    div.style.cssText = 'position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:#dc3545;color:white;padding:10px 20px;border-radius:25px;font-size:14px;font-family:Arial,sans-serif;z-index:99999;box-shadow:0 2px 10px rgba(0,0,0,0.3);display:flex;align-items:center;gap:10px;';
    div.innerHTML = '<span>⚠️ Auto logout in <strong id="idle-countdown">10</strong>s</span>';
    document.body.appendChild(div);
    timerDisplay = div;

    let secondsLeft = 10;
    countdownInterval = setInterval(function() {
      secondsLeft--;
      const countdownEl = document.getElementById('idle-countdown');
      if (countdownEl) countdownEl.textContent = secondsLeft;
      if (secondsLeft <= 0) {
        clearInterval(countdownInterval);
        removeTimer();
        redirectToLogin();
      }
    }, 1000);

    setTimeout(function() {
      const btn = document.createElement('button');
      btn.textContent = 'Stay';
      btn.style.cssText = 'background:#28a745;border:none;color:white;padding:5px 12px;border-radius:15px;cursor:pointer;font-size:12px;font-weight:bold;';
      btn.onclick = function(e) {
        e.stopPropagation();
        removeTimer();
        resetTimer();
      };
      div.appendChild(btn);
    }, 2000);
  }

  function resetTimer() {
    clearTimeout(idleTimer);
    removeTimer();
    idleTimer = setTimeout(showTimer, IDLE_TIMEOUT);
  }

  const events = ['mousemove', 'keydown', 'scroll', 'click', 'touchstart'];
  events.forEach(event => {
    document.addEventListener(event, resetTimer);
  });

  resetTimer();
})();