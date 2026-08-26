<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Signup</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background: linear-gradient(135deg, #667eea, #764ba2); min-height: 100vh; }
        .signup-card { border-radius: 15px; padding: 30px; background: #fff; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
        .signup-title { font-weight: bold; color: #333; }
        .btn-custom { background: #667eea; color: #fff; border-radius: 8px; }
        .btn-custom:hover { background: #5a67d8; }
        .link { text-decoration: none; color: #667eea; }
        .type-toggle .btn { border-radius: 8px; }
        .type-toggle .btn.active { background: #667eea; color: #fff; border-color: #667eea; }
        #otpSection { display: none; }
        #businessFields { display: none; }
    </style>
</head>

<body>

<div class="container d-flex justify-content-center align-items-center py-5">

    <div class="col-md-5">
        <div class="signup-card">

            <h3 class="text-center mb-4 signup-title">Sign Up</h3>

            @if($errors->any())
                <div class="alert alert-danger">
                    @foreach($errors->all() as $error)
                        <p class="mb-1">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <form action="{{ url('signup_user') }}" method="POST" id="signupForm">
                @csrf
                <input type="hidden" name="redirect" value="{{ request()->query('redirect') }}">

                <!-- Account type toggle -->
                <div class="mb-3">
                    <label class="d-block">Account Type</label>
                    <div class="btn-group w-100 type-toggle" role="group">
                        <button type="button" class="btn btn-outline-primary active" id="btnPersonal" onclick="setAccountType('personal')">Personal</button>
                        <button type="button" class="btn btn-outline-primary" id="btnBusiness" onclick="setAccountType('business')">Business</button>
                    </div>
                    <input type="hidden" name="account_type" id="account_type" value="{{ old('account_type', 'personal') }}">
                </div>

                <div class="mb-3">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Enter name" required>
                </div>

                <!-- Business-only fields -->
                <div id="businessFields">
                    <div class="mb-3">
                        <label>Company Name</label>
                        <input type="text" name="company_name" class="form-control" value="{{ old('company_name') }}" placeholder="Enter company name">
                    </div>
                    <div class="mb-3">
                        <label>GST Number</label>
                        <input type="text" name="gst_no" class="form-control" value="{{ old('gst_no') }}" placeholder="Enter company name">
                    </div>
                    <div class="mb-3">
                        <label>Industry</label>
                        <select name="industry" class="form-control">
                            <option value="">Select Industry</option>
                            <option value="IT">IT</option>
                            <option value="Software">Software</option>
                            <option value="Retail">Retail</option>
                            <option value="E-commerce">E-commerce</option>
                            <option value="Manufacturing">Manufacturing</option>
                            <option value="Construction">Construction</option>
                            <option value="Real Estate">Real Estate</option>
                            <option value="Healthcare">Healthcare</option>
                            <option value="Pharmaceutical">Pharmaceutical</option>
                            <option value="Education">Education</option>
                            <option value="Finance & Banking">Finance & Banking</option>
                            <option value="Insurance">Insurance</option>
                            <option value="Automobile">Automobile</option>
                            <option value="Telecommunications">Telecommunications</option>
                            <option value="Media & Entertainment">Media & Entertainment</option>
                            <option value="Travel & Tourism">Travel & Tourism</option>
                            <option value="Hospitality">Hospitality</option>
                            <option value="Food & Beverage">Food & Beverage</option>
                            <option value="Logistics & Transportation">Logistics & Transportation</option>
                            <option value="Agriculture">Agriculture</option>
                            <option value="Government">Government</option>
                            <option value="Consulting">Consulting</option>
                            <option value="Marketing & Advertising">Marketing & Advertising</option>
                            <option value="Legal Services">Legal Services</option>
                            <option value="Energy">Energy</option>
                            <option value="Textile">Textile</option>
                            <option value="Import & Export">Import & Export</option>
                            <option value="Wholesale">Wholesale</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label>Email</label>
                    <div class="input-group">
                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" placeholder="Enter email" required>
                        <button type="button" class="btn btn-outline-secondary" id="sendOtpBtn" onclick="sendOtp()">Send OTP</button>
                    </div>
                    <small class="text-muted" id="otpStatus"></small>
                </div>

                <div class="mb-3">
                    <label>Mobile Number</label>
                    <input type="text" name="mobile_number" id="mobile_number" class="form-control" value="{{ old('mobile_number') }}" placeholder="Enter mobile number" required>
                </div>

                <div class="mb-3" id="otpSection">
                    <label>Enter OTP (sent to your email)</label>
                    <input type="text" name="otp" id="otp" class="form-control" placeholder="6-digit OTP" maxlength="6">
                </div>

                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
                </div>

                <div class="mb-3">
                    <label>Confirm Password</label>
                    <input type="password" id="confirmPassword" name="password_confirmation" class="form-control" placeholder="Confirm password" required>
                </div>

                <div class="mb-3">
                    <input type="checkbox" onclick="togglePassword()"> Show Password
                </div>

                <div class="d-grid mb-3">
                    <button class="btn btn-custom" type="submit" id="submitBtn" disabled>Sign Up</button>
                </div>

                <div class="text-center">
                    Already have an account?
                    <a href="{{ url('login') }}" class="link">Login</a>
                </div>

            </form>

        </div>
    </div>

</div>

<script>
function togglePassword() {
    let pass = document.getElementById("password");
    let confirm = document.getElementById("confirmPassword");
    pass.type = pass.type === "password" ? "text" : "password";
    confirm.type = confirm.type === "password" ? "text" : "password";
}

function setAccountType(type) {
    document.getElementById('account_type').value = type;
    document.getElementById('btnPersonal').classList.toggle('active', type === 'personal');
    document.getElementById('btnBusiness').classList.toggle('active', type === 'business');
    document.getElementById('businessFields').style.display = type === 'business' ? 'block' : 'none';

    // required only when business
    document.querySelector('[name="company_name"]').required = (type === 'business');
    document.querySelector('[name="gst_no"]').required = (type === 'business');
    document.querySelector('[name="industry"]').required = (type === 'business');
}

// init on load (handles old() re-render after validation error)
setAccountType(document.getElementById('account_type').value);

function sendOtp() {
    const email = document.getElementById('email').value.trim();
    const status = document.getElementById('otpStatus');
    const btn = document.getElementById('sendOtpBtn');

    if (!email) {
        status.textContent = 'Enter your email first.';
        status.className = 'text-danger';
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Sending...';

    fetch("{{ route('send.otp') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
        },
        body: JSON.stringify({ email: email })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.textContent = 'Resend OTP';
        if (data.success) {
            document.getElementById('otpSection').style.display = 'block';
            document.getElementById('submitBtn').disabled = false;
            status.textContent = 'OTP sent to your email.';
            status.className = 'text-success';
        } else {
            status.textContent = data.message || 'Failed to send OTP.';
            status.className = 'text-danger';
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.textContent = 'Send OTP';
        status.textContent = 'Something went wrong. Try again.';
        status.className = 'text-danger';
    });
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>