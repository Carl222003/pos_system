<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>More Bites</title>
        
        <?php
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        ?>
        
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
        
        <!-- Inter Font -->
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        
        <!-- Custom dashboard styles -->
        <link rel="stylesheet" href="styles/dashboard.css?v=<?php echo time(); ?>">
        
        <!-- Scripts -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
        
        <!-- SweetAlert2 -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        
        <script>
            // Update user activity periodically
            function updateUserActivity() {
                $.ajax({
                    url: 'update_user_activity.php',
                    method: 'POST',
                    data: { update_activity: true },
                    success: function(response) {
                        if (response.success) {
                            console.log('User activity updated:', response.timestamp);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error updating user activity:', error);
                    }
                });
            }

            // Update activity every 5 minutes
            setInterval(updateUserActivity, 300000);
            
            // Update activity on page load
            $(document).ready(function() {
                updateUserActivity();
            });

            // Check if CSS is loaded and initialize sidebar functionality
            window.onload = function() {
                const link = document.querySelector('link[href^="styles/dashboard.css"]');
                if (link) {
                    console.log('Dashboard CSS is linked');
                    // Force reload CSS
                    link.href = link.href.split('?')[0] + '?v=' + new Date().getTime();
                }
                
                // User dropdown functionality
                const userMenuBtn = document.querySelector('.user-menu');
                const userDropdown = document.querySelector('.dropdown-menu');
                
                if (userMenuBtn && userDropdown) {
                    // Toggle dropdown on button click
                    userMenuBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        userDropdown.classList.toggle('show');
                        userMenuBtn.setAttribute('aria-expanded', userDropdown.classList.contains('show'));
                    });
                    
                    // Close dropdown when clicking outside
                    document.addEventListener('click', (e) => {
                        if (!userMenuBtn.contains(e.target) && !userDropdown.contains(e.target)) {
                            userDropdown.classList.remove('show');
                            userMenuBtn.setAttribute('aria-expanded', 'false');
                        }
                    });
                }
                
                // Sidebar toggle functionality
                const sidebarToggle = document.querySelector('.sidebar-toggle');
                const sidebar = document.querySelector('.app-sidebar');
                const appMain = document.querySelector('.app-main');
                
                if (sidebarToggle && sidebar && appMain) {
                    sidebarToggle.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        // Toggle collapsed class
                        sidebar.classList.toggle('collapsed');
                        
                        // Only close submenus, keep active states
                        document.querySelectorAll('.submenu').forEach(submenu => {
                            submenu.classList.remove('active');
                        });
                        
                        // Reset dropdown indicators
                        document.querySelectorAll('.dropdown-indicator').forEach(indicator => {
                            indicator.style.transform = 'translateY(-50%) rotate(0deg)';
                        });
                    });
                }
                
                // Handle active menu item
                const currentPath = window.location.pathname;
                const menuLinks = document.querySelectorAll('.menu-link');
                menuLinks.forEach(link => {
                    const href = link.getAttribute('href');
                    if (href && currentPath.endsWith(href)) {
                        link.classList.add('active');
                        // If this is a submenu item, also activate its parent
                        const submenuWrapper = link.closest('.submenu-wrapper');
                        if (submenuWrapper) {
                            const parentLink = submenuWrapper.querySelector('.menu-link');
                            if (parentLink) {
                                parentLink.classList.add('active');
                                submenuWrapper.querySelector('.submenu').classList.add('active');
                            }
                        }
                    }
                });
            };
        </script>
        <style>
            /* Base resets */
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                margin: 0;
                padding: 0;
                background-color: #f5f5f5;
            }

            /* Layout structure */
            .app-container {
                display: flex;
                min-height: 100vh;
                width: 100%;
                background-color: #f5f5f5;
            }

            .app-main {
                flex: 1;
                display: flex;
                flex-direction: column;
                min-height: 100vh;
                background-color: #f5f5f5;
            }

            .app-content {
                flex: 1;
                padding: 20px;
                background-color: #f5f5f5;
            }

            /* Container adjustments */
            .container-fluid {
                width: 100%;
                padding-right: 20px;
                padding-left: 20px;
                margin-right: auto;
                margin-left: auto;
            }

            /* Menu Items section */
            .menu-items-section {
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                margin-bottom: 20px;
                overflow: hidden;
            }

            .menu-items-header {
                padding: 15px 20px;
                border-bottom: 1px solid rgba(0, 0, 0, 0.1);
                background-color: #fff;
            }

            .menu-items-body {
                padding: 20px;
            }

            /* Order Details section */
            .order-details-section {
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                padding: 20px;
                margin-top: 20px;
            }

            /* Notification System Styles */
.notification-container {
    position: relative;
    display: inline-block;
    z-index: 1060;
    background: transparent !important;
    overflow: visible;
}

.notification-btn {
    position: relative;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 12px;
    padding: 12px;
    color: #6c757d;
    font-size: 1.2rem;
}

.notification-btn i {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.notification-btn:hover {
    transform: scale(1.05);
    color: #8B4543;
    background: rgba(139, 69, 67, 0.08) !important;
    box-shadow: 0 4px 12px rgba(139, 69, 67, 0.15);
}

.notification-btn:hover i {
    transform: scale(1.1) rotate(-10deg);
    color: #8B4543;
}

.notification-btn:hover .notification-badge {
    animation: none;
    transform: scale(1.15);
    box-shadow: 
        0 4px 12px rgba(0, 0, 0, 0.25),
        0 2px 6px rgba(0, 0, 0, 0.15);
}

.notification-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: linear-gradient(135deg, #ff4757 0%, #ff3742 50%, #e63946 100%);
    color: white !important;
    border-radius: 16px;
    min-width: 20px;
    height: 20px;
    padding: 0 6px;
    font-size: 0.7rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid #ffffff;
    box-shadow: 
        0 2px 8px rgba(0, 0, 0, 0.15),
        0 1px 3px rgba(0, 0, 0, 0.1);
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.4);
    z-index: 15;
    animation: modernPulse 2.5s ease-in-out infinite;
    line-height: 1;
    white-space: nowrap;
    backdrop-filter: blur(10px);
    transform-origin: center;
}

@keyframes modernPulse {
    0%, 100% { 
        transform: scale(1);
        box-shadow: 
            0 2px 8px rgba(0, 0, 0, 0.15),
            0 1px 3px rgba(0, 0, 0, 0.1);
    }
    50% { 
        transform: scale(1.15);
        box-shadow: 
            0 4px 12px rgba(0, 0, 0, 0.2),
            0 2px 6px rgba(0, 0, 0, 0.15);
    }
}

@keyframes bellShake {
    0%, 100% { transform: rotate(0deg); }
    25% { transform: rotate(-5deg); }
    75% { transform: rotate(5deg); }
}

.notification-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    width: 400px;
    max-height: 550px;
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(25px);
    -webkit-backdrop-filter: blur(25px);
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 
        0 25px 50px rgba(0, 0, 0, 0.15),
        0 12px 24px rgba(0, 0, 0, 0.08),
        0 4px 8px rgba(0, 0, 0, 0.05),
        inset 0 1px 0 rgba(255, 255, 255, 0.4);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-15px) scale(0.9);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 1070;
    overflow: hidden;
    margin-top: 8px;
}

.notification-dropdown.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0) scale(1);
}

.notification-dropdown::before {
    content: '';
    position: absolute;
    top: -8px;
    right: 20px;
    width: 0;
    height: 0;
    border-left: 8px solid transparent;
    border-right: 8px solid transparent;
    border-bottom: 8px solid rgba(255, 255, 255, 0.95);
    filter: drop-shadow(0 -2px 4px rgba(0, 0, 0, 0.1));
}

.notification-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px 16px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
    background: linear-gradient(135deg, rgba(139, 69, 67, 0.05) 0%, rgba(212, 165, 154, 0.05) 100%);
}

.notification-title {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: #3C2A2A;
    display: flex;
    align-items: center;
}

.notification-title i {
    color: #8B4543;
}

.notification-actions {
    display: flex;
    gap: 8px;
}

.btn-refresh {
    background: rgba(139, 69, 67, 0.1);
    border: none;
    border-radius: 8px;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #8B4543;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-refresh:hover {
    background: rgba(139, 69, 67, 0.2);
    transform: rotate(180deg);
}

.notification-list {
    max-height: 350px;
    overflow-y: auto;
    padding: 0;
}

.notification-list::-webkit-scrollbar {
    width: 6px;
}

.notification-list::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.05);
    border-radius: 3px;
}

.notification-list::-webkit-scrollbar-thumb {
    background: rgba(139, 69, 67, 0.3);
    border-radius: 3px;
}

.notification-list::-webkit-scrollbar-thumb:hover {
    background: rgba(139, 69, 67, 0.5);
}

.notification-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    color: #6c757d;
    gap: 12px;
}

.notification-loading i {
    font-size: 1.5rem;
    color: #8B4543;
}

.notification-item {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 16px 24px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.2s ease;
    cursor: pointer;
}

.notification-item:hover {
    background: rgba(139, 69, 67, 0.03);
}

.notification-item:last-child {
    border-bottom: none;
}

.notification-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    color: white;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.notification-content {
    flex: 1;
    min-width: 0;
}

.notification-title-text {
    font-weight: 600;
    color: #3C2A2A;
    font-size: 0.9rem;
    margin-bottom: 4px;
    line-height: 1.3;
}

.notification-message {
    color: #6c757d;
    font-size: 0.85rem;
    margin-bottom: 4px;
    line-height: 1.4;
}

.notification-details {
    color: #8B4543;
    font-size: 0.8rem;
    font-weight: 500;
}

.notification-timestamp {
    color: #adb5bd;
    font-size: 0.75rem;
    margin-top: 4px;
}

.notification-footer {
    padding: 16px 24px;
    border-top: 1px solid rgba(0, 0, 0, 0.08);
    background: linear-gradient(135deg, rgba(139, 69, 67, 0.05) 0%, rgba(212, 165, 154, 0.05) 100%);
}

.btn-view-all {
    width: 100%;
    background: linear-gradient(135deg, #8B4543 0%, #723937 100%);
    color: white;
    border: none;
    border-radius: 12px;
    padding: 12px 20px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 4px 16px rgba(139, 69, 67, 0.3);
}

.btn-view-all:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(139, 69, 67, 0.4);
}

.notification-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    color: #6c757d;
    gap: 12px;
}

.notification-empty i {
    font-size: 2rem;
    color: #adb5bd;
}

.notification-empty span {
    font-size: 0.9rem;
}

/* Header adjustments */
.app-header {
    padding: 15px 20px;
    background: #fff;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    z-index: 1000;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 15px;
    position: relative;
}

.greeting {
    flex: 1;
}

.realtime-clock {
    display: flex !important;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    gap: 1.5rem;
    min-width: 260px;
    padding: 16px 24px;
    background: linear-gradient(135deg, rgba(139, 69, 67, 0.08) 0%, rgba(212, 165, 154, 0.08) 100%);
    border: 1px solid rgba(139, 69, 67, 0.15);
    border-radius: 16px;
    box-shadow: 
        0 4px 12px rgba(139, 69, 67, 0.12),
        0 2px 4px rgba(0, 0, 0, 0.05),
        inset 0 1px 0 rgba(255, 255, 255, 0.1);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: visible;
    visibility: visible;
    opacity: 1;
    backdrop-filter: blur(10px);
}



.clock-icon {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 20px;
    height: 20px;
    background: rgba(139, 69, 67, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #8B4543;
    font-size: 0.8rem;
    opacity: 0.7;
    transition: all 0.3s ease;
}

.realtime-clock:hover .clock-icon {
    opacity: 1;
    background: rgba(139, 69, 67, 0.2);
    transform: scale(1.1);
}

.realtime-clock:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 
        0 8px 20px rgba(139, 69, 67, 0.18),
        0 4px 8px rgba(0, 0, 0, 0.08),
        inset 0 1px 0 rgba(255, 255, 255, 0.15);
    border-color: rgba(139, 69, 67, 0.25);
    background: linear-gradient(135deg, rgba(139, 69, 67, 0.12) 0%, rgba(212, 165, 154, 0.12) 100%);
}

.realtime-clock span {
    display: inline-block !important;
    line-height: 1.2;
    text-align: center;
    white-space: nowrap;
    visibility: visible;
    opacity: 1;
}

#current-time {
    font-weight: 700;
    color: #3C2A2A !important;
    font-size: 1.3rem;
    font-family: 'Inter', 'Segoe UI', sans-serif;
    letter-spacing: 0.5px;
    margin-bottom: 0;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    display: inline-block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

#current-date {
    font-size: 0.9rem;
    color: #8B4543 !important;
    font-weight: 500;
    opacity: 1 !important;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    border-right: 2px solid rgba(139, 69, 67, 0.2);
    padding-right: 1.2rem;
    margin-right: 0.8rem;
    display: inline-block !important;
    visibility: visible !important;
}

/* Clock animation for time updates */
@keyframes timeUpdate {
    0% { transform: scale(1); }
    50% { transform: scale(1.02); }
    100% { transform: scale(1); }
}

#current-time.updating {
    animation: timeUpdate 0.3s ease-in-out;
}

/* Responsive clock adjustments */
@media (max-width: 768px) {
    .realtime-clock {
        min-width: 220px;
        padding: 12px 16px;
        gap: 1rem;
    }
    
    #current-time {
        font-size: 1.1rem;
    }
    
    #current-date {
        font-size: 0.8rem;
        padding-right: 0.8rem;
        margin-right: 0.5rem;
    }
}

@media (max-width: 480px) {
    .realtime-clock {
        min-width: 180px;
        padding: 10px 14px;
        gap: 0.8rem;
    }
    
    #current-time {
        font-size: 1rem;
    }
    
    #current-date {
        font-size: 0.75rem;
        padding-right: 0.6rem;
        margin-right: 0.4rem;
    }
}

            /* Existing styles continue below */
            .menu-link {
                position: relative;
                display: flex;
                align-items: center;
                padding: 12px 15px;
                text-decoration: none;
                color: rgba(255, 255, 255, 0.8);
                transition: all 0.3s ease;
            }

            .menu-link .dropdown-indicator {
                position: absolute;
                right: 15px;
                top: 50%;
                transform: translateY(-50%);
                font-size: 10px;
                color: rgba(255, 255, 255, 0.7);
                transition: all 0.3s ease;
            }

            .menu-link:hover .dropdown-indicator {
                color: rgba(255, 255, 255, 0.9);
            }

            .menu-link.active .dropdown-indicator {
                color: #ffffff;
                transform: translateY(-50%) rotate(180deg);
            }

            .menu-link i:not(.dropdown-indicator) {
                width: 20px;
                margin-right: 12px;
                text-align: center;
            }

            .menu-link span {
                flex: 1;
            }

            /* Hide dropdown icon when sidebar is collapsed */
            .app-sidebar.collapsed .dropdown-indicator {
                display: none;
            }

            /* Adjust padding when sidebar is collapsed */
            .app-sidebar.collapsed .menu-link {
                padding-right: 12px !important;
                justify-content: center;
            }

            .app-sidebar.collapsed .menu-link i:not(.dropdown-indicator) {
                margin-right: 0;
            }

            /* Submenu styles */
            .submenu-wrapper {
                position: relative;
            }

            .submenu {
                position: static;
                width: 100%;
                background: rgba(0, 0, 0, 0.1);
                list-style: none;
                padding: 0;
                margin: 0;
                max-height: 0;
                overflow: hidden;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
            }

            .submenu.active {
                max-height: 1000px;
                opacity: 1;
                visibility: visible;
                padding: 8px;
            }

            .submenu-link {
                display: flex;
                align-items: center;
                padding: 10px 15px;
                color: rgba(255, 255, 255, 0.7);
                text-decoration: none;
                border-radius: 6px;
                transition: all 0.3s ease;
                font-size: 0.9em;
            }

            .submenu-link i {
                width: 20px;
                margin-right: 10px;
                font-size: 0.9em;
            }

            .submenu-link:hover {
                color: #fff;
                background: rgba(255, 255, 255, 0.1);
                padding-left: 20px;
            }

            /* Rotate dropdown indicator when active */
            .menu-link.active .dropdown-indicator {
                transform: translateY(-50%) rotate(180deg);
            }

            /* Hide submenu when sidebar is collapsed */
            .app-sidebar.collapsed .submenu {
                display: none;
            }

            /* Add these new styles */
            .menu-link.active {
                background: rgba(255, 255, 255, 0.1);
                color: #fff;
                position: relative;
            }

            .menu-link.active::after {
                content: '';
                position: absolute;
                left: 0;
                bottom: 0;
                width: 100%;
                height: 2px;
                background: #fff;
            }

            /* Adjust underline for collapsed state */
            .app-sidebar.collapsed .menu-link.active::after {
                width: 4px;
                height: 100%;
                top: 0;
                bottom: auto;
            }

            /* Keep active state visible in collapsed mode */
            .app-sidebar.collapsed .menu-link.active {
                background: rgba(255, 255, 255, 0.1);
            }

            #receiptContent {
                font-family: 'Inter', sans-serif;
                line-height: 1.6;
                color: #000;
            }

            #receiptContent h5 {
                margin-bottom: 20px;
                text-align: center;
            }

            #receiptContent .receipt-header,
            #receiptContent .receipt-footer {
                text-align: center;
                margin-bottom: 20px;
            }

            #receiptContent .receipt-details,
            #receiptContent .receipt-items {
                margin-bottom: 20px;
            }

            #receiptContent .receipt-details th,
            #receiptContent .receipt-details td,
            #receiptContent .receipt-items th,
            #receiptContent .receipt-items td {
                padding: 5px;
                text-align: left;
            }

            #receiptContent .receipt-total {
                font-weight: bold;
                text-align: right;
            }

            @media print {
                body * {
                    visibility: hidden;
                }
                #receiptContent, #receiptContent * {
                    visibility: visible;
                }
                #receiptContent {
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 100%;
                    padding: 20px;
                    box-sizing: border-box;
                }
            }
            
            /* Header Action Buttons */
            .header-action-btn {
                background: transparent !important;
                border: none;
                color: #6c757d;
                font-size: 1.1rem;
                padding: 8px;
                border-radius: 8px;
                cursor: pointer;
                transition: all 0.3s ease;
                position: relative;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: visible;
            }
            
            .header-action-btn:hover {
                background: rgba(139, 69, 67, 0.1);
                color: #8B4543;
                transform: scale(1.05);
            }
            
            .notification-btn {
                background: transparent !important;
                overflow: visible !important;
            }
            
            .notification-btn:hover {
                background: transparent !important;
            }
            
            /* User Menu Styles */
            .user-menu {
                background: none;
                border: none;
                color: #6c757d;
                font-size: 0.9rem;
                padding: 8px 12px;
                border-radius: 8px;
                cursor: pointer;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .user-menu:hover {
                background: rgba(139, 69, 67, 0.1);
                color: #8B4543;
            }
            
            .dropdown-menu {
                border: none;
                border-radius: 12px;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
                padding: 8px 0;
            }
            
            .dropdown-item {
                padding: 10px 20px;
                color: #6c757d;
                transition: all 0.2s ease;
                border-radius: 8px;
                margin: 2px 8px;
            }
            
            .dropdown-item:hover {
                background: rgba(139, 69, 67, 0.1);
                color: #8B4543;
            }
        </style>
    </head>
    <body>
        <div class="app-container">
            <aside class="app-sidebar">
                <div class="sidebar-header">
                    <a href="dashboard.php" class="sidebar-brand">
                        <img src="asset/images/logo.png" alt="MoreBites" class="brand-logo">
                        <span>MoreBites</span>
                    </a>
                    <button class="sidebar-toggle">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                </div>
                <div class="sidebar-menu">
                    <nav id="mainMenu">
                        <ul class="menu-list">
                            <?php if ($_SESSION['user_type'] === 'Cashier'): ?>
                                <li class="menu-item">
                                    <a href="add_order.php" class="menu-link">
                                        <i class="fa-solid fa-cart-plus"></i>
                                        <span>Create Order</span>
                                    </a>
                                </li>
                                <li class="menu-item">
                                    <a href="order.php" class="menu-link">
                                        <i class="fa-solid fa-list-ul"></i>
                                        <span>Orders</span>
                                    </a>
                                </li>
                                <li class="menu-item">
                                    <a href="sales.php" class="menu-link">
                                        <i class="fa-solid fa-cash-register"></i>
                                        <span>Sales</span>
                                    </a>
                                </li>
                                <li class="menu-item">
                                    <a href="order_history.php" class="menu-link">
                                        <i class="fa-solid fa-history"></i>
                                        <span>Order History</span>
                                    </a>
                                </li>
                                <li class="menu-item">
                                    <a href="cashier_report.php" class="menu-link">
                                        <i class="fa-solid fa-chart-bar"></i>
                                        <span>My Performance</span>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="menu-item">
                                    <a href="<?php echo ($_SESSION['user_type'] === 'Stockman') ? 'stockman_dashboard.php' : 'dashboard.php'; ?>" class="menu-link">
                                        <i class="fa-solid fa-tachometer-alt"></i>
                                        <span>Dashboard</span>
                                    </a>
                                </li>
                                <?php if(isset($_SESSION['user_type'])): ?>
                                    <?php if($_SESSION['user_type'] === 'Stockman'): ?>
                                        <li class="menu-item">
                                            <a href="available_ingredients.php" class="menu-link">
                                                <i class="fa-solid fa-mortar-pestle"></i>
                                                <span>Available Ingredients</span>
                                            </a>
                                        </li>
                                        <li class="menu-item">
                                            <a href="stock_inventory.php" class="menu-link">
                                                <i class="fa-solid fa-boxes"></i>
                                                <span>Stock Inventory</span>
                                            </a>
                                        </li>
                                        <li class="menu-item">
                                            <a href="stockman_products.php" class="menu-link">
                                                <i class="fa-solid fa-shopping-bag"></i>
                                                <span>Available Products</span>
                                            </a>
                                        </li>
                                        <li class="menu-item">
                                            <a href="request_stock.php" class="menu-link">
                                                <i class="fa-solid fa-plus-circle"></i>
                                                <span>Request Ingredient</span>
                                            </a>
                                        </li>
                                        <li class="menu-item">
                                            <a href="request_stock_updates.php" class="menu-link">
                                                <i class="fa-solid fa-edit"></i>
                                                <span>Update Stock</span>
                                            </a>
                                        </li>
                                        <li class="menu-item">
                                            <a href="stockman_activity_log.php" class="menu-link">
                                                <i class="fa-solid fa-clipboard-list"></i>
                                                <span>Activity Log</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    <?php if($_SESSION['user_type'] === 'Admin'): ?>
                                        <li class="menu-item">
                                            <a href="category.php" class="menu-link">
                                                <i class="fa-solid fa-th-list"></i>
                                                <span>Category</span>
                                            </a>
                                        </li>
                                        <li class="menu-item">
                                            <div class="submenu-wrapper">
                                                <a href="#" class="menu-link" onclick="toggleSubmenu(event, this)">
                                                    <i class="fa-solid fa-users"></i>
                                                    <span>User</span>
                                                    <i class="fa-solid fa-chevron-down dropdown-indicator"></i>
                                                </a>
                                                <ul class="submenu">
                                                    <li><a href="user.php" class="submenu-link"><i class="fa-solid fa-users-gear"></i>Manage Users</a></li>
                                                    <li><a href="add_user.php?role=cashier" class="submenu-link"><i class="fa-solid fa-cash-register"></i>Add Cashier</a></li>
                                                    <li><a href="add_user.php?role=stockman" class="submenu-link"><i class="fa-solid fa-boxes"></i>Add Stockman</a></li>
                                                </ul>
                                            </div>
                                        </li>
                                        <li class="menu-item">
                                            <a href="ingredients.php" class="menu-link">
                                                <i class="fa-solid fa-mortar-pestle"></i>
                                                <span>Ingredients</span>
                                            </a>
                                        </li>
                                        <li class="menu-item">
                                            <a href="ingredient_requests.php" class="menu-link">
                                                <i class="fa-solid fa-clipboard-list"></i>
                                                <span>List of Request</span>
                                            </a>
                                        </li>
                                        <!-- Stock Update Requests menu item removed -->
                                        <li class="menu-item">
                                            <a href="product.php" class="menu-link">
                                                <i class="fa-solid fa-utensils"></i>
                                                <span>Product</span>
                                            </a>
                                        </li>
                                        <li class="menu-item">
                                            <div class="submenu-wrapper">
                                                <a href="#" class="menu-link" onclick="toggleSubmenu(event, this)">
                                                    <i class="fa-solid fa-store"></i>
                                                    <span>Branch</span>
                                                    <i class="fa-solid fa-chevron-down dropdown-indicator"></i>
                                                </a>
                                                <ul class="submenu">
                                                    <li><a href="add_branch.php" class="submenu-link"><i class="fa-solid fa-plus"></i>Add Branch</a></li>
                                                    <li><a href="branch_details.php" class="submenu-link"><i class="fa-solid fa-info-circle"></i>Branch Details</a></li>
                                                    <li><a href="branch_overview.php" class="submenu-link"><i class="fa-solid fa-chart-bar"></i>Branch Overview</a></li>
                                                </ul>
                                            </div>
                                        </li>
                                        <!-- Sales menu item removed -->
                                        <li class="menu-item">
                                            <a href="archived_list.php" class="menu-link">
                                                <i class="fa-solid fa-box-archive"></i>
                                                <span>Archived List</span>
                                            </a>
                                        </li>
                                        <li class="menu-item">
                                            <a href="activity_log.php" class="menu-link">
                                                <i class="fa-solid fa-list-check"></i>
                                                <span>Activity log</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php if($_SESSION['user_type'] === 'Cashier'): ?>
                                        <li class="menu-item">
                                            <a href="add_order.php" class="menu-link">
                                                <i class="fa-solid fa-cart-plus"></i>
                                                <span>Create Order</span>
                                            </a>
                                        </li>
                                        <li class="menu-item">
                                            <a href="order.php" class="menu-link">
                                                <i class="fa-solid fa-history"></i>
                                                <span>Order History</span>
                                            </a>
                                        </li>
                                        <li class="menu-item">
                                            <a href="cashier_report.php" class="menu-link">
                                                <i class="fa-solid fa-chart-line"></i>
                                                <span>My Performance</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </aside>

            <div class="app-main">
                <header class="app-header">
                    <div class="greeting">
                        <div class="welcome-text" id="greeting-text">
                        </div>
                    </div>
                    <div class="header-actions">
                        <div id="realtime-clock" class="realtime-clock">
                            <span id="current-date"></span>
                            <span id="current-time"></span>
                        </div>
                        <div class="notification-container">
                            <button class="header-action-btn notification-btn" id="notificationBtn" title="Notifications">
                                <i class="fa-solid fa-bell"></i>
                                <span class="notification-badge" id="notificationBadge" style="display: none !important;">0</span>
                            </button>
                            
                            <!-- Glassmorphism Notification Dropdown -->
                            <div class="notification-dropdown" id="notificationDropdown">
                                <div class="notification-header">
                                    <h6 class="notification-title">
                                        <i class="fas fa-bell me-2"></i>Notifications
                                    </h6>
                                    <div class="notification-actions">
                                        <button class="btn-refresh" id="refreshNotifications" title="Refresh">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="notification-list" id="notificationList">
                                    <div class="notification-loading">
                                        <i class="fas fa-spinner fa-spin"></i>
                                        <span>Loading notifications...</span>
                                    </div>
                                </div>
                                
                                <div class="notification-footer">
                                    <button class="btn-view-all" id="viewAllNotifications">
                                        <i class="fas fa-eye me-2"></i>View All
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="dropdown">
                            <button class="user-menu" type="button" aria-expanded="false">
                                <i class="fa-solid fa-user"></i>
                                <span><?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User'; ?></span>
                                <i class="fa-solid fa-chevron-down ms-2"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li>
                                    <a class="dropdown-item" href="user_profile.php">
                                        <i class="fa-solid fa-user-circle"></i>
                                        <span>Profile</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="change_password.php">
                                        <i class="fa-solid fa-key"></i>
                                        <span>Change Password</span>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="logout.php">
                                        <i class="fa-solid fa-sign-out-alt"></i>
                                        <span>Logout</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </header>

                <main class="app-content">
                    <div class="container-fluid px-4 mb-4">



<script>
function updateGreeting() {
    const now = new Date();
    const hour = now.getHours();
    let greeting = '';
    
    if (hour >= 5 && hour < 12) {
        greeting = 'Good Morning';
    } else if (hour >= 12 && hour < 17) {
        greeting = 'Good Afternoon';
    } else {
        greeting = 'Good Evening';
    }
    
    const username = '<?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Admin'; ?>';
    document.getElementById('greeting-text').innerHTML = greeting + ', <span class="username">' + username + '</span>';
}

// Notification System
let notificationDropdown = null;
let notificationBadge = null;

function initNotificationSystem() {
    console.log('🔔 Initializing notification system...');
    
    notificationDropdown = document.getElementById('notificationDropdown');
    notificationBadge = document.getElementById('notificationBadge');
    
    if (!notificationDropdown) {
        console.error('❌ Notification dropdown not found!');
        return;
    }
    
    if (!notificationBadge) {
        console.error('❌ Notification badge not found!');
        return;
    }
    
    console.log('✅ Notification elements found:', { notificationDropdown, notificationBadge });
    
    // Toggle notification dropdown
    const notificationBtn = document.getElementById('notificationBtn');
    if (notificationBtn) {
        notificationBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            console.log('🔔 Notification button clicked!');
            toggleNotificationDropdown();
        });
        console.log('✅ Notification button event listener added');
    } else {
        console.error('❌ Notification button not found!');
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!notificationDropdown.contains(e.target) && !e.target.closest('.notification-btn')) {
            hideNotificationDropdown();
        }
    });
    
    // Refresh notifications
    document.getElementById('refreshNotifications').addEventListener('click', function(e) {
        e.stopPropagation();
        loadNotifications();
    });
    
    // View all notifications
    document.getElementById('viewAllNotifications').addEventListener('click', function(e) {
        e.stopPropagation();
        // Redirect to notifications page or show full modal
        console.log('View all notifications clicked');
    });
    
    // Load notifications on page load to show badge count
    loadNotifications();
    
    // Auto-refresh notifications every 30 seconds
    setInterval(function() {
        // Only refresh if dropdown is not open
        if (!notificationDropdown.classList.contains('show')) {
            loadNotifications();
        }
    }, 30000);
}

function toggleNotificationDropdown() {
    console.log('🔄 Toggling notification dropdown...');
    console.log('Current state:', notificationDropdown.classList.contains('show'));
    
    if (notificationDropdown.classList.contains('show')) {
        hideNotificationDropdown();
    } else {
        showNotificationDropdown();
    }
}

function showNotificationDropdown() {
    console.log('👁️ Showing notification dropdown...');
    notificationDropdown.classList.add('show');
    console.log('Dropdown classes after show:', notificationDropdown.className);
    
    // Load notifications first, then clear badge after a short delay
    loadNotifications();
    
    // Clear the notification badge when opening dropdown (mark as read)
    setTimeout(() => {
        updateNotificationBadge(0);
    }, 500);
}

function hideNotificationDropdown() {
    console.log('🙈 Hiding notification dropdown...');
    notificationDropdown.classList.remove('show');
    console.log('Dropdown classes after hide:', notificationDropdown.className);
}

function loadNotifications() {
    const notificationList = document.getElementById('notificationList');
    
    // Show loading state
    notificationList.innerHTML = `
        <div class="notification-loading">
            <i class="fas fa-spinner fa-spin"></i>
            <span>Loading notifications...</span>
        </div>
    `;
    
    // Fetch notifications from server
    fetch('notifications.php?action=get_notifications')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayNotifications(data.notifications);
                // Always update badge with current count, unless dropdown is open and was just clicked
                updateNotificationBadge(data.count);
            } else {
                showNotificationError();
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            showNotificationError();
        });
}

function displayNotifications(notifications) {
    const notificationList = document.getElementById('notificationList');
    
    if (notifications.length === 0) {
        notificationList.innerHTML = `
            <div class="notification-empty">
                <i class="fas fa-bell-slash"></i>
                <span>No new notifications</span>
            </div>
        `;
        return;
    }
    
    const notificationsHTML = notifications.map(notification => `
        <div class="notification-item" data-notification-id="${notification.id}">
            <div class="notification-icon" style="background: ${notification.icon_color}">
                <i class="${notification.icon}"></i>
            </div>
            <div class="notification-content">
                <div class="notification-title-text">${notification.title}</div>
                <div class="notification-message">${notification.message}</div>
                <div class="notification-details">${notification.details}</div>
                <div class="notification-timestamp">${notification.timestamp}</div>
            </div>
        </div>
    `).join('');
    
    notificationList.innerHTML = notificationsHTML;
    
    // Add click handlers for notification items
    document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', function() {
            const notificationId = this.dataset.notificationId;
            handleNotificationClick(notificationId);
        });
    });
}

function updateNotificationBadge(count) {
    if (count > 0) {
        notificationBadge.textContent = count > 99 ? '99+' : count;
        notificationBadge.style.display = 'flex';
        notificationBadge.style.removeProperty('display');
    } else {
        notificationBadge.style.display = 'none';
        notificationBadge.style.setProperty('display', 'none', 'important');
    }
}

function showNotificationError() {
    const notificationList = document.getElementById('notificationList');
    notificationList.innerHTML = `
        <div class="notification-empty">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Failed to load notifications</span>
        </div>
    `;
}

function handleNotificationClick(notificationId) {
    // Handle different notification types
    const [type, id] = notificationId.split('_');
    
    switch(type) {
        case 'low':
        case 'out':
            // Redirect to ingredients page
            window.location.href = 'ingredients.php';
            break;
        case 'req':
            // Redirect to ingredient requests page
            window.location.href = 'ingredient_requests.php';
            break;
        case 'processed':
            // Redirect to stockman's own requests page
            window.location.href = 'ingredient_requests.php';
            break;
        case 'mystatus':
            // Redirect to stockman's own requests page
            window.location.href = 'ingredient_requests.php';
            break;
        case 'exp':
            // Redirect to ingredients page with expiry filter
            window.location.href = 'ingredients.php?filter=expiring';
            break;
        default:
            console.log('Unknown notification type:', type);
    }
    
    // Hide dropdown after action
    hideNotificationDropdown();
}

function updateClock() {
    const timeElement = document.getElementById('current-time');
    const dateElement = document.getElementById('current-date');
    const now = new Date();
    
    // Format time
    const time = now.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit',
        second: '2-digit',
        hour12: true 
    });
    
    // Format date
    const date = now.toLocaleDateString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric'
    });
    
    // Add update animation
    if (timeElement.textContent !== time) {
        timeElement.classList.add('updating');
        setTimeout(() => {
            timeElement.classList.remove('updating');
        }, 300);
    }
    
    dateElement.textContent = date;
    timeElement.textContent = time;
    updateGreeting(); // Update greeting with current time
}

// Update clock and greeting immediately and then every second
updateClock();
setInterval(updateClock, 1000);



// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Initialize notification system
    initNotificationSystem();
});
</script>

<script>
    // Toggle submenu function
    function toggleSubmenu(event, element) {
        event.preventDefault();
        event.stopPropagation();
        
        const submenu = element.nextElementSibling;
        const isMenuClick = event.target === element || event.target.parentElement === element;
        
        if (isMenuClick) {
            // Toggle current submenu
            element.classList.toggle('active');
            submenu.classList.toggle('active');
            
            // Close other submenus
            document.querySelectorAll('.submenu-wrapper').forEach(wrapper => {
                if (wrapper !== element.parentElement) {
                    wrapper.querySelector('.menu-link').classList.remove('active');
                    wrapper.querySelector('.submenu').classList.remove('active');
                }
            });
        }
        
        // Keep active states for current page
        const currentPath = window.location.pathname;
        submenu.querySelectorAll('.submenu-link').forEach(link => {
            if (link.getAttribute('href') && currentPath.endsWith(link.getAttribute('href'))) {
                link.classList.add('active');
                element.classList.add('active');
                submenu.classList.add('active');
            }
        });
    }
    
    // Add this to window.onload
    window.addEventListener('load', function() {
        // Prevent document click from closing submenu
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.submenu-wrapper')) {
                // Close all submenus when clicking outside
                document.querySelectorAll('.menu-link, .submenu').forEach(el => {
                    el.classList.remove('active');
                });
            }
        });
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Check if the current page is the "Create Order" page
  if (window.location.pathname.includes('create_order.php')) {
    // Hide the menu
    document.getElementById('mainMenu').style.display = 'none';
  } else {
    // Show the menu on other pages
    document.getElementById('mainMenu').style.display = 'block';
  }
});
</script>