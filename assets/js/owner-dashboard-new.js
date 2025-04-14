document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded event fired in owner-dashboard-new.js');

    // Check for critical elements
    console.log('owner-dashboard-new.js checking for critical elements:');
    console.log('managers-listing exists:', !!document.getElementById('managers-listing'));
    console.log('profile-content exists:', !!document.getElementById('profile-content'));

    // Get elements
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');

    // Check if elements exist before using them
    if (sidebarToggle && sidebar && mainContent) {
        // Toggle function
        function toggleSidebar(e) {
            if (e) e.preventDefault();
            sidebar.classList.toggle('collapsed');

            // Only adjust main content on desktop
            if (window.innerWidth > 768) {
                mainContent.classList.toggle('expanded');
            }
        }

        // Add click event listener
        sidebarToggle.addEventListener('click', toggleSidebar);

        // Handle responsive behavior
        window.addEventListener('resize', () => {
            if (window.innerWidth <= 768) {
                sidebar.classList.add('collapsed');
                mainContent.classList.remove('expanded');
            }
        });
    } else {
        console.error('Required elements not found for sidebar toggle');
    }

    // Theme switcher
    const themeSwitcher = document.getElementById('theme-switcher');
    if (themeSwitcher) {
        themeSwitcher.addEventListener('click', () => {
            const currentTheme = document.body.classList.contains('dark-theme') ? 'dark' : 'light';
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            setTheme(newTheme);
        });
    }

    // Set theme function
    function setTheme(theme, init = false) {
        if (theme === 'dark') {
            document.body.classList.add('dark-theme');
            localStorage.setItem('preferred-theme', 'dark');
        } else {
            document.body.classList.remove('dark-theme');
            localStorage.setItem('preferred-theme', 'light');
        }

        // Update theme switcher icon if it exists
        const themeIcon = document.querySelector('#theme-switcher i');
        if (themeIcon) {
            if (theme === 'dark') {
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
            } else {
                themeIcon.classList.remove('fa-sun');
                themeIcon.classList.add('fa-moon');
            }
        }
    }

    // Initialize theme
    const savedTheme = localStorage.getItem('preferred-theme') || 'light';
    setTheme(savedTheme, true);

    // Profile form toggle
    const editProfileBtn = document.getElementById('edit-profile-btn');
    const saveProfileBtn = document.getElementById('save-profile-btn');
    const cancelProfileBtn = document.getElementById('cancel-profile-btn');
    const profileView = document.getElementById('profile-view');
    const profileForm = document.getElementById('profile-form');

    if (editProfileBtn && saveProfileBtn && cancelProfileBtn && profileView && profileForm) {
        editProfileBtn.addEventListener('click', function() {
            profileView.style.display = 'none';
            profileForm.style.display = 'block';
        });

        cancelProfileBtn.addEventListener('click', function() {
            profileForm.style.display = 'none';
            profileView.style.display = 'block';
        });
    }

    // Profile picture change
    const profilePictureInput = document.getElementById('profile-picture-input');
    const changePictureBtn = document.getElementById('change-picture-btn');
    const profilePicturePreview = document.getElementById('profile-picture-preview');

    if (profilePictureInput && changePictureBtn && profilePicturePreview) {
        changePictureBtn.addEventListener('click', function() {
            profilePictureInput.click();
        });

        profilePictureInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    profilePicturePreview.src = e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    // Initialize charts if Chart.js is available
    if (typeof Chart !== 'undefined') {
        // Property Type Distribution Chart
        const propertyTypeCtx = document.getElementById('propertyTypeChart');
        if (propertyTypeCtx) {
            new Chart(propertyTypeCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Apartments', 'Houses', 'Commercial', 'Land'],
                    datasets: [{
                        data: [45, 25, 20, 10],
                        backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e'],
                        hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#dda20a'],
                        hoverBorderColor: "rgba(234, 236, 244, 1)",
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        }
                    },
                    cutout: '70%'
                }
            });
        }

        // Monthly Revenue Chart
        const revenueCtx = document.getElementById('revenueChart');
        if (revenueCtx) {
            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                    datasets: [{
                        label: "Revenue",
                        lineTension: 0.3,
                        backgroundColor: "rgba(78, 115, 223, 0.05)",
                        borderColor: "rgba(78, 115, 223, 1)",
                        pointRadius: 3,
                        pointBackgroundColor: "rgba(78, 115, 223, 1)",
                        pointBorderColor: "rgba(78, 115, 223, 1)",
                        pointHoverRadius: 3,
                        pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                        pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                        pointHitRadius: 10,
                        pointBorderWidth: 2,
                        data: [0, 10000, 5000, 15000, 10000, 20000, 15000, 25000, 20000, 30000, 25000, 40000],
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            }
                        },
                        y: {
                            ticks: {
                                maxTicksLimit: 5,
                                padding: 10,
                                callback: function(value) {
                                    return '$' + value;
                                }
                            },
                            grid: {
                                color: "rgb(234, 236, 244)",
                                drawBorder: false,
                                borderDash: [2],
                                zeroLineBorderDash: [2]
                            }
                        },
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: "rgb(255,255,255)",
                            bodyColor: "#858796",
                            titleMarginBottom: 10,
                            titleColor: '#6e707e',
                            titleFontSize: 14,
                            borderColor: '#dddfeb',
                            borderWidth: 1,
                            padding: 15,
                            displayColors: false,
                            intersect: false,
                            mode: 'index',
                            caretPadding: 10,
                            callbacks: {
                                label: function(context) {
                                    var label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += '$' + context.parsed.y;
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        }
    }
});
