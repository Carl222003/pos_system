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
/* Enhanced Modal Animations and Effects */
@keyframes fadeInUp {
    from { 
        opacity: 0; 
        transform: translateY(30px); 
    }
    to { 
        opacity: 1; 
        transform: translateY(0); 
    }
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

@keyframes slideInRight {
    from { 
        opacity: 0; 
        transform: translateX(30px); 
    }
    to { 
        opacity: 1; 
        transform: translateX(0); 
    }
}

/* Modal Animation Classes */
.modal.fade .modal-dialog {
    animation: fadeInUp 0.6s ease-out;
}

.verification-icon {
    animation: pulse 2s infinite;
}

/* Enhanced Button Hover Effects */
#modalVerifyBtn:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 12px 35px rgba(40, 167, 69, 0.4) !important;
}

#modalResendBtn:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 10px 30px rgba(108, 117, 125, 0.4) !important;
}

/* Enhanced Input Focus Effects */
#modalVerificationCode:focus {
    transform: scale(1.02);
    box-shadow: 0 12px 35px rgba(220, 53, 69, 0.25) !important;
    border-color: #28a745 !important;
}

/* Timer Animation */
#modalVerificationTimer {
    animation: pulse 2s infinite;
}

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
  // Clean, minimalist modal design matching the system style
  const modalHtml = `
    <div class="modal fade" id="enhancedFeedbackModal" tabindex="-1" aria-labelledby="enhancedFeedbackModalLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border: none; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); overflow: hidden; background: white;">
          
          <div class="modal-body" style="padding: 40px 30px; text-align: center;">
            <!-- Success Icon -->
            <div class="text-center mb-4">
              <div style="width: 80px; height: 80px; background: #e8f5e8; border: 2px solid #90EE90; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto;">
                <i class="fas ${type === 'error' ? 'fa-exclamation-triangle' : type === 'success' ? 'fa-check' : 'fa-info-circle'}" style="color: ${type === 'error' ? '#dc3545' : type === 'success' ? '#28a745' : '#ffc107'}; font-size: 2rem;"></i>
              </div>
              
              <!-- Title -->
              <h4 style="color: #333; font-weight: 600; font-size: 1.5rem; margin-bottom: 15px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
                ${title}
              </h4>
              
              <!-- Message -->
              <p style="color: #666; font-size: 1rem; line-height: 1.5; margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
                ${text}
              </p>
            </div>
            
            <!-- Action Button -->
            <div class="mt-4">
              <button type="button" class="btn" id="enhancedFeedbackBtn" style="background: #A04030; border: none; color: white; border-radius: 6px; padding: 12px 30px; font-weight: 500; font-size: 1rem; text-transform: uppercase; letter-spacing: 0.5px; transition: background-color 0.2s ease;">
                OK
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  `;
  
  // Remove existing modal if any
  $('#enhancedFeedbackModal').remove();
  
  // Add modal to body
  $('body').append(modalHtml);
  
  // Show modal
  const modal = new bootstrap.Modal(document.getElementById('enhancedFeedbackModal'));
  modal.show();
  
  // Handle button click
  $('#enhancedFeedbackBtn').on('click', function() {
    modal.hide();
    // Auto-redirect for success messages
    if (type === 'success' && title.includes('Success')) {
      setTimeout(function() {
        window.location.href = 'user.php';
      }, 500);
    }
  });
  
  // Auto-hide success modals after 3 seconds
  if (type === 'success') {
    setTimeout(function() {
      modal.hide();
      if (title.includes('Success')) {
        setTimeout(function() {
          window.location.href = 'user.php';
        }, 500);
      }
    }, 3000);
  }
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
    // Enhanced modal design with effects, animations, and close button
    const modalHtml = `
        <div class="modal fade" id="emailVerificationModal" tabindex="-1" aria-labelledby="emailVerificationModalLabel" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.12), 0 4px 16px rgba(0,0,0,0.08); overflow: hidden; background: white; position: relative; transform: scale(0.95); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);">
                    
                    <!-- Close Button with Hover Effects -->
                    <button type="button" class="btn-close-custom" id="modalCloseBtn" 
                            style="position: absolute; top: 15px; right: 15px; width: 32px; height: 32px; background: rgba(0,0,0,0.1); border: none; border-radius: 50%; color: #666; font-size: 16px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease; z-index: 10; backdrop-filter: blur(10px);">
                        <i class="fas fa-times"></i>
                    </button>
                    
                    <div class="modal-body" style="padding: 40px 30px; text-align: center;">
                        <!-- Enhanced Email Icon with Animation -->
                        <div class="text-center mb-4">
                            <div class="icon-container" style="width: 90px; height: 90px; background: linear-gradient(135deg, #e8f5e8, #d4edda); border: 3px solid #90EE90; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px auto; position: relative; transition: all 0.3s ease; box-shadow: 0 4px 20px rgba(40, 167, 69, 0.2);">
                                <i class="fas fa-envelope" style="color: #28a745; font-size: 2.2rem; transition: all 0.3s ease;"></i>
                                <!-- Floating particles around icon -->
                                <div style="position: absolute; top: -5px; left: -5px; width: 10px; height: 10px; background: #28a745; border-radius: 50%; opacity: 0.6; animation: float 3s ease-in-out infinite;"></div>
                                <div style="position: absolute; top: -3px; right: -3px; width: 8px; height: 8px; background: #20c997; border-radius: 50%; opacity: 0.4; animation: float 3s ease-in-out infinite 0.5s;"></div>
                                <div style="position: absolute; bottom: -4px; left: 50%; transform: translateX(-50%); width: 6px; height: 6px; background: #90EE90; border-radius: 50%; opacity: 0.5; animation: float 3s ease-in-out infinite 1s;"></div>
                            </div>
                            
                            <!-- Enhanced Title with Typing Effect -->
                            <h4 class="typing-title" style="color: #333; font-weight: 700; font-size: 1.6rem; margin-bottom: 18px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; position: relative; overflow: hidden;">
                                Email Verification Required
                                <span class="typing-cursor" style="position: absolute; right: -2px; animation: blink 1s infinite;"></span>
                            </h4>
                            
                            <!-- Enhanced Message with Fade In -->
                            <p class="fade-in-text" style="color: #666; font-size: 1.05rem; line-height: 1.6; margin: 0 0 25px 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; opacity: 0; animation: fadeInUp 0.6s ease forwards 0.3s;">
                                ${message}
                            </p>
                            
                            <!-- Enhanced Email Info Box with Hover Effects -->
                            <div class="info-box" style="background: linear-gradient(135deg, #f8f9fa, #e9ecef); border: 2px solid #dee2e6; border-radius: 12px; padding: 22px; margin: 25px 0; text-align: center; transition: all 0.3s ease; cursor: pointer; position: relative; overflow: hidden;">
                                <div style="position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(40, 167, 69, 0.1), transparent); transition: left 0.5s ease;"></div>
                                <div style="display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 12px; position: relative; z-index: 1;">
                                    <i class="fas fa-envelope" style="color: #28a745; font-size: 1.1rem; transition: transform 0.3s ease;"></i>
                                    <span style="color: #495057; font-weight: 600; font-size: 1.05rem;">Verification Code Sent!</span>
                                </div>
                                <p style="color: #6c757d; margin: 0; font-size: 0.95rem; position: relative; z-index: 1;">
                                    A verification code has been sent to <strong style="color: #495057;">${email}</strong>
                                </p>
                            </div>
                            
                            ${testCode ? `
                            <!-- Enhanced Backup Code Box with Glow Effect -->
                            <div class="backup-box" style="background: linear-gradient(135deg, #fff3cd, #ffeaa7); border: 2px solid #ffc107; border-radius: 12px; padding: 22px; margin: 25px 0; text-align: center; transition: all 0.3s ease; position: relative; overflow: hidden; box-shadow: 0 4px 20px rgba(255, 193, 7, 0.2);">
                                <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(45deg, transparent 30%, rgba(255, 193, 7, 0.1) 50%, transparent 70%); animation: shimmer 2s infinite;"></div>
                                <div style="display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 15px; position: relative; z-index: 1;">
                                    <i class="fas fa-code" style="color: #856404; font-size: 1.1rem;"></i>
                                    <span style="color: #856404; font-weight: 600; font-size: 1.05rem;">Backup Code</span>
                                </div>
                                <div style="text-align: center; margin-bottom: 15px; position: relative; z-index: 1;">
                                    <div style="background: white; border: 2px solid #ffc107; border-radius: 8px; padding: 18px; margin: 10px 0; display: inline-block; transition: all 0.3s ease; box-shadow: 0 2px 10px rgba(255, 193, 7, 0.2);">
                                        <span style="font-family: 'Courier New', monospace; font-size: 1.6rem; font-weight: 700; color: #856404; letter-spacing: 0.4rem; text-shadow: 0 1px 2px rgba(133, 100, 4, 0.1);">${testCode}</span>
                                    </div>
                                </div>
                                <p style="color: #856404; margin: 0; font-size: 0.9rem; font-style: italic; position: relative; z-index: 1;">
                                    Check your email first! Use this code only if email doesn't arrive.
                                </p>
                            </div>
                            ` : ''}
                        </div>
                        
                        <!-- Enhanced Code Input Section with Focus Effects -->
                        <div class="form-group mb-4">
                            <label for="modalVerificationCode" class="form-label" style="color: #495057; font-weight: 600; font-size: 1.05rem; margin-bottom: 18px; text-align: center; display: block; transition: color 0.3s ease;">
                                <i class="fas fa-key me-2" style="color: #28a745;"></i>Enter 6-Digit Verification Code
                            </label>
                            <div style="position: relative; max-width: 320px; margin: 0 auto;">
                                <input type="text" class="form-control text-center enhanced-input" id="modalVerificationCode" 
                                       placeholder="000000" maxlength="6" 
                                       style="font-size: 1.6rem; letter-spacing: 0.6rem; font-weight: 700; color: #495057; border: 3px solid #e9ecef; border-radius: 10px; padding: 18px; background: white; transition: all 0.3s ease; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                                <div class="input-focus-indicator" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; border: 3px solid transparent; border-radius: 10px; pointer-events: none; transition: all 0.3s ease;"></div>
                            </div>
                            <div class="form-text text-center mt-4">
                                <span id="modalVerificationTimer" style="color: #6c757d; font-weight: 600; font-size: 0.95rem; background: linear-gradient(135deg, #f8f9fa, #e9ecef); padding: 8px 16px; border-radius: 20px; display: inline-block; border: 1px solid #dee2e6; transition: all 0.3s ease;">
                                    <i class="fas fa-clock me-2" style="color: #28a745;"></i>Code expires in 10:00
                                </span>
                            </div>
                        </div>
                        
                        <div id="modalVerificationStatus" style="display: none;"></div>
                        
                        <!-- Enhanced Action Buttons with Hover Effects -->
                        <div class="d-grid gap-3" style="max-width: 320px; margin: 0 auto;">
                            <button type="button" class="btn enhanced-btn" id="modalVerifyBtn" 
                                    style="background: linear-gradient(135deg, #A04030, #8B3A2A); border: none; color: white; padding: 15px 35px; border-radius: 8px; font-weight: 600; font-size: 1.05rem; text-transform: uppercase; letter-spacing: 0.8px; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(160, 64, 48, 0.3); position: relative; overflow: hidden;">
                                <span style="position: relative; z-index: 1;">
                                    <i class="fas fa-check-circle me-2"></i>Verify Email
                                </span>
                                <div class="btn-ripple" style="position: absolute; top: 50%; left: 50%; width: 0; height: 0; background: rgba(255,255,255,0.3); border-radius: 50%; transform: translate(-50%, -50%); transition: all 0.6s ease;"></div>
                            </button>
                            <button type="button" class="btn enhanced-btn" id="modalResendBtn" 
                                    style="background: linear-gradient(135deg, #6c757d, #495057); border: none; color: white; padding: 12px 30px; border-radius: 8px; font-weight: 500; font-size: 1rem; transition: all 0.3s ease; box-shadow: 0 3px 12px rgba(108, 117, 125, 0.3); position: relative; overflow: hidden;">
                                <span style="position: relative; z-index: 1;">
                                    <i class="fas fa-redo me-2"></i>Resend Code
                                </span>
                                <div class="btn-ripple" style="position: absolute; top: 50%; left: 50%; width: 0; height: 0; background: rgba(255,255,255,0.3); border-radius: 50%; transform: translate(-50%, -50%); transition: all 0.6s ease;"></div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Enhanced CSS Animations and Effects -->
        <style>
            @keyframes float {
                0%, 100% { transform: translateY(0px); opacity: 0.6; }
                50% { transform: translateY(-10px); opacity: 1; }
            }
            
            @keyframes blink {
                0%, 50% { opacity: 1; }
                51%, 100% { opacity: 0; }
            }
            
            @keyframes fadeInUp {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            
            @keyframes shimmer {
                0% { transform: translateX(-100%); }
                100% { transform: translateX(100%); }
            }
            
            .typing-cursor::after {
                content: '|';
                color: #28a745;
                font-weight: bold;
            }
            
            .enhanced-input:focus {
                border-color: #28a745 !important;
                box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1) !important;
                transform: translateY(-2px);
            }
            
            .enhanced-input:focus + .input-focus-indicator {
                border-color: #28a745;
                box-shadow: 0 0 0 6px rgba(40, 167, 69, 0.1);
            }
            
            .info-box:hover {
                transform: translateY(-3px);
                box-shadow: 0 8px 25px rgba(40, 167, 69, 0.15);
                border-color: #28a745;
            }
            
            .info-box:hover::before {
                left: 100%;
            }
            
            .info-box:hover i {
                transform: scale(1.2);
            }
            
            .backup-box:hover {
                transform: translateY(-3px);
                box-shadow: 0 8px 25px rgba(255, 193, 7, 0.3);
                border-color: #ff9800;
            }
            
            .enhanced-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(0,0,0,0.2);
            }
            
            .enhanced-btn:active {
                transform: translateY(0);
            }
            
            .btn-ripple.active {
                width: 300px;
                height: 300px;
                opacity: 0;
            }
            
            .icon-container:hover {
                transform: scale(1.05);
                box-shadow: 0 6px 25px rgba(40, 167, 69, 0.3);
            }
            
            .icon-container:hover i {
                transform: scale(1.1);
            }
            
            .btn-close-custom:hover {
                background: rgba(220, 53, 69, 0.2) !important;
                color: #dc3545 !important;
                transform: scale(1.1) rotate(90deg);
            }
            
            .btn-close-custom:active {
                transform: scale(0.95) rotate(90deg);
            }
        </style>
    `;
    
    // Remove existing modal if any
    $('#emailVerificationModal').remove();
    
    // Add modal to body
    $('body').append(modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('emailVerificationModal'));
    modal.show();
    
    // Add close button functionality
    $('#modalCloseBtn').on('click', function() {
        modal.hide();
    });
    
    // Add button ripple effects
    $('.enhanced-btn').on('click', function(e) {
        const btn = $(this);
        const ripple = btn.find('.btn-ripple');
        
        // Get click position relative to button
        const rect = btn[0].getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        // Position ripple at click point
        ripple.css({
            left: x + 'px',
            top: y + 'px'
        });
        
        // Trigger ripple animation
        ripple.addClass('active');
        
        // Remove active class after animation
        setTimeout(() => {
            ripple.removeClass('active');
        }, 600);
    });
    
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
                $('#modalVerificationTimer').html(`
                    <span style="color: #dc3545; font-weight: 700; background: rgba(220, 53, 69, 0.1); padding: 8px 16px; border-radius: 20px; display: inline-block; animation: pulse 1s infinite;">
                        <i class="fas fa-exclamation-triangle me-1"></i>Verification code expired
                    </span>
                `);
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
        let alertStyle, icon, iconColor;
        
        if (type === 'success') {
            alertStyle = 'background: linear-gradient(135deg, #d4edda, #c3e6cb); border: 2px solid #28a745; color: #155724;';
            icon = 'check-circle';
            iconColor = '#28a745';
        } else if (type === 'error') {
            alertStyle = 'background: linear-gradient(135deg, #f8d7da, #f5c6cb); border: 2px solid #dc3545; color: #721c24;';
            icon = 'exclamation-circle';
            iconColor = '#dc3545';
        } else {
            alertStyle = 'background: linear-gradient(135deg, #fff3cd, #ffeaa7); border: 2px solid #ffc107; color: #856404;';
            icon = 'exclamation-triangle';
            iconColor = '#ffc107';
        }
        
        const statusHtml = `
            <div style="${alertStyle} border-radius: 15px; padding: 20px; margin: 20px 0; box-shadow: 0 8px 25px rgba(0,0,0,0.1); animation: slideInRight 0.5s ease-out;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="width: 40px; height: 40px; background: ${iconColor}; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.2);">
                        <i class="fas fa-${icon}" style="color: white; font-size: 1.2rem;"></i>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; font-size: 1.1rem; margin-bottom: 5px;">
                            ${type === 'success' ? '✅ Success!' : type === 'error' ? '❌ Error!' : '⚠️ Warning!'}
                        </div>
                        <div style="font-size: 1rem; line-height: 1.4;">
                            ${message}
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('#modalVerificationStatus').html(statusHtml).show();
    }
}

</script>

<?php include('footer.php'); ?>