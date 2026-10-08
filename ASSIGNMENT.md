# 🎓 Git & Version Control Team Assignment: Glassmorphism Portal Enhancements

Welcome to the team assignment! In this project, each student will practice real-world Git workflows by building a distinct feature for the Glassmorphism Authentication Portal on an isolated Git branch.

---

## 🧭 The Git Workflow (Rules for Everyone)

Each student **must follow this exact Git branching lifecycle**:

```bash
# 1. Clone the repository
git clone https://github.com/Wycliffemoenga/M25-LOGIN-ASIGNMENT.git
cd M25-LOGIN-ASIGNMENT

# 2. Make sure you are on the latest main branch
git checkout main
git pull origin main

# 3. Create and switch to your feature branch (use your assigned branch name)
git checkout -b feature/<your-assigned-feature>

# 4. Do your work, write clean code, and test in the browser
# 5. Check what changed
git status
git diff

# 6. Stage and commit with descriptive commit messages
git add .
git commit -m "feat: implement <description of what you built>"

# 7. Push your branch to GitHub
git push -u origin feature/<your-assigned-feature>

# 8. Open a Pull Request (PR) on GitHub against the `main` branch for code review
```

---

## 📋 Student Feature Assignments

### 👤 BRENDA: Real-time Password Strength Meter
- **Branch Name**: `feature/password-strength-meter`
- **Objective**: Add dynamic visual feedback under the password field in the Sign Up tab.
- **Requirements**:
  1. Add a multi-segment progress bar or continuous meter bar beneath the password input.
  2. Evaluate strength based on:
     - Minimum length (8+ characters)
     - Mixed case letters (uppercase and lowercase)
     - Numbers
     - Special characters (`!@#$%^&*`)
  3. Change the bar color dynamically: Red (Weak) ➔ Orange (Fair) ➔ Green (Strong).
  4. Display helpful helper text beneath (e.g., *"Add a special character to make it stronger"*).

---

### 👤 ABIGAEL: "Forgot Password" Glassmorphic Modal
- **Branch Name**: `feature/forgot-password-modal`
- **Objective**: Make the "Forgot?" link interactive by opening a frosted glass dialog.
- **Requirements**:
  1. Add an overlay modal matching the frosted glassmorphism aesthetic.
  2. Include an email input field and a "Send Reset Link" button.
  3. Add a close button (`×`) and allow closing by clicking outside the modal or pressing the `Esc` key.
  4. On submit, validate the email and show a success confirmation message (*"Password reset link sent to your inbox!"*).

---

### 👤 DOREEN: Multi-Theme Glass Switcher (Dark / Light / Cyberpunk)
- **Branch Name**: `feature/theme-switcher`
- **Objective**: Add a theme switcher toggle in the top-right corner.
- **Requirements**:
  1. Place a sleek floating glass pill button with theme icons (Sun ☀️, Moon 🌙, Neon ⚡).
  2. Support at least 2 or 3 distinct color modes:
     - **Cosmic Dark** (Default)
     - **Frosted Pearl / Dawn** (Light glassmorphism with soft pastel ambient blobs)
     - **Cyberpunk Neon** (Electric cyan and hot pink neon glow)
  3. Save the user's selected theme preference in `localStorage` so it persists on page reload.

---

### 👤 DAPHINE: Two-Factor / MFA Verification Step
- **Branch Name**: `feature/mfa-verification`
- **Objective**: Implement a step 2 verification screen after login.
- **Requirements**:
  1. Once the user submits valid login credentials, smoothly transition the form into a 6-digit OTP code verification card.
  2. Implement 6 separate single-digit input boxes that auto-focus the next box as the user types and handle `Backspace` smoothly.
  3. Include a countdown timer: *"Resend code in 45s"*.
  4. Provide a "Verify & Continue" button with success and failure feedback.

---

### 👤 DOMNIC: Interactive Floating Particle Canvas
- **Branch Name**: `feature/interactive-canvas-background`
- **Objective**: Enhance the ambient background with interactive particles.
- **Requirements**:
  1. Add an HTML5 `<canvas>` element in the background beneath the glass card.
  2. Render subtle glowing particles or interconnected nodes that float smoothly.
  3. Make particles gently react to the user's mouse movement (repel or gravitate slightly).
  4. Add a discreet glass switch/button to toggle particles on/off for performance.

---

### 👤 EMMANUEL: Remember Me & User Session Persistence
- **Branch Name**: `feature/remember-me-storage`
- **Objective**: Make the "Remember for 30 days" feature functional using web storage.
- **Requirements**:
  1. When "Remember for 30 days" is checked upon login, store the user's email safely in `localStorage`.
  2. When the page is reloaded, automatically pre-fill the email field and check the checkbox.
  3. If a remembered user exists, display a subtle glass avatar badge above the title with the user's initials and a "Not you? Switch account" link to clear the saved session.

---

## 🏆 Pull Request (PR) & Code Review Checklist

Before any branch is merged into `main`, team members must review each other's Pull Requests against these standards:
- [ ] The code is on the correct branch (never directly committed to `main`).
- [ ] HTML is semantic and valid.
- [ ] Glassmorphism design consistency is preserved (proper blur, shadows, and contrast).
- [ ] No broken CSS or JavaScript errors in the browser console.
- [ ] Feature works responsively on mobile and desktop.
