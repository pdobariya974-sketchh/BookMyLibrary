const email = document.getElementById("email");
const password = document.getElementById("password");

const emailError = document.getElementById("emailError");
const passwordError = document.getElementById("passwordError");

const togglePassword = document.getElementById("togglePassword");

/* Email Validation */
function validateEmail() {
    const value = email.value.trim();
    const emailPattern = /^[^ ]+@[^ ]+\.[a-z]{2,}$/;

    if (value === "") {
        emailError.textContent = "Email is required";
        email.classList.add("error-input");
        return false;
    }
    else if (!emailPattern.test(value)) {
        emailError.textContent = "Enter a valid email address";
        email.classList.add("error-input");
        return false;
    }
    else {
        emailError.textContent = "";
        email.classList.remove("error-input");
        return true;
    }
}

/* Password Validation */
function validatePassword() {
    const value = password.value.trim();
    const digitPattern = /^[0-9]{6}$/;

    if (value === "") {
        passwordError.textContent = "Password is required";
        password.classList.add("error-input");
        return false;
    }
    else if (!digitPattern.test(value)) {
        passwordError.textContent = "Password must be exactly 6 digits";
        password.classList.add("error-input");
        return false;
    }
    else {
        passwordError.textContent = "";
        password.classList.remove("error-input");
        return true;
    }
}


/* Live validation */
email.addEventListener("input", validateEmail);
password.addEventListener("input", validatePassword);

/* Show / Hide password */
togglePassword.addEventListener("click", () => {
    if (password.type === "password") {
        password.type = "text";
        togglePassword.classList.replace("fa-eye", "fa-eye-slash");
    } else {
        password.type = "password";
        togglePassword.classList.replace("fa-eye-slash", "fa-eye");
    }
});

/* Submit Validation (SHOW ALL ERRORS) */
document.getElementById("loginForm").addEventListener("submit", function (e) {
    e.preventDefault();

    const isEmailValid = validateEmail();
    const isPasswordValid = validatePassword();

    if (isEmailValid && isPasswordValid) {
        // let the form submit to server for real authentication
        this.submit();
    }
});