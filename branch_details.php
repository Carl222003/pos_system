<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

include('header.php');
?>

<div class="container-fluid px-4">
    <h1 class="section-title"><span class="section-icon"><i class="fas fa-info-circle"></i></span>Branch Details</h1>
    
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-store me-1"></i>
                        Branch List
                    </div>
                </div>
                <div class="card-body">
                    <table id="branchTable" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Branch Code</th>
                                <th>Branch Name</th>
                                <th>Contact</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>Operating Hours</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Edit Branch Modal -->
<div class="modal fade" id="editBranchModal" tabindex="-1" aria-labelledby="editBranchModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl landscape-modal">
    <div class="modal-content enhanced-edit-branch-modal">
      <!-- Enhanced Modal Header -->
      <div class="modal-header">
        <div class="d-flex align-items-center">
          <div class="edit-icon me-3">
            <i class="fas fa-edit"></i>
          </div>
          <div>
            <h5 class="modal-title mb-0" id="editBranchModalLabel">Edit Branch</h5>
            <small class="text-light opacity-75">Update branch information</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close">
          <span class="close-x">×</span>
        </button>
      </div>
      
      <!-- Enhanced Modal Body -->
      <div class="modal-body">
        <form id="editBranchForm">
          <input type="hidden" name="branch_id" id="editBranchId">
          
          <div class="row g-4">
            <!-- Left Column - Basic Information -->
            <div class="col-lg-4">
              <div class="form-section">
                <h6 class="section-title">
                  <i class="fas fa-info-circle me-2"></i>Basic Information
                </h6>
                
                <div class="mb-4">
                  <label for="editBranchName" class="form-label">
                    <i class="fas fa-store me-1"></i>Branch Name
                  </label>
                  <input type="text" name="branch_name" id="editBranchName" class="form-control form-control-lg" required>
                  <div class="form-text">
                    <i class="fas fa-info-circle me-1"></i>
                    Enter the complete branch name
                  </div>
                </div>
                
                <div class="mb-4">
                  <label for="editBranchCode" class="form-label">
                    <i class="fas fa-barcode me-1"></i>Branch Code
                  </label>
                  <input type="text" name="branch_code" id="editBranchCode" class="form-control form-control-lg" required readonly>
                  <div class="form-text">
                    <i class="fas fa-info-circle me-1"></i>
                    Branch code is auto-generated and cannot be changed
                  </div>
                </div>
                
                <div class="mb-4">
                  <label for="editStatus" class="form-label">
                    <i class="fas fa-toggle-on me-1"></i>Status
                  </label>
                  <select name="status" id="editStatus" class="form-select form-select-lg" required>
                    <option value="Active">🟢 Active</option>
                    <option value="Inactive">🔴 Inactive</option>
                  </select>
                  <div class="form-text">
                    <i class="fas fa-info-circle me-1"></i>
                    Choose the current status of this branch
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Middle Column - Contact Information -->
            <div class="col-lg-4">
              <div class="form-section">
                <h6 class="section-title">
                  <i class="fas fa-address-card me-2"></i>Contact Information
                </h6>
                
                <div class="mb-4">
                  <label for="editContactNumber" class="form-label">
                    <i class="fas fa-phone me-1"></i>Contact Number
                  </label>
                  <input type="text" name="contact_number" id="editContactNumber" class="form-control form-control-lg" placeholder="Enter contact number">
                  <div class="form-text">
                    <i class="fas fa-info-circle me-1"></i>
                    Primary contact number for this branch
                  </div>
                </div>
                
                <div class="mb-4">
                  <label for="editEmail" class="form-label">
                    <i class="fas fa-envelope me-1"></i>Email Address
                  </label>
                  <input type="email" name="email" id="editEmail" class="form-control form-control-lg" placeholder="Enter email address">
                  <div class="form-text">
                    <i class="fas fa-info-circle me-1"></i>
                    Official email address for this branch
                  </div>
                </div>
                
                <div class="mb-4">
                  <label for="editOperatingHours" class="form-label">
                    <i class="fas fa-clock me-1"></i>Operating Hours
                  </label>
                  <input type="text" name="operating_hours" id="editOperatingHours" class="form-control form-control-lg" placeholder="e.g., 9:00 AM - 10:00 PM">
                  <div class="form-text">
                    <i class="fas fa-info-circle me-1"></i>
                    Business hours in format: Start Time - End Time
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Right Column - Address Information -->
            <div class="col-lg-4">
              <div class="form-section">
                <h6 class="section-title">
                  <i class="fas fa-map-marker-alt me-2"></i>Location Details
                </h6>
                
                <div class="mb-4">
                  <label for="editCompleteAddress" class="form-label">
                    <i class="fas fa-map-marker-alt me-1"></i>Complete Address
                  </label>
                  <textarea name="complete_address" id="editCompleteAddress" class="form-control form-control-lg" rows="5" placeholder="Enter complete address"></textarea>
                  <div class="form-text">
                    <i class="fas fa-info-circle me-1"></i>
                    Full physical address of the branch
                  </div>
                </div>
                
                <div class="branch-preview">
                  <h6 class="preview-title">
                    <i class="fas fa-eye me-2"></i>Branch Preview
                  </h6>
                  <div class="preview-card">
                    <div class="preview-header">
                      <span class="preview-name" id="previewBranchName">Branch Name</span>
                      <span class="preview-status" id="previewBranchStatus">🟢 Active</span>
                    </div>
                    <div class="preview-details">
                      <div class="preview-item">
                        <i class="fas fa-barcode"></i>
                        <span id="previewBranchCode">BR-XXXXX</span>
                      </div>
                      <div class="preview-item">
                        <i class="fas fa-phone"></i>
                        <span id="previewContact">Contact Number</span>
                      </div>
                      <div class="preview-item">
                        <i class="fas fa-clock"></i>
                        <span id="previewHours">Operating Hours</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
      
      <!-- Enhanced Modal Footer -->
      <div class="modal-footer">
        <div class="d-flex justify-content-between align-items-center w-100">
          <div class="keyboard-hints">
            <small class="text-muted">
              <i class="fas fa-keyboard me-1"></i>
              Ctrl+S to save • Esc to close
            </small>
          </div>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
              <i class="fas fa-times me-2"></i>Cancel
            </button>
            <button type="submit" class="btn btn-primary btn-lg" form="editBranchForm">
              <i class="fas fa-save me-2"></i>Update Branch
            </button>
          </div>
        </div>
      </div>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
/* Modern Card and Table Styling */
:root {
    --primary-color: #8B4543;
    --primary-dark: #723937;
    --primary-light: #A65D5D;
    --accent-color: #D4A59A;
    --text-light: #F3E9E7;
    --text-dark: #3C2A2A;
    --border-color: #C4B1B1;
    --hover-color: #F5EDED;
    --danger-color: #B33A3A;
    --success-color: #4A7C59;
    --warning-color: #C4804D;
}

.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(139, 69, 67, 0.15);
    border: none;
    border-radius: 0.75rem;
    background: #ffffff;
}

.card-header {
    background: var(--primary-color);
    color: var(--text-light);
    border-bottom: none;
    padding: 1.5rem;
    border-radius: 0.75rem 0.75rem 0 0;
}

.card-header i {
    color: var(--text-light);
}

.table {
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0 8px;
}

.table thead th {
    background-color: var(--hover-color);
    border: none;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    color: var(--primary-color);
    padding: 1rem;
    white-space: nowrap;
}

.table tbody tr {
    background: white;
    box-shadow: 0 2px 4px rgba(139, 69, 67, 0.05);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.table tbody tr:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(139, 69, 67, 0.1);
    background: var(--hover-color);
}

.table tbody td {
    padding: 1rem;
    border: none;
    background: transparent;
    vertical-align: middle;
}

.table tbody tr td:first-child {
    border-top-left-radius: 0.5rem;
    border-bottom-left-radius: 0.5rem;
}

.table tbody tr td:last-child {
    border-top-right-radius: 0.5rem;
    border-bottom-right-radius: 0.5rem;
}

/* Status Badges */
.badge {
    padding: 0.5rem 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: 0.35rem;
}

.badge.bg-success {
    background: var(--success-color) !important;
    color: white;
}

.badge.bg-danger {
    background: var(--danger-color) !important;
    color: white;
}

/* Enhanced Edit Branch Modal Styling */
.enhanced-edit-branch-modal {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: none;
    border-radius: 1rem;
    box-shadow: 0 20px 60px rgba(139, 69, 67, 0.3);
    backdrop-filter: blur(10px);
    animation: modalSlideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Landscape Modal Styling */
.landscape-modal {
    max-width: 1400px !important;
    min-height: 600px;
}

.landscape-modal .modal-body {
    padding: 2rem;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    min-height: 500px;
}

.landscape-modal .form-section {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.landscape-modal .form-section .mb-4:last-of-type {
    margin-bottom: 0 !important;
}

.enhanced-edit-branch-modal .modal-header {
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    border-bottom: none;
    padding: 1.5rem 2rem;
    border-radius: 1rem 1rem 0 0;
    position: relative;
    overflow: hidden;
}

.enhanced-edit-branch-modal .modal-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.1) 50%, transparent 70%);
    animation: shimmer 3s infinite;
}

.enhanced-edit-branch-modal .edit-icon {
    width: 50px;
    height: 50px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    animation: pulse 2s infinite;
}

.enhanced-edit-branch-modal .modal-title {
    color: white;
    font-weight: 700;
    font-size: 1.5rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.enhanced-edit-branch-modal .btn-close {
    width: 32px;
    height: 32px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    border: none;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    animation: glow 2s ease-in-out infinite;
}

.enhanced-edit-branch-modal .btn-close:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.1) rotate(45deg);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.enhanced-edit-branch-modal .close-x {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 1.2rem;
    font-weight: 700;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
}

.enhanced-edit-branch-modal .modal-body {
    padding: 2rem;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

.enhanced-edit-branch-modal .form-section {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(139, 69, 67, 0.1);
    border: 1px solid rgba(139, 69, 67, 0.1);
    transition: all 0.3s ease;
}

.enhanced-edit-branch-modal .form-section:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(139, 69, 67, 0.15);
}

.enhanced-edit-branch-modal .section-title {
    color: #8B4543;
    font-weight: 700;
    font-size: 1.1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid rgba(139, 69, 67, 0.2);
    position: relative;
}

.enhanced-edit-branch-modal .section-title::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 50px;
    height: 2px;
    background: linear-gradient(90deg, #8B4543, #A65D5D);
    animation: shimmer 2s infinite;
}

.enhanced-edit-branch-modal .form-label {
    color: #2c3e50;
    font-weight: 600;
    font-size: 0.95rem;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
}

.enhanced-edit-branch-modal .form-label i {
    color: #8B4543;
    margin-right: 0.5rem;
    font-size: 1rem;
}

.enhanced-edit-branch-modal .form-control,
.enhanced-edit-branch-modal .form-select {
    border: 2px solid rgba(139, 69, 67, 0.2);
    border-radius: 0.75rem;
    padding: 0.875rem 1rem;
    font-size: 1rem;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.enhanced-edit-branch-modal .form-control:focus,
.enhanced-edit-branch-modal .form-select:focus {
    border-color: #8B4543;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25), 0 8px 25px rgba(139, 69, 67, 0.15);
    transform: translateY(-2px);
    background: white;
}

.enhanced-edit-branch-modal .form-control[readonly] {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    color: #6c757d;
    cursor: not-allowed;
}

.enhanced-edit-branch-modal .form-text {
    color: #6c757d;
    font-size: 0.85rem;
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
}

.enhanced-edit-branch-modal .form-text i {
    color: #8B4543;
    margin-right: 0.25rem;
}

/* Modal Footer Styling */
.enhanced-edit-branch-modal .modal-footer {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-top: 1px solid rgba(139, 69, 67, 0.1);
    padding: 1.5rem 2rem;
    border-radius: 0 0 1rem 1rem;
}

.enhanced-edit-branch-modal .btn {
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    border-radius: 0.75rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    border: none;
    font-size: 1rem;
}

.enhanced-edit-branch-modal .btn-primary {
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(139, 69, 67, 0.3);
}

.enhanced-edit-branch-modal .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(139, 69, 67, 0.4);
    background: linear-gradient(135deg, #723937 0%, #5a2e2c 100%);
}

.enhanced-edit-branch-modal .btn-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white;
}

.enhanced-edit-branch-modal .btn-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(108, 117, 125, 0.4);
    background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
}

/* Keyboard Hints Styling */
.enhanced-edit-branch-modal .keyboard-hints {
    display: flex;
    align-items: center;
}

.enhanced-edit-branch-modal .keyboard-hints small {
    font-size: 0.8rem;
    color: #6c757d;
    opacity: 0.8;
    transition: all 0.3s ease;
}

.enhanced-edit-branch-modal .keyboard-hints:hover small {
    opacity: 1;
    color: #8B4543;
}

.enhanced-edit-branch-modal .keyboard-hints i {
    font-size: 0.9rem;
    margin-right: 0.25rem;
}

/* Branch Preview Styling */
.enhanced-edit-branch-modal .branch-preview {
    margin-top: 1.5rem;
    flex-grow: 1;
}

.enhanced-edit-branch-modal .preview-title {
    color: #8B4543;
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
}

.enhanced-edit-branch-modal .preview-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 2px solid rgba(139, 69, 67, 0.2);
    border-radius: 0.75rem;
    padding: 1.25rem;
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.enhanced-edit-branch-modal .preview-card:hover {
    border-color: rgba(139, 69, 67, 0.4);
    box-shadow: 0 4px 15px rgba(139, 69, 67, 0.1);
}

.enhanced-edit-branch-modal .preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid rgba(139, 69, 67, 0.2);
}

.enhanced-edit-branch-modal .preview-name {
    font-weight: 700;
    font-size: 1.1rem;
    color: #2c3e50;
}

.enhanced-edit-branch-modal .preview-status {
    font-size: 0.9rem;
    font-weight: 600;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    background: rgba(139, 69, 67, 0.1);
    color: #8B4543;
}

.enhanced-edit-branch-modal .preview-details {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.enhanced-edit-branch-modal .preview-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
    background: rgba(255, 255, 255, 0.5);
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}

.enhanced-edit-branch-modal .preview-item:hover {
    background: rgba(255, 255, 255, 0.8);
    transform: translateX(5px);
}

.enhanced-edit-branch-modal .preview-item i {
    color: #8B4543;
    font-size: 0.9rem;
    width: 16px;
    text-align: center;
}

.enhanced-edit-branch-modal .preview-item span {
    color: #2c3e50;
    font-size: 0.9rem;
    font-weight: 500;
}

/* Preview Animation */
.enhanced-edit-branch-modal .preview-updated {
    animation: previewUpdate 0.3s ease-in-out;
}

@keyframes previewUpdate {
    0% { transform: scale(1); }
    50% { transform: scale(1.02); }
    100% { transform: scale(1); }
}

/* Animations */
@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

@keyframes glow {
    0%, 100% { box-shadow: 0 0 5px rgba(255, 255, 255, 0.3); }
    50% { box-shadow: 0 0 20px rgba(255, 255, 255, 0.6); }
}
    background: var(--danger-color) !important;
    color: white;
}

/* Action Buttons */
.btn-group .btn, .btn {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 0.35rem;
    margin: 0 0.125rem;
    border: none;
    transition: all 0.2s ease;
}

.btn-warning {
    background: var(--warning-color);
    color: white;
}

.btn-warning:hover {
    background: darken(var(--warning-color), 10%);
    transform: translateY(-1px);
    box-shadow: 0 0.15rem 1.75rem 0 rgba(196, 128, 77, 0.15);
}

.btn-danger {
    background: var(--danger-color);
    color: white;
}

.btn-danger:hover {
    background: darken(var(--danger-color), 10%);
    transform: translateY(-1px);
    box-shadow: 0 0.15rem 1.75rem 0 rgba(179, 58, 58, 0.15);
}

/* Search and Length Menu */
.dataTables_wrapper {
    padding: 1.5rem;
}

.dataTables_length select {
    padding: 0.5rem 2.5rem 0.5rem 1rem;
    font-size: 0.875rem;
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    background-color: white;
    cursor: pointer;
}

.dataTables_filter input {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    background-color: white;
    min-width: 300px;
}

.dataTables_filter input:focus {
    border-color: var(--primary-color);
    outline: none;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 67, 0.25);
}

/* SweetAlert2 Custom Styles */
.swal2-popup {
    border-radius: 1rem;
    padding: 2rem;
}

.swal2-title {
    color: var(--text-dark);
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.swal2-html-container {
    color: #6c757d;
    font-size: 1rem;
    margin: 1rem 0;
}

.swal2-icon {
    border-color: var(--warning-color);
    color: var(--warning-color);
}

.swal2-confirm {
    background-color: var(--danger-color) !important;
    border-radius: 0.75rem !important;
    padding: 0.75rem 1.5rem !important;
    font-size: 1rem !important;
    font-weight: 500 !important;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(179, 58, 58, 0.15) !important;
}

.swal2-confirm:focus {
    box-shadow: 0 0 0 0.25rem rgba(179, 58, 58, 0.25) !important;
}

.swal2-cancel {
    background-color: #f8f9fa !important;
    color: var(--text-dark) !important;
    border-radius: 0.75rem !important;
    padding: 0.75rem 1.5rem !important;
    font-size: 1rem !important;
    font-weight: 500 !important;
    margin-right: 0.5rem !important;
}

.swal2-cancel:hover {
    background-color: #e9ecef !important;
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
  /* Center the modal content and adjust width for better appearance */
  @media (min-width: 992px) {
    #editBranchModal .modal-dialog {
      max-width: 650px;
    }
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

// Custom function for Archive button
function archiveBranchCustomAction(branchId) {
    console.log('Archive button clicked for branch ID:', branchId);
    // Add more custom actions here if needed
}

// Function to handle archive errors
function handleArchiveError(message) {
    showFeedbackModal('error', 'Error!', message);
    // Add more custom error handling here if needed
}

$(document).ready(function() {
    $('#branchTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "branch_ajax.php",
            "type": "GET"
        },
        "columns": [
            { "data": "branch_code" },
            { "data": "branch_name" },
            { "data": "contact_number" },
            { "data": "email" },
            { "data": "complete_address" },
            { "data": "operating_hours" },
            { 
                "data": "status",
                "render": function(data, type, row) {
                    return data === 'Active' ? 
                        '<span class="badge bg-success">Active</span>' : 
                        '<span class="badge bg-danger">Inactive</span>';
                }
            },
            {
                "data": null,
                "render": function(data, type, row) {
                    return `
                        <div class="text-center">
                            <a href="#" class="btn btn-warning btn-sm edit-branch-btn" data-id="${row.branch_id}" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button class="btn btn-secondary btn-sm archive-btn" data-id="${row.branch_id}" title="Archive">
                                <i class="fas fa-archive"></i>
                            </button>
                        </div>`;
                }
            }
        ],
        "order": [[1, "asc"]],
        "pageLength": 5,
        "pagingType": "simple", // Show only Previous/Next buttons
        "responsive": true
    });

    // Handle Delete Button Click
    $(document).on('click', '.archive-btn', function() {
        let branchId = $(this).data('id');
        
        // Custom function before confirmation dialog
        archiveBranchCustomAction(branchId);
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You can restore this branch from the archive.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#6c757d',
            cancelButtonColor: '#f8f9fa',
            confirmButtonText: '<i class="fas fa-box-archive me-2"></i>Yes, archive it!',
            cancelButtonText: '<i class="fas fa-times me-2"></i>Cancel',
            customClass: {
                confirmButton: 'btn btn-secondary btn-lg',
                cancelButton: 'btn btn-light btn-lg'
            },
            buttonsStyling: false,
            padding: '2rem',
            width: 400,
            showClass: {
                popup: 'animate__animated animate__fadeInDown animate__faster'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp animate__faster'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'archive_branch.php',
                    type: 'POST',
                    data: { id: branchId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#branchTable').DataTable().ajax.reload();
                            
                            showFeedbackModal('success', 'Archived!', 'Branch has been archived successfully.');
                        } else {
                            handleArchiveError(response.message || 'Failed to archive branch.');
                        }
                    },
                    error: function(xhr, status, error) {
                        let errorMsg = 'An error occurred while archiving the branch.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        handleArchiveError(errorMsg);
                    }
                });
            }
        });
    });

    // Live preview functionality for Edit Branch modal
    $('#editBranchModal').on('show.bs.modal', function() {
        // Initialize preview
        updateBranchPreview();
        
        // Add live preview listeners
        $('#editBranchName').on('input', updateBranchPreview);
        $('#editBranchCode').on('input', updateBranchPreview);
        $('#editContactNumber').on('input', updateBranchPreview);
        $('#editOperatingHours').on('input', updateBranchPreview);
        $('#editStatus').on('change', updateBranchPreview);
    });
    
    function updateBranchPreview() {
        const name = $('#editBranchName').val() || 'Branch Name';
        const code = $('#editBranchCode').val() || 'BR-XXXXX';
        const contact = $('#editContactNumber').val() || 'Contact Number';
        const hours = $('#editOperatingHours').val() || 'Operating Hours';
        const status = $('#editStatus').val();
        
        $('#previewBranchName').text(name);
        $('#previewBranchCode').text(code);
        $('#previewContact').text(contact);
        $('#previewHours').text(hours);
        $('#previewBranchStatus').text(status === 'Active' ? '🟢 Active' : '🔴 Inactive');
        
        // Add animation to preview
        $('.preview-card').addClass('preview-updated');
        setTimeout(() => {
            $('.preview-card').removeClass('preview-updated');
        }, 300);
    }
    
    // Edit button handler for modal
    $(document).on('click', '.edit-branch-btn', function(e) {
        e.preventDefault();
        const branchId = $(this).data('id');
        // Fetch branch data via AJAX
        $.get('get_branch_details.php', { id: branchId }, function(response) {
            if (response.success) {
                const b = response.data;
                $('#editBranchId').val(b.branch_id);
                $('#editBranchName').val(b.branch_name);
                $('#editBranchCode').val(b.branch_code);
                $('#editContactNumber').val(b.contact_number);
                $('#editEmail').val(b.email);
                $('#editCompleteAddress').val(b.complete_address);
                $('#editOperatingHours').val(b.operating_hours);
                $('#editStatus').val(b.status);
                
                // Update preview after setting values
                setTimeout(updateBranchPreview, 100);
                
                $('#editBranchModal').modal('show');
            } else {
                showFeedbackModal('error', 'Error!', response.message || 'Failed to fetch branch details.');
            }
        }, 'json');
    });
    // Submit edit form via AJAX
    $('#editBranchForm').on('submit', function(e) {
        e.preventDefault();
        $.post('update_branch.php', $(this).serialize(), function(response) {
            if (response.success) {
                $('#editBranchModal').modal('hide');
                $('#branchTable').DataTable().ajax.reload();
                showFeedbackModal('success', 'Updated!', 'Branch details updated successfully.');
            } else {
                showFeedbackModal('error', 'Error!', response.message || 'Failed to update branch.');
            }
        }, 'json');
    });
});
</script>

<?php include('footer.php'); ?> 