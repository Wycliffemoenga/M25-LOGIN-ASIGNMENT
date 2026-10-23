document.addEventListener('DOMContentLoaded', () => {
  // DOM Elements
  const tabSignIn = document.getElementById('tabSignIn');
  const tabSignUp = document.getElementById('tabSignUp');
  const tabSlider = document.querySelector('.tab-slider');
  
  const cardTitle = document.querySelector('.card-title');
  const cardSubtitle = document.querySelector('.card-subtitle');
  const submitBtnText = document.getElementById('submitBtnText');
  const checkboxText = document.getElementById('checkboxText');
  
  const nameFieldGroup = document.getElementById('nameFieldGroup');
  const fullNameInput = document.getElementById('fullName');
  const emailInput = document.getElementById('email');
  const passwordInput = document.getElementById('password');
  const rememberCheckbox = document.getElementById('rememberMe');
  
  const nameError = document.getElementById('nameError');
  const emailError = document.getElementById('emailError');
  const passwordError = document.getElementById('passwordError');
  const statusAlert = document.getElementById('statusAlert');
  
  const authForm = document.getElementById('authForm');
  const submitBtn = document.getElementById('submitBtn');
  const togglePasswordBtn = document.getElementById('togglePasswordBtn');
  const glassCard = document.getElementById('glassCard');

  let currentMode = 'signin'; // 'signin' or 'signup'

  // ==========================================
  // Mode Switching (Sign In / Sign Up)
  // ==========================================
  function switchMode(mode) {
    currentMode = mode;
    clearErrors();
    statusAlert.className = 'status-alert';
    statusAlert.textContent = '';

    if (mode === 'signin') {
      tabSignIn.classList.add('active');
      tabSignUp.classList.remove('active');
      tabSignIn.setAttribute('aria-selected', 'true');
      tabSignUp.setAttribute('aria-selected', 'false');
      tabSlider.style.transform = 'translateX(0%)';

      cardTitle.textContent = 'Welcome Back';
      cardSubtitle.textContent = 'Enter your credentials to access your workspace';
      submitBtnText.textContent = 'Sign In';
      checkboxText.textContent = 'Remember for 30 days';
      nameFieldGroup.classList.remove('active');
      fullNameInput.removeAttribute('required');
    } else {
      tabSignUp.classList.add('active');
      tabSignIn.classList.remove('active');
      tabSignUp.setAttribute('aria-selected', 'true');
      tabSignIn.setAttribute('aria-selected', 'false');
      tabSlider.style.transform = 'translateX(100%)';

      cardTitle.textContent = 'Create Account';
      cardSubtitle.textContent = 'Join in seconds and elevate your workflow';
      submitBtnText.textContent = 'Get Started';
      checkboxText.textContent = 'I agree to the Terms & Privacy Policy';
      nameFieldGroup.classList.add('active');
      fullNameInput.setAttribute('required', 'true');
    }
  }

  tabSignIn.addEventListener('click', () => switchMode('signin'));
  tabSignUp.addEventListener('click', () => switchMode('signup'));

  // ==========================================
  // Password Visibility Toggle
  // ==========================================
  togglePasswordBtn.addEventListener('click', () => {
    const isPassword = passwordInput.getAttribute('type') === 'password';
    passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
    togglePasswordBtn.classList.toggle('active', isPassword);
  });

  // ==========================================
  // Input Validation & Error Handling
  // ==========================================
  function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }

  function setFieldError(inputElement, errorElement, message) {
    const container = inputElement.closest('.input-container');
    container.classList.add('has-error');
    errorElement.textContent = message;
    errorElement.classList.add('active');
  }

  function clearFieldError(inputElement, errorElement) {
    const container = inputElement.closest('.input-container');
    container.classList.remove('has-error');
    errorElement.textContent = '';
    errorElement.classList.remove('active');
  }

  function clearErrors() {
    clearFieldError(fullNameInput, nameError);
    clearFieldError(emailInput, emailError);
    clearFieldError(passwordInput, passwordError);
  }

  // Real-time error clearing when user types
  [fullNameInput, emailInput, passwordInput].forEach(input => {
    input.addEventListener('input', () => {
      const container = input.closest('.input-container');
      container.classList.remove('has-error');
      const err = input.closest('.input-group').querySelector('.field-error');
      if (err) {
        err.textContent = '';
        err.classList.remove('active');
      }
    });
  });

  // ==========================================
  // Form Submission
  // ==========================================
  authForm.addEventListener('submit', (e) => {
    e.preventDefault();
    clearErrors();

    let isValid = true;
    const emailValue = emailInput.value.trim();
    const passwordValue = passwordInput.value;
    const nameValue = fullNameInput.value.trim();

    // Sign up full name check
    if (currentMode === 'signup') {
      if (!nameValue) {
        setFieldError(fullNameInput, nameError, 'Please enter your full name');
        isValid = false;
      } else if (nameValue.length < 2) {
        setFieldError(fullNameInput, nameError, 'Name must be at least 2 characters');
        isValid = false;
      }
    }

    // Email check
    if (!emailValue) {
      setFieldError(emailInput, emailError, 'Email address is required');
      isValid = false;
    } else if (!validateEmail(emailValue)) {
      setFieldError(emailInput, emailError, 'Please enter a valid email address');
      isValid = false;
    }

    // Password check
    if (!passwordValue) {
      setFieldError(passwordInput, passwordError, 'Password is required');
      isValid = false;
    } else if (passwordValue.length < 6) {
      setFieldError(passwordInput, passwordError, 'Password must be at least 6 characters');
      isValid = false;
    }

    if (!isValid) return;

    // Loading State Simulation
    submitBtn.classList.add('loading');
    submitBtn.disabled = true;

    setTimeout(() => {
      submitBtn.classList.remove('loading');
      submitBtn.disabled = false;

      statusAlert.className = 'status-alert success';
      if (currentMode === 'signin') {
        statusAlert.textContent = `Welcome back! Signed in as ${emailValue}`;
      } else {
        statusAlert.textContent = `Account created successfully for ${nameValue}!`;
      }
    }, 1200);
  });

  // ==========================================
  // Vector Background Image Presets & Switcher
  // ==========================================
  const vectorPresets = {
    'geometric-hexagons': {
      name: 'Geometric Hexagons',
      url: 'https://raw.githubusercontent.com/CorruptedArk/svg-wallpapers/master/rainbow_hexagon_and_lines.svg',
      fallback: 'assets/vectors/geometric-hexagons.svg'
    },
    'cosmic-void': {
      name: 'Cosmic Nebula',
      url: 'https://raw.githubusercontent.com/CorruptedArk/svg-wallpapers/master/black_hole_flat.svg',
      fallback: 'assets/vectors/cosmic-void.svg'
    },
    'tech-code': {
      name: 'Cyber Code Matrix',
      url: 'https://raw.githubusercontent.com/Mikluki/awesome_svg_wallpaper/main/4_code/code.svg',
      fallback: 'assets/vectors/tech-code.svg'
    },
    'abstract-waves': {
      name: 'Abstract Waves',
      url: 'https://raw.githubusercontent.com/CorruptedArk/svg-wallpapers/master/winter_abstract.svg',
      fallback: 'assets/vectors/abstract-waves.svg'
    },
    'celestial-dust': {
      name: 'Celestial Dust',
      url: 'https://raw.githubusercontent.com/Mikluki/awesome_svg_wallpaper/main/0_pink_planet_dust/2_pink_dust_dark.svg',
      fallback: 'assets/vectors/celestial-dust.svg'
    }
  };

  const vectorSelect = document.getElementById('vectorImageSelect');

  function applyVectorBackground(key) {
    const preset = vectorPresets[key] || vectorPresets['geometric-hexagons'];
    document.documentElement.style.setProperty('--bg-vector-image', `url('${preset.url}')`);
    document.documentElement.style.setProperty('--bg-vector-fallback', `url('${preset.fallback}')`);
    try {
      localStorage.setItem('portal_vector_bg', key);
    } catch (e) {
      // Storage access error handling
    }
  }

  if (vectorSelect) {
    try {
      const savedPreset = localStorage.getItem('portal_vector_bg');
      if (savedPreset && vectorPresets[savedPreset]) {
        vectorSelect.value = savedPreset;
        applyVectorBackground(savedPreset);
      }
    } catch (e) {
      // Storage access error handling
    }

    vectorSelect.addEventListener('change', (e) => {
      applyVectorBackground(e.target.value);
    });
  }

  // ==========================================
  // Interactive 3D Card Tilt Effect
  // ==========================================
  if (window.matchMedia('(min-width: 768px)').matches) {
    let cardRect = null;

    const updateCardRect = () => {
      cardRect = glassCard.getBoundingClientRect();
    };

    window.addEventListener('resize', updateCardRect);
    window.addEventListener('scroll', updateCardRect);

    document.addEventListener('mousemove', (e) => {
      if (!cardRect) cardRect = glassCard.getBoundingClientRect();

      const cardCenterX = cardRect.left + cardRect.width / 2;
      const cardCenterY = cardRect.top + cardRect.height / 2;

      const deltaX = e.clientX - cardCenterX;
      const deltaY = e.clientY - cardCenterY;

      const distance = Math.hypot(deltaX, deltaY);
      const maxDistance = 600;

      if (distance < maxDistance) {
        const tiltX = -(deltaY / 28).toFixed(2);
        const tiltY = (deltaX / 28).toFixed(2);
        glassCard.style.transform = `perspective(1000px) rotateX(${tiltX}deg) rotateY(${tiltY}deg) scale3d(1.01, 1.01, 1.01)`;
      } else {
        glassCard.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)';
      }
    });

    document.addEventListener('mouseleave', () => {
      glassCard.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)';
    });
  }
});
