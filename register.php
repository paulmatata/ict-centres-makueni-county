<?php 

session_start();
include 'includes/db.php';
include 'includes/header.php';

$centres = mysqli_query($conn, "SELECT * FROM ict_centres");

?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-6">
      <div class="card border-0 shadow-lg rounded-4 p-4">

        <div class="text-center mb-4">
          <img src="assets/images/makueni-logo.png" width="80">
          <h3 class="fw-bold mt-3">Create Student Account</h3>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form action="register_process.php" method="POST">

          <div class="mb-3">
            <label>Full Name</label>
            <input type="text" name="fullname" class="form-control form-control-lg rounded-3" placeholder="Paul Matata" required>
          </div>

          <div class="mb-3">
            <label>Username</label>
            <input type="text" name="username" class="form-control form-control-lg rounded-3" placeholder="@paulmatata" required>
          </div>

          <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control form-control-lg rounded-3" placeholder="" required>
          </div>

          <div class="mb-3">
            <label>Phone Number</label>
            <input type="text" name="phone" class="form-control form-control-lg rounded-3" placeholder="+2547 / 07.." required>
          </div>

          <div class="mb-3">
            <label>Preferred ICT Centre</label>
            <select name="centre_id" class="form-control form-control-lg" required>
              <option value="">Select ICT Centre</option>
              <?php while($centre = mysqli_fetch_assoc($centres)): ?>
                <option value="<?php echo $centre['id']; ?>"><?php echo $centre['centre_name']; ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="mb-3">
            <label>Password</label>
            <div class="input-group">
              <input type="password" name="password" id="password" class="form-control form-control-lg rounded-start-3" placeholder="Use a strong password" required>
              <button type="button" class="btn btn-outline-secondary" id="togglePassword" tabindex="-1">
                <i class="bi bi-eye" id="toggleIcon"></i>
              </button>
            </div>

            <!-- Password Strength Bar -->
            <div class="mt-2">
              <div class="progress" style="height: 6px; border-radius: 4px;">
                <div id="strengthBar" class="progress-bar" role="progressbar" style="width: 0%; transition: width 0.3s, background-color 0.3s;"></div>
              </div>
              <small id="strengthLabel" class="text-muted"></small>
            </div>

            <!-- Checklist -->
            <ul class="list-unstyled mt-2 mb-0 small" id="passwordChecklist">
              <li id="check-length"  class="text-muted"><i class="bi bi-circle me-1"></i> At least 8 characters</li>
              <li id="check-upper"   class="text-muted"><i class="bi bi-circle me-1"></i> One uppercase letter (A–Z)</li>
              <li id="check-lower"   class="text-muted"><i class="bi bi-circle me-1"></i> One lowercase letter (a–z)</li>
              <li id="check-number"  class="text-muted"><i class="bi bi-circle me-1"></i> One number (0–9)</li>
              <li id="check-special" class="text-muted"><i class="bi bi-circle me-1"></i> One special character (!@#$...)</li>
            </ul>
          </div>

          <div class="mb-3">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" id="confirm_password" class="form-control form-control-lg rounded-3" placeholder="Confirm Password" required>
            <small id="matchMsg" class="d-block mt-1"></small>
          </div>

          <div class="alert alert-info rounded-4">
            <h5>Registration Information</h5>
            <p>Students are required to:</p>
            <ul>
              <li>Pay Ksh. 100 registration fee</li>
              <li>Pay Ksh. 1,000 training fee at the selected ICT Centre</li>
            </ul>
            <p class="mb-0">Payment instructions will be provided after registration.</p>
          </div>

          <div class="mb-3 form-check">
    <input type="checkbox" class="form-check-input" id="data_consent" name="data_consent" required>
    <label class="form-check-label" for="data_consent">
        I have read and agree to the
        <a href="privacy-policy.php" target="_blank">Data Protection &amp; Privacy Policy</a>,
        and I consent to Makueni County ICT Centers collecting and processing my personal
        data for training registration and placement purposes.
    </label>
</div>

          <button class="btn btn-success btn-lg w-100 rounded-3" id="submitBtn">
            Create Account
          </button>

        </form>

        <div class="text-center mt-3">
          Already have an account? <a href="login.php">Login here</a>
          <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center mt-4">
            <a href="index.php" class="btn btn-secondary btn-lg rounded-pill px-4">
              <i class="bi bi-house-door"></i> Home
            </a>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
  const passwordInput   = document.getElementById('password');
  const confirmInput    = document.getElementById('confirm_password');
  const strengthBar     = document.getElementById('strengthBar');
  const strengthLabel   = document.getElementById('strengthLabel');
  const matchMsg        = document.getElementById('matchMsg');
  const toggleBtn       = document.getElementById('togglePassword');
  const toggleIcon      = document.getElementById('toggleIcon');

  const checks = {
    length:  { el: document.getElementById('check-length'),  fn: v => v.length >= 8 },
    upper:   { el: document.getElementById('check-upper'),   fn: v => /[A-Z]/.test(v) },
    lower:   { el: document.getElementById('check-lower'),   fn: v => /[a-z]/.test(v) },
    number:  { el: document.getElementById('check-number'),  fn: v => /[0-9]/.test(v) },
    special: { el: document.getElementById('check-special'), fn: v => /[^A-Za-z0-9]/.test(v) },
  };

  const strengthLevels = [
    { label: '',          color: '',          width: '0%'   },
    { label: 'Weak',      color: '#dc3545',   width: '25%'  },
    { label: 'Fair',      color: '#fd7e14',   width: '50%'  },
    { label: 'Good',      color: '#ffc107',   width: '75%'  },
    { label: 'Strong',    color: '#198754',   width: '100%' },
  ];

  passwordInput.addEventListener('input', () => {
    const val = passwordInput.value;
    let passed = 0;

    for (const key in checks) {
      const ok = checks[key].fn(val);
      const li = checks[key].el;
      const icon = li.querySelector('i');

      if (ok) {
        li.classList.replace('text-muted', 'text-success');
        icon.className = 'bi bi-check-circle-fill me-1';
        passed++;
      } else {
        li.classList.replace('text-success', 'text-muted');
        icon.className = 'bi bi-circle me-1';
      }
    }

    const level = val.length === 0 ? 0 : passed <= 1 ? 1 : passed <= 2 ? 2 : passed <= 3 ? 3 : passed === 4 ? 3 : 4;
    strengthBar.style.width           = strengthLevels[level].width;
    strengthBar.style.backgroundColor = strengthLevels[level].color;
    strengthLabel.textContent         = strengthLevels[level].label;
    strengthLabel.style.color         = strengthLevels[level].color;

    checkMatch();
  });

  confirmInput.addEventListener('input', checkMatch);

  function checkMatch() {
    if (!confirmInput.value) { matchMsg.textContent = ''; return; }

    if (passwordInput.value === confirmInput.value) {
      matchMsg.textContent  = '✓ Passwords match';
      matchMsg.className    = 'd-block mt-1 text-success small';
      confirmInput.classList.remove('is-invalid');
      confirmInput.classList.add('is-valid');
    } else {
      matchMsg.textContent  = '✗ Passwords do not match';
      matchMsg.className    = 'd-block mt-1 text-danger small';
      confirmInput.classList.remove('is-valid');
      confirmInput.classList.add('is-invalid');
    }
  }

  // Show/hide toggle
  toggleBtn.addEventListener('click', () => {
    const isPassword = passwordInput.type === 'password';
    passwordInput.type       = isPassword ? 'text' : 'password';
    toggleIcon.className     = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
  });
</script>

<?php include 'includes/footer.php'; ?>
