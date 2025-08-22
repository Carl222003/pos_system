<?php 

require_once 'db_connect.php';
require_once 'auth_function.php';
require_once 'generate_employee_id.php';

checkAdminLogin();

$role = isset($_GET['role']) ? $_GET['role'] : '';

// Fetch active branches for the dropdown (exclude main branch for stockman)
if ($role === 'stockman') {
    // Exclude main branch for stockman since they have same role as admin there
    $stmt = $pdo->query("SELECT branch_id, branch_name, branch_code FROM pos_branch WHERE status = 'Active' AND branch_name NOT LIKE '%main%' AND branch_code != 'BR-QZF8K0'");
} else {
    // For cashiers and other roles, show all branches
    $stmt = $pdo->query("SELECT branch_id, branch_name, branch_code FROM pos_branch WHERE status = 'Active'");
}
$branches = $stmt->fetchAll(PDO::FETCH_ASSOC);

include('header.php');
?>

<div class="container-fluid px-4">
    <h1 class="section-title"><span class="section-icon"><i class="fas fa-user-cog"></i></span>Add New <?php echo ucfirst($role); ?></h1>

    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user-plus me-1"></i>
                    <?php echo ucfirst($role); ?> Information
                </div>
                <div class="card-body">
                    <form id="addUserForm" method="POST" action="process_add_user.php" enctype="multipart/form-data">
                        <input type="hidden" name="user_type" value="<?php echo ucfirst($role); ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-4">Basic Information</h5>
                                
                                <div class="form-group mb-3">
                                    <label for="user_name" class="form-label">Full Name*</label>
                                    <input type="text" class="form-control" id="user_name" name="user_name" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="user_email" class="form-label">Email Address*</label>
                                    <input type="email" class="form-control" id="user_email" name="user_email" required>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        A verification code will be sent to this email after account creation
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="user_password" class="form-label">Password*</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="user_password" name="user_password" required>
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="contact_number" class="form-label">Contact Number*</label>
                                    <input type="tel" class="form-control" id="contact_number" name="contact_number" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="profile_image" class="form-label">Profile Image</label>
                                    <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                                </div>
                            </div>

                            <?php if ($role === 'cashier' || $role === 'stockman'): ?>
                            <div class="col-md-6">
                                <h5 class="mb-4">Work Information</h5>

                                <div class="form-group mb-3">
                                    <label for="branch_id" class="form-label">Assigned Branch*</label>
                                    <select class="form-select" id="branch_id" name="branch_id" required>
                                        <option value="">Select Branch</option>
                                        <?php foreach ($branches as $branch): ?>
                                            <option value="<?php echo $branch['branch_id']; ?>">
                                                <?php echo $branch['branch_name'] . ' (' . $branch['branch_code'] . ')'; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if ($role === 'stockman'): ?>
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>
                                            <strong>Note:</strong> Main branch is not available for stockmen as admin handles inventory management there.
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="employee_id" class="form-label">Employee ID</label>
                                    <input type="text" class="form-control" id="employee_id" name="employee_id" readonly style="background-color: #f8f9fa;">
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Employee ID will be automatically generated when form is submitted
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="date_hired" class="form-label">Date Hired*</label>
                                    <input type="date" class="form-control" id="date_hired" name="date_hired" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="emergency_contact" class="form-label">Emergency Contact Name*</label>
                                    <input type="text" class="form-control" id="emergency_contact" name="emergency_contact" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="emergency_number" class="form-label">Emergency Contact Number*</label>
                                    <input type="tel" class="form-control" id="emergency_number" name="emergency_number" required>
                                </div>
                            </div>

                            <div class="col-12">
                                <h5 class="mb-4 mt-3">Additional Information</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="address" class="form-label">Complete Address*</label>
                                            <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="notes" class="form-label">Additional Notes</label>
                                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save <?php echo ucfirst($role); ?>
                            </button>
                            <a href="user.php" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                        </div>  
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Form Styling */
.form-label {
    font-weight: 500;
    color: #566a7f;
}

.form-control, .form-select {
    border-radius: 0.5rem;
    border: 1px solid #d9dee3;
    padding: 0.5rem 1rem;
    font-size: 0.9375rem;
}

.form-control:focus, .form-select:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.25rem rgba(139, 69, 67, 0.25);
}

/* Card Styling */
.card {
    border: none;
    border-radius: 0.75rem;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(139, 69, 67, 0.15);
}

.card-header {
    background: #8B4543;
    color: #ffffff;
    padding: 1rem 1.25rem;
    border-radius: 0.75rem 0.75rem 0 0;
    font-weight: 500;
}

/* Button Styling */
.btn-primary {
    background-color: #8B4543;
    border-color: #8B4543;
}

.btn-primary:hover {
    background-color: #723937;
    border-color: #723937;
}

.btn-secondary {
    background-color: #8592a3;
    border-color: #8592a3;
}

.btn-secondary:hover {
    background-color: #6d788d;
    border-color: #6d788d;
}

/* Section Headers */
h5 {
    color: #566a7f;
    font-weight: 500;
    margin-bottom: 1.5rem;
}

.section-title {
    color: #8B4543;
    font-size: 2.2rem;
    font-weight: 700;
    letter-spacing: 0.7px;
    margin-bottom: 1.7rem;
    margin-top: 1.2rem;
    display: flex;
    align-items: center;
    gap: 0.7rem;
    position: relative;
    background: none;
    border: none;
    animation: fadeInDown 0.7s;
}
.section-title .section-icon {
    font-size: 1.5em;
    color: #8B4543;
    opacity: 0.92;
}
.section-title::after {
    content: '';
    display: block;
    position: absolute;
    left: 0;
    bottom: -7px;
    width: 100%;
    height: 5px;
    border-radius: 3px;
    background: linear-gradient(90deg, #8B4543 0%, #b97a6a 100%);
    opacity: 0.18;
}
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-18px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
function showFeedbackModal(type, title, text) {
  Swal.fire({
    icon: type,
    title: title,
    text: text,
    confirmButtonText: 'OK',
    customClass: { confirmButton: 'swal2-confirm-archive' },
    buttonsStyling: false
  });
}

function togglePassword() {
    const passwordInput = document.getElementById('user_password');
    const icon = document.querySelector('.fa-eye');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

$(document).ready(function() {
    // Set default date hired to today
    document.getElementById('date_hired').valueAsDate = new Date();
    
    // Set placeholder text for employee ID
    $('#employee_id').attr('placeholder', 'Will be generated automatically');

    // Form validation
    $('#addUserForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        $.ajax({
            url: 'process_add_user.php', // Force use of process_add_user.php
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    if (response.requires_verification) {
                        // Show email verification modal
                        showEmailVerificationModal(response.email, response.verification_id, response.message, response.verification_code);
                    } else {
                        showFeedbackModal('success', 'Success!', response.message);
                        setTimeout(function() {
                            window.location.href = 'user.php';
                        }, 1500);
                    }
                } else {
                    showFeedbackModal('error', 'Error!', response.message);
                }
            },
            error: function() {
                showFeedbackModal('error', 'Error!', 'An error occurred while saving the user.');
            }
        });
    });

    // Phone number formatting
    $('#contact_number, #emergency_number').on('input', function() {
        let number = $(this).val().replace(/\D/g, '');
        if (number.length > 10) {
            number = number.substring(0, 10);
        }
        $(this).val(number.replace(/(\d{3})(\d{3})(\d{4})/, '$1-$2-$3'));
    });
});

// Email verification modal functionality
function showEmailVerificationModal(email, verificationId, message, testCode = null) {
    // Create and show the verification modal
    const modalHtml = `
        <div class="modal fade" id="emailVerificationModal" tabindex="-1" aria-labelledby="emailVerificationModalLabel" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="emailVerificationModalLabel">
                            <i class="fas fa-envelope-circle-check me-2"></i>Email Verification Required
                        </h5>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            <div class="verification-icon mb-3">
                                <i class="fas fa-envelope fa-3x text-primary"></i>
                            </div>
                            <h6>Verification Required</h6>
                            <p class="text-muted">${message}</p>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-1"></i>
                                A verification code has been sent to <strong>${email}</strong>
                            </div>
                            ${testCode ? `
                            <div class="alert alert-warning">
                                <i class="fas fa-code me-1"></i>
                                <strong>Backup Code:</strong> <span class="fw-bold fs-5">${testCode}</span>
                                <br><small>📧 Check your email first! Use this code only if email doesn't arrive.</small>
                            </div>
                            ` : ''}
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="modalVerificationCode" class="form-label">Enter 6-Digit Verification Code</label>
                            <input type="text" class="form-control text-center" id="modalVerificationCode" 
                                   placeholder="000000" maxlength="6" style="font-size: 1.5rem; letter-spacing: 0.5rem;">
                            <div class="form-text text-center">
                                <span id="modalVerificationTimer">Code expires in 10:00</span>
                            </div>
                        </div>
                        
                        <div id="modalVerificationStatus" style="display: none;"></div>
                        
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-primary" id="modalVerifyBtn">
                                <i class="fas fa-check-circle me-1"></i> Verify Email
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="modalResendBtn">
                                <i class="fas fa-redo me-1"></i> Resend Code
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <small class="text-muted">
                            <i class="fas fa-shield-alt me-1"></i>
                            This verification step ensures the security and validates your email before account creation
                        </small>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    $('#emailVerificationModal').remove();
    
    // Add modal to body
    $('body').append(modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('emailVerificationModal'));
    modal.show();
    
    // Initialize verification functionality
    initModalVerification(email, verificationId);
}

function initModalVerification(email, verificationId) {
    let verificationTimer = null;
    let timeRemaining = 600; // 10 minutes
    
    // Start timer
    startModalTimer();
    
    // Auto-format code input
    $('#modalVerificationCode').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length > 6) {
            this.value = this.value.slice(0, 6);
        }
    });
    
    // Verify button click
    $('#modalVerifyBtn').on('click', function() {
        const code = $('#modalVerificationCode').val().trim();
        
        if (!code || code.length !== 6) {
            showModalStatus('error', 'Please enter a valid 6-digit verification code');
            return;
        }
        
        verifyModalCode(email, verificationId, code);
    });
    
    // Resend button click
    $('#modalResendBtn').on('click', function() {
        resendModalCode(email, verificationId);
    });
    
    function startModalTimer() {
        verificationTimer = setInterval(function() {
            const minutes = Math.floor(timeRemaining / 60);
            const seconds = timeRemaining % 60;
            const timeString = minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
            
            $('#modalVerificationTimer').html('Code expires in ' + timeString);
            
            timeRemaining--;
            
            if (timeRemaining < 0) {
                clearInterval(verificationTimer);
                $('#modalVerificationTimer').html('<span class="text-danger">Verification code expired</span>');
                $('#modalVerifyBtn').prop('disabled', true);
                showModalStatus('warning', 'Verification code has expired. Please request a new code.');
            }
        }, 1000);
    }
    
    function verifyModalCode(email, verificationId, code) {
        const btn = $('#modalVerifyBtn');
        const originalHtml = btn.html();
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Verifying...');
        
        $.ajax({
            url: 'create_verified_account.php',
            type: 'POST',
            data: { 
                email: email,
                verification_id: verificationId,
                verification_code: code
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showModalStatus('success', response.message);
                    
                    if (verificationTimer) {
                        clearInterval(verificationTimer);
                    }
                    
                    setTimeout(function() {
                        $('#emailVerificationModal').modal('hide');
                        window.location.href = 'user.php';
                    }, 2000);
                } else {
                    showModalStatus('error', response.message);
                    btn.prop('disabled', false).html(originalHtml);
                }
            },
            error: function() {
                showModalStatus('error', 'An error occurred while verifying the code');
                btn.prop('disabled', false).html(originalHtml);
            }
        });
    }
    
    function resendModalCode(email, verificationId) {
        const btn = $('#modalResendBtn');
        const originalHtml = btn.html();
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Sending...');
        
        $.ajax({
            url: 'resend_precreation_code.php',
            type: 'POST',
            data: { 
                email: email,
                verification_id: verificationId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    let message = 'Verification code sent successfully!';
                    if (response.test_mode && response.verification_code) {
                        message += ` <br><strong>Test Code:</strong> <span class="fw-bold">${response.verification_code}</span>`;
                    }
                    showModalStatus('success', message);
                    
                    // Reset timer
                    if (verificationTimer) {
                        clearInterval(verificationTimer);
                    }
                    timeRemaining = 600;
                    startModalTimer();
                    
                    $('#modalVerifyBtn').prop('disabled', false);
                } else {
                    showModalStatus('error', response.message);
                }
            },
            error: function() {
                showModalStatus('error', 'Failed to resend verification code');
            },
            complete: function() {
                btn.prop('disabled', false).html(originalHtml);
            }
        });
    }
    
    function showModalStatus(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'error' ? 'alert-danger' : 'alert-warning';
        const icon = type === 'success' ? 'check-circle' : 
                     type === 'error' ? 'exclamation-circle' : 'exclamation-triangle';
        
        $('#modalVerificationStatus').removeClass('alert-success alert-danger alert-warning')
            .addClass('alert ' + alertClass)
            .html('<i class="fas fa-' + icon + ' me-1"></i>' + message)
            .show();
    }
}

</script>

<?php include('footer.php'); ?>